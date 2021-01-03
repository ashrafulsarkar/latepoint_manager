<?php
/*
Plugin Name: LatePoint Manager
Plugin URI: https://github.com/ashrafulsarkar/latepoint-manager
Description: It's Create Only LatePoint - Appointment Booking & Reservation plugin manage a Role For pending User.
Version: 1.0
Author: Ashraful Sarkar
Author URI: http://ashrafulsarkar.com
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: lp_manager_role
Domain Path: /languages/
*/

function mlate_load_textdomain(){
    load_plugin_textdomain("lp_manager_role", false, dirname(__FILE__) . "/languages");
}
add_action("plugins_loaded", "mlate_load_textdomain");

if (class_exists('LatePoint')) {
    add_action("init", function () {
        add_role(
            "manage_lpoint",
            __("LatePoint Manager", "lp_manager_role"),
            array(
                'manage_options' => true,
                'manage_latepoint' => true,
            )
        );
        if (get_role("manage_lpoint")) {
            $user = wp_get_current_user();
            if ($user->has_cap('manage_latepoint')) {
                $mlate_page = $_GET['page'] ?? '';
                if ("latepoint" == $mlate_page) {
                    $mlate_routname = $_GET['route_name'];
                    if ($mlate_page == "latepoint" && $mlate_routname != "bookings__pending_approval") {
                        wp_redirect(admin_url() . "admin.php?page=latepoint&route_name=bookings__pending_approval");
                    }
                } elseif (is_admin()) {
                    wp_redirect(admin_url() . "admin.php?page=latepoint&route_name=bookings__pending_approval");
                }
            }
        }
    });
    function mlate_menu_list($menus){
        $user = wp_get_current_user();
        if ($user->has_cap('manage_latepoint')) {
            $menus = array_splice($menus, 2, 1);
            unset($menus['0']['children']['0']);
            unset($menus['0']['children']['2']);
            return $menus;
        } else {
            return $menus;
        }
    }
    add_filter('latepoint_side_menu', "mlate_menu_list");

    function mlate_enqueue_scripts(){
        $user = wp_get_current_user();
        if ($user->has_cap('manage_latepoint')) {
            wp_enqueue_style("mlate-css", plugin_dir_url(__FILE__) . "style.css");
        }
    }
    add_action("admin_enqueue_scripts", "mlate_enqueue_scripts");

    function mlate_gettext_admin($translated_text, $text_to_translate, $textdomain){
        if ('Administrator' == $text_to_translate) {
            $translated_text = __('LatePoint Manager', 'lp_manager_role');
        }
        return $translated_text;
    }
    add_action('admin_head', function () {
        $user = wp_get_current_user();
        if ($user->has_cap('manage_latepoint')) {
            add_filter('gettext', 'mlate_gettext_admin', 10, 3);
        }
    });
}
