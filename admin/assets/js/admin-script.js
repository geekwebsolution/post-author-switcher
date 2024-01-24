jQuery(document).ready(function ($) {
    /** select2 for author list field */
    jQuery('.gclpas_select_author').select2({
        minimumInputLength: 3,
        width: 'resolve',
        allowClear: true,
        placeholder: "Search Author",
        ajax: {
            type: 'POST',
            url: gclpasObj.ajaxurl,
            dataType: 'json',
            data: (params) => {
                return {
                    'search': params.term,
                    'action': 'gclpas_author_list',
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

    /** Check all checkbox */
    jQuery("#gclpas_post_type").click(function () {
        jQuery('input[name="gclpas_post_type[]"]:checkbox').not(this).prop('checked', this.checked);
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