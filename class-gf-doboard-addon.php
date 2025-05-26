<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

GFForms::include_addon_framework();
/**
 * Gravity Forms doBoard Add-On
 *
 * @package     GravityForms
 * @subpackage  doBoard Add-On
 * @author      Cleantalk
 * @since       1.0
 */
class GFdoBoard_AddOn extends GFAddOn {

	/**
	 * Defines the version of the Breeze Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version, defined from breeze.php
	 */
    protected $_version = GF_DOBOARD_VERISON;

    /**
     * Defines the minimum Gravity Forms version required for the Breeze Add-On.
     *
     * @since  1.0
     * @access protected
     * @var    string $_min_gravityforms_version Contains the minimum Gravity Forms version, defined from breeze.php
     */
    protected $_min_gravityforms_version = '2.5';

    /**
     * Defines the name of the Add-On.
     *
     * @since  1.0
     * @access protected
     * @var    string $_slug Contains the slug, defined from breeze.php
     */
    protected $_slug = 'gf-doboard-addon';

    /**
     * Defines the full path to the Add-On.
     *
     * @since  1.0
     * @access protected
     * @var    string $_path Contains the path, defined
     */
    protected $_path = 'gf-doboard-addon/gf-doboard-addon.php';

    /**
     * Defines the full path to the Add-On.
     *
     * @since  1.0
     * @access protected
     * @var    string $_full_path Contains the full path, defined
     */
    protected $_full_path = __FILE__;

    /**
     * Defines the name of the Add-On.
     *
     * @since  1.0
     * @access protected
     * @var    string $_title Contains the title, defined
     */
    protected $_title = 'doBoard Add-On';

    /**
     * Defines the name of the Add-On.
     *
     * @since  1.0
     * @access protected
     * @var    string $_short_title Contains the short title, defined
     */
    protected $_short_title = 'doBoard';

    /**
     * Defines the name of the Add-On.
     *
     * @since  1.0
     * @access protected
     * @var    string $_capabilities_settings_page Contains the capabilities settings page, defined
     */
    protected $_capabilities_settings_page = 'gravityforms_doboard';

    /**
     * Defines the name of the Add-On.
     *
     * @since  1.0
     * @access protected
     * @var    string $_capabilities Contains the capabilities, defined
     */
    protected $_capabilities = array( 'gravityforms_doboard' );

    /**
     * Defines the name of the Add-On.
     *
     * @since  1.0
     * @access protected
     * @var    string $_capabilities_feeds_page Contains the capabilities feeds page, defined
     */
    private static $_instance = null;

    /**
     * Plugin initialization
     *
     * @since  1.0
     * @access public
     */
    public function init() {
        parent::init();

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'gform_after_submission', array( $this, 'doboard_send' ), 10, 2 );
    }

    public function enqueue_admin_styles() {
        wp_enqueue_style(
            'gf-doboard-admin',
            plugins_url( '/public/gf-doboard-admin.css', __FILE__ ),
            array(),
            $this->_version
        );
    }

    /**
     * Returns the main instance of GFdoBoard_AddOn
     *
     * @since  1.0
     * @access public
     * @static
     *
     * @return object
     */
    public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}
        
		return self::$_instance;
	}

    /**
     * Returns the plugin settings fields
     *
     * @since  1.0
     * @access public
     *
     * @return array
     */
    public function plugin_settings_fields() {
        return array(
            array(
                'title'  => esc_html__( 'doBoard Settings', 'gf-doboard-addon' ),
                'fields' => array(
                    array(
                        'name'     => 'doBoard_email',
                        'label'    => esc_html__( 'Email', 'gf-doboard-addon' ),
                        'type'     => 'text',
                        'class'    => 'medium',
                        'required' => true,
                    ),
                    array(
                        'name'     => 'doBoard_password',
                        'label'    => esc_html__( 'Password', 'gf-doboard-addon' ),
                        'type'     => 'text',
                        'class'    => 'medium gf-doboard-password-field',
                        'required' => true,
                    ),
                    array(
                        'name'     => 'doBoard_company_id',
                        'label'    => esc_html__( 'Company ID', 'gf-doboard-addon' ),
                        'type'     => 'text',
                        'class'    => 'medium',
                        'required' => true,
                    ),
                    array(
                        'name'     => 'doBoard_project_id',
                        'label'    => esc_html__( 'Project ID', 'gf-doboard-addon' ),
                        'type'     => 'text',
                        'class'    => 'medium',
                        'required' => true,
                    ),
                    array(
                        'name'     => 'doBoard_task_board_id',
                        'label'    => esc_html__( 'Task Board ID', 'gf-doboard-addon' ),
                        'type'     => 'text',
                        'class'    => 'medium',
                        'required' => true,
                    ),
                    array(
                        'name'     => 'doBoard_label_ids',
                        'label'    => esc_html__( 'Label ID', 'gf-doboard-addon' ),
                        'type'     => 'text',
                        'class'    => 'medium',
                        'required' => false,
                    ),
                    array(
                        'name'     => 'doBoard_auth_callback',
                        'type'     => 'hidden',
                        'validation_callback' => array( $this, 'doboard_auth' ),
                        'required' => false,
                    ),
                    array(
						'type'              => 'save',
						'messages'          => array(
							'success' => esc_html__( 'DoBoard settings have been updated.', 'gf-doboard-addon' ),
						),
					),
                ),
            ),
        );
    }

    protected function get_doboard_settings() {
        return array(
            'doBoard_email'      => $this->get_plugin_setting('doBoard_email'),
            'doBoard_password'   => $this->get_plugin_setting('doBoard_password'),
            'doBoard_company_id'    => $this->get_plugin_setting('doBoard_company_id'),
            'doBoard_project_id'    => $this->get_plugin_setting('doBoard_project_id'),
            'doBoard_task_board_id' => $this->get_plugin_setting('doBoard_task_board_id'),
            'doBoard_label_ids'      => $this->get_plugin_setting('doBoard_label_ids'),
            'doBoard_user_token' => $this->get_plugin_setting('doBoard_user_token'),
            'doBoard_user_id'    => $this->get_plugin_setting('doBoard_user_id'),
            'doBoard_session_id' => $this->get_plugin_setting('doBoard_session_id'),
            'doBoard_session_time' => $this->get_plugin_setting('doBoard_session_time'),
        );
    }

    protected function set_doboard_settings( $settings ) {
        $current = $this->get_plugin_settings();
        if ( ! is_array( $current ) ) {
            $current = array();
        }
        $new = array_merge($current, $settings);
        $this->update_plugin_settings($new);
    }

    public function doboard_auth() {
        $email = '';
        $password = '';

        if (rgpost('gform-settings-save') === 'save') {
            $email = rgpost('_gform_setting_doBoard_email');
            $password = rgpost('_gform_setting_doBoard_password');
        } else if (empty($email) || empty($password)) {
            $email = $this->get_plugin_setting('doBoard_email');
            $password = $this->get_plugin_setting('doBoard_password');
        }

        if ($this->doboard_is_session_valid()) {
            return true;
        }

        if ( ! class_exists( 'GF_doBoard_API' ) ) {
            require_once( 'includes/class-gf-doboard-api.php' );
        }
        $data = array(
            'email'      => $email,
            'password'   => $password,
        );
        $doBoard = new GF_doBoard_API();
        $auth_result = $doBoard->auth($data);

        if (!empty($auth_result['data']['session_id'])) {
            $this->set_doboard_settings(array(
                'doBoard_session_id'   => $auth_result['data']['session_id'],
                'doBoard_user_id'      => $auth_result['data']['user_id'],
                'doBoard_user_token'   => $auth_result['data']['user_token'],
                'doBoard_session_time' => time(),
            ));
            return true;
        }

        return false;
    }

    protected function doboard_is_session_valid() {
        $session_id = $this->get_plugin_setting('doBoard_session_id');
        $session_time = $this->get_plugin_setting('doBoard_session_time');
        $lifetime = 3600;

        if (empty($session_id) || empty($session_time)) {
            return false;
        }

        if (time() - intval($session_time) > $lifetime) {
            return false;
        }

        return true;
    }

    public function doboard_send ($entry, $form) {
        try {
            $task_id = $this->doboard_add_task($entry, $form);
        } catch (\Exception $e) {
            $this->doboard_auth();
            $task_id = $this->doboard_add_task($entry, $form);
        }
        if (isset($task_id['data']['task_id'])) {
            $this->doboard_add_comment($task_id['data']['task_id'], $entry, $form);
        }
        return true;
    }

    /**
     * Handles the submission of the form and sends data to doBoard
     *
     * @since  1.0
     * @access public
     *
     * @param array $entry The entry data.
     * @param array $form  The form data.
     */
    public function doboard_add_task($entry, $form) {
        if ( ! class_exists( 'GF_doBoard_API' ) ) {
            require_once plugin_dir_path(__FILE__) . 'includes/class-gf-doboard-api.php';
        }
        $project        = $this->get_plugin_setting( 'doBoard_project_id' );
        $session_id     = $this->get_plugin_setting( 'doBoard_session_id' );
        $user_id        = $this->get_plugin_setting( 'doBoard_user_id' );
        $company_id     = $this->get_plugin_setting( 'doBoard_company_id' );
        $task_board_id  = $this->get_plugin_setting('doBoard_task_board_id');
        $doBoard_label  = $this->get_plugin_setting('doBoard_label_ids');
        $fields_string = $this->doboard_get_entry_fields_string($entry, $form, 'title_name', " ");
        $title_name = mb_substr($fields_string, 0, 100) . (mb_strlen($fields_string) > 15 ? '...' : '');

        $data = array(
            'session_id' => $session_id,
            'name'       => $title_name,
            'user_id'    => $user_id,
            'project_id' => $project,
            'track_id' => $task_board_id,
        );

        $doBoard = new GF_doBoard_API();
        try {
            $add_task_doBoard_result = $doBoard->add_task($data, $company_id);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Unauthorized') !== false) {
                $this->doboard_auth();
                $session_id = $this->get_plugin_setting( 'doBoard_session_id' );
                $user_id    = $this->get_plugin_setting( 'doBoard_user_id' );
                $data['session_id'] = $session_id;
                $data['user_id'] = $user_id;
                $add_task_doBoard_result = $doBoard->add_task($data, $company_id);
            } else {
                throw $e;
            }
        }

        if ( is_wp_error( $add_task_doBoard_result ) ) {
            error_log( __METHOD__ . '(): Error sending data to doBoard: ' . $add_task_doBoard_result->get_error_message() );
        }

        return $add_task_doBoard_result;
    }

    public function doboard_add_comment( $task_id, $entry, $form ) {
        $project    = $this->get_plugin_setting( 'doBoard_project_id' );
        $session_id = $this->get_plugin_setting( 'doBoard_session_id' );
        $comment = $this->doboard_get_entry_fields_string($entry, $form, 'comment', "<br>");
        $company_id = $this->get_plugin_setting( 'doBoard_company_id' );

        $data = array(
            'session_id' => $session_id,
            'task_id' => $task_id,
            'comment' => $comment,
            'project_id' => $project,
        );

        $doBoard = new GF_doBoard_API();
        $add_comment_doBoard_result = $doBoard->add_comment($data, $company_id);

        if ( is_wp_error( $add_comment_doBoard_result ) ) {
            error_log( __METHOD__ . '(): Error sending data to doBoard: ' . $add_comment_doBoard_result->get_error_message() );
        }
    }

    protected function doboard_get_entry_fields_string($entry, $form, $type_string, $separator = "<br>") {
        $result = [];
        $used_values = [];
        foreach ($form['fields'] as $field) {
            if ($type_string === 'comment') {
                // Для простых полей
                if (isset($entry[$field->id]) && !is_array($entry[$field->id]) && trim($entry[$field->id]) !== '') {
                    $value = trim($entry[$field->id]);
                    // Проверяем на дубли
                    if (!in_array($value, $used_values, true)) {
                        $result[] = $field->label . ': ' . $value;
                        $used_values[] = $value;
                    }
                }
                // Для составных полей (например, name)
                if (!empty($field->inputs) && is_array($field->inputs)) {
                    foreach ($field->inputs as $input) {
                        $input_id = (string)$input['id'];
                        if (isset($entry[$input_id]) && trim($entry[$input_id]) !== '') {
                            $value = trim($entry[$input_id]);
                            if (!in_array($value, $used_values, true)) {
                                $result[] = $input['label'] . ': ' . $value;
                                $used_values[] = $value;
                            }
                        }
                    }
                }

                if (!empty($entry['source_url'])) {
                    $result[] = 'URL Page: ' . esc_html($entry['source_url']);
                }
                if (!empty($entry['ip'])) {
                    $result[] = 'IP User: ' . esc_html($entry['ip']);
                }
            } elseif ($type_string === 'title_name') {
                if (
                    in_array($field->type, array('textarea', 'text', 'post_content', 'paragraph')) &&
                    isset($entry[$field->id]) &&
                    !empty($entry[$field->id])
                ) {
                    if (preg_match('/comment|comments|description|message|desc/i', $field->label)) {
                        return $entry[$field->id];
                    }
                    $result = $entry[$field->id];
                }
            }
        }
        return implode($separator, $result);
    }
}
