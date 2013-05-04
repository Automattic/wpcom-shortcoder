var WPCOM_Shortcoder = {};
			
WPCOM_Shortcoder.addShortcode = function() {
    if(jQuery('.media-frame-toolbar .button').attr('disabled') == 'disabled'){
        return false;
    }
    
    (window.dialogArguments || opener || parent || top).send_to_editor('[' + jQuery('.media-iframe iframe').contents().find('input[name="WPS[shortcode]"]:checked').val() + ']');
};