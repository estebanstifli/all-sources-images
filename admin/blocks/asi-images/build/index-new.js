/**
 * All Sources Images - Gutenberg Block (Rewritten Clean Version)
 * 
 * This file replaces the webpack-compiled index.js with a clean, readable version
 */

(function() {
    'use strict';

    const { registerBlockType } = wp.blocks;
    const { Button, Modal, TextControl, TabPanel } = wp.components;
    const { useState } = wp.element;
    const { __ } = wp.i18n;
    const { useBlockProps, BlockControls, AlignmentToolbar } = wp.blockEditor;

    // Bank configuration mapping
    function getBankConfig(bank) {
        const bankLower = bank.toLowerCase();
        const configs = {
            'pixabay': { apiPath: 'hits', imgKey: 'webformatURL', imgLarge: 'largeImageURL', altKey: 'tags', captionKey: 'user' },
            'openverse': { apiPath: 'results', imgKey: 'url', imgLarge: 'url', altKey: 'title', captionKey: 'creator' },
            'cc_search': { apiPath: 'results', imgKey: 'url', imgLarge: 'url', altKey: 'title', captionKey: 'creator' },
            'unsplash': { apiPath: 'results', imgKey: 'urls.small', imgLarge: 'urls.regular', altKey: 'alt_description', captionKey: 'user.name' },
            'pexels': { apiPath: 'photos', imgKey: 'src.tiny', imgLarge: 'src.large2x', altKey: 'alt', captionKey: 'photographer' },
            'youtube': { apiPath: 'items', imgKey: 'snippet.thumbnails.medium.url', imgLarge: 'snippet.thumbnails.high.url', altKey: 'snippet.title', captionKey: '' },
            'flickr': { apiPath: 'photos', imgKey: 'url', imgLarge: 'url', altKey: '', captionKey: '' }
        };
        return configs[bankLower] || { apiPath: 'results', imgKey: 'url', imgLarge: 'url', altKey: 'alt', captionKey: 'caption' };
    }

    // Helper to get nested object value by path string
    function getNestedValue(obj, path) {
        if (!path) return '';
        return path.split('.').reduce((o, key) => (o && o[key] !== undefined) ? o[key] : '', obj);
    }

    // Register Gutenberg Block
    registerBlockType('asi/asi-images', {
        apiVersion: 3,
        title: 'ASI Images',
        icon: 'format-image',
        category: 'media',
        attributes: {
            alignment: { type: 'string', default: 'none' }
        },

        edit: function(props) {
            const { attributes, setAttributes, clientId } = props;
            const [isModalOpen, setIsModalOpen] = useState(false);
            const [searchTerm, setSearchTerm] = useState('');
            const [resultsSearch, setResultsSearch] = useState({});
            const [isSearching, setIsSearching] = useState({});

            // Translate search term once via AJAX
            async function translateSearchTerm(term) {
                if (typeof asiAjax === 'undefined' || !asiAjax.translation_en) {
                    return { translated: term, wasTranslated: false };
                }
                try {
                    const params = new URLSearchParams({
                        action: 'asi_translate_search',
                        nonce: asiAjax.nonce,
                        search: term
                    });
                    const response = await fetch(`${asiAjax.ajax_url}?${params.toString()}`);
                    const data = await response.json();
                    if (data.success && data.data) {
                        return { translated: data.data.translated, wasTranslated: data.data.was_translated };
                    }
                } catch (error) {
                    console.warn('ASI: Translation failed', error);
                }
                return { translated: term, wasTranslated: false };
            }

            // Search all configured banks
            async function searchAllBanks() {
                const postId = wp.data.select('core/editor').getCurrentPostId();
                const banks = asiAjax.choosed_banks || {};

                // Translate once before searching all banks
                const { translated, wasTranslated } = await translateSearchTerm(searchTerm);
                if (wasTranslated && translated !== searchTerm) {
                    setSearchTerm(translated);
                }

                Object.entries(banks).forEach(([key, bankName], index) => {
                    searchSingleBank(bankName, index, postId, translated, wasTranslated);
                });
            }

            // Search a single bank
            function searchSingleBank(bankName, index, postId, translatedTerm = null, skipTranslation = false) {
                const termToSearch = translatedTerm || searchTerm;
                
                // Normalize bank name
                let bankParam = bankName.toLowerCase();
                if (bankParam === 'openverse') bankParam = 'cc_search';
                
                setIsSearching(prev => ({ ...prev, [index]: true }));

                const params = new URLSearchParams({
                    action: 'asi_block_searching_images',
                    search: termToSearch,
                    bank: bankParam,
                    index: index,
                    id: postId,
                    nonce: asiAjax.nonce,
                    skip_translation: skipTranslation ? '1' : '0'
                });

                fetch(`${asiAjax.ajax_url}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response for', bankName, ':', data);
                        
                        if (data.success && data.data && data.data.results) {
                            const config = getBankConfig(bankParam);
                            const apiData = data.data.results;
                            
                            // Get the image array from the API response
                            let images = apiData[config.apiPath] || [];
                            
                            console.log('Extracted images:', images.length, 'from', config.apiPath);

                            if (images.length > 0) {
                                const imageElements = renderImageGrid(images, config, bankParam);
                                setResultsSearch(prev => ({ ...prev, [index]: imageElements }));
                            } else {
                                setResultsSearch(prev => ({ 
                                    ...prev, 
                                    [index]: wp.element.createElement('p', null, __('No results found', 'all-sources-images'))
                                }));
                            }
                        } else {
                            console.error('Invalid response format:', data);
                            setResultsSearch(prev => ({ 
                                ...prev, 
                                [index]: wp.element.createElement('p', null, __('Error loading results', 'all-sources-images'))
                            }));
                        }
                        
                        setIsSearching(prev => ({ ...prev, [index]: false }));
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        setIsSearching(prev => ({ ...prev, [index]: false }));
                        setResultsSearch(prev => ({ 
                            ...prev, 
                            [index]: wp.element.createElement('p', null, __('Network error', 'all-sources-images'))
                        }));
                    });
            }

            // Render image grid
            function renderImageGrid(images, config, bankName) {
                const licensing = asiAjax.licensing_data || '0';
                const displayImages = (licensing !== '1') ? images.slice(0, 6) : images;

                return wp.element.createElement('ul', { className: 'media-grid' },
                    displayImages.map((image, idx) => {
                        const thumbUrl = getNestedValue(image, config.imgKey);
                        const largeUrl = getNestedValue(image, config.imgLarge);
                        const altText = getNestedValue(image, config.altKey) || 'Image';
                        const caption = getNestedValue(image, config.captionKey) || '';

                        return wp.element.createElement('li', {
                            key: idx,
                            className: 'attachment mpt-attachment',
                            style: { display: 'inline-block', margin: '5px' }
                        },
                            wp.element.createElement('div', { className: 'thumbnail' },
                                wp.element.createElement('img', {
                                    src: thumbUrl,
                                    alt: altText,
                                    style: { width: '150px', height: '150px', objectFit: 'cover', cursor: 'pointer' },
                                    onClick: () => downloadAndUseImage(largeUrl, altText, caption, bankName)
                                })
                            )
                        );
                    })
                );
            }

            // Download image and insert into post
            function downloadAndUseImage(url, alt, caption, bank) {
                console.log('Downloading:', url);

                const data = {
                    action: 'asi_block_downloading_image',
                    url_image: url,
                    alt_image: alt,
                    caption_image: caption,
                    bank: bank,
                    search_term: searchTerm,
                    nonce: asiAjax.nonce
                };

                jQuery.post(asiAjax.ajax_url, data)
                    .done(response => {
                        if (response.success) {
                            // Insert image block
                            const newBlock = wp.blocks.createBlock('core/image', {
                                url: response.data.url_media,
                                alt: response.data.alt_image,
                                caption: response.data.caption_image
                            });

                            const selectedBlockClientId = wp.data.select('core/block-editor').getSelectedBlockClientId();
                            const blocks = wp.data.select('core/block-editor').getBlocks();
                            const indexBlock = blocks.findIndex(block => block.clientId === selectedBlockClientId) + 1;

                            wp.data.dispatch('core/block-editor').insertBlock(newBlock, indexBlock);
                            wp.data.dispatch('core/block-editor').removeBlock(selectedBlockClientId);

                            setIsModalOpen(false);
                        } else {
                            alert('Error downloading image');
                        }
                    })
                    .fail(() => alert('Network error'));
            }

            // Build tabs for each bank
            const banks = asiAjax.choosed_banks || {};
            const tabs = Object.entries(banks).map(([key, value], index) => ({
                name: `tab${index}`,
                title: value.charAt(0).toUpperCase() + value.slice(1),
                className: `tab-${index}`
            }));

            return wp.element.createElement('div', useBlockProps(),
                // Block toolbar
                wp.element.createElement(BlockControls, null,
                    wp.element.createElement(AlignmentToolbar, {
                        value: attributes.alignment,
                        onChange: (newAlignment) => setAttributes({ alignment: newAlignment || 'none' })
                    })
                ),

                // Button to open modal
                wp.element.createElement('div', { className: 'button-center' },
                    wp.element.createElement(Button, {
                        isPrimary: true,
                        onClick: () => setIsModalOpen(true)
                    }, __('Search for Images', 'all-sources-images'))
                ),

                // Modal with search and results
                isModalOpen && wp.element.createElement(Modal, {
                    title: 'All Sources Images',
                    onRequestClose: () => setIsModalOpen(false),
                    className: 'media-modal-content',
                    style: { maxWidth: '90%', width: '1200px' }
                },
                    // Search input
                    wp.element.createElement(TextControl, {
                        value: searchTerm,
                        onChange: setSearchTerm,
                        placeholder: __('Enter search term...', 'all-sources-images'),
                        onKeyPress: (e) => { if (e.key === 'Enter') searchAllBanks(); }
                    }),

                    // Search button
                    wp.element.createElement(Button, {
                        isPrimary: true,
                        onClick: searchAllBanks,
                        style: { marginBottom: '20px' }
                    }, __('Search', 'all-sources-images')),

                    // Tabs for each bank
                    wp.element.createElement(TabPanel, {
                        className: 'mpt-tab-panel',
                        activeClass: 'active-tab',
                        tabs: tabs
                    }, (tab) => {
                        const index = parseInt(tab.name.replace('tab', ''));
                        return wp.element.createElement('div', null,
                            isSearching[index] && wp.element.createElement('p', null, __('Searching...', 'all-sources-images')),
                            resultsSearch[index] || wp.element.createElement('p', null, __('Click Search to load images', 'all-sources-images'))
                        );
                    })
                )
            );
        },

        save: function() {
            return null;
        }
    });
})();
