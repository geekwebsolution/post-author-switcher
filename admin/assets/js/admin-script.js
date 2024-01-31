jQuery(document).ready(function ($) {

    /** Check all checkbox when All Post Type selected */
    jQuery("body").on("click","#gclpas_post_type",function() {
        jQuery('input[name="gclpas_post_type[]"]:checkbox').not(this).prop('checked', this.checked);
    });
    jQuery('body').on('change','input[name="gclpas_post_type[]"]',function() {
        if(jQuery('input[name="gclpas_post_type[]"]:checked').length == 0) {
            jQuery('#gclpas_post_type').prop('checked', false);
        }
    });

    /** Check all checkbox when All Post Status selected */
    jQuery("body").on("click","#gclpas_post_status",function() {
        jQuery('input[name="gclpas_post_status[]"]:checkbox').not(this).prop('checked', this.checked);
    });
    jQuery('body').on('change','input[name="gclpas_post_status[]"]',function() {
        if(jQuery('input[name="gclpas_post_status[]"]:checked').length == 0) {
            jQuery('#gclpas_post_status').prop('checked', false);
        }
    });

    /** Hide/Show sub fields */
    jQuery("body").on("change","#gclpas_switch_author_status",function(){
        if(jQuery(this).is(":checked")) {
            jQuery(".gclpas-sub-row").show();
        }else{
            jQuery(".gclpas-sub-row").hide();
        }
    });

    /** select2 for author list field */
    var $select_box = jQuery('.gclpas_select_author');
    $select_box.select2({
        minimumInputLength: 3,
        width: 'resolve',
        allowClear: true,
        ajax: {
            type: 'POST',
            url: gclpasObj.ajaxurl,
            dataType: 'json',
            data: (params) => {
                return {
                    'search': params.term,
                    'exclude': (jQuery($select_box).data("exclude-user")) ? jQuery($select_box).data("exclude-user") : '',
                    'action': 'gclpas_author_list'
                }
            },
            processResults: function (data, params) {
                const results = data.map(item => {
                    return {
                        id: item.id,
                        text: item.title,
                    };
                });
                return {
                    results: results,
                }
            },
        }
    });

    /** validation for required setting fields */
    jQuery('#your-profile').on('submit', function (event) {
        var validate = false;

        if(jQuery('#gclpas_switch_author_status').length > 0 && jQuery('#gclpas_switch_author_status').is(":checked")) {
          
            //Asynchronous Transfer 
            if(jQuery('input[name="gclpas_post_type[]"]:checked').length == 0) {
                validate = true;
                jQuery(".gclpas-post-type-error").text("Please select at least one post type.");
            }else{
                jQuery(".gclpas-post-type-error").text("");
            }

            //Asynchronous Transfer 
            if(jQuery('input[name="gclpas_post_status[]"]:checked').length == 0) {
                validate = true;
                jQuery(".gclpas-post-status-error").text("Please select at least one post status.");
            }else{
                jQuery(".gclpas-post-status-error").text("");
            }

            if(jQuery('#gclpas_switch_author_to option:selected').length == 0) {
                validate = true;
                jQuery(".gclpas-select-author-to-error").text("Please select at least one author.");
            }else{
                jQuery(".gclpas-select-author-to-error").text("");
            }

            if(validate) {
                event.preventDefault();
            }
        }

    });

    /** validation for required setting fields */
    jQuery('#post_author_switcher').on('submit', function (event) {
        var validate = false;
          
        //Asynchronous Transfer 
        if(jQuery('input[name="gclpas_post_type[]"]:checked').length == 0) {
            validate = true;
            jQuery(".gclpas-post-type-error").text("Please select at least one post type.");
        }else{
            jQuery(".gclpas-post-type-error").text("");
        }

        if(jQuery('#gclpas_switch_author_from option:selected').length == 0) {
            validate = true;
            jQuery(".gclpas-select-author-from-error").text("Please select at least one author.");
        }else{
            jQuery(".gclpas-select-author-from-error").text("");
        }

        if(jQuery('#gclpas_switch_author_to option:selected').length == 0) {
            validate = true;
            jQuery(".gclpas-select-author-to-error").text("Please select at least one author.");
        }else{
            jQuery(".gclpas-select-author-to-error").text("");
        }

        if(validate) {
            event.preventDefault();
        }

    });
    
});