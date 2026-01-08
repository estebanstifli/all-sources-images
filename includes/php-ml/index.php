<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Simple Keyword Extractor
 * 
 * Extracts keywords from text using TF-IDF-like weighting.
 * Uses a simple word tokenizer (no external dependencies).
 *
 * @package All_Sources_Images
 * @since 1.0.0
 */

class KeywordExtractor
{
    private $stopWords;

    /**
     * Constructor to initialize stop words.
     * Selects the appropriate stop words file based on the specified language.
     *
     * @param string $selected_lang Language code for stop words (default: 'en').
     */
    public function __construct($selected_lang = 'en')
    {
        // Supported language codes for stop words
        $languages = array('ar','bg','cs','da','de','el','en','es','et','fa','fi','fr','he','hi','hr','hu','hy','id','it','ja','ko','lt','lv','nl','no','pl','pt','ro','ru','sk','sl','sv','th','tr','vi','zh');

        // Select the stop words file based on the chosen language or default to English
        if (in_array($selected_lang, $languages)) {
            $stopWordsFile = plugin_dir_path(__FILE__) . 'stop-words/' . $selected_lang . '.txt';
        } else {
            $stopWordsFile = plugin_dir_path(__FILE__) . 'stop-words/en.txt';
        }

        // Load and prepare stop words as lowercase
        if ( file_exists( $stopWordsFile ) ) {
            $this->stopWords = array_map('strtolower', array_map('trim', file($stopWordsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));
        } else {
            $this->stopWords = array();
        }
    }

    /**
     * Simple word tokenizer - splits text into words.
     * Replaces the php-ml WordTokenizer dependency.
     *
     * @param string $text The input text to tokenize.
     * @return array List of words/tokens.
     */
    private function tokenize($text)
    {
        // Match word characters (letters, numbers, underscores) with Unicode support
        preg_match_all('/\w+/u', $text, $matches);
        return $matches[0];
    }

    /**
     * Extracts keywords from the provided text and returns the top results.
     *
     * @param string $text The input text for keyword extraction.
     * @param int $topResults Number of top keywords to return (default: 10).
     * @return array List of most relevant keywords.
     */
    public function extractKeywords($text, $topResults = 10)
    {
        // Clean the text before processing
        $cleanedText = $this->cleanPostContent($text);

        // Tokenize and filter words by removing stop words
        $words = $this->tokenize($cleanedText);
        $filteredWords = array_filter($words, function($word) {
            return !in_array(strtolower($word), $this->stopWords) && strlen($word) > 2;
        });
        $filteredWords = array_values($filteredWords); // Re-index array
        
        // Count unigrams, bigrams, and trigrams
        $unigrams = array_count_values($filteredWords);
        $bigrams = array_count_values($this->generateNgrams($filteredWords, 2));
        $trigrams = array_count_values($this->generateNgrams($filteredWords, 3));

        // Combine the counts of unigrams, bigrams, and trigrams
        $combinedCounts = array();
        $this->mergeCounts($unigrams, $combinedCounts);
        $this->mergeCounts($bigrams, $combinedCounts);
        $this->mergeCounts($trigrams, $combinedCounts);

        // Return top keywords based on weighted counts
        return $this->getTopKeywords($combinedCounts, count($words), $topResults);
    }

    /**
     * Cleans the content by removing HTML tags, Gutenberg comments, and unnecessary spaces.
     *
     * @param string $content The raw content to clean.
     * @return string The cleaned content.
     */
    private function cleanPostContent($content)
    {
        // Remove Gutenberg comments
        $content = preg_replace('/<!--.*?-->/s', '', $content);
        
        // Remove all HTML tags
        $content = wp_strip_all_tags($content);
        
        // Remove multiple spaces and unnecessary newlines
        $content = preg_replace('/\s+/', ' ', $content);

        // Remove spaces &nbsp; 
        $content = str_replace('&nbsp;', ' ', $content);
        
        // Trim leading and trailing whitespace
        return trim($content);
    }

    /**
     * Generates n-grams (bigrams or trigrams) from the list of words, excluding any that contain stop words.
     *
     * @param array $words List of filtered words.
     * @param int $n Number of words in each n-gram.
     * @return array Unique n-grams as strings.
     */
    private function generateNgrams($words, $n)
    {
        $ngrams = array();
        $wordCount = count($words);
        for ($i = 0; $i <= $wordCount - $n; $i++) {
            $ngram = array_slice($words, $i, $n);

            // Ensure the n-gram contains no stop words and no repeated words
            if (count(array_intersect(array_map('strtolower', $ngram), $this->stopWords)) === 0 && count(array_unique($ngram)) === count($ngram)) {
                $ngrams[] = implode(' ', $ngram);
            }
        }
        return array_unique($ngrams);
    }

    /**
     * Merges word counts into a combined array, adding counts if the word already exists.
     *
     * @param array $counts Array of word counts to merge.
     * @param array &$combinedCounts Reference to the array where counts are combined.
     */
    private function mergeCounts($counts, &$combinedCounts)
    {
        foreach ($counts as $phrase => $count) {
            if ( isset( $combinedCounts[$phrase] ) ) {
                $combinedCounts[$phrase] += $count;
            } else {
                $combinedCounts[$phrase] = $count;
            }
        }
    }

    /**
     * Calculates and retrieves the top keywords based on weighted counts.
     *
     * @param array $combinedCounts Combined counts of unigrams, bigrams, and trigrams.
     * @param int $totalWords Total number of words in the cleaned text.
     * @param int $topResults Number of top results to retrieve.
     * @return array Top keywords sorted by weight.
     */
    private function getTopKeywords($combinedCounts, $totalWords, $topResults)
    {
        if ( $totalWords === 0 ) {
            return array();
        }
        
        $weightedCounts = array();
        foreach ($combinedCounts as $phrase => $count) {
            // Weight based on phrase length for higher relevance
            $lengthWeight = strlen($phrase) / 10;
            $weightedCounts[$phrase] = ($count / $totalWords) * $lengthWeight;
        }

        // Sort by descending weight and return the top results
        arsort($weightedCounts);
        return array_keys(array_slice($weightedCounts, 0, $topResults, true));
    }
}