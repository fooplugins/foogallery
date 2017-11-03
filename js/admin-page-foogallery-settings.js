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
                $container = $('#foogallery_clear_css_optimizations_container'),
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
                    $container.html(data);
                },
                complete: function() {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                }
            });
        });
    };

    FOOGALLERY.bindTestThumbnailButton = function() {
        $('.foogallery_thumb_generation_test').click(function(e) {
            e.preventDefault();

            var $button = $(this),
                $container = $('#foogallery_thumb_generation_test_container'),
                $spinner = $('#foogallery_thumb_generation_test_spinner'),
                data = 'action=foogallery_thumb_generation_test' +
                    '&_wpnonce=' + $button.data('nonce') +
                    '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

            $spinner.addClass('is-active');
            $button.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function(data) {
                    $container.html(data);
                },
                complete: function() {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                }
            });
        });
    };

    FOOGALLERY.bindApplyRetinaDefaults = function() {
        $('.foogallery_apply_retina_support').click(function(e) {
            e.preventDefault();

            var $button = $(this),
                $container = $('#foogallery_apply_retina_support_container'),
                $spinner = $('#foogallery_apply_retina_support_spinner'),
                data = 'action=foogallery_apply_retina_defaults' +
                    '&_wpnonce=' + $button.data('nonce') +
                    '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

            var selected = [];
            $( $button.data('inputs') ).each(function() {
                if ($(this).is(":checked")) {
                    selected.push($(this).attr('name'));
                }
            });

            data += '&defaults=' + selected;

            $spinner.addClass('is-active');
            $button.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function(data) {
                    $container.html(data);
                },
                complete: function() {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                }
            });
        });
    };

    FOOGALLERY.bindUninstallButton = function() {
        $('.foogallery_uninstall').click(function(e) {
            e.preventDefault();

            var $button = $(this),
                $container = $('#foogallery_uninstall_container'),
                $spinner = $('#foogallery_uninstall_spinner'),
                data = 'action=foogallery_uninstall' +
                    '&_wpnonce=' + $button.data('nonce') +
                    '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

            $spinner.addClass('is-active');
            $button.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function(data) {
                    $container.html(data);
                },
                complete: function() {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                }
            });
        });
    };

    FOOGALLERY.bindClearHTMLCacheButton = function() {
        $('.foogallery_clear_html_cache').click(function(e) {
            e.preventDefault();

            var $button = $(this),
                $container = $('#foogallery_clear_html_cache_container'),
                $spinner = $('#foogallery_clear_html_cache_spinner'),
                data = 'action=foogallery_clear_html_cache' +
                    '&_wpnonce=' + $button.data('nonce') +
                    '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

            $spinner.addClass('is-active');
            $button.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function(data) {
                    $container.html(data);
                },
                complete: function() {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                }
            });
        });
    };

    //find all generic foogallery ajax buttons and bind them
    FOOGALLERY.bindSettingsAjaxButtons = function () {
        $('.foogallery_settings_ajax').click(function(e) {
            e.preventDefault();

            var $button = $(this),
                $container = $button.parents('.foogallery_settings_ajax_container:first'),
                $spinner = $container.find('.spinner'),
                response = $button.data('response'),
                confirmMessage = $button.data('confirm'),
                confirmResult = true,
                data = 'action=' + $button.data('action') +
                    '&_wpnonce=' + $button.data('nonce') +
                    '&_wp_http_referer=' + encodeURIComponent($('input[name="_wp_http_referer"]').val());

            if ( confirmMessage ) {
                confirmResult = confirm( confirmMessage );
            };

            if ( confirmResult ) {
                $spinner.addClass('is-active');
                $button.prop('disabled', true);

                $.ajax({
                    type    : "POST",
                    url     : ajaxurl,
                    data    : data,
                    success : function (data) {
                        if (response === 'replace_container') {
                            $container.html(data);
                        } else if (response === 'alert') {
                            alert(data);
                        }
                    },
                    complete: function () {
                        $spinner.removeClass('is-active');
                        $button.prop('disabled', false);
                    }
                });
            }
        });
    };

    $(function() { //wait for ready
        FOOGALLERY.loadImageOptimizationContent();
        FOOGALLERY.bindClearCssOptimizationButton();
        FOOGALLERY.bindTestThumbnailButton();
        FOOGALLERY.bindApplyRetinaDefaults();
        FOOGALLERY.bindUninstallButton();
        FOOGALLERY.bindClearHTMLCacheButton();

        FOOGALLERY.bindSettingsAjaxButtons();
    });

}(window.FOOGALLERY = window.FOOGALLERY || {}, jQuery));