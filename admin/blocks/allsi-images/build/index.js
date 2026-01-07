/**
 * All Sources Images - Gutenberg Block (Rewritten Clean Version)
 * 
 * This file replaces the webpack-compiled index.js with a clean, readable version
 */

(function() {
    'use strict';

    const { registerBlockType } = wp.blocks;
        const { Button, Modal, TextControl, TabPanel, SelectControl, CheckboxControl, Spinner, TextareaControl, Notice } = wp.components;
            const { useState, useEffect, useRef, useCallback, useMemo } = wp.element;
    const { __, sprintf } = wp.i18n;
    const { useBlockProps, BlockControls, AlignmentToolbar } = wp.blockEditor;

    const PAGINATED_BANKS = new Set(['unsplash', 'pixabay', 'pexels', 'openverse', 'cc_search', 'flickr', 'google_image', 'giphy']);
    const LAST_SEARCH_STORAGE_KEY = 'allsiImagesLastSearch';

    function normalizeBankSlug(slug) {
        if (!slug || typeof slug !== 'string') {
            return '';
        }
        const lower = slug.toLowerCase();
        if (lower === 'openverse') {
            return 'cc_search';
        }
        return lower;
    }

    // Helper to get nested object value by path string
    function getNestedValue(obj, path) {
        if (!path) return '';
        return path.split('.').reduce((o, key) => (o && o[key] !== undefined) ? o[key] : '', obj);
    }

    // Register Gutenberg Block
    registerBlockType('allsi/allsi-images', {
        apiVersion: 3,
        title: 'ASI Images',
        icon: 'format-image',
        category: 'media',
        attributes: {
            alignment: { type: 'string', default: 'none' }
        },

        edit: function(props) {
            const {
                attributes = {},
                setAttributes = () => {},
                mode = 'block',
                standaloneOptions = {},
            } = props;
            const isStandaloneMode = (mode === 'standalone');
            const [isModalOpen, setIsModalOpen] = useState(isStandaloneMode && standaloneOptions.openOnLoad ? true : false);
            const [searchTerm, setSearchTerm] = useState('');
            const [resultsSearch, setResultsSearch] = useState({});
            const [searchStatus, setSearchStatus] = useState({});
            const [timerTick, setTimerTick] = useState(Date.now());
            const [isDownloading, setIsDownloading] = useState(false);
            const [downloadedImages, setDownloadedImages] = useState({});
                const [hasRenderedCachedResults, setHasRenderedCachedResults] = useState(false);
            const [notification, setNotification] = useState(null);
            const [activeTab, setActiveTab] = useState('tab0');
            const lastDownloadedRef = useRef(null);
            const skipNextResetRef = useRef(false);
            const hydrationReadyRef = useRef(false);
            const buildEmptySettingsState = () => ({
                isOpen: false,
                bank: '',
                image: null,
                extension: 'jpg',
                values: {
                    fileName: '',
                    title: '',
                    alt: '',
                    caption: '',
                },
            });
            const [settingsPanel, setSettingsPanel] = useState(() => buildEmptySettingsState());
            const defaultBanks = allsiAjax.choosed_banks || {};
            const availableBanks = allsiAjax.available_banks || {};
            const [sourceMode, setSourceMode] = useState('preset');
            const [customBanks, setCustomBanks] = useState(Object.values(defaultBanks));
            const customBanksKey = customBanks.join('|');
            const activeBanks = useMemo(() => getActiveBanks(), [sourceMode, customBanksKey]);
            const aiSources = (Array.isArray(allsiAjax.ai_sources) ? allsiAjax.ai_sources : ['dallev1', 'stability', 'replicate', 'gemini'])
                .map((slug) => (typeof slug === 'string' ? slug.toLowerCase() : slug));
            const hasActiveSearch = Object.values(searchStatus).some((status) => status && status.active);
            const hasAnyStoredResults = Object.values(resultsSearch).some((entry) => entry && Array.isArray(entry.items) && entry.items.length > 0);
            const showingCachedResults = hasRenderedCachedResults && hasAnyStoredResults && !hasActiveSearch;
            const isBlockMode = !isStandaloneMode;
            const wrapperProps = useBlockProps(isStandaloneMode ? { className: 'allsi-standalone-wrapper' } : {});
            const isExplorerVisible = isBlockMode ? isModalOpen : true;
            const tabs = useMemo(() => {
                return Object.entries(activeBanks).map(([key, value], index) => ({
                    name: `tab${index}`,
                    title: getBankLabel(value),
                    className: `tab-${index}`
                }));
            }, [activeBanks]);
            const tabsKey = useMemo(() => tabs.map((tab) => tab.name).join('|'), [tabs]);
            const hasBanks = tabs.length > 0;
            const availableBankEntries = Object.entries(availableBanks);
            const activeBankEntries = useMemo(() => Object.entries(activeBanks), [activeBanks]);

            const masonryInstances = useRef(new Map());
            const masonryFrameRef = useRef(null);
            const masonryRetryTimeoutRef = useRef(null);
            const sentinelObserverRef = useRef(null);

            const bankSupportsPagination = useCallback((slug) => PAGINATED_BANKS.has(normalizeBankSlug(slug)), []);

            const resolvePostId = useCallback(() => {
                if (isBlockMode && wp && wp.data && typeof wp.data.select === 'function') {
                    const editorStore = wp.data.select('core/editor');
                    if (editorStore && typeof editorStore.getCurrentPostId === 'function') {
                        const currentId = parseInt(editorStore.getCurrentPostId(), 10);
                        if (!Number.isNaN(currentId)) {
                            return currentId;
                        }
                    }
                }
                if (standaloneOptions && typeof standaloneOptions.postId !== 'undefined') {
                    const provided = parseInt(standaloneOptions.postId, 10);
                    if (!Number.isNaN(provided)) {
                        return provided;
                    }
                }
                if (typeof allsiAjax !== 'undefined' && typeof allsiAjax.default_post_id !== 'undefined') {
                    const fallback = parseInt(allsiAjax.default_post_id, 10);
                    if (!Number.isNaN(fallback)) {
                        return fallback;
                    }
                }
                return 0;
            }, [isBlockMode, standaloneOptions]);

            const buildImageKey = (bankSlug, image) => {
                const normalizedBank = normalizeBankSlug(bankSlug) || 'default';
                if (image && image.url) {
                    return normalizedBank + '::' + image.url;
                }
                return normalizedBank + '::' + JSON.stringify(image || {});
            };

            const markImageDownloaded = (bankSlug, image) => {
                const key = buildImageKey(bankSlug, image);
                setDownloadedImages((prev) => {
                    if (prev[key]) {
                        return prev;
                    }
                    return Object.assign({}, prev, { [key]: true });
                });
            };

            const showNotification = (status, message) => {
                setNotification({ status, message, key: Date.now(), text: message });
            };

            const persistLastSearch = useCallback((payloadResults) => {
                if (typeof window === 'undefined' || !window.localStorage) {
                    return;
                }
                try {
                    const payload = {
                        searchTerm,
                        sourceMode,
                        customBanks,
                        results: payloadResults,
                        timestamp: Date.now()
                    };
                    window.localStorage.setItem(LAST_SEARCH_STORAGE_KEY, JSON.stringify(payload));
                } catch (error) {
                    console.warn('ASI: unable to persist last search', error);
                }
            }, [searchTerm, sourceMode, customBanks]);

            const hydrateFromStorage = useCallback(() => {
                if (typeof window === 'undefined' || !window.localStorage) {
                    hydrationReadyRef.current = true;
                    return;
                }
                try {
                    const existing = window.localStorage.getItem(LAST_SEARCH_STORAGE_KEY);
                    if (!existing) {
                        hydrationReadyRef.current = true;
                        return;
                    }
                    const parsed = JSON.parse(existing);
                    if (!parsed || !parsed.searchTerm) {
                        hydrationReadyRef.current = true;
                        return;
                    }
                    // Skip multiple resets during hydration - we'll reset the flag after a delay
                    skipNextResetRef.current = true;
                    if (parsed.sourceMode === 'custom' && Array.isArray(parsed.customBanks)) {
                        setSourceMode('custom');
                        setCustomBanks(parsed.customBanks);
                    }
                    setSearchTerm(parsed.searchTerm || '');
                    if (parsed.results && typeof parsed.results === 'object') {
                        setResultsSearch(parsed.results);
                        setHasRenderedCachedResults(true);
                    }
                    // Reset the skip flag after all state updates have been processed
                    setTimeout(() => {
                        skipNextResetRef.current = false;
                    }, 100);
                } catch (error) {
                    console.warn('ASI: unable to restore last search', error);
                } finally {
                    hydrationReadyRef.current = true;
                }
            }, [setSourceMode, setCustomBanks]);

            useEffect(() => {
                hydrateFromStorage();
            }, [hydrateFromStorage]);

            useEffect(() => {
                if (!searchTerm) {
                    return;
                }
                if (!resultsSearch || Object.keys(resultsSearch).length === 0) {
                    return;
                }
                persistLastSearch(resultsSearch);
            }, [resultsSearch, searchTerm, persistLastSearch]);

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
                    document.querySelectorAll('.allsi-media-grid').forEach((grid) => {
                        grid.classList.remove('allsi-masonry-ready');
                        grid.removeAttribute('data-masonry-id');
                    });
                }
            }, []);

            useEffect(() => {
                if (!hydrationReadyRef.current) {
                    return;
                }
                if (skipNextResetRef.current) {
                    // Don't reset the flag here - let the timeout in hydrateFromStorage handle it
                    return;
                }
                setResultsSearch({});
                setSearchStatus({});
                setHasRenderedCachedResults(false);
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
                if (!tabs || tabs.length === 0) {
                    if (activeTab !== 'tab0') {
                        setActiveTab('tab0');
                    }
                    return;
                }
                const exists = tabs.some((tab) => tab.name === activeTab);
                if (!exists) {
                    setActiveTab(tabs[0].name);
                }
            }, [tabs, activeTab]);

            useEffect(() => {
                if (!isExplorerVisible) {
                    destroyMasonryInstances();
                }
            }, [isExplorerVisible, destroyMasonryInstances]);

            useEffect(() => {
                return () => {
                    destroyMasonryInstances();
                };
            }, [destroyMasonryInstances]);

            const initializeMasonry = useCallback(() => {
                if (typeof window === 'undefined' || typeof window.MiniMasonry === 'undefined') {
                    return false;
                }
                const grids = document.querySelectorAll('.allsi-media-grid');
                const gridCount = grids.length;
                if (!gridCount) {
                    return false;
                }

                const activeIds = new Set();
                grids.forEach((grid) => {
                    if (!grid) {
                        return;
                    }
                    let gridId = grid.getAttribute('data-masonry-id');
                    if (!gridId) {
                        gridId = `allsi-grid-${Math.random().toString(16).slice(2)}`;
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
                    grid.classList.add('allsi-masonry-ready');
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
                return true;
            }, [requestMasonryLayout]);

            useEffect(() => {
                if (!isExplorerVisible) {
                    return;
                }
                let attempts = 0;
                const maxAttempts = 10;

                const tryInitialize = () => {
                    if (!isExplorerVisible) {
                        return;
                    }
                    const ready = initializeMasonry();
                    if (!ready && attempts < maxAttempts) {
                        attempts += 1;
                        if (typeof window !== 'undefined') {
                            masonryRetryTimeoutRef.current = window.setTimeout(tryInitialize, 120);
                        }
                    }
                };

                tryInitialize();
                return () => {
                    attempts = maxAttempts;
                    if (masonryRetryTimeoutRef.current && typeof window !== 'undefined') {
                        window.clearTimeout(masonryRetryTimeoutRef.current);
                        masonryRetryTimeoutRef.current = null;
                    }
                };
            }, [resultsSearch, isExplorerVisible, initializeMasonry, activeTab]);

            useEffect(() => {
                if (!isExplorerVisible) {
                    return;
                }
                const timer = setTimeout(() => {
                    requestMasonryLayout();
                }, 200);
                return () => clearTimeout(timer);
            }, [isExplorerVisible, requestMasonryLayout, activeTab]);

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

            // Translate search term once via AJAX
            async function translateSearchTerm(term) {
                // Check if translation is enabled
                if (typeof allsiAjax === 'undefined' || !allsiAjax.translation_en) {
                    return { translated: term, wasTranslated: false };
                }

                try {
                    const params = new URLSearchParams({
                        action: 'allsi_translate_search',
                        nonce: allsiAjax.nonce,
                        search: term
                    });
                    const response = await fetch(`${allsiAjax.ajax_url}?${params.toString()}`);
                    const data = await response.json();
                    if (data.success && data.data) {
                        return {
                            translated: data.data.translated,
                            wasTranslated: data.data.was_translated
                        };
                    }
                } catch (error) {
                    console.warn('ASI: Translation failed, using original term', error);
                }
                return { translated: term, wasTranslated: false };
            }

            // Search all configured banks
            async function searchAllBanks() {
                const postId = resolvePostId();
                const banks = activeBanks;
                const entries = Object.entries(banks);

                if (entries.length === 0) {
                    window.alert(__('Select at least one source before searching.', 'all-sources-images'));
                    return;
                }

                setHasRenderedCachedResults(false);

                // Translate once before searching all banks
                const { translated, wasTranslated } = await translateSearchTerm(searchTerm);
                
                // Update search input if translated
                if (wasTranslated && translated !== searchTerm) {
                    setSearchTerm(translated);
                }

                entries.forEach(([key, bankName], index) => {
                    searchSingleBank(bankName, index, postId, 1, false, translated, wasTranslated);
                });
            }

            // Search a single bank
            function searchSingleBank(bankName, index, postId, page = 1, append = false, translatedTerm = null, skipTranslation = false) {
                // Use translated term if provided, otherwise use current searchTerm
                const termToSearch = translatedTerm || searchTerm;
                
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
                    action: 'allsi_block_searching_images',
                    search: termToSearch,
                    bank: bankParam,
                    index: index,
                    id: postId,
                    nonce: allsiAjax.nonce,
                    skip_translation: skipTranslation ? '1' : '0'
                });
                if (supportsPagination) {
                    params.append('page', page);
                }

                fetch(`${allsiAjax.ajax_url}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
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
                const postId = resolvePostId();
                const nextPage = (entry.page || 1) + 1;
                // On pagination, the searchTerm is already translated (if it was)
                // so we skip translation on backend
                searchSingleBank(bankName, index, postId, nextPage, true, searchTerm, true);
            }, [resultsSearch, bankSupportsPagination, resolvePostId, searchTerm]);

            useEffect(() => {
                if (sentinelObserverRef.current) {
                    sentinelObserverRef.current.disconnect();
                    sentinelObserverRef.current = null;
                }
                if (!isExplorerVisible || typeof IntersectionObserver === 'undefined') {
                    return;
                }
                const rootElement = document.querySelector('.allsi-modal .components-modal__content');
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
                const sentinels = document.querySelectorAll('.allsi-infinite-sentinel');
                sentinels.forEach(node => observer.observe(node));
                return () => {
                    if (sentinelObserverRef.current) {
                        sentinelObserverRef.current.disconnect();
                        sentinelObserverRef.current = null;
                    } else {
                        observer.disconnect();
                    }
                };
            }, [resultsSearch, isExplorerVisible, fetchNextPage, activeTab]);

            // Image item component - MUST be defined outside map to use hooks properly
            function ImageGridItem({ image, bankName, onImageClick, onImageLoad, onSettingsClick, isDownloaded }) {
                const thumbUrl = image.thumb || image.url;
                const largeUrl = image.url;
                const altText = image.alt || image.title || 'Image';
                const caption = image.caption || '';
                const cardClass = 'allsi-media-card' + (isDownloaded ? ' allsi-media-card--downloaded' : '');

                return wp.element.createElement('li', {
                    className: 'attachment mpt-attachment allsi-media-item',
                    onClick: () => onImageClick(largeUrl, altText, caption, bankName, image)
                },
                    wp.element.createElement('div', { className: cardClass },
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
                        isDownloaded ? wp.element.createElement('div', {
                            className: 'allsi-download-stamp',
                            'aria-label': __('Image already downloaded', 'all-sources-images')
                        },
                            wp.element.createElement('span', { className: 'allsi-download-stamp__icon' }, '✓'),
                            wp.element.createElement('span', { className: 'allsi-download-stamp__text' }, __('Downloaded', 'all-sources-images'))
                        ) : null,
                        wp.element.createElement('div', { className: 'allsi-media-overlay' },
                            wp.element.createElement('span', { className: 'allsi-media-author' }, caption || __('Unknown author', 'all-sources-images')),
                            wp.element.createElement('button', {
                                type: 'button',
                                className: 'mpt-settings-button allsi-settings-button',
                                onClick: (e) => {
                                    e.stopPropagation();
                                    if (typeof onSettingsClick === 'function') {
                                        onSettingsClick(image, bankName);
                                    }
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
                const isDownloadedFn = typeof options.isDownloaded === 'function' ? options.isDownloaded : () => false;

                return wp.element.createElement(wp.element.Fragment, null,
                    wp.element.createElement('ul', {
                        className: 'media-grid allsi-media-grid',
                        role: 'list',
                        'data-bank': bankName
                    },
                        images.map((image, idx) => {
                            return wp.element.createElement(ImageGridItem, {
                                key: idx,
                                image: image,
                                bankName: bankName,
                                onImageClick: downloadAndUseImage,
                                onImageLoad: requestMasonryLayout,
                                onSettingsClick: (img, bank) => openSettingsPanel(img, bank),
                                isDownloaded: isDownloadedFn(image)
                            });
                        })
                    ),
                    (bankSupportsPagination(bankName) && hasMore) ? wp.element.createElement('div', {
                        className: 'allsi-infinite-sentinel',
                        'data-bank': bankName,
                        'data-index': index
                    }) : null
                );
            }

            // Download image and insert into post
            function downloadAndUseImage(url, alt, caption, bank, imageMeta = null, overrides = {}) {
                if (isDownloading) {
                    return;
                }

                setIsDownloading(true);

                const postId = resolvePostId();

                const effectiveAlt = overrides.alt !== undefined ? overrides.alt : alt;
                const effectiveCaption = overrides.caption !== undefined ? overrides.caption : caption;
                const effectiveTitle = overrides.title !== undefined ? overrides.title : '';
                const effectiveFileName = overrides.fileName !== undefined ? overrides.fileName : '';

                const data = {
                    action: 'allsi_block_downloading_image',
                    url_image: url,
                    alt_image: effectiveAlt,
                    caption_image: effectiveCaption,
                    title_image: effectiveTitle,
                    file_name: effectiveFileName,
                    bank: bank,
                    search_term: searchTerm,
                    post_id: postId,
                    nonce: allsiAjax.nonce
                };

                jQuery.post(allsiAjax.ajax_url, data)
                    .done(response => {
                        if (response.success) {
                            lastDownloadedRef.current = response.data;
                            if (typeof window !== 'undefined' && typeof window.dispatchEvent === 'function') {
                                const event = new CustomEvent('allsi:image:downloaded', { detail: response.data });
                                window.dispatchEvent(event);
                            }
                            if (isBlockMode) {
                                const newBlock = wp.blocks.createBlock('core/image', {
                                    id: response.data.id_media,
                                    url: response.data.url_media,
                                    alt: response.data.alt_image,
                                    caption: response.data.caption_image
                                });

                                const selectedBlockClientId = wp.data.select('core/block-editor').getSelectedBlockClientId();
                                wp.data.dispatch('core/block-editor').replaceBlock(selectedBlockClientId, newBlock);
                                wp.data.dispatch('core/block-editor').selectBlock(newBlock.clientId);

                                setIsModalOpen(false);
                            } else {
                                if (imageMeta) {
                                    markImageDownloaded(bank, imageMeta);
                                }
                                showNotification('success', __('Image saved to the media library.', 'all-sources-images'));
                            }
                        } else {
                            const message = __('Error downloading image', 'all-sources-images');
                            if (isStandaloneMode) {
                                showNotification('error', message);
                            } else {
                                alert(message);
                            }
                        }
                    })
                    .fail(() => {
                        const message = __('Network error', 'all-sources-images');
                        if (isStandaloneMode) {
                            showNotification('error', message);
                        } else {
                            alert(message);
                        }
                    })
                    .always(() => setIsDownloading(false));
            }

            function deriveFilenameParts(image) {
                const fallbackBase = 'allsi-image';
                const mimeMap = {
                    'image/jpeg': 'jpg',
                    'image/jpg': 'jpg',
                    'image/png': 'png',
                    'image/webp': 'webp',
                    'image/gif': 'gif',
                };
                let extension = 'jpg';
                let base = fallbackBase;

                if (image && image.mime && mimeMap[image.mime]) {
                    extension = mimeMap[image.mime];
                }

                if (image && image.url && image.url.indexOf('data:image') !== 0) {
                    const cleanUrl = image.url.split('?')[0].split('#')[0];
                    const parts = cleanUrl.split('/');
                    const lastPart = parts.length ? parts[parts.length - 1] : '';
                    if (lastPart) {
                        const dotIndex = lastPart.lastIndexOf('.');
                        if (dotIndex > 0) {
                            base = lastPart.slice(0, dotIndex);
                            const maybeExt = lastPart.slice(dotIndex + 1).toLowerCase();
                            if (maybeExt) {
                                extension = maybeExt;
                            }
                        } else {
                            base = lastPart;
                        }
                    }
                }

                base = base || fallbackBase;
                base = base.replace(/[^a-zA-Z0-9-_]/g, '-');
                extension = extension.replace(/[^a-z0-9]/g, '') || 'jpg';

                return { base, extension };
            }

            function openSettingsPanel(image, bankName) {
                if (!image) {
                    return;
                }
                const { base, extension } = deriveFilenameParts(image);
                setSettingsPanel({
                    isOpen: true,
                    bank: bankName,
                    image: image,
                    extension: extension,
                    values: {
                        fileName: base,
                        title: image.title || '',
                        alt: image.alt || image.title || __('Image', 'all-sources-images'),
                        caption: image.caption || '',
                    },
                });
            }

            function closeSettingsPanel() {
                setSettingsPanel(buildEmptySettingsState());
            }

            function handleSettingsValueChange(field, value) {
                setSettingsPanel((prev) => ({
                    ...prev,
                    values: {
                        ...prev.values,
                        [field]: value,
                    },
                }));
            }

            function handleAddAttribution() {
                if (!settingsPanel.image) {
                    return;
                }
                const authorName = settingsPanel.image.caption || __('Unknown author', 'all-sources-images');
                const sourceUrl = settingsPanel.image.source || settingsPanel.image.url || '';
                let generated = __('Photo attribution unavailable', 'all-sources-images');
                if (sourceUrl) {
                    generated = sprintf(__('Photo by <a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>', 'all-sources-images'), sourceUrl, authorName);
                } else {
                    generated = sprintf(__('Photo by %s', 'all-sources-images'), authorName);
                }
                handleSettingsValueChange('caption', generated);
            }

            function handleSettingsDownload() {
                if (!settingsPanel.image) {
                    return;
                }
                const overrides = {
                    alt: settingsPanel.values.alt,
                    caption: settingsPanel.values.caption,
                    title: settingsPanel.values.title,
                    fileName: settingsPanel.values.fileName,
                };
                const image = settingsPanel.image;
                const bank = settingsPanel.bank;
                closeSettingsPanel();
                downloadAndUseImage(image.url, image.alt || image.title || '', image.caption || '', bank, image, overrides);
            }
            return wp.element.createElement('div', wrapperProps,
                // Block toolbar
                isBlockMode ? wp.element.createElement(BlockControls, null,
                    wp.element.createElement(AlignmentToolbar, {
                        value: attributes.alignment,
                        onChange: (newAlignment) => setAttributes({ alignment: newAlignment || 'none' })
                    })
                ) : null,

                isBlockMode ? wp.element.createElement('div', { className: 'button-center' },
                    wp.element.createElement(Button, {
                        isPrimary: true,
                        onClick: () => setIsModalOpen(true)
                    }, __('Search for Images', 'all-sources-images'))
                ) : null,

                (isBlockMode ? isModalOpen : true) && (function(){
                    const containerProps = isBlockMode ? {
                        title: 'All Sources Images',
                        onRequestClose: () => {
                            setIsModalOpen(false);
                            setIsDownloading(false);
                            closeSettingsPanel();
                            setNotification(null);
                        },
                        className: 'media-modal-content allsi-modal',
                        style: { maxWidth: '95%' }
                    } : {
                        className: 'allsi-inline-explorer'
                    };
                    return wp.element.createElement(isBlockMode ? Modal : 'div', containerProps,
                    isDownloading && wp.element.createElement('div', {
                        className: 'allsi-download-overlay',
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
                    notification ? wp.element.createElement(Notice, {
                        status: notification.status || 'info',
                        onRemove: () => setNotification(null),
                        isDismissible: true,
                        className: 'allsi-download-notice'
                    }, notification.message || notification.text || '') : null,
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
                            className: 'allsi-custom-bank-picker',
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

                        // Search button and translation indicator
                        wp.element.createElement('div', {
                            style: {
                                display: 'flex',
                                alignItems: 'center',
                                gap: '12px',
                                flexWrap: 'wrap'
                            }
                        },
                            wp.element.createElement(Button, {
                                isPrimary: true,
                                onClick: searchAllBanks,
                                disabled: !hasBanks
                            }, __('Search', 'all-sources-images')),
                            
                            // Translation indicator - show only if translation_en is enabled
                            allsiAjax.translation_en && wp.element.createElement('span', {
                                style: {
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    gap: '4px',
                                    fontSize: '12px',
                                    color: '#1e88e5',
                                    backgroundColor: '#e3f2fd',
                                    padding: '4px 8px',
                                    borderRadius: '4px',
                                    fontWeight: '500'
                                }
                            },
                                wp.element.createElement('span', {
                                    className: 'dashicons dashicons-translation',
                                    style: { fontSize: '14px', width: '14px', height: '14px' }
                                }),
                                __('Auto-translate ON', 'all-sources-images')
                            )
                        )
                    ),

                        showingCachedResults ? wp.element.createElement(Notice, {
                            status: 'info',
                            isDismissible: false,
                            className: 'allsi-cached-results-notice'
                        }, __('Showing images from your last search. Run a new search to refresh the gallery.', 'all-sources-images')) : null,

                    // Tabs for each bank
                    hasBanks ? wp.element.createElement(TabPanel, {
                        className: 'mpt-tab-panel',
                        activeClass: 'active-tab',
                        key: tabsKey,
                        tabs: tabs,
                        initialTabName: activeTab,
                        onSelect: (tabName) => {
                            setActiveTab(tabName);
                            if (typeof window !== 'undefined') {
                                window.setTimeout(() => requestMasonryLayout(), 50);
                            } else {
                                requestMasonryLayout();
                            }
                        }
                    }, (tab) => {
                        const index = parseInt(tab.name.replace('tab', ''));
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
                            hasImages ? renderImageGrid(resultEntry.items, bankSlug, index, {
                                hasMore: resultEntry.hasMore,
                                isDownloaded: (img) => {
                                    const key = buildImageKey(bankSlug, img);
                                    return !!downloadedImages[key];
                                }
                            }) : (errorMessage ? wp.element.createElement('p', {
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
                            }, hasRenderedCachedResults
                                ? (hasAnyStoredResults
                                    ? __('No saved results for this source from your last search. Run a new search to refresh it.', 'all-sources-images')
                                    : __('Your last search returned no images. Try a different keyword.', 'all-sources-images'))
                                : __('Click Search to load images', 'all-sources-images'))),
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
                    }, __('Select at least one source to preview results.', 'all-sources-images')),
                    settingsPanel.isOpen && wp.element.createElement('div', {
                        className: 'allsi-settings-backdrop'
                    },
                        wp.element.createElement('div', {
                            className: 'allsi-settings-dialog'
                        },
                            wp.element.createElement('div', {
                                className: 'allsi-settings-preview'
                            },
                                settingsPanel.image ? wp.element.createElement('img', {
                                    src: settingsPanel.image.thumb || settingsPanel.image.url,
                                    alt: settingsPanel.values.alt || __('Selected image', 'all-sources-images')
                                }) : null
                            ),
                            wp.element.createElement('div', {
                                className: 'allsi-settings-form'
                            },
                                wp.element.createElement('div', {
                                    className: 'allsi-settings-field'
                                },
                                    wp.element.createElement('label', {
                                        className: 'allsi-settings-label'
                                    }, __('Filename', 'all-sources-images')),
                                    wp.element.createElement('div', {
                                        className: 'allsi-filename-field'
                                    },
                                        wp.element.createElement('input', {
                                            type: 'text',
                                            value: settingsPanel.values.fileName,
                                            onChange: (event) => handleSettingsValueChange('fileName', event.target.value),
                                            className: 'allsi-filename-input'
                                        }),
                                        settingsPanel.extension ? wp.element.createElement('span', {
                                            className: 'allsi-filename-extension'
                                        }, `.${settingsPanel.extension}`) : null
                                    )
                                ),
                                wp.element.createElement(TextControl, {
                                    label: __('Title', 'all-sources-images'),
                                    value: settingsPanel.values.title,
                                    onChange: (value) => handleSettingsValueChange('title', value)
                                }),
                                wp.element.createElement(TextControl, {
                                    label: __('Alt Text', 'all-sources-images'),
                                    value: settingsPanel.values.alt,
                                    onChange: (value) => handleSettingsValueChange('alt', value)
                                }),
                                // Translation indicator for ALT text
                                allsiAjax.translate_alt && allsiAjax.translate_alt_lang && allsiAjax.translate_alt_lang !== 'en' && wp.element.createElement('p', {
                                    style: {
                                        marginTop: '-8px',
                                        marginBottom: '16px',
                                        fontSize: '11px',
                                        color: '#1e88e5',
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: '4px'
                                    }
                                },
                                    wp.element.createElement('span', {
                                        className: 'dashicons dashicons-translation',
                                        style: { fontSize: '12px', width: '12px', height: '12px' }
                                    }),
                                    sprintf(__('Will be translated to %s', 'all-sources-images'), allsiAjax.translate_alt_lang.toUpperCase())
                                ),
                                wp.element.createElement('div', {
                                    className: 'allsi-settings-caption-group'
                                },
                                    wp.element.createElement(TextareaControl, {
                                        label: __('Caption', 'all-sources-images'),
                                        value: settingsPanel.values.caption,
                                        onChange: (value) => handleSettingsValueChange('caption', value)
                                    }),
                                    wp.element.createElement('button', {
                                        type: 'button',
                                        className: 'allsi-attribution-link',
                                        onClick: handleAddAttribution
                                    }, __('Add Photo Attribution', 'all-sources-images'))
                                ),
                                wp.element.createElement('div', {
                                    className: 'allsi-settings-actions'
                                },
                                    wp.element.createElement(Button, {
                                        isSecondary: true,
                                        onClick: closeSettingsPanel,
                                        disabled: isDownloading
                                    }, __('Cancel', 'all-sources-images')),
                                    wp.element.createElement(Button, {
                                        isPrimary: true,
                                        onClick: handleSettingsDownload,
                                        disabled: isDownloading
                                    }, __('Download', 'all-sources-images'))
                                )
                            )
                        )
                    )
                    );
                })()
            );
        },

        save: function() {
            return null;
        }
    });

    window.allsiImagesExplorerMount = function(rootId, options) {
        if (!rootId || !document) {
            return;
        }
        const target = document.getElementById(rootId);
        if (!target || !wp || !wp.blocks) {
            return;
        }
        const blockType = wp.blocks.getBlockType('allsi/allsi-images');
        if (!blockType || typeof blockType.edit !== 'function') {
            return;
        }
        const elementProps = {
            attributes: { alignment: 'none' },
            setAttributes: () => {},
            mode: 'standalone',
            standaloneOptions: options || {},
            clientId: 'allsi-standalone'
        };
        let element = wp.element.createElement(blockType.edit, elementProps);
        if (wp.blockEditor && wp.blockEditor.BlockEditContext && wp.blockEditor.BlockEditContext.Provider) {
            element = wp.element.createElement(
                wp.blockEditor.BlockEditContext.Provider,
                { value: { clientId: 'allsi-standalone', name: 'allsi/allsi-images', attributes: elementProps.attributes, isSelected: true } },
                element
            );
        }
        if (wp.element && typeof wp.element.render === 'function') {
            wp.element.render(element, target);
        }

        window.allsiImagesExplorerGetLastDownload = function() {
            if (!lastDownloadedRef.current) {
                return null;
            }
            return Object.assign({}, lastDownloadedRef.current);
        };
    };
})();
