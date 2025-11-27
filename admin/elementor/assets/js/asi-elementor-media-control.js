/**
 * ASI Elementor Media Control
 * Custom control for Elementor that uses ASI media modal
 * Shows ONLY the ASI tab (no Upload Files or Media Library tabs)
 */
(function($){
    'use strict';

    console.log('[ASI Elementor] Script loaded');

    var tabId = 'asi-media-tab';

    // Register the control view for Elementor
    function registerControlView() {
        if (!window.elementor || !window.elementor.modules || !window.elementor.modules.controls) {
            console.log('[ASI Elementor] Elementor controls not available');
            return false;
        }

        if (!wp || !wp.media) {
            console.log('[ASI Elementor] wp.media not available');
            return false;
        }

        // Check if already registered
        if (window._asiControlRegistered) {
            return true;
        }

        var BaseData = elementor.modules.controls.BaseData;
        if (!BaseData) {
            console.log('[ASI Elementor] BaseData not found');
            return false;
        }

        console.log('[ASI Elementor] Registering control view...');

        var AsiMediaControl = BaseData.extend({
            events: {
                'click .asi-media-select': 'openFrame',
                'click .asi-media-remove': 'removeValue'
            },

            onReady: function(){
                console.log('[ASI Elementor] Control onReady');
                this.renderPreview();
                this.toggleRemove();
                
                // Store reference to this control for download callback
                this._controlId = 'asi_control_' + Date.now();
                window._asiActiveControl = this;
            },

            getValueData: function(){
                var value = this.getControlValue();
                if (!value || 'object' !== typeof value){
                    return {};
                }
                return value;
            },

            renderPreview: function(){
                var data = this.getValueData();
                var $preview = this.$el.find('.asi-media-preview');
                var $placeholder = this.$el.find('.asi-media-placeholder');

                if (data && data.url){
                    $preview.html('<img src="' + data.url + '" alt="" style="max-width:100%;height:auto;" />');
                    $preview.show();
                    $placeholder.hide();
                } else {
                    $preview.empty().hide();
                    var placeholderText = (window.asiElementorMediaControl && asiElementorMediaControl.placeholder) || 'No image selected';
                    $placeholder.text(placeholderText).show();
                }
            },

            toggleRemove: function(){
                var data = this.getValueData();
                var hasImage = data && data.id;
                this.$el.find('.asi-media-remove').toggle(!!hasImage);
            },

            openFrame: function(event){
                event.preventDefault();
                event.stopPropagation();
                var self = this;

                console.log('[ASI Elementor] Opening ASI frame...');
                
                // Set this as active control for download callback
                window._asiActiveControl = this;

                // Always destroy and recreate frame to ensure clean state
                if (this.frame) {
                    try {
                        this.frame.off();
                        if (this.frame.dispose) {
                            this.frame.dispose();
                        } else if (this.frame.remove) {
                            this.frame.remove();
                        }
                    } catch(e) {
                        console.log('[ASI Elementor] Error disposing frame:', e);
                    }
                    // Clean up mount state using stored ID
                    var frameIdToClean = this.frame._asiFrameId || this._currentFrameId;
                    if (frameIdToClean && window._asiModalMountState) {
                        delete window._asiModalMountState[frameIdToClean];
                    }
                    this.frame = null;
                    this._currentFrameId = null;
                }
                
                console.log('[ASI Elementor] Creating custom ASI-only frame...');
                this.createAsiOnlyFrame();

                this.frame.open();
                console.log('[ASI Elementor] Frame opened');

                // Focus ASI tab after a short delay
                setTimeout(function(){
                    self.focusAsiTab();
                }, 100);
            },

            createAsiOnlyFrame: function(){
                var self = this;
                
                // Create a custom frame that only has the ASI tab
                // We extend MediaFrame.Select but override the router to only show ASI
                var AsiOnlyFrame = wp.media.view.MediaFrame.Select.extend({
                    initialize: function() {
                        wp.media.view.MediaFrame.Select.prototype.initialize.apply(this, arguments);
                        
                        // Generate unique frame ID
                        this._asiFrameId = 'elementor_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                        this._asiTabEnabled = true;
                        
                        // Initialize global mount state
                        window._asiModalMountState = window._asiModalMountState || {};
                    },
                    
                    // Override to show ONLY the ASI tab
                    browseRouter: function(routerView) {
                        var label = (window.asiMediaModal && window.asiMediaModal.tabLabel) || 'All Sources Images';
                        // Only add ASI tab - no Upload or Media Library
                        routerView.set({
                            'asi-media-tab': {
                                text: label,
                                priority: 10
                            }
                        });
                    },
                    
                    // Override to default to ASI tab
                    bindHandlers: function() {
                        wp.media.view.MediaFrame.Select.prototype.bindHandlers.apply(this, arguments);
                        
                        var frame = this;
                        
                        // Handle ASI tab content - create custom content view
                        this.on('content:create:asi-media-tab', function(){
                            console.log('[ASI Elementor] Creating ASI tab content view');
                            
                            // Create a custom content view
                            var AsiContentView = wp.media.View.extend({
                                className: 'asi-media-tab-content',
                                
                                initialize: function() {
                                    this._asiFrame = frame;
                                },
                                
                                render: function() {
                                    console.log('[ASI Elementor] Rendering ASI content view');
                                    
                                    // Clear any previous content
                                    this.$el.empty();
                                    
                                    // Create mount container
                                    var mountId = 'asi-media-modal-root-' + frame._asiFrameId.replace(/[^a-z0-9]/gi, '');
                                    var wrapper = $('<div>', { 
                                        id: mountId, 
                                        class: 'asi-inline-explorer',
                                        css: {
                                            width: '100%',
                                            height: '100%',
                                            minHeight: '400px',
                                            overflow: 'auto'
                                        }
                                    });
                                    this.$el.append(wrapper);
                                    
                                    // Mount ASI explorer after a short delay to ensure DOM is ready
                                    var self = this;
                                    setTimeout(function(){
                                        self.mountExplorer(mountId);
                                    }, 50);
                                    
                                    return this;
                                },
                                
                                mountExplorer: function(mountId) {
                                    console.log('[ASI Elementor] Mounting explorer to:', mountId);
                                    
                                    // Check if already mounted
                                    if (window._asiModalMountState && window._asiModalMountState[frame._asiFrameId]) {
                                        console.log('[ASI Elementor] Already mounted, skipping');
                                        return;
                                    }
                                    
                                    // Mount ASI explorer
                                    if (window.ASIImagesExplorerMount) {
                                        var postId = (window.asiMediaModal && window.asiMediaModal.fallbackPostId) || 0;
                                        try {
                                            window.ASIImagesExplorerMount(mountId, {
                                                openOnLoad: true,
                                                postId: postId,
                                                mode: 'elementor'
                                            });
                                            window._asiModalMountState = window._asiModalMountState || {};
                                            window._asiModalMountState[frame._asiFrameId] = true;
                                            console.log('[ASI Elementor] Explorer mounted successfully');
                                        } catch(e) {
                                            console.error('[ASI Elementor] Error mounting explorer:', e);
                                            this.$el.find('#' + mountId).html('<p style="padding:20px;text-align:center;color:red;">Error loading ASI Explorer: ' + e.message + '</p>');
                                        }
                                    } else {
                                        console.log('[ASI Elementor] ASIImagesExplorerMount not available');
                                        this.$el.find('#' + mountId).html('<p style="padding:20px;text-align:center;">ASI Explorer not available. Please reload the page.</p>');
                                    }
                                }
                            });
                            
                            // Set the content view
                            frame.content.set(new AsiContentView());
                        });
                        
                        // Reset mount state on close
                        this.on('close', function(){
                            if (frame._asiFrameId) {
                                delete window._asiModalMountState[frame._asiFrameId];
                            }
                            console.log('[ASI Elementor] Frame closed');
                        });
                    }
                });
                
                // Create frame instance
                this.frame = new AsiOnlyFrame({
                    title: (window.asiElementorMediaControl && asiElementorMediaControl.chooseLabel) || 'All Sources Images',
                    multiple: false,
                    library: {
                        type: 'image'
                    },
                    button: {
                        text: 'Select'
                    }
                });
                
                // Store frame ID reference for cleanup
                this._currentFrameId = this.frame._asiFrameId;
                
                // Listen for image download event
                this.frame.on('asi:downloaded', function(data){
                    self.onImageDownloaded(data);
                });
                
                console.log('[ASI Elementor] Custom frame created with ID:', this.frame._asiFrameId);
            },

            focusAsiTab: function(){
                if (!this.frame) return;
                
                try {
                    if (this.frame.content && typeof this.frame.content.mode === 'function') {
                        this.frame.content.mode(tabId);
                    }
                } catch (e) {
                    console.log('[ASI Elementor] Could not focus tab:', e);
                }
            },

            // Called when an image is downloaded from ASI
            onImageDownloaded: function(data){
                console.log('[ASI Elementor] Image downloaded:', data);
                
                if (!data) return;
                
                // Get the downloaded image data
                var imageData = data.detail || data;
                
                if (!imageData.id_media && !imageData.url_media) {
                    console.log('[ASI Elementor] Invalid image data');
                    return;
                }
                
                // Set the value in the control
                var value = {
                    id: imageData.id_media,
                    url: imageData.url_media,
                    alt: imageData.alt_image || ''
                };
                
                this.setValue(value);
                this.renderPreview();
                this.toggleRemove();
                
                // Close the frame
                if (this.frame) {
                    this.frame.close();
                }
                
                console.log('[ASI Elementor] Widget updated with new image');
            },

            removeValue: function(event){
                event.preventDefault();
                this.setValue('');
                this.renderPreview();
                this.toggleRemove();
            },
            
            onDestroy: function(){
                if (window._asiActiveControl === this) {
                    window._asiActiveControl = null;
                }
            }
        });

        elementor.addControlView('asi_media', AsiMediaControl);
        window._asiControlRegistered = true;
        console.log('[ASI Elementor] Control view registered: asi_media');
        return true;
    }

    // Listen for ASI download events and forward to active control
    window.addEventListener('asi:image:downloaded', function(event){
        console.log('[ASI Elementor] Download event received:', event.detail);
        
        // Forward to active control
        if (window._asiActiveControl && window._asiActiveControl.frame) {
            window._asiActiveControl.onImageDownloaded(event);
        }
        
        // Also trigger on wp.media.frame if it has ASI enabled
        if (wp && wp.media && wp.media.frame && wp.media.frame._asiTabEnabled) {
            wp.media.frame.trigger('asi:downloaded', event.detail);
        }
    });

    // Try to register control on elementor:init
    $(window).on('elementor:init', function(){
        console.log('[ASI Elementor] elementor:init event fired');
        registerControlView();
    });

    // Also try on document ready as fallback
    $(document).ready(function(){
        console.log('[ASI Elementor] document ready');
        setTimeout(function(){
            registerControlView();
        }, 500);
    });

})(jQuery);
