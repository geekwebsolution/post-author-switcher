<?php
$show_msg = [];

$post_types = get_post_types(array('public' => true));
unset($post_types['attachment']);

if(isset($_POST["submit"])) {

    if(wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'gclpas_nonce' ] ) ), 'gclpas_security_nonce' )) {

        $new_author = (isset($_POST["gclpas_switch_author_to"]) && !empty($_POST["gclpas_switch_author_to"])) ? $_POST["gclpas_switch_author_to"] : [];
        $post_type = (isset($_POST["gclpas_post_type"]) && !empty($_POST["gclpas_post_type"])) ? $_POST["gclpas_post_type"] : [];
        $author_from = (isset($_POST["gclpas_switch_author_from"]) && !empty($_POST["gclpas_switch_author_from"])) ? $_POST["gclpas_switch_author_from"] : [];

        global $wpdb;

        if(!empty($new_author) && !empty($post_type) && !empty($author_from)) {

            $sql_post_type = array_map( function($type) { return "post_type = '$type'"; }, $post_type );
            $sql_author_from = array_map( function($from_author) { return "post_author = '$from_author'"; }, $author_from );

            $query_post_type = implode(" OR ",$sql_post_type);
            $query_author_from = implode(" OR ",$sql_author_from);

            $update_query = $wpdb->query( 
                $wpdb->prepare( "UPDATE $wpdb->posts SET post_author = %d WHERE ( $query_post_type ) AND ( post_status != 'auto-draft' OR post_status != 'inherit' ) AND ( $query_author_from )", $new_author ),
            );

            if(!empty($update_query)) {
                // Success returns rows affected
                $show_msg = array( "type" => "success", "message" => "Successfully update $update_query number of posts." );
            }else{
                // There was an error.
                $show_msg = array( "type" => "info", "message" => "Not found any posts related to selected author." );
            }
        }else{
            $show_msg = array( "type" => "error", "message" => "Please feel out all fields correctly." );
        }
    }
}
?>
<div class="wrap">
    <h1><?php echo esc_html('Post Author Switcher','post-author-switcher'); ?></h1>

    <form method="post" id="post_author_switcher">
        <input type="hidden" name="gclpas_nonce" value="<?php echo esc_attr( wp_create_nonce( 'gclpas_security_nonce' ) ); ?>">

        <?php
        if (isset($show_msg) && !empty($show_msg)) {
            wp_admin_notice(
                '<strong>' . __( $show_msg['message'], 'post-author-switcher' ) . '</strong>',
                array(
                    'type'        => $show_msg['type'],
                    'dismissible' => false,
                    'id'          => 'message'
                )
            );    
        }
        ?>
    
        <table class="form-table gclpas-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for=""><?php echo esc_html('All','post-author-switcher'); ?><?php echo esc_html('Select Post Type','post-author-switcher'); ?></label>
                    </th>
                    <td>
                        <label class="gclpas-containercheckbox">
                            <input type="checkbox" id="gclpas_post_type"><?php echo esc_html('All','post-author-switcher'); ?>
                        </label><br>
                        <?php 
                        if(count($post_types) > 0 && !empty($post_types)) {
                            foreach($post_types as $post_type) { ?>
                                <label class="gclpas-containercheckbox">
                                    <input type="checkbox" name="gclpas_post_type[]" value="<?php echo esc_attr($post_type); ?>"><?php echo esc_html(ucfirst($post_type)); ?>
                                </label><br>
                                <?php
                            }
                        } ?>
                        <div class="gclpas-error gclpas-post-type-error"></div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="gclpas_switch_author_from"><?php echo esc_html('Switch Author From','post-author-switcher'); ?></label>
                    </th>
                    <td>
                        <select name="gclpas_switch_author_from[]" class="gclpas_select_author" style="width: 30%" id="gclpas_switch_author_from" multiple="multiple"></select>
                        <div class="gclpas-error gclpas-select-author-from-error"></div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="gclpas_switch_author_to"><?php echo esc_html('Switch Author To','post-author-switcher'); ?></label>
                    </th>
                    <td>
                        <select name="gclpas_switch_author_to" class="gclpas_select_author" style="width: 30%" id="gclpas_switch_author_to"></select>
                        <div class="gclpas-error gclpas-select-author-to-error"></div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="gclpas-submit-btn">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Run Author Switcher">
        </div>
    </form>
</div>