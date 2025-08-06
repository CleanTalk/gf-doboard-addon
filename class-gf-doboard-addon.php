<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

GFForms::include_feed_addon_framework();
/**
 * CleanTalk doBoard Add-On for Gravity Forms
 *
 * @package     GravityForms
 * @subpackage  doBoard Add-On
 * @author      Cleantalk
 * @since       1.0
 */
class GFdoBoard_AddOn extends GFFeedAddOn {

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
     * Summary of api
     * @var
     */
    protected $api = null;

    /**
     * Plugin initialization
     *
     * @since  1.0
     * @access public
     */
    public function init() {
        parent::init();
        add_filter('gform_pre_validation_' . $this->_slug, array($this, 'fix_label_ids_setting'));
        add_filter('gform_pre_process_feed_settings_' . $this->_slug, array($this, 'fix_label_ids_setting'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('gform_after_submission', array($this, 'doboard_send_after_submission'), 10, 2);
        if (isset($_POST['_gform_setting_doBoard_label_ids']) && !is_array($_POST['_gform_setting_doBoard_label_ids'])) {
            $_POST['_gform_setting_doBoard_label_ids'] = array($_POST['_gform_setting_doBoard_label_ids']);
        }

        add_action('wp_ajax_gf_doboard_get_projects', function(){
            $account_id = sanitize_text_field($_POST['account_id']);
            $session_id = sanitize_text_field($_POST['session_id']);
            $addon = GFdoBoard_AddOn::get_instance();
            $projects = $addon->get_projects_for_feed_setting($account_id, $session_id);
            wp_send_json_success($projects);
        });

        add_action('wp_ajax_gf_doboard_get_task_boards', function(){
            $account_id = sanitize_text_field($_POST['account_id']);
            $session_id = sanitize_text_field($_POST['session_id']);
            $project_id = sanitize_text_field($_POST['project_id']);
            $addon = GFdoBoard_AddOn::get_instance();
            $boards = $addon->get_task_boards_for_feed_setting($account_id, $session_id, $project_id);
            wp_send_json_success($boards);
        });

        add_action('wp_ajax_gf_doboard_get_labels', function(){
            $account_id = sanitize_text_field($_POST['account_id']);
            $session_id = sanitize_text_field($_POST['session_id']);
            $addon = GFdoBoard_AddOn::get_instance();
            $labels = $addon->get_labels_for_feed_setting($account_id, $session_id);
            wp_send_json_success($labels);
        });
    }

    /**
     * Handles the sending of data to doBoard after form submission.
     *
     * @since  1.0
     * @access public
     *
     * @param array $entry The entry data.
     * @param array $form  The form data.
     */
    public function doboard_send_after_submission( $entry, $form ) {
        if ( isset( $entry['status'] ) && $entry['status'] === 'spam' ) {
            return;
        }
        $this->doboard_send( $entry, $form );
    }

    public function enqueue_admin_styles() {
        wp_enqueue_style(
            'gf-doboard-admin',
            plugins_url( '/public/gf-doboard-admin.css', __FILE__ ),
            array(),
            $this->_version
        );
        wp_enqueue_script(
            'gf-doboard-admin-js',
            plugins_url( '/public/gf-doboard-admin.js', __FILE__ ),
            array('jquery'),
            $this->_version,
            true
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
                'description' => wp_kses_post(
                    "<a href='https://doboard.com/' target='_blank' style='font-weight: bold;'>" . esc_html__( "doBoard.com", 'gf-doboard-addon' ) . "</a> "
                    . esc_html__( 'is an online task management app that helps you convert messages submitted through forms into actionable tasks.', 'gf-doboard-addon' )
                    . " <a href='https://doboard.com/' target='_blank'>" . esc_html__( 'Learn more', 'gf-doboard-addon' ) . "</a>"
                ),
                'fields' => array(
                    array(
                        'name'     => 'doBoard_user_token',
                        'label'    => esc_html__( 'User Token', 'gf-doboard-addon' ),
                        'type'     => 'text',
                        'class'    => 'medium',
                        'required' => true,
                        'tooltip'  => esc_html__( 'Enter your doBoard user token.', 'gf-doboard-addon'),
                        'description' => wp_kses_post(
                            esc_html__( 'You can find your user token in your', 'gf-doboard-addon' ) .
                            " <a href='https://cleantalk.org/my/profile' target='_blank'>" . esc_html__( 'doBoard account settings.', 'gf-doboard-addon' ) . "</a>"
                        ),
                    ),
                    array(
                        'type'     => 'save',
                        'messages' => array(
                            'success' => esc_html__( 'Settings updated.', 'gf-doboard-addon' ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Define feed settings fields.
     *
     * @since  1.0
     * @access public
     * @return array The settings fields associated with feeds for the Breeze Add-On
     */
    public function feed_settings_fields() {
        $current_settings = $this->get_current_settings();
        $auth_data = $this->get_auth_data_for_feed_fields();
        $accounts = $this->get_accounts_for_feed_setting();
        $default_account_id = '';
        foreach ($accounts as $acc) {
            if (!empty($acc['value'])) {
                $default_account_id = $acc['value'];
                break;
            }
        }
        $feed_account_id = $this->get_setting('doBoard_account_id');
        $selected_account_id = rgpost('feed_setting_doBoard_account_id') ?: $feed_account_id ?: $default_account_id;

        return array(
            array(
                'title'  => esc_html__( 'doBoard Feeds', 'gf-doboard-addon' ),
                'fields' => array(
                    array(
                        'name'      => 'feed_name',
                        'label'     => esc_html__( 'Feed Name', 'gf-doboard-addon' ),
                        'type'      => 'text',
                        'class'     => 'medium',
                        'required'  => true,
                        'tooltip'   => esc_html__( 'Enter a name to identify this feed.', 'gf-doboard-addon' ),
                    ),
                    array(
                        'name'     => 'doBoard_account_id',
                        'label'    => esc_html__( 'Account ID', 'gf-doboard-addon' ),
                        'type'     => 'select',
                        'choices'  => $accounts,
                        'class'    => 'medium',
                        'required' => true,
                        'default_value' => $default_account_id,
                        'value'    => $selected_account_id,
                        'tooltip'  => esc_html__( 'Select the doBoard account to which the tasks will be sent.', 'gf-doboard-addon'),
                    ),
                    array(
                        'name'     => 'doBoard_project_id',
                        'label'    => esc_html__( 'Project ID', 'gf-doboard-addon' ),
                        'type'     => 'select',
                        'choices'  => $this->get_projects_for_feed_setting($selected_account_id, isset($auth_data['session_id']) ? $auth_data['session_id'] : ''),
                        'class'    => 'medium',
                        'required' => true,
                        'tooltip'  => esc_html__( 'Enter the doBoard project ID where tasks will be created.', 'gf-doboard-addon'),
                    ),
                    array(
                        'name'     => 'doBoard_task_board_id',
                        'label'    => esc_html__( 'Task Board ID', 'gf-doboard-addon' ),
                        'type'     => 'select',
                        'choices'  => $this->get_task_boards_for_feed_setting($selected_account_id, isset($auth_data['session_id']) ? $auth_data['session_id'] : ''),
                        'class'    => 'medium',
                        'required' => false,
                        'tooltip'  => esc_html__( 'Select the doBoard task board where tasks will be created.', 'gf-doboard-addon'),
                    ),
                    array(
                        'name'     => 'doBoard_label_ids',
                        'label'    => esc_html__( 'Label IDs', 'gf-doboard-addon' ),
                        'type'     => 'select',
                        'choices'  => $this->get_labels_for_feed_setting($selected_account_id, isset($auth_data['session_id']) ? $auth_data['session_id'] : ''),
                        'class'    => 'medium',
                        'multiple' => true,
                        'required' => false,
                        'tooltip'  => esc_html__( 'Select one or more doBoard labels to assign to the tasks.', 'gf-doboard-addon'),
                    ),
                    array(
                        'name'  => 'doBoard_session_id',
                        'type'  => 'hidden',
                        'value' => isset($auth_data['session_id']) ? $auth_data['session_id'] : '',
                    ),
                    array(
                        'name'  => 'doBoard_user_id',
                        'type'  => 'hidden',
                        'value' => isset($auth_data['user_id']) ? $auth_data['user_id'] : '',
                    ),
                    array(
                        'name'  => 'doBoard_email',
                        'type'  => 'hidden',
                        'value' => isset($auth_data['email']) ? $auth_data['email'] : '',
                    ),
                ),
            ),
        );
    }

    public function initialize_api() {
        if ( is_object( $this->api ) ) {
            return true;
        }
        if ( ! class_exists( 'GF_doBoard_API' ) ) {
            require_once( 'includes/class-gf-doboard-api.php' );
        }
        $user_token = $this->get_plugin_setting('doBoard_user_token');
        if ( empty( $user_token ) ) {
            return false;
        }
        $doBoard = new GF_doBoard_API();
        $auth_result = $doBoard->auth($user_token);
        if ( !empty($auth_result['data']['accounts']) ) {
            $this->api = $doBoard;
            return true;
        }
        return false;
    }

    protected function get_accounts_for_feed_setting() {
        $choices = array(
            array(
                'label' => esc_html__( 'Select an account', 'gf-doboard-addon' ),
                'value' => '',
            ),
        );
        $user_token = $this->get_plugin_setting('doBoard_user_token');
        if (!$user_token) {
            return $choices;
        }
        if ( ! class_exists( 'GF_doBoard_API' ) ) {
            require_once( 'includes/class-gf-doboard-api.php' );
        }
        $doBoard = new GF_doBoard_API();
        $auth_result = $doBoard->auth($user_token);
        if (!empty($auth_result['data']['accounts']) && is_array($auth_result['data']['accounts'])) {
            foreach ($auth_result['data']['accounts'] as $acc) {
                $choices[] = array(
                    'label' => $acc['org_name'],
                    'value' => $acc['account_id'],
                );
            }
        }
        return $choices;
    }

    protected function get_auth_data_for_feed_fields() {
        $user_token = $this->get_plugin_setting('doBoard_user_token');
        if (!$user_token) {
            return array();
        }
        if ( ! class_exists( 'GF_doBoard_API' ) ) {
            require_once( 'includes/class-gf-doboard-api.php' );
        }
        $doBoard = new GF_doBoard_API();
        $auth_result = $doBoard->auth($user_token);
        if (!empty($auth_result['data'])) {
            return array(
                'session_id' => isset($auth_result['data']['session_id']) ? $auth_result['data']['session_id'] : '',
                'user_id'    => isset($auth_result['data']['user_id']) ? $auth_result['data']['user_id'] : '',
                'email'      => isset($auth_result['data']['email']) ? $auth_result['data']['email'] : '',
            );
        }
        return array();
    }

    protected function get_projects_for_feed_setting($account_id, $session_id) {
        $choices = array(
            array(
                'label' => esc_html__( 'Select a project', 'gf-doboard-addon' ),
                'value' => '',
            ),
        );
        if (!$account_id || !$session_id) {
            return $choices;
        }
        if ( ! class_exists( 'GF_doBoard_API' ) ) {
            require_once( 'includes/class-gf-doboard-api.php' );
        }
        $doBoard = new GF_doBoard_API();
        $projects = $doBoard->get_projects($account_id, $session_id);

        if (!empty($projects) && is_array($projects)) {
            foreach ($projects as $project) {
                $choices[] = array(
                    'label' => $project['name'],
                    'value' => $project['project_id'],
                );
            }
        }
        return $choices;
    }

    protected function get_task_boards_for_feed_setting($account_id, $session_id, $project_id = null) {
        $choices = array(
            array(
                'label' => esc_html__( 'Select a task board', 'gf-doboard-addon' ),
                'value' => '',
            ),
        );
        if (!$account_id || !$session_id) {
            return $choices;
        }
        if ( ! class_exists( 'GF_doBoard_API' ) ) {
            require_once( 'includes/class-gf-doboard-api.php' );
        }
        $doBoard = new GF_doBoard_API();
        $task_boards = $doBoard->get_task_boards($account_id, $session_id, $project_id);

        if (!empty($task_boards) && is_array($task_boards)) {
            foreach ($task_boards as $board) {
                $choices[] = array(
                    'label' => $board['name'],
                    'value' => $board['track_id'],
                );
            }
        }
        return $choices;
    }

    protected function get_labels_for_feed_setting($account_id, $session_id) {
        $choices = array(
            array(
                'label' => esc_html__( 'Select labels', 'gf-doboard-addon' ),
                'value' => '',
            ),
        );
        if (!$account_id || !$session_id) {
            return $choices;
        }
        if ( ! class_exists( 'GF_doBoard_API' ) ) {
            require_once( 'includes/class-gf-doboard-api.php' );
        }
        $doBoard = new GF_doBoard_API();
        $labels = $doBoard->get_labels($account_id, $session_id);

        if (!empty($labels) && is_array($labels)) {
            foreach ($labels as $label) {
                $choices[] = array(
                    'label' => $label['name'],
                    'value' => $label['label_id'],
                );
            }
        }
        return $choices;
    }

    public function can_create_feed() {
        $result = $this->initialize_api();
        return $result;
    }

    public function doboard_send ( $entry, $form ) {
        try {
            $task_id = $this->doboard_add_task($entry, $form);
        } catch (\Exception $e) {
            $this->initialize_api();
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
    public function doboard_add_task( $entry, $form ) {
        if ( ! class_exists( 'GF_doBoard_API' ) ) {
            require_once plugin_dir_path(__FILE__) . 'includes/class-gf-doboard-api.php';
        }

        // Getting settings
        $feeds = $this->get_feeds( $form['id'] );
        $settings = array();
        foreach ( $feeds as $feed ) {
            if ( $this->is_feed_condition_met( $feed, $form, $entry ) ) {
                $settings = $feed['meta'];
                break;
            }
        }

        $project        = $settings['doBoard_project_id'];
        $session_id     = $settings['doBoard_session_id'];
        $user_id        = $settings['doBoard_user_id'];
        $account_id     = $settings['doBoard_account_id'];
        $task_board_id  = $settings['doBoard_task_board_id'];
        $doBoard_label  = $settings['doBoard_label_ids'];

        // Adding label_ids to the array if it is a string
        if (!is_array($doBoard_label) && !empty($doBoard_label)) {
            $doBoard_label = array_map('trim', explode(',', $doBoard_label));
        } elseif (empty($doBoard_label)) {
            $doBoard_label = array();
        }

        $fields_string = $this->doboard_get_entry_fields_string($entry, $form, 'title_name', " ");
        $title_name = mb_substr($fields_string, 0, 100) . (mb_strlen($fields_string) > 15 ? '...' : '');

        $data = array(
            'session_id' => $session_id,
            'name'       => $title_name,
            'user_id'    => $user_id,
            'project_id' => $project,
            'track_id'   => $task_board_id,
            'label_ids'  => $doBoard_label,
        );

        $doBoard = new GF_doBoard_API();
        try {
            $add_task_doBoard_result = $doBoard->add_task($data, $account_id);
        } catch (Exception $e) {
            throw $e;
        }

        if ( is_wp_error( $add_task_doBoard_result ) ) {
            error_log( __METHOD__ . '(): Error sending data to doBoard: ' . $add_task_doBoard_result->get_error_message() );
        }

        return $add_task_doBoard_result;
    }

    /**
     * Summary of doboard_add_comment
     * @param mixed $task_id
     * @param mixed $entry
     * @param mixed $form
     * @return void
     */
    public function doboard_add_comment( $task_id, $entry, $form ) {
        $feeds = $this->get_feeds( $form['id'] );
        $settings = array();
        foreach ( $feeds as $feed ) {
            if ( $this->is_feed_condition_met( $feed, $form, $entry ) ) {
                $settings = $feed['meta'];
                break;
            }
        }
        $project    = $settings['doBoard_project_id'];
        $session_id = $settings['doBoard_session_id'];
        $comment    = $this->doboard_get_entry_fields_string($entry, $form, 'comment', "<br>");
        $account_id = $settings['doBoard_account_id'];

        $data = array(
            'session_id' => $session_id,
            'task_id'    => $task_id,
            'comment'    => $comment,
            'project_id' => $project,
        );

        $doBoard = new GF_doBoard_API();
        $add_comment_doBoard_result = $doBoard->add_comment($data, $account_id);

        if ( is_wp_error( $add_comment_doBoard_result ) ) {
            error_log( __METHOD__ . '(): Error sending data to doBoard: ' . $add_comment_doBoard_result->get_error_message() );
        }
    }

    protected function doboard_get_entry_fields_string( $entry, $form, $type_string, $separator = "<br>" ) {
        $result = [];
        $used_values = [];
        if ($type_string === 'comment') {
            if (!empty($entry['source_url'])) {
                $result[] = 'URL Page: ' . esc_html($entry['source_url']);
            }
            if (!empty($entry['ip'])) {
                $result[] = 'IP User: ' . esc_html($entry['ip']);
            }
            foreach ($form['fields'] as $field) {
                if (isset($entry[$field->id]) && !is_array($entry[$field->id]) && trim($entry[$field->id]) !== '') {
                    $value = trim($entry[$field->id]);
                    if (!in_array($value, $used_values, true)) {
                        $result[] = $field->label . ': ' . $value;
                        $used_values[] = $value;
                    }
                }
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
            }
        } elseif ($type_string === 'title_name') {
            foreach ($form['fields'] as $field) {
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

    /**
     * Returns the columns for the feed list.
     * @return array{feed_name: mixed}
     */
    public function feed_list_columns() {
        return array(
            'feed_name' => esc_html__( 'Feed Name', 'gf-doboard-addon' ),
        );
    }

    /**
     * Returns the value for the feed name column.
     *
     * @param array $feed The feed data.
     * @return string The feed name or a placeholder if not set.
     */
    public function get_column_value_feed_name( $feed ) {
        return rgar( $feed['meta'], 'feed_name' ) ? $feed['meta']['feed_name'] : esc_html__( '(No name)', 'gf-doboard-addon' );
    }

}
