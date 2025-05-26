<?php
if ( ! defined( 'ABSPATH' ) ) {
    die();
}

/**
 * Plugin Name: Gravity Forms doBoard Add-On
 * Plugin URI: https://www.gravityforms.com
 * Description: Integrates Gravity Forms with doBoard, сreates tasks in the doBoard task board for each submission of forms
 * Version: 1.0
 * Author: cleantalk
 * Author URI: https://www.cleantalk.org
 * Text Domain: gf-doboard-addon
 * Domain Path: /languages
 */

define( 'GF_DOBOARD_VERISON', '1.0' );

// If Gravity Forms is loaded, bootstrap the doBoard Add-On.
add_action( 'gform_loaded', array( 'GF_doBoard_Bootstrap', 'load' ), 5 );

/**
 * Class GF_doBoard_Bootstrap
 * Handles the loading of the doBoard Add-On and registers with the Add-On Framework.
 */
class GF_doBoard_Bootstrap {

    /**
     * If the Add-On Framework exists, doBoard Add-On is loaded.
     * @access public
     * @static
     */
    public static function load() {
        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'class-gf-doboard-addon.php' );
        GFAddOn::register( 'GFdoBoard_AddOn' );
    }
}

/**
 * Returns the main instance of GFdoBoard_AddOn.
 * @see    GFdoBoard_AddOn::get_instance()
 * @return object GFdoBoard_AddOn
 */
function gf_doboard() {
    return GFdoBoard_AddOn::get_instance();
}
