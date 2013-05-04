jQuery(document).ready(function(){
    var disabled = false;
    
    function checkIfDisabled(){
        if(disabled == false) {
            disabled = true;
           
            parent.jQuery('.media-frame-toolbar .button').removeAttr('disabled');
        }
    }
    
    jQuery('#wpcom-shortcoder-shortcodes input:radio').bind('click', function(){
        checkIfDisabled();
        
        var $shortcodeItem = jQuery(this).parent().find('.wpcom-shortcoder-shortcode-options');
        jQuery('.media-sidebar').html($shortcodeItem.length == 1 ? $shortcodeItem.html() : '');
    });
    
    jQuery('#wpcom-shortcoder-inputs select').bind('change', function(){
       var value = jQuery(this).val();

       if(value == ''){
           jQuery('#wpcom-shortcoder-shortcodes li').show();
       }
       else {
           var $shortcodeInput = jQuery('input[value="' + value + '"]');
           $shortcodeInput.prop('checked', true);
           $shortcodeInput.parent().show().siblings().hide();
        
           checkIfDisabled();
       }
    });

    parent.jQuery('.hide-toolbar').removeClass('hide-toolbar');
    parent.jQuery('.media-frame-toolbar .media-toolbar-primary').html('<a onclick="WPCOM_Shortcoder.addShortcode();" disabled="disabled" class="button button-large media-button button-primary" href="#">Insert into post</a>');
});