<?php
$show_msg = [];

$post_types = get_post_types(array('public' => true));
unset($post_types['attachment']);

if(isset($_POST["submit"])) {

    if(wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'gclpas_nonce' ] ) ), 'gclpas_security_nonce' )) {

        $new_author = (isset($_POST["gclpas_switch_author_to"]) && !empty($_POST["gclpas_switch_author_to"])) ? $_POST["gclpas_switch_author_to"] : [];
        $post_type = (isset($_POST["gclpas_post_type"]) && !empty($_POST["gclpas_post_type"])) ? $_POST["gclpas_post_type"] : [];
        $post_status = (isset($_POST["gclpas_post_status"]) && !empty($_POST["gclpas_post_status"])) ? $_POST["gclpas_post_status"] : [];
        $author_from = (isset($_POST["gclpas_switch_author_from"]) && !empty($_POST["gclpas_switch_author_from"])) ? $_POST["gclpas_switch_author_from"] : [];

        global $wpdb;

        if(!empty($new_author) && !empty($post_type) && !empty($post_status) && !empty($author_from)) {

        $sql_post_type = array_map( function($type) { return "post_type = '$type'"; }, $post_type );
        $sql_post_status = array_map( function($status) { return "post_status = '$status'"; }, $post_status );
        $sql_author_from = array_map( function($from_author) { return "post_author = '$from_author'"; }, $author_from );

        $query_post_type = implode(" OR ",$sql_post_type);
        $query_post_status = implode(" OR ",$sql_post_status);
        $query_author_from = implode(" OR ",$sql_author_from);

        $update_query = $wpdb->query( 
            $wpdb->prepare( "UPDATE $wpdb->posts SET post_author = %d WHERE ( $query_post_type ) AND ( $query_post_status ) AND ( $query_author_from )", $new_author ),
        );

            if(!empty($update_query)) {
                // Success returns rows affected
                $show_msg = array( "type" => "success", "message" => __("Successfully updated author of $update_query number of posts.","post-author-switcher") );
                            }else{
                // There was an error.
                $show_msg = array( "type" => "info", "message" => __("Not found any posts related to selected author.","post-author-switcher") );
                            }
        }else{
                        $show_msg = array( "type" => "error", "message" => __("Please feel out all fields correctly.","post-author-switcher") );
        }
    }
}
?>
<div class="wrap">
        <h1><?php echo esc_html__('Post Author Switcher','post-author-switcher'); ?></h1>

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
    
        <table class="form-table gclpas-table gclpas-table-box" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                                                <label for=""><?php echo esc_html__('Select Post Type','post-author-switcher'); ?></label>
                    </th>
                    <td class="gclpas-containertd">
                        <label class="gclpas-containercheckbox">
                            <input type="checkbox" id="gclpas_post_type"><?php echo esc_html__('All','post-author-switcher'); ?>
                        </label>
                        <?php 
                        if(count($post_types) > 0 && !empty($post_types)) {
                            foreach($post_types as $post_type) { ?>
                                <label class="gclpas-containercheckbox">
                                    <input type="checkbox" name="gclpas_post_type[]" value="<?php echo esc_attr($post_type); ?>"><?php echo esc_html(ucfirst($post_type)); ?>
                                </label>
                                <?php
                            }
                        } ?>
                                                <div class="gclpas-note"><i><?php echo esc_html__('Select post type to switch author of posts.','post-author-switcher'); ?></i></div>
                        <div class="gclpas-error gclpas-post-type-error"></div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for=""><?php echo esc_html__('Select Post Status','post-author-switcher'); ?></label>
                    </th>
                    <td class="gclpas-containertd">
                        <label class="gclpas-containercheckbox">
                            <input type="checkbox" id="gclpas_post_status" checked><?php echo esc_html__('All','post-author-switcher'); ?>
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
                        <div class="gclpas-note"><i><?php echo esc_html__('Select post status of which you want to switch author.','post-author-switcher'); ?></i></div>
                        <div class="gclpas-error gclpas-post-status-error"></div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="gclpas-switch-author-label">
                        <label for="gclpas_switch_author_from"><?php echo esc_html__('Switch Author From','post-author-switcher'); ?></label>
                    </th>
                    <td>
                        <select name="gclpas_switch_author_from[]" class="gclpas_select_author" style="width: 30%" id="gclpas_switch_author_from" multiple="multiple" data-placeholder="<?php echo esc_attr__('Search Author','post-author-switcher'); ?>"></select>
                        <div class="gclpas-note" style="margin-top: 10px;"><i><?php echo esc_html__('Select authors of whose you want to change posts author.','post-author-switcher'); ?></i></div>
                        <div class="gclpas-error gclpas-select-author-from-error"></div>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="gclpas-switch-author-label">
                        <label for="gclpas_switch_author_to"><?php echo esc_html__('Switch Author To','post-author-switcher'); ?></label>
                    </th>
                    <td>
                        <select name="gclpas_switch_author_to" class="gclpas_select_author" style="width: 30%" id="gclpas_switch_author_to" data-placeholder="<?php echo esc_attr__('Search Author','post-author-switcher'); ?>"></select>
                        <div class="gclpas-note" style="margin-top: 10px;"><i><?php echo esc_html__('Select new author which you want as new author of posts.','post-author-switcher'); ?></i></div>
                        <div class="gclpas-error gclpas-select-author-to-error"></div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="gclpas-submit-btn">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr__('Run Author Switcher','post-author-switcher'); ?>">
        </div>
    </form>
</div>