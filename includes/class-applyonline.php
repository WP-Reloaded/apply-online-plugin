<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://wpreloaded.com/farhan-noor
 * @since      1.0
 *
 * @package    Applyonline
 * @subpackage Applyonline/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0
 * @package    Applyonline
 * @subpackage Applyonline/includes
 * @author     Farhan Noor
 */
class Applyonline {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Applyonline_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
                if ( defined( 'APPLYONLINE_VERSION' ) ) {
			$this->version = APPLYONLINE_VERSION;
		} else {
			$this->version = '2.5';
		}
                
                define( 'ALLOWED_FILE_TYPES', 'jpg,jpeg,png,doc,docx,pdf,rtf,odt,txt' );

		$this->plugin_name = 'apply-online';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

                add_action( 'init', array( $this, 'register_aol_post_types' ), 5 );
                add_action( 'init', array($this, 'after_plugin_update'));
                add_action( 'wp_enqueue_scripts', array($this, 'load_dashicons_front_end') );

                new Applyonline_Labels();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Applyonline_Loader. Orchestrates the hooks of the plugin.
	 * - Applyonline_i18n. Defines internationalization functionality.
	 * - Applyonline_Admin. Defines all hooks for the admin area.
	 * - Applyonline_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-applyonline-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-applyonline-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-applyonline-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-applyonline-public.php';
                
                /*
                 * Form Builder addon
                 */
                //require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/builder/class-functions.php';
                //require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/builder/class-init.php';

                //require_once plugin_dir_path( dirname( __FILE__ ) ) . 'required-plugins/class-tgm-plugin-activation.php';

		$this->loader = new Applyonline_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Applyonline_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Applyonline_i18n();
		$plugin_i18n->set_domain( 'ApplyOnline' );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Applyonline_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
                $this->loader->add_action( 'set_current_user', $plugin_admin, 'output_attachment' );

                $this->loader->add_filter( 'views_edit-aol_application', $plugin_admin, 'status_filters' );
                
                $this->loader->add_action( 'save_post', $plugin_admin, 'save_ad'  );
                
                /*Schedule Ad*/
                $this->loader->add_filter('display_post_states', $plugin_admin, 'add_closed_state', 10, 2);
                
                /*Admin Notice*/
                $this->loader->add_action('admin_notices', $plugin_admin, 'settings_notice');
                $this->loader->add_action('wp_ajax_aol_dismiss_notice', $plugin_admin, 'admin_dismiss_notice');
                
                $this->loader->add_filter( 'wp_dropdown_users_args',  $plugin_admin, 'ad_editor_authors_metabox');
                $this->loader->add_action('wp_ajax_applications_search_and_filter', $plugin_admin, 'applications_search_and_filter');
                
                /*User Insertion Sanitiziation for AOL Manager Role*/                
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Applyonline_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles', 1 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
                
                /*Schedule Ad*/
                $this->loader->add_action('pre_get_posts', $plugin_public, 'check_ad_closing_status');
	}

        /**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Applyonline_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
                
        function after_plugin_update(){
            require_once plugin_dir_path( __FILE__ ).'class-applyonline-activator.php';
            $saved_version = get_option('aol_version', 0);
            if($saved_version < 1.6) {
                Applyonline_Activator::bug_fix_before_16();
            }
            
            if($saved_version < 1.9){
                Applyonline_Activator::fix_roles();
            }
            
            if($saved_version < 2.1){
                /*Merge Custom Filters to Default Filters*/
                $default_filters = array(
                    'category' => array('singular' => __('Category', 'ApplyOnline'), 'plural' => __('Categories', 'ApplyOnline')),
                    'type' => array('singular' => __('Type', 'ApplyOnline'), 'plural' => __('Types', 'ApplyOnline')),
                    'location' => array('singular' => esc_html__('Location', 'ApplyOnline'), 'plural' => esc_html__('Locations', 'ApplyOnline'))
                );
                $custom_filters = get_option_fixed('aol_custom_filters', array());
                $filters = array_merge($default_filters, $custom_filters);
                //Update Option was not working for Existing options, hence it is 1st being deleted.
                delete_option('aol_ad_filters');
                update_option('aol_ad_filters', $filters);
                
                /*Merge Custom Statuses to Default Statuses*/
                $default_statuses = array('pending' => __('Pending', 'ApplyOnline'), 'rejected'=> __('Rejected', 'ApplyOnline'), 'shortlisted' => __('Shortlisted', 'ApplyOnline'));
                $custom_statuses = get_option_fixed('aol_custom_statuses', array());
                $statuses = array_merge($default_statuses, $custom_statuses);
                //Update Option was not working for Existing options, hence it is 1st being deleted.
                delete_option('aol_custom_statuses');
                update_option('aol_custom_statuses', $statuses);
                
                update_option('aol_mail_footer', "\n\nThank you\n".get_bloginfo('name')."\n".site_url()."n------\nPlease do not reply to this system generated message.");
                
                /*Setting version to latest 2.1*/
                update_option('aol_version', $this->get_version(), TRUE);
            }
        }
        
        function load_dashicons_front_end() {
          wp_enqueue_style( 'dashicons' );
        }

        public function cpt_generator($cpt, $singular, $plural, $description, $args_custom = array()){
            if($singular != NULL){
            $labels=array(
                'name'  => $plural,
                'singular_name'  => __($singular, 'ApplyOnline' ),
                'add_new_item'       => sprintf(__('Add New %s', 'ApplyOnline'), $singular),
		'new_item'           => sprintf(__( 'New %s', 'ApplyOnline' ), $singular),
		'edit_item'          => sprintf(__( 'Edit %s', 'ApplyOnline' ), $singular),
		'view_item'          => sprintf(__( 'View %s', 'ApplyOnline' ), $singular),
                'search_items'      => sprintf(__('Search %s', 'ApplyOnline'), $plural),
                );
            }

            $args=array(
                'labels'=> $labels,
                'public'=>  true,
                'show_in_nav_menus' => false,
                'capability_type'   => array('ad', 'ads'),
                'map_meta_cap'      => TRUE,
                'has_archive'   => true,
                'menu_icon'  => 'dashicons-admin-site',
                'show_in_menu'  => 'edit.php?post_type=aol_ad',
                'description' => $description,
                'rewrite'       => array('slug' => sanitize_key($plural)),
                'supports' => array('editor', 'excerpt', 'title', 'thumbnail', 'revisions', 'author'),
            );
            register_post_type('aol_'.sanitize_key($cpt), array_merge($args, $args_custom));
        }
        
        public function taxonomy_generator($key, $singular, $plural,  $hierarchical = TRUE){
            // Add new taxonomy, make it hierarchical (like categories)
            $labels = array(
                'name'              => $plural,
                'singular_name'     => $singular,
                'plural_name'     => $plural,
                'search_items'      => sprintf(__( 'Search %s', 'ApplyOnline' ), $plural),
                'all_items'         => sprintf(__( 'All %s', 'ApplyOnline' ), $plural),
                'parent_item'       => sprintf(__( 'Parent %s', 'ApplyOnline' ), $singular),
                'parent_item_colon' => sprintf(__( 'Parent %s:', 'ApplyOnline' ), $singular),
                'edit_item'         => sprintf(__( 'Edit %s', 'ApplyOnline' ), $singular),
                'update_item'       => sprintf(__( 'Update %s', 'ApplyOnline' ), $singular),
                'add_new_item'      => sprintf(__( 'Add New %s', 'ApplyOnline' ), $singular),
                'new_item_name'     => sprintf(__( 'New %s Name', 'ApplyOnline' ), $singular),
            );
            
            $capabilities = array(
		'manage_terms'               => 'manage_ad_terms',
		'edit_terms'                 => 'edit_ad_terms',
		'delete_terms'               => 'delete_ad_terms',
		'assign_terms'               => 'assign_ad_terms',
                );

            $args = array(
                    'hierarchical'      => $hierarchical,
                    'labels'            => $labels,
                    'show_ui'           => true,
                    'show_admin_column' => true,
                    'query_var'         => true,
                    'show_in_menu'      => false,
                    'rewrite'           => array( 'slug' => sanitize_key('ad-'.$key) ),
                    'capabilities'      => $capabilities,
            );
            $cpts = get_option_fixed('aol_ad_types', array());
            $types = array();
            if(!is_array($types)) $types = array();
            foreach ($cpts as $cpt => $val){
                if(isset($val['filters']) AND in_array(sanitize_key($key), (array)$val['filters'])) $types[] = 'aol_'.$cpt;
            }
            register_taxonomy( 'aol_ad_'.sanitize_key($key), $types, $args );
        }


        /*
         * @todo make label of the CPT editable from plugin settings so user can show his own title on the archive page
         */
        public function register_aol_post_types(){
            $slug = get_option_fixed('aol_slug', 'ads');
            /*Register Main Post Type*/
            $labels=array(
                'add_new'  => __('Create Ad', 'ApplyOnline' ),
                'add_new_item'  => __('New Ad', 'ApplyOnline' ),
                'edit_item'  => __('Edit Ad', 'ApplyOnline' ),
                'all_items' => __('Ads', 'ApplyOnline' ),
                //'menu_name' => __('Apply Online', 'ApplyOnline' )
            );
            $args=array(
                'label' => __( 'All Ads', 'ApplyOnline' ),
                'labels'=> $labels,
                'show_in_menu'  => true,
                'description' => __( 'All Ads', 'ApplyOnline' ),
                'rewrite' => array('slug'=>  $slug),
                'menu_position' => 30,
            );
            //register_post_type('aol_ad',$args);
            $this->cpt_generator('ad', 'Ad', 'Ads', 'All Ads', $args);
            $types = get_option_fixed('aol_ad_types', array());
            unset($types['ad']); //Already reigstered couple of lines before. 
            if(!empty($types)){
                foreach($types as $cpt => $type){
                    $this->cpt_generator($cpt, $type['singular'], $type['plural'], $type['description']);
                }
            }
            
            $filters = aol_ad_filters();
            foreach($filters as $key => $val){
                $this->taxonomy_generator($key, $val['singular'], $val['plural']);
            }
            
            /*Register Applications Post Type*/
            $lables= array(
                'edit_item'=>'Application',
                'not_found' => __( 'No applications found.', 'ApplyOnline' ),
                'not_found_in_trash'  => __( 'No applications found.', 'ApplyOnline' )
                );
            $args=array(
                'label' => __( 'Applications', 'ApplyOnline' ),
                'labels' => $lables,
                'show_ui'           => true,
                'public'            => false,
                'exclude_from_search'=> true,
                'capability_type'   => array('application', 'applications'),
                'capabilities'  => array( 'create_posts' => 'create_applications'),
                'description' =>    __( 'All Applications', 'ApplyOnline' ),
                'supports' =>       array('comments', 'editor'),
                'map_meta_cap'      => TRUE,
                'show_in_menu'      => 'aol-settings',
        );
            register_post_type('aol_application',$args);
            
            //Application tags
            $labels = array(
                'name' => _x( 'Application Status', 'ApplyOnline' ), 
                'singular_name' => 'Status',
                );
            $args = array(
                    'label' =>         esc_html__( 'Status','ApplyOnline'),
                    'hierarchical'      => false,
                    'labels'            => $labels,
                    'show_ui'           => false,
                    'show_admin_column' => false,
                    'query_var'         => true,
                    'show_in_menu'      => false,
                    'show_in_nav_menus' => false,
            );
            register_taxonomy( 'aol_application_status', 'aol_application', $args );
        }
}

class Applyonline_labels{
    public function __construct() {
        add_filter('gettext', array($this, 'translations'), 3, 3);
        add_filter('gettext_with_context', array($this, 'gettext_with_context'), 3, 4);
    }
    
    function translations( $translated_text, $text, $domain ) {
        //Stop if not applyOnlin text domain.
        if($domain != 'ApplyOnline') return $translated_text;
        
            switch ( $text ) {
                
                case 'Fields with (*)  are compulsory.' :
                    $translated_text = get_option('aol_required_fields_notice', 'Fields with (*)  are compulsory.');
                    break;
                case 'Form has been submitted successfully. If required, we will get back to you shortly.' :
                    $translated_text = get_option('aol_application_message', 'Form has been submitted successfully. If required, we will get back to you shortly.');
                    break;
                case 'Submit' :
                    $translated_text = get_option('aol_application_submit_button', 'Submit');
                    break;
                case 'Read More' :
                    $translated_text = get_option('aol_shortcode_readmore', 'Read More');
                    break;
            }
        return $translated_text;
    }
    
    /**
    * @param string $translated
    * @param string $text
    * @param string $context
    * @param string $domain
    * @return string
    */
    function gettext_with_context( $translated, $text, $context, $domain ) {
        //Stop if not applyOnlin text domain.
        if($domain != 'ApplyOnline') return $translated;
        
        if($context == 'public' AND $text == 'Apply Online'){
            $translated = get_option('aol_form_heading', 'Apply Online');
        }

        return $translated;
    }
}