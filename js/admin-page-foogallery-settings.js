jQuery(document).ready(function($) {
    $.admin_tabs = {

        init : function() {
          $("a.nav-tab").click( function(e) {
              e.preventDefault();

              $this = $(this);

              $this.parents(".nav-tab-wrapper:first").find(".nav-tab-active").removeClass("nav-tab-active");
              $this.addClass("nav-tab-active");

              $(".nav-container:visible").hide();

              var hash = $this.attr("href");

              $(hash+'_tab').show();

              //fix the referer so if changes are saved, we come back to the same tab
              var referer = $("input[name=_wp_http_referer]").val();
              if (referer.indexOf("#") >= 0) {
                referer = referer.substr(0, referer.indexOf("#"));
              }
              referer += hash;

              window.location.hash = hash;

              $("input[name=_wp_http_referer]").val(referer);
          });

          if (window.location.hash) {
            $('a.nav-tab[href="' + window.location.hash + '"]').click();
          }

          return false;
        }

    }; //End of admin_tabs

    $.admin_tabs.init();
});

//
(function(FOOGALLERY, $, undefined) {

    FOOGALLERY.loadImageOptimizationContent = function() {
        var data = 'action=foogallery_get_image_optimization_info' +
            '&_wpnonce=' + $('#foogallery_setting_image_optimization-nonce').val() +
            '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function(data) {
                $('#foogallery_settings_image_optimization_container').replaceWith(data);
            }
        });
    };

    FOOGALLERY.bindClearCssOptimizationButton = function() {
        $('.foogallery_clear_css_optimizations').click(function(e) {
            e.preventDefault();



            var $button = $(this),
                $spinner = $('#foogallery_clear_css_cache_spinner'),
                data = 'action=foogallery_clear_css_optimizations' +
                '&_wpnonce=' + $button.data('nonce') +
                '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

            $spinner.addClass('is-active');
            $button.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function(data) {
                    alert(data);
                },
                complete: function() {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                }
            });
        });
    };

    $(function() { //wait for ready
        FOOGALLERY.loadImageOptimizationContent();
        FOOGALLERY.bindClearCssOptimizationButton();
    });

}(window.FOOGALLERY = window.FOOGALLERY || {}, jQuery));