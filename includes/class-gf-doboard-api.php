<?php

/**
 * doBoard API library for Gravity Forms integration.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    Cleantalk
*/
class GF_doBoard_API {

    /**
     * Defines the API token needed to access doBoard.
     *
     * @since  1.0
     * @access protected
     * @var    string $api_token The Breeze API token.
     */
    protected $api_token = null;

    /**
     * Defines the base URL path for Breeze API requests.
     *
     * @since  1.0
     * @access protected
     * @var    string $api_url_base The Breeze API URL base path.
     */
    protected $api_url_base = 'https://api.doboard.com/';

    /**
     * Defines the base URL path for doBoard API requests.
     *
     * @since  1.0
     * @access protected
     * @var    string $api_url_base The doBoard API URL base path.
     */

    /**
     * Make a doBoard API request.
     *
     * @since  1.0
     * @access private
     *
     * @param  string $path API request path.
     * @param  array  $options (default: array()) Request options.
     * @param  string $method (default: 'POST') Request HTTP method.
     * @param  int    $code (default: 200) Expected HTTP response code.
     *
     * @return array
     * @throws Exception If HTTP response code is invalid, exception is thrown.
     */
    private function make_request( $path, $options = array(), $method = 'POST', $code = 200 ) {
        $url = $this->api_url_base . $path;

        $args = array(
            'body'    => !empty( $options ) && is_array($options) ? $options : '',
            'method'  => $method,
        );

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            throw new Exception( $response->get_error_message() );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== wp_remote_retrieve_response_code( $response ) ) {
            $message = isset( $body['error'] ) && ! empty( $body['error'] ) ? $body['error'] : wp_remote_retrieve_response_message( $response );
            throw new Exception( $message );
        }

        return $body;
    }

    public function auth($data) {
        $response = $this->make_request(
            'user_authorize', 
            $data,
            'POST',
            200
        );

        return $response;
    }

    public function add_task($data, $company_id ) {
        $response = $this->make_request(
            $company_id . '/task_add', 
            $data,
            'POST',
            200
        );

        return $response;
    }

    public function add_comment($data, $company_id ) {
        $response = $this->make_request(
            $company_id . '/comment_add', 
            $data,
            'POST',
            200
        );

        return $response;
    }
}