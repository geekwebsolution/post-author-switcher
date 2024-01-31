<?php
if( !class_exists('GCLPAS_Switch_User') ) {
    class GCLPAS_Switch_User
    {
        public function __construct() {
            /** User edit page hooks */
            add_action('show_user_profile', array( $this, 'gclpas_user_profile_edit_action'), PHP_INT_MAX);
            add_action('personal_options_update', array( $this, 'gclpas_user_profile_update_action'), PHP_INT_MAX);

            add_action('edit_user_profile', array( $this, 'gclpas_user_profile_edit_action'), PHP_INT_MAX );
            add_action('edit_user_profile_update', array( $this, 'gclpas_user_profile_update_action'), PHP_INT_MAX );
        }

        public function gclpas_user_profile_edit_action() { 
            ?>
            <h2><?php echo esc_html__('Post Author Switcher','post-author-switcher'); ?></h2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="gclpas_switch_author_status"><?php echo esc_html('Switch Post Author','post-author-switcher'); ?></label></th>
                        <td>
                            <label class="gclpas-switch">
                                <input name="gclpas_switch_author_status" class="gclpas-checkbox" id="gclpas_switch_author_status" type="checkbox" value="1">
                                <span class="gclpas-slider gclpas-round"></span>
                            </label>
                            <div class="gclpas-note"><i><?php echo esc_html__('Enable this to switch author of multiple posts.','post-author-switcher'); ?></i></div>
                        </td>
                    </tr>
                    <?php
                    $post_types = get_post_types(array('public' => true));
                    unset($post_types['attachment']); ?>
                    <tr class="gclpas-sub-row">
                        <th scope="row">
                            <label for=""><?php echo esc_html__('Select Post Type','post-author-switcher'); ?></label>
                        </th>
                        <td>
                            <label class="gclpas-containercheckbox">
                                <input type="checkbox" id="gclpas_post_type" name="gclpas_post_type"><?php echo esc_html__('All','post-author-switcher'); ?>
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
                            <div class="gclpas-note"><i><?php echo esc_html__('Select post type to switch author of posts.','post-author-switcher'); ?></i></div>
                            <div class="gclpas-error gclpas-post-type-error"></div>
                        </td>
                    </tr>
                    <tr class="gclpas-sub-row">
                        <th><label for="gclpas_switch_author_to"><?php echo esc_html__('Select New Post Author','post-author-switcher'); ?></label></th>
                        <td>
                            <?php
                            $get_user_args = array( 'capability__in'   => 'edit_posts' );
                            if(isset($_GET["user_id"])) {
                                $get_user_args = array_merge( array( 'exclude' => array( $_GET["user_id"] ) ), $get_user_args );
                            }
                            $gclpas_get_author = get_users( $get_user_args );
                            ?>
                            <select name="gclpas_switch_author_to" id="gclpas_switch_author_to" class="gclpas_select_author" data-placeholder="Search Author" data-exclude-user="<?php echo esc_attr(isset($_GET["user_id"]) ? $_GET["user_id"] : ''); ?>" style="width: 25em;"></select>
                            <div class="gclpas-note"><i><?php echo esc_html__('Select new author which you want as new author of posts.','post-author-switcher'); ?></i></div>
                            <div class="gclpas-error gclpas-select-author-to-error"></div>
                        </td>
                    </tr>
                    <tr class="gclpas-sub-row">
                        <th scope="row">
                            <label for=""><?php echo esc_html__('Select Post Status','post-author-switcher'); ?></label>
                        </th>
                        <td>
                            <label class="gclpas-containercheckbox">
                                <input type="checkbox" id="gclpas_post_status" checked><?php echo esc_html__('All','post-author-switcher'); ?>
                            </label><br>
                            <?php 
                            $post_status = array( "publish" => "Publish", "pending" => "Pending", "draft" => "Draft", "future" => "Future", "private" => "Private" );
                            if(count($post_status) > 0 && !empty($post_status)) {
                                foreach($post_status as $key => $value) { ?>
                                    <label class="gclpas-containercheckbox">
                                        <input type="checkbox" name="gclpas_post_status[]" value="<?php echo esc_attr($key); ?>" checked><?php echo esc_html($value); ?>
                                    </label><br>
                                    <?php
                                }
                            } ?>
                            <div class="gclpas-note"><i><?php echo esc_html__('Select post status of which you want to switch author.','post-author-switcher'); ?></i></div>
                            <div class="gclpas-error gclpas-post-status-error"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php 
        }

        public function gclpas_user_profile_update_action($user_id) {

            $status_switch_author = (isset($_POST["gclpas_switch_author_status"]) && !empty($_POST["gclpas_switch_author_status"])) ? 1 : 0;

            if(isset($status_switch_author) && !empty($status_switch_author)) {

                $new_author = (isset($_POST["gclpas_switch_author_to"]) && !empty($_POST["gclpas_switch_author_to"])) ? $_POST["gclpas_switch_author_to"] : [];
                $post_type = (isset($_POST["gclpas_post_type"]) && !empty($_POST["gclpas_post_type"])) ? $_POST["gclpas_post_type"] : [];
                $post_status = (isset($_POST["gclpas_post_status"]) && !empty($_POST["gclpas_post_status"])) ? $_POST["gclpas_post_status"] : [];

                $author_from = (isset($user_id) && !empty($user_id)) ? $user_id : '';

                global $wpdb;
                if(!empty($new_author) && !empty($post_type) && !empty($author_from) && !empty($post_status)) {
                    $sql_post_type = array_map( function($type) { return "post_type = '$type'"; }, $post_type );
                    $sql_post_status = array_map( function($status) { return "post_status = '$status'"; }, $post_status );

                    $query_post_type = implode(" OR ",$sql_post_type);
                    $query_post_status = implode(" OR ",$sql_post_status);

                    $update_query = $wpdb->query( 
                        $wpdb->prepare( "UPDATE $wpdb->posts SET post_author = %d WHERE ( $query_post_type ) AND ( $query_post_status ) AND ( post_author = $author_from )", $new_author ),
                    );
                }
            }
        }
    }
    new GCLPAS_Switch_User();
}