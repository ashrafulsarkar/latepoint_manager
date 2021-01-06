<?php
/*
Plugin Name: LatePoint Manager
Plugin URI: https://github.com/ashrafulsarkar/latepoint-manager
Description: LatePoint Manager is a new role for LatePoint - Appointment Booking & Reservation plugin. You can contronl pending Appointment Booking list and manage it.
Version: 1.0.0
Author: Ashraful Sarkar
Author URI: http://ashrafulsarkar.com
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: latepoint_manager
Domain Path: /languages/
*/

function mlate_load_textdomain(){
    load_plugin_textdomain("latepoint_manager", false, dirname(__FILE__) . "/languages");
}
add_action("plugins_loaded", "mlate_load_textdomain");

if (class_exists('LatePoint')) {
    add_action("init", function () {
        add_role(
            "manage_lpoint",
            esc_html__("LatePoint Manager", "latepoint_manager"),
            array(
                'manage_options' => true,
                'read' => true,
                'edit_posts' => true,
                'manage_latepoint' => true,
            )
        );
        
        if (get_role("manage_lpoint")) {
            $user = wp_get_current_user();
            $mlate_admin_url = basename(sanitize_text_field($_SERVER['REQUEST_URI']), '?' . sanitize_text_field($_SERVER['QUERY_STRING']));
            if ($user->has_cap('manage_latepoint')) {
                $mlate_page = $_GET['page'] ?? '';
                $mlate_page = sanitize_text_field($mlate_page);
                if ("latepoint" == $mlate_page) {
                    $mlate_routname = sanitize_text_field($_GET['route_name']);
                    if ($mlate_page == "latepoint" && $mlate_routname != "bookings__pending_approval") {
                        wp_redirect(admin_url() . "admin.php?page=latepoint&route_name=bookings__pending_approval");
                    }
                } elseif ($mlate_admin_url == 'admin.php' && $mlate_page != "latepoint") {
                    wp_redirect(admin_url() . "admin.php?page=latepoint&route_name=bookings__pending_approval");
                } elseif ($mlate_admin_url == 'options-general.php' || $mlate_admin_url == 'options-writing.php' || $mlate_admin_url == 'options-reading.php' || $mlate_admin_url == 'options-discussion.php' || $mlate_admin_url == 'options-media.php' || $mlate_admin_url == 'options-permalink.php' || $mlate_admin_url == 'options-privacy.php' || $mlate_admin_url == 'export-personal-data.php' || $mlate_admin_url == 'post-new.php' || $mlate_admin_url == 'post.php') {
                    wp_redirect(admin_url() . "admin.php?page=latepoint&route_name=bookings__pending_approval");
                }
            }
        }
    });

    //latepoint menu edit
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

    //admin menu edit
    function mlate_init_menus(){
        $user = wp_get_current_user();
        if ($user->has_cap('manage_latepoint')) {
            $GLOBALS['menu'] = array_filter($GLOBALS['menu'], function ($var) {
                $mlt_menus = ($var['2'] == 'index.php');
                $mlt_menus .= ($var['2'] == 'profile.php');
                $mlt_menus .= ($var['2'] == 'latepoint');
                return $mlt_menus;
            });
        }else{
            $GLOBALS['menu'];
        }
    }
    add_action('admin_init', 'mlate_init_menus');

    //enqueue style
    function mlate_enqueue_scripts(){
        $user = wp_get_current_user();
        if ($user->has_cap('manage_latepoint')) {
            wp_enqueue_style("mlate-css", plugin_dir_url(__FILE__) . "style.css");
        }
    }
    add_action("admin_enqueue_scripts", "mlate_enqueue_scripts");

    //translate admin text
    function mlate_gettext_admin($translated_text, $text_to_translate, $textdomain){
        if ('Administrator' == $text_to_translate) {
            $translated_text = esc_html__('LatePoint Manager', 'latepoint_manager');
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