<?php
/**
 * The REST functionality of the plugin.
 *
 * @link       
 * @since      2.6.7.3
 *
 * @package    Applyonline
 * @subpackage Applyonline/rest
 */

/**
 * The updater functionality of the plugin.
 *
 * Defines the plugin name, version
 *
 * @package    Applyonline
 * @subpackage Applyonline/rest
 * @author     Farhan Noor <profiles.wordpress.org/farhannoor>
 */
class Applyonline_Rest{
    
    /**
	 * The ID of this plugin.
	 *
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	protected $plugin_version;
        
        protected $namespace;
        
        protected $callbacks;

        /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
        
        function __construct( $plugin_name, $version ) {
            $this->plugin_name = $plugin_name;
            $this->plugin_version = $version;
            $this->namespace = 'aol/v1';
            $this->callbacks = new Applyonline_Rest_Functions();
        }
        
        function rest_authentication_errors( $errors ){
            
            if( is_wp_error($errors) ){
                var_dump($errors); die('Alhamdulillah');                
            }
        }
                
        function rest_api_init() {
            /*
            register_rest_route( $this->namespace, '/nonce', array(
                'methods' => 'GET',
                'callback' => [ $this, 'get_nonce' ],
            ));*/
            register_rest_route( $this->namespace, '/form', array(
                'methods' => 'POST',
                'permission_callback' => [$this, 'validate_nonce'],
                'callback' => [ $this->callbacks, 'form_post' ],
                'args' => [
                    'ad_id' => [
                        'required' => TRUE,
                        //'validate_callback' => [$this, 'validate_id'],
                        'sanitize_callback' => [$this, 'sanitize_int'],
                    ]
                ],
            ));
        }
        
        public function get_nonce() {
            return wp_create_nonce('wp_rest');
        }

        public function validate_nonce( WP_REST_REQUEST $request ) {
            // Allow public access. You can modify this to require authentication.
            $nonce = $request->get_header('X-WP-Nonce');
            $result = wp_verify_nonce($nonce, 'wp_rest');
            if( $result == FALSE ){
                $result = new WP_Error();
                $result->add('rest_forbidden', __('Session expired. Please refresh page & try again. If problem presists please contact support.', 'apply-online'), ['status' => 403]);
            }
            return $result;
	}
        
        function validate_id($param, $request, $key){
            return empty( $param );
        }
        
        function validate_email($param, $request, $key) {
            return is_email( $param );
        }
        
        function sanitize_email($param, $request, $key){
            return sanitize_email( $param );
        }
        
        function sanitize_text($param, $request, $key){
            return sanitize_text_field( $param );
        }
        
        function sanitize_int($param, $request, $key){
            return (int)$param;
        }
}