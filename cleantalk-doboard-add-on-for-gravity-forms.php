<?php
if ( ! defined( 'ABSPATH' ) ) {
    die();
}

/**
 * Plugin Name: doBoard Add-On for Gravity Forms
 * Plugin URI: https://www.gravityforms.com/add-ons/doboard-add-on-for-gravity-forms/
 * Description: Integrates Gravity Forms with doBoard, сreates tasks in the doBoard task board for each submission of forms
 * Version: 1.0.5
 * Author: CleanTalk Inc
 * Author URI: https://doboard.com/
 * Text Domain: cleantalk-doboard-add-on-for-gravity-forms
 * Domain Path: /languages
 * License: GPLv2 or later
 */

/**
 * Naming rules:
 * Basic name: cleantalk_doboard_addon_for_gravity_forms_%
 * Constants: CLEANTALK_DOBOARD_ADDON_FOR_GRAVITY_FORMS__%
 * Classes: CleantalkDoboardAddonForGravityForms%
 * Files: %cleantalk-doboard-addon-for-gravity-forms
 * Slug: cleantalk-doboard-add-on-for-gravity-forms
 */


define('CLEANTALK_DOBOARD_ADDON_FOR_GRAVITY_FORMS__VERISON', '1.0.5' );
define('CLEANTALK_DOBOARD_ADDON_FOR_GRAVITY_FORMS__API_CLASS_PATH', __DIR__ .'/includes/class-cleantalk-doboard-add-on-for-gravity-forms-api.php' );
define('CLEANTALK_DOBOARD_ADDON_FOR_GRAVITY_FORMS__MAIN_CLASS_PATH', __DIR__ . '/includes/class-cleantalk-doboard-add-on-for-gravity-forms.php' );
define('CLEANTALK_DOBOARD_ADDON_FOR_GRAVITY_FORMS__BOOTSTRAP_CLASS_PATH', __DIR__ . '/includes/class-cleantalk-doboard-add-on-for-gravity-forms-bootstrap.php' );

require_once(CLEANTALK_DOBOARD_ADDON_FOR_GRAVITY_FORMS__BOOTSTRAP_CLASS_PATH);
// If Gravity Forms is loaded, bootstrap the doBoard Add-On.
add_action('gform_loaded', array('CleantalkDoboardAddonForGravityFormsBootstrap', 'load' ), 5 );

/**
 * Returns the main instance of GFdoBoard_AddOn.
 * @return CleantalkDoboardAddonForGravityForms
 * @see    CleantalkDoboardAddonForGravityForms::get_instance()
 */
function cleantalk_doboard_addon_for_gravity_forms() {
    return CleantalkDoboardAddonForGravityForms::get_instance();
}
