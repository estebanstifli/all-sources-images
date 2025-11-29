/**
 * ASI Elementor Widget - JavaScript
 *
 * @package All_Sources_Images
 */

console.log('=== ASI Elementor Widget JS File Loaded ===');

(function($) {
    'use strict';

    console.log('ASI Elementor: Executing main function, jQuery version:', $.fn.jquery);

    // Check if asiAjax is available
    if (typeof asiAjax === 'undefined') {
        console.error('ASI Elementor: asiAjax is not defined. Plugin may not work correctly.');
        console.log('ASI Elementor: Available global objects:', Object.keys(window));
        return;
    }

    console.log('ASI Elementor Widget initialized with asiAjax:', asiAjax);

    /**
     * ASI Image Browser for Elementor
     */
    var ASIElementorBrowser = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;

            console.log('ASI Elementor: Binding events');

            // Handle search trigger button click
            $(document).on('click', '.asi-search-trigger', function(e) {
                e.preventDefault();
                console.log('ASI Elementor: Search trigger clicked', this);
                var $wrapper = $(this).closest('.asi-elementor-image-wrapper');
                console.log('ASI Elementor: Wrapper found', $wrapper.length);
                self.openBrowser($wrapper);
            });

            // Handle Elementor custom event (from button control)
            if (typeof elementor !== 'undefined') {
                console.log('ASI Elementor: Elementor editor detected');
                elementor.channels.editor.on('asi:openBrowser', function(view) {
                    console.log('ASI Elementor: Custom event triggered', view);
                    var $wrapper = view.$el.find('.asi-elementor-image-wrapper');
                    self.openBrowser($wrapper);
                });
            }
        },

        /**
         * Open image browser modal
         */
        openBrowser: function($wrapper) {
            console.log('ASI Elementor: openBrowser called', $wrapper);
            
            var self = this;
            var widgetId = $wrapper.data('widget-id');
            var searchTerm = $wrapper.data('search-term') || '';
            var imageBank = $wrapper.data('image-bank') || 'pixabay';

            console.log('ASI Elementor: Widget data', {widgetId, searchTerm, imageBank});

            // Get post ID (Elementor context)
            var postId = 0;
            if (typeof elementor !== 'undefined' && elementor.config && elementor.config.initial_document) {
                postId = elementor.config.initial_document.id;
            } else if (typeof asiAjax !== 'undefined' && asiAjax.default_post_id) {
                postId = asiAjax.default_post_id;
            }

            // Create modal HTML
            var modalHtml = self.createModalHTML(searchTerm, imageBank);
            
            console.log('ASI Elementor: Modal HTML created');
            
            // Insert modal
            var $modal = $(modalHtml);
            $wrapper.find('.asi-modal-container').html($modal);
            
            // Show modal
            $modal.fadeIn(200);

            // Bind modal events
            self.bindModalEvents($modal, $wrapper, postId);

            // If search term exists, auto-search
            if (searchTerm) {
                self.performSearch($modal, searchTerm, imageBank, postId);
            }
        },

        /**
         * Create modal HTML
         */
        createModalHTML: function(defaultSearch, defaultBank) {
            var banks = {
                'pixabay': 'Pixabay',
                'unsplash': 'Unsplash',
                'pexels': 'Pexels',
                'openverse': 'Openverse',
                'flickr': 'Flickr',
                'google_image': 'Google Images',
                'giphy': 'Giphy',
                'youtube': 'YouTube',
                'dallev1': 'DALL·E',
                'stability': 'Stable Diffusion',
                'gemini': 'Gemini',
                'replicate': 'Replicate'
            };

            var bankOptions = '';
            for (var key in banks) {
                var selected = (key === defaultBank) ? ' selected' : '';
                bankOptions += '<option value="' + key + '"' + selected + '>' + banks[key] + '</option>';
            }

            // Translation indicator (only shown if asiAjax.translation_en is true)
            var translationIndicator = '';
            if (typeof asiAjax !== 'undefined' && asiAjax.translation_en) {
                translationIndicator = `
                    <span class="asi-translation-indicator" style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: #1e88e5; background-color: #e3f2fd; padding: 4px 8px; border-radius: 4px; font-weight: 500; margin-left: 10px;">
                        <span class="dashicons dashicons-translation" style="font-size: 14px; width: 14px; height: 14px;"></span>
                        Auto-translate ON
                    </span>
                `;
            }

            return `
                <div class="asi-modal-overlay">
                    <div class="asi-modal-content">
                        <div class="asi-modal-header">
                            <h3>Search Images</h3>
                            <button type="button" class="asi-modal-close">&times;</button>
                        </div>
                        <div class="asi-modal-body">
                            <div class="asi-search-controls">
                                <input type="text" class="asi-search-input" placeholder="Enter search term..." value="${defaultSearch}">
                                <select class="asi-bank-select">
                                    ${bankOptions}
                                </select>
                                <button type="button" class="asi-search-button">Search</button>
                                ${translationIndicator}
                            </div>
                            <div class="asi-results-container">
                                <div class="asi-image-grid"></div>
                            </div>
                            <div class="asi-pagination" style="display:none;"></div>
                        </div>
                    </div>
                </div>
            `;
        },

        /**
         * Bind modal events
         */
        bindModalEvents: function($modal, $wrapper, postId) {
            var self = this;

            // Close modal
            $modal.find('.asi-modal-close, .asi-modal-overlay').on('click', function(e) {
                if (e.target === this) {
                    $modal.fadeOut(200, function() {
                        $(this).remove();
                    });
                }
            });

            // Prevent closing when clicking inside modal
            $modal.find('.asi-modal-content').on('click', function(e) {
                e.stopPropagation();
            });

            // Search button
            $modal.find('.asi-search-button').on('click', function() {
                var searchTerm = $modal.find('.asi-search-input').val();
                var bank = $modal.find('.asi-bank-select').val();
                self.performSearch($modal, searchTerm, bank, postId);
            });

            // Enter key to search
            $modal.find('.asi-search-input').on('keypress', function(e) {
                if (e.which === 13) {
                    $modal.find('.asi-search-button').click();
                }
            });

            // Image selection
            $modal.on('click', '.asi-image-item', function() {
                var imageUrl = $(this).data('url');
                var imageAlt = $(this).data('alt') || '';
                var imageTitle = $(this).data('title') || '';
                var imageBank = $(this).data('bank') || '';
                
                self.selectImage($wrapper, imageUrl, imageAlt, imageTitle, imageBank, postId, $modal);
            });
        },

        /**
         * Perform search via AJAX
         */
        performSearch: function($modal, searchTerm, bank, postId, page) {
            var self = this;
            page = page || 1;

            if (!searchTerm) {
                alert('Please enter a search term');
                return;
            }

            // Check if asiAjax is available (from main plugin)
            if (typeof asiAjax === 'undefined') {
                alert('ASI plugin not properly loaded. Please refresh the page.');
                return;
            }

            // Show loading
            $modal.find('.asi-results-container').html('<div class="asi-loading">Loading images...</div>');
            $modal.find('.asi-pagination').hide();

            // AJAX call to existing endpoint
            $.ajax({
                url: asiAjax.ajax_url,
                type: 'GET',
                data: {
                    action: 'asi_block_searching_images',
                    nonce: asiAjax.nonce,
                    search: searchTerm,
                    bank: bank,
                    id: postId,
                    index: 0,
                    page: page
                },
                success: function(response) {
                    if (response.success && response.data) {
                        self.renderResults($modal, response.data, bank, searchTerm, postId);
                    } else {
                        $modal.find('.asi-results-container').html(
                            '<div class="asi-no-results">No images found. Try a different search term.</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error('ASI Search Error:', error);
                    $modal.find('.asi-results-container').html(
                        '<div class="asi-no-results">Error loading images. Please try again.</div>'
                    );
                }
            });
        },

        /**
         * Render search results
         */
        renderResults: function($modal, data, bank, searchTerm, postId) {
            var self = this;
            var $grid = $modal.find('.asi-image-grid');
            $grid.empty();

            // Extract images from response based on bank
            var images = this.extractImages(data, bank);

            if (!images || images.length === 0) {
                $modal.find('.asi-results-container').html(
                    '<div class="asi-no-results">No images found for this search.</div>'
                );
                return;
            }

            // Render images
            images.forEach(function(image) {
                var $item = $('<div class="asi-image-item"></div>')
                    .data('url', image.url)
                    .data('alt', image.alt)
                    .data('title', image.title)
                    .data('bank', bank);

                var $img = $('<img>')
                    .attr('src', image.preview || image.url)
                    .attr('alt', image.alt)
                    .attr('loading', 'lazy');

                $item.append($img);
                $grid.append($item);
            });

            // Handle pagination if available
            if (data.pagination) {
                self.renderPagination($modal, data.pagination, searchTerm, bank, postId);
            }
        },

        /**
         * Extract images from API response
         */
        extractImages: function(data, bank) {
            // Normalized response from backend
            if (data.images && Array.isArray(data.images)) {
                return data.images;
            }

            // Fallback to raw results (legacy format)
            var images = [];
            
            if (data.results) {
                // Different banks have different structures
                var bankData = data.results[bank] || data.results;
                
                if (Array.isArray(bankData)) {
                    bankData.forEach(function(item) {
                        images.push({
                            url: item.url || item.largeImageURL || item.urls?.regular || item.src?.large || '',
                            preview: item.previewURL || item.urls?.small || item.src?.medium || item.url || '',
                            alt: item.tags || item.alt_description || item.title || '',
                            title: item.title || item.tags || ''
                        });
                    });
                }
            }

            return images;
        },

        /**
         * Render pagination
         */
        renderPagination: function($modal, pagination, searchTerm, bank, postId) {
            var self = this;
            var $pagination = $modal.find('.asi-pagination');
            
            if (!pagination.has_more && pagination.page === 1) {
                $pagination.hide();
                return;
            }

            $pagination.empty().show();

            // Previous button
            if (pagination.page > 1) {
                $('<button>Previous</button>')
                    .on('click', function() {
                        self.performSearch($modal, searchTerm, bank, postId, pagination.page - 1);
                    })
                    .appendTo($pagination);
            }

            // Current page
            $('<span class="current-page">Page ' + pagination.page + '</span>')
                .appendTo($pagination);

            // Next button
            if (pagination.has_more) {
                $('<button>Next</button>')
                    .on('click', function() {
                        self.performSearch($modal, searchTerm, bank, postId, pagination.page + 1);
                    })
                    .appendTo($pagination);
            }
        },

        /**
         * Select image and update widget
         */
        selectImage: function($wrapper, imageUrl, imageAlt, imageTitle, imageBank, postId, $modal) {
            var self = this;
            var widgetId = $wrapper.data('widget-id');

            // Download image to WordPress media library via AJAX
            $.ajax({
                url: asiAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'asi_block_downloading_image',
                    nonce: asiAjax.nonce,
                    url_image: imageUrl,
                    alt_image: imageAlt,
                    title_image: imageTitle,
                    bank: imageBank,
                    post_id: postId,
                    search_term: $wrapper.data('search-term') || 'image'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Update Elementor widget settings
                        if (typeof elementor !== 'undefined') {
                            var view = elementor.getPreviewView();
                            var widgetView = view.children.find(function(child) {
                                return child.model.get('id') === widgetId;
                            });

                            if (widgetView) {
                                widgetView.model.setSetting('selected_image_url', response.data.url);
                                widgetView.model.setSetting('selected_image_alt', imageAlt);
                                widgetView.render();
                            }
                        }

                        // Update preview immediately
                        $wrapper.find('.asi-elementor-image').html(
                            '<img src="' + response.data.url + '" alt="' + imageAlt + '" loading="lazy" />'
                        );

                        // Close modal
                        $modal.fadeOut(200, function() {
                            $(this).remove();
                        });
                    } else {
                        alert('Error downloading image. Please try again.');
                    }
                },
                error: function() {
                    alert('Error downloading image. Please try again.');
                }
            });
        }
    };

    // Initialize when document ready
    $(document).ready(function() {
        console.log('ASI Elementor: Document ready, initializing...');
        ASIElementorBrowser.init();
    });

    // Also initialize when Elementor editor loads
    if (typeof elementor !== 'undefined') {
        console.log('ASI Elementor: Elementor detected, binding to preview:loaded');
        elementor.on('preview:loaded', function() {
            console.log('ASI Elementor: Preview loaded, re-initializing...');
            ASIElementorBrowser.init();
        });
    }

})(jQuery);
