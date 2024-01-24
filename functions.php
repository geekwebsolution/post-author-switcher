<?php
if( !class_exists('GCLPAS_Functions') ) {
    class GCLPAS_Functions
    {
        public function __construct() {
            add_action('admin_enqueue_scripts', array( $this, 'gclpas_admin_enqueue_scripts' ));
            add_action('admin_menu', array( $this, 'gclpas_admin_menu' ));
            add_action('admin_init', array( $this, 'gclpas_admin_init' ));
            
            /** Ajax for author list */
            add_action('wp_ajax_gclpas_author_list', array( $this, 'gclpas_author_list_ajax_callback' ));
            add_action('wp_ajax_nopriv_gclpas_author_list', array( $this, 'gclpas_author_list_ajax_callback' ));

            /** User edit page hooks */
            add_action('edit_user_profile', array( $this, 'gclpas_user_profile_edit_action'), 999 );
            add_action('edit_user_profile_update', array( $this, 'gclpas_user_profile_update_action'), 999 );
        }

        public function gclpas_admin_enqueue_scripts($hook) {
            if($hook == 'toplevel_page_gclpas-settings') {
                wp_enqueue_style( "gclpas_admin_select2", GCLPAS_PLUGIN_URL . "/admin/assets/css/select2.min.css", '', GCLPAS_BUILD);
                wp_enqueue_style( "gclpas_admin_style", GCLPAS_PLUGIN_URL . "/admin/assets/css/admin-style.css", '', GCLPAS_BUILD);

                wp_enqueue_script( 'gclpas_admin_select2', GCLPAS_PLUGIN_URL.'/admin/assets/js/select2.min.js', array('jquery'), GCLPAS_BUILD);
                wp_enqueue_script( 'gclpas_admin_script', GCLPAS_PLUGIN_URL.'/admin/assets/js/admin-script.js', array('jquery'), GCLPAS_BUILD);
                wp_localize_script( 'gclpas_admin_script', 'gclpasObj', [ 'ajaxurl' => admin_url('admin-ajax.php') ] );
            }
        }

        public function gclpas_admin_menu() {
            add_menu_page(
                __('Post Author Switcher','post-author-switcher'),
                __('Post Author Switcher','post-author-switcher'),
                'manage_options',
                'gclpas-settings',
                array($this,'gclpas_settings_html'),
            );
        }

        public function gclpas_settings_html() {
            require_once('admin/settings.php');
        }

        public function gclpas_admin_init() {
            register_setting('gclpas-all-settings','gclpas_options');
        }

        public function gclpas_author_list_ajax_callback() {
            $result = array();
            $search = $_POST['search'];

            $gclpas_get_author = get_users(array(
                'search'           => '*'.$search.'*',
                'search_columns'   => array( 'ID', 'user_login', 'user_email', 'display_name' ),
                'capability__in'   => 'edit_posts'
            ));

            foreach ($gclpas_get_author as $gclpas_author) {		

                $result[] = array(
                    'id' => $gclpas_author->data->ID,
                    'title' => $gclpas_author->data->display_name . "(#" . $gclpas_author->data->ID . ")"
                );

                echo json_encode($result);

            }
            
            wp_die();
        }

        public function gclpas_user_profile_edit_action() {
            if(!isset($_GET["user_id"]))  return; ?>
            <script>
                /** Check all checkbox */
                jQuery("#gclpas_post_type").click(function () {
                    jQuery('input[name="gclpas_post_type[]"]:checkbox').not(this).prop('checked', this.checked);
                });
            </script>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="gclpas_switch_author_status"><?php echo esc_html('Switch Post Author','post-author-switcher'); ?></label></th>
                        <td>
                            <input name="gclpas_switch_author_status" id="gclpas_switch_author_status" type="checkbox" id="artwork_approved" value="1">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="gclpas_new_post_author"><?php echo esc_html('Select New Post Author','post-author-switcher'); ?></label></th>
                        <td>
                            <?php
                            $get_user_args = array( 'capability__in'   => 'edit_posts' );
                            if(isset($_GET["user_id"])) {
                                $get_user_args = array_merge( array( 'exclude' => array( $_GET["user_id"] ) ), $get_user_args );
                            }
                            $gclpas_get_author = get_users( $get_user_args );
                            ?>
                            <select name="gclpas_new_post_author" id="gclpas_new_post_author">
                                <option value=""> --- Select --- </option>
                                <?php
                                foreach ($gclpas_get_author as $gclpas_author) { ?>
                                    <option value="<?php echo esc_attr($gclpas_author->data->ID); ?>"><?php echo esc_attr($gclpas_author->data->display_name); ?></option>
                                    <?php
                                } ?>
                            </select>
                        </td>
                    </tr>
                    <?php
                    $post_types = get_post_types(array('public' => true));
                    unset($post_types['attachment']); ?>
                    <tr>
                        <th scope="row">
                            <label for=""><?php echo esc_html('Select Post Type','post-author-switcher'); ?></label>
                        </th>
                        <td>
                            <label class="gclpas-containercheckbox">
                                <input type="checkbox" id="gclpas_post_type" name="gclpas_post_type[]"><?php echo esc_html('All','post-author-switcher'); ?>
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
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php 
        }

        public function gclpas_user_profile_update_action($user_id) {

            $status_switch_author = (isset($_POST["gclpas_switch_author_status"]) && !empty($_POST["gclpas_switch_author_status"])) ? 1 : 0;

            if(isset($status_switch_author) && !empty($status_switch_author)) {

                $new_author = (isset($_POST["gclpas_new_post_author"]) && !empty($_POST["gclpas_new_post_author"])) ? $_POST["gclpas_new_post_author"] : [];
                $post_type = (isset($_POST["gclpas_post_type"]) && !empty($_POST["gclpas_post_type"])) ? $_POST["gclpas_post_type"] : [];
                $author_from = (isset($user_id) && !empty($user_id)) ? $user_id : '';

                global $wpdb;
                if(!empty($new_author) && !empty($post_type) && !empty($author_from)) {
                    $sql_post_type = array_map( function($type) { return "post_type = '$type'"; }, $post_type );

                    $query_post_type = implode(" OR ",$sql_post_type);

                    $update_query = $wpdb->query( 
                        $wpdb->prepare( "UPDATE $wpdb->posts SET post_author = %d WHERE ( $query_post_type ) AND ( post_status != 'auto-draft' OR post_status != 'inherit' ) AND ( post_author = $author_from )", $new_author ),
                    );
                }
            }
        }
    }
    new GCLPAS_Functions();
}