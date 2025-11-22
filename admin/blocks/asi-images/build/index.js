/**
 * All Sources Images - Gutenberg Block (Rewritten Clean Version)
 * 
 * This file replaces the webpack-compiled index.js with a clean, readable version
 */

(function() {
    'use strict';

    const { registerBlockType } = wp.blocks;
    const { Button, Modal, TextControl, TabPanel, SelectControl, CheckboxControl, Spinner } = wp.components;
    const { useState, useEffect, useRef, useCallback } = wp.element;
    const { __, sprintf } = wp.i18n;
    const { useBlockProps, BlockControls, AlignmentToolbar } = wp.blockEditor;

    const PAGINATED_BANKS = new Set(['unsplash']);

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
            const [searchStatus, setSearchStatus] = useState({});
            const [timerTick, setTimerTick] = useState(Date.now());
            const [isDownloading, setIsDownloading] = useState(false);
            const defaultBanks = asiAjax.choosed_banks || {};
            const availableBanks = asiAjax.available_banks || {};
            const [sourceMode, setSourceMode] = useState('preset');
            const [customBanks, setCustomBanks] = useState(Object.values(defaultBanks));
            const customBanksKey = customBanks.join('|');
            const aiSources = (Array.isArray(asiAjax.ai_sources) ? asiAjax.ai_sources : ['dallev1', 'stability', 'replicate', 'gemini'])
                .map((slug) => (typeof slug === 'string' ? slug.toLowerCase() : slug));
            const hasActiveSearch = Object.values(searchStatus).some((status) => status && status.active);

            const masonryInstances = useRef(new Map());
            const masonryFrameRef = useRef(null);
            const sentinelObserverRef = useRef(null);

            const bankSupportsPagination = useCallback((slug) => PAGINATED_BANKS.has(slug), []);

            const requestMasonryLayout = useCallback(() => {
                if (typeof window === 'undefined') {
                    return;
                }
                if (masonryFrameRef.current) {
                    window.cancelAnimationFrame(masonryFrameRef.current);
                }
                masonryFrameRef.current = window.requestAnimationFrame(() => {
                    masonryInstances.current.forEach((instance) => {
                        if (instance && typeof instance.layout === 'function') {
                            instance.layout();
                        }
                    });
                });
            }, []);

            const destroyMasonryInstances = useCallback(() => {
                masonryInstances.current.forEach((instance) => {
                    if (instance && typeof instance.destroy === 'function') {
                        instance.destroy();
                    }
                });
                masonryInstances.current.clear();
                if (typeof document !== 'undefined') {
                    document.querySelectorAll('.asi-media-grid').forEach((grid) => {
                        grid.classList.remove('asi-masonry-ready');
                        grid.removeAttribute('data-masonry-id');
                    });
                }
            }, []);

            useEffect(() => {
                setResultsSearch({});
                setSearchStatus({});
            }, [sourceMode, customBanksKey]);

            useEffect(() => {
                if (!hasActiveSearch) {
                    return;
                }
                const intervalId = setInterval(() => {
                    setTimerTick(Date.now());
                }, 1000);
                return () => clearInterval(intervalId);
            }, [hasActiveSearch]);

            useEffect(() => {
                if (!isModalOpen) {
                    destroyMasonryInstances();
                }
            }, [isModalOpen, destroyMasonryInstances]);

            useEffect(() => {
                return () => {
                    destroyMasonryInstances();
                };
            }, [destroyMasonryInstances]);

            useEffect(() => {
                if (!isModalOpen) {
                    return;
                }
                if (typeof window === 'undefined' || typeof window.MiniMasonry === 'undefined') {
                    return;
                }

                const grids = document.querySelectorAll('.asi-media-grid');
                const activeIds = new Set();

                grids.forEach((grid) => {
                    if (!grid) {
                        return;
                    }
                    let gridId = grid.getAttribute('data-masonry-id');
                    if (!gridId) {
                        gridId = `asi-grid-${Math.random().toString(16).slice(2)}`;
                        grid.setAttribute('data-masonry-id', gridId);
                    }
                    activeIds.add(gridId);

                    let instance = masonryInstances.current.get(gridId);
                    if (!instance) {
                        instance = new window.MiniMasonry({
                            container: grid,
                            baseWidth: 260,
                            gutterX: 0,
                            gutterY: 0,
                            surroundingGutter: false,
                        });
                        masonryInstances.current.set(gridId, instance);
                    } else if (typeof instance.layout === 'function') {
                        instance.layout();
                    }
                    grid.classList.add('asi-masonry-ready');
                });

                masonryInstances.current.forEach((instance, key) => {
                    if (!activeIds.has(key)) {
                        if (instance && typeof instance.destroy === 'function') {
                            instance.destroy();
                        }
                        masonryInstances.current.delete(key);
                    }
                });

                requestMasonryLayout();
            }, [resultsSearch, isModalOpen, requestMasonryLayout]);

            // sentinel observer effect moved below fetchNextPage definition

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
            function searchSingleBank(bankName, index, postId, page = 1, append = false) {
                // Normalize bank name
                let bankParam = bankName.toLowerCase();
                if (bankParam === 'openverse') bankParam = 'cc_search';
                
                const isAiSource = aiSources.includes(bankParam);
                const supportsPagination = bankSupportsPagination(bankParam);
                const isInitialRequest = !append;
                const startedAt = Date.now();
                if (isInitialRequest) {
                    setSearchStatus(prev => ({
                        ...prev,
                        [index]: {
                            ...(prev[index] || {}),
                            active: true,
                            isAi: isAiSource,
                            startedAt,
                        }
                    }));
                }

                setResultsSearch(prev => {
                    const previous = prev[index] || {};
                    return {
                        ...prev,
                        [index]: {
                            items: isInitialRequest ? [] : (previous.items || []),
                            page: isInitialRequest ? 0 : (previous.page || 0),
                            hasMore: isInitialRequest ? supportsPagination : previous.hasMore,
                            loadingMore: true,
                            error: null,
                            bank: bankParam
                        }
                    };
                });

                const params = new URLSearchParams({
                    action: 'asi_block_searching_images',
                    search: searchTerm,
                    bank: bankParam,
                    index: index,
                    id: postId,
                    nonce: asiAjax.nonce
                });
                if (supportsPagination) {
                    params.append('page', page);
                }

                fetch(`${asiAjax.ajax_url}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response for', bankName, ':', data);
                        
                        if (data.success && data.data && data.data.images) {
                            const images = data.data.images;
                            const pagination = data.data.pagination || {};
                            const returnedPage = pagination.page ? parseInt(pagination.page, 10) : page;
                            const hasMore = supportsPagination ? !!pagination.has_more : false;

                            setResultsSearch(prev => {
                                const previous = prev[index] || {};
                                const mergedItems = append ? [ ...(previous.items || []), ...images ] : images;
                                return {
                                    ...prev,
                                    [index]: {
                                        items: mergedItems,
                                        page: returnedPage,
                                        hasMore: hasMore,
                                        loadingMore: false,
                                        error: images.length === 0 ? __('No results found', 'all-sources-images') : null,
                                        bank: bankParam
                                    }
                                };
                            });
                            requestMasonryLayout();
                        } else {
                            console.error('Invalid response format:', data);
                            setResultsSearch(prev => ({ 
                                ...prev, 
                                [index]: {
                                    ...(prev[index] || {}),
                                    loadingMore: false,
                                    error: __('Error loading results', 'all-sources-images'),
                                    items: (prev[index] && prev[index].items) ? prev[index].items : [],
                                    hasMore: false,
                                    page: prev[index] ? prev[index].page : 0,
                                    bank: bankParam
                                }
                            }));
                        }
                        
                        if (isInitialRequest) {
                            setSearchStatus(prev => ({
                                ...prev,
                                [index]: {
                                    ...(prev[index] || {}),
                                    active: false,
                                    completedAt: Date.now(),
                                }
                            }));
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        if (isInitialRequest) {
                            setSearchStatus(prev => ({
                                ...prev,
                                [index]: {
                                    ...(prev[index] || {}),
                                    active: false,
                                    completedAt: Date.now(),
                                }
                            }));
                        }
                        setResultsSearch(prev => ({ 
                            ...prev, 
                            [index]: {
                                ...(prev[index] || {}),
                                loadingMore: false,
                                error: __('Network error', 'all-sources-images'),
                                items: (prev[index] && prev[index].items) ? prev[index].items : [],
                                hasMore: false,
                                page: prev[index] ? prev[index].page : 0,
                                bank: bankParam
                            }
                        }));
                    });
            }

            const fetchNextPage = useCallback((bankName, index) => {
                if (!bankSupportsPagination(bankName)) {
                    return;
                }
                const entry = resultsSearch[index];
                if (!entry || entry.loadingMore || !entry.hasMore) {
                    return;
                }
                const postId = wp.data.select('core/editor').getCurrentPostId();
                const nextPage = (entry.page || 1) + 1;
                searchSingleBank(bankName, index, postId, nextPage, true);
            }, [resultsSearch, bankSupportsPagination]);

            useEffect(() => {
                if (sentinelObserverRef.current) {
                    sentinelObserverRef.current.disconnect();
                    sentinelObserverRef.current = null;
                }
                if (!isModalOpen || typeof IntersectionObserver === 'undefined') {
                    return;
                }
                const rootElement = document.querySelector('.asi-modal .components-modal__content');
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const bankAttr = entry.target.getAttribute('data-bank');
                            const indexAttr = entry.target.getAttribute('data-index');
                            const parsedIndex = indexAttr ? parseInt(indexAttr, 10) : Number.NaN;
                            if (bankAttr && !Number.isNaN(parsedIndex)) {
                                fetchNextPage(bankAttr, parsedIndex);
                            }
                        }
                    });
                }, {
                    root: rootElement || null,
                    rootMargin: '250px',
                    threshold: 0
                });
                sentinelObserverRef.current = observer;
                const sentinels = document.querySelectorAll('.asi-infinite-sentinel');
                sentinels.forEach(node => observer.observe(node));
                return () => {
                    if (sentinelObserverRef.current) {
                        sentinelObserverRef.current.disconnect();
                        sentinelObserverRef.current = null;
                    } else {
                        observer.disconnect();
                    }
                };
            }, [resultsSearch, isModalOpen, fetchNextPage]);

            // Image item component - MUST be defined outside map to use hooks properly
            function ImageGridItem({ image, bankName, onImageClick, onImageLoad }) {
                const thumbUrl = image.thumb || image.url;
                const largeUrl = image.url;
                const altText = image.alt || image.title || 'Image';
                const caption = image.caption || '';

                return wp.element.createElement('li', {
                    className: 'attachment mpt-attachment asi-media-item',
                    onClick: () => onImageClick(largeUrl, altText, caption, bankName)
                },
                    wp.element.createElement('div', { className: 'asi-media-card' },
                        wp.element.createElement('img', {
                            src: thumbUrl,
                            alt: altText,
                            loading: 'lazy',
                            onLoad: () => {
                                if (typeof onImageLoad === 'function') {
                                    onImageLoad();
                                }
                            }
                        }),
                        wp.element.createElement('div', { className: 'asi-media-overlay' },
                            wp.element.createElement('span', { className: 'asi-media-author' }, caption || __('Unknown author', 'all-sources-images')),
                            wp.element.createElement('button', {
                                type: 'button',
                                className: 'mpt-settings-button asi-settings-button',
                                onClick: (e) => {
                                    e.stopPropagation();
                                    console.log('Settings clicked for image by:', caption);
                                },
                                'aria-label': __('Open image settings', 'all-sources-images')
                            },
                                wp.element.createElement('svg', {
                                    width: '16',
                                    height: '16',
                                    viewBox: '0 0 16 16',
                                    fill: 'currentColor'
                                },
                                    wp.element.createElement('path', { 
                                        d: 'M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z'
                                    }),
                                    wp.element.createElement('path', {
                                        d: 'M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z'
                                    })
                                )
                            )
                        )
                    )
                );
            }

            // Render image grid
            function renderImageGrid(images, bankName, index, options = {}) {
                const hasMore = options.hasMore;
                const licensing = asiAjax.licensing_data || '0';
                const displayImages = (licensing !== '1') ? images.slice(0, 6) : images;

                return wp.element.createElement(wp.element.Fragment, null,
                    wp.element.createElement('ul', {
                        className: 'media-grid asi-media-grid',
                        role: 'list',
                        'data-bank': bankName
                    },
                        displayImages.map((image, idx) => {
                            return wp.element.createElement(ImageGridItem, {
                                key: idx,
                                image: image,
                                bankName: bankName,
                                onImageClick: downloadAndUseImage,
                                onImageLoad: requestMasonryLayout
                            });
                        })
                    ),
                    (bankSupportsPagination(bankName) && hasMore) ? wp.element.createElement('div', {
                        className: 'asi-infinite-sentinel',
                        'data-bank': bankName,
                        'data-index': index
                    }) : null
                );
            }

            // Download image and insert into post
            function downloadAndUseImage(url, alt, caption, bank) {
                if (isDownloading) {
                    return;
                }

                setIsDownloading(true);

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
                                id: response.data.id_media,
                                url: response.data.url_media,
                                alt: response.data.alt_image,
                                caption: response.data.caption_image
                            });

                            const selectedBlockClientId = wp.data.select('core/block-editor').getSelectedBlockClientId();
                            
                            // Replace the ASI block with the new image block and select it
                            wp.data.dispatch('core/block-editor').replaceBlock(selectedBlockClientId, newBlock);
                            wp.data.dispatch('core/block-editor').selectBlock(newBlock.clientId);

                            setIsModalOpen(false);
                        } else {
                            alert('Error downloading image');
                        }
                    })
                    .fail(() => alert('Network error'))
                    .always(() => setIsDownloading(false));
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
                    onRequestClose: () => {
                        setIsModalOpen(false);
                        setIsDownloading(false);
                    },
                    className: 'media-modal-content asi-modal',
                    style: { maxWidth: '95%' }
                },
                    isDownloading && wp.element.createElement('div', {
                        className: 'asi-download-overlay',
                        style: {
                            position: 'fixed',
                            top: 0,
                            left: 0,
                            right: 0,
                            bottom: 0,
                            backgroundColor: 'rgba(0, 0, 0, 0.55)',
                            display: 'flex',
                            flexDirection: 'column',
                            alignItems: 'center',
                            justifyContent: 'center',
                            zIndex: 100000,
                            color: '#fff'
                        }
                    },
                        wp.element.createElement(Spinner, {
                            style: {
                                transform: 'scale(1.5)',
                                marginBottom: '10px'
                            }
                        }),
                        wp.element.createElement('p', {
                            style: {
                                fontSize: '16px',
                                fontWeight: 'bold',
                                margin: 0
                            }
                        }, __('Downloading image…', 'all-sources-images'))
                    ),
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
                        const activeBankEntries = Object.entries(activeBanks);
                        const activeBankEntry = activeBankEntries[index] || [];
                        const bankSlug = activeBankEntry[1] || '';
                        const status = searchStatus[index];
                        const isActive = status && status.active;
                        const elapsedSeconds = (status && status.startedAt)
                            ? Math.max(0, Math.floor((timerTick - status.startedAt) / 1000))
                            : 0;
                        const baseText = status && status.isAi ? __('Making...', 'all-sources-images') : __('Searching...', 'all-sources-images');
                        const messageText = (status && status.isAi)
                            ? sprintf(__('%1$s (%2$ss)', 'all-sources-images'), baseText, elapsedSeconds)
                            : baseText;
                        const resultEntry = resultsSearch[index];
                        const hasImages = resultEntry && Array.isArray(resultEntry.items) && resultEntry.items.length > 0;
                        const errorMessage = resultEntry && resultEntry.error;
                        const showLoadMoreSpinner = resultEntry && resultEntry.loadingMore && !isActive && hasImages;

                        return wp.element.createElement('div', { 
                            style: { 
                                minHeight: '400px',
                                padding: '20px 0'
                            }
                        },
                            isActive && wp.element.createElement('p', { 
                                style: { 
                                    textAlign: 'center',
                                    padding: '40px',
                                    fontSize: '16px',
                                    color: '#666'
                                }
                            }, messageText),
                            hasImages ? renderImageGrid(resultEntry.items, bankSlug, index, { hasMore: resultEntry.hasMore }) : (errorMessage ? wp.element.createElement('p', {
                                style: {
                                    textAlign: 'center',
                                    padding: '40px',
                                    fontSize: '16px',
                                    color: '#cc0000'
                                }
                            }, errorMessage) : wp.element.createElement('p', { 
                                style: { 
                                    textAlign: 'center',
                                    padding: '40px',
                                    fontSize: '16px',
                                    color: '#999'
                                }
                            }, __('Click Search to load images', 'all-sources-images'))),
                            showLoadMoreSpinner ? wp.element.createElement('div', {
                                style: {
                                    textAlign: 'center',
                                    padding: '15px'
                                }
                            }, wp.element.createElement(Spinner, null)) : null
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
