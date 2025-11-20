/**
 * All Sources Images - Gutenberg Block (Rewritten Clean Version)
 * 
 * This file replaces the webpack-compiled index.js with a clean, readable version
 */

(function() {
    'use strict';

    const { registerBlockType } = wp.blocks;
    const { Button, Modal, TextControl, TabPanel, SelectControl, CheckboxControl } = wp.components;
    const { useState, useEffect } = wp.element;
    const { __ } = wp.i18n;
    const { useBlockProps, BlockControls, AlignmentToolbar } = wp.blockEditor;

    // Universal bank configuration - no longer needed since backend normalizes
    function getBankConfig(bank) {
        return {
            imgKey: 'thumb',
            imgLarge: 'url', 
            altKey: 'alt',
            captionKey: 'caption'
        };
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
            const { attributes, setAttributes } = props;
            const [isModalOpen, setIsModalOpen] = useState(false);
            const [searchTerm, setSearchTerm] = useState('');
            const [resultsSearch, setResultsSearch] = useState({});
            const [isSearching, setIsSearching] = useState({});
            const defaultBanks = asiAjax.choosed_banks || {};
            const availableBanks = asiAjax.available_banks || {};
            const [sourceMode, setSourceMode] = useState('preset');
            const [customBanks, setCustomBanks] = useState(Object.values(defaultBanks));
            const customBanksKey = customBanks.join('|');

            useEffect(() => {
                setResultsSearch({});
                setIsSearching({});
            }, [sourceMode, customBanksKey]);

            function getBankLabel(bankSlug) {
                if (!bankSlug || typeof bankSlug !== 'string') {
                    return '';
                }
                if (availableBanks[bankSlug]) {
                    return availableBanks[bankSlug];
                }
                const formatted = bankSlug.replace(/_/g, ' ');
                return formatted.charAt(0).toUpperCase() + formatted.slice(1);
            }

            function getActiveBanks() {
                if (sourceMode === 'preset') {
                    return defaultBanks;
                }
                if (!customBanks || customBanks.length === 0) {
                    return {};
                }
                const uniqueBanks = customBanks.filter((slug, index, arr) => slug && arr.indexOf(slug) === index);
                return uniqueBanks.reduce((acc, slug, index) => {
                    acc[`custom-${index}`] = slug;
                    return acc;
                }, {});
            }

            function toggleCustomBank(bankSlug) {
                setCustomBanks(prev => {
                    if (!bankSlug) {
                        return prev;
                    }
                    if (prev.includes(bankSlug)) {
                        return prev.filter(slug => slug !== bankSlug);
                    }
                    return [...prev, bankSlug];
                });
            }

            // Search all configured banks
            function searchAllBanks() {
                const postId = wp.data.select('core/editor').getCurrentPostId();
                const banks = getActiveBanks();
                const entries = Object.entries(banks);

                if (entries.length === 0) {
                    window.alert(__('Select at least one source before searching.', 'all-sources-images'));
                    return;
                }

                entries.forEach(([key, bankName], index) => {
                    searchSingleBank(bankName, index, postId);
                });
            }

            // Search a single bank
            function searchSingleBank(bankName, index, postId) {
                // Normalize bank name
                let bankParam = bankName.toLowerCase();
                if (bankParam === 'openverse') bankParam = 'cc_search';
                
                setIsSearching(prev => ({ ...prev, [index]: true }));

                const params = new URLSearchParams({
                    action: 'asi_block_searching_images',
                    search: searchTerm,
                    bank: bankParam,
                    index: index,
                    id: postId,
                    nonce: asiAjax.nonce
                });

                fetch(`${asiAjax.ajax_url}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response for', bankName, ':', data);
                        
                        if (data.success && data.data && data.data.images) {
                            const config = getBankConfig(bankParam);
                            const images = data.data.images;
                            
                            console.log('Extracted images:', images.length);
                            if (images.length > 0) {
                                console.log('First image:', images[0]);
                            }

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

                return wp.element.createElement('ul', { 
                    className: 'media-grid',
                    style: { 
                        listStyle: 'none',
                        padding: 0,
                        margin: 0,
                        display: 'grid',
                        gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))',
                        gap: '15px'
                    }
                },
                    displayImages.map((image, idx) => {
                        // Direct access - backend normalized the structure
                        const thumbUrl = image.thumb || image.url;
                        const largeUrl = image.url;
                        const altText = image.alt || image.title || 'Image';
                        const caption = image.caption || '';

                        return wp.element.createElement('li', {
                            key: idx,
                            className: 'attachment mpt-attachment',
                            style: { 
                                position: 'relative',
                                overflow: 'hidden',
                                borderRadius: '4px',
                                boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
                                transition: 'transform 0.2s',
                                cursor: 'pointer',
                                backgroundColor: '#f0f0f0'
                            },
                            onMouseEnter: (e) => e.currentTarget.style.transform = 'scale(1.05)',
                            onMouseLeave: (e) => e.currentTarget.style.transform = 'scale(1)',
                            onClick: () => downloadAndUseImage(largeUrl, altText, caption, bankName)
                        },
                            wp.element.createElement('div', { 
                                className: 'thumbnail',
                                style: {
                                    width: '100%',
                                    paddingBottom: '100%', // Square aspect ratio
                                    position: 'relative'
                                }
                            },
                                wp.element.createElement('img', {
                                    src: thumbUrl,
                                    alt: altText,
                                    style: { 
                                        position: 'absolute',
                                        top: 0,
                                        left: 0,
                                        width: '100%',
                                        height: '100%',
                                        objectFit: 'cover'
                                    }
                                })
                            ),
                            // Button overlay
                            wp.element.createElement('div', {
                                className: 'img-result',
                                style: {
                                    position: 'absolute',
                                    bottom: 0,
                                    left: 0,
                                    right: 0,
                                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                                    color: 'white',
                                    padding: '8px',
                                    textAlign: 'center',
                                    fontSize: '12px',
                                    fontWeight: 'bold',
                                    opacity: 0,
                                    transition: 'opacity 0.2s'
                                },
                                onMouseEnter: (e) => e.currentTarget.style.opacity = '1',
                                onMouseLeave: (e) => e.currentTarget.style.opacity = '0'
                            }, __('Use this image', 'all-sources-images'))
                        );
                    })
                );
            }

            // Download image and insert into post
            function downloadAndUseImage(url, alt, caption, bank) {
                console.log('Downloading:', url);

                const postId = wp.data.select('core/editor').getCurrentPostId();

                const data = {
                    action: 'asi_block_downloading_image',
                    url_image: url,
                    alt_image: alt,
                    caption_image: caption,
                    bank: bank,
                    search_term: searchTerm,
                    post_id: postId,
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
            const activeBanks = getActiveBanks();
            const tabs = Object.entries(activeBanks).map(([key, value], index) => ({
                name: `tab${index}`,
                title: getBankLabel(value),
                className: `tab-${index}`
            }));
            const hasBanks = tabs.length > 0;
            const availableBankEntries = Object.entries(availableBanks);

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
                    className: 'media-modal-content asi-modal',
                    style: { maxWidth: '95%' }
                },
                    // Search container
                    wp.element.createElement('div', { style: { marginBottom: '20px' } },
                        wp.element.createElement(SelectControl, {
                            label: __('Sources to use', 'all-sources-images'),
                            value: sourceMode,
                            options: [
                                { label: __('Use sources from Settings', 'all-sources-images'), value: 'preset' },
                                { label: __('Custom selection', 'all-sources-images'), value: 'custom' }
                            ],
                            onChange: (value) => setSourceMode(value),
                            style: { marginBottom: '10px' }
                        }),

                        sourceMode === 'custom' && wp.element.createElement('div', {
                            className: 'asi-custom-bank-picker',
                            style: {
                                border: '1px solid #dcdcde',
                                borderRadius: '4px',
                                padding: '15px',
                                marginBottom: '15px',
                                backgroundColor: '#fff'
                            }
                        },
                            wp.element.createElement('p', {
                                style: {
                                    marginTop: 0,
                                    color: '#555',
                                    fontSize: '13px'
                                }
                            }, __('Select the image sources you want to query.', 'all-sources-images')),
                            availableBankEntries.length > 0 ? wp.element.createElement('div', {
                                style: {
                                    display: 'grid',
                                    gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))',
                                    gap: '8px'
                                }
                            },
                                availableBankEntries.map(([slug, label]) => (
                                    wp.element.createElement(CheckboxControl, {
                                        key: slug,
                                        label: label,
                                        checked: customBanks.includes(slug),
                                        onChange: () => toggleCustomBank(slug)
                                    })
                                ))
                            ) : wp.element.createElement('p', {
                                style: {
                                    color: '#888',
                                    fontStyle: 'italic'
                                }
                            }, __('No sources available. Please configure them in the plugin settings.', 'all-sources-images'))
                        ),

                        // Search input
                        wp.element.createElement(TextControl, {
                            value: searchTerm,
                            onChange: setSearchTerm,
                            placeholder: __('Enter search term...', 'all-sources-images'),
                            onKeyPress: (e) => { if (e.key === 'Enter') searchAllBanks(); },
                            style: { marginBottom: '10px' }
                        }),

                        // Search button
                        wp.element.createElement(Button, {
                            isPrimary: true,
                            onClick: searchAllBanks,
                            disabled: !hasBanks
                        }, __('Search', 'all-sources-images'))
                    ),

                    // Tabs for each bank
                    hasBanks ? wp.element.createElement(TabPanel, {
                        className: 'mpt-tab-panel',
                        activeClass: 'active-tab',
                        tabs: tabs
                    }, (tab) => {
                        const index = parseInt(tab.name.replace('tab', ''));
                        return wp.element.createElement('div', { 
                            style: { 
                                minHeight: '400px',
                                padding: '20px 0'
                            }
                        },
                            isSearching[index] && wp.element.createElement('p', { 
                                style: { 
                                    textAlign: 'center',
                                    padding: '40px',
                                    fontSize: '16px',
                                    color: '#666'
                                }
                            }, __('Searching...', 'all-sources-images')),
                            resultsSearch[index] || wp.element.createElement('p', { 
                                style: { 
                                    textAlign: 'center',
                                    padding: '40px',
                                    fontSize: '16px',
                                    color: '#999'
                                }
                            }, __('Click Search to load images', 'all-sources-images'))
                        );
                    }) : wp.element.createElement('p', {
                        style: {
                            textAlign: 'center',
                            padding: '60px 20px',
                            fontSize: '16px',
                            color: '#777'
                        }
                    }, __('Select at least one source to preview results.', 'all-sources-images'))
                )
            );
        },

        save: function() {
            return null;
        }
    });
})();
