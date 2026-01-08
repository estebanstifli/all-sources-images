/**
 * Media Picker Initialization
 * 
 * Initializes the All Sources Images explorer when the page loads
 * 
 * @package All_Sources_Images
 * @since 1.0.2
 */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    if (window.allsiImagesExplorerMount) {
        var fallbackId = 0;
        var asiData = window.allsiAjax || {};
        
        if (typeof asiData.default_post_id !== 'undefined') {
            var parsed = parseInt(asiData.default_post_id, 10);
            if (!isNaN(parsed)) {
                fallbackId = parsed;
            }
        }
        
        window.allsiImagesExplorerMount('allsi-media-picker-root', {
            openOnLoad: true,
            postId: fallbackId
        });
    }
});
