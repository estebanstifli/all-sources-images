(function($){
    'use strict';

    const mountId = 'asi-media-modal-root';
    const tabId = (window.asiMediaModal && window.asiMediaModal.tabId) ? window.asiMediaModal.tabId : 'asi-media-tab';

    function ensureMountExists(container){
        if (!container || container.find('#' + mountId).length){
            return container && container.find('#' + mountId)[0];
        }
        const wrapper = $('<div>', { id: mountId, class: 'asi-inline-explorer' });
        container.empty().append(wrapper);
        return wrapper[0];
    }

    function mountExplorer(postId){
        if (!window.ASIImagesExplorerMount){
            return;
        }
        const fallback = typeof postId === 'number' ? postId : (asiAjax && parseInt(asiAjax.default_post_id,10)) || 0;
        window.ASIImagesExplorerMount(mountId, {
            openOnLoad: true,
            postId: fallback,
            mode: 'media-modal'
        });
    }

    function registerFrameEvents(frame){
        if (!frame || frame._asiTabInitialized){
            return;
        }

        frame.on('content:render:' + tabId, function(){
            const view = frame.content.get();
            if (!view){
                return;
            }
            const container = view.$el || view;
            if (!container){
                return;
            }
            (container.addClass || function(){ }).call(container, 'asi-media-tab-view');
            ensureMountExists(container);
            mountExplorer(parseInt(asiMediaModal.fallbackPostId, 10) || 0);
        });

        frame.on('asi:downloaded', function(){
            if (!window.ASIImagesExplorerGetLastDownload){
                return;
            }
            const data = window.ASIImagesExplorerGetLastDownload();
            if (!data || !data.id_media){
                return;
            }
            const attachmentModel = wp.media.model.Attachment.get(data.id_media);
            attachmentModel.fetch().then(function(){
                const state = frame.state();
                if (state && state.get('library')){
                    state.get('library').add(attachmentModel);
                }
                if (state && state.get('selection')){
                    state.get('selection').reset([attachmentModel]);
                }
            });
        });

        frame._asiTabInitialized = true;
    }

    function extendMediaFramePrototype(){
        if (!wp || !wp.media || !wp.media.view || !wp.media.view.MediaFrame){
            return;
        }

        const proto = wp.media.view.MediaFrame.Post && wp.media.view.MediaFrame.Post.prototype;
        if (!proto){
            return;
        }

        const originalInitialize = proto.initialize;
        proto.initialize = function(){
            originalInitialize.apply(this, arguments);
            registerFrameEvents(this);
            this.on('open', function(){
                if (this.content && typeof this.content.mode === 'function') {
                    this.content.mode('browse');
                }
                if (this.router) {
                    if (typeof this.router.navigate === 'function') {
                        this.router.navigate('browse');
                    }
                    if (this.router.active && typeof this.router.active.set === 'function') {
                        this.router.active.set('browse');
                    }
                }
            });
            this.on('close', function(){
                if (this.router && this.router.active && typeof this.router.active.set === 'function') {
                    this.router.active.set('browse');
                }
            });
        };

        const originalRouter = proto.browseRouter;
        proto.browseRouter = function(routerView){
            if (typeof originalRouter === 'function') {
                originalRouter.apply(this, arguments);
            }
            routerView.set(tabId, {
                text: asiMediaModal.tabLabel,
                priority: 120
            });
        };
    }

    extendMediaFramePrototype();

    window.addEventListener('asi:image:downloaded', function(){
        if (wp && wp.media && wp.media.frame){
            wp.media.frame.trigger('asi:downloaded');
        }
    });

})(jQuery);
