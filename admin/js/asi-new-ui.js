/**
 * All Sources Images - New UI Scripts
 * 
 * Handles UI interactions for the new admin pages
 * 
 * @package All_Sources_Images
 * @since 6.2.0
 */

/* global jQuery, asiNewUI */
(function($) {
    'use strict';

    $(document).ready(function() {

        // =============================================
        // Proxy Settings Toggle (new-settings-proxy.php)
        // =============================================
        $('#enable_proxy').on('change', function() {
            if ($(this).is(':checked')) {
                $('#asi-proxy-settings').slideDown();
            } else {
                $('#asi-proxy-settings').slideUp();
            }
        });

        // =============================================
        // Others Settings (new-settings-others.php)
        // =============================================
        
        // Toggle Google API row and Source Language row based on translation settings
        function toggleGoogleApiRow() {
            var translationEN = $('#translation_EN').is(':checked');
            var translateAlt = $('#translate_alt').is(':checked');
            
            if (translationEN || translateAlt) {
                $('.asi-google-api-row').slideDown();
            } else {
                $('.asi-google-api-row').slideUp();
            }
            
            // Show/hide source language row based on translation_EN only
            if (translationEN) {
                $('.asi-source-lang-row').slideDown();
            } else {
                $('.asi-source-lang-row').slideUp();
            }
        }
        
        $('#translation_EN, #translate_alt').on('change', toggleGoogleApiRow);
        
        // Toggle password visibility for Google Translate API Key
        var passwordSelector = '#password-google-translate';
        var password = document.querySelector(passwordSelector + " input");
        var togglePassword = document.querySelector(passwordSelector + " #togglePassword");
        
        if (togglePassword && password) {
            togglePassword.addEventListener("click", function() {
                var type = password.getAttribute("type") === "password" ? "text" : "password";
                password.setAttribute("type", type);
                this.classList.toggle("close-eye");
            });
        }

        // =============================================
        // Post Processing Toggle (new-automatic-post-processing.php)
        // =============================================
        
        // Toggle ALT options
        $('#enable_alt').on('change', function() {
            if ($(this).is(':checked')) {
                $('.show_alt').show();
            } else {
                $('.show_alt').hide();
            }
        });

        // Toggle Caption options
        $('#enable_caption').on('change', function() {
            if ($(this).is(':checked')) {
                $('.show_caption').show();
            } else {
                $('.show_caption').hide();
            }
        });

        // =============================================
        // Image Placement Blocks (new-automatic-image-placement.php)
        // =============================================
        
        // Only run if we have the image placement data
        if (typeof asiNewUI !== 'undefined' && asiNewUI.imagePlacement) {
            
            var blockIndex = asiNewUI.imagePlacement.blockIndex || 1;
            var helpTexts = asiNewUI.imagePlacement.helpTexts || {};
            
            // Toggle inline content fields when radio changes
            $(document).on('change', '.image_location input[type="radio"]', function() {
                var $block = $(this).closest('.image-placement-block');
                var $inlineFields = $block.find('.inline-content-fields');
                
                if ($(this).val() === 'custom') {
                    $inlineFields.addClass('visible');
                } else {
                    $inlineFields.removeClass('visible');
                }
            });
            
            // Show/hide based_on fields and update help text
            $(document).on('change', '.based-on-select', function() {
                var $block = $(this).closest('.image-placement-block');
                var value = $(this).val();
                
                // Hide all based-on fields
                $block.find('.based-on-fields').removeClass('visible');
                
                // Show relevant fields based on selection
                if (value === 'text_analyser' || value === 'text_analyser_previous_paragraph' || value === 'text_analyser_next_paragraph') {
                    $block.find('.section_text_analyser').addClass('visible');
                } else if (value === 'tags') {
                    $block.find('.section_tags').addClass('visible');
                } else if (value === 'categories') {
                    $block.find('.section_categories').addClass('visible');
                } else if (value === 'custom_field') {
                    $block.find('.section_custom_field').addClass('visible');
                } else if (value === 'custom_request') {
                    $block.find('.section_custom_request').addClass('visible');
                } else if (value === 'openai_extractor') {
                    $block.find('.section_openai_extractor').addClass('visible');
                } else if (value === 'ai_image_prompt') {
                    $block.find('.section_ai_image_prompt').addClass('visible');
                }
                
                // Update help text
                var $helpText = $block.find('.based-on-help-text');
                $helpText.removeClass(function(index, className) {
                    return (className.match(/(^|\s)help-\S+/g) || []).join(' ');
                });
                $helpText.addClass('help-' + value);
                if (helpTexts[value]) {
                    $helpText.html('<i class="dashicons dashicons-info-outline"></i> ' + helpTexts[value]);
                }
                
                // Also handle title section visibility
                var $titleSection = $block.find('.section_title');
                if (value === 'title') {
                    $titleSection.show();
                } else {
                    $titleSection.hide();
                }
            });
            
            // Add new block
            $('#add-image-block').on('click', function() {
                var $template = $('#template-container .image-placement-block.template-block').first();
                var $newBlock = $template.clone();
                
                // Update block index
                $newBlock.removeClass('template-block');
                $newBlock.addClass('image-block-' + blockIndex);
                $newBlock.attr('data-block-index', blockIndex);
                $newBlock.find('.block-number').text(blockIndex);
                
                // Update all input names - replace [0] with new index
                $newBlock.find('[name*="[image_block][0]"]').each(function() {
                    var name = $(this).attr('name');
                    $(this).attr('name', name.replace('[image_block][0]', '[image_block][' + blockIndex + ']'));
                });
                
                // Handle data-name-template attributes (convert to name with proper index)
                $newBlock.find('[data-name-template]').each(function() {
                    var nameTemplate = $(this).attr('data-name-template');
                    var newName = nameTemplate.replace('__INDEX__', blockIndex);
                    $(this).attr('name', newName);
                    $(this).removeAttr('data-name-template');
                });
                
                // Update data-block-index on select
                $newBlock.find('.based-on-select').attr('data-block-index', blockIndex);
                
                // Show the block
                $newBlock.css('display', 'block');
                
                // Append to container (inside the form)
                $('#image-blocks-container').append($newBlock);
                
                // Increment block index for next addition
                blockIndex++;
                
                // Renumber all visible blocks
                renumberBlocks();
            });
            
            // Delete block
            $(document).on('click', '.btn-delete-block', function() {
                var $block = $(this).closest('.image-placement-block');
                
                // Don't delete the template
                if ($block.hasClass('template-block')) {
                    return;
                }
                
                $block.remove();
                renumberBlocks();
            });
            
            // Renumber blocks after deletion - updates BOTH display AND input names
            function renumberBlocks() {
                var newIndex = 1;
                $('#image-blocks-container .image-placement-block:not(.template-block)').each(function() {
                    var $block = $(this);
                    var oldIndex = $block.attr('data-block-index');
                    
                    // Update display number
                    $block.find('.block-number').text(newIndex);
                    
                    // Update data-block-index attribute
                    $block.attr('data-block-index', newIndex);
                    $block.removeClass('image-block-' + oldIndex).addClass('image-block-' + newIndex);
                    
                    // Update ALL input/select names in this block to use the new index
                    $block.find('input, select, textarea').each(function() {
                        var name = $(this).attr('name');
                        if (name && name.indexOf('[image_block]') !== -1) {
                            // Replace [image_block][ANY_NUMBER] with [image_block][newIndex]
                            var newName = name.replace(/\[image_block\]\[\d+\]/, '[image_block][' + newIndex + ']');
                            $(this).attr('name', newName);
                        }
                    });
                    
                    // Update data-block-index on select
                    $block.find('.based-on-select').attr('data-block-index', newIndex);
                    
                    newIndex++;
                });
                
                // Update blockIndex for next new block
                blockIndex = newIndex;
            }
            
            // Also renumber on form submit to ensure consistent indices
            $('#image-placement-form').on('submit', function() {
                renumberBlocks();
            });
        }

    });

})(jQuery);
