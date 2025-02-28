<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpreloaded.com/farhan-noor
 * @since      1.0.0
 *
 * @package    Applyonline
 * @subpackage Applyonline/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Applyonline
 * @subpackage Applyonline/admin
 * @author     Farhan Noor <profiles.wordpress.org/farhannoor>
 */
class Applyonline_Admin{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	protected $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
                
                //Fix comments on application
                add_filter('comment_row_actions', array($this, 'comments_fix'), 10, 2);
                
                //Application Print
                add_action('wp_ajax_application_table_filter_result', array($this, 'application_table_filter_result'));
                                                
                new ApplyOnline_Ad_Options();
                                
                new Applyonline_Form_Builder();
                
                new Applyonline_Ads();
                
                new Applyonline_Applications();
                
                new Applyonline_Settings($version);
                
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Applyonline_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Applyonline_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
                wp_enqueue_style( 'aol-select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all'  );
                wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/applyonline-admin.css', array(), $this->version, 'all' );
                
                if ( is_aol_admin_screen() ){
                    wp_enqueue_style( 'aol-select2', plugin_dir_url( __FILE__ ) . 'select2/css/select2.min.css', array(), $this->version, 'all'  );
                    wp_enqueue_style('aol-jquery-ui', plugin_dir_url(__FILE__).'css/jquery-ui.min.css');                    
                }                
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hooks) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Applyonline_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Applyonline_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
                $localize = array();
<<<<<<< Updated upstream
                $localize['app_submission_message'] = esc_html__('Form has been submitted successfully. If required, we will get back to you shortly!', 'ApplyOnline'); 
                $localize['app_closed_alert'] = esc_html__('We are no longer accepting applications for this ad!', 'ApplyOnline'); 
                $localize['aol_required_fields_notice'] = esc_html__('Fields with (*)  are compulsory.', 'ApplyOnline');
=======
                $localize['app_submission_message'] = esc_html__('Form has been submitted successfully. If required, we will get back to you shortly!', 'apply-online'); 
                $localize['app_closed_alert'] = esc_html__('We are no longer accepting applications for this ad!', 'apply-online'); 
                $localize['aol_required_fields_notice'] = esc_html__('Fields with (*)  are compulsory.', 'apply-online');
>>>>>>> Stashed changes
                $localize['admin_url'] = admin_url();
                $localize['aol_url'] = plugins_url( 'apply-online/' );
                $localize['nonce'] = wp_create_nonce('aol_nonce');
                wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/applyonline-admin.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-autocomplete' ), $this->version, TRUE );
                
                if( is_aol_admin_screen() ) wp_enqueue_script( 'aol-select2', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array(), $this->version, TRUE );
                
                wp_localize_script( $this->plugin_name, 'aol_admin', $localize );
                
                wp_enqueue_script( 'jquery-ui-datepicker');
	}
        
        function get_ads_list(){
            if( !current_user_can('manage_ads') ) die('Are you nuts?');
            
            $types = get_aol_ad_types();
            $posts = get_posts(array('numberposts' => -1, 'post_type' => $types));
            $response = array();
            foreach($posts as $post){
                $response[] = $post->post_title;
            }
            echo json_encode($response); exit;
        }

        function ad_editor_authors_metabox($args){
            global $post;
            $ad_types = aol_ad_types();
            if ( !in_array(substr($post->post_type, 4), array_keys($ad_types) ) ) return $args;
            
            $args['role__in'] = array('aol_manager', 'administrator');
            $args['who'] = null;
            return $args;
        }
 
        function status_filters($views){
            unset($views['mine']); unset($views['publish']);
            $statuses = aol_app_statuses();
            foreach ($statuses as $key => $status){
                (isset($_GET['aol_application_status']) AND $_GET['aol_application_status'] == $key)? $class = 'current' : $class = NULL;
<<<<<<< Updated upstream
                $views[$status] = '<a class="'.$class.'" href="'.  admin_url("edit.php?post_type=aol_application&aol_application_status=$key").'">'.esc_html__($status, 'ApplyOnline').'</a>';        
=======
                $views[$status] = '<a class="'.$class.'" href="'.  admin_url("edit.php?post_type=aol_application&aol_application_status=$key").'">'.esc_html__($status, 'apply-online').'</a>';        
>>>>>>> Stashed changes
            }
            return $views;
        }

        /**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
        function save_ad( $post_id ){
            /*
             * We need to verify this came from our screen and with proper authorization,
             * because the save_post action can be triggered at other times.
             */
            
            if( !current_user_can('edit_ads') ) 
                return;

            // Check if our nonce is set.
            if ( ! isset( $_POST['adpost_meta_box_nonce'] ) ) {
                return;
            }

            // Verify that the nonce is valid.
            if ( ! wp_verify_nonce( $_POST['adpost_meta_box_nonce'], 'myplugin_adpost_meta_awesome_box' ) ) {
                return;
            }
            $types = get_aol_ad_types();
            if ( !in_array($_POST['post_type'], $types) ) return;
            /* OK, it's safe for us to save the data now. */

            //Update ad closing
            if ( isset($_POST['_aol_ad_closing_date']) ) {
                $time = empty(trim($_POST['_aol_ad_closing_time'])) ? '2359' : trim($_POST['_aol_ad_closing_time']);
                $timestamp = empty(trim($_POST['_aol_ad_closing_date'])) ? NULL: strtotime($_POST['_aol_ad_closing_date'].' '.$time);
                update_post_meta( $post_id, '_aol_ad_closing_date', $timestamp); //Add new value.
            }
            update_post_meta( $post_id, '_aol_ad_close_type', sanitize_key($_POST['_aol_ad_close_type']) ); //Add new value.
            update_post_meta( $post_id, '_recipients_emails', sanitize_textarea_field( $_POST['_recipients_emails']) ); //Add new value.

            //Save ad settings fields from ad ad settings API.
            /*
            $settings = apply_filters('aol_ad_options', array());
            foreach($settings as $setting){
                update_post_meta( $post_id, $setting['key'], $settings['value']); //Add new value.                
            }
             * 
             */
        }

        /*
         * Show data in the Filter Application dropdown on Applications Admin Table
         */
        function application_table_filter_result(){
            if( !current_user_can('manage_ads') ) die('Are you nuts?');
            
            //$search = (isset($_GET['search']) AND !empty($_GET['search'])) ? $_GET['search']: NULL;
            $ads = get_posts(array('post_type' => 'aol_ad', 's' => $_GET['search'], 'lang' => '', 'numberposts' => -1));
            $ads_arr = array(); $i = 0;
            foreach($ads as $ad){
                $ads_arr[$i]['id'] = $ad->ID;
                $ads_arr[$i]['text'] = $ad->post_title;
                $i++;
            }
            wp_send_json($ads_arr);
        }

        public function admin_notice(){            
            //$notices = get_option('aol_dismissed_notices', array()); Obselete in favor of aol_admin_notices since 2.5.1
            $notices = get_option('aol_admin_notices', array('aol_fresh_install'));
            if( empty($notices) OR !current_user_can('manage_options')) return;
<<<<<<< Updated upstream
            //esc_html__( "%sApply Online%s - It's good to %scheck things%s before a long drive.", 'ApplyOnline' )
            ?>
                <div class="notice notice-info is-dismissible aol-notice">
                    <p>
                        <?php echo sprintf(esc_html__( "%sApply Online%s plugin is just installed.", 'ApplyOnline' ), '<strong>', '</strong>'); ?> 
                        <?php echo sprintf(esc_html__('%sClick Here%s for settings.', 'ApplyOnline'), '<a href="'.  get_admin_url().'?page=aol-settings">', '</a>'); ?>
=======
            //esc_html__( "%sApply Online%s - It's good to %scheck things%s before a long drive.", 'apply-online' )
            ?>
                <div class="notice notice-info is-dismissible aol-notice">
                    <p>
                        <?php echo sprintf(esc_html__( "%sApply Online%s plugin is just installed.", 'apply-online' ), '<strong>', '</strong>'); ?> 
                        <?php //sprintf(esc_html__( "Hey - we noticed you've been using %sApply Online% for a while - that's great! Could you do us a favor and give it a 5-star review on WordPress to help us spread the word and boost our motivation?", 'apply-online' ), '<strong>', '</strong>'); ?>
                        <?php echo sprintf(esc_html__('%sClick Here%s for settings.', 'apply-online'), '<a href="'.  get_admin_url().'?page=aol-settings">', '</a>'); ?>
>>>>>>> Stashed changes
                    </p>
                </div>
            <?php
            
<<<<<<< Updated upstream
            if( is_plugin_active('applyonline-statuses/applyonline-statuses.php') ) echo '<div class="notice notice-warning"><p>'.sprintf(esc_html__('%sApplyOnline - Statuses%s extension has been depricated since ApplyOnline 2.1. %sClick Here%s to uninstall this extension.', 'ApplyOnline'), '<strong>', '</strong>', '<a href="'.admin_url().'plugins.php">', '</a>').'</p></div>';
            if( is_plugin_active('applyonline-filters/applyonline-filters.php') ) echo '<div class="notice notice-warning"><p>'.sprintf(esc_html__('%sApplyOnline - Filters%s extension has been depricated since ApplyOnline 2.1. %sClick Here%s to uninstall this extension.', 'ApplyOnline'), '<strong>', '</strong>', '<a href="'.admin_url().'plugins.php">', '</a>').'</p></div>';
=======
            if( is_plugin_active('applyonline-statuses/applyonline-statuses.php') ) echo '<div class="notice notice-warning"><p>'.sprintf(esc_html__('%sApplyOnline - Statuses%s extension has been depricated since apply-online 2.1. %sClick Here%s to uninstall this extension.', 'apply-online'), '<strong>', '</strong>', '<a href="'.admin_url().'plugins.php">', '</a>').'</p></div>';
            if( is_plugin_active('applyonline-filters/applyonline-filters.php') ) echo '<div class="notice notice-warning"><p>'.sprintf(esc_html__('%sApplyOnline - Filters%s extension has been depricated since ApplyOnline 2.1. %sClick Here%s to uninstall this extension.', 'apply-online'), '<strong>', '</strong>', '<a href="'.admin_url().'plugins.php">', '</a>').'</p></div>';
>>>>>>> Stashed changes

            //Sticky Note unless option is saved.
            $path = get_option('aol_upload_path');
            if( empty($path) AND current_user_can('manage_options')){
                ?>
                <div class="notice notice-error aol-notice">
                    <p>
<<<<<<< Updated upstream
                        <?php echo sprintf(esc_html__( "%sApply Online%s attachments are public. Set a private path to avoid user's senseitive data breach.", 'ApplyOnline' ), '<strong>', '</strong>'); ?> 
                        <?php echo sprintf(esc_html__('%sClick Here%s for settings.', 'ApplyOnline'), '<a href="'.  get_admin_url().'?page=aol-settings#aol_upload_path">', '</a>'); ?>
=======
                        <?php echo sprintf(esc_html__( "%sApply Online%s attachments are public. Set a private path to avoid user's senseitive data breach.", 'apply-online' ), '<strong>', '</strong>'); ?> 
                        <?php echo sprintf(esc_html__('%sClick Here%s for settings.', 'apply-online'), '<a href="'.  get_admin_url().'?page=aol-settings#aol_upload_path">', '</a>'); ?>
>>>>>>> Stashed changes
                    </p>
                </div>
                <?php
            }            
        }
        
        public function admin_dismiss_notice(){
            if( !current_user_can('manage_ads') ) die('Are you nuts?');
            
            $notices = get_option('aol_admin_notices', array());
            unset($notices['aol_fresh_install']);
            update_option('aol_admin_notices', $notices);
        }
        
    public function add_closed_state($post_states, $post){
        $timestamp = (int)get_post_meta($post->ID, '_aol_ad_closing_date', true);
        if($timestamp != null and $timestamp < time()){
            $post_states['ad_closed'] = __( 'Closed' );
        }
        return $post_states;
    }
        
        /**
        * Extend WordPress search to include custom fields
        * Join posts and postmeta tables
        *
        * https://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
         * 
         * @since 1.6
        */
        
       function cf_search_join( $join ) {
           global $wpdb;

           if ( is_search() and is_admin() ) {    
               $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
           }

           return $join;
       }

       /**
        * Modify the search query with posts_where
        *
        * https://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
        * 
        * @since 1.6
        */
       function cf_search_where( $where ) {
           global $wpdb;

           if ( is_search() and is_admin() ) {
               $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
           }
           return $where;
       }

       /**
        * Prevent duplicates
        *
        * https://codex.wordpress.org/Plugin_API/Filter_Reference/posts_distinct
        * 
        * @since 1.6
        */
       function cf_search_distinct( $where ) {
           global $wpdb;

           if ( is_search() and is_admin() ) {
           return "DISTINCT";
       }

           return $where;
       }
        
       /**
        * 
        */
       public function comments_fix($actions, $comment){
            $post_id = $comment->comment_post_ID;
            if(get_post_field('post_type', $post_id) == 'aol_application'){
                $author = get_user_by('email', $comment->comment_author_email );
                if(get_current_user_id() != $author->ID) unset($actions['quickedit']); //if not comment author, dont show the quick edit
                unset($actions['unapprove']);
                unset($actions['trash']);
                unset($actions['edit']);
            }
            return $actions;                
        } 
    }
    
    class ApplyOnline_Ad_Options{
        public function __construct() {
            add_action( 'add_meta_boxes', array($this, 'add_meta_boxes'),1 );
        }
            
        function add_meta_boxes(){
            $screens = array('aol_ad');
            $types = get_option_fixed('aol_ad_types');
            if(is_array($types)){
                foreach ($types as $type){
                    $screens[] = 'aol_'.strtolower($type['singular']);
                }
            }
            if(empty($screens) or !is_array($screens)) $screens = array();

            add_meta_box(
                'aol_ad_options',
<<<<<<< Updated upstream
                '<span class="dashicons dashicons-admin-site"></span> '.esc_html__('Ad Options', 'ApplyOnline' ),
=======
                '<span class="dashicons dashicons-admin-site"></span> '.esc_html__('Ad Options', 'apply-online' ),
>>>>>>> Stashed changes
                array($this, 'aol_ad_options'),
                $screens,
                'advanced',
                'high'
            );
        }

        public function aol_ad_options($post){
            $types = get_aol_ad_types();
            if( !in_array($post->post_type, $types)) return;

            $recipients = get_post_meta($post->ID, '_recipients_emails', true);
            //var_dump($recipients);
            //var_dump( explode("\n", str_replace(array("\r", " "),"", $recipients)) );

            $date = $closed_class = $time = NULL;
            $close_type = get_post_meta($post->ID, '_aol_ad_close_type', true);
            $close_form = ($close_type == 'form' or empty($close_type)) ? 'checked': NULL;
            $close_ad = ($close_type == 'ad') ? 'checked': NULL;
            $timestamp = get_post_meta($post->ID, '_aol_ad_closing_date', true);
            if(!empty($timestamp)){
                $date = date_i18n('j-m-Y' ,$timestamp);
                $time = date_i18n('H:i' ,$timestamp);
                $closed_class  =  ($timestamp < time()) ? 'closed' : null;
            }
            $extra_tabs = apply_filters('aol_ad_options_api', array(), $post);

            ob_start(); ?>
            <div class="aol-ad-closing aol-settings aol-meta">
                <div class="nav-tab-wrapper aol-tabs-wrapper">
<<<<<<< Updated upstream
                    <a class="aol-tab nav-tab active" data-id="shortcodes"><?php echo esc_html_e('Shortcodes', 'ApplyOnline'); ?></a>
                    <a class="aol-tab nav-tab" data-id="expiration"><?php echo esc_html_e('Expiration', 'ApplyOnline'); ?></a>
                    <a class="aol-tab nav-tab" data-id="recipients"><?php esc_html_e('Email Recipients', 'ApplyOnline'); ?></a>
                    
                </div>
                <div id="shortcodes" class="aol-tab-data wrap" style="display:block;">
                    <?php do_action('aol_metabox_before', $post); ?>
                    <p class="description"><?php esc_html_e('Use these shortcodes to display this ad or form on a WordPress page. To list all ads on a page, use [aol] shortcode instead.', 'ApplyOnline'); ?></p>
                    <p><label for="ad-shortcode"><?php esc_html_e('Ad shortcode','ApplyOnline'); ?></label><input id="ad-shortcode" type="text" value="[aol_ad id=<?= $post->ID; ?>]" readonly></p>
                    <p><label for="form-shortcode"><?php esc_html_e('Form shortcode', 'ApplyOnline'); ?></label><input id="form-shortcode" type="text" value="[aol_form id=<?= $post->ID; ?>]" readonly></p>
                    <p><a rel="permalink" href="<?php echo admin_url('edit.php?post_type=aol_application'); ?>&ad=<?php echo (int)$post->ID; ?>"> <?php esc_html_e('View All Applications', 'ApplyOnline'); ?></a></p>
                </div>
                <div id="expiration" class="aol-tab-data wrap">
                    <h3><?php esc_html_e('Expiration date and time', 'ApplyOnline'); ?></h3>
                    <p><i><?php esc_html_e('Leave empty to never close this ad.', 'ApplyOnline') ?></i></p>
                    <input type="text" placeholder="<?php esc_attr_e('Date'); ?>" name="_aol_ad_closing_date" class="datepicker <?php echo $closed_class; ?>" value="<?php echo $date; ?>" />
                    <input type="time" placeholder="<?php esc_attr_e('Time in 24hour format', 'ApplyOnline'); ?>" name="_aol_ad_closing_time" class="datetimepicker" value="<?php echo $time; ?>" />
                    <p><b><?php esc_html_e('Format', 'ApplyOnline'); ?>:</b><i> dd-mm-yyyy</i><br/><b><?php esc_html_e('Example', 'WordPress'); ?>:</b> <i><?php echo current_time('j-m-Y'); ?></i><br/></p>
                    <p class="when-expires"><b><?php esc_html_e('When Expires', 'ApplyOnline'); ?>:</b><br /> <label for="hide_form" style="display: inline-block"><input type="radio" id="hide_form" name="_aol_ad_close_type" value="form" <?php echo $close_form; ?> /><?php esc_html_e('Hide form only', 'ApplyOnline'); ?></label><br />
                    <label for="hide_ad" style="display: inline-block"><input type="radio" id="hide_ad" name="_aol_ad_close_type" value="ad" <?php echo $close_ad; ?> /><?php esc_html_e('Hide ad completely', 'ApplyOnline'); ?></label></p>                
                </div>
                <?php do_action('aol_ad_close_before', $post); ?>
                <div id="recipients" class="aol-tab-data wrap">
                    <p class="description"><?php esc_html_e('Leave these fields intact to use global settings for the ad.', 'ApplyOnline'); ?></p>
                    <h3><?php esc_html_e('New application alert recipients', 'ApplyOnline'); ?></h3>
                    <textarea name="_recipients_emails"><?php echo sanitize_textarea_field($recipients); ?></textarea>
                    <p class="description"><?php esc_html_e('One email address in one line. Mail send limit imposed by your web hosting/server may affect mail delivery.', 'ApplyOnline'); ?><!--Upgrade to use Mailchimp extension--></p>
=======
                    <a class="aol-tab nav-tab active" data-id="shortcodes"><?php echo esc_html_e('Shortcodes', 'apply-online'); ?></a>
                    <a class="aol-tab nav-tab" data-id="expiration"><?php echo esc_html_e('Expiration', 'apply-online'); ?></a>
                    <a class="aol-tab nav-tab" data-id="recipients"><?php esc_html_e('Email Recipients', 'apply-online'); ?></a>                    
                </div>
                <div id="shortcodes" class="aol-tab-data wrap" style="display:block;">
                    <?php do_action('aol_metabox_before', $post); ?>
                    <p class="description"><?php esc_html_e('Use these shortcodes to display this ad or form on a WordPress page. To list all ads on a page, use [aol] shortcode instead.', 'apply-online'); ?></p>
                    <p><label for="ad-shortcode"><?php esc_html_e('Ad shortcode','apply-online'); ?></label><input id="ad-shortcode" type="text" value="[aol_ad id=<?= $post->ID; ?>]" readonly></p>
                    <p><label for="form-shortcode"><?php esc_html_e('Form shortcode', 'apply-online'); ?></label><input id="form-shortcode" type="text" value="[aol_form id=<?= $post->ID; ?>]" readonly></p>
                    <p><a rel="permalink" href="<?php echo admin_url('edit.php?post_type=aol_application'); ?>&ad=<?php echo (int)$post->ID; ?>"> <?php esc_html_e('View All Applications', 'apply-online'); ?></a></p>
                </div>
                <div id="expiration" class="aol-tab-data wrap">
                    <h3><?php esc_html_e('Expiration date and time', 'apply-online'); ?></h3>
                    <p><i><?php esc_html_e('Leave empty to never close this ad.', 'apply-online') ?></i></p>
                    <input type="text" placeholder="<?php esc_attr_e('Date'); ?>" name="_aol_ad_closing_date" class="datepicker <?php echo $closed_class; ?>" value="<?php echo $date; ?>" />
                    <input type="time" placeholder="<?php esc_attr_e('Time in 24hour format', 'apply-online'); ?>" name="_aol_ad_closing_time" class="datetimepicker" value="<?php echo $time; ?>" />
                    <p><b><?php esc_html_e('Format', 'apply-online'); ?>:</b><i> dd-mm-yyyy</i><br/><b><?php esc_html_e('Example', 'WordPress'); ?>:</b> <i><?php echo current_time('j-m-Y'); ?></i><br/></p>
                    <p class="when-expires"><b><?php esc_html_e('When Expires', 'apply-online'); ?>:</b><br /> <label for="hide_form" style="display: inline-block"><input type="radio" id="hide_form" name="_aol_ad_close_type" value="form" <?php echo $close_form; ?> /><?php esc_html_e('Hide form only', 'apply-online'); ?></label><br />
                    <label for="hide_ad" style="display: inline-block"><input type="radio" id="hide_ad" name="_aol_ad_close_type" value="ad" <?php echo $close_ad; ?> /><?php esc_html_e('Hide ad completely', 'apply-online'); ?></label></p>                
                </div>
                <?php do_action('aol_ad_close_before', $post); ?>
                <div id="recipients" class="aol-tab-data wrap">
                    <p class="description"><?php esc_html_e('Leave these fields intact to use global settings for the ad.', 'apply-online'); ?></p>
                    <h3><?php esc_html_e('New application alert recipients', 'apply-online'); ?></h3>
                    <textarea name="_recipients_emails"><?php echo sanitize_textarea_field($recipients); ?></textarea>
                    <p class="description"><?php esc_html_e('One email address in one line. Mail send limit imposed by your web hosting/server may affect mail delivery.', 'apply-online'); ?><!--Upgrade to use Mailchimp extension--></p>
>>>>>>> Stashed changes
                </div>
                 <?php 
                    foreach($extra_tabs as $tab):
                        //if( !empty($extra_tabs) ) echo '<div id="'.esc_attr($tab['id']).'" class="aol-tab-data wrap">'.$tab['content'].'</div>';
                    endforeach;
                ?>
           </div>
            <?php 
                //do_action('aol_ad_options', $post);
                do_action('aol_metabox_after', $post);
                echo ob_get_clean();
        }
    }
    
    class Applyonline_Ads extends Applyonline_Admin{
        function __construct() {
            //add_action('post_submitbox_misc_actions', array($this, 'aol_ad_options'));//optional
            add_action( 'add_meta_boxes', array($this, 'aol_meta_boxes'),1 );
            add_filter( 'manage_aol_ad_posts_columns', array ( $this, 'ads_extra_columns' ) );
            add_action( 'manage_aol_ad_posts_custom_column', array( $this, 'ads_extra_columns_values' ), 10, 2 );
        }
        
        /**
	 * Metaboxes for Ads Editor
	 *
	 * @since     1.0
	 */
        function aol_meta_boxes($post) {
            $screens = array('aol_ad');
            $types = get_option_fixed('aol_ad_types');
            if(is_array($types)){
                foreach ($types as $type){
                    $screens[] = 'aol_'.strtolower($type['singular']);
                }
            }
            if(empty($screens) or !is_array($screens)) $screens = array();

            add_meta_box(
                'aol_ad_metas',
<<<<<<< Updated upstream
                '<span class="dashicons dashicons-admin-site"></span> '.esc_html__('Ad Features', 'ApplyOnline' ),
=======
                '<span class="dashicons dashicons-admin-site"></span> '.esc_html__('Ad Features', 'apply-online' ),
>>>>>>> Stashed changes
                array($this, 'ad_features'),
                $screens,
                'advanced',
                'high'
            );
        }
               
        public function ad_features( $post ){

            // Add a nonce field so we can check for it later.
                wp_nonce_field( 'myplugin_adpost_meta_awesome_box', 'adpost_meta_box_nonce' );
                $keys = get_post_custom_keys( $post->ID);
                $features = array();
                if($keys != NULL):
                    foreach($keys as $key):
                        if(substr($key, 0, 13)=='_aol_feature_'){
                            $features[$key] = get_post_meta($post->ID, $key, TRUE);
                        }
                    endforeach;
                endif;
                $features = apply_filters('aol_features', $features, $post);

                /*
                 * Use get_post_meta() to retrieve an existing value
                 * from the database and use the value for the form.
                 */
            ?>
            <div class="ad_features adpost_fields">
                <?php do_action('aol_before_feature');?>
                <ol id="ad_features">
                    <?php
                        foreach($features as $key => $val):
                            $key = sanitize_key($key);
                             echo '<li>';
                                //echo '<label for="'.$key.'">'. str_replace('_',' ', $key) . '</label>';
                                if( is_array( $val) ){
                                    echo '<input type="text" id="'.$key.'-label" name="'.$key.'[label]" value="'.esc_attr( $val['label'] ).'" placeholder="Label" /> &nbsp; <input type="text" id="'.$key.'-value" name="'.$key.'[value]" value="'.esc_attr( $val['value'] ).'" placeholder="Value" /> &nbsp; <div class="button aol-remove"><span class="dashicons dashicons-remove"></span> Delete</div></li>';
                                } else{
                                    echo '<input type="text" id="'.$key.'" name="'.$key.'" value="'.esc_attr( $val ).'" /> &nbsp; <div class="button aol-remove">Delete</div>';
                                }   
                             echo '</li>';
                        endforeach;
                    ?>
                </ol>
            </div>
            <hr />
            <table id="adfeatures_form" class="alignleft">
            <tbody>
                <tr>
                    <td colspan="2">
                        &nbsp; &nbsp; &nbsp; 
<<<<<<< Updated upstream
                        <input type="text" id="adfeature_name" placeholder="<?php esc_attr_e('Feature','ApplyOnline');?>" /> &nbsp;
                        <input type="text" id="adfeature_value" placeholder="<?php esc_attr_e('Value','ApplyOnline');?>" /> &nbsp; 
                        <div class="button aol-add" id="addFeature"><span class="dashicons dashicons-insert"></span> <?php esc_html_e('Add','ApplyOnline');?></div>
=======
                        <input type="text" id="adfeature_name" placeholder="<?php esc_attr_e('Feature','apply-online');?>" /> &nbsp;
                        <input type="text" id="adfeature_value" placeholder="<?php esc_attr_e('Value','apply-online');?>" /> &nbsp; 
                        <div class="button aol-add" id="addFeature"><span class="dashicons dashicons-insert"></span> <?php esc_html_e('Add','apply-online');?></div>
>>>>>>> Stashed changes
                    </td>
                </tr>
            </tbody>
            </table>
            <div class="clearfix clear"></div>
            <?php 
        }
        
        public function ads_extra_columns( $columns ){
            $columns['date'] = esc_html__( 'Published' );
<<<<<<< Updated upstream
            $columns['closing'] =  esc_html__( 'Closing', 'ApplyOnline' );
=======
            $columns['closing'] =  esc_html__( 'Closing', 'apply-online' );
>>>>>>> Stashed changes
            return $columns;
        }
        
        public function ads_extra_columns_values($column, $post_id){
            switch ( $column ) :
                case 'closing':
                    $date = get_post_meta($post_id, '_aol_ad_closing_date', TRUE);
                    $date = empty($date) ? $date = '--': date_i18n(get_option('date_format'), $date);
                    echo esc_html(apply_filters('aol_ads_table_closing_date', $date, $post_id));
                    break;
            endswitch;
        }
    }
    
    class Applyonline_Applications{
        public function __construct(){
                
            //Add Application data to the Application editor. 
            add_action ( 'edit_form_after_title', array ( $this, 'aol_application_post_editor' ) );
            add_filter('post_row_actions',array($this, 'aol_post_row_actions'), 10, 2);
            add_action('admin_init', array($this, 'alter_metaboxes_on_application_page'));
            add_action( 'add_meta_boxes', array($this, 'aol_meta_boxes'),1 );
            add_action('save_post_aol_application', array($this, 'save_application'));
            add_action('init', array($this, 'application_print'));
            add_action('manage_posts_extra_tablenav', array($this, 'applications_table_filter') );
            
            /*Preview or Quickview an application.*/
            add_action( 'admin_action_aol_modal_box', array ( $this, 'application_quick_view') );
            
            add_filter( 'post_date_column_status', array($this, 'application_date_column'), 10, 2);

            // Hook - Applicant Listing - Column Name
            add_filter( 'manage_edit-aol_application_columns', array ( $this, 'applicants_list_columns' ) );

            // Hook - Applicant Listing - Column Value
            add_action( 'manage_aol_application_posts_custom_column', array ( $this, 'applicants_list_columns_value' ), 10, 2 ); 
            
            //Filter Applications based on parent.
            add_action( 'pre_get_posts', array($this, 'applications_filter') );
            
            add_filter( 'bulk_actions-edit-aol_application', array($this, 'custom_bulk_actions') );
            add_filter( 'handle_bulk_actions-edit-aol_application', array($this, 'my_bulk_action_handler'), 10, 3 );
        }
        
        function custom_bulk_actions($actions){
            $stauses = aol_app_statuses_active();
            foreach($stauses as $key => $val){
                $actions['change_to_'.$key] = sprintf(esc_html__('Change to %s'), $val);
            }
            return $actions;
        }
        
        function my_bulk_action_handler($redirect_to, $term, $post_ids){
            if( !current_user_can('delete_applications') ) return;
            
            $stauses = aol_app_statuses_active();
            $stauses = array_keys($stauses);
            $term = str_replace('change_to_', '', $term);
            if ( !in_array($term, $stauses) )  return $redirect_to;
            
            foreach ( $post_ids as $post_id ) {
                $result =  wp_set_post_terms( $post_id, $term, 'aol_application_status' );
                do_action('aol_application_status_change', $result[0], $post_id);
            }
            //$redirect_to = add_query_arg( 'bulk_emailed_posts', count( $post_ids ), $redirect_to );
            return $redirect_to;
        }
        
        public function aol_post_row_actions($actions, $post){
            $types = get_aol_ad_types();
            if($post->post_type == 'aol_application'){
                $actions = array(); //Empty actions.
                $filter = isset($_GET['aol_application_status']) ? '&aol_application_status=pending' : NULL;
<<<<<<< Updated upstream
                $actions['filters'] = '<a rel="permalink" title="'. esc_attr__('Filter Similar Applications', 'ApplyOnline').'" href="'.  admin_url('edit.php?post_type=aol_application').'&ad='.$post->post_parent.$filter.'"><span class="dashicons dashicons-filter"></span></a>';
                $actions['ad'] = '<a rel="permalink" title="'.esc_attr__('Edit Ad', 'ApplyOnline').'" href="'.  admin_url('post.php?action=edit').'&post='.$post->post_parent.'"><span class="dashicons dashicons-admin-tools"></span></a>';
                $actions['view'] = '<a rel="permalink" title="'.esc_attr__('View Ad', 'ApplyOnline').'" target="_blank" href="'.  get_the_permalink($post->post_parent). '"><span class="dashicons dashicons-external"></span></a>';
            }
            elseif( in_array($post->post_type, $types) ){
                $actions['test'] = '<a rel="permalink" title="'.esc_attr__('View All Applications', 'ApplyOnline').'" href="'.  admin_url('edit.php?post_type=aol_application').'&ad='.$post->ID.'">'.esc_html__('Applications', 'ApplyOnline').'</a>';
=======
                $actions['filters'] = '<a rel="permalink" title="'. esc_attr__('Filter Similar Applications', 'apply-online').'" href="'.  admin_url('edit.php?post_type=aol_application').'&ad='.$post->post_parent.$filter.'"><span class="dashicons dashicons-filter"></span></a>';
                $actions['ad'] = '<a rel="permalink" title="'.esc_attr__('Edit Ad', 'apply-online').'" href="'.  admin_url('post.php?action=edit').'&post='.$post->post_parent.'"><span class="dashicons dashicons-admin-tools"></span></a>';
                $actions['view'] = '<a rel="permalink" title="'.esc_attr__('View Ad', 'apply-online').'" target="_blank" href="'.  get_the_permalink($post->post_parent). '"><span class="dashicons dashicons-external"></span></a>';
            }
            elseif( in_array($post->post_type, $types) ){
                $actions['test'] = '<a rel="permalink" title="'.esc_attr__('View All Applications', 'apply-online').'" href="'.  admin_url('edit.php?post_type=aol_application').'&ad='.$post->ID.'">'.esc_html__('Applications', 'apply-online').'</a>';
>>>>>>> Stashed changes
            }
            return apply_filters('aol_application_row_actions', $actions);
        }
        
        /**
         * Creates Detail Page for Applicants
         * 
         * 
         * @access  public
         * @since   1.0.0
         * @return  void
         */
        public function aol_application_post_editor ($post){
            //global $post;
            if ( !empty( $post ) and $post->post_type =='aol_application' ):
                ?>
                <div class="wrap"><div id="icon-tools" class="icon32"></div>
                    <h3>#<?php echo (int)$post->ID.' - '. sanitize_text_field($post->post_title); ?></h3><hr />
                        <?php 
                        /*
                        _aol_attachment feature has been obsolete since version 1.4, It is now being treated as Post Meta.
                        if ( in_array ( '_aol_attachment', $keys ) ):
                            $files = get_post_meta ( $post->ID, '_aol_attachment', true );
                            ?>
<<<<<<< Updated upstream
                        &nbsp; &nbsp; <small><a href="<?php echo esc_url(get_post_meta ( $post->ID, '_aol_attachment', true )); ?>" target="_blank" ><?php echo esc_html__( 'Attachment' , 'ApplyOnline' );?></a></small>
=======
                        &nbsp; &nbsp; <small><a href="<?php echo esc_url(get_post_meta ( $post->ID, '_aol_attachment', true )); ?>" target="_blank" ><?php echo esc_html__( 'Attachment' , 'apply-online' );?></a></small>
>>>>>>> Stashed changes
                        <?php 
                        endif; 
                         * 
                         */
                        ?>
                    <?php do_action('aol_before_application', $post); ?>
                    <?php echo aol_application_table($post); ?>
                    <?php do_action('aol_after_application', $post); ?>
                </div>
                <?php
            endif;
        }        
        
        function aol_meta_boxes(){
            add_meta_box(
                'aol_application',
<<<<<<< Updated upstream
                esc_html__( 'Application Detail', 'ApplyOnline' ),
=======
                esc_html__( 'Application Detail', 'apply-online' ),
>>>>>>> Stashed changes
                array($this, 'application_sidebar'),
                'aol_application',
                'side'
            );
        }
                
        public function alter_metaboxes_on_application_page(){
            remove_meta_box('commentstatusdiv', 'aol_application', 'normal'); //Hide discussion meta box.
            remove_meta_box('submitdiv', 'aol_application', 'side');
        }
            
        function save_application($post_id){
            if ( wp_is_post_revision( $post_id ) ) return;
            // Check if this post is in default category
            if ( isset($_POST['aol_tag']) AND !empty($_POST['aol_tag']) ){
                $term = sanitize_key($_POST['aol_tag']);
                $result = current_user_can('delete_applications') ? wp_set_post_terms( $post_id, $term, 'aol_application_status' ): array();
                do_action('aol_application_status_change', $result[0], $post_id);
            }
        }
        
        function application_sidebar(){
            global $post;
            $post_terms = get_the_terms( $post->ID, 'aol_application_status');
            $stauses = aol_app_statuses_active();
            ?>
            <div class="submitpost">
                <div class="minor-publishing-actions">
                    <p class="post-attributes-label-wrapper">
<<<<<<< Updated upstream
                        <!--<a href="<?php admin_url(); ?>?aol_page=print&id=<?php echo (int)$post->ID; ?>" target="_blank" alt="<?php esc_attr_e('Print Application','ApplyOnline');?>"><span class="dashicons dashicons-printer"></span></a>-->
                        <a href="<?php admin_url(); ?>?aol_page=print&id=<?php echo (int)$post->ID; ?>" class="button button-secondary button-large" target="_blank"><?php esc_html_e('Print Application','ApplyOnline');?></a>
=======
                        <!--<a href="<?php admin_url(); ?>?aol_page=print&id=<?php echo (int)$post->ID; ?>" target="_blank" alt="<?php esc_attr_e('Print Application','apply-online');?>"><span class="dashicons dashicons-printer"></span></a>-->
                        <a href="<?php admin_url(); ?>?aol_page=print&id=<?php echo (int)$post->ID; ?>" class="button button-secondary button-large" target="_blank"><?php esc_html_e('Print Application','apply-online');?></a>
>>>>>>> Stashed changes
                    </p>
                    <?php 
                    do_action('aol_app_updatebox_after');
                    if(current_user_can('delete_applications')){
                    ?>
<<<<<<< Updated upstream
                        <p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="parent_id"><?php esc_html_e('Application Status','ApplyOnline');?></label></p>
=======
                        <p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="parent_id"><?php esc_html_e('Application Status','apply-online');?></label></p>
>>>>>>> Stashed changes
                        <select class="aol_select" name="aol_tag">
                            <?php
                            foreach($stauses as $key => $val){
                                $selected = ( $key == $post_terms[0]->slug ) ? 'selected' : NULL;
                                echo '<option value="'. sanitize_key($key).'" '.$selected.'>'. esc_html__($val, 'apply-online').'</option>';
                            }
                            ?>
                        </select>
<<<<<<< Updated upstream
                        <p class="description"><?php esc_html_e('An email will be sent to the applicant on each status change.', 'ApplyOnline'); ?></p>
=======
                        <p class="description"><?php esc_html_e('An email will be sent to the applicant on each status change.', 'apply-online'); ?></p>
>>>>>>> Stashed changes
                <?php } ?>
                </div>
                <div id="major-publishing-actions">
                    <div id="delete-action">
                    <a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php esc_html_e('Move to Trash','apply-online');?></a></div>

                    <div id="publishing-action">
                    <span class="spinner"></span>
                        <input name="original_publish" id="original_publish" value="Update" type="hidden">
                        <input name="save" class="button button-primary button-large" id="publish" value="Update" type="submit">
                    </div>
                    <div class="clear"></div>
                </div>  
            </div>
            <?php
        }

        public function application_print(){
            if(
                    current_user_can('edit_applications')
                    AND isset($_GET['aol_page'])
                    AND $_GET['aol_page'] == 'print'
                    AND isset($_GET['id'])
                ){
                
                $ad_id = (int)$_GET['id']; //Sanitize ad id
                
                $post = get_post( $ad_id );
                if( is_null($post) ) wp_die('Inavalid Post');

                $GLOBALS['post'] = $post; //Support AOL Tracker plugin.
                $parent = get_post($post->post_parent);
                ?>
                <!DOCTYPE html>
                <html <?php language_attributes(); ?>>
                    <head>
                        <meta charset="<?php bloginfo( 'charset' ); ?>" />
                        <link rel="profile" href="http://gmpg.org/xfn/11" />
                        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
                        <title><?php esc_html_e('Application','apply-online');?> <?php echo (int)$ad_id; ?> - <?php esc_html_e('ApplyOnline','apply-online');?></title>
                        <?php /*End of WP official headers*/ ?>
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <meta name="robots" content="noindex,nofollow">
                        <link rel='stylesheet' id='single-style-css'  href='<?php echo plugin_dir_url(__FILE__); ?>css/print.css?ver=<?php echo $this->version; ?>' type='text/css' media='all' />
                    </head>
                <body class="body wpinv print">
                    <div class="row top-bar no-print">
                        <div class="container">
                            <div class="col-xs-6">
                                <a class="btn btn-primary btn-sm" onclick="window.print();" href="javascript:void(0)"> <?php esc_html_e('Print Application','apply-online');?></a>
                            </div>
                        </div>
                    </div>
                    <div class="container wrap">
                        <htmlpageheader name="pdf-header">
                            <div class="row header">
                                <div class="col-md-9 business">
                                    #<?php echo (int)$ad_id; ?>
                                    <h3><?php echo sanitize_text_field($post->post_title); ?></h3>
                                    <?php echo sanitize_text_field($post->post_date); ?>
                                    <?php add_action('aol_print_header_left', $post); ?>
                                </div>

                                <div class="col-md-3">
<<<<<<< Updated upstream
                                     <?php esc_html_e('Application','ApplyOnline');?>
=======
                                     <?php esc_html_e('Application','apply-online');?>
>>>>>>> Stashed changes
                                    <h3><?php bloginfo('name'); ?></h3>
                                    <?php add_action('aol_print_header_right', $post); ?>
                                </div>
                            </div>
                        </htmlpageheader>
                        <?php do_action('aol_print_before_application', $post); ?>
                        <?php echo aol_application_table($post, 'table table-sm table-bordered table-responsive'); ?>
                        <?php do_action('aol_print_after_application'); ?>
                        <htmlpagefooter name="wpinv-pdf-footer">
                            <div class="row wpinv-footer">
                                <div class="col-sm-12">
                                    <div class="footer-text"><a target="_blank" href="<?php bloginfo('url') ?>" ><?php bloginfo('url'); ?></a></div>
                                </div>
                            </div>
                    </htmlpagefooter>
                    </div>
                </body>
                </html>
            <?php 
            exit();
            }
        }

        function applications_table_filter(){
            global $typenow;
            if('aol_application' == $typenow):
            ?>
<<<<<<< Updated upstream
                &nbsp; <select id="aol-apps-table-search"><option><?php esc_html_e('Filter Applications', 'ApplyOnline'); ?></option></select>
=======
                &nbsp; <select id="aol-apps-table-search"><option><?php esc_html_e('Filter Applications', 'apply-online'); ?></option></select>
>>>>>>> Stashed changes
            <?php
            endif;
        }
        
        /**
         * Applicant Listing - Column Name
         *
         * @param   array   $columns
         * @access  public
         * @return  array
         */
        public function applicants_list_columns( $columns ){
            $columns = array (
                'cb'       => '<input type="checkbox" />',
<<<<<<< Updated upstream
                'id'    => esc_html__( 'ID', 'ApplyOnline' ),
                'title'    => esc_html__( 'Ad Title', 'ApplyOnline' ),
                'qview'      => NULL,
                'applicant'=> esc_html__( 'Applicant', 'ApplyOnline' ),
                'taxonomy' => esc_html__( 'Status', 'ApplyOnline' ),
            );
            $columns = apply_filters('aol_application_posts_columns', $columns);
            $columns['date'] = esc_html__( 'Date', 'ApplyOnline' );
=======
                'id'    => esc_html__( 'ID', 'apply-online' ),
                'title'    => esc_html__( 'Ad Title', 'apply-online' ),
                'qview'      => NULL,
                'applicant'=> esc_html__( 'Applicant', 'apply-online' ),
                'taxonomy' => esc_html__( 'Status', 'apply-online' ),
            );
            $columns = apply_filters('aol_application_posts_columns', $columns);
            $columns['date'] = esc_html__( 'Date', 'apply-online' );
>>>>>>> Stashed changes
            return $columns;
        }

        /**
         * Applicant Listing - Column Value
         *
         * @param   array   $columns
         * @param   int     $post_id
         * @access  public
         * @return  void
         */
        public function applicants_list_columns_value( $column, $post_id ){
            $keys = get_post_custom_keys( $post_id ); $values = get_post_meta($post_id); 
            $new = array();
            foreach($values as $key => $val){
                $new[$key]=$val[0];
            }
            $name = aol_array_find('Name', $keys);
            switch ( $column ) {
                case 'id' :
                    echo $post_id;
                 break;
                case 'qview' :
                     add_thickbox();
                     $url = add_query_arg( array(
                        'action'    => 'aol_modal_box',
                        'app_id'   => $post_id,
                        'TB_iframe' => 'true',
                    ), admin_url( 'admin.php' ) );

<<<<<<< Updated upstream
                    echo '<a href="' . esc_url($url) . '" class="thickbox" title="'. esc_attr__('Quick View', 'ApplyOnline').'"><span class="dashicons dashicons-visibility"></span></a>';
                 break;
                case 'applicant' :
                    if($name === FALSE):
                        $applicant_name = esc_html__('Undefined', 'ApplyOnline');
=======
                    echo '<a href="' . esc_url($url) . '" class="thickbox" title="'. esc_attr__('Quick View', 'apply-online').'"><span class="dashicons dashicons-visibility"></span></a>';
                 break;
                case 'applicant' :
                    if($name === FALSE):
                        $applicant_name = esc_html__('Undefined', 'apply-online');
>>>>>>> Stashed changes
                    else:
                        $applicant = apply_filters( 'aol_applicants_table_name_column', get_post_meta( $post_id, $keys[ $name ], TRUE ), $post_id, $keys[ $name ] );
                        if(is_object($applicant)) $applicant = NULL;
                        elseif(is_array($applicant))    $applicant = implode(',', $applicant);

                        $applicant_name = sprintf( 
                                '<a href="%s">%s</a>', 
                                esc_url( add_query_arg( array ( 'post' => $post_id, 'action' => 'edit' ), 'post.php' ) ), 
                                esc_html( $applicant )
                        );
                    endif;
                    echo sanitize_text_field($applicant_name); 
                    break;
                case 'taxonomy' :
                    //$parent_id = wp_get_post_parent_id( $post_id ); // get_post_field ( 'post_parent', $post_id );
                    $terms = get_the_terms( $post_id, 'aol_application_status' );
                    $statuses = aol_app_statuses();
                    if ( ! empty( $terms ) ) {
                        $out = array ();
                        foreach ( $terms as $term ){
                            $status_name = isset($statuses[$term->slug]) ? $statuses[$term->slug] : $term->name;
                            $out[] = sprintf( 
                                    '<a href="%s">%s</a>', 
                                    esc_url( add_query_arg( array ( 'post_type' => 'aol_application', 'aol_application_status' => $term->slug ), 'edit.php' ) ),
                                    esc_html( sanitize_term_field( 'name', __($status_name, 'apply-online'), $term->term_id, 'aol_application_status', 'display' ) )
                            );
                        }
                        echo sanitize_text_field(join( ', ', $out ));
                    }/* If no terms were found, output a default message. */ else {
<<<<<<< Updated upstream
                        esc_html_e( 'Undefined' , 'ApplyOnline');
=======
                        esc_html_e( 'Undefined' , 'apply-online');
>>>>>>> Stashed changes
                    }
                    break;
            }
        }                

        /**
         * Quick View application
         *
         * @param   array   $columns
         * @access  public
         * @return  array
         */
       public function application_quick_view() {
           if( !current_user_can('manage_ads') OR empty( (int)$_GET['app_id']) ) die('Are you nuts?');

            $ad_id  = (int)$_GET['app_id'];
            $post = get_post( $ad_id );
           
            define( 'IFRAME_REQUEST', true );
            iframe_header();
            
            $this->aol_application_post_editor($post);
            iframe_footer();
            exit;
        }

        function application_date_column($status, $post ){
            if($post->post_type == 'aol_application') $status = esc_html__('Received');
            return $status;
        }  
        
        public function applications_filter( $query ) {
            if ( $query->is_main_query() AND is_admin() AND isset($_GET['ad'])) {
                $parent_id = (int)$_GET['ad'];
                //If Polaylang is active
                if(function_exists('pll_get_post_translations')){
                    $post_translation_ids = pll_get_post_translations( $parent_id );
                    $post_translation_ids[] = $parent_id;
                    $query->set( 'post_parent__in', $post_translation_ids );
                    //$query->set( 'orderby', 'title' );
                    //$query->set( 'order', 'ASC' );
                    //$query->set( 'post_parent', NULL );
                } else{
                    $query->set( 'post_parent', $parent_id ); //Set post parent ID.
                }

            }
        }
        
    }

  /**
  * This class adds Meta Boxes to the Edit Screen of the Ads.
  * 
  * 
  * @since      1.0
  * @package    MetaBoxes
  * @subpackage MetaBoxes/includes
  * @author     Farhan Noor
  **/
 class Applyonline_Form_Builder{
     
        /**
	 * Application Form Field Types.
	 *
	 * @since    1.3
	 * @access   public
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
        var $app_field_types;
             
        public function __construct() {
            $this->app_field_types = $this->app_field_types();
            
            add_action( 'save_post', array($this, 'save_form_elements'),1 );
            
            add_action( 'add_meta_boxes', array($this, 'aol_meta_boxes'),1 );
            
            /*Ajax Calls*/
            add_action("wp_ajax_aol_template_render", array($this, "template_form_callback"));
            add_action("wp_ajax_aol_ad_form_render", array($this, "aol_ad_form_render"));
        }
        
        //Save From Elements in the Database
        function save_form_elements( $post_id ){
            /*
             * We need to verify this came from our screen and with proper authorization,
             * because the save_post action can be triggered at other times.
             */
            
            if( !current_user_can('edit_ads') ) 
                return;

            // Check if our nonce is set.
            if ( ! isset( $_POST['adpost_meta_box_nonce'] ) ) {
                return;
            }

            // Verify that the nonce is valid.
            if ( ! wp_verify_nonce( $_POST['adpost_meta_box_nonce'], 'myplugin_adpost_meta_awesome_box' ) ) {
                return;
            }

            /* OK, it's safe for us to save the data now. */
            $types = get_aol_ad_types();
            if ( !in_array($_POST['post_type'], $types) ) return;

            //Delete fields.
            $old_keys = (array)get_post_custom_keys($post_id);
            $new_keys = array_keys($_POST);
            $new_keys = array_map('sanitize_key', $new_keys); //First santize all keys.
            $removed_keys = array_diff($old_keys, $new_keys); //List of removed meta keys.
            foreach($removed_keys as $key => $val):
                if(substr($val, 0, 13) == '_aol_feature_' OR substr($val, 0, 9) == '_aol_app_'){
                        delete_post_meta($post_id, $val); //Remove meta from the db.
                }
            endforeach;
            $existing_keys = array_diff($old_keys, $removed_keys); //List of removed meta keys. UNUSED
            // Add/update new value.
            $fields_order = array();
            foreach ($_POST as $key => $val):
                $key = sanitize_key($key); //Sanitize Key before processing.
                // Make sure that it is set.
                if ( substr($key, 0, 13)=='_aol_feature_' and isset( $val ) ) {
                    //die('Hello World');
                    /*Adding Support for version >= 1.9*/
                    if( !is_array($val) ){
                        $val = array('label' => str_replace('_', ' ',substr($key, 13)), 'value' => sanitize_text_field($val)); //sanitize & convert to array.
                    }
                    //Sanitize user input.
                    $my_data = array_map( 'sanitize_text_field', $val );
                    $restul = update_post_meta( $post_id, $key,  $my_data); // Add new value.
                }
                // Make sure that it is set.
                elseif ( substr($key, 0, 9) == '_aol_app_' and isset( $val ) ) {
                    //$my_data = serialize($val);
                        if(in_array($val['type'], array('separator', 'seprator', 'paragraph'))) $val['required'] = 0;
                        
                        if(isset($val['options'])){
                            $val['options'] = explode(',', $val['options']);
                            $val['options'] = implode(',', array_map('trim',$val['options']));                            
                        }
                        /*END - Remove white spaces */
                    update_post_meta( $post_id, $key, aol_array_map_r( 'sanitize_textarea_field', $val ) ); // Add new value.
                    $fields_order[] = $key;
                }
                // 
            endforeach;
            update_post_meta( $post_id, '_aol_fields_order',  $fields_order); // Add new value.
        }        
        
        /**
	 * Metaboxes for Ads Editor
	 *
	 * @since     1.0
	 */
        function aol_meta_boxes($post) {
            $screens = array('aol_ad');
            $types = get_option_fixed('aol_ad_types');
            if(is_array($types)){
                foreach ($types as $type){
                    $screens[] = 'aol_'.strtolower($type['singular']);
                }
            }
            if(empty($screens) or !is_array($screens)) $screens = array();

            add_meta_box(
                'aol_ad_app_fields',
<<<<<<< Updated upstream
                '<span class="dashicons dashicons-admin-site"></span> '.esc_html__('Application Form Builder', 'ApplyOnline' ),
=======
                '<span class="dashicons dashicons-admin-site"></span> '.esc_html__('Application Form Builder', 'apply-online' ),
>>>>>>> Stashed changes
                array($this, 'application_form_fields'),
                $screens,
                'advanced',
                'high'
            );
                        
            /*
            add_meta_box(
                'aol_form_builder',
<<<<<<< Updated upstream
                esc_html__( 'New Application Form Builder', 'ApplyOnline' ),
=======
                esc_html__( 'New Application Form Builder', 'apply-online' ),
>>>>>>> Stashed changes
                array($this, 'application_form_builder'),
                $screens,
                'advanced',
                'high'
            );
             * 
             */
        }        

        function app_field_types(){
            return array(
                'text'=> esc_html__('Text Field','apply-online'),
                'number'=> esc_html__('Number Field','apply-online'),
                'text_area'=>esc_html__('Text Area','apply-online'),
                'email'=> esc_html__('E Mail Field','apply-online'),
                'date'=>esc_html__('Date Field','apply-online'),
                'checkbox'=>esc_html__('Check Boxes','apply-online'),
                'radio'=> esc_html__('Radio Buttons','apply-online'),
                'dropdown'=>esc_html__('Dropdown Options','apply-online'), 
                'file'=>esc_html__('Attachment Field','apply-online'),
                //'seprator' => 'Seprator', //Deprecated since 1.9.6. Need to be fixed for older versions.
                'separator' => esc_html__('Separator','apply-online'),
                'paragraph' => esc_html__('Paragraph','apply-online'),
                //'url' => esc_html__('URL','apply-online'),
                );
        }

        public function aol_fields_icons($id="") {
            $icons = array(
                'text' => 'dashicons-editor-textcolor',
                'text_area' => 'dashicons-format-aside',
                'number' => 'dashicons-editor-ol',
                'email' => 'dashicons-email-alt',
                'date' => 'dashicons-calendar',
                'checkbox' => 'dashicons-yes',
                'radio' => 'dashicons-marker',
                'dropdown' => 'dashicons-sort',
                'file' => 'dashicons-paperclip',
                'separator' => 'dashicons-minus',
                'paragraph' => 'dashicons-editor-justify',
                'url' => 'dashicons-admin-links'
                );
                //if((float)get_bloginfo('version') < 5) $icons['file'] = 'dashicons-admin-links';
            $icon   = '<i class="dashicons '.$icons[$id].' aol_fields" data-id="'.$id.'"></i>';
            return $icon;
        }

        public function application_fields_generator($app_fields, $temp = NULL){
            add_thickbox();
            $adapp_form_generator = empty($temp) ? 'adapp_form_fields' : 'adapp_generator_'.$temp
            ?>
<<<<<<< Updated upstream
                <a href="#TB_inline?width=700&height=550&inlineId=<?php echo $adapp_form_generator; ?>" class="thickbox textfield-poup" title="<?php esc_attr_e('Select a Type', 'ApplyOnline'); ?>">
                <button type="button" class="button aol-add" ><span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Add Field', 'ApplyOnline'); ?> </button>
=======
                <a href="#TB_inline?width=700&height=550&inlineId=<?php echo $adapp_form_generator; ?>" class="thickbox textfield-poup" title="<?php esc_attr_e('Select a Type', 'apply-online'); ?>">
                <button type="button" class="button aol-add" ><span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Add Field', 'apply-online'); ?> </button>
>>>>>>> Stashed changes
            </a>
            <div id="<?php echo $adapp_form_generator; ?>" class="field-generator adapp_form_fields <?php echo $adapp_form_generator; ?>" style="display:none;">
                <div class="aol-selectors">
                    <table>
                        <tbody>
                            <tr>
                                <td><?php echo $this->aol_fields_icons('text'); ?></td>
                                <td><?php echo $this->aol_fields_icons('text_area'); ?></td>
                                <td><?php echo $this->aol_fields_icons('number'); ?></td>
                                <td><?php echo $this->aol_fields_icons('email'); ?></td>
                                <td><?php echo $this->aol_fields_icons('date'); ?></td>
                                <td><?php echo $this->aol_fields_icons('checkbox'); ?></td>
                                <td><?php echo $this->aol_fields_icons('radio'); ?></td>
                                <td><?php echo $this->aol_fields_icons('dropdown'); ?></td>
                                <td><?php echo $this->aol_fields_icons('file'); ?></td>
                                <td><?php echo $this->aol_fields_icons('separator'); ?></td>
                                <td><?php echo $this->aol_fields_icons('paragraph'); ?></td>
                                <!--<td><?php echo $this->aol_fields_icons('url'); ?></td>-->
                            </tr>
                            <tr>
<<<<<<< Updated upstream
                                <td><?php esc_html_e('TextField', 'ApplyOnline'); ?></td>
                                <td><?php esc_html_e('TextArea', 'ApplyOnline'); ?></td>
                                <td><?php esc_html_e('Number', 'ApplyOnline'); ?></td>
                                <td><?php esc_html_e('Email', 'ApplyOnline'); ?></td>
                                <td><?php esc_html_e('Date', 'ApplyOnline'); ?></td>
                                <td><?php esc_html_e('CheckBox', 'ApplyOnline'); ?></td>
                                <td><?php esc_html_e('RadioBox', 'ApplyOnline'); ?></td>
                                <td><?php esc_html_e('DropDown', 'ApplyOnline'); ?></td>
                                <td><?php esc_html_e('Attachment', 'ApplyOnline'); ?></td>
                                <td><?php esc_html_e('Separator', 'ApplyOnline'); ?></td>
                                <td><?php esc_html_e('Paragraph', 'ApplyOnline'); ?></td>
                                <!--<td><?php esc_html_e('URL', 'ApplyOnline'); ?></td>-->
=======
                                <td><?php esc_html_e('TextField', 'apply-online'); ?></td>
                                <td><?php esc_html_e('TextArea', 'apply-online'); ?></td>
                                <td><?php esc_html_e('Number', 'apply-online'); ?></td>
                                <td><?php esc_html_e('Email', 'apply-online'); ?></td>
                                <td><?php esc_html_e('Date', 'apply-online'); ?></td>
                                <td><?php esc_html_e('CheckBox', 'apply-online'); ?></td>
                                <td><?php esc_html_e('RadioBox', 'apply-online'); ?></td>
                                <td><?php esc_html_e('DropDown', 'apply-online'); ?></td>
                                <td><?php esc_html_e('Attachment', 'apply-online'); ?></td>
                                <td><?php esc_html_e('Separator', 'apply-online'); ?></td>
                                <td><?php esc_html_e('Paragraph', 'apply-online'); ?></td>
                                <!--<td><?php esc_html_e('URL', 'apply-online'); ?></td>-->
>>>>>>> Stashed changes
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="aol_new_form" class="aol_form" style="display:none;">
                    <input type="hidden" name="aol_type" value="">
<<<<<<< Updated upstream
                    <div class="aol_uid"><label for="adapp_uid">*<?php esc_html_e('Unique ID', 'ApplyOnline') ?></label><input class="aol-form-field adapp_uid" type="text" id="adapp_uid" ></div>
                    <div><label for="adapp_name">*<?php esc_html_e('Label', 'ApplyOnline') ?></label><input class="aol-form-field adapp_name" type="text" id="adapp_name" ></div>
                    <div class="aol_help_text aol_add_field"><label for="adapp_field_help"><?php esc_html_e('Help Text', 'ApplyOnline') ?></label><input class="aol-form-field adapp_field_help" id="adapp_field_help" type="text" /></div>
                    <div class="aol_text aol_add_field"><label for="adapp_text"><?php esc_html_e('Text', 'ApplyOnline') ?></label><textarea class="aol-form-field adapp_text" id="adapp_text" type="text" ></textarea><p class="description"><?php esc_html_e('To generate a link, use shortcode [link href="https://google.com" title="My Link Title"]', 'ApplyOnline'); ?></p></div>
                    <div class="aol_text_height aol_add_field"><label fpr="adapp_text_height"><?php esc_html_e('Fixed Height', 'ApplyOnline'); ?></label><input class="aol-form-field adapp_text_height" id="adapp_text_height"  type="number" value="0" />px</div>
                    <div class="aol_placeholder aol_add_field"><label for="adapp_placeholder"><?php esc_html_e('Place Holder', 'ApplyOnline') ?></label><input class="aol-form-field adapp_placeholder" type="text" id="adapp_placeholder" ></div>
    <!--            <div class="aol_value aol_add_field"><label><?php esc_html_e('Defult value', 'ApplyOnline') ?></label><input type="text" id="adapp_value" placeholder="<?php esc_attr_e('Defult value') ?>" ></div>-->
                    <div class="aol_form_options aol_options aol_add_field"><label for="adapp_field_options"><?php esc_html_e('Options', 'ApplyOnline') ?></label><input id="adapp_field_options" class="adapp_field_options" type="text"  placeholder="<?php esc_attr_e('Option 1, Option 2, Option 3', 'ApplyOnline'); ?>" ></div>
                    <div class="aol_class"><label for="adapp_class"><?php esc_html_e('Classes', 'ApplyOnline') ?></label><input type="text" id="adapp_class" class="aol-form-field adapp_class" ></div>
                    <div class="aol_file_types"><label for="adapp_file_types">*<?php esc_html_e('Allowed Types', 'ApplyOnline') ?></label><input type="text" id="adapp_file_types" class="aol-form-field adapp_file_types" value="<?php echo esc_attr( get_option("aol_allowed_file_types") ); ?>" ><p class="description"><?php esc_html_e('Comma seperated values', 'ApplyOnline'); ?></p></div>
                    <div class="aol_file_max_size"><label for="aol_file_max_size">*<?php esc_html_e('Max Size Limit', 'ApplyOnline') ?></label><input type="number" id="adapp_file_max_size" class="adapp_file_max_size" value="<?php echo esc_attr(get_option('aol_form_max_upload_size')); ?>" placeholder="<?php echo floor(wp_max_upload_size()/1000000); ?> " >MB</div>
                    <div class="aol_limit aol_add_field"><label for="adapp_limit"><?php esc_html_e('Charcter Limit', 'ApplyOnline') ?></label><input id="adapp_limit" class="adapp_limit" type="number" min="1"  placeholder="<?php esc_attr_e('No Limit', 'ApplyOnline'); ?>" ></div>
                    <div class="aol_preselect aol_add_field"><label for="aol_preselect"><?php esc_html_e('Preselect', 'ApplyOnline');?></label><input class="required_preselect adapp_preselect" type="checkbox" id="aol_preselect" checked value="1" /><i class="description"><?php esc_html_e('Default first field selection.', 'ApplyOnline'); ?></i> </div>
                    <div class="aol_notification aol_add_field"><label for="aol_notification"><?php esc_html_e('Notify This Email', 'ApplyOnline');?></label><input class="aol_checkbox adapp_notification" type="checkbox" id="aol_notification" value="0" /> </div>
                    <div class="aol_required aol_add_field"><label for="aol_required"><?php esc_html_e('Required Field', 'ApplyOnline');?></label><input class="aol_checkbox adapp_required" type="checkbox" id="aol_required" value="0" /> </div>
                    <!-- <div class="aol_orientation aol_add_field"><label><?php esc_html_e('Orientation', 'ApplyOnline');?></label><label><input class="required_option" type="radio" id="aol_required" checked value="0" /> Horizontal</label> &nbsp; <label><input class="required_option" type="radio" id="aol_required" value="0" />Vertical</label></div> -->
                    <?php do_action('aol_after_admin_form_fields'); ?>
                    <p class="description"><?php esc_html_e('Fields with (*) are compulsory.', 'ApplyOnline'); ?></p>
                    <button type="button" class="button aol-add button-primary addField <?php echo esc_attr($temp); ?>" data-temp="<?php echo esc_attr($temp); ?>"><span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Add Field', 'ApplyOnline'); ?> </button>
=======
                    <div class="aol_uid"><label for="adapp_uid">*<?php esc_html_e('Unique ID', 'apply-online') ?></label><input class="aol-form-field adapp_uid" type="text" id="adapp_uid" ></div>
                    <div><label for="adapp_name">*<?php esc_html_e('Label', 'apply-online') ?></label><input class="aol-form-field adapp_name" type="text" id="adapp_name" ></div>
                    <div class="aol_help_text aol_add_field"><label for="adapp_field_help"><?php esc_html_e('Help Text', 'apply-online') ?></label><input class="aol-form-field adapp_field_help" id="adapp_field_help" type="text" /></div>
                    <div class="aol_text aol_add_field"><label for="adapp_text"><?php esc_html_e('Text', 'apply-online') ?></label><textarea class="aol-form-field adapp_text" id="adapp_text" type="text" ></textarea><p class="description"><?php esc_html_e('To generate a link, use shortcode [link href="https://wordpress.org" title="My Link Title"]', 'apply-online'); ?></p></div>
                    <div class="aol_text_height aol_add_field"><label fpr="adapp_text_height"><?php esc_html_e('Fixed Height', 'apply-online'); ?></label><input class="aol-form-field adapp_text_height" id="adapp_text_height"  type="number" value="0" />px</div>
                    <div class="aol_placeholder aol_add_field"><label for="adapp_placeholder"><?php esc_html_e('Place Holder', 'apply-online') ?></label><input class="aol-form-field adapp_placeholder" type="text" id="adapp_placeholder" ></div>
    <!--            <div class="aol_value aol_add_field"><label><?php esc_html_e('Defult value', 'apply-online') ?></label><input type="text" id="adapp_value" placeholder="<?php esc_attr_e('Defult value') ?>" ></div>-->
                    <div class="aol_form_options aol_options aol_add_field"><label for="adapp_field_options"><?php esc_html_e('Options', 'apply-online') ?></label><input id="adapp_field_options" class="adapp_field_options" type="text"  placeholder="<?php esc_attr_e('Option 1, Option 2, Option 3', 'apply-online'); ?>" ></div>
                    <div class="aol_class"><label for="adapp_class"><?php esc_html_e('Classes', 'apply-online') ?></label><input type="text" id="adapp_class" class="aol-form-field adapp_class" ></div>
                    <div class="aol_file_types"><label for="adapp_file_types">*<?php esc_html_e('Allowed Types', 'apply-online') ?></label><input type="text" id="adapp_file_types" class="aol-form-field adapp_file_types" value="<?php echo esc_attr( get_option("aol_allowed_file_types") ); ?>" ><p class="description"><?php esc_html_e('Comma seperated values', 'apply-online'); ?></p></div>
                    <div class="aol_file_max_size"><label for="aol_file_max_size">*<?php esc_html_e('Max Size Limit', 'apply-online') ?></label><input type="number" id="adapp_file_max_size" class="adapp_file_max_size" value="<?php echo esc_attr(get_option('aol_form_max_upload_size')); ?>" placeholder="<?php echo floor(wp_max_upload_size()/1000000); ?> " >MB</div>
                    <div class="aol_limit aol_add_field"><label for="adapp_limit"><?php esc_html_e('Charcter Limit', 'apply-online') ?></label><input id="adapp_limit" class="adapp_limit" type="number" min="1"  placeholder="<?php esc_attr_e('No Limit', 'apply-online'); ?>" ></div>
                    <div class="aol_preselect aol_add_field"><label for="aol_preselect"><?php esc_html_e('Preselect', 'apply-online');?></label><input class="required_preselect adapp_preselect" type="checkbox" id="aol_preselect" checked value="1" /><i class="description"><?php esc_html_e('Default first field selection.', 'apply-online'); ?></i> </div>
                    <div class="aol_notification aol_add_field"><label for="aol_notification"><?php esc_html_e('Notify This Email', 'apply-online');?></label><input class="aol_checkbox adapp_notification" type="checkbox" id="aol_notification" value="0" /> </div>
                    <div class="aol_required aol_add_field"><label for="aol_required"><?php esc_html_e('Required Field', 'apply-online');?></label><input class="aol_checkbox adapp_required" type="checkbox" id="aol_required" value="0" /> </div>
                    <!-- <div class="aol_orientation aol_add_field"><label><?php esc_html_e('Orientation', 'apply-online');?></label><label><input class="required_option" type="radio" id="aol_required" checked value="0" /> Horizontal</label> &nbsp; <label><input class="required_option" type="radio" id="aol_required" value="0" />Vertical</label></div> -->
                    <?php do_action('aol_after_admin_form_fields'); ?>
                    <p class="description"><?php esc_html_e('Fields with (*) are compulsory.', 'apply-online'); ?></p>
                    <button type="button" class="button aol-add button-primary addField <?php echo esc_attr($temp); ?>" data-temp="<?php echo esc_attr($temp); ?>"><span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Add Field', 'apply-online'); ?> </button>
>>>>>>> Stashed changes
                </div>
            </div>
            
        <?php
        }
        
        function aol_ad_form_render(){
            if( !current_user_can('manage_ads') ) die('Are you nuts?');
            
            $post_id = isset($_POST['ad_id']) ? (int)$_POST['ad_id'] : 0;
            $row = get_post_meta($post_id);
            $fields = array();
            foreach($row as $key => $val){
                if( substr($key, 0, 8) == '_aol_app' ) $fields[$key] = maybe_unserialize ($val[0]);
            }
            echo $this->aol_form_template($fields); 
            exit;
        }

        /*
         * An ajax call to return Application Template Form Fields.
         */
        function template_form_callback(){
<<<<<<< Updated upstream
            if( !current_user_can('manage_ads') ) die('Are you nuts?');
=======
            if( !current_user_can('manage_ads') OR !wp_verify_nonce( $_POST['nonce'], 'aol_nonce' ) ) die('Are you nuts?');
>>>>>>> Stashed changes
            
            $fields = get_option('aol_form_templates', array());
            $array = $fields[ sanitize_text_field($_POST['template']) ];
            foreach($array as $key => $field){
                if( substr($key, 0, 4) != '_aol' ) unset($array[$key]);
            }
            echo $this->aol_form_template($array); 
            exit;
        }
        
        function aol_form_template($fields, $tempid = NULL){
            add_thickbox();
            $types_names = $this->app_field_types();
            $req_class = NULL;
             foreach($fields as $key => $val):
                if(substr($key, 0, 9) != '_aol_app_') continue;
                
                //Sanitizing data before output.
                $key = esc_attr($key);
                $label = isset($val['label']) ? sanitize_text_field($val['label']) : str_replace('_',' ',substr($key,9));
                
                if($val['type']=='seprator') $val['type'] = 'separator'; //Fixed bug before 1.9.6, spell mistake in the key.
                //if(!isset($val['required'])) $val['required'] = 1;
               // $req_class = ($val['required'] == 0) ? 'button-disabled': null;
                $fields = NULL;
                $field_types = $this->app_field_types();
                foreach($field_types as $field_key => $field_val){
                    $field_key = esc_attr($field_key);
                    $field_val = sanitize_text_field($field_val);
                    if($val['type'] == $field_key) $fields .= '<option value="'.$field_key.'" selected>'.$field_val.'</option>';
                    else $fields .= '<option value="'.$field_key.'" >'.$field_val.'</option>';
                }
                $req_class .= ($val['type'] == 'separator' OR $val['type'] == 'paragraph') ? ' button-disabled' : ' toggle-required';
                echo '<tr data-id="'.$key.'" class="'.$key.'">';
                    echo '<td><span class="dashicons dashicons-menu"></span> &nbsp;<label for="'.$key.'">'.$label.'</label></td>';
                    echo '<td>';
                        empty($tempid) ? do_action('aol_after_form_field', $key) :  do_action('aol_after_application_template_field', $tempid, $key);
<<<<<<< Updated upstream
                        echo '<div class="aol-edit-form"><a href="#TB_inline?&inlineId='.$key.'" title="'. esc_attr($types_names[$val['type']]).'" class="thickbox dashicons dashicons-edit"></a><span class="dashicons dashicons-no aol-remove" title="'.esc_attr__('Delete', 'ApplyOnline').'" ></span></div>';
=======
                        echo '<div class="aol-edit-form"><a href="#TB_inline?&inlineId='.$key.'" title="'. esc_attr($types_names[$val['type']]).'" class="thickbox dashicons dashicons-edit"></a><span class="dashicons dashicons-no aol-remove" title="'.esc_attr__('Delete', 'apply-online').'" ></span></div>';
>>>>>>> Stashed changes
                        $this->row_popup($key, $val, $tempid);
                    echo '</td>';
                echo '</tr>';
                //}
            endforeach;
        }
        
        public function row_popup($key, $val, $template = NULL){
            
            //Sanitizing data before output.
            $template = sanitize_key($template);
            $key = esc_attr($key);
            $label = isset($val['label']) ? esc_attr($val['label']) : str_replace('_',' ',substr($key,9));
            $description = isset($val['description']) ? esc_attr($val['description']) : NULL; //
            $text = isset($val['text']) ? sanitize_textarea_field($val['text']) : $description; //
            $height = (isset($val['height']) and $val['height'] > 0) ? (int)($val['height']) : 0; //
            $placeholder = isset($val['placeholder']) ? esc_attr($val['placeholder']) : NULL; 
            $limit = isset($val['limit']) ? (int)$val['limit'] : NULL; 
            $class = !empty($val['class']) ? esc_attr($val['class']) : NULL;
            $types = !empty($val['allowed_file_types']) ? esc_attr($val['allowed_file_types']) : esc_attr(get_option("aol_allowed_file_types", ALLOWED_FILE_TYPES));
            $size = !empty($val['allowed_size']) ? (int)$val['allowed_size'] : (int)get_option('aol_upload_max_size');
            $selection = !empty($val['preselect']) && $val['preselect'] == 1 ? 'checked' : ''; 
            $icon = sanitize_text_field($this->aol_fields_icons($val['type']));
            $name = empty($template) ? $key : $template."[$key]";

            $required = isset( $val['required'] ) ? (int)$val['required'] : 0;
            $checked    = !empty( $val['required'] ) && $val['required'] == 1 ? 'checked' : '';
            
            $notify    = !empty( $val['notify'] ) && $val['notify'] == 1 ? 'checked' : '';
            
            echo '<div style="display:none;" id='.$key.'><div class="aol_form" data-id="'.$key.'">';
            echo '<div class="form-group"><label>'.$icon.'</label><span></span></div>';
<<<<<<< Updated upstream
            //echo '<div><label>'.esc_html__('Type', 'ApplyOnline').'</label><select disabled class="adapp_field_type" name="'.$key.'[type]">'.$fields.'</select></div>';
            echo '<div class="form-group"><label>*'.esc_html__('Unique ID', 'ApplyOnline').'</label><input type="text" disabled value="'.str_replace('_aol_app_', '', $key).'" /></div>';
            echo '<div class="form-group"><label for="'.$name.'-label">*'.esc_html__('Label', 'ApplyOnline').'</label><input id="'.$name.'-label" class="adapp_label" type="text" name="'.$name.'[label]" value="'.$label.'" /></div>';

            if($val['type'] == 'paragraph'){
                echo '<div class="form-group form-group-paragraph"><label>'.esc_html__('Text', 'ApplyOnline').'</label><textarea class="aol-form-field" name="'.$name.'[text]" >'.$text.'</textarea><p class="description">'.esc_html__('To generate a link, use shortcode [link href="https://google.com" title="My Link Title"]').'</p></div>';
                echo '<div class="form-group"><label for="'.$name.'-height">'.esc_html__('Fixed Height', 'ApplyOnline').'</label><input id="'.$name.'-height" class="aol-form-field" type="number" name="'.$name.'[height]" value="'.$height.'" />px</div>';
            } else{
                echo '<div class="form-group"><label for="'.$name.'-desc">'.esc_html__('Help Text', 'ApplyOnline').'</label><input id="'.$name.'-desc" type="text" name="'.$name.'[description]" value="'.$description.'" /></div>';
=======
            //echo '<div><label>'.esc_html__('Type', 'apply-online').'</label><select disabled class="adapp_field_type" name="'.$key.'[type]">'.$fields.'</select></div>';
            echo '<div class="form-group"><label>*'.esc_html__('Unique ID', 'apply-online').'</label><input type="text" disabled value="'.str_replace('_aol_app_', '', $key).'" /></div>';
            echo '<div class="form-group"><label for="'.$name.'-label">*'.esc_html__('Label', 'apply-online').'</label><input id="'.$name.'-label" class="adapp_label" type="text" name="'.$name.'[label]" value="'.$label.'" /></div>';

            if($val['type'] == 'paragraph'){
                echo '<div class="form-group form-group-paragraph"><label>'.esc_html__('Text', 'apply-online').'</label><textarea class="aol-form-field" name="'.$name.'[text]" >'.$text.'</textarea><p class="description">'.esc_html__('To generate a link, use shortcode [link href="https://google.com" title="My Link Title"]').'</p></div>';
                echo '<div class="form-group"><label for="'.$name.'-height">'.esc_html__('Fixed Height', 'apply-online').'</label><input id="'.$name.'-height" class="aol-form-field" type="number" name="'.$name.'[height]" value="'.$height.'" />px</div>';
            } else{
                echo '<div class="form-group"><label for="'.$name.'-desc">'.esc_html__('Help Text', 'apply-online').'</label><input id="'.$name.'-desc" type="text" name="'.$name.'[description]" value="'.$description.'" /></div>';
>>>>>>> Stashed changes
            }

            echo '<input type="hidden" name="'.$name.'[type]" value="'.$val['type'].'">';
            if(in_array($val['type'], array('text_area','text','number','email', 'url'))):
<<<<<<< Updated upstream
                echo '<div><label for="'.$name.'-placeholder">'.esc_html__('Placeholder', 'ApplyOnline').'</label><input id="'.$name.'-placeholder" type="text" name="'.$name.'[placeholder]" value="'.$placeholder.'" /></div>';
            endif;
            if(in_array($val['type'], array('checkbox','dropdown','radio'))):
                echo '<div class="aol_options_check"><label>'.esc_html__('Options', 'ApplyOnline').'</label><input type="text" name="'.$name.'[options]" value="'.esc_attr($val['options']).'" placeholder="'.esc_attr__('Option1, Option2, Option3', 'ApplyOnline').'" /></div>';
            endif;
            echo '<div><label for="'.$name.'-classes">'.esc_html__('Classes', 'ApplyOnline').'</label><input id="'.$name.'-classes" type="text" name="'.$name.'[class]" value="'.$class.'" /></div>';
            if( $val['type'] === 'radio' ){
                echo '<div><label for="'.$name.'-preselect">'.esc_html__('Preselect', 'ApplyOnline').'</label><input id="'.$name.'-preselect" type="checkbox" name="'.$name.'[preselect]" value="1" '.$selection.'><i class="description">'.esc_html__('Default first field selection.', 'ApplyOnline').'</i></div>';
            }

            if(in_array($val['type'], array('text_area','text'))):
                echo '<div><label for="'.$name.'-limit">'.esc_html__('Charchter Limit', 'ApplyOnline').'</label><input id="'.$name.'-limit" type="number" placeholder="No limit" name="'.$name.'[limit]" value="'.$limit.'" /></div>';
            endif;

            if( $val['type'] === 'email' ):
                echo '<div><label for="'.$name.'-notification">'.esc_html__('Notify This Email', 'ApplyOnline').'</label><input id="'.$name.'-notification" class="adpp_notification" type="checkbox" '.$notify.' name="'.$name.'[notify]" value="1" /></div>';
            endif;
            if(in_array($val['type'], array('checkbox','dropdown','radio','text_area','text','number','email','date','file'))):
                if($val['type'] == 'file'){
                    echo '<div><label for="'.$name.'-file-types">'.esc_html__('Allowed File Types', 'ApplyOnline').'</label><input id="'.$name.'-file-types" class="aol-form-field file_types_option" type="text" name="'.$name.'[allowed_file_types]" value="'.$types.'" /><p class="description">Comma separated values</p></div>';
                    echo '<div><label for="'.$name.'_file_max_size">'.esc_html__('*Max Size Limit', 'ApplyOnline').'</label><input id="'.$name.'_file_max_size" class="small-text aol-form-field file_max_size" type="number" name="'.$name.'[file_max_size]" value="'.$size.'" />MB</div>';
                }
                echo '<div><label for="'.$name.'-required">'.esc_html__('Required Field', 'ApplyOnline').'</label><input id="'.$name.'-required" class="required_option" type="checkbox" '.$checked.' name="'.$name.'[required]" value="1" /></div>';
            endif;
            echo '<p class="description">'.esc_html__('Fields with (*) are compulsory.', 'ApplyOnline').'</p>';
            //echo '<div class="button-primary button-required '.$req_class.'">'.esc_html__('Required', 'ApplyOnline').'</div> </div>';
            //echo '<div><label>'.esc_html__('Type', 'ApplyOnline').'</label><select disabled class="adapp_field_type" name="'.$key.'[type]">'.$fields.'</select></div>';
=======
                echo '<div><label for="'.$name.'-placeholder">'.esc_html__('Placeholder', 'apply-online').'</label><input id="'.$name.'-placeholder" type="text" name="'.$name.'[placeholder]" value="'.$placeholder.'" /></div>';
            endif;
            if(in_array($val['type'], array('checkbox','dropdown','radio'))):
                echo '<div class="aol_options_check"><label>'.esc_html__('Options', 'apply-online').'</label><input type="text" name="'.$name.'[options]" value="'.esc_attr($val['options']).'" placeholder="'.esc_attr__('Option1, Option2, Option3', 'apply-online').'" /></div>';
            endif;
            echo '<div><label for="'.$name.'-classes">'.esc_html__('Classes', 'apply-online').'</label><input id="'.$name.'-classes" type="text" name="'.$name.'[class]" value="'.$class.'" /></div>';
            if( $val['type'] === 'radio' ){
                echo '<div><label for="'.$name.'-preselect">'.esc_html__('Preselect', 'apply-online').'</label><input id="'.$name.'-preselect" type="checkbox" name="'.$name.'[preselect]" value="1" '.$selection.'><i class="description">'.esc_html__('Default first field selection.', 'apply-online').'</i></div>';
            }

            if(in_array($val['type'], array('text_area','text'))):
                echo '<div><label for="'.$name.'-limit">'.esc_html__('Charchter Limit', 'apply-online').'</label><input id="'.$name.'-limit" type="number" placeholder="No limit" name="'.$name.'[limit]" value="'.$limit.'" /></div>';
            endif;

            if( $val['type'] === 'email' ):
                echo '<div><label for="'.$name.'-notification">'.esc_html__('Notify This Email', 'apply-online').'</label><input id="'.$name.'-notification" class="adpp_notification" type="checkbox" '.$notify.' name="'.$name.'[notify]" value="1" /></div>';
            endif;
            if(in_array($val['type'], array('checkbox','dropdown','radio','text_area','text','number','email','date','file'))):
                if($val['type'] == 'file'){
                    echo '<div><label for="'.$name.'-file-types">'.esc_html__('Allowed File Types', 'apply-online').'</label><input id="'.$name.'-file-types" class="aol-form-field file_types_option" type="text" name="'.$name.'[allowed_file_types]" value="'.$types.'" /><p class="description">Comma separated values</p></div>';
                    echo '<div><label for="'.$name.'_file_max_size">'.esc_html__('*Max Size Limit', 'apply-online').'</label><input id="'.$name.'_file_max_size" class="small-text aol-form-field file_max_size" type="number" name="'.$name.'[file_max_size]" value="'.$size.'" />MB</div>';
                }
                echo '<div><label for="'.$name.'-required">'.esc_html__('Required Field', 'apply-online').'</label><input id="'.$name.'-required" class="required_option" type="checkbox" '.$checked.' name="'.$name.'[required]" value="1" /></div>';
            endif;
            echo '<p class="description">'.esc_html__('Fields with (*) are compulsory.', 'apply-online').'</p>';
            //echo '<div class="button-primary button-required '.$req_class.'">'.esc_html__('Required', 'apply-online').'</div> </div>';
            //echo '<div><label>'.esc_html__('Type', 'apply-online').'</label><select disabled class="adapp_field_type" name="'.$key.'[type]">'.$fields.'</select></div>';
>>>>>>> Stashed changes

            //if(!($val['type']=='text' or $val['type']=='email' or $val['type']=='date' or $val['type']=='text_area' or $val['type']=='file' )):
            echo do_action('aol_after_admin_form_fields', $name, $val);
            
            echo '<div><button type="button" class="button button-primary aol-save-form" data-temp=""> Update Field </button></div>';
            echo '</div></div>'; //End class="aol_form"
        }

        public function application_form_fields( $post ) {
            //global $adfields;
            // Add a nonce field so we can check for it later.
            wp_nonce_field( 'myplugin_adpost_meta_awesome_box', 'adpost_meta_box_nonce' );
            do_action('aol_before_form_builder', $post);
            /*
             * Use get_post_meta() to retrieve an existing value
             * from the database and use the value for the form.
             */
            ?>
            <div class="app_form_fields adpost_fields aol-wrapper aolFormBuilder">
                <table class="widefat striped">
                    <?php
                    do_action('aol_before_form_builder', $post);
                    //Fetch Feilds keys order
                    $fields = get_aol_ad_post_meta($post->ID);
                    if(empty($fields)):
                        //$fields = get_option('aol_default_fields', array());
                        //if(empty($fields)):
                            $fields = get_option('aol_form_templates', array());
                            $templates = TRUE;
                            $keys = array_keys($fields);
                            $options = null;
                            
                            //Sanitizing data before output.
                            foreach($keys as $key){ $options.= '<option value="'.sanitize_key($key).'">'. sanitize_text_field($fields[$key]['templateName']).'</option>'; }
                            ?>
                                <thead>
                                    <tr>
                                        <td colspan="2">
                                            <select id="aol_template_loader">
                                                <option class="aol_default_option"><?php esc_html_e('Import a form template','apply-online');?></option>
                                                <?php echo $options; ?>
                                            </select> &nbsp; &nbsp; 
                                            <!--
                                            <select id="aol_import_loader" class="aol-import-form">
                                                <option value="" class="aol_default_option"><?php esc_html_e('Import an existing ad','apply-online');?></option>
                                            </select> &nbsp; &nbsp; 
                                            -->
                                            <span class="template_loading_status"></span>
                                        </td>
                                        <td></td>
                                    </tr>
                                </thead>
                            <?php
                        //endif;
                    endif;
                        ?>
                        <tbody id="app_form_fields" class="app_form_fields">
                            <?php                       
                            if(!isset($templates)):
                                $this->aol_form_template($fields);
                            endif;
                            do_action('aol_after_form_builder', $post);
                        ?>
                        </tbody>
                </table>
            </div>  
            <?php $this->application_fields_generator($this->app_field_types); ?>
            <?php
        }
        
        function ismd5($md5 ='') {
          return strlen($md5) == 32 && ctype_xdigit($md5);
        }
        
}

  /**
  * This class contains all nuts and bolts of plugin settings.
  * 
  * 
  * @since      1.3
  * @package    Applyonline_settings
  * @author     Farhan Noor
  **/
class Applyonline_Settings extends Applyonline_Form_Builder{
    
    private $version;

    public function __construct($version) {
        
        //parent::__construct(); //Acitvating Parent's constructor
        
        $this->version = $version;
        
        //Registering Submenus.
        add_action('admin_menu', array($this, 'sub_menus'));
        
        //Registering Settings.
        add_action( 'admin_init', array($this, 'registers_settings') );
        
        add_filter( 'plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2 );
        
        //Manageing AOL role capabilites.
        add_filter( "option_page_capability_aol_settings_group", 'aol_manager_capability' );
        add_filter( "option_page_capability_aol_ad_template", 'aol_manager_capability' );
        add_filter( "option_page_capability_aol_ads", 'aol_manager_capability' );
        add_filter( "option_page_capability_aol_applications", 'aol_manager_capability' );
        
    }

    public function plugin_row_meta($links, $file){
        if ( strpos( $file, 'apply-online.php' ) !== false ){
<<<<<<< Updated upstream
            $links['settings'] = '<a href="'.  admin_url().'?page=aol-settings">'.esc_html__('Settings', 'ApplyOnline').'</a>';
=======
            $links['settings'] = '<a href="'.  admin_url().'?page=aol-settings">'.esc_html__('Settings', 'apply-online').'</a>';
>>>>>>> Stashed changes
	}
	
	return $links;
    }

    public function sub_menus(){
<<<<<<< Updated upstream
        add_menu_page( esc_html__('Settings', 'ApplyOnline'), _x('Apply Online', 'Admin Menu', 'ApplyOnline'), 'edit_applications', 'aol-settings', array($this, 'settings_page_callback'), 'dashicons-admin-site',31 );
        add_submenu_page('aol-settings', esc_html__('Settings', 'ApplyOnline'), esc_html__('Settings', 'ApplyOnline'), 'delete_others_ads', 'aol-settings');
        $filters = aol_ad_filters();
        foreach($filters as $key => $val){
            add_submenu_page( 'aol-settings', '', sprintf(esc_html__('%s Filter', 'ApplyOnline'), $val['plural']), 'delete_others_ads', "edit-tags.php?taxonomy=aol_ad_".sanitize_key($key)."&post_type=aol_ad", null );
=======
        add_menu_page( esc_html__('Settings', 'apply-online'), _x('ApplyOnline', 'Admin Menu', 'apply-online'), 'edit_applications', 'aol-settings', array($this, 'settings_page_callback'), 'dashicons-admin-site',31 );
        add_submenu_page('aol-settings', esc_html__('Settings', 'apply-online'), esc_html__('Settings', 'apply-online'), 'delete_others_ads', 'aol-settings');
        $filters = aol_ad_filters();
        foreach($filters as $key => $val){
            add_submenu_page( 'aol-settings', '', sprintf(esc_html__('%s Filter', 'apply-online'), $val['plural']), 'delete_others_ads', "edit-tags.php?taxonomy=aol_ad_".sanitize_key($key)."&post_type=aol_ad", null );
>>>>>>> Stashed changes
        }
    }

    function save_settings(){
        if(!current_user_can('edit_applications')) return;
        if ( !empty( $_POST['aol_default_app_fields'] ) && check_admin_referer( 'aol_awesome_pretty_nonce','aol_default_app_fields' ) ) {
            $args = array(
                'label' => FILTER_SANITIZE_STRING, 
                'required' => 1, 
                'type' => FILTER_SANITIZE_STRING, 
                'description' => FILTER_SANITIZE_STRING,
                'placeholder' => FILTER_SANITIZE_STRING,
                'class' => FILTER_SANITIZE_STRING,
                'limit' => FILTER_SANITIZE_NUMBER_INT,
                'preselect' => FILTER_SANITIZE_NUMBER_INT,
                'options' => FILTER_SANITIZE_STRING,
                'filter' => FILTER_SANITIZE_STRING,
                );
            $settings = array();
            foreach($_POST as $tempid => $template):
                //Check if all top level template keys starts with 'template' keyword.
                if(isset($_POST[$tempid])){
                    if($tempid == 'new'){
                       if( !empty($_POST[$tempid]['templateName']) ) $settings['template'.time()] = $template;
                        unset($_POST[$tempid]);
                    }
                    
                    elseif( substr($tempid, 0, 8) == 'template' ){ 
                        foreach($template as $key => $val){
                            $settings[sanitize_key($tempid)][$key] = is_array($val) ?  filter_var_array($val, $args) : sanitize_text_field($val);
                        }
                    }
                    //elseif( substr($tempid, 0, 8) != 'template' ) unset ($_POST[$tempid]); 16/08/2019
                    
                    //if(is_array($template) AND (key($template) != 'templateName' OR substr(key($template), 0, 4) != '_aol')) unset($_POST[$tempid][key($template)]);
                }
                
                /*
                if(substr($tempid, 0, 8) != 'template'){
                        unset($_POST[$tempid]);
                        continue;
                }
                 * 
                 */
                //Remove unnecessary fields
                //foreach($template as $key => $val){
                    //If not an aol meta key, unset it & continue to next iteration.
                    
                    //Replacing meta key with sanitized one.
                    //unset($_POST[$tempid][$key]);
                    //$_POST[$tempid][sanitize_key($key)] = $val;
                //}
            //Save aol default fields in DB.
            endforeach;
            update_option('aol_form_templates', $settings, FALSE);
            do_action('aol_save_settings');
        }
    }
    
    function settings_api(){
        $tabs = array(
                'general' => array(
                    'id'        => 'general',
<<<<<<< Updated upstream
                    'name'      => esc_html__( 'General' ,'ApplyOnline' ),
                    'desc'      => esc_html__( 'Global settings for the plugin. Some options can be overwritten from the ad editor screen.', 'ApplyOnline' ),
=======
                    'name'      => esc_html__( 'General' ,'apply-online' ),
                    'desc'      => esc_html__( 'Global settings for the plugin. Some options can be overwritten from the ad editor screen.', 'apply-online' ),
>>>>>>> Stashed changes
                    'href'      => null,
                    'classes'   => ' active',
                    'callback'  => array($this, 'tab_general')
                ),/*
                'ui' => array(
                    'id'        => 'ui',
<<<<<<< Updated upstream
                    'name'      => esc_html__('User Interface' ,'ApplyOnline'),
                    'desc'      => esc_html__( 'Front-end User Iterface Manager', 'ApplyOnline' ),
=======
                    'name'      => esc_html__('User Interface' ,'apply-online'),
                    'desc'      => esc_html__( 'Front-end User Iterface Manager', 'apply-online' ),
>>>>>>> Stashed changes
                    'href'      => null,
                ),
                 * 
                 */
                'template' => array(
                    'id'        => 'template',
<<<<<<< Updated upstream
                    'name'      => esc_html__('Template' ,'ApplyOnline'),
                    'desc'      => esc_html__( 'Application form templates for new ads.', 'ApplyOnline' ),
=======
                    'name'      => esc_html__('Template' ,'apply-online'),
                    'desc'      => esc_html__( 'Application form templates for new ads.', 'apply-online' ),
>>>>>>> Stashed changes
                    'href'      => null,
                    'callback' => array($this, 'tab_template')
                ),
                'applications' => array(
                    'id'        => 'applications',
<<<<<<< Updated upstream
                    'name'      => esc_html__('Applications' ,'ApplyOnline'),
                    'desc'      => esc_html__( 'This section is intended for received applications.', 'ApplyOnline' ),
=======
                    'name'      => esc_html__('Applications' ,'apply-online'),
                    'desc'      => esc_html__( 'This section is intended for received applications.', 'apply-online' ),
>>>>>>> Stashed changes
                    'href'      => null,
                    'callback'  => array($this, 'tab_applications')
                ),
                'filters' => array(
                    'id'        => 'filters',
<<<<<<< Updated upstream
                    'name'      => esc_html__('Ad Filters' ,'ApplyOnline'),
                    'desc'      => esc_html__( 'Display Filters in [aol] shortcode outupt.', 'ApplyOnline' ),
=======
                    'name'      => esc_html__('Ad Filters' ,'apply-online'),
                    'desc'      => esc_html__( 'Display Filters in [aol] shortcode outupt.', 'apply-online' ),
>>>>>>> Stashed changes
                    'href'      => null,
                    'callback'  => array($this, 'tab_filters')
                ),
                'types' => array(
                    'id'        => 'types',
<<<<<<< Updated upstream
                    'name'      => esc_html__('Ad Types' ,'ApplyOnline'),
                    'desc'      => esc_html__( 'Define different types of ads e.g. Careers, Classes, Memberships. These types will appear under All Ads section in WordPress admin panel.', 'ApplyOnline' ),
=======
                    'name'      => esc_html__('Ad Types' ,'apply-online'),
                    'desc'      => esc_html__( 'Define different types of ads e.g. Careers, Classes, Memberships. These types will appear under All Ads section in WordPress admin panel.', 'apply-online' ),
>>>>>>> Stashed changes
                    'href'      => null,
                    'callback'  => array($this, 'tab_types')
                ),
        );
        $tabs = apply_filters('aol_settings_tabs', $tabs);
        //Show these tabs at the end.
        $tabs['faqs'] = array(
                    'id'        => 'faqs',
<<<<<<< Updated upstream
                    'name'      => esc_html__('FAQ' ,'ApplyOnline'),
                    'desc'      => esc_html__('Frequently Asked Questions.' ,'ApplyOnline'),
=======
                    'name'      => esc_html__('FAQ' ,'apply-online'),
                    'desc'      => esc_html__('Frequently Asked Questions.' ,'apply-online'),
>>>>>>> Stashed changes
                    'href'      => null,
                    'callback'  => array($this, 'tab_faqs')
                );
        $tabs['extend'] = array(
                    'id'        => 'extend',
<<<<<<< Updated upstream
                    'name'      => esc_html__('Extend' ,'ApplyOnline'),
                    'desc'      => esc_html__('Extend Plugin' ,'ApplyOnline'),
=======
                    'name'      => esc_html__('Extend' ,'apply-online'),
                    'desc'      => esc_html__('Extend Plugin' ,'apply-online'),
>>>>>>> Stashed changes
                    'href'      => 'https://wpreloaded.com/shop/',
                    'capability' => 'manage_options',
                    'callback'  => array($this, 'tab_extend')
                );
        $tabs = apply_filters('aol_settings_all_tabs', $tabs);
        return $tabs;
    }
    
    public function settings_page_callback(){
        $this->save_settings();
        //$tabs = json_decode(json_encode($this->settings_api()), FALSE);
        $tabs = $this->settings_api();
        ob_start();
        ?>
            <div class="wrap aol-settings">
                <h2>
<<<<<<< Updated upstream
                    <?php _ex('ApplyOnline', 'admin', 'ApplyOnline'); ?> 
                    <small class="wp-caption alignright"><i> <?php printf(esc_html__('version %s', 'ApplyOnline'), $this->version); ?></i></small>
=======
                    <?php _ex('ApplyOnline', 'admin', 'apply-online'); ?> 
                    <small class="wp-caption alignright"><i> <?php printf(esc_html__('version %s', 'apply-online'), $this->version); ?></i></small>
>>>>>>> Stashed changes
                </h2>
                <span class="alignright" style="display: none">
                    <a target="_blank" title="Love" class="aol-heart" href="https://wordpress.org/plugins/apply-online/#reviews"><span class="dashicons dashicons-heart"></span></a> &nbsp;
                    <a target="_blank" title="Support" class="aol-help" href="https://wordpress.org/support/plugin/apply-online/"><span class="dashicons dashicons-format-chat"></span></a> &nbsp;
                    <a target="_blank" title="Stats" class="aol-stats" href="https://wordpress.org/plugins/apply-online/advanced/"><span class="dashicons dashicons-chart-pie"></span></a> &nbsp;
                    <a target="_blank" title="Shop" class="aol-shop" href="https://wpreloaded.com/shop/"><span class="dashicons dashicons-cart"></span></a> &nbsp;
                </span>
                <h2 class="nav-tab-wrapper aol-tabs-wrapper aol-primary">
                    <?php 
                        foreach($tabs as $tab){
                            if( isset($tab['capability']) AND !current_user_can($tab['capability']) ) continue;
                            $href = empty($tab['href']) ? null : 'href="'.$tab['href'].'" target="_blank"';
                            $classes = isset($tab['classes']) ? $tab['classes'] : null;
                            echo '<a class="nav-tab aol-tab '. esc_attr($classes).'" data-id="'.esc_attr($tab['id']).'" '.esc_url($href).'>'.sanitize_text_field($tab['name']).'</a>';
                        }
                    ?>
                </h2>
                <?php 
                    foreach($tabs as $tab){
                        //$callback = ( isset($tab->output) and !isset($tab->callback) ) ? $tab->output : $tab->callback;
                        echo '<div class="aol-tab-data wrap" id="'.esc_attr($tab['id']).'">';
                            if(isset($tab['name'])) echo '<h3>'.sanitize_text_field($tab['name']).'</h3>';
                            if(isset($tab['desc'])) echo '<p>'.sanitize_text_field($tab['desc']).'</p>';

                            //Output is already sanitized in the concerned method.
                            $callback = $tab['callback'];
                            //echo isset($tab['output']) ? $tab['output'] : $this[$callback()];
                            if( is_array($callback) ){
                                $obj = $callback[0];
                                $method = $callback[1];
                                echo $obj->$method();
                            } else {
                                echo $callback();
                            }
                        echo '</div>';
                    }
                ?>
            </div>
        <?php
        return ob_get_flush();
    }

    public function registers_settings(){
        register_setting( 'aol_settings_group', 'aol_recipients_emails', array( 'sanitize_callback' => 'sanitize_textarea_field') );
        register_setting( 'aol_settings_group', 'aol_application_success_alert', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_is_progress_bar', array( 'sanitize_callback' => 'boolval') );
        register_setting( 'aol_settings_group', 'aol_progress_bar_color', array( 'sanitize_callback' => 'aol_sanitize_array') );

        register_setting( 'aol_settings_group', 'aol_shortcode_readmore', array( 'sanitize_callback' => 'esc_attr') );
        register_setting( 'aol_settings_group', 'aol_application_submit_button', array( 'sanitize_callback' => 'esc_attr') );
        register_setting( 'aol_settings_group', 'aol_required_fields_notice', array( 'sanitize_callback' => 'sanitize_text_field'));
        register_setting( 'aol_settings_group', 'aol_thankyou_page', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_upload_path', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_form_heading', array( 'sanitize_callback' => 'esc_attr') );
        register_setting( 'aol_settings_group', 'aol_features_title', array( 'sanitize_callback' => 'esc_attr') );
        register_setting( 'aol_settings_group', 'aol_slug', 'sanitize_title', array( 'sanitize_callback' => 'sanitize_key') ); 
        register_setting( 'aol_settings_group', 'aol_upload_max_size', array('default' => 1, 'sanitize_callback' => 'intval') );
        register_setting( 'aol_settings_group', 'aol_days_for_older_ads_alert', array('default' => 0, 'sanitize_callback' => 'intval') );
        //register_setting( 'aol_settings_group', 'aol_upload_folder', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_allowed_file_types', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_application_close_message', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_ad_author_notification', array( 'sanitize_callback' => 'boolval') );
        //register_setting( 'aol_settings_group', 'aol_nonce_is_active', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_success_mail_message', array( 'sanitize_callback' => 'sanitize_textarea_field') );
        register_setting( 'aol_settings_group', 'aol_success_mail_subject', array( 'sanitize_callback' => 'esc_attr') );
        register_setting( 'aol_settings_group', 'aol_admin_mail_subject', array( 'sanitize_callback' => 'esc_attr') );
        register_setting( 'aol_settings_group', 'aol_not_found_alert', array( 'sanitize_callback' => 'esc_attr') );
        
        register_setting( 'aol_filters', 'aol_ad_filters', array( 'sanitize_callback' => 'aol_sanitize_array') );
        
        //Registering settings for aol_settings API option.
        $settings = get_aol_settings();
        foreach($settings as $setting){
            //$key = get_option($setting['key']);
            register_setting( 'aol_settings_group', $setting['key'], array( 'sanitize_callback' => $setting['sanitize_callback']) );
        }
        
        register_setting( 'aol_ad_template', 'aol_default_fields');//Depreciated
        register_setting( 'aol_ad_template', 'aol_form_templates');
        register_setting( 'aol_ads', 'aol_ad_types', array('sanitize_callback' => 'aol_sanitize_array') );
        register_setting( 'aol_applications', 'aol_app_statuses', array('default' => array()));
        register_setting( 'aol_applications', 'aol_custom_statuses', array('default' => array(), 'sanitize_callback' => 'aol_sanitize_array'));
        register_setting( 'aol_ui_settings_group', 'aol_submit_button_classes', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting( 'aol_ui_settings_group', 'aol_readmore_button_classes', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting( 'aol_ui_settings_group', 'aol_multistep_button_classes', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting( 'aol_ui_settings_group', 'aol_submit_button_classes', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting( 'aol_ui_settings_group', 'aol_submit_button_classes', array('sanitize_callback' => 'sanitize_text_field'));

        //On update of aol_slug field, update permalink too.
        add_action('update_option_aol_slug', array($this, 'refresh_permalink'));
        add_action('update_option_aol_ad_types', array($this, 'refresh_types_permalink'), 10, 3);
    }
    
    public function refresh_permalink(){
        //Re register post type for proper Flush Rules.
        $slug = get_option_fixed('aol_slug', 'ads');
        /*Register Main Post Type*/
        register_post_type('aol_ad', array('has_archive' => true, 'rewrite' => array('slug'=>  $slug)));
        flush_rewrite_rules();
    }
    
    function register_ad_types_for_flushing($cpt, $plural){
        $result = register_post_type('aol_'.$cpt, array(
            'has_archive' => true, 
            'public' => true,
            'rewrite' => array('slug' => $plural),
            ));
    }
    
    function refresh_types_permalink($old, $new, $option){
        wp_cache_delete ( 'alloptions', 'options' );
        foreach($new as $cpt => $val){
            $this->register_ad_types_for_flushing($cpt, $val['plural']);
        }
        flush_rewrite_rules();
    }
    
    private function wp_pages(){
        $pages = get_pages();
        $pages_arr = array();
        foreach ( $pages as $page ) {
            $pages_arr[$page->ID] = $page->post_title;
        }
        return $pages_arr;
    }
    
    private function tab_general(){
        ?>
            <form action="options.php" method="post" name="">
                <table class="form-table">
                <?php
                    settings_fields( 'aol_settings_group' ); 
                    do_settings_sections( 'aol_settings_group' );
                    $uload_dir = wp_upload_dir();
                    $aol_upload_path = wp_normalize_path($uload_dir['basedir']);
                    $progress_bar = (array)get_option('aol_progress_bar_color', array('foreground' => '#222222', 'background' => '#dddddd', 'counter' => '#888888'));
                    $failure_alert = "Something went wrong please follow these instruciton:&#013;Try submitting form again.&#013;Refresh page and try submitting form again.&#013;If problem persists, please report this issue through Contact Us page.";

                    $submission_alert = 'Form has been submitted successfully with application id [id]. If required, we will get back to you shortly!';

                    $message="Hi there,\n\n"
                        ."Thanks for showing interest in the ad: [title]. Your application with id [id] has been received. We will review your application and contact you if required.\n\n"
                        .sprintf(__('Team %s'), get_bloginfo('name'))."\n"
                        .site_url()."\n"
                        ."Please do not reply to this system generated message.";
<<<<<<< Updated upstream
                    $depricated = sprintf(__('This section is being depricated and will be removed on %s. Please use similar section in each ad.', 'ApplyOnline'), date('M d, Y'));
                ?>
                    <!--
                    <tr>
                        <th><label for="aol_multistep"><?php esc_html_e('Multistep application forms', 'ApplyOnline'); ?></label></th>
=======
                    $depricated = sprintf(__('This section is being depricated and will be removed on %s. Please use similar section in each ad.', 'apply-online'), date('M d, Y'));
                ?>
                    <!--
                    <tr>
                        <th><label for="aol_multistep"><?php esc_html_e('Multistep application forms', 'apply-online'); ?></label></th>
>>>>>>> Stashed changes
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="aol_multistep" <?php echo sanitize_key(get_option('aol_multistep')) ? 'checked="checked"':Null; ?> >
                                <span class="slider"></span>
                             </label>
                            <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
<<<<<<< Updated upstream
                        <th><label for="aol_nonce"><?php esc_html_e('Security Nonce', 'ApplyOnline'); ?> </label></th>
=======
                        <th><label for="aol_nonce"><?php esc_html_e('Security Nonce', 'apply-online'); ?> </label></th>
>>>>>>> Stashed changes
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="aol_nonce_is_active" <?php echo sanitize_key(get_option('aol_nonce_is_active')) ? 'checked="checked"':Null; ?> >
                                <span class="slider"></span>
                             </label>
<<<<<<< Updated upstream
                            <p class="description"><?php esc_html_e('If you have firewall installed (e.g. WordFence) and get Session Expired error on form submissions then disabling nonce might be helpful.', 'ApplyOnline'); ?></p>
=======
                            <p class="description"><?php esc_html_e('If you have firewall installed (e.g. WordFence) and get Session Expired error on form submissions then disabling nonce might be helpful.', 'apply-online'); ?></p>
>>>>>>> Stashed changes
                        </td>
                    </tr>
                    -->
                    <tr>
<<<<<<< Updated upstream
                        <th><label for="aol_recipients_emails"><?php esc_html_e('List of e-mails to get application alerts', 'ApplyOnline'); ?></label></th>
                        <td>
                            <textarea id="aol_recipients_emails" class="small-text code" name="aol_recipients_emails" cols="50" rows="5"><?php echo sanitize_textarea_field(get_option_fixed('aol_recipients_emails') ); ?></textarea>
                            <p class="description"><?php esc_html_e('One email address in one line.', 'ApplyOnline'); ?></p>
                            <p class="description"><?php esc_html_e('Mail send limit imposed by your hosting/server provider may effect mail delivery.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_ad_author_notification"><?php esc_html_e('Email notification for ad authors', 'ApplyOnline'); ?></label></th>
=======
                        <th><label for="aol_recipients_emails"><?php esc_html_e('List of e-mails to get application alerts', 'apply-online'); ?></label></th>
                        <td>
                            <textarea id="aol_recipients_emails" class="small-text code" name="aol_recipients_emails" cols="50" rows="5"><?php echo sanitize_textarea_field(get_option_fixed('aol_recipients_emails') ); ?></textarea>
                            <p class="description"><?php esc_html_e('One email address in one line.', 'apply-online'); ?></p>
                            <p class="description"><?php esc_html_e('Mail send limit imposed by your hosting/server provider may effect mail delivery.', 'apply-online'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_ad_author_notification"><?php esc_html_e('Email notifications for ad authors', 'apply-online'); ?></label></th>
>>>>>>> Stashed changes
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="aol_ad_author_notification" <?php echo get_option('aol_ad_author_notification') ? 'checked="checked"':Null; ?> >
                                <span class="slider"></span>
                             </label>
                            <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
<<<<<<< Updated upstream
                        <th><label for="aol_progress_bar"><?php esc_html_e('Application Form Progress Bar', 'ApplyOnline'); ?></label></th>
=======
                        <th><label for="aol_progress_bar"><?php esc_html_e('Application Form Progress Bar', 'apply-online'); ?></label></th>
>>>>>>> Stashed changes
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="aol_is_progress_bar" <?php echo get_option('aol_is_progress_bar') ? 'checked="checked"':Null; ?> >
                                <span class="slider"></span>
                             </label>
<<<<<<< Updated upstream
                            <p class="description"><?php esc_html_e('Applies to required form fields only.', 'ApplyOnline'); ?> </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_progress_bar_color"><?php esc_html_e('Progress Bar Color Scheme', 'ApplyOnline'); ?></label></th>
=======
                            <p class="description"><?php esc_html_e('Applies to required form fields only.', 'apply-online'); ?> </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_progress_bar_color"><?php esc_html_e('Progress Bar Color Scheme', 'apply-online'); ?></label></th>
>>>>>>> Stashed changes
                        <td>
                            <label> Foreground <input type="color" name="aol_progress_bar_color[foreground]"  value="<?php echo esc_attr($progress_bar['foreground']); ?>" /></label> &nbsp; 
                            <label> Background <input type="color" name="aol_progress_bar_color[background]"  value="<?php echo esc_attr($progress_bar['background']); ?>" /></label> &nbsp; 
                            <label> Text <input type="color" name="aol_progress_bar_color[counter]"  value="<?php echo esc_attr($progress_bar['counter']); ?>" /></label>
                        </td>
                    </tr>
                    <tr>
<<<<<<< Updated upstream
                        <th><label for="aol_application_success_alert"><?php esc_html_e('Application submission note', 'ApplyOnline'); ?></label></th>
                        <td>
                            <textarea class="small-text code" name="aol_application_success_alert" cols="50" rows="4" id="aol_application_success_alert"><?php echo sanitize_text_field( get_option_fixed('aol_application_success_alert', $submission_alert ) ); ?></textarea>
                            <p class="description"><?php esc_html_e('Use [id] for dynamic application ID.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_admin_mail_subject"><?php esc_html_e('Email notification subject for admin', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input class="regular-text" type="text" name="aol_admin_mail_subject" cols="50" rows="3" id="aol_admin_mail_subject" value="<?php echo esc_attr( get_option_fixed('aol_admin_mail_subject', 'New application [id] for [title]' ) ); ?>" />
                            <p class="description"> <?php esc_html_e('Use [id] and [title] to write ad ID and title in the email subject.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_success_mail_subject"><?php esc_html_e('Email notification subject for applicant', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input class="regular-text" type="text" name="aol_success_mail_subject" cols="50" rows="3" id="aol_success_mail_subject" value="<?php echo esc_attr( get_option_fixed('aol_success_mail_subject', 'Your application for [title]' ) ); ?>" />
                            <p class="description"> <?php esc_html_e('Use [id] and [title] to write ad ID and title in the email subject.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_success_mail_message"><?php esc_html_e('Email notification message', 'ApplyOnline'); ?></label></th>
                        <td>
                            <textarea class="small-text code" name="aol_success_mail_message" cols="50" rows="10" id="aol_success_mail_message"><?php echo sanitize_textarea_field( get_option_fixed('aol_success_mail_message', $message) ); ?></textarea>
                            <p class="description"> <?php esc_html_e('Ues [title] & [id] to add ad title & its ID number in the mail.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_required_fields_notice"><?php esc_html_e('Required form fields notice', 'ApplyOnline'); ?></label></th>
                        <td>
                            <textarea class="small-text code" name="aol_required_fields_notice" cols="50" rows="3" id="aol_required_fields_notice"><?php echo sanitize_text_field( get_option_fixed('aol_required_fields_notice', 'Fields with (*)  are compulsory.' ) ); ?></textarea>
                            <br />
                            <button class="button" id="aol_required_fields_button"><?php esc_html_e('Default Notice', 'ApplyOnline'); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="app_closed_alert"><?php esc_html_e('Closed Application alert', 'ApplyOnline'); ?></label></th>
=======
                        <th><label for="aol_application_success_alert"><?php esc_html_e('Application submission note', 'apply-online'); ?></label></th>
                        <td>
                            <textarea class="small-text code" name="aol_application_success_alert" cols="50" rows="4" id="aol_application_success_alert"><?php echo sanitize_text_field( get_option_fixed('aol_application_success_alert', $submission_alert ) ); ?></textarea>
                            <p class="description"><?php esc_html_e('Use [id] for dynamic application ID.', 'apply-online'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_admin_mail_subject"><?php esc_html_e('Email notification subject for admin', 'apply-online'); ?></label></th>
                        <td>
                            <input class="regular-text" type="text" name="aol_admin_mail_subject" cols="50" rows="3" id="aol_admin_mail_subject" value="<?php echo esc_attr( get_option_fixed('aol_admin_mail_subject', 'New application [id] for [title]' ) ); ?>" />
                            <p class="description"> <?php esc_html_e('Use [id] and [title] to write ad ID and title in the email subject.', 'apply-online'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_success_mail_subject"><?php esc_html_e('Email notification subject for applicant', 'apply-online'); ?></label></th>
                        <td>
                            <input class="regular-text" type="text" name="aol_success_mail_subject" cols="50" rows="3" id="aol_success_mail_subject" value="<?php echo esc_attr( get_option_fixed('aol_success_mail_subject', 'Your application for [title]' ) ); ?>" />
                            <p class="description"> <?php esc_html_e('Use [id] and [title] to write ad ID and title in the email subject.', 'apply-online'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_success_mail_message"><?php esc_html_e('Email notification message', 'apply-online'); ?></label></th>
                        <td>
                            <textarea class="small-text code" name="aol_success_mail_message" cols="50" rows="10" id="aol_success_mail_message"><?php echo sanitize_textarea_field( get_option_fixed('aol_success_mail_message', $message) ); ?></textarea>
                            <p class="description"> <?php esc_html_e('Ues [title] & [id] to add ad title & its ID number in the mail.', 'apply-online'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_required_fields_notice"><?php esc_html_e('Required form fields notice', 'apply-online'); ?></label></th>
                        <td>
                            <textarea class="small-text code" name="aol_required_fields_notice" cols="50" rows="3" id="aol_required_fields_notice"><?php echo sanitize_text_field( get_option_fixed('aol_required_fields_notice', 'Fields with (*)  are compulsory.' ) ); ?></textarea>
                            <br />
                            <button class="button" id="aol_required_fields_button"><?php esc_html_e('Default Notice', 'apply-online'); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="app_closed_alert"><?php esc_html_e('Closed Application alert', 'apply-online'); ?></label></th>
>>>>>>> Stashed changes
                        <td>
                            <textarea id="app_closed_alert" class="small-text code" name="aol_application_close_message" cols="50" rows="3"><?php echo sanitize_text_field( get_option_fixed('aol_application_close_message', __('We are no longer accepting applications for this ad.', 'apply-online')) ); ?></textarea>
                            <br />
<<<<<<< Updated upstream
                            <button id="app_closed_alert_button" class="button"><?php esc_html_e('Default Alert', 'ApplyOnline'); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_days_for_older_ads_alert"><?php esc_html_e('Number of days for older ads email alert.', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="number" id="aol_days_for_older_ads_alert" class="regular-text" name="aol_days_for_older_ads_alert" value="<?php echo (int)get_option_fixed('aol_days_for_older_ads_alert', 0); ?>">
                            <p class="description"><?php esc_html_e('Number of days after Email alert should be sent. No email alerts for zero.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="thanks-page"><?php esc_html_e('Thank you page', 'ApplyOnline'); ?></label></th>
                        <td>
                            <select id="thank-page" class="aol_select2_filter" style="width: 330px" name="aol_thankyou_page">
                                <option value=""><?php esc_html_e('Not selected', 'ApplyOnline'); ?></option> 
=======
                            <button id="app_closed_alert_button" class="button"><?php esc_html_e('Default Alert', 'apply-online'); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_days_for_older_ads_alert"><?php esc_html_e('Number of days for older ads email alert.', 'apply-online'); ?></label></th>
                        <td>
                            <input type="number" id="aol_days_for_older_ads_alert" class="regular-text" name="aol_days_for_older_ads_alert" value="<?php echo (int)get_option_fixed('aol_days_for_older_ads_alert', 0); ?>">
                            <p class="description"><?php esc_html_e('Number of days after Email alert should be sent. No email alerts for zero.', 'apply-online'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="thanks-page"><?php esc_html_e('Thank you page', 'apply-online'); ?></label></th>
                        <td>
                            <select id="thank-page" class="aol_select2_filter" style="width: 330px" name="aol_thankyou_page">
                                <option value=""><?php esc_html_e('Not selected', 'apply-online'); ?></option> 
>>>>>>> Stashed changes
                                <?php 
                                $selected = get_option('aol_thankyou_page');

                                 $pages = get_pages();
                                 foreach ( $pages as $page ) {
                                        $selected == $page->ID ? $selected = 'selected' : NULL;
                                     
                                        $option = '<option value="' . (int)$page->ID . '" '.$selected.'>';
                                        $option .= sanitize_text_field($page->post_title);
                                        $option .= '</option>';
                                        echo $option;
                                 }
                                ?>
                           </select>
                        </td>
                    </tr>
                    <tr>
<<<<<<< Updated upstream
                        <th><label for="aol_form_heading"><?php esc_html_e('Application form title', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_form_heading" class="regular-text" name="aol_form_heading" value="<?php echo esc_attr(get_option('aol_form_heading', 'Apply Online')); ?>">
                            <p class="description"><?php esc_html_e('Default: Apply Online', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_features_title"><?php esc_html_e('Ad features title', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_features_title" class="regular-text" name="aol_features_title" value="<?php echo esc_attr(get_option('aol_features_title', 'Salient Features')); ?>">
                            <p class="description"><?php esc_html_e('Default: Salient Features', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_application_submit_button"><?php esc_html_e('Application form Submit Button', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_application_submit_button" class="regular-text" name="aol_application_submit_button" value="<?php echo esc_attr(get_option_fixed('aol_application_submit_button', 'Submit')); ?>">
                            <p class="description"><?php esc_html_e('Default: Submit', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_not_found_alert"><?php esc_html_e('Not Found alert text', 'ApplyOnline'); ?></label></th>
=======
                        <th><label for="aol_form_heading"><?php esc_html_e('Application form title', 'apply-online'); ?></label></th>
                        <td>
                            <input type="text" id="aol_form_heading" class="regular-text" name="aol_form_heading" value="<?php echo esc_attr(get_option('aol_form_heading', 'Apply Online')); ?>">
                            <p class="description"><?php esc_html_e('Default: Apply Online', 'apply-online'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_features_title"><?php esc_html_e('Ad features title', 'apply-online'); ?></label></th>
                        <td>
                            <input type="text" id="aol_features_title" class="regular-text" name="aol_features_title" value="<?php echo esc_attr(get_option('aol_features_title', 'Salient Features')); ?>">
                            <p class="description"><?php esc_html_e('Default: Salient Features', 'apply-online'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_application_submit_button"><?php esc_html_e('Application form Submit Button', 'apply-online'); ?></label></th>
                        <td>
                            <input type="text" id="aol_application_submit_button" class="regular-text" name="aol_application_submit_button" value="<?php echo esc_attr(get_option_fixed('aol_application_submit_button', 'Submit')); ?>">
                            <p class="description"><?php esc_html_e('Default: Submit', 'apply-online'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_not_found_alert"><?php esc_html_e('Not Found alert text', 'apply-online'); ?></label></th>
>>>>>>> Stashed changes
                        <td>
                            <input type="text" id="aol_not_found_alert" class="regular-text" name="aol_not_found_alert" value="<?php echo esc_attr(get_option_fixed('aol_not_found_alert', 'Sorry, we could not find what you were looking for.')); ?>">
                        </td>
                    </tr>
                    <tr>
<<<<<<< Updated upstream
                        <th><label for="aol_shortcode_readmore"><?php esc_html_e('Read More button text', 'ApplyOnline'); ?></label></th>
=======
                        <th><label for="aol_shortcode_readmore"><?php esc_html_e('Read More button text', 'apply-online'); ?></label></th>
>>>>>>> Stashed changes
                        <td>
                            <input type="text" id="aol_shortcode_readmore" class="regular-text" name="aol_shortcode_readmore" value="<?php echo esc_attr(get_option_fixed('aol_shortcode_readmore')); ?>">
                        </td>
                    </tr>
                    <tr>
<<<<<<< Updated upstream
                        <th><label for="aol_date_format"><?php esc_html_e('Date format for date fields', 'ApplyOnline'); ?></label></th>
                        <td>
                            <p><?php echo sprintf(esc_html__('Update format on Wordpress %sGeneral Settings%s page', 'ApplyOnline'), '<a href="'.admin_url('options-general.php#timezone_string').'" target="_blank" />', '</a>'); ?> </p>
                        </td>
                    </tr>                    
                    <tr>
                        <th><label for="aol_slug"><?php esc_html_e('Default Ads slug', 'ApplyOnline'); ?></label></th>
=======
                        <th><label for="aol_date_format"><?php esc_html_e('Date format for date fields', 'apply-online'); ?></label></th>
                        <td>
                            <p><?php echo sprintf(esc_html__('Update format on Wordpress %sGeneral Settings%s page', 'apply-online'), '<a href="'.admin_url('options-general.php#timezone_string').'" target="_blank" />', '</a>'); ?> </p>
                        </td>
                    </tr>                    
                    <tr>
                        <th><label for="aol_slug"><?php esc_html_e('Default Ads slug', 'apply-online'); ?></label></th>
>>>>>>> Stashed changes
                        <td>
                            <input id="aol_slug" type="text" class="regular-text" name="aol_slug" placeholder="ads" value="<?php echo esc_attr(get_option_fixed('aol_slug', 'ads') ); ?>" />
                            <?php $permalink_option = get_option('permalink_structure'); if(empty($permalink_option)): ?>
                                <p><?php printf(esc_html__("This option doesn't work with Plain permalinks structure. Check %sPermalink Settings%s"), '<a href="'.admin_url('options-permalink.php').'">', '</a>'); ?></p>
                            <?php else: ?>
<<<<<<< Updated upstream
                                <p class="description"><?php printf(esc_html__('Current permalink is %s', 'ApplyOnline'), '<a href="'.get_post_type_archive_link('aol_ad').'" target="_blank">'.get_post_type_archive_link('aol_ad').'</a>') ?></p>
=======
                                <p class="description"><?php printf(esc_html__('Current permalink is %s', 'apply-online'), '<a href="'.get_post_type_archive_link('aol_ad').'" target="_blank">'.get_post_type_archive_link('aol_ad').'</a>') ?></p>
>>>>>>> Stashed changes
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
<<<<<<< Updated upstream
                        <th><label for="aol_upload_path"><?php esc_html_e('File upload path', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_upload_path" class="regular-text" placeholder="<?php echo $aol_upload_path; ?>" name="aol_upload_path" value="<?php echo esc_attr(get_option('aol_upload_path')); ?>"> <?php aol_empty_option_alert('aol_upload_path', $aol_upload_path); ?>
                            <p class="description"><?php esc_html_e("Set a private path, ideally before the root directory of your website; in most cases before the public_html directory. Leave it empty for default upload directory (discouraged).", 'ApplyOnline'); ?> </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_form_max_file_size"><?php esc_html_e('Max file attachment size', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input id="aol_form_max_upload_size" max="" type="number" name="aol_upload_max_size" placeholder="1" value="<?php echo (int)get_option('aol_upload_max_size', 1); ?>" />MBs
                            <p class="description"><?php printf(esc_html__('Max limit by server is %d MBs', 'ApplyOnline'), floor(wp_max_upload_size()/1000000)); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_allowed_file_types"><?php esc_html_e('Allowed file types', 'ApplyOnline'); ?></label></th>
                        <td>
                            <textarea id="aol_allowed_file_types" name="aol_allowed_file_types" placeholder="<?php echo esc_attr( get_option("aol_allowed_file_types", ALLOWED_FILE_TYPES) ); ?>" class="code" placeholder="<?php echo esc_attr( get_option("aol_allowed_file_types", ALLOWED_FILE_TYPES) ); ?>" cols="50" rows="2"><?php echo sanitize_text_field( get_option_fixed('aol_allowed_file_types', 'jpg,jpeg,png,doc,docx,pdf,rtf,odt,txt') ); ?></textarea>
                            <p class="description"><?php printf(esc_html__('Comma separated names of file extentions. Default: $s', 'ApplyOnline'), get_option("aol_allowed_file_types", ALLOWED_FILE_TYPES)); ?></p>
=======
                        <th><label for="aol_upload_path"><?php esc_html_e('File upload path', 'apply-online'); ?></label></th>
                        <td>
                            <input type="text" id="aol_upload_path" class="regular-text" placeholder="<?php echo $aol_upload_path; ?>" name="aol_upload_path" value="<?php echo esc_attr(get_option('aol_upload_path')); ?>"> <?php aol_empty_option_alert('aol_upload_path', $aol_upload_path); ?>
                            <p class="description"><?php esc_html_e("Set a private path, ideally before the root directory of your website; in most cases before the public_html directory. Leave it empty for default upload directory (discouraged).", 'apply-online'); ?> </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_form_max_file_size"><?php esc_html_e('Max file attachment size', 'apply-online'); ?></label></th>
                        <td>
                            <input id="aol_form_max_upload_size" max="" type="number" name="aol_upload_max_size" placeholder="1" value="<?php echo (int)get_option('aol_upload_max_size', 1); ?>" />MBs
                            <p class="description"><?php printf(esc_html__('Max limit by server is %d MBs', 'apply-online'), floor(wp_max_upload_size()/1000000)); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_allowed_file_types"><?php esc_html_e('Allowed file types', 'apply-online'); ?></label></th>
                        <td>
                            <textarea id="aol_allowed_file_types" name="aol_allowed_file_types" placeholder="<?php echo esc_attr( get_option("aol_allowed_file_types", ALLOWED_FILE_TYPES) ); ?>" class="code" placeholder="<?php echo esc_attr( get_option("aol_allowed_file_types", ALLOWED_FILE_TYPES) ); ?>" cols="50" rows="2"><?php echo sanitize_text_field( get_option_fixed('aol_allowed_file_types', 'jpg,jpeg,png,doc,docx,pdf,rtf,odt,txt') ); ?></textarea>
                            <p class="description"><?php printf(esc_html__('Comma separated names of file extentions. Default: $s', 'apply-online'), get_option("aol_allowed_file_types", ALLOWED_FILE_TYPES)); ?></p>
>>>>>>> Stashed changes
                        </td>
                    </tr>
                    <?php 
                    $settings = get_aol_settings();
                    foreach ($settings as $setting){
                        //setting default values as NULL.
                        //$setting = array_merge(array_fill_keys(array('type', 'key', 'secret', 'placeholder', 'value', 'label', 'helptext', 'icon', 'class'), NULL), $setting);
                        //$placeholder = ($setting['secret']==true AND !empty($setting['value'])) ? aol_crypt($setting['value'], 'd'):$setting['placeholder'];
                        //$value      = ( isset($setting['value']) AND !empty($setting['value']) ) ? $setting['value'] : get_option($setting['key']);
                        //$value = $setting['secret']==true ? NULL:$setting['value'];
                        //if( $setting['secret']==true ) $setting['type'] = 'password';
                    ?>
                        <tr>
                            <th><label for="<?php echo esc_attr( $setting['key'] ); ?>"><?php echo sanitize_text_field( $setting['label'] ); ?></label></th>
                            <td>
                                <?php 
                                switch($setting['type']):
                                    case 'textarea':
                                        ?>
                                <textarea class="code <?php echo esc_attr( $setting['class'] ); ?>" id="<?php echo esc_attr( $setting['key'] ); ?>" name="<?php echo esc_attr( $setting['key'] ); ?>" placeholder="<?php echo esc_attr( $setting['placeholder'] ); ?>" ><?php echo sanitize_text_field( $setting['value'] ); ?></textarea>
                                        <?php
                                            break;
                                    default:
                                        ?>
                                        <input class="regular-text <?php echo esc_attr( $setting['class'] ); ?>" type="<?php echo esc_attr( $setting['type'] ); ?>" id="<?php echo esc_attr( $setting['key'] ); ?>" name="<?php echo esc_attr( $setting['key'] ); ?>" placeholder="<?php echo esc_attr( $setting['placeholder'] ); ?>" value="<?php echo esc_attr( $setting['value'] ); ?>" />
                                <?php endswitch; ?>
                                <?php echo isset($setting['button']) ? '<a id="'.esc_attr( $setting['key'] ).'_button" href="'. esc_url( $setting['button']['link'] ).'" target="_blank" class="'.esc_attr( $setting['key'] ).'_button button">'.sanitize_text_field( $setting['button']['title'] ).'</a>': NULL ; ?>
                                <p class="description">
                                    <?php 
                                    if(isset($setting['icon'])) echo '<span class="dashicons dashicons-'. esc_attr( $setting['icon'] ).'"></span>';
                                    echo sanitize_text_field( $setting['helptext'] );
                                    ?>
                                </p>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <?php submit_button(); ?>
            </form>
            <?php 
    }
    
    private function tab_template(){
        ?>
            <div>
                <form id="templateForm" method="post">
                    <div class="app_form_fields adpost_fields default_fields aol-template-wrapper">
                            <?php 
                                $app_fields = $this->app_field_types();
                                settings_fields( 'aol_ad_template' );
                                do_settings_sections( 'aol_ad_template' );
                                
                                //Support for deprecated Template Form.
                                $xfields = get_option('aol_default_fields');
                                if(!empty($xfields)){
                                    $xfields['templateName'] = 'Default Template';
                                    update_option ('aol_form_templates', array('templatedefault' => $xfields));
                                    update_option ('aol_default_fields_x', $xfields, FALSE);
                                    delete_option('aol_default_fields');
                                }

                                //update_option('aol_form_templates', array('english' => $template, 'french' => $template));
                                $templates = get_option('aol_form_templates', array());
                                if(!empty($templates)):
                                    $i = 0;
                                    echo '<h2 class="nav-tab-wrapper aol-template-tabs">';
                                    foreach($templates as $key => $val){ ?>
                                        <a class="nav-tab <?php if($i ==0) echo 'nav-tab-active'; ?>" data-id="<?php echo sanitize_key($key); ?>"><?php echo sanitize_text_field($val['templateName']); ?></a>
                                    <?php $i++; } ?>
                                    <a class="nav-tab" data-id="new"><span class="dashicons dashicons-plus-alt"></span></a>
                                    <?php
                                    echo '</h2>';
                                    foreach ($templates as $tempid => $temp):
                                        $tempid = sanitize_key($tempid);
                                        //$fields = apply_filters('aol_ad_default_fields', get_option('aol_default_fields'));
                                        //if(!empty($temp)):
                                        ?>
                                        <div id="<?php echo esc_attr( $tempid ); ?>" class="templateForm aolFormBuilder">
<<<<<<< Updated upstream
                                            <p><input type="text" class="aolTempName" name="<?php echo esc_attr( $tempid ); ?>[templateName]" value="<?php echo esc_attr($temp['templateName']); ?>" placeholder="<?php esc_attr_e('Template Name', 'ApplyOnline'); ?>" /> <span class="dashicons aol-remove dashicons-trash"></span></p>
=======
                                            <p><input type="text" class="aolTempName" name="<?php echo esc_attr( $tempid ); ?>[templateName]" value="<?php echo esc_attr($temp['templateName']); ?>" placeholder="<?php esc_attr_e('Template Name', 'apply-online'); ?>" /> <span class="dashicons aol-remove dashicons-trash"></span></p>
>>>>>>> Stashed changes
                                            <table class="aol_table widefat striped">
                                                <tbody class="app_form_fields <?php echo $tempid; ?>>">
                                                <?php $this->aol_form_template($temp, $tempid); ?>
                                                </tbody>
                                             </table>
                                         <?php $this->application_fields_generator($this->app_field_types(), $tempid); ?>
                                        </div>
                                    <?php endforeach; endif; //Tempaltes loop ?>
                                    <div id="new" class="templateForm aolFormBuilder templateFormNew">
                                        <table class="aol_table widefat striped">
                                            <thead>
                                                <tr>
<<<<<<< Updated upstream
                                                    <td colspan="3"><input type="text" name="new[templateName]" placeholder="<?php esc_attr_e('Template Name', 'ApplyOnline'); ?>" /></td>
=======
                                                    <td colspan="3"><input type="text" name="new[templateName]" placeholder="<?php esc_attr_e('Template Name', 'apply-online'); ?>" /></td>
>>>>>>> Stashed changes
                                                </tr>
                                            </thead>
                                            <tbody class="app_form_fields"></tbody>
                                        </table>
                                         <?php $this->application_fields_generator($this->app_field_types(), 'new'); ?>
                                     </div>
                    </div>  
                <hr />
<<<<<<< Updated upstream
                <?php submit_button(esc_html__('Save form Templates', 'ApplyOnline')); ?>
=======
                <?php submit_button(esc_html__('Save form Templates', 'apply-online')); ?>
>>>>>>> Stashed changes
                <?php wp_nonce_field( 'aol_awesome_pretty_nonce','aol_default_app_fields' ); ?>
            </form>                
            </div>
        <?php do_action('aol_after_template');
    }

    public function tab_filters(){
            $filter = get_option('aol_show_filter', 1);
            ob_start();
        ?>
            <form action="options.php" method="post"  id="aol_filters_form">
            <div class="app_form_fields adpost_fields">
                <div class="aol_table">
                <table id="ad_custom_filters">
                    <tbody>
                    <?php
                    settings_fields( 'aol_filters' ); 
                    do_settings_sections( 'aol_filters' );
                    $filters = get_option('aol_ad_filters');
                    $i=0;
                    if(!empty($filters)):
                        foreach ($filters as $key => $val){
                            echo '<tr>';
                            if(!isset($val['plural'])){
                                echo '<td><label for="filter-'. sanitize_key($key).'" ><strong>'.sanitize_text_field($key).'</strong> </label></td><td><input type="text" name="aol_ad_filters['.sanitize_key($key).'][singular]" value="'.esc_attr($val).'" placeholder="Singular" /> <input type="text" name="aol_ad_filters['.sanitize_key($key).'][plural]" value="'.esc_attr($val).'" placeholder="Singular" /></td>';
                            } else {
                                echo '<td><label for="filter-'. sanitize_key($key).'" ><strong>'.sanitize_text_field($val['plural']).'</strong> </label></td><td><input type="text" name="aol_ad_filters['.sanitize_key($key).'][singular]" value="'.esc_attr($val['singular']).'" placeholder="Singular" /> <input type="text" name="aol_ad_filters['.sanitize_key($key).'][plural]" value="'.esc_attr($val['plural']).'" placeholder="Singular" /></td>';
                            }
                            echo '<td><span class="aol-remove dashicons dashicons-trash removeField button-trash"></span></td>';
                            echo '<tr>';
                            $i++;
                        }
                    endif;
                    ?>
                    </tbody>
                    <tfoot id="adapp_form_fields">
                        <tr>
                            <td></td>
                            <td class="left" id="newmetaleft">
<<<<<<< Updated upstream
                                <input type="text" id="ad_filter_singular" placeholder="<?php esc_attr_e('Singular Name', 'ApplyOnline'); ?>" /> <input type="text" id="ad_filter_plural" placeholder="<?php esc_attr_e('Plural Name', 'ApplyOnline'); ?>" />
                            </td>
                            <td class="left" id="newmetaleft">
                                <div class=""><div class="button aol-add" id="ad_aol_filter"><span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Add Filter', 'ApplyOnline'); ?></div></div>
=======
                                <input type="text" id="ad_filter_singular" placeholder="<?php esc_attr_e('Singular Name', 'apply-online'); ?>" /> <input type="text" id="ad_filter_plural" placeholder="<?php esc_attr_e('Plural Name', 'apply-online'); ?>" />
                            </td>
                            <td class="left" id="newmetaleft">
                                <div class=""><div class="button aol-add" id="ad_aol_filter"><span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Add Filter', 'apply-online'); ?></div></div>
>>>>>>> Stashed changes
                            </td>
                        </tr>                        
                    </tfoot>
                </table>
                    </div>
            </div>  
            <!--Generator -->
            <div class="clearfix clear"></div>
            <div class="clearfix clear"></div>
<<<<<<< Updated upstream
            <p class="description"><b><?php esc_html_e('IMPORTANT', 'ApplyOnline'); ?></b> <i><?php esc_html_e('Filters are used to narrow down ads listing on front-end and work with [aol] shortcode only.'); ?> <?php printf(esc_html__('Saved filters are available in %sAd Types%s section.', 'ApplyOnline'), '<strong>', '</strong>'); ?></i></p>
=======
            <p class="description"><b><?php esc_html_e('IMPORTANT', 'apply-online'); ?></b> <i><?php esc_html_e('Filters are used to narrow down ads listing on front-end and work with [aol] shortcode only.'); ?> <?php printf(esc_html__('Saved filters are available in %sAd Types%s section.', 'apply-online'), '<strong>', '</strong>'); ?></i></p>
>>>>>>> Stashed changes
            <?php submit_button(); ?>
            </form>
            <?php
             return ob_get_clean();
        }
        
    private function tab_types(){
            $types= aol_ad_types();
        ?>
            <form id="types_form" method="post" action="options.php" >
                <div class="app_form_fields adpost_fields">
                    <div class="aol_table">
                    <ol id="ad_types">
                        <?php 
                            settings_fields( 'aol_ads' ); 
                            do_settings_sections( 'aol_ads' );
                            if(!empty($types)): 
                                //@todo: Show data in tebular form intead of listing.
                                foreach($types as $key => $type):
                                    $type['filters'] = isset($type['filters']) ? $type['filters'] : array();
                                    //Sanitizing key beforehand as it's used for multiple times.
                                    $key = sanitize_key($key);
                                    $count = wp_count_posts('aol_'.sanitize_key($type['singular']));
                                    echo '<li><p><a href="'. admin_url('edit.php?post_type=aol_'. sanitize_key( $type['singular']) ).'">'.sanitize_text_field( $type['singular'] ) .' ('. sanitize_text_field( $type['plural'] ) .')</a></p>';
<<<<<<< Updated upstream
                                        echo '<p><b>'.esc_html__('Description', 'ApplyOnline').': </b><input type="text" name="aol_ad_types['.$key.'][description]" value="'. esc_attr( $type['description'] ).'" Placeholder="'.esc_attr__('Not set', 'ApplyOnline').'"/></p>';
                                    echo '<p><b>'.esc_html__('Shortcode', 'ApplyOnline').': </b><input type="text" readonly value="[aol type=&quot;'.sanitize_key($type['singular']).'&quot;]" /></p>';
                                    echo '<p><b>'.esc_html__('Direct URL', 'ApplyOnline').': <a href="'.get_post_type_archive_link( 'aol_'.$key ).'" target="_blank">'.get_post_type_archive_link( 'aol_'.$key ).'</a></b></p>';
                                    echo '<p class="description">'.esc_html__("Direct links comes with theme's UI and shortcode comes with plugin's UI", 'ApplyOnline').'</p>';
                                    echo '<input type="hidden" name="aol_ad_types['.$key.'][singular]" value="'.$type['singular'].'"/>';
                                    echo '<input type="hidden" name="aol_ad_types['.$key.'][plural]" value="'.$type['plural'].'"/>';
                                    $this->filters($type['filters'], $key);
                                    if($key != 'ad') echo ' <button class="button button-small aol-remove button-danger">'.esc_html__('Delete', 'ApplyOnline').'</button></li>';
=======
                                        echo '<p><b>'.esc_html__('Description', 'apply-online').': </b><input type="text" name="aol_ad_types['.$key.'][description]" value="'. esc_attr( $type['description'] ).'" Placeholder="'.esc_attr__('Not set', 'apply-online').'"/></p>';
                                    echo '<p><b>'.esc_html__('Shortcode', 'apply-online').': </b><input type="text" readonly value="[aol type=&quot;'.sanitize_key($type['singular']).'&quot;]" /></p>';
                                    echo '<p><b>'.esc_html__('Direct URL', 'apply-online').': <a href="'.get_post_type_archive_link( 'aol_'.$key ).'" target="_blank">'.get_post_type_archive_link( 'aol_'.$key ).'</a></b></p>';
                                    echo '<p class="description">'.esc_html__("Direct links comes with theme's UI and shortcode comes with plugin's UI", 'apply-online').'</p>';
                                    echo '<input type="hidden" name="aol_ad_types['.$key.'][singular]" value="'.$type['singular'].'"/>';
                                    echo '<input type="hidden" name="aol_ad_types['.$key.'][plural]" value="'.$type['plural'].'"/>';
                                    $this->filters($type['filters'], $key);
                                    if($key != 'ad') echo ' <button class="button button-small aol-remove button-danger">'.esc_html__('Delete', 'apply-online').'</button></li>';
>>>>>>> Stashed changes
                                endforeach;
                            endif;
                        ?>
                    </ol>
                    </div>
                </div>  
                
                <!--Generator -->
                <div class="clearfix clear"></div>
                <table id="adapp_form_fields" class="alignleft">
                <tbody>
                    <tr>
                        <td class="left" id="singular">
<<<<<<< Updated upstream
                            <input type="text" id="ad_type_singular" placeholder="<?php esc_attr_e('Singular e.g. Career', 'ApplyOnline'); ?>" />
                        </td>
                        <td class="left" id="plural">
                            <input type="text" id="ad_type_plural" placeholder="<?php esc_attr_e('Plural e.g. Careers', 'ApplyOnline'); ?>" />
                        </td>
                        <td class="left" id="desc">
                            <input type="text" id="ad_type_description" placeholder="<?php esc_attr_e('Description', 'ApplyOnline'); ?>" />
=======
                            <input type="text" id="ad_type_singular" placeholder="<?php esc_attr_e('Singular e.g. Career', 'apply-online'); ?>" />
                        </td>
                        <td class="left" id="plural">
                            <input type="text" id="ad_type_plural" placeholder="<?php esc_attr_e('Plural e.g. Careers', 'apply-online'); ?>" />
                        </td>
                        <td class="left" id="desc">
                            <input type="text" id="ad_type_description" placeholder="<?php esc_attr_e('Description', 'apply-online'); ?>" />
>>>>>>> Stashed changes
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
<<<<<<< Updated upstream
                            <div class=""><div class="button" id="ad_aol_type"><?php esc_html_e('Add New Ad Type', 'ApplyOnline'); ?></div></div>
=======
                            <div class=""><div class="button" id="ad_aol_type"><?php esc_html_e('Add New Ad Type', 'apply-online'); ?></div></div>
>>>>>>> Stashed changes
                        </td>
                    </tr>
                </tbody>
                </table>
                <div class="clearfix clear"></div>
<<<<<<< Updated upstream
                <p class="description"><b><?php esc_html_e('IMPORTANT', 'ApplyOnline'); ?></b> <?php printf(esc_html__('If you get 404 error on direct links, try saving this section once again.', 'ApplyOnline')); ?></p>
=======
                <p class="description"><b><?php esc_html_e('IMPORTANT', 'apply-online'); ?></b> <?php printf(esc_html__('If you get 404 error on direct links, try saving this section once again.', 'apply-online')); ?></p>
>>>>>>> Stashed changes
                <hr />
                <?php submit_button(); ?>
        </form>     
        <?php 
    }
    
    private function filters($set_filters, $cpt){
        $cpt = sanitize_key($cpt);
        ?>
            <ul id="ad_filters">
                <?php
                $filters = get_option_fixed('aol_ad_filters', array() );
                
                foreach ($filters as $key => $val){
                    
                    //Sanitizing key before hand as its used for multiple times.
                    //$cpt is already sanitized.
                    $key = sanitize_key($key);
                    $checked = in_array($key, $set_filters) ? 'checked' : NULL;
<<<<<<< Updated upstream
                    echo '<li><input id="filter-'.$cpt.'-'.$key.'" type="checkbox" name="aol_ad_types['.$cpt.'][filters][]" value="'.$key.'" '.$checked.'><label for="filter-'.$cpt.'-'.$key.'">'. sprintf(esc_html__('Enable %s filter', 'ApplyOnline'), sanitize_text_field($val['plural'])).'</label></li>';
=======
                    echo '<li><input id="filter-'.$cpt.'-'.$key.'" type="checkbox" name="aol_ad_types['.$cpt.'][filters][]" value="'.$key.'" '.$checked.'><label for="filter-'.$cpt.'-'.$key.'">'. sprintf(esc_html__('Enable %s filter', 'apply-online'), sanitize_text_field($val['plural'])).'</label></li>';
>>>>>>> Stashed changes
                }
                ?>
            </ul>
                <?php do_action('aol_after_filters'); ?>
        <?php
    }
    
    private function tab_applications(){
        ?>
            <form  action="options.php" method="post"  id="aol_applications_form">
            <div class="app_form_fields adpost_fields">
                <div class="aol_table">
                <ul id="aol_application_status_setting">
                    <?php 
                    settings_fields( 'aol_applications' );
                    do_settings_sections( 'aol_applications' );
                    $filters = aol_app_statuses();
                    $set_filters = get_option_fixed('aol_app_statuses', array());
                    $i = 0;
                    foreach ($filters as $key => $val){
                        //Sanitizing variables beforehand.
                        $key = sanitize_key($key);
                        $val = esc_attr($val);
                        $checked = in_array($key, $set_filters) ? 'checked' : NULL;
<<<<<<< Updated upstream
                        echo '<li><input type="hidden" name="aol_custom_statuses['.$key.']" value="'.$val.'" /><input id="filter-'.$key.'" type="checkbox" name="aol_app_statuses[]" value="'.$key.'" '.$checked.'><label for="filter-'.$key.'">'.sprintf(esc_html__('Enable %s status.', 'ApplyOnline'), $val).'</label>';
=======
                        echo '<li><input type="hidden" name="aol_custom_statuses['.$key.']" value="'.$val.'" /><input id="filter-'.$key.'" type="checkbox" name="aol_app_statuses[]" value="'.$key.'" '.$checked.'><label for="filter-'.$key.'">'.sprintf(esc_html__('Enable %s status.', 'apply-online'), $val).'</label>';
>>>>>>> Stashed changes
                        if( !in_array($key, array('pending', 'rejected', 'shortlisted'))) echo '<span class="aol-remove dashicons dashicons-trash button-trash dashicons dashicons-dismiss"></span>';
                        echo '</li>';
                        $i++;
                    }
                    ?>
                </ul>
                <div class="clearfix clear"></div>
               <!--Generator -->
               <div class="clearfix clear"></div>
               <table id="adapp_form_fields" class="alignleft">
               <tbody>
                   <tr>
                       <td class="left" id="newmetaleft">
                           <input type="text" id="ad_status_singular" placeholder="<?php esc_attr_e('Status Name', 'apply-online'); ?>" />
                       </td>
                       <td class="left" id="newmetaleft">
<<<<<<< Updated upstream
                           <div class=""><div class="button aol-add" id="ad_aol_status"><span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Add Status', 'ApplyOnline'); ?></div></div>
=======
                           <div class=""><div class="button aol-add" id="ad_aol_status"><span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Add Status', 'apply-online'); ?></div></div>
>>>>>>> Stashed changes
                       </td>
                   </tr>
                   <tr>
                       <td colspan="2">

                       </td>
                   </tr>
               </tbody>
               </table>
               <div class="clearfix clear"></div>
                    <?php do_action('aol_after_application_setting'); ?>
                </div>
            </div>  
            <hr />
            <div class="clearfix clear"></div>
            <?php submit_button(); ?>
        </form>     
        <?php 
    }

    private function tab_ui(){
        ?>
        <form action="options.php" method="post" name="">
                <table class="form-table">
                <?php
                    settings_fields( 'aol_ui_settings_group' ); 
                    do_settings_sections( 'aol_ui_settings_group' );
                    ?>
                    <tr>
<<<<<<< Updated upstream
                        <th><label for="aol_submit_button_classes"><?php esc_html_e('Submit Button Classes', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_submit_button_classes" class="regular-text" name="aol_submit_button_classes" value="<?php echo esc_attr(get_option('aol_submit_button_classes')); ?>">
                            <p class="description"><?php esc_html_e('Extra button classes to ad theme support.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_readmore_button_classes"><?php esc_html_e('Read More Button Classes', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_readmore_button_classes" class="regular-text" name="aol_readmore_button_classes" value="<?php echo esc_attr(get_option('aol_readmore_button_classes')); ?>">
                            <p class="description"><?php esc_html_e('Extra button classes to ad theme support.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_multistep_button_classes"><?php esc_html_e('Multistep Buttons Classes', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_multistep_button_classes" class="regular-text" name="aol_multistep_button_classes" value="<?php echo esc_attr(get_option('aol_multistep_button_classes')); ?>">
                            <p class="description"><?php esc_html_e('Extra button classes to ad theme support.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_form_classes"><?php esc_html_e('Form Classes', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_form_classes" class="regular-text" name="aol_form_classes" value="<?php echo esc_attr(get_option('aol_form_classes')); ?>">
                            <p class="description"><?php esc_html_e('Extra form classes to ad theme support.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr> 
                    <tr>
                        <th><label for="aol_form_field_classes"><?php esc_html_e('Form Fields Classes', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_form_field_classes" class="regular-text" name="aol_form_field_classes" value="<?php echo esc_attr(get_option('aol_form_field_classes')); ?>">
                            <p class="description"><?php esc_html_e('Extra classes for form fields to ad theme support.', 'ApplyOnline'); ?></p>
=======
                        <th><label for="aol_submit_button_classes"><?php esc_html_e('Submit Button Classes', 'apply-online'); ?></label></th>
                        <td>
                            <input type="text" id="aol_submit_button_classes" class="regular-text" name="aol_submit_button_classes" value="<?php echo esc_attr(get_option('aol_submit_button_classes')); ?>">
                            <p class="description"><?php esc_html_e('Extra button classes to ad theme support.', 'apply-online'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_readmore_button_classes"><?php esc_html_e('Read More Button Classes', 'apply-online'); ?></label></th>
                        <td>
                            <input type="text" id="aol_readmore_button_classes" class="regular-text" name="aol_readmore_button_classes" value="<?php echo esc_attr(get_option('aol_readmore_button_classes')); ?>">
                            <p class="description"><?php esc_html_e('Extra button classes to ad theme support.', 'apply-online'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_multistep_button_classes"><?php esc_html_e('Multistep Buttons Classes', 'apply-online'); ?></label></th>
                        <td>
                            <input type="text" id="aol_multistep_button_classes" class="regular-text" name="aol_multistep_button_classes" value="<?php echo esc_attr(get_option('aol_multistep_button_classes')); ?>">
                            <p class="description"><?php esc_html_e('Extra button classes to ad theme support.', 'apply-online'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_form_classes"><?php esc_html_e('Form Classes', 'apply-online'); ?></label></th>
                        <td>
                            <input type="text" id="aol_form_classes" class="regular-text" name="aol_form_classes" value="<?php echo esc_attr(get_option('aol_form_classes')); ?>">
                            <p class="description"><?php esc_html_e('Extra form classes to ad theme support.', 'apply-online'); ?></p>
                        </td>
                    </tr> 
                    <tr>
                        <th><label for="aol_form_field_classes"><?php esc_html_e('Form Fields Classes', 'apply-online'); ?></label></th>
                        <td>
                            <input type="text" id="aol_form_field_classes" class="regular-text" name="aol_form_field_classes" value="<?php echo esc_attr(get_option('aol_form_field_classes')); ?>">
                            <p class="description"><?php esc_html_e('Extra classes for form fields to ad theme support.', 'apply-online'); ?></p>
>>>>>>> Stashed changes
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        <?php
    }
    private function tab_faqs(){
        $slug = get_option_fixed('aol_slug', 'ads');
        $faqs = array(
            array(
<<<<<<< Updated upstream
                'question' => esc_html__('How to create an ad?', 'ApplyOnline'),
                'answer' => esc_html__('In your WordPress admin panel, go to "All Ads" menu with globe icon and add a new ad listing here.', 'ApplyOnline')
            ),
            array(
                'question' => esc_html__('How to show ad listings on the front-end?', 'ApplyOnline'),
                'answer' => esc_html__('You may choose either option.', 'ApplyOnline'),
            ),
            array(
                'answer' => array(
                    esc_html__('Write [aol] shortcode in an existing page or add a new page and write shortcode anywhere in the page editor. Now click on VIEW to see all of your ads on front-end.?' ,'ApplyOnline'),
                    sprintf(esc_html__('The url %s lists all the ads using your theme&#39;s default look and feel. %s(If above not working, try saving %s permalinks %s without any changes)' ,'ApplyOnline'), '<b><a href="'.get_post_type_archive_link( 'aol_ad' ).'" target="_blank" >'.get_post_type_archive_link( 'aol_ad' ).'</a></b>', '<br />&nbsp; &nbsp;&nbsp;', '<a href="'.get_admin_url().'options-permalink.php"  >', '</a>')
                )
            ),
            array(
                'question' => esc_html__('Ads archive page on front-end shows 404 error or Nothing Found.' ,'ApplyOnline'),
                'answer' => sprintf(esc_html__('Try saving %spermalinks%s without any change.' ,'ApplyOnline'), '<a href="'.get_admin_url().'options-permalink.php"  >', '</a>')
            ),
            array(
                'question' => esc_html__('I have a long application form to fill, how can i facilitate applicant to fill it conveniently?' ,'ApplyOnline'),
                'answer' => sprintf(esc_html__('With %sApplication Tracking System%s extention, applicant can save/update incomplete form for multiple times before final submission.' ,'ApplyOnline'), '<a href="https://wpreloaded.com/product/apply-online-application-tracking-system/" target="_blank" class="strong">', '</a>')
            ),
            array(
                'question' => esc_html__('How can I show selected ads on front-end?' ,'ApplyOnline'),
                'answer' => array(
                        esc_html__('You can show selected ads on your website by using shortcode with "ads" attribute. Ad ids must be separated with commas i.e. [aol ads="1,2,3"].' ,'ApplyOnline'),
=======
                'question' => esc_html__('How to create an ad?', 'apply-online'),
                'answer' => esc_html__('In your WordPress admin panel, go to "All Ads" menu with globe icon and add a new ad listing here.', 'apply-online')
            ),
            array(
                'question' => esc_html__('How to show ad listings on the front-end?', 'apply-online'),
                'answer' => esc_html__('You may choose either option.', 'apply-online'),
            ),
            array(
                'answer' => array(
                    esc_html__('Write [aol] shortcode in an existing page or add a new page and write shortcode anywhere in the page editor. Now click on VIEW to see all of your ads on front-end.?' ,'apply-online'),
                    sprintf(esc_html__('The url %s lists all the ads using your theme&#39;s default look and feel. %s(If above not working, try saving %s permalinks %s without any changes)' ,'apply-online'), '<b><a href="'.get_post_type_archive_link( 'aol_ad' ).'" target="_blank" >'.get_post_type_archive_link( 'aol_ad' ).'</a></b>', '<br />&nbsp; &nbsp;&nbsp;', '<a href="'.get_admin_url().'options-permalink.php"  >', '</a>')
                )
            ),
            array(
                'question' => esc_html__('Ads archive page on front-end shows 404 error or Nothing Found.' ,'apply-online'),
                'answer' => sprintf(esc_html__('Try saving %spermalinks%s without any change.' ,'apply-online'), '<a href="'.get_admin_url().'options-permalink.php"  >', '</a>')
            ),
            array(
                'question' => esc_html__('I have a long application form to fill, how can i facilitate applicant to fill it conveniently?' ,'apply-online'),
                'answer' => sprintf(esc_html__('With %sApplication Tracking System%s extention, applicant can save/update incomplete form for multiple times before final submission.' ,'apply-online'), '<a href="https://wpreloaded.com/product/apply-online-application-tracking-system/" target="_blank" class="strong">', '</a>')
            ),
            array(
                'question' => esc_html__('How can I show selected ads on front-end?' ,'apply-online'),
                'answer' => array(
                        esc_html__('You can show selected ads on your website by using shortcode with "ads" attribute. Ad ids must be separated with commas i.e. [aol ads="1,2,3"].' ,'apply-online'),
>>>>>>> Stashed changes
                        esc_html__('To show first 5 ads, use count shortcode attribute i.e. [aol count="5"]')
                    ),
            ),
            array(
<<<<<<< Updated upstream
                'question' => esc_html__('Can I show ads without excerpt/summary?' ,'ApplyOnline'),
                'answer' => esc_html__('Yes, use shortcode with "excerpt" attribute i.e. [aol excerpt="no"]' ,'ApplyOnline')
            ),
            array(
                'question' => esc_html__('What attributes can i use in the shortcode?' ,'ApplyOnline'),
                'answer' => esc_html__('Shortcode with default attributes is [aol ads="all" count="-1" excerpt="yes" type="ad" display="full"]. Use only required attributes.' ,'ApplyOnline')
            ),
            array(
                'question' => esc_html__('Can I display only application form using shortocode?' ,'ApplyOnline'),
                'answer' => esc_html__(' Yes, [aol_form id="0"] is the shortcode to display a particular application form in WordPress pages or posts. Use correct form id in the shortocode.' ,'ApplyOnline')
            ),
            array(
                'question' => esc_html__('Can I list ads without any fancy styling?' ,'ApplyOnline'),
                'answer' => esc_html__('Yes, use shortcode with "style" attribute to list ads with bullets i.e. [aol display="list"]. To generate an ordered list add another attribute "list-style" i.e. [aol display="list" list-style="ol"].' ,'ApplyOnline')
            ),
            array(
                'question' => esc_html__('Filters under ApplyOnline section are not accessible.' ,'ApplyOnline'),
                'answer' => esc_html__('Try deactivating & then reactivating this plugin.' ,'ApplyOnline')
            ),
            array(
                'question' => esc_html__("I Have enabled the filters but they are not visible on the 'ads' page." ,'ApplyOnline'),
=======
                'question' => esc_html__('Can I show ads without excerpt/summary?' ,'apply-online'),
                'answer' => esc_html__('Yes, use shortcode with "excerpt" attribute i.e. [aol excerpt="no"]' ,'apply-online')
            ),
            array(
                'question' => esc_html__('What attributes can i use in the shortcode?' ,'apply-online'),
                'answer' => esc_html__('Shortcode with default attributes is [aol ads="all" count="-1" excerpt="yes" type="ad" display="full"]. Use only required attributes.' ,'apply-online')
            ),
            array(
                'question' => esc_html__('Can I display only application form using shortocode?' ,'apply-online'),
                'answer' => esc_html__(' Yes, [aol_form id="0"] is the shortcode to display a particular application form in WordPress pages or posts. Use correct form id in the shortocode.' ,'apply-online')
            ),
            array(
                'question' => esc_html__('Can I list ads without any fancy styling?' ,'apply-online'),
                'answer' => esc_html__('Yes, use shortcode with "style" attribute to list ads with bullets i.e. [aol display="list"]. To generate an ordered list add another attribute "list-style" i.e. [aol display="list" list-style="ol"].' ,'apply-online')
            ),
            array(
                'question' => esc_html__('Filters under ApplyOnline section are not accessible.' ,'apply-online'),
                'answer' => esc_html__('Try deactivating & then reactivating this plugin.' ,'apply-online')
            ),
            array(
                'question' => esc_html__("I Have enabled the filters but they are not visible on the 'ads' page." ,'apply-online'),
>>>>>>> Stashed changes
                'answer' => ''
            ),
            array(
                'question' => '',
                'answer' => ''
            ),
            array(
                'question' => '',
                'answer' => ''
            ),
            array(
                'question' => '',
                'answer' => ''
            ),
            array(
                'question' => '',
                'answer' => ''
            ),
            array(
                'question' => '',
                'answer' => ''
            ),
            array(
                'question' => '',
                'answer' => ''
            ),
        );
        ?>
        <div class="card" style="max-width:100%">
<<<<<<< Updated upstream
            <h3><?php esc_html_e('How to create an ad?' ,'ApplyOnline'); ?></h3>
            <?php esc_html_e('In your WordPress admin panel, go to "All Ads" menu with globe icon and add a new ad listing here.', 'ApplyOnline'); ?>

            <h3><?php esc_html_e('How to show ad listings on the front-end?' ,'ApplyOnline'); ?></h3>
            <!-- @todo Fix empty return value of aol_slug option. !-->
            <?php esc_html_e('You may choose either option.' ,'ApplyOnline') ?>
            <ol>
                <li><?php esc_html_e('Write [aol] shortcode in an existing page or add a new page and write shortcode anywhere in the page editor. Now click on VIEW to see all of your ads on front-end.?' ,'ApplyOnline') ?>
                <li><?php printf(esc_html__('The url %s lists all the applications using your theme&#39;s default look and feel. %s(If above not working, try saving %s permalinks %s without any change)' ,'ApplyOnline'), '<b><a href="'.get_post_type_archive_link( 'aol_ad' ).'" target="_blank" >'.get_post_type_archive_link( 'aol_ad' ).'</a></b>', '<br />&nbsp; &nbsp;&nbsp;', '<a href="'.get_admin_url().'options-permalink.php"  >', '</a>'); ?></li>
            </ol>
            <h3><?php esc_html_e('Application form submission fails and shows Session Expired error.', 'ApplyOnline'); ?></h3>
            <?php esc_html_e('If you have firewall installed such as WordFence or CloudFlare then disabling Security Nonce under General tab will be helpful.', 'ApplyOnline'); ?>
            
            <h3><?php esc_html_e('Ads archive page on front-end shows 404 error or Nothing Found.' ,'ApplyOnline') ?></h3>
            <?php printf(esc_html__('Try saving %spermalinks%s without any change.' ,'ApplyOnline'), '<a href="'.get_admin_url().'options-permalink.php"  >', '</a>'); ?>
            
            <h3><?php esc_html_e('I have a long application form to be filled, how can i facilitate applicant to fill it conveniently?' ,'ApplyOnline'); ?></h3>
            <?php printf(esc_html__('With %sApplication Tracking System%s extention, applicant can save/update incomplete form for multiple times before final submission.' ,'ApplyOnline'), '<a href="https://wpreloaded.com/product/apply-online-application-tracking-system/" target="_blank" class="strong">', '</a>'); ?>
            
            <h3><?php esc_html_e('Can I show selected ads on front-end?' ,'ApplyOnline'); ?></h3>
            <?php esc_html_e('Yes, you can show any number of ads on your website by using shortcode with "ads" attribute. Ad ids must be separated with commas i.e. [aol ads="1,2,3" type="ad"]. Default type is "ad".' ,'ApplyOnline'); ?>

            <h3><?php esc_html_e('Can I show ads without excerpt/summary?' ,'ApplyOnline'); ?></h3>
            <?php esc_html_e('Yes, use shortcode with "excerpt" attribute i.e. [aol excerpt="no"]' ,'ApplyOnline'); ?>

            <h3><?php esc_html_e('What attributes can i use in the shortcode?' ,'ApplyOnline'); ?></h3>
            <?php printf(esc_html__('Shortcode with all attributes and default values is %s. Use only required attributes.' ,'ApplyOnline'), '[aol count="-1" ads="all" excerpt="yes" type="ad" display="full"]'); ?>
            <ul>
                <li><?php esc_html_e('"count" is used to control number of ads shown in the ads list. e.g. count="10" will show latest 10 ads.', 'ApplyOnline'); ?></li>
                <li><?php esc_html_e('"ads" is used to show selected ads. This attribute accepts ads ids e.g. ads="5,10,70" will show three ads with given ids.', 'ApplyOnline'); ?></li>
                <li><?php esc_html_e('"excerpt" is used to show or hide excerpt in each ad section. It excepts two values yes or no.', 'ApplyOnline'); ?></li>
                <li><?php esc_html_e('"type" attribute is used to show ads from selected ad type only e.g. type="admission"', 'ApplyOnline'); ?></li>
                <li><?php esc_html_e('"display" attribute is used to control the output style of the shortcode. It accepts two display types full or list, ', 'ApplyOnline'); ?></li>
            </ul>

            <h3><?php esc_html_e('Can I display application form only using shortocode?' ,'ApplyOnline'); ?></h3>
            <?php esc_html_e(' Yes, [aol_form id="0"] is the shortcode to display a particular application form in WordPress pages or posts. Use correct form id in the shortocode.' ,'ApplyOnline'); ?>
            
            <h3><?php esc_html_e('Can I list ads without any fancy styling?' ,'ApplyOnline'); ?></h3>
            <?php esc_html_e('Yes, use shortcode with "style" attribute to list ads with bullets i.e. [aol display="list"]. To generate an ordered list add another attribute "list-style" i.e. [aol display="list" list-style="ol"].' ,'ApplyOnline'); ?>
            
            <h3><?php esc_html_e('Filters under ApplyOnline section are not accessible.' ,'ApplyOnline'); ?></h3>
            <?php esc_html_e('Try deactivating & then reactivating this plugin.' ,'ApplyOnline'); ?>
            
            <h3><?php esc_html_e("I Have enabled the filters but they are not visible on the 'ads' page." ,'ApplyOnline'); ?></h3>
            <?php esc_html_e('Possible reasons for not displaying ad filters are given as under:' ,'ApplyOnline'); ?>

            <ol>
                <li><?php esc_html_e('Filters are visible when you show your ad on front-end using [aol] shortcode only. ' ,'ApplyOnline'); ?></li>
                <li><?php esc_html_e('Make sure Filters are enable under ApplyOnline/Settings/AdTypes section in wordpress Admin Panel.' ,'ApplyOnline'); ?></li>
                <li><?php esc_html_e('On Ad Editor screen in the right siedebar, there is an option to mark the ad for a filter e.g. Categories, Types or Locations.' ,'ApplyOnline'); ?></li>
            </ol>
            
            <h3><?php esc_html_e('Is plugin not working accordingly or generating 500 internal server error?' ,'ApplyOnline') ?></h3>
            <?php printf(esc_html__("You may need to resolve a theme or plugin conflict with ApplyOnline plugin. %s Click Here %s to fix this conflict." ,'ApplyOnline'), '<a href="https://wpreloaded.com/wordpress-theme-or-plugin-conflicts-and-their-solution/" target="_blank">', '</a>'); ?>            
            
            <h3><?php esc_html_e('I am facing a different problem. I may need a new feature in the plugin.' ,'ApplyOnline') ?></h3>
            <?php printf(esc_html__("Please contact us through %s plugin's website %s for more information." ,'ApplyOnline'), '<a href="https://wpreloaded.com/contact-us/" target="_blank">', '</a>'); ?>
=======
            <h3><?php esc_html_e('How to create an ad?' ,'apply-online'); ?></h3>
            <?php esc_html_e('In your WordPress admin panel, go to "All Ads" menu with globe icon and add a new ad listing here.', 'apply-online'); ?>

            <h3><?php esc_html_e('How to show ad listings on the front-end?' ,'apply-online'); ?></h3>
            <!-- @todo Fix empty return value of aol_slug option. !-->
            <?php esc_html_e('You may choose either option.' ,'apply-online') ?>
            <ol>
                <li><?php esc_html_e('Write [aol] shortcode in an existing page or add a new page and write shortcode anywhere in the page editor. Now click on VIEW to see all of your ads on front-end.?' ,'apply-online') ?>
                <li><?php printf(esc_html__('The url %s lists all the applications using your theme&#39;s default look and feel. %s(If above not working, try saving %s permalinks %s without any change)' ,'apply-online'), '<b><a href="'.get_post_type_archive_link( 'aol_ad' ).'" target="_blank" >'.get_post_type_archive_link( 'aol_ad' ).'</a></b>', '<br />&nbsp; &nbsp;&nbsp;', '<a href="'.get_admin_url().'options-permalink.php"  >', '</a>'); ?></li>
            </ol>
            <h3><?php esc_html_e('Application form submission fails and shows Session Expired error.', 'apply-online'); ?></h3>
            <?php esc_html_e('If you have firewall installed such as WordFence or CloudFlare then disabling Security Nonce under General tab will be helpful.', 'apply-online'); ?>
            
            <h3><?php esc_html_e('Ads archive page on front-end shows 404 error or Nothing Found.' ,'apply-online') ?></h3>
            <?php printf(esc_html__('Try saving %spermalinks%s without any change.' ,'apply-online'), '<a href="'.get_admin_url().'options-permalink.php"  >', '</a>'); ?>
            
            <h3><?php esc_html_e('I have a long application form to be filled, how can i facilitate applicant to fill it conveniently?' ,'apply-online'); ?></h3>
            <?php printf(esc_html__('With %sApplication Tracking System%s extention, applicant can save/update incomplete form for multiple times before final submission.' ,'apply-online'), '<a href="https://wpreloaded.com/product/apply-online-application-tracking-system/" target="_blank" class="strong">', '</a>'); ?>
            
            <h3><?php esc_html_e('Can I show selected ads on front-end?' ,'apply-online'); ?></h3>
            <?php esc_html_e('Yes, you can show any number of ads on your website by using shortcode with "ads" attribute. Ad ids must be separated with commas i.e. [aol ads="1,2,3" type="ad"]. Default type is "ad".' ,'apply-online'); ?>

            <h3><?php esc_html_e('Can I show ads without excerpt/summary?' ,'apply-online'); ?></h3>
            <?php esc_html_e('Yes, use shortcode with "excerpt" attribute i.e. [aol excerpt="no"]' ,'apply-online'); ?>

            <h3><?php esc_html_e('What attributes can i use in the shortcode?' ,'apply-online'); ?></h3>
            <?php printf(esc_html__('Shortcode with all attributes and default values is %s. Use only required attributes.' ,'apply-online'), '[aol count="-1" ads="all" excerpt="yes" type="ad" display="full"]'); ?>
            <ul>
                <li><?php esc_html_e('"count" is used to control number of ads shown in the ads list. e.g. count="10" will show latest 10 ads.', 'apply-online'); ?></li>
                <li><?php esc_html_e('"ads" is used to show selected ads. This attribute accepts ads ids e.g. ads="5,10,70" will show three ads with given ids.', 'apply-online'); ?></li>
                <li><?php esc_html_e('"excerpt" is used to show or hide excerpt in each ad section. It excepts two values yes or no.', 'apply-online'); ?></li>
                <li><?php esc_html_e('"type" attribute is used to show ads from selected ad type only e.g. type="admission"', 'apply-online'); ?></li>
                <li><?php esc_html_e('"display" attribute is used to control the output style of the shortcode. It accepts two display types full or list, ', 'apply-online'); ?></li>
            </ul>

            <h3><?php esc_html_e('Can I display application form only using shortocode?' ,'apply-online'); ?></h3>
            <?php esc_html_e(' Yes, [aol_form id="0"] is the shortcode to display a particular application form in WordPress pages or posts. Use correct form id in the shortocode.' ,'apply-online'); ?>
            
            <h3><?php esc_html_e('Can I list ads without any fancy styling?' ,'apply-online'); ?></h3>
            <?php esc_html_e('Yes, use shortcode with "style" attribute to list ads with bullets i.e. [aol display="list"]. To generate an ordered list add another attribute "list-style" i.e. [aol display="list" list-style="ol"].' ,'apply-online'); ?>
            
            <h3><?php esc_html_e('Filters under ApplyOnline section are not accessible.' ,'apply-online'); ?></h3>
            <?php esc_html_e('Try deactivating & then reactivating this plugin.' ,'apply-online'); ?>
            
            <h3><?php esc_html_e("I Have enabled the filters but they are not visible on the 'ads' page." ,'apply-online'); ?></h3>
            <?php esc_html_e('Possible reasons for not displaying ad filters are given as under:' ,'apply-online'); ?>

            <ol>
                <li><?php esc_html_e('Filters are visible when you show your ad on front-end using [aol] shortcode only. ' ,'apply-online'); ?></li>
                <li><?php esc_html_e('Make sure Filters are enable under ApplyOnline/Settings/AdTypes section in wordpress Admin Panel.' ,'apply-online'); ?></li>
                <li><?php esc_html_e('On Ad Editor screen in the right siedebar, there is an option to mark the ad for a filter e.g. Categories, Types or Locations.' ,'apply-online'); ?></li>
            </ol>
            
            <h3><?php esc_html_e('Is plugin not working accordingly or generating 500 internal server error?' ,'apply-online') ?></h3>
            <?php printf(esc_html__("You may need to resolve a theme or plugin conflict with ApplyOnline plugin. %s Click Here %s to fix this conflict." ,'apply-online'), '<a href="https://wpreloaded.com/wordpress-theme-or-plugin-conflicts-and-their-solution/" target="_blank">', '</a>'); ?>            
            
            <h3><?php esc_html_e('I am facing a different problem. I may need a new feature in the plugin.' ,'apply-online') ?></h3>
            <?php printf(esc_html__("Please contact us through %s plugin's website %s for more information." ,'apply-online'), '<a href="https://wpreloaded.com/contact-us/" target="_blank">', '</a>'); ?>
>>>>>>> Stashed changes
        </div>    
        <?php
    }
    
    private function tab_extend(){
        ?>
        <div class="card" style="max-width:100%">
<<<<<<< Updated upstream
            <p><?php echo esc_html__('Looking for more options to put additional power in your hands?' ,'ApplyOnline') ?></p>
            <p><?php printf(esc_html__("There's a range of ApplyOnline extensions availabel. %sClick Here%s for docs and extensions." ,'ApplyOnline'), '<a href="https://wpreloaded.com/shop" target="_blank">', '</a>'); ?></p>
=======
            <p><?php echo esc_html__('Looking for more options to put additional power in your hands?' ,'apply-online') ?></p>
            <p><?php printf(esc_html__("There's a range of ApplyOnline extensions availabel. %sClick Here%s for docs and extensions." ,'apply-online'), '<a href="https://wpreloaded.com/shop" target="_blank">', '</a>'); ?></p>
>>>>>>> Stashed changes
        </div>            
        <?php
    }
 }