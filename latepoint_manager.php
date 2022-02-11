<?php
/*
Plugin Name: LatePoint Manager
Plugin URI: https://github.com/ashrafulsarkar/latepoint-manager
Description: LatePoint Manager is a new role for LatePoint - Appointment Booking & Reservation plugin. You can contronl pending Appointment Booking list and manage it.
Version: 1.2.0
Author: Ashraful Sarkar
Author URI: http://ashrafulsarkar.com
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: latepoint_manager
Domain Path: /languages/
*/
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('LATEPOINT_MANAGER_ADDON_VER', '1.2.0');
define('LATEPOINT_MANAGER_ADDON_REQUIRE_VER', '3.0.5');

/**
 * plugin activation hook
 */
function latepoint_manager_activate()
{
    if (get_role("manage_lpoint")) {
        $role = get_role('manage_lpoint');
        $role->add_cap('manage_options');
        $role->add_cap('edit_posts');
    }
}
register_activation_hook(__FILE__, 'latepoint_manager_activate');

/**
 * plugin deactivation hook
 */
function latepoint_manager_deactivate()
{
    if (get_role("manage_lpoint")) {
        $role = get_role('manage_lpoint');
        $role->remove_cap('manage_options');
        $role->remove_cap('edit_posts');
    }
}
register_deactivation_hook(__FILE__, 'latepoint_manager_deactivate');

/**
 * Class LatePoint_Manager_Addon_Preload
 */
class LatePoint_Manager_Addon_Preload
{

    /**
     * LatePoint_Manager_Addon_Preload constructor.
     */
    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'mlate_load_textdomain'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('init', array($this, 'load_main_plugin'), 99);
    }

    public function load_main_plugin()
    {
        if (class_exists('LatePoint')) {
            add_action('admin_init', array($this, 'LatePoint_Manager_load'));
            add_action('admin_init', array($this, 'lp_m_latepoint_main_plugin_reactive'));
        }
        if (!class_exists('LatePoint')) {
            add_action('admin_init', array($this, 'lp_m_latepoint_main_plugin_deactive'));
        }
    }

    /**
     * Admin notice
     */
    public function admin_notices()
    {
?>
        <div class="error">
            <p><?php echo wp_kses(
                    sprintf(
                        __('<strong>%s</strong> addon version %s requires %s version %s or higher is <strong>installed</strong> and <strong>activated</strong>.', 'latepoint_manager'),
                        __('LatePoint Manager', 'latepoint_manager'),
                        LATEPOINT_MANAGER_ADDON_VER,
                        sprintf('<a href="%s" target="_blank"><strong>%s</strong></a>', esc_attr('https://codecanyon.net/item/latepoint-appointment-booking-reservation-plugin-for-wordpress/22792692'), __('LatePoint', 'latepoint_manager')),
                        LATEPOINT_MANAGER_ADDON_REQUIRE_VER
                    ),
                    array(
                        'a'      => array(
                            'href'  => array(),
                            'target' => array('_blank')
                        ),
                        'strong' => array()
                    )
                ); ?>
            </p>
        </div>
<?php
    }

    /**
     * Plugin Load TextDomain
     */
    public function mlate_load_textdomain()
    {
        load_plugin_textdomain("latepoint_manager", false, dirname(__FILE__) . "/languages");
    }

    /**
     * Plugin Load 
     */
    public function LatePoint_Manager_load()
    {
        remove_action('admin_notices', array($this, 'admin_notices'));

        add_action("admin_init", function () {
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
        });

        if (get_role("manage_lpoint")) {
            $user = wp_get_current_user();
            $mlate_admin_url = basename(sanitize_text_field($_SERVER['REQUEST_URI']), '?' . sanitize_text_field($_SERVER['QUERY_STRING']));

            if ($user->has_cap('manage_latepoint')) {
                $mlate_page = '';
                if (sanitize_text_field(isset($_GET['page']))) {
                    $mlate_page = sanitize_text_field($_GET['page']);
                }
                if ("latepoint" == $mlate_page) {
                    $mlate_routname = sanitize_text_field($_GET['route_name']);
                    if ($mlate_page == "latepoint" && $mlate_routname != "bookings__pending_approval") {
                        wp_redirect(admin_url() . "admin.php?page=latepoint&route_name=bookings__pending_approval");
                    }
                }
                if ($mlate_admin_url == 'admin.php' && $mlate_page != "latepoint") {
                    wp_redirect(admin_url() . "admin.php?page=latepoint&route_name=bookings__pending_approval");
                }
                if ($mlate_admin_url == 'options-general.php' || $mlate_admin_url == 'options-writing.php' || $mlate_admin_url == 'options-reading.php' || $mlate_admin_url == 'options-discussion.php' || $mlate_admin_url == 'options-media.php' || $mlate_admin_url == 'options-permalink.php' || $mlate_admin_url == 'options-privacy.php' || $mlate_admin_url == 'export-personal-data.php' || $mlate_admin_url == 'post-new.php' || $mlate_admin_url == 'post.php') {
                    wp_redirect(admin_url() . "admin.php?page=latepoint&route_name=bookings__pending_approval");
                }
            }

            /**
             * remove some deshboard widget
             */
            if ($user->has_cap('manage_latepoint')) {
                function filter_wp_dashboard_widgets()
                {
                    remove_meta_box( 'dashboard_plugins','dashboard','normal' ); // Plugins
                    remove_meta_box( 'dashboard_right_now','dashboard', 'normal' ); // Right Now
                    remove_action( 'welcome_panel','wp_welcome_panel' ); // Welcome Panel
                    remove_action( 'try_gutenberg_panel', 'wp_try_gutenberg_panel'); // Try Gutenberg
                    remove_meta_box('dashboard_quick_press','dashboard','side'); // Quick Press widget
                    remove_meta_box('dashboard_recent_drafts','dashboard','side'); // Recent Drafts
                    remove_meta_box('dashboard_secondary','dashboard','side'); // Other WordPress News
                    remove_meta_box('dashboard_incoming_links','dashboard','normal'); //Incoming Links
                    remove_meta_box('rg_forms_dashboard','dashboard','normal'); // Gravity Forms
                    remove_meta_box('dashboard_recent_comments','dashboard','normal'); // Recent Comments
                    remove_meta_box('icl_dashboard_widget','dashboard','normal'); // Multi Language Plugin
                };

                // add the filter 
                add_filter('wp_dashboard_setup', 'filter_wp_dashboard_widgets', 10);
            }
        }


        //latepoint menu edit
        function mlate_menu_list($menus)
        {
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
        function mlate_init_menus()
        {
            $user = wp_get_current_user();
            if ($user->has_cap('manage_latepoint')) {
                $GLOBALS['menu'] = array_filter($GLOBALS['menu'], function ($var) {
                    $mlt_menus = ($var['2'] == 'index.php');
                    $mlt_menus .= ($var['2'] == 'profile.php');
                    $mlt_menus .= ($var['2'] == 'latepoint');
                    return $mlt_menus;
                });
            } else {
                $GLOBALS['menu'];
            }
        }
        add_action('admin_init', 'mlate_init_menus', 99);

        //enqueue style
        function mlate_enqueue_scripts()
        {
            $user = wp_get_current_user();
            if ($user->has_cap('manage_latepoint')) {
                wp_enqueue_style("mlate-css", plugin_dir_url(__FILE__) . "style.css");
            }
        }
        add_action("admin_enqueue_scripts", "mlate_enqueue_scripts");

        //translate admin text
        function mlate_gettext_admin($translated_text, $text_to_translate, $textdomain)
        {
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

    /**
     * LatePoint Deactive
     */
    public function lp_m_latepoint_main_plugin_deactive()
    {
        if (get_role("manage_lpoint")) {
            $role = get_role('manage_lpoint');
            $role->remove_cap('manage_options');
            $role->remove_cap('edit_posts');
        }
    }
    /**
     * LatePoint reactive
     */
    public function lp_m_latepoint_main_plugin_reactive()
    {
        if (get_role("manage_lpoint")) {
            $role = get_role('manage_lpoint');
            $role->add_cap('manage_options');
            $role->add_cap('edit_posts');
        }
    }
}

new LatePoint_Manager_Addon_Preload();
