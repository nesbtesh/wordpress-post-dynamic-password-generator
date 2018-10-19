<?php
/**
*   @package DynamicPasswordGenerator
*/

/**
*   Plugin Name: Post Dynamic Password Generator
*   Plugin URI: http://nesbtesh.github.io/wordpress-post-dynamic-password-generator.html
*   Description: Set the Password Protection for a page Dynamically
*   Version: 1.0.0
*   Author: Nessim Btesh
*   Author URI: http://nesbtesh.github.io
*   Licence GPLv2 or Later
*/


defined( 'ABSPATH' ) or die( 'Hey, what are you doing here? You silly human!' );

define('DYNAMIC_PASSWORD_PLUGIN_PATH', WP_PLUGIN_DIR . '/dynamic-password-protect/');


function get_cron_events() {
    $crons  = _get_cron_array();
    $events = array();
   
    foreach ( $crons as $time => $cron ) {
        foreach ( $cron as $hook => $dings ) {
            foreach ( $dings as $sig => $data ) {
                # This is a prime candidate for a Crontrol_Event class but I'm not bothering currently.
                $events[ "$hook-$sig-$time" ] = (object) array(
                    'hook'     => $hook,
                    'time'     => $time,
                    'sig'      => $sig,
                    'args'     => $data['args'],
                    'schedule' => $data['schedule'],
                    'interval' => isset( $data['interval'] ) ? $data['interval'] : null,
                );
            }
        }
    }
    return $events;
}

if (!class_exists('PassProtectDynamic')) {
    class PassProtectDynamic {

        public $plugin;

        function __construct() {
			$this->plugin = plugin_basename( __FILE__ );
		}

        function register() {
            add_action('admin_menu', array($this, 'add_admin_pages'));

            add_filter( "plugin_action_links_$this->plugin", array( $this, 'settings_link' ) );
		}

        public function settings_link( $links ) {
			$settings_link = '<a href="admin.php?page=dynamic_password_protect">Settings</a>';
			array_push( $links, $settings_link );
			return $links;
		}

        function add_admin_pages() {
            add_menu_page('Dynamic Password', 'Dynamic', 'manage_options', 'dynamic_password_protect', array($this, 'admin_index'), 'dashicons-admin-network', 110);
        }

        public function admin_index() {
            if( isset( $_POST['action'] ) && isset( $_POST['page-id'] ) ) {
                if ($_POST['action'] == "create") {
                    wp_schedule_event( time(), $_POST['hours'], 'wpse_change_pass_event',  array($_POST['page-id']) );
                 
                } elseif ($_POST['action'] == "remove") {
                    $events = get_cron_events();
                    foreach ( $events as $id => $event ) { 
                        if ($event->sig == $_POST['page-id']) {
                            wp_unschedule_event( $event->time, 'wpse_change_pass_event', $event->args );
                        }
                    }
                    
                }
                ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php _e( 'Succes your dynamic password has been set!', 'sample-text-domain' ); ?></p>
                    </div>
                <?php
            }
            require_once plugin_dir_path(__FILE__). 'templates/admin.php';

        }

        function activate() {
            flush_rewrite_rules();
        }

        function deactivate() {
            flush_rewrite_rules();
            wp_clear_scheduled_hook( 'wpse_change_pass_event' );
        }

        function uninstall() {

        }

        function setCronPasswordChange() {
            // if( isset( $_POST['create'] ) && isset( $_POST['page-id'] )) {
            //     register_activation_hook( __FILE__, function()
            //     {
            //         // Start the cron job:
            //         wp_schedule_event( time(), 'every_2nd_day', 'wpse_change_pass_event',   $_POST['page-id'] );
            //     });
            // }

        }

    }

}

// if (class_exists('PassProtectDynamic')) {
$passProtectDynamic = new PassProtectDynamic();
$passProtectDynamic->register();
// }

register_activation_hook(__FILE__, array($passProtectDynamic, 'activate'));

register_deactivation_hook(__FILE__, array($passProtectDynamic, 'deactivate'));

register_uninstall_hook(__FILE__, array($passProtectDynamic, 'uninstall'));


add_action( 'wpse_change_pass_event', function($id)
{

    $new_password = uniqid();
    global $wpdb;
    $wpdb->update(
        $wpdb->posts,
        array( 'post_password' =>  $new_password),
        array( 'id'     => $id    )
    );

    $args2 = array(
     'role' => 'administrator'
    );
    $authors = get_users($args2);
    $page_link = get_page_link($id);
    foreach ($authors as $user) {
        wp_mail($user->user_email , "Wordpress: Page Password has been change", "The new password for ".$page_link." is: ".$new_password);
    }
});




?>
