<?php
/**
 * Class CleantalkDoboardAddonForGravityFormsBootstrap
 * Handles the loading of the doBoard Add-On and registers with the Add-On Framework.
 */
class CleantalkDoboardAddonForGravityFormsBootstrap {

    /**
     * If the Add-On Framework exists, doBoard Add-On is loaded.
     * @access public
     * @static
     */
    public static function load() {
        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once(CLEANTALK_DOBOARD_ADDON_FOR_GRAVITY_FORMS__MAIN_CLASS_PATH);
        GFAddOn::register('CleantalkDoboardAddonForGravityForms');
    }
}
