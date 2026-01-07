/**
 * ASI Media Modal - Provides functions to create ASI-enabled media frames
 * This script does NOT modify global wp.media prototypes.
 * Instead, it provides helper functions that can be called to create
 * media frames with the ASI tab included.
 */
(function($){
    'use strict';

    var mountId = 'allsi-media-modal-root';
    var tabId = (window.allsiMediaModal && window.allsiMediaModal.tabId) ? window.allsiMediaModal.tabId : 'allsi-media-tab';

    // Global tracking to prevent duplicate mounts
    window._allsiModalMountState = window._allsiModalMountState || {};

    /**
     * Ensure the mount container exists
     */
    function ensureMountExists(container) {
        if (!container || container.find('#' + mountId).length) {
            return container && container.find('#' + mountId)[0];
        }
        var wrapper = $('<div>', { id: mountId, class: 'allsi-inline-explorer' });
        container.empty().append(wrapper);
        return wrapper[0];
    }

    /**
     * Mount the ASI explorer component
     */
    function mountExplorer(postId, frameId) {
        if (!window.allsiImagesExplorerMount) {
            console.log('[ASI Modal] allsiImagesExplorerMount not available');
            return false;
        }
        
        // Check if already mounted for this frame
        if (frameId && window._allsiModalMountState[frameId]) {
            console.log('[ASI Modal] Already mounted for frame', frameId);
            return true;
        }
        
        var fallback = typeof postId === 'number' ? postId : 
            (window.allsiAjax && parseInt(window.allsiAjax.default_post_id, 10)) || 0;
        
        window.allsiImagesExplorerMount(mountId, {
            openOnLoad: true,
            postId: fallback,
            mode: 'media-modal'
        });
        
        if (frameId) {
            window._allsiModalMountState[frameId] = true;
        }
        console.log('[ASI Modal] Explorer mounted');
        return true;
    }

    /**
     * Add ASI tab to a specific frame's router
     * Call this on a frame instance to add the tab only to that frame
     */
    function addAsiTabToFrame(frame) {
        if (!frame) return;
        
        // Generate unique frame ID
        var frameId = 'allsi_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        frame._allsiFrameId = frameId;
        frame._allsiTabEnabled = true;

        // Override browseRouter for this specific frame instance
        var originalBrowseRouter = frame.browseRouter;
        frame.browseRouter = function(routerView) {
            if (typeof originalBrowseRouter === 'function') {
                originalBrowseRouter.apply(this, arguments);
            }
            var label = (window.allsiMediaModal && window.allsiMediaModal.tabLabel) || 'All Sources Images';
            routerView.set(tabId, {
                text: label,
                priority: 120
            });
        };

        // Listen for ASI tab content render
        frame.on('content:render:' + tabId, function() {
            var view = frame.content.get();
            if (!view) return;

            var container = view.$el || view;
            if (!container) return;

            if (container.addClass) {
                container.addClass('allsi-media-tab-view');
            }
            
            ensureMountExists(container);
            var postId = (window.allsiMediaModal && window.allsiMediaModal.fallbackPostId) || 0;
            mountExplorer(parseInt(postId, 10) || 0, frameId);
        });

        // Handle downloaded images
        frame.on('allsi:downloaded', function() {
            if (!window.allsiImagesExplorerGetLastDownload) return;
            
            var data = window.allsiImagesExplorerGetLastDownload();
            if (!data || !data.id_media) return;
            
            var attachmentModel = wp.media.model.Attachment.get(data.id_media);
            attachmentModel.fetch().then(function() {
                var state = frame.state();
                if (state && state.get('library')) {
                    state.get('library').add(attachmentModel);
                }
                if (state && state.get('selection')) {
                    state.get('selection').reset([attachmentModel]);
                }
            });
        });

        // Reset mount state when frame closes
        frame.on('close', function() {
            if (frameId) {
                delete window._allsiModalMountState[frameId];
            }
        });

        return frame;
    }

    /**
     * Create a new media frame with ASI tab enabled
     * Use this instead of wp.media() when you want the ASI tab
     */
    function createAsiMediaFrame(options) {
        options = options || {};
        
        // Create standard frame
        var frame = wp.media(options);
        
        // Add ASI tab to this specific frame
        addAsiTabToFrame(frame);
        
        return frame;
    }

    /**
     * Navigate to ASI tab in a frame
     */
    function focusAllsiTab(frame) {
        if (!frame) return;
        
        try {
            if (frame.content && typeof frame.content.mode === 'function') {
                frame.content.mode(tabId);
            }
            if (frame.router && frame.router.get) {
                var routerView = frame.router.get();
                if (routerView && routerView.select) {
                    routerView.select(tabId);
                }
            }
        } catch (e) {
            console.log('[ASI Modal] Could not focus tab:', e);
        }
    }

    // Listen for ASI download events globally
    window.addEventListener('allsi:image:downloaded', function() {
        if (wp && wp.media && wp.media.frame && wp.media.frame._allsiTabEnabled) {
            wp.media.frame.trigger('allsi:downloaded');
        }
    });

    // Export functions for use by other scripts
    window.allsiMediaModal = {
        createFrame: createAsiMediaFrame,
        addTabToFrame: addAsiTabToFrame,
        focusTab: focusAllsiTab,
        mountExplorer: mountExplorer,
        tabId: tabId
    };

})(jQuery);
