<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

GFForms::include_feed_addon_framework();
/**
 * doBoard Add-On for Gravity Forms
 *
 * @package     GravityForms
 * @subpackage  doBoard Add-On
 * @author      Cleantalk
 * @since       1.0
 */
class CleantalkDoboardAddonForGravityForms extends GFFeedAddOn {

	/**
	 * Defines the version of the Breeze Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version, defined from breeze.php
	 */
    protected $_version = CLEANTALK_DOBOARD_ADDON_FOR_GRAVITY_FORMS__VERISON;

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
    protected $_slug = 'cleantalk-doboard-add-on-for-gravity-forms';

    /**
     * Defines the full path to the Add-On.
     *
     * @since  1.0
     * @access protected
     * @var    string $_path Contains the path, defined
     */
    protected $_path = 'cleantalk-doboard-add-on-for-gravity-forms/cleantalk-doboard-add-on-for-gravity-forms.php';

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
     * @var array|false
     */
    private $task_data_to_send;
    /**
     * @var array|false
     */
    private $task_data_received;
    public $gravity_entry_id;

    /**
     * Plugin initialization
     *
     * @since  1.0
     * @access public
     */
    public function init() {
        parent::init();

        // Get the main plugin file path
        $plugin_dir = dirname( dirname( $this->_full_path ) );
        $main_plugin_file = $plugin_dir . '/' . basename( $this->_path );
        $plugin_basename = plugin_basename( $main_plugin_file );
        
        add_filter('plugin_action_links_' . $plugin_basename, array($this, 'add_plugin_action_links'));
        add_filter('plugin_row_meta', array($this, 'add_plugin_row_meta'), 10, 2);
        add_filter('gform_pre_validation_' . $this->_slug, array($this, 'fix_label_ids_setting'));
        add_filter('gform_pre_process_feed_settings_' . $this->_slug, array($this, 'fix_label_ids_setting'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_filter('gform_notification', array($this, 'interceptNotification'), 10, 3);
        add_action('gform_after_submission', array($this, 'saveEntryTaskMeta'), 10, 2);
        add_action('gform_entry_info', array($this, 'loadEntryTaskMeta'), 10, 2);


        add_action('wp_ajax_ct_gf_doboard_get_projects', function(){
            check_admin_referer( 'gform_settings_save', 'gform_settings_save_nonce' );
            if (!isset($_POST['account_id']) || !isset($_POST['session_id'])) {
                return;
            }
            $account_id = sanitize_text_field(wp_unslash($_POST['account_id']));
            $session_id = sanitize_text_field(wp_unslash($_POST['session_id']));
            $addon = CleantalkDoboardAddonForGravityForms::get_instance();
            $projects = $addon->get_projects_for_feed_setting($account_id, $session_id);
            wp_send_json_success($projects);
        });

        add_action('wp_ajax_ct_gf_doboard_get_task_boards', function(){
            check_admin_referer( 'gform_settings_save', 'gform_settings_save_nonce' );
            if (!isset($_POST['account_id']) || !isset($_POST['session_id']) || !isset($_POST['project_id'])) {
                return;
            }
            $account_id = sanitize_text_field(wp_unslash($_POST['account_id']));
            $session_id = sanitize_text_field(wp_unslash($_POST['session_id']));
            $project_id = sanitize_text_field(wp_unslash($_POST['project_id']));
            $addon = CleantalkDoboardAddonForGravityForms::get_instance();
            $boards = $addon->get_task_boards_for_feed_setting($account_id, $session_id, $project_id);
            wp_send_json_success($boards);
        });

        add_action('wp_ajax_ct_gf_doboard_get_labels', function(){
            check_admin_referer( 'gform_settings_save', 'gform_settings_save_nonce' );
            if (!isset($_POST['account_id']) || !isset($_POST['session_id'])) {
                return;
            }
            $account_id = sanitize_text_field(wp_unslash($_POST['account_id']));
            $session_id = sanitize_text_field(wp_unslash($_POST['session_id']));
            $addon = CleantalkDoboardAddonForGravityForms::get_instance();
            $labels = $addon->get_labels_for_feed_setting($account_id, $session_id);
            wp_send_json_success($labels);
        });
    }

    /**
     * Add Settings link to plugin action links
     *
     * @since  1.0.4
     * @access public
     *
     * @param array $links Existing plugin action links.
     * @return array Modified plugin action links.
     */
    public function add_plugin_action_links( $links ) {
        $settings_link = '<a href="' . admin_url( 'admin.php?page=gf_settings&subview=cleantalk-doboard-add-on-for-gravity-forms' ) . '">' . esc_html__( 'Settings', 'cleantalk-doboard-add-on-for-gravity-forms' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Add Support link to plugin row meta
     *
     * @since  1.0.4
     * @access public
     *
     * @param array  $plugin_meta Existing plugin row meta links.
     * @param string $plugin_file Plugin file path.
     * @return array Modified plugin row meta links.
     */
    public function add_plugin_row_meta( $plugin_meta, $plugin_file ) {
        // Get the main plugin file path
        $plugin_dir = dirname( dirname( $this->_full_path ) );
        $main_plugin_file = $plugin_dir . '/' . basename( $this->_path );
        $plugin_basename = plugin_basename( $main_plugin_file );
        
        if ( $plugin_basename === $plugin_file ) {
            $support_link = '<a href="https://wordpress.org/support/plugin/cleantalk-doboard-add-on-for-gravity-forms/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Support', 'cleantalk-doboard-add-on-for-gravity-forms' ) . '</a>';
            $review_link = '<a href="https://wordpress.org/support/plugin/cleantalk-doboard-add-on-for-gravity-forms/reviews/#new-post" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Review', 'cleantalk-doboard-add-on-for-gravity-forms' ) . '</a>';
            $plugin_meta[] = $support_link . ' | ' . $review_link;
        }
        return $plugin_meta;
    }

    /**
     * Get API class
     * @return CleantalkDoboardAddonForGravityFormsDoBoardAPI
     */
    private function doBoardAPIFramework()
    {
        if ( ! class_exists('CleantalkDoboardAddonForGravityFormsDoBoardAPI') ) {
            require_once( CLEANTALK_DOBOARD_ADDON_FOR_GRAVITY_FORMS__API_CLASS_PATH );
        }

        return new CleantalkDoboardAddonForGravityFormsDoBoardAPI();
    }

    /**
     * Main addon logic.
     * @param array $notification
     * @param $form
     * @param $entry
     * @return array
     */
    public function interceptNotification($notification, $form, $entry) {
        // this will run once!
        $this->task_data_to_send = $this->prepareDoboardTaskData($entry, $form);
        $this->task_data_received = $this->task_data_to_send ? $this->receiveDoboardResponse($entry, $form, $this->task_data_to_send) : false;
        // what to do on hook
        $to_user = isset($notification['name']) && $notification['name'] === 'User Notification';
        $to_admin = isset($notification['name']) && $notification['name'] === 'Admin Notification';
        if ($this->task_data_to_send && $this->task_data_received) {
            if ($to_user) {
                $notification = $this->changeUserEmailContent($notification, $this->task_data_received, $this->task_data_to_send);
            } elseif ($to_admin) {
                $this->changeAdminEmailContent();
            }
        }
        return $notification;
    }

    public function saveEntryTaskMeta($entry, $form)
    {
        $entry_id = $entry['id'];

        /**
         * @var CleantalkDoboardAddonForGravityForms $doboard_instance
         */
        $doboard_instance = CleantalkDoboardAddonForGravityForms::get_instance();
        if ( $doboard_instance->gravity_entry_id === $entry_id) {
            $task_url = $doboard_instance->preparePublicTaskURL($doboard_instance->task_data_received, $doboard_instance->task_data_to_send);
            if (false !== $task_url) {
                $doboard_message_text = __('Reply and manage', 'cleantalk-doboard-add-on-for-gravity-forms');
                $custom_data = array(
                        'message' => $doboard_message_text,
                        'link' => $task_url['full'],
                        'link_text' => $task_url['short'],
                );

                gform_update_meta($entry_id, 'doboard_task_metadata', json_encode($custom_data));
            }
        }
    }

    public function loadEntryTaskMeta($form_id, $entry)
    {
        $entry_id = $entry['id'];
        $meta_value = gform_get_meta($entry_id, 'doboard_task_metadata');
        $meta_value = json_decode($meta_value, true);
        if (isset($meta_value['message'], $meta_value['link'], $meta_value['link_text'])) {
            $template = '%s: <a href="%s">%s</a><br><br>';
            $new_row = sprintf(
                    $template,
                    esc_js($meta_value['message']),
                    esc_js($meta_value['link']),
                    esc_js($meta_value['link_text'])
            );
            echo $new_row;
        }
    }

    /**
     * @param $entry
     * @param $form
     * @return array|false
     */
    private function prepareDoboardTaskData($entry, $form) {
        if (!isset($this->task_data_to_send)) {
            $this->task_data_to_send = $this->getTaskDataFromGravityFeeds($entry, $form);
        }
        return $this->task_data_to_send;
    }

    /**
     * @param $entry
     * @param $form
     * @return false|array
     */
    private function getTaskDataFromGravityFeeds($entry, $form)
    {
        // gravity func call
        $feeds = $this->get_feeds( $entry['form_id'] );
        $data = false;
        foreach ( $feeds as $feed ) {
            // gravity func call
            if ( $this->is_feed_condition_met( $feed, $form, $entry ) ) {
                $data = $feed['meta'];
                break;
            }
        }

        if ($data) {
            $this->gravity_entry_id = $entry['id'];
        }

        return $this->validateTaskDataToSend($data);
    }

    /**
     * @param $data
     * @return false|array
     */
    private function validateTaskDataToSend($data)
    {
        if (isset(
                $data['doboard_project_id'],
                $data['doboard_session_id'],
                $data['doBoard_user_id'],
                $data['doboard_account_id'],
                $data['doboard_task_board_id'],
                $data['doboard_label_ids'],
                $data['doboard_new_tasks_is_public']
        )) {
            $data['doboard_new_tasks_publicity'] = $data['doboard_new_tasks_is_public'] === '1' ? 'PUBLIC' : 'REGULAR';
            unset($data['doboard_new_tasks_is_public']);
            return $data;
        }
        return false;
    }

    /**
     * @param $entry
     * @param $form
     * @param $task_data_to_send
     * @return array|false
     */
    private function receiveDoboardResponse($entry, $form, $task_data_to_send) {
        if (!isset($this->task_data_received)) {
            // if something goes wrong or g-entry is spam
            if ( !isset($entry['status']) || $entry['status'] !== 'spam') {
                $this->task_data_received  = $this->makeDoboardRequests($entry, $form, $task_data_to_send);
            }
        }
        return $this->task_data_received;
    }

    /**
     * @param $entry
     * @param $form
     * @param array $task_data
     * @return array|false
     */
    public function makeDoboardRequests($entry, $form, $task_data ) {
        $doboard_add_task_response = false;
        $doboard_add_comment_response = false;
        // add task
        try {
            $doboard_add_task_response = $this->doboardAddTask($entry, $form, $task_data);
        } catch (\Exception $e) {
            $this->initializeDoboardAPI();
            $doboard_add_task_response = $this->doboardAddTask($entry, $form, $task_data);
        }
        // validate
        try {
            $doboard_add_task_response = $this->validateDoboardAddTaskResponse($doboard_add_task_response);
        } catch(\Exception $e) {
            $this->errorLog($e->getMessage());
        }
        // add comment
        if ($doboard_add_task_response) {
            try {
                $doboard_add_comment_response = $this->doboardAddComment($doboard_add_task_response['data']['task_id'], $entry, $form, $task_data);
            } catch(\Exception $e) {
                $this->errorLog($e->getMessage());
            }
        }
        // validate
        try {
            $doboard_add_comment_response = $this->validateDoboardAddCommentResponse($doboard_add_comment_response);
        } catch(\Exception $e) {
            $this->errorLog($e->getMessage());
        }
        if ($doboard_add_task_response && $doboard_add_comment_response) {
            return $doboard_add_task_response;
        }
        return false;
    }

    /**
     * @param $string
     * @return void
     */
    private function errorLog($string)
    {
        if (is_string($string) && function_exists('error_log') && is_callable('error_log')) {
            error_log($string);
        }
    }

    /**
     * @param array $doboard_response
     * @return array
     * @throws Exception
     */
    private function validateDoboardAddTaskResponse($doboard_response)
    {
        if (!isset($doboard_response['data'])) {
            throw new \Exception('Doboard response is invalid');
        }
        $data = $doboard_response['data'];
        if (!isset($data['operation_status'])) {
            throw new \Exception('Doboard response is invalid');
        }
        if ($data['operation_status'] !== 'SUCCESS') {
            throw new \Exception('Doboard response is not successful');
        }
        return $doboard_response;
    }

    /**
     * @param array $doboard_response
     * @return array
     * @throws Exception
     */
    private function validateDoboardAddCommentResponse($doboard_response)
    {
        return $doboard_response;
    }

    /**
     * Handles the submission of the form and sends data to doBoard
     *
     * @since  1.0
     * @access public
     *
     * @param array $entry The entry data.
     * @param array $form  The form data.
     * @param array $task_data  The task data.
     * @return array|false The result of adding a task to doBoard or false on failure.
     */
    public function doboardAddTask($entry, $form, $task_data ) {
        $doBoard_label  = $task_data['doboard_label_ids'];

        // Adding label_ids to the array if it is a string
        if (!is_array($doBoard_label) && !empty($doBoard_label)) {
            $doBoard_label = array_map('trim', explode(',', $doBoard_label));
        } elseif (empty($doBoard_label)) {
            $doBoard_label = array();
        }

        $fields_string = $this->doboard_get_entry_fields_string($entry, $form, 'title_name', " ");
        $title_name = mb_substr($fields_string, 0, 100) . (mb_strlen($fields_string) > 15 ? '...' : '');
        if (!$title_name) {
            $title_name = $entry['title'];
        }

        $data = array(
                'session_id' => $task_data['doboard_session_id'],
                'name'       => $title_name,
                'user_id'    => $task_data['doBoard_user_id'],
                'project_id' => $task_data['doboard_project_id'],
                'track_id'   => $task_data['doboard_task_board_id'],
                'label_ids'  => $doBoard_label,
                'task_type'  => $task_data['doboard_new_tasks_publicity'],
        );

        return $this->doBoardAPIFramework()->add_task($data, $task_data['doboard_account_id']);
    }

    /**
     * Summary of doboard_add_comment
     * @param string $task_id
     * @param array $entry
     * @param array $form
     * @param array $task_data
     * @return false|array
     */
    public function doboardAddComment($task_id, $entry, $form, $task_data ) {
        $comment    = $this->doboard_get_entry_fields_string($entry, $form, 'comment', "<br>");

        $data = array(
                'session_id' => $task_data['doboard_session_id'],
                'task_id'    => $task_id,
                'comment'    => $comment,
                'project_id' => $task_data['doboard_project_id'],
        );

        return $this->doBoardAPIFramework()->add_comment($data, $task_data['doboard_account_id']);
    }


    /**
     * @param array $notification
     * @param array $task_data_received
     * @param array $task_data_to_send
     * @return array
     */
    private function changeUserEmailContent($notification, $task_data_received, $task_data_to_send) {
        if ($task_data_to_send['doboard_new_tasks_publicity'] === 'PUBLIC') {
            $task_url = $this->preparePublicTaskURL($task_data_received, $task_data_to_send);
            $doboard_message_text = __('Here is the tracking link for your inquiry:', 'cleantalk-doboard-add-on-for-gravity-forms');
            $gforms_message = isset($notification['message']) && $notification['message'];
            if (false !== $task_url && $gforms_message) {
                $doboard_message_template = '<p>%s&nbsp;<a href="%s">%s</a></p>';
                $doboard_message_template = sprintf(
                        $doboard_message_template,
                        $doboard_message_text,
                        $task_url['full'],
                        // in the user email the visible link should be full
                        $task_url['full']
                );
                $notification['message'] .= $doboard_message_template;
            }
        }
        return $notification;
    }

    /**
     * Run filter to add task link for admin email
     * @return void
     */
    private function changeAdminEmailContent() {
        add_filter('gform_pre_send_email', function($email, $message_format, $notification, $entry) {
            if (isset($email['message'])) {
                $doboard_instance = CleantalkDoboardAddonForGravityForms::get_instance();
                $task_url = $doboard_instance->preparePublicTaskURL($doboard_instance->task_data_received, $doboard_instance->task_data_to_send);
                $doboard_message_text = __('Manage and reply to the sender in doBoard:', 'cleantalk-doboard-add-on-for-gravity-forms');
                if (false !== $task_url) {
                    $doboard_message_template = '<br><p>%s&nbsp;<a href="%s">%s</a></p>';
                    $doboard_message_template = sprintf(
                            $doboard_message_template,
                            $doboard_message_text,
                            $task_url['full'],
                            // in the user email the visible link should be full
                            $task_url['full']
                    );
                    $email['message'] = str_replace('</body>', $doboard_message_template, $email['message']);
                }
            }
            return $email;
        }, 10, 4);
    }

    /**
     * @param $task_data_received
     * @param $task_data_to_send
     * @return string[]|false
     */
    private function preparePublicTaskURL($task_data_received, $task_data_to_send)
    {
        $result = array();
        if (isset($task_data_received['data']['tasks'][0])) {
            $task_response = $task_data_received['data']['tasks'][0];
            if (isset($task_response['task_id'])) {
                $doboard_account_id = $task_data_to_send['doboard_account_id'];
                $token_part = '';
                if ($task_response['token'] !== null) {
                    $token_part = '?token=' . $task_response['token'];
                }

                $result['full'] = sprintf(
                        "https://app.doboard.com/%d/task/%d%s",
                        $doboard_account_id,
                        $task_response['task_id'],
                        $token_part
                );
                $result['short'] = '.../task/' . $task_response['task_id'] . '/';
            }
        }

        return empty($result) ? false : $result;
    }

    public function initializeDoboardAPI() {
        if ( is_object( $this->api ) ) {
            return true;
        }
        $user_token = $this->get_plugin_setting('doBoard_user_token');
        if ( empty( $user_token ) ) {
            return false;
        }
        $auth_result = $this->doBoardAPIFramework()->auth($user_token);
        if ( !empty($auth_result['data']['accounts']) ) {
            $this->api = new CleantalkDoboardAddonForGravityFormsDoBoardAPI();
            return true;
        }
        return false;
    }

    protected function get_accounts_for_feed_setting() {
        $choices = array(
            array(
                'label' => esc_html__( 'Select an account', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                'value' => '',
            ),
        );
        $user_token = $this->get_plugin_setting('doBoard_user_token');
        if (!$user_token) {
            return $choices;
        }
        $doBoard = new CleantalkDoboardAddonForGravityFormsDoBoardAPI();
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
        $auth_result = $this->doBoardAPIFramework()->auth($user_token);
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
                'label' => esc_html__( 'Select a project', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                'value' => '',
            ),
        );
        if (!$account_id || !$session_id) {
            return $choices;
        }

        $projects = $this->doBoardAPIFramework()->get_projects($account_id, $session_id);

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
                'label' => esc_html__( 'Select a task board', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                'value' => '',
            ),
        );
        if (!$account_id || !$session_id) {
            return $choices;
        }

        $task_boards = $this->doBoardAPIFramework()->get_task_boards($account_id, $session_id, $project_id);

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
                'label' => esc_html__( 'Select labels', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                'value' => '',
            ),
        );
        if (!$account_id || !$session_id) {
            return $choices;
        }

        $labels = $this->doBoardAPIFramework()->get_labels($account_id, $session_id);

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

    protected function get_options_for_publicity() {
        $choices = array(
                array(
                        'label' => esc_html__( 'Public', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                        'value' => '1',
                ),
                array(
                        'label' => esc_html__( 'Private', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                        'value' => '0',
                ),
        );
        return $choices;
    }

    public function can_create_feed() {
        $result = $this->initializeDoboardAPI();
        return $result;
    }

    public function enqueue_admin_styles() {
        wp_enqueue_style(
                'cleantalk-doboard-add-on-for-gravity-forms-css',
                plugins_url( '../public/cleantalk-doboard-add-on-for-gravity-forms.css', __FILE__ ),
                array(),
                $this->_version
        );
        wp_enqueue_script(
                'cleantalk-doboard-add-on-for-gravity-forms-js',
                plugins_url( '../public/cleantalk-doboard-add-on-for-gravity-forms.js', __FILE__ ),
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
                        'title'  => esc_html__( 'doBoard Settings', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                        'description' => wp_kses_post(
                                "<a href='https://doboard.com/' target='_blank' style='font-weight: bold;'>" . esc_html__( "doBoard.com", 'cleantalk-doboard-add-on-for-gravity-forms' ) . "</a> "
                                . esc_html__( 'is an online task management app that helps you convert messages submitted through forms into actionable tasks.', 'cleantalk-doboard-add-on-for-gravity-forms' )
                        ),
                        'fields' => array(
                                array(
                                        'name'     => 'doBoard_user_token',
                                        'label'    => esc_html__( 'User Token', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                                        'type'     => 'text',
                                        'class'    => 'medium',
                                        'required' => true,
                                        'tooltip'  => esc_html__( 'Enter your doBoard user token.', 'cleantalk-doboard-add-on-for-gravity-forms'),
                                        'description' => wp_kses_post(
                                                esc_html__( 'You can find your user token in your', 'cleantalk-doboard-add-on-for-gravity-forms' ) .
                                                " <a href='https://cleantalk.org/my/profile' target='_blank'>" . esc_html__( 'doBoard account settings.', 'cleantalk-doboard-add-on-for-gravity-forms' ) . "</a>"
                                        ),
                                ),
                                array(
                                        'type'     => 'save',
                                        'messages' => array(
                                                'success' => esc_html__( 'Settings updated.', 'cleantalk-doboard-add-on-for-gravity-forms' ),
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
        $feed_account_id = $this->get_setting('doboard_account_id');
        $selected_account_id = rgpost('feed_setting_doboard_account_id') ?: $feed_account_id ?: $default_account_id;

        return array(
                array(
                        'title'  => esc_html__( 'doBoard Feeds', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                        'description' => wp_kses_post(
                                "<a href='https://wordpress.org/support/plugin/cleantalk-doboard-add-on-for-gravity-forms/' target='_blank' rel='noopener noreferrer'>" . esc_html__( 'Support', 'cleantalk-doboard-add-on-for-gravity-forms' ) . "</a>"
                        ),
                        'fields' => array(
                                array(
                                        'name'      => 'feed_name',
                                        'label'     => esc_html__( 'Feed Name', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                                        'type'      => 'text',
                                        'class'     => 'medium',
                                        'required'  => true,
                                        'tooltip'   => esc_html__( 'Enter a name to identify this feed.', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                                ),
                                array(
                                        'name'     => 'doboard_account_id',
                                        'label'    => esc_html__( 'Account ID', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                                        'type'     => 'select',
                                        'choices'  => $accounts,
                                        'class'    => 'medium',
                                        'required' => true,
                                        'default_value' => $default_account_id,
                                        'value'    => $selected_account_id,
                                        'tooltip'  => esc_html__( 'Select the doBoard account to which the tasks will be sent.', 'cleantalk-doboard-add-on-for-gravity-forms'),
                                ),
                                array(
                                        'name'     => 'doboard_project_id',
                                        'label'    => esc_html__( 'Project ID', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                                        'type'     => 'select',
                                        'choices'  => $this->get_projects_for_feed_setting($selected_account_id, isset($auth_data['session_id']) ? $auth_data['session_id'] : ''),
                                        'class'    => 'medium',
                                        'required' => true,
                                        'tooltip'  => esc_html__( 'Enter the doBoard project ID where tasks will be created.', 'cleantalk-doboard-add-on-for-gravity-forms'),
                                ),
                                array(
                                        'name'     => 'doboard_task_board_id',
                                        'label'    => esc_html__( 'Task Board ID', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                                        'type'     => 'select',
                                        'choices'  => $this->get_task_boards_for_feed_setting($selected_account_id, isset($auth_data['session_id']) ? $auth_data['session_id'] : ''),
                                        'class'    => 'medium',
                                        'required' => false,
                                        'tooltip'  => esc_html__( 'Select the doBoard task board where tasks will be created.', 'cleantalk-doboard-add-on-for-gravity-forms'),
                                ),
                                array(
                                        'name'     => 'doboard_label_ids',
                                        'label'    => esc_html__( 'Label IDs', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                                        'type'     => 'select',
                                        'choices'  => $this->get_labels_for_feed_setting($selected_account_id, isset($auth_data['session_id']) ? $auth_data['session_id'] : ''),
                                        'class'    => 'medium',
                                        'multiple' => true,
                                        'required' => false,
                                        'tooltip'  => esc_html__( 'Select one or more doBoard labels to assign to the tasks.', 'cleantalk-doboard-add-on-for-gravity-forms'),
                                ),
                                array(
                                        'name'     => 'doboard_new_tasks_is_public',
                                        'label'    => esc_html__( 'Tasks visibility', 'cleantalk-doboard-add-on-for-gravity-forms' ),
                                        'type'     => 'select',
                                        'choices'  => $this->get_options_for_publicity(),
                                        'class'    => 'medium',
                                        'required' => false,
                                        'tooltip'  => esc_html__( 'If this option is set to public, all tasks created from form entries will include a shared link available to both you and the sender. Using this link, the sender can track their submission and reply directly within the task without emailing you.', 'cleantalk-doboard-add-on-for-gravity-forms'),
                                ),
                                array(
                                        'name'  => 'doboard_session_id',
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

        if (!is_array($result)) {
            return false;
        }

        return implode($separator, $result);
    }

    /**
     * Returns the columns for the feed list.
     * @return array{feed_name: mixed}
     */
    public function feed_list_columns() {
        return array(
                'feed_name' => esc_html__( 'Feed Name', 'cleantalk-doboard-add-on-for-gravity-forms' ),
        );
    }

    /**
     * Returns the value for the feed name column.
     *
     * @param array $feed The feed data.
     * @return string The feed name or a placeholder if not set.
     */
    public function get_column_value_feed_name( $feed ) {
        return rgar( $feed['meta'], 'feed_name' ) ? $feed['meta']['feed_name'] : esc_html__( '(No name)', 'cleantalk-doboard-add-on-for-gravity-forms' );
    }

    /**
     * Override feed list page to add support link description
     *
     * @since  1.0.4
     * @access public
     *
     * @param array|null $form The form object.
     */
    public function feed_list_page( $form = null ) {
        parent::feed_list_page( $form );
        ?>
        <div class="gform-settings-description" style="margin-top: 10px; padding: 0 20px;">
            <?php echo wp_kses_post(
                    __('If you have any questions, please contact our support team at', 'cleantalk-doboard-add-on-for-gravity-forms') . ' ' .
                    "<a href='https://wordpress.org/support/plugin/cleantalk-doboard-add-on-for-gravity-forms/' target='_blank' rel='noopener noreferrer'>" . esc_html__( 'support', 'cleantalk-doboard-add-on-for-gravity-forms' ) . "</a>"
                    . '. '
                    . "<a href='https://wordpress.org/support/plugin/cleantalk-doboard-add-on-for-gravity-forms/reviews/#new-post' target='_blank' rel='noopener noreferrer'>" . esc_html__( 'Review', 'cleantalk-doboard-add-on-for-gravity-forms' ) . "</a>"
                    . ' ' .
                    __('the plugin.', 'cleantalk-doboard-add-on-for-gravity-forms')
            ); ?>
        </div>
        <?php
    }

    public function get_slug() {
        return $this->_slug;
    }
}
