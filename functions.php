<?php
if( !class_exists('GCLPAS_Functions') ) {
    class GCLPAS_Functions
    {
        public function __construct() {
            add_action( 'admin_enqueue_scripts', array( $this, 'gclpas_admin_enqueue_scripts' ), PHP_INT_MAX );
            add_action( 'admin_menu', array( $this, 'gclpas_admin_menu' ));
            add_action( 'admin_init', array( $this, 'gclpas_admin_init' ));
            add_filter( 'admin_body_class', array( $this, 'gclpas_admin_classes' ));
            
            /** Ajax for author list */
            add_action( 'wp_ajax_gclpas_author_list', array( $this, 'gclpas_author_list_ajax_callback' ));
            add_action( 'wp_ajax_nopriv_gclpas_author_list', array( $this, 'gclpas_author_list_ajax_callback' ));
        }

        public function gclpas_admin_enqueue_scripts($hook) {
            if($hook == 'toplevel_page_gclpas-settings' || $hook == 'user-edit.php' || $hook == 'profile.php') {
                wp_register_style( "gclpas-admin-select2", GCLPAS_PLUGIN_URL . "/admin/assets/css/select2.min.css", '', GCLPAS_BUILD);
                wp_enqueue_style( "gclpas-admin-select2" );
                wp_enqueue_style( "gclpas-admin-style", GCLPAS_PLUGIN_URL . "/admin/assets/css/admin-style.css", '', GCLPAS_BUILD);

                wp_register_script( "gclpas-admin-select2", GCLPAS_PLUGIN_URL.'/admin/assets/js/select2.min.js', array('jquery'), GCLPAS_BUILD);
                wp_enqueue_script( "gclpas-admin-select2" );
                wp_enqueue_script( 'gclpas-admin-script', GCLPAS_PLUGIN_URL.'/admin/assets/js/admin-script.js', array('jquery'), GCLPAS_BUILD);
                wp_localize_script( 'gclpas-admin-script', 'gclpasObj', [ 'ajaxurl' => admin_url('admin-ajax.php'), 'ajaxnonce' => esc_attr( wp_create_nonce( 'gclpas_security_ajaxnonce' ) ) ] );
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
            register_setting( 'gclpas-all-settings', 'gclpas_options' );
        }

        public function gclpas_author_list_ajax_callback() {
            $result = array();
            $search = sanitize_text_field($_POST['search']);
            $exclude_author = (isset($_REQUEST['exclude']) && !empty($_REQUEST['exclude'])) ? sanitize_text_field($_REQUEST['exclude']) : "";

            if(wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ 'ajaxnonce' ] ) ), 'gclpas_security_ajaxnonce' )) {
                

                $gclpas_get_author = get_users(array(
                    'search'           => '*'.$search.'*',
                    'search_columns'   => array( 'ID', 'user_login', 'user_email', 'display_name' ),
                    'capability__in'   => 'edit_posts',
                    'exclude' =>  (isset($exclude_author) && !empty($exclude_author)) ? array( $exclude_author ) : array()
                ));

                foreach ($gclpas_get_author as $gclpas_author) {

                    $result[] = array(
                        'id' => $gclpas_author->data->ID,
                        'title' => $gclpas_author->data->display_name . "(#" . $gclpas_author->data->ID . ")"
                    );

                }
            }
            
            if($result) {
                echo html_entity_decode(esc_html(wp_json_encode($result)));
            }

            wp_die();
        }

        public function gclpas_admin_classes( $classes ) {
            $current_screen = get_current_screen();

            if($current_screen->base == 'profile' || $current_screen->base == 'user-edit' || $current_screen->base == 'toplevel_page_gclpas-settings') {
                $classes .= ' gclpas-page';
            }
        
            return $classes;
        }

    }
    new GCLPAS_Functions();
}