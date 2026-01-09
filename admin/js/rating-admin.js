/* Rating div */
jQuery(document).ready(function($){
    $('#mpt-rate').on('click', function(e){
      e.preventDefault();
      $('#mpt-rating-notice').html(allsiTranslationsJsVars.translations.rate_notice);
    });
  
    $(document).on('click', '#mpt-hide-notice', function(e){
      e.preventDefault();
      $.post(ajaxurl, {action: 'allsi_hide_notice'});
      $('#mpt-rating-notice').hide();
    });
  
    $(document).on('click', '#mpt-remind-later', function(e){
      e.preventDefault();
      $.post(ajaxurl, {action: 'allsi_remind_later', delay: 30});
      $('#mpt-rating-notice').hide();
    });
  
    $(document).on('click', '#mpt-already-done', function(e){
      e.preventDefault();
      $.post(ajaxurl, {action: 'allsi_remind_later', delay: 180});
      $('#mpt-rating-notice').hide();
    });
  });
