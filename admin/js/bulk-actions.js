/**
 * Bulk Actions Menu Enhancement
 * 
 * Adds "Generate Images (ASI)" option to the bulk actions dropdown
 * 
 * @package All_Sources_Images
 * @since 1.0.2
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Get the translated text from localized data
        var optionText = (window.allsiBulkActions && window.allsiBulkActions.optionText) 
            ? window.allsiBulkActions.optionText 
            : 'Generate Images (ASI)';
        
        // Add the option before the last item in bulk action dropdowns
        $('select[name^="action"] option:last-child').before(
            '<option value="bulk_regenerate_thumbnails">' + optionText + '</option>'
        );
    });
})(jQuery);
