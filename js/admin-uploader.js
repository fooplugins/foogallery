jQuery(document).ready(function() {
    jQuery('.image-upload-button').click(function(e) {
        e.preventDefault();
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        return false;
    });

    window.send_to_editor = function(html) {
        var img = jQuery('img',html).attr('src');
        jQuery(targetfield).val(img);
        tb_remove();
    };
});
