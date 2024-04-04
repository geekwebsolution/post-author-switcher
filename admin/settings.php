<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$show_msg = [];

$post_types = get_post_types(array('public' => true));
unset($post_types['attachment']);

if(isset($_POST["submit"])) {

    if(wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'gclpas_nonce' ] ) ), 'gclpas_security_nonce' )) {

        $new_author = (isset($_POST["gclpas_switch_author_to"]) && !empty($_POST["gclpas_switch_author_to"])) ? sanitize_text_field($_POST["gclpas_switch_author_to"]) : "";
        $post_type = (isset($_POST["gclpas_post_type"]) && !empty($_POST["gclpas_post_type"])) ? array_map('sanitize_text_field', $_POST["gclpas_post_type"]) : [];
        $post_status = (isset($_POST["gclpas_post_status"]) && !empty($_POST["gclpas_post_status"])) ? array_map('sanitize_text_field', $_POST["gclpas_post_status"]) : [];
        $author_from = (isset($_POST["gclpas_switch_author_from"]) && !empty($_POST["gclpas_switch_author_from"])) ? array_map('sanitize_text_field', $_POST["gclpas_switch_author_from"]) : [];

        global $wpdb;

        if(!empty($new_author) && !empty($post_type) && !empty($post_status) && !empty($author_from)) {

            $query_post_type = implode("', '", $post_type);
            $query_post_status = implode("', '", $post_status);
            $query_author_from = implode("', '", $author_from);

            $where_sql = sprintf('( post_type = %s ) AND ( post_status IN (%s) ) AND ( post_author = %s )', '%s', '%s', '%s');

            $update_query = $wpdb->query( stripslashes($wpdb->prepare( "UPDATE {$wpdb->posts} SET post_author = %d WHERE {$where_sql}", $new_author, $query_post_type, $query_post_status, $query_author_from )) );

            if(!empty($update_query)) {
                // Success returns rows affected
                $show_msg = array( "type" => "success", "message" => __( sprintf( 'Successfully updated author of %1$s number of posts.', $update_query ) , 'post-author-switcher') );
            }else{
                // There was an error.
                $show_msg = array( "type" => "info", "message" => __( "Not found any posts related to selected author.","post-author-switcher" ) );
            }
        }else{
            $show_msg = array( "type" => "error", "message" => __( "Please feel out all fields correctly.","post-author-switcher") );
        }
    }
}
?>
<div class="wrap">
    <h1><?php echo esc_html__('Post Author Switcher','post-author-switcher'); ?></h1>

    <form method="post" id="post_author_switcher">
        <input type="hidden" name="gclpas_nonce" value="<?php esc_attr_e( wp_create_nonce( 'gclpas_security_nonce' ) ); ?>">

        <?php
        if (isset($show_msg) && !empty($show_msg)) {
            $admin_notice_msg = sprintf( '<strong>%1$s</strong>', $show_msg['message'] );
            wp_admin_notice(
                $admin_notice_msg,
                array(
                    'type'        => $show_msg['type'],
                    'dismissible' => false,
                    'id'          => 'message'
                )
            );    
        } ?>
    
        <table class="form-table gclpas-table gclpas-table-box" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for=""><?php esc_html_e('Select Post Type','post-author-switcher'); ?></label>
                    </th>
                    <td class="gclpas-containertd">
                        <label class="gclpas-containercheckbox">
                            <input type="checkbox" id="gclpas_post_type"><?php esc_html_e('All','post-author-switcher'); ?>
                        </label>
                        <?php 
                        if(count($post_types) > 0 && !empty($post_types)) {
                            foreach($post_types as $post_type) { ?>
                                <label class="gclpas-containercheckbox">
                                    <input type="checkbox" name="gclpas_post_type[]" value="<?php esc_html_e($post_type); ?>"><?php esc_html_e(ucfirst($post_type)); ?>
                                </label>
                                <?php
                            }
                        } ?>
                        <div class="gclpas-note"><i><?php esc_html_e('Select post type to switch author of posts.','post-author-switcher'); ?></i></div>
                        <div class="gclpas-error gclpas-post-type-error"></div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for=""><?php esc_html_e('Select Post Status','post-author-switcher'); ?></label>
                    </th>
                    <td class="gclpas-containertd">
                        <label class="gclpas-containercheckbox">
                            <input type="checkbox" id="gclpas_post_status" checked><?php esc_html_e('All','post-author-switcher'); ?>
                        </label>
                        <?php 
                        $post_status = array( "publish" => "Publish", "pending" => "Pending", "draft" => "Draft", "future" => "Future", "private" => "Private" );
                        if(count($post_status) > 0 && !empty($post_status)) {
                            foreach($post_status as $key => $value) { ?>
                                <label class="gclpas-containercheckbox">
                                    <input type="checkbox" name="gclpas_post_status[]" value="<?php echo esc_attr($key); ?>" checked><?php echo esc_html($value); ?>
                                </label>
                                <?php
                            }
                        } ?>
                        <div class="gclpas-note"><i><?php esc_html_e('Select post status of which you want to switch author.','post-author-switcher'); ?></i></div>
                        <div class="gclpas-error gclpas-post-status-error"></div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="gclpas-switch-author-label">
                        <label for="gclpas_switch_author_from"><?php esc_html_e('Switch Author From','post-author-switcher'); ?></label>
                    </th>
                    <td>
                        <select name="gclpas_switch_author_from[]" class="gclpas_select_author" style="width: 30%" id="gclpas_switch_author_from" multiple="multiple" data-placeholder="<?php esc_attr_e('Search Author','post-author-switcher'); ?>"></select>
                        <div class="gclpas-note" style="margin-top: 10px;"><i><?php esc_html_e('Select authors of whose you want to change posts author.','post-author-switcher'); ?></i></div>
                        <div class="gclpas-error gclpas-select-author-from-error"></div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="gclpas-switch-author-label">
                        <label for="gclpas_switch_author_to"><?php esc_html_e('Switch Author To','post-author-switcher'); ?></label>
                    </th>
                    <td>
                        <select name="gclpas_switch_author_to" class="gclpas_select_author" style="width: 30%" id="gclpas_switch_author_to" data-placeholder="<?php esc_attr_e('Search Author','post-author-switcher'); ?>"></select>
                        <div class="gclpas-note" style="margin-top: 10px;"><i><?php esc_html_e('Select new author which you want as new author of posts.','post-author-switcher'); ?></i></div>
                        <div class="gclpas-error gclpas-select-author-to-error"></div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="gclpas-submit-btn">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Run Author Switcher','post-author-switcher'); ?>">
        </div>
    </form>
</div>