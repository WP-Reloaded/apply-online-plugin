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
                
                //add_action( 'set_current_user', array($this,  'output_attachment') );
                
                $this->hooks_to_search_in_post_metas();
                                
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
                $screen = get_current_screen();
                wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/applyonline-admin.css', array(), $this->version, 'all' );
                wp_enqueue_style( 'aol-select2', plugin_dir_url( __FILE__ ) . 'select2/css/select2.min.css', array(), $this->version, 'all'  );
                
                if($screen->id == 'aol_ad'); wp_enqueue_style('aol-jquery-ui-css', plugin_dir_url(__FILE__).'css/jquery-ui.min.css');
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
            
                $localize['app_submission_message'] = __('Form has been submitted successfully. If required, we will get back to you shortly!', 'ApplyOnline'); 
                $localize['app_closed_alert'] = __('We are no longer accepting applications for this ad!', 'ApplyOnline'); 
                $localize['aol_required_fields_notice'] = __('Fields with (*)  are compulsory.', 'ApplyOnline');
                $localize['admin_url'] = admin_url();
                $localize['aol_url'] = plugins_url( 'apply-online/' );
                wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/applyonline-admin.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-autocomplete' ), $this->version, false );
                wp_enqueue_script( 'aol-select2', plugin_dir_url( __FILE__ ) . 'select2/js/select2.min.js', array(), $this->version, false );
                //wp_enqueue_script($this->plugin_name.'_md5', plugin_dir_url(__FILE__).'js/md5.min.js', array( 'jquery' ), $this->version, false);
                wp_localize_script( $this->plugin_name, 'aol_admin', $localize );
                
                wp_enqueue_script( 'jquery-ui-datepicker');
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
                $views[$status] = '<a class="'.$class.'" href="'.  admin_url("edit.php?post_type=aol_application&aol_application_status=$key").'">'.__($status, 'ApplyOnline').'</a>';        
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

            //die('Hello World');
            /* OK, it's safe for us to save the data now. */
            $types = get_aol_ad_types();
            if ( !in_array($_POST['post_type'], $types) ) return;

            //Delete fields.
            $old_keys = (array)get_post_custom_keys($post_id);
            $new_keys = array_keys($_POST);
            $new_keys = array_map('sanitize_key', $new_keys); //First santize all keys.
            $removed_keys = array_diff($old_keys, $new_keys); //List of removed meta keys.
            //print_rich($removed_keys); die();
            foreach($removed_keys as $key => $val):
                if(substr($val, 0, 13) == '_aol_feature_' OR substr($val, 0, 9) == '_aol_app_'){
                        delete_post_meta($post_id, $val); //Remove meta from the db.
                }
            endforeach;
            //
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
                    var_dump($restul);
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
            
            //Update ad closing
            if ( isset($_POST['_aol_ad_closing_date']) ) {
                $time = empty(trim($_POST['_aol_ad_closing_time'])) ? '2359' : trim($_POST['_aol_ad_closing_time']);
                $timestamp = empty(trim($_POST['_aol_ad_closing_date'])) ? NULL: strtotime($_POST['_aol_ad_closing_date'].' '.$time);
                update_post_meta( $post_id, '_aol_ad_closing_date', $timestamp); //Add new value.
            }
            update_post_meta( $post_id, '_aol_ad_close_type', sanitize_key($_POST['_aol_ad_close_type']) ); //Add new value.            

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
            if( !current_user_can('manage_options') ) return;
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

        public function settings_notice(){
            if( is_plugin_active('applyonline-statuses/applyonline-statuses.php') ) echo '<div class="notice notice-warning is-dismissible"><p>'.sprintf(__('%sApplyOnline - Statuses%s extension has been obsoleted after the release of core plugin ApplyOnline 2.1. %sClick Here%s to uninstall this extension.', 'ApplyOnline'), '<strong>', '</strong>', '<a href="'.admin_url().'plugins.php">', '</a>').'</p></div>';
            if( is_plugin_active('applyonline-filters/applyonline-filters.php') ) echo '<div class="notice notice-warning is-dismissible"><p>'.sprintf(__('%sApplyOnline - Filters%s extension has been obsoleted after the release of core plugin ApplyOnline 2.1. %sClick Here%s to uninstall this extension.', 'ApplyOnline'), '<strong>', '</strong>', '<a href="'.admin_url().'plugins.php">', '</a>').'</p></div>';

            $notices = get_option('aol_dismissed_notices', array());
            if(in_array('aol', $notices) OR !current_user_can('manage_options')) return;
            //__( "%sApply Online%s - It's good to %scheck things%s before a long drive.", 'ApplyOnline' )
            ?>
                <div class="notice notice-warning is-dismissible aol">
                    <p>
                        <?php echo sprintf(__( "%sApply Online%s plugin is just installed.", 'ApplyOnline' ), '<strong>', '</strong>'); ?> 
                        <?php echo sprintf(__('%sClick Here%s for settings or close this message.', 'ApplyOnline'), '<a href="'.  get_admin_url().'?page=aol-settings">', '</a>'); ?>
                    </p>
                </div>
            <?php
        }
        
        public function admin_dismiss_notice(){
            $notices = get_option('aol_dismissed_notices', array());
            $notices[] = 'aol';
            update_option('aol_dismissed_notices', $notices);
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
        function hooks_to_search_in_post_metas(){
           add_filter('posts_join', array($this, 'cf_search_join' ));
           add_filter( 'posts_where', array($this, 'cf_search_where' ));
           add_filter( 'posts_distinct', array($this, 'cf_search_distinct' ));
        }
        
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
        
        /*
         * This function provides a READ ONLY access to attachments by Administrator & AOL Manager user roles. 
         * There is an article on internet with FALSE claim of plugin vulnerability in this function,
         * However the author of the article failed to understand that this function only provides a READ ONLY access to Attachments to internal users only e.g. Administrator.
         */
        function output_attachment(){
            if( isset($_REQUEST['aol_attachment']) AND current_user_can('read_application') ){
                
                //the file you want to send
                $path = urldecode( aol_crypt( ($_REQUEST['aol_attachment']), 'd') );
                // the file name of the download, change this if needed
                $public_name = basename($path);
                $mime_type = wp_check_filetype($path);

                // send the headers
                header("Content-Disposition: attachment; filename=$public_name;");
                header("Content-Type: ".$mime_type['type']);
                header('Content-Length: ' . filesize($path));

                if( !function_exists('finfo_open') ){
                    echo file_get_contents($path); 
                    exit;
                }

                // get the file's mime type to send the correct content type header
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $path);

                // stream the file
                $fp = fopen($path, 'rb');
                $result = fpassthru($fp);
                
                //Revert to File load method if File stream method fails
                if( $result == FALSE ) echo file_get_contents($path); 
                exit;
            }
        }        
    }
    
    class Applyonline_Ads extends Applyonline_Admin{
        function __construct() {
            //add_action('post_submitbox_misc_actions', array($this, 'aol_ad_options'));//optional
            add_action( 'add_meta_boxes', array($this, 'aol_meta_boxes'),1 );
            add_filter( 'manage_aol_ad_posts_columns', array ( $this, 'ads_extra_columns' ) );
            add_action( 'manage_aol_ad_posts_custom_column', array( $this, 'ads_extra_columns_values' ), 10, 2 );
        }
        
    public function aol_ad_options($post){
        $types = get_aol_ad_types();
        if( !in_array($post->post_type, $types)) return;
        
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
        ob_start(); ?>
        <div class="aol-ad-closing aol-meta">
            <?php do_action('aol_ad_close_before', $post); ?>
            <fieldset class="misc-pub-section curtime misc-pub-curtime aol-ad-options">
                <legend id="ad-closing"><strong><?php echo __('Expires on', 'ApplyOnline'); ?></strong></legend>
                <input type="text" placeholder="<?php _e('Date'); ?>" name="_aol_ad_closing_date" class="datepicker <?php echo $closed_class; ?>" value="<?php echo $date; ?>" />
                <input type="time" placeholder="<?php _e('Time in 24hour format', 'ApplyOnline'); ?>" name="_aol_ad_closing_time" class="datetimepicker" value="<?php echo $time; ?>" />
                    <p><i><?php _e('Leave empty to never close this ad.', 'ApplyOnline') ?></i></p>
                <p><b><?php _e('Format', 'ApplyOnline'); ?>:</b><i> dd-mm-yyyy</i><br/><b><?php _e('Example', 'WordPress'); ?>:</b> <i><?php echo current_time('j-m-Y'); ?></i><br/></p>
                <p class="when-expires"><b><?php _e('When Expires', 'ApplyOnline'); ?>:</b><br /> <label for="hide_form" style="display: inline-block"><input type="radio" id="hide_form" name="_aol_ad_close_type" value="form" <?php echo $close_form; ?> /><?php _e('Hide Form', 'ApplyOnline'); ?></label> &nbsp; &nbsp; <label for="hide_ad" style="display: inline-block"><input type="radio" id="hide_ad" name="_aol_ad_close_type" value="ad" <?php echo $close_ad; ?> /><?php _e('Hide Ad', 'ApplyOnline'); ?></label></p>
            </fieldset>
        </div>
        <?php 
        echo ob_get_clean();
        $this->aol_metas($post);
    }

        /*
         * Generates shortcode and php code for the form.
         */
        function aol_metas($post){
            $types = get_aol_ad_types();
            if(!in_array($post->post_type, $types)) return;
            
            echo '<div class="misc-pub-section aol-meta">';
            do_action('aol_metabox_before', $post);
            echo '<p><label for="ad-shortcode">'.__('Ad shortcode','ApplyOnline').'</label><input id="ad-shortcode" type="text" value="[aol_ad id='.$post->ID.']" readonly></p>';
            echo '<p><label for="form-shortcode">'.__('Form shortcode', 'ApplyOnline').'</label><input id="form-shortcode" type="text" value="[aol_form id='.$post->ID.']" readonly></p>';
            echo '<p><a rel="permalink" title="'.__('View All Applications', 'ApplyOnline').'" href="'.  admin_url('edit.php?post_type=aol_application').'&ad='.$post->ID.'">'.__('View All Applications', 'ApplyOnline').'</a></p>';
            do_action('aol_ad_options', $post);
            do_action('aol_metabox_after', $post);
            echo '</div>';
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
                '<span class="dashicons dashicons-admin-site"></span> '.__('Ad Features', 'ApplyOnline' ),
                array($this, 'ad_features'),
                $screens,
                'advanced',
                'high'
            );
            
            add_meta_box(
                'aol_ad_options',
                '<span class="dashicons dashicons-admin-site"></span> '.__('Ad Options', 'ApplyOnline' ),
                array($this, 'aol_ad_options'),
                $screens,
                'side',
                'low'
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
                            $features[sanitize_key($key)] = aol_sanitize_array(get_post_meta($post->ID, $key, TRUE));
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
                             echo '<li>';
                                //echo '<label for="'.$key.'">'. str_replace('_',' ', $key) . '</label>';
                                if( is_array( $val) ){
                                    echo '<input type="text" id="'.$key.'-label" name="'.$key.'[label]" value="'.sanitize_text_field( $val['label'] ).'" placeholder="Label" /> &nbsp; <input type="text" id="'.$key.'-value" name="'.$key.'[value]" value="'.sanitize_text_field( $val['value'] ).'" placeholder="Value" /> &nbsp; <div class="button aol-remove">Delete</div></li>';
                                } else{
                                    echo '<input type="text" id="'.$key.'" name="'.$key.'" value="'.sanitize_text_field( $val ).'" /> &nbsp; <div class="button aol-remove">Delete</div>';
                                }   
                             echo '</li>';
                        endforeach;
                    ?>
                </ol>
            </div>
            <div class="clearfix clear"></div>
            <table id="adfeatures_form" class="alignleft">
            <tbody>
                <tr>
                    <td colspan="2">
                        &nbsp; &nbsp; &nbsp; 
                        <input type="text" id="adfeature_name" placeholder="<?php esc_html_e('Feature','ApplyOnline');?>" /> &nbsp;
                        <input type="text" id="adfeature_value" placeholder="<?php esc_html_e('Value','ApplyOnline');?>" /> &nbsp; 
                        <div class="button" id="addFeature"><?php esc_html_e('Add','ApplyOnline');?></div>
                    </td>
                </tr>
            </tbody>
            </table>
            <div class="clearfix clear"></div>
            <?php 
        }
        
        public function ads_extra_columns( $columns ){
            $columns['date'] = __( 'Published' );
            $columns['closing'] =  __( 'Closing', 'ApplyOnline' );
            return $columns;
        }
        
        public function ads_extra_columns_values($column, $post_id){
            switch ( $column ) :
                case 'closing':
                    $date = get_post_meta($post_id, '_aol_ad_closing_date', TRUE);
                    $date = empty($date) ? $date = '--': date_i18n(get_option('date_format'), $date);
                    echo apply_filters('aol_ads_table_closing_date', $date, $post_id);
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
            
            //Filter Aplications based on parent.
            add_action( 'pre_get_posts', array($this, 'applications_filter') );
            
            add_filter( 'bulk_actions-edit-aol_application', array($this, 'custom_bulk_actions') );
            add_filter( 'handle_bulk_actions-edit-aol_application', array($this, 'my_bulk_action_handler'), 10, 3 );
        }
        
        function custom_bulk_actions($actions){
            $stauses = aol_app_statuses_active();
            foreach($stauses as $key => $val){
                $actions['change_to_'.$key] = sprintf(__('Change to %s'), $val);
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
                $actions['filters'] = '<a rel="permalink" title="'.__('Filter Applications', 'ApplyOnline').'" href="'.  admin_url('edit.php?post_type=aol_application').'&ad='.$post->post_parent.$filter.'"><span class="dashicons dashicons-filter"></span></a>';
                $actions['ad'] = '<a rel="permalink" title="'.__('Edit Ad', 'ApplyOnline').'" href="'.  admin_url('post.php?action=edit').'&post='.$post->post_parent.'"><span class="dashicons dashicons-admin-tools"></span></a>';
                $actions['view'] = '<a rel="permalink" title="'.__('View Ad', 'ApplyOnline').'" target="_blank" href="'.  get_the_permalink($post->post_parent). '"><span class="dashicons dashicons-external"></span></a>';
            }
            elseif( in_array($post->post_type, $types) ){
                $actions['test'] = '<a rel="permalink" title="'.__('View All Applications', 'ApplyOnline').'" href="'.  admin_url('edit.php?post_type=aol_application').'&ad='.$post->ID.'">'.__('Applications', 'ApplyOnline').'</a>';
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
                    <h3>#<?php echo $post->ID.' - '.$post->post_title; ?></h3><hr />
                        <?php 
                        /*
                        _aol_attachment feature has been obsolete since version 1.4, It is now being treated as Post Meta.
                        if ( in_array ( '_aol_attachment', $keys ) ):
                            $files = get_post_meta ( $post->ID, '_aol_attachment', true );
                            ?>
                        &nbsp; &nbsp; <small><a href="<?php echo esc_url(get_post_meta ( $post->ID, '_aol_attachment', true )); ?>" target="_blank" ><?php echo __( 'Attachment' , 'ApplyOnline' );?></a></small>
                        <?php 
                        endif; 
                         * 
                         */
                        ?>
                    <?php do_action('aol_before_application', $post); ?>
                    <table class="widefat striped">
                        <?php
                        $rows = aol_application_data($post);
                        foreach ( $rows as $row ):
                                echo '<tr><td>' . $row['label'] . '</td><td>' . $row['value'] . '</td></tr>';
                        endforeach;;
                        ?>
                    </table>
                    <?php do_action('aol_after_application', $post); ?>
                </div>
                <?php
            endif;
        }        
        
        function aol_meta_boxes(){
            add_meta_box(
                'aol_application',
                __( 'Application Detail', 'ApplyOnline' ),
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
                    <p class="post-attributes-label-wrapper"><a href="<?php admin_url(); ?>?aol_page=print&id=<?php echo $post->ID; ?>" class="button button-secondary button-large" target="_blank"><?php esc_html_e('Print Application','ApplyOnline');?></a></p>
                    <?php 
                    do_action('aol_app_updatebox_after');
                    if(current_user_can('delete_applications')){
                    ?>
                        <p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="parent_id"><?php esc_html_e('Application Status','ApplyOnline');?></label></p>
                        <select name="aol_tag">
                            <?php
                            foreach($stauses as $key => $val){
                                $selected = ( $key == $post_terms[0]->slug ) ? 'selected' : NULL;
                                echo '<option value="'.$key.'" '.$selected.'>'.__($val, 'ApplyOnline').'</option>';
                            }
                            ?>
                        </select>
                <?php } ?>
                </div>
                <div id="major-publishing-actions">
                    <div id="delete-action">
                    <a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php esc_html_e('Move to Trash','ApplyOnline');?></a></div>

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
                ){
                
                $ad_id = (int)$_GET['id']; //Sanitize ad id
                
                $post = get_post( $ad_id );
                $GLOBALS['post'] = $post; //Support AOL Tracker plugin.
                $parent = get_post($post->post_parent);
                ?>
                <!DOCTYPE html>
                <html <?php language_attributes(); ?>>
                    <head>
                        <meta charset="<?php bloginfo( 'charset' ); ?>" />
                        <link rel="profile" href="http://gmpg.org/xfn/11" />
                        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
                        <title><?php esc_html_e('Application','ApplyOnline');?> <?php echo $ad_id; ?> - <?php esc_html_e('Apply online','ApplyOnline');?></title>
                        <?php /*End of WP official headers*/ ?>
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <meta name="robots" content="noindex,nofollow">
                        <link rel='stylesheet' id='single-style-css'  href='<?php echo plugin_dir_url(__FILE__); ?>css/print.css?ver=<?php echo $this->version; ?>' type='text/css' media='all' />
                    </head>
                <body class="body wpinv print">
                    <div class="row top-bar no-print">
                        <div class="container">
                            <div class="col-xs-6">
                                <a class="btn btn-primary btn-sm" onclick="window.print();" href="javascript:void(0)"> <?php esc_html_e('Print Application','ApplyOnline');?></a>
                            </div>
                        </div>
                    </div>
                    <div class="container wrap">
                        <htmlpageheader name="pdf-header">
                            <div class="row header">
                                <div class="col-md-9 business">
                                    #<?php echo $ad_id; ?>
                                    <h3><?php echo $post->post_title; ?></h3>
                                    <?php echo $post->post_date; ?>
                                    <?php add_action('aol_print_header_left', $post); ?>
                                </div>

                                <div class="col-md-3">
                                     <?php esc_html_e('Application','ApplyOnline');?>
                                    <h3><?php bloginfo('name'); ?></h3>
                                    <?php add_action('aol_print_header_right', $post); ?>
                                </div>
                            </div>
                        </htmlpageheader>
                        <?php do_action('aol_print_before_application', $post); ?>
                        <table class="table table-sm table-bordered table-responsive">
                            <tbody>
                                <?php 
                                    $rows = aol_application_data($post);
                                    foreach ( $rows as $row ):
                                            echo '<tr><td>' . $row['label'] . '</td><td>' . $row['value'] . '</td></tr>';
                                    endforeach;
                                    ?>
                            </tbody>
                        </table>
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
                &nbsp; <select id="aol-apps-table-search"><option><?php _e('Filter Applications', 'ApplyOnline'); ?></option></select>
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
                'title'    => __( 'Ad Title', 'ApplyOnline' ),
                'qview'      => NULL,
                'applicant'=> __( 'Applicant', 'ApplyOnline' ),
                'taxonomy' => __( 'Status', 'ApplyOnline' ),
                'date'     => __( 'Date', 'ApplyOnline' ),
            );
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
                 case 'qview' :
                     add_thickbox();
                     $url = add_query_arg( array(
                        'action'    => 'aol_modal_box',
                        'app_id'   => $post_id,
                        'TB_iframe' => 'true',
                    ), admin_url( 'admin.php' ) );

                    echo '<a href="' . $url . '" class="thickbox" title="'. __('Quick View', 'ApplyOnline').'"><span class="dashicons dashicons-visibility"></span></a>';
                 break;
                case 'applicant' :
                    if($name === FALSE):
                        $applicant_name = __('Undefined', 'ApplyOnline');
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
                    echo $applicant_name; 
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
                                    esc_html( sanitize_term_field( 'name', __($status_name, 'ApplyOnline'), $term->term_id, 'aol_application_status', 'display' ) )
                            );
                        }
                        echo join( ', ', $out );
                    }/* If no terms were found, output a default message. */ else {
                        _e( 'Undefined' , 'ApplyOnline');
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
            define( 'IFRAME_REQUEST', true );
            iframe_header();
            $ad_id  = !empty( $_GET['app_id'] ) ? $_GET['app_id'] : '';
            $post = get_post( $ad_id );
            
            $this->aol_application_post_editor($post);
            iframe_footer();
            exit;
        }

        function application_date_column($status, $post ){
            if($post->post_type == 'aol_application') $status = __('Received');
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
            add_action( 'add_meta_boxes', array($this, 'aol_meta_boxes'),1 );
            
            /*Ajax Calls*/
            add_action("wp_ajax_aol_template_render", array($this, "template_form_callback"));
            add_action("wp_ajax_aol_ad_form_render", array($this, "aol_ad_form_render"));
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
                '<span class="dashicons dashicons-admin-site"></span> '.__('Application Form Builder', 'ApplyOnline' ),
                array($this, 'application_form_fields'),
                $screens,
                'advanced',
                'high'
            );
                        
            /*
            add_meta_box(
                'aol_form_builder',
                __( 'New Application Form Builder', 'ApplyOnline' ),
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
                'text'=> esc_html__('Text Field','ApplyOnline'),
                'number'=> esc_html__('Number Field','ApplyOnline'),
                'text_area'=>esc_html__('Text Area','ApplyOnline'),
                'email'=> esc_html__('E Mail Field','ApplyOnline'),
                'date'=>esc_html__('Date Field','ApplyOnline'),
                'checkbox'=>esc_html__('Check Boxes','ApplyOnline'),
                'radio'=> esc_html__('Radio Buttons','ApplyOnline'),
                'dropdown'=>esc_html__('Dropdown Options','ApplyOnline'), 
                'file'=>esc_html__('Attachment Field','ApplyOnline'),
                //'seprator' => 'Seprator', //Deprecated since 1.9.6. Need to be fixed for older versions.
                'separator' => esc_html__('Separator','ApplyOnline'),
                'paragraph' => esc_html__('Paragraph','ApplyOnline'),
                //'url' => esc_html__('URL','ApplyOnline'),
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
                //'url' => 'dashicons-admin-links'
                );
                if((float)get_bloginfo('version') < 5) $icons['file'] = 'dashicons-admin-links';
            $icon   = '<i class="dashicons '.$icons[$id].' aol_fields" data-id="'.$id.'"></i>';
            return $icon;
        }

        public function application_fields_generator($app_fields, $temp = NULL){
            add_thickbox();
            $adapp_form_generator = empty($temp) ? 'adapp_form_fields' : 'adapp_generator_'.$temp
            ?>
            <a href="#TB_inline?width=700&height=550&inlineId=<?php echo $adapp_form_generator; ?>" class="thickbox textfield-poup" title="<?php _e('Select a Type', 'ApplyOnline'); ?>">
                <button type="button" class="button aol-add" ><span class="dashicons dashicons-plus-alt"></span> <?php _e('Add Field', 'ApplyOnline'); ?> </button>
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
                                <td><?php _e('TextField', 'ApplyOnline'); ?></td>
                                <td><?php _e('TextArea', 'ApplyOnline'); ?></td>
                                <td><?php _e('Number', 'ApplyOnline'); ?></td>
                                <td><?php _e('Email', 'ApplyOnline'); ?></td>
                                <td><?php _e('Date', 'ApplyOnline'); ?></td>
                                <td><?php _e('CheckBox', 'ApplyOnline'); ?></td>
                                <td><?php _e('RadioBox', 'ApplyOnline'); ?></td>
                                <td><?php _e('DropDown', 'ApplyOnline'); ?></td>
                                <td><?php _e('Attachment', 'ApplyOnline'); ?></td>
                                <td><?php _e('Separator', 'ApplyOnline'); ?></td>
                                <td><?php _e('Paragraph', 'ApplyOnline'); ?></td>
                                <!--<td><?php _e('URL', 'ApplyOnline'); ?></td>-->
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="aol_new_form" class="aol_form" style="display:none;">
                    <input type="hidden" name="aol_type" value="">
                    <div class="aol_uid"><label for="adapp_uid">*<?php _e('Unique ID', 'ApplyOnline') ?></label><input class="aol-form-field adapp_uid" type="text" id="adapp_uid" ></div>
                    <div><label for="adapp_name">*<?php _e('Label', 'ApplyOnline') ?></label><input class="aol-form-field adapp_name" type="text" id="adapp_name" ></div>
                    <div class="aol_help_text aol_add_field"><label for="adapp_field_help"><?php _e('Help Text', 'ApplyOnline') ?></label><input class="aol-form-field adapp_field_help" id="adapp_field_help" type="text" /></div>
                    <div class="aol_text aol_add_field"><label for="adapp_text"><?php _e('Text', 'ApplyOnline') ?></label><textarea class="aol-form-field adapp_text" id="adapp_text" type="text" ></textarea><p class="description"><?php _e('To generate a link, use shortcode [link href="https://google.com" title="My Link Title"]', 'ApplyOnline'); ?></p></div>
                    <div class="aol_text_height aol_add_field"><label fpr="adapp_text_height"><?php _e('Fixed Height', 'ApplyOnline'); ?></label><input class="aol-form-field adapp_text_height" id="adapp_text_height"  type="number" value="0" />px</div>
                    <div class="aol_placeholder aol_add_field"><label for="adapp_placeholder"><?php _e('Place Holder', 'ApplyOnline') ?></label><input class="aol-form-field adapp_placeholder" type="text" id="adapp_placeholder" ></div>
    <!--            <div class="aol_value aol_add_field"><label><?php _e('Defult value', 'ApplyOnline') ?></label><input type="text" id="adapp_value" placeholder="<?php _e('Defult value') ?>" ></div>-->
                    <div class="aol_form_options aol_options aol_add_field"><label for="adapp_field_options"><?php _e('Options', 'ApplyOnline') ?></label><input id="adapp_field_options" class="adapp_field_options" type="text"  placeholder="<?php _e('Option 1, Option 2, Option 3', 'ApplyOnline'); ?>" ></div>
                    <div class="aol_class"><label for="adapp_class"><?php _e('Classes', 'ApplyOnline') ?></label><input type="text" id="adapp_class" class="aol-form-field adapp_class" ></div>
                    <div class="aol_file_types"><label for="adapp_file_types">*<?php _e('Allowed Types', 'ApplyOnline') ?></label><input type="text" id="adapp_file_types" class="aol-form-field adapp_file_types" value="<?php echo get_option("aol_allowed_file_types"); ?>" ><p class="description"><?php _e('Comma seperated values', 'ApplyOnline'); ?></p></div>
                    <div class="aol_file_max_size"><label for="aol_file_max_size">*<?php _e('Max Size Limit', 'ApplyOnline') ?></label><input type="number" id="adapp_file_max_size" class="adapp_file_max_size" value="<?php echo get_option('aol_form_max_upload_size'); ?>" placeholder="<?php echo floor(wp_max_upload_size()/1000000); ?> " >MB</div>
                    <div class="aol_limit aol_add_field"><label for="adapp_limit"><?php _e('Charcter Limit', 'ApplyOnline') ?></label><input id="adapp_limit" class="adapp_limit" type="number" min="1"  placeholder="<?php _e('No Limit', 'ApplyOnline'); ?>" ></div>
                    <div class="aol_preselect aol_add_field"><label for="aol_preselect"><?php _e('Preselect', 'ApplyOnline');?></label><input class="required_preselect adapp_preselect" type="checkbox" id="aol_preselect" checked value="1" /><i class="description"><?php _e('Default first field selection.', 'ApplyOnline'); ?></i> </div>
                    <div class="aol_notification aol_add_field"><label for="aol_notification"><?php _e('Notify This Email', 'ApplyOnline');?></label><input class="aol_checkbox adapp_notification" type="checkbox" id="aol_notification" value="0" /> </div>
                    <div class="aol_required aol_add_field"><label for="aol_required"><?php _e('Required Field', 'ApplyOnline');?></label><input class="aol_checkbox adapp_required" type="checkbox" id="aol_required" value="0" /> </div>
                    <!-- <div class="aol_orientation aol_add_field"><label><?php _e('Orientation', 'ApplyOnline');?></label><label><input class="required_option" type="radio" id="aol_required" checked value="0" /> Horizontal</label> &nbsp; <label><input class="required_option" type="radio" id="aol_required" value="0" />Vertical</label></div> -->
                    <?php do_action('aol_after_admin_form_fields'); ?>
                    <p class="description"><?php _e('Fields with (*) are compulsory.', 'ApplyOnline'); ?></p>
                    <button type="button" class="button aol-add button-primary addField <?php echo $temp; ?>" data-temp="<?php echo $temp; ?>"><span class="dashicons dashicons-plus-alt"></span> <?php _e('Add Field', 'ApplyOnline'); ?> </button>
                </div>
            </div>
            
        <?php
        }
        
        function aol_ad_form_render(){
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
                
                $key = esc_attr($key); //Sanitizing key.
                $label = isset($val['label']) ? sanitize_text_field($val['label']) : str_replace('_',' ',substr($key,9)); //
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
                    echo '<td><span class="dashicons dashicons-menu"></span> <label for="'.$key.'">'.$label.'</label></td>';
                    echo '<td>';
                        empty($tempid) ? do_action('aol_after_form_field', $key) :  do_action('aol_after_application_template_field', $tempid, $key);
                        echo '<div class="aol-edit-form"><a href="#TB_inline?&inlineId='.$key.'" title="'.$types_names[$val['type']].'" class="thickbox dashicons dashicons-edit"></a><span class="dashicons dashicons-no aol-remove" title="'.__('Delete', 'ApplyOnline').'" ></span></div>';
                        $this->row_popup($key, $val, $tempid);
                    echo '</td>';
                echo '</tr>';
                //}
            endforeach;
        }
        
        public function row_popup($key, $val, $template = NULL){ 
            $label = isset($val['label']) ? sanitize_text_field($val['label']) : str_replace('_',' ',substr($key,9));
            $description = isset($val['description']) ? sanitize_text_field($val['description']) : NULL; //
            $text = isset($val['text']) ? sanitize_textarea_field($val['text']) : $description; //
            $height = (isset($val['height']) and $val['height'] > 0) ? (int)($val['height']) : 0; //
            $placeholder = isset($val['placeholder']) ? ($val['placeholder']) : NULL; 
            $limit = isset($val['limit']) ? ($val['limit']) : NULL; 
            $class = !empty($val['class']) ? ($val['class']) : NULL;
            $types = !empty($val['allowed_file_types']) ? ($val['allowed_file_types']) : get_option("aol_allowed_file_types", ALLOWED_FILE_TYPES);
            $size = !empty($val['allowed_size']) ? ($val['allowed_size']) : get_option('aol_upload_max_size');
            $selection = !empty($val['preselect']) && $val['preselect'] === '1' ? 'checked' : ''; 
           
            $name = empty($template) ? $key : $template."[$key]";

            $required = isset( $val['required'] ) ? (int)$val['required'] : 0;
            $checked    = !empty( $val['required'] ) && $val['required'] == 1 ? 'checked' : '';
            
            $notify    = !empty( $val['notify'] ) && $val['notify'] == 1 ? 'checked' : '';
            
            echo '<div style="display:none;" id='.$key.'><div class="aol_form" data-id="'.$key.'">';
            echo '<div class="form-group"><label>'.$this->aol_fields_icons($val['type']).'</label><span></span></div>';
            //echo '<div><label>'.__('Type', 'ApplyOnline').'</label><select disabled class="adapp_field_type" name="'.$key.'[type]">'.$fields.'</select></div>';
            echo '<div class="form-group"><label>*'.__('Unique ID', 'ApplyOnline').'</label><input type="text" disabled value="'.str_replace('_aol_app_', '', $key).'" /></div>';
            echo '<div class="form-group"><label for="'.$name.'-label">*'.__('Label', 'ApplyOnline').'</label><input id="'.$name.'-label" class="adapp_label" type="text" name="'.$name.'[label]" value="'.$label.'" /></div>';

            if($val['type'] == 'paragraph'){
                echo '<div class="form-group form-group-paragraph"><label>'.__('Text', 'ApplyOnline').'</label><textarea class="aol-form-field" name="'.$name.'[text]" >'.$text.'</textarea><p class="description">'.__('To generate a link, use shortcode [link href="https://google.com" title="My Link Title"]').'</p></div>';
                echo '<div class="form-group"><label for="'.$name.'-height">'.__('Fixed Height', 'ApplyOnline').'</label><input id="'.$name.'-height" class="aol-form-field" type="number" name="'.$name.'[height]" value="'.$height.'" />px</div>';
            } else{
                echo '<div class="form-group"><label for="'.$name.'-desc">'.__('Help Text', 'ApplyOnline').'</label><input id="'.$name.'-desc" type="text" name="'.$name.'[description]" value="'.$description.'" /></div>';
            }

            echo '<input type="hidden" name="'.$name.'[type]" value="'.$val['type'].'">';
            if(in_array($val['type'], array('text_area','text','number','email', 'url'))):
                echo '<div><label for="'.$name.'-placeholder">'.__('Placeholder', 'ApplyOnline').'</label><input id="'.$name.'-placeholder" type="text" name="'.$name.'[placeholder]" value="'.$placeholder.'" /></div>';
            endif;
            if(in_array($val['type'], array('checkbox','dropdown','radio'))):
                echo '<div class="aol_options_check"><label>'.__('Options', 'ApplyOnline').'</label><input type="text" name="'.$name.'[options]" value="'.sanitize_text_field($val['options']).'" placeholder="'.__('Option1, Option2, Option3', 'ApplyOnline').'" /></div>';
            endif;
            echo '<div><label for="'.$name.'-classes">'.__('Classes', 'ApplyOnline').'</label><input id="'.$name.'-classes" type="text" name="'.$name.'[class]" value="'.$class.'" /></div>';
            if( $val['type'] === 'radio' ){
                echo '<div><label for="'.$name.'-preselect">'.__('Preselect', 'ApplyOnline').'</label><input id="'.$name.'-preselect" type="checkbox" name="'.$name.'[preselect]" value="1" '.$selection.'><i class="description">'.__('Default first field selection.', 'ApplyOnline').'</i></div>';
            }

            if(in_array($val['type'], array('text_area','text'))):
                echo '<div><label for="'.$name.'-limit">'.__('Charchter Limit', 'ApplyOnline').'</label><input id="'.$name.'-limit" type="number" placeholder="No limit" name="'.$name.'[limit]" value="'.$limit.'" /></div>';
            endif;

            if( $val['type'] === 'email' ):
                echo '<div><label for="'.$name.'-notification">'.__('Notify This Email', 'ApplyOnline').'</label><input id="'.$name.'-notification" class="adpp_notification" type="checkbox" '.$notify.' name="'.$name.'[notify]" value="1" /></div>';
            endif;
            if(in_array($val['type'], array('checkbox','dropdown','radio','text_area','text','number','email','date','file'))):
                if($val['type'] == 'file'){
                    echo '<div><label for="'.$name.'-file-types">'.__('Allowed File Types', 'ApplyOnline').'</label><input id="'.$name.'-file-types" class="aol-form-field file_types_option" type="text" name="'.$name.'[allowed_file_types]" value="'.$types.'" /><p class="description">Comma separated values</p></div>';
                    echo '<div><label for="'.$name.'_file_max_size">'.__('*Max Size Limit', 'ApplyOnline').'</label><input id="'.$name.'_file_max_size" class="aol-form-field file_max_size" type="number" name="'.$name.'[file_max_size]" value="'.$size.'" /></div>';
                }
                echo '<div><label for="'.$name.'-required">'.__('Required Field', 'ApplyOnline').'</label><input id="'.$name.'-required" class="required_option" type="checkbox" '.$checked.' name="'.$name.'[required]" value="1" />MB</div>';
            endif;
            echo '<p class="description">'.__('Fields with (*) are compulsory.', 'ApplyOnline').'</p>';
            //echo '<div class="button-primary button-required '.$req_class.'">'.__('Required', 'ApplyOnline').'</div> </div>';
            //echo '<div><label>'.__('Type', 'ApplyOnline').'</label><select disabled class="adapp_field_type" name="'.$key.'[type]">'.$fields.'</select></div>';

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
                    if(empty($fields)){
                        $fields = get_option('aol_default_fields', array());
                        if(empty($fields)){
                            $fields = get_option('aol_form_templates', array());
                            $templates = TRUE;
                            $keys = array_keys($fields);
                            $options = null;
                            foreach($keys as $key){ $options.= '<option value="'.$key.'">'.$fields[$key]['templateName'].'</option>'; }
                            ?>
                                <thead>
                                    <tr>
                                        <td colspan="2">
                                            <select id="aol_template_loader">
                                                <option class="aol_default_option"><?php esc_html_e('Select a Form Template','ApplyOnline');?></option>
                                                <?php echo $options; ?>
                                            </select> &nbsp; &nbsp; 
                                            <select id="aol_import_loader" class="aol-import-form">
                                                <option value="" class="aol_default_option"><?php esc_html_e('Import an existing ad','ApplyOnline');?></option>
                                                <?php echo $options; ?>
                                            </select> &nbsp; &nbsp; 
                                            <span class="template_loading_status"></span>
                                        </td>
                                        <td></td>
                                    </tr>
                                </thead>
                            <?php
                        }
                    }
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
            $links['settings'] = '<a href="'.  admin_url().'?page=aol-settings">'.__('Settings', 'ApplyOnline').'</a>';
	}
	
	return $links;
    }

    public function sub_menus(){
        add_menu_page( __('Settings', 'ApplyOnline'), _x('Apply Online', 'Admin Menu', 'ApplyOnline'), 'edit_applications', 'aol-settings', array($this, 'settings_page_callback'), 'dashicons-admin-site',31 );
        add_submenu_page('aol-settings', __('Settings', 'ApplyOnline'), __('Settings', 'ApplyOnline'), 'delete_others_ads', 'aol-settings');
        $filters = aol_ad_filters();
        foreach($filters as $key => $val){
            add_submenu_page( 'aol-settings', '', sprintf(__('%s Filter', 'ApplyOnline'), $val['plural']), 'delete_others_ads', "edit-tags.php?taxonomy=aol_ad_".sanitize_key($key)."&post_type=aol_ad", null );            
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
    
    public function settings_page_callback(){
        $this->save_settings();
        $tabs = json_decode(json_encode($this->settings_api()), FALSE);
        ob_start();
        ?>
            <div class="wrap aol-settings">
                <h2>
                    <?php echo _x('Apply Online', 'admin', 'ApplyOnline'); ?> 
                    <small class="wp-caption alignright"><i> <?php echo __('version ', 'ApplyOnline').$this->version; ?></i></small>
                </h2>
                <span class="alignright" style="display: none">
                    <a target="_blank" title="Love" class="aol-heart" href="https://wordpress.org/plugins/apply-online/#reviews"><span class="dashicons dashicons-heart"></span></a> &nbsp;
                    <a target="_blank" title="Support" class="aol-help" href="https://wordpress.org/support/plugin/apply-online/"><span class="dashicons dashicons-format-chat"></span></a> &nbsp;
                    <a target="_blank" title="Stats" class="aol-stats" href="https://wordpress.org/plugins/apply-online/advanced/"><span class="dashicons dashicons-chart-pie"></span></a> &nbsp;
                    <a target="_blank" title="Shop" class="aol-shop" href="https://wpreloaded.com/shop/"><span class="dashicons dashicons-cart"></span></a> &nbsp;
                </span>
                <h2 class="nav-tab-wrapper aol-primary">
                    <?php 
                        foreach($tabs as $tab){
                            if( isset($tab->capability) AND !current_user_can($tab->capability) ) continue;
                            empty($tab->href) ? $href = null : $href = 'href="'.$tab->href.'" target="_blank"';
                            isset($tab->classes) ? $classes = $tab->classes : $classes = null;
                            echo '<a class="nav-tab '.$classes.'" data-id="'.$tab->id.'" '.$href.'>'.$tab->name.'</a>';
                        }
                    ?>
                </h2>
                <?php 
                    foreach($tabs as $tab){
                        $func = 'tab_'.$tab->id;
                        echo '<div class="tab-data wrap" id="'.$tab->id.'">';
                        if(isset($tab->name)) echo '<h3>'.$tab->name.'</h3>';
                        if(isset($tab->desc)) echo '<p>'.$tab->desc.'</p>';
                        
                        //Return $output or related method of the same variable name.
                        if(isset($tab->callback)){
                            echo $tab->callback;
                        } elseif(isset($tab->output)){
                            echo $tab->output;
                        } else{
                            echo $this->$func();
                        }
                         
                        //echo isset($tab->output) ? $tab->output : $this->$func(); //Return $output or related method of the same variable name.
                        echo '</div>';
                    }
                ?>
            </div>
            <style>
                h3{margin-bottom: 5px;}
                .nav-tab{cursor: pointer}
                .tab-data, .templateForm{display: none;}
            </style>
        <?php
        return ob_get_flush();
    }           

    public function registers_settings(){
        register_setting( 'aol_settings_group', 'aol_recipients_emails', array( 'sanitize_callback' => 'sanitize_textarea_field') );
        register_setting( 'aol_settings_group', 'aol_application_success_alert', array( 'sanitize_callback' => 'sanitize_textarea_field') );
        register_setting( 'aol_settings_group', 'aol_shortcode_readmore', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_application_submit_button', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_required_fields_notice', array( 'sanitize_callback' => 'sanitize_text_field'));
        register_setting( 'aol_settings_group', 'aol_thankyou_page', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_upload_path', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_form_heading', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_features_title', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_slug', 'sanitize_title', array( 'sanitize_callback' => 'sanitize_text_field') ); 
        register_setting( 'aol_settings_group', 'aol_upload_max_size', array('type' => 'integer', 'default' => 1, 'sanitize_callback' => 'intval') );
        register_setting( 'aol_settings_group', 'aol_upload_folder', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_allowed_file_types', array('sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_application_close_message', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_ad_author_notification', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_multistep', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_nonce_is_active', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_success_mail_message', array( 'sanitize_callback' => 'sanitize_textarea_field') );
        register_setting( 'aol_settings_group', 'aol_success_mail_subject', array( 'sanitize_callback' => 'sanitize_text_field') );
        register_setting( 'aol_settings_group', 'aol_not_found_alert', array( 'sanitize_callback' => 'sanitize_text_field') );
        
        register_setting( 'aol_filters', 'aol_ad_filters', array( 'sanitize_callback' => 'aol_sanitize_array') );
        //register_setting( 'aol_filters', 'aol_ad_filters');
        
        //Registering settings for aol_settings API option.
        $settings = get_aol_settings();
        foreach($settings as $setting){
            //$key = get_option($setting['key']);
            register_setting( 'aol_settings_group', $setting['key'], array( 'sanitize_callback' => 'sanitize_text_field') );
        }
        
        register_setting( 'aol_ad_template', 'aol_default_fields');//Depreciated
        register_setting( 'aol_ad_template', 'aol_form_templates');
        register_setting( 'aol_ads', 'aol_ad_types', array('sanitize_callback' => 'aol_sanitize_array') );
        //register_setting( 'aol_ads', 'aol_ad_filters', array('sanitize_callback' => 'aol_array_check') );
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
            'rewrite' => array('slug' => sanitize_key($plural)),
            ));
    }
    
    function refresh_types_permalink($old, $new, $option){
        wp_cache_delete ( 'alloptions', 'options' );
        foreach($new as $cpt => $val){
            $this->register_ad_types_for_flushing($cpt, $val['plural']);
        }
        flush_rewrite_rules();
    }
    
    function settings_api(){
        $tabs = array(
                'general' => array(
                    'id'        => 'general',
                    'name'      => __( 'General' ,'ApplyOnline' ),
                    'desc'      => __( 'Global settings for the plugin.', 'ApplyOnline' ),
                    'href'      => null,
                    'classes'     => 'nav-tab-active',
                ),/*
                'ui' => array(
                    'id'        => 'ui',
                    'name'      => __('User Interface' ,'ApplyOnline'),
                    'desc'      => __( 'Front-end User Iterface Manager', 'ApplyOnline' ),
                    'href'      => null,
                ),
                 * 
                 */
                'template' => array(
                    'id'        => 'template',
                    'name'      => __('Template' ,'ApplyOnline'),
                    'desc'      => __( 'Application form templates for new ads.', 'ApplyOnline' ),
                    'href'      => null,
                ),
                'applications' => array(
                    'id'        => 'applications',
                    'name'      => __('Applications' ,'ApplyOnline'),
                    'desc'      => __( 'This section is intended for received applications.', 'ApplyOnline' ),
                    'href'      => null,
                ),
                'filters' => array(
                    'id'        => 'filters',
                    'name'      => __('Ad Filters' ,'ApplyOnline'),
                    'desc'      => __( 'Display Filters in [aol] shortcode outupt.', 'ApplyOnline' ),
                    'href'      => null,
                ),
                'types' => array(
                    'id'        => 'types',
                    'name'      => __('Ad Types' ,'ApplyOnline'),
                    'desc'      => __( 'Define different types of ads e.g. Careers, Classes, Memberships. These types will appear under All Ads section.', 'ApplyOnline' ),
                    'href'      => null,
                ),
        );
        $tabs = apply_filters('aol_settings_tabs', $tabs);
        $tabs['faqs'] = array(
                    'id'        => 'faqs',
                    'name'      => __('How Tos' ,'ApplyOnline'),
                    'desc'      => __('Frequently Asked Questions.' ,'ApplyOnline'),
                    'href'      => null,
                );
        $tabs['extend'] = array(
                    'id'        => 'extend',
                    'name'      => __('Extend' ,'ApplyOnline'),
                    'desc'      => __('Extend Plugin' ,'ApplyOnline'),
                    'href'      => 'https://wpreloaded.com/shop/',
                    'capability' => 'manage_options'
                );
        $tabs = apply_filters('aol_settings_all_tabs', $tabs);
        return $tabs;
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
                    $message="Hi there,\n\n"
                        ."Thank you for showing your interest in the ad: [title]. Your application with id [id] has been received. We will review your application and contact you if required.\n\n"
                        .sprintf(__('Team %s'), get_bloginfo('name'))."\n"
                        .site_url()."\n"
                        ."Please do not reply to this system generated message.";
                ?>
                    <!--
                    <tr>
                        <th><label for="aol_multistep"><?php _e('Multistep application forms', 'ApplyOnline'); ?></label></th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="aol_multistep" <?php echo sanitize_key(get_option('aol_multistep')) ? 'checked="checked"':Null; ?> >
                                <span class="slider"></span>
                             </label>
                            <p class="description"></p>
                        </td>
                    </tr>
                    -->
                    <tr>
                        <th><label for="aol_nonce"><?php _e('Security Nonce', 'ApplyOnline'); ?> </label></th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="aol_nonce_is_active" <?php echo sanitize_key(get_option('aol_nonce_is_active')) ? 'checked="checked"':Null; ?> >
                                <span class="slider"></span>
                             </label>
                            <p class="description"><?php _e('If you have firewall installed (e.g. WordFence) and get Session Expired error on form submissions then disabling nonce might be helpful.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_ad_author_notification"><?php _e('Email alerts for ad authors', 'ApplyOnline'); ?></label></th>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="aol_ad_author_notification" <?php echo sanitize_key(get_option('aol_ad_author_notification')) ? 'checked="checked"':Null; ?> >
                                <span class="slider"></span>
                             </label>
                            <p class="description"></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_recipients_emails"><?php _e('List of e-mails to get application alerts', 'ApplyOnline'); ?></label></th>
                        <td>
                            <textarea id="aol_recipients_emails" class="small-text code" name="aol_recipients_emails" cols="50" rows="5"><?php echo sanitize_textarea_field(get_option_fixed('aol_recipients_emails') ); ?></textarea>
                            <p class="description"> <?php _e('Just one email id in one line.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_application_success_alert"><?php _e('Application success alert', 'ApplyOnline'); ?></label></th>
                        <td>
                            <textarea class="small-text code" name="aol_application_success_alert" cols="50" rows="3" id="aol_application_success_alert"><?php echo sanitize_text_field( get_option_fixed('aol_application_success_alert' ) ); ?></textarea>
                            <p class="description"><?php _e('Use [id] for dynamic application ID.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_success_mail_subject"><?php _e('Application success email subject', 'ApplyOnline'); ?></label></th>
                        <td>
                            <textarea class="small-text code" name="aol_success_mail_subject" cols="50" rows="3" id="aol_required_fields_notice"><?php echo sanitize_text_field( get_option_fixed('aol_success_mail_subject', 'Thank you for the application' ) ); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_success_mail_message"><?php _e('Application success email message', 'ApplyOnline'); ?></label></th>
                        <td>
                            <textarea class="small-text code" name="aol_success_mail_message" cols="50" rows="10" id="aol_required_fields_notice"><?php echo sanitize_textarea_field( get_option_fixed('aol_success_mail_message', $message) ); ?></textarea>
                            <p class="description"> <?php _e('Ues [title] & [id] to add ad title & its ID number in the mail.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_required_fields_notice"><?php _e('Required form fields notice', 'ApplyOnline'); ?></label></th>
                        <td>
                            <textarea class="small-text code" name="aol_required_fields_notice" cols="50" rows="3" id="aol_required_fields_notice"><?php echo sanitize_text_field( get_option_fixed('aol_required_fields_notice', 'Fields with (*)  are compulsory.' ) ); ?></textarea>
                            <br />
                            <button class="button" id="aol_required_fields_button"><?php _e('Default Notice', 'ApplyOnline'); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="app_closed_alert"><?php _e('Closed Application alert', 'ApplyOnline'); ?></label></th>
                        <td>
                            <textarea id="app_closed_alert" class="small-text code" name="aol_application_close_message" cols="50" rows="3"><?php echo sanitize_text_field( get_option_fixed('aol_application_close_message', __('We are no longer accepting applications for this ad.', 'ApplyOnline')) ); ?></textarea>
                            <br />
                            <button id="app_closed_alert_button" class="button"><?php _e('Default Alert', 'ApplyOnline'); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="thanks-page"><?php _e('Thank you page', 'ApplyOnline'); ?></label></th>
                        <td>
                            <select id="thank-page" class="aol-select2" style="width: 330px" name="aol_thankyou_page">
                                <option value=""><?php _e('Not selected', 'ApplyOnline'); ?></option> 
                                <?php 
                                $selected = get_option('aol_thankyou_page');

                                 $pages = get_pages();
                                 foreach ( $pages as $page ) {
                                     $attr = null;
                                     if($selected == $page->ID) $attr = 'selected';

                                       $option = '<option value="' . (int)$page->ID . '" '.$attr.'>';
                                       $option .= sanitize_text_field($page->post_title);
                                       $option .= '</option>';
                                       echo $option;
                                 }
                                ?>
                           </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_form_heading"><?php _e('Application form title', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_form_heading" class="regular-text" name="aol_form_heading" value="<?php echo sanitize_text_field(get_option('aol_form_heading', 'Apply Online')); ?>">
                            <p class="description"><?php _e('Default: Apply Online', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_features_title"><?php _e('Ad features title', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_features_title" class="regular-text" name="aol_features_title" value="<?php echo sanitize_text_field(get_option('aol_features_title', 'Salient Features')); ?>">
                            <p class="description"><?php _e('Default: Salient Features', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_application_submit_button"><?php _e('Application form Submit Button', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_form_heading" class="regular-text" name="aol_application_submit_button" value="<?php echo sanitize_text_field(get_option_fixed('aol_application_submit_button', 'Submit')); ?>">
                            <p class="description"><?php _e('Default: Submit', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_not_found_alert"><?php _e('Not Found alert text', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_form_heading" class="regular-text" name="aol_not_found_alert" value="<?php echo sanitize_text_field(get_option_fixed('aol_not_found_alert', 'Sorry, we could not find what you were looking for.')); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_shortcode_readmore"><?php _e('Read More button text', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_shortcode_readmore" class="regular-text" name="aol_shortcode_readmore" value="<?php echo sanitize_text_field(get_option_fixed('aol_shortcode_readmore')); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_upload_path"><?php _e('File upload path', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_upload_path" class="regular-text" placeholder="<?php echo $aol_upload_path; ?>" name="aol_upload_path" value="<?php echo sanitize_text_field(get_option('aol_upload_path')); ?>"> <?php aol_empty_option_alert('aol_upload_path', $aol_upload_path); ?>
                            <p class="description"><?php _e("By default application attachments are saved in WordPress upload folder which is public. So, change your attachments path, preferably outside your website root, to make it private. In most cases any path before public_html directory is good.", 'ApplyOnline'); ?> <?php _e('(Delete and save settings to restore default path.)', 'ApplyOnline'); ?> </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_date_format"><?php _e('Date format for date fields', 'ApplyOnline'); ?></label></th>
                        <td>
                            <p><?php echo sprintf(__('Update format on Wordpress %sGeneral Settings%s page', 'ApplyOnline'), '<a href="'.admin_url('options-general.php#timezone_string').'" target="_blank" />', '</a>'); ?> </p>
                        </td>
                    </tr>                    
                    <tr>
                        <th><label for="aol_slug"><?php _e('Default Ads slug', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input id="aol_slug" type="text" class="regular-text" name="aol_slug" placeholder="ads" value="<?php echo sanitize_text_field(get_option_fixed('aol_slug', 'ads') ); ?>" />
                            <?php $permalink_option = get_option('permalink_structure'); if(empty($permalink_option)): ?>
                                <p><?php printf(__("This option doesn't work with Plain permalinks structure. Check %sPermalink Settings%s"), '<a href="'.admin_url('options-permalink.php').'">', '</a>'); ?></p>
                            <?php else: ?>
                                <p class="description"><?php printf(__('Current permalink is %s', 'ApplyOnline'), '<a href="'.get_post_type_archive_link('aol_ad').'" target="_blank">'.get_post_type_archive_link('aol_ad').'</a>') ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_form_max_file_size"><?php _e('Max file attachment size', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input id="aol_form_max_upload_size" max="" type="number" name="aol_upload_max_size" placeholder="1" value="<?php echo (int)get_option('aol_upload_max_size', 1); ?>" />MBs
                            <p class="description"><?php printf(__('Max limit by server is %d MBs', 'ApplyOnline'), floor(wp_max_upload_size()/1000000)); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_allowed_file_types"><?php _e('Allowed file types', 'ApplyOnline'); ?></label></th>
                        <td>
                            <textarea id="aol_allowed_file_types" name="aol_allowed_file_types" placeholder="Leave empty to apply global options" class="code" placeholder="<?php echo get_option("aol_allowed_file_types", ALLOWED_FILE_TYPES); ?>" cols="50" rows="2"><?php echo esc_textarea(get_option_fixed('aol_allowed_file_types', 'jpg,jpeg,png,doc,docx,pdf,rtf,odt,txt')); ?></textarea>
                            <p class="description"><?php printf(__('Comma separated names of file extentions. Default: $s', 'ApplyOnline'), get_option("aol_allowed_file_types", ALLOWED_FILE_TYPES)); ?></p>
                        </td>
                    </tr>
                    <?php 
                    $settings = get_aol_settings();
                    foreach ($settings as $setting){
                        //setting default values as NULL.
                        $setting = array_merge(array_fill_keys(array('type', 'key', 'secret', 'placeholder', 'value', 'label', 'helptext', 'icon', 'class'), NULL), $setting);
                        //$placeholder = ($setting['secret']==true AND !empty($setting['value'])) ? aol_crypt($setting['value'], 'd'):$setting['placeholder'];
                        //$value = $setting['secret']==true ? NULL:$setting['value'];
                        $placeholder = NULL;
                        $value      = ( isset($setting['value']) AND !empty($setting['value']) ) ? $setting['value'] : get_option($setting['key']);
                    ?>
                    <tr>
                        <th><label for="<?php echo $setting['key'] ?>"><?php echo $setting['label'] ?></label></th>
                        <td>
                            <?php 
                            switch($setting['type']):
                                case 'textarea':
                                ?>
                                <textarea class="code <?php echo $setting['class']; ?>" id="<?php echo $setting['key']; ?>" name="<?php echo $setting['key'] ?>" placeholder="<?php echo $placeholder; ?>" ><?php echo $value; ?></textarea>
                                <?php
                                    break;
                                default:
                                ?>
                                <input class="regular-text <?php echo $setting['class']; ?>" type="<?php echo $setting['type']; ?>" id="<?php echo $setting['key']; ?>" name="<?php echo $setting['key'] ?>" placeholder="<?php echo $placeholder; ?>" value="<?php echo $value; ?>" />
                            <?php endswitch; ?>
                            <?php echo isset($setting['button']) ? '<a id="'.$setting['key'].'_button" href="'.$setting['button']['link'].'" target="_blank" class="'.$setting['key'].'_button button">'.$setting['button']['title'].'</a>': NULL ; ?>
                            <p class="description">
                                <?php 
                                if(isset($setting['icon'])) echo '<span class="dashicons dashicons-'.$setting['icon'].'"></span>';
                                echo $setting['helptext']; 
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
                                //print_rich($templates);
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
                                    <div id="<?php echo $tempid; ?>" class="templateForm aolFormBuilder">
                                        <p><input type="text" class="aolTempName" name="<?php echo $tempid; ?>[templateName]" value="<?php echo sanitize_text_field($temp['templateName']); ?>" placeholder="<?php _e('Template Name', 'ApplyOnline'); ?>" /> <span class="dashicons aol-remove dashicons-trash"></span></p>
                                        <table class="aol_table widefat striped">
                                            <tbody class="app_form_fields">
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
                                                <td colspan="3"><input type="text" name="new[templateName]" placeholder="<?php _e('Template Name', 'ApplyOnline'); ?>" /></td>
                                            </tr>
                                        </thead>
                                        <tbody class="app_form_fields"></tbody>
                                    </table>
                                     <?php $this->application_fields_generator($this->app_field_types(), 'new'); ?>
                                 </div>
                    </div>  
                <hr />
                <?php submit_button(__('Save form Templates', 'ApplyOnline')); ?>
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
                    //print_rich($filters);
                    if(!empty($filters)):
                        foreach ($filters as $key => $val){
                            echo '<tr>';
                            if(!isset($val['plural'])) echo '<td><label for="filter-'. sanitize_key($key).'" ><strong>'.sanitize_text_field($key).'</strong> </label></td><td><input type="text" name="aol_ad_filters['.sanitize_key($key).'][singular]" value="'.sanitize_text_field($val).'" placeholder="Singular" /> <input type="text" name="aol_ad_filters['.sanitize_key($key).'][plural]" value="'.sanitize_text_field($val).'" placeholder="Singular" /></td>';
                            else echo '<td><label for="filter-'. sanitize_key($key).'" ><strong>'.sanitize_text_field($val['plural']).'</strong> </label></td><td><input type="text" name="aol_ad_filters['.sanitize_key($key).'][singular]" value="'.sanitize_text_field($val['singular']).'" placeholder="Singular" /> <input type="text" name="aol_ad_filters['.sanitize_key($key).'][plural]" value="'.sanitize_text_field($val['plural']).'" placeholder="Singular" /></td>';

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
                                <input type="text" id="ad_filter_singular" placeholder="<?php _e('Singular Name', 'ApplyOnline'); ?>" /> <input type="text" id="ad_filter_plural" placeholder="<?php _e('Plural Name', 'ApplyOnline'); ?>" />
                            </td>
                            <td class="left" id="newmetaleft">
                                <div class=""><div class="button aol-add" id="ad_aol_filter"><span class="dashicons dashicons-plus-alt"></span> <?php _e('Add Filter', 'ApplyOnline'); ?></div></div>
                            </td>
                        </tr>                        
                    </tfoot>
                </table>
                    </div>
            </div>  
            <!--Generator -->
            <div class="clearfix clear"></div>
            <div class="clearfix clear"></div>
            <p class="description"><b><?php _e('IMPORTANT', 'ApplyOnline'); ?></b> <i><?php _e('Filters are used to narrow down ads listing on front-end and work with [aol] shortcode only.'); ?> <?php echo sprintf(__('Saved filters are available in %sAd Types%s section.', 'ApplyOnline'), '<strong>', '</strong>'); ?></i></p>
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
                                foreach($types as $key => $type):
                                    $type['filters'] = isset($type['filters']) ? $type['filters'] : array();
                                    $key = sanitize_key($key);
                                    $count = wp_count_posts('aol_'.sanitize_key($type['singular']));
                                    echo '<li><p><a href="'.  admin_url('edit.php?post_type=aol_'.sanitize_key($type['singular'])).'">'.sanitize_text_field( $type['singular'] ) .' ('. sanitize_text_field( $type['plural'] ) .')</a></p>';
                                        echo '<p><b>'.__('Description', 'ApplyOnline').': </b><input type="text" name="aol_ad_types['.$key.'][description]" value="'.$type['description'].'" Placeholder="'.__('Not set', 'ApplyOnline').'"/></p>';
                                    echo '<p><b>'.__('Shortcode', 'ApplyOnline').': </b><input type="text" readonly value="[aol type=&quot;'.sanitize_key($type['singular']).'&quot;]" /></p>';
                                    echo '<p><b>'.__('Direct URL', 'ApplyOnline').': <a href="'.get_post_type_archive_link( 'aol_'.$key ).'" target="_blank">'.get_post_type_archive_link( 'aol_'.$key ).'</a></b></p>';
                                    echo '<input type="hidden" name="aol_ad_types['.$key.'][singular]" value="'.$type['singular'].'"/>';
                                    echo '<input type="hidden" name="aol_ad_types['.$key.'][plural]" value="'.$type['plural'].'"/>';
                                    $this->filters($type['filters'], $key);
                                    if($key != 'ad') echo ' <button class="button button-small aol-remove button-danger">'.__('Delete', 'ApplyOnline').'</button></li>';
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
                            <input type="text" id="ad_type_singular" placeholder="<?php _e('Singular e.g. Career', 'ApplyOnline'); ?>" />
                        </td>
                        <td class="left" id="plural">
                            <input type="text" id="ad_type_plural" placeholder="<?php _e('Plural e.g. Careers', 'ApplyOnline'); ?>" />
                        </td>
                        <td class="left" id="desc">
                            <input type="text" id="ad_type_description" placeholder="<?php _e('Description', 'ApplyOnline'); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class=""><div class="button" id="ad_aol_type">Add Type</div></div>
                        </td>
                    </tr>
                </tbody>
                </table>
                <div class="clearfix clear"></div>
                <p class="description"><b><?php _e('IMPORTANT', 'ApplyOnline'); ?></b> <?php printf(__('If you get 404 error on direct links, try saving this section once again.', 'ApplyOnline')); ?></p>
                <hr />
                <?php submit_button(); ?>
            <?php //wp_nonce_field( 'aol_awesome_pretty_nonce','aol_ad_type_nonce' ); ?>
        </form>     
        <?php 
    }
    
    private function filters($set_filters, $cpt){
        ?>
            <ul id="ad_filters">
                <?php
                $filters = get_option_fixed('aol_ad_filters', array() );
                
                foreach ($filters as $key => $val){
                    $checked = in_array(sanitize_key($key), $set_filters) ? 'checked' : NULL;
                    echo '<li><input id="filter-'.$cpt.'-'.$key.'" type="checkbox" name="aol_ad_types['.$cpt.'][filters][]" value="'.sanitize_key($key).'" '.$checked.'><label for="filter-'.$cpt.'-'.$key.'">'. sprintf(__('Enable %s filter', 'ApplyOnline'), sanitize_text_field($val['plural'])).'</label></li>';
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
                        $key = sanitize_key($key);
                        $val = sanitize_text_field($val);
                        $checked = in_array($key, $set_filters) ? 'checked' : NULL;
                        echo '<li><input type="hidden" name="aol_custom_statuses['.$key.']" value="'.$val.'" /><input id="filter-'.$key.'" type="checkbox" name="aol_app_statuses[]" value="'.$key.'" '.$checked.'><label for="filter-'.$key.'">'.sprintf(__('Enable %s status.', 'ApplyOnline'), $val).'</label>';
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
                           <input type="text" id="ad_status_singular" placeholder="<?php _e('Status Name', 'apply-online'); ?>" />
                       </td>
                       <td class="left" id="newmetaleft">
                           <div class=""><div class="button aol-add" id="ad_aol_status"><span class="dashicons dashicons-plus-alt"></span> <?php _e('Add Status', 'ApplyOnline'); ?></div></div>
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
                        <th><label for="aol_submit_button_classes"><?php _e('Submit Button Classes', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_submit_button_classes" class="regular-text" name="aol_submit_button_classes" value="<?php echo sanitize_text_field(get_option('aol_submit_button_classes')); ?>">
                            <p class="description"><?php _e('Extra button classes to ad theme support.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_readmore_button_classes"><?php _e('Read More Button Classes', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_readmore_button_classes" class="regular-text" name="aol_readmore_button_classes" value="<?php echo sanitize_text_field(get_option('aol_readmore_button_classes')); ?>">
                            <p class="description"><?php _e('Extra button classes to ad theme support.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_multistep_button_classes"><?php _e('Multistep Buttons Classes', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_multistep_button_classes" class="regular-text" name="aol_multistep_button_classes" value="<?php echo sanitize_text_field(get_option('aol_multistep_button_classes')); ?>">
                            <p class="description"><?php _e('Extra button classes to ad theme support.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="aol_form_classes"><?php _e('Form Classes', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_form_classes" class="regular-text" name="aol_form_classes" value="<?php echo sanitize_text_field(get_option('aol_form_classes')); ?>">
                            <p class="description"><?php _e('Extra form classes to ad theme support.', 'ApplyOnline'); ?></p>
                        </td>
                    </tr> 
                    <tr>
                        <th><label for="aol_form_field_classes"><?php _e('Form Fields Classes', 'ApplyOnline'); ?></label></th>
                        <td>
                            <input type="text" id="aol_form_field_classes" class="regular-text" name="aol_form_field_classes" value="<?php echo sanitize_text_field(get_option('aol_form_field_classes')); ?>">
                            <p class="description"><?php _e('Extra classes for form fields to ad theme support.', 'ApplyOnline'); ?></p>
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
                'question' => __('How to create an ad?', 'ApplyOnline'),
                'answer' => __('In your WordPress admin panel, go to "All Ads" menu with globe icon and add a new ad listing here.', 'ApplyOnline')
            ),
            array(
                'question' => __('How to show ad listings on the front-end?', 'ApplyOnline'),
                'answer' => __('You may choose either option.', 'ApplyOnline'),
            ),
            array(
                'answer' => array(
                    __('Write [aol] shortcode in an existing page or add a new page and write shortcode anywhere in the page editor. Now click on VIEW to see all of your ads on front-end.?' ,'ApplyOnline'),
                    sprintf(__('The url %s lists all the applications using your theme&#39;s default look and feel. %s(If above not working, try saving %s permalinks %s without any change)' ,'ApplyOnline'), '<b><a href="'.get_post_type_archive_link( 'aol_ad' ).'" target="_blank" >'.get_post_type_archive_link( 'aol_ad' ).'</a></b>', '<br />&nbsp; &nbsp;&nbsp;', '<a href="'.get_admin_url().'options-permalink.php"  >', '</a>')
                )
            ),
            array(
                'question' => __('Ads archive page on front-end shows 404 error or Nothing Found.' ,'ApplyOnline'),
                'answer' => sprintf(__('Try saving %spermalinks%s without any change.' ,'ApplyOnline'), '<a href="'.get_admin_url().'options-permalink.php"  >', '</a>')
            ),
            array(
                'question' => __('I have a long application form to fill, how can i facilitate applicant to fill it conveniently?' ,'ApplyOnline'),
                'answer' => sprintf(__('With %sApplication Tracking System%s extention, applicant can save/update incomplete form for multiple times before final submission.' ,'ApplyOnline'), '<a href="https://wpreloaded.com/product/apply-online-application-tracking-system/" target="_blank" class="strong">', '</a>')
            ),
            array(
                'question' => __('How can I show selected ads on front-end?' ,'ApplyOnline'),
                'answer' => array(
                        __('You can show selected ads on your website by using shortcode with "ads" attribute. Ad ids must be separated with commas i.e. [aol ads="1,2,3"].' ,'ApplyOnline'),
                        __('To show first 5 ads, use count shortcode attribute i.e. [aol count="5"]')
                    ),
            ),
            array(
                'question' => __('Can I show ads without excerpt/summary?' ,'ApplyOnline'),
                'answer' => __('Yes, use shortcode with "excerpt" attribute i.e. [aol excerpt="no"]' ,'ApplyOnline')
            ),
            array(
                'question' => __('What attributes can i use in the shortcode?' ,'ApplyOnline'),
                'answer' => __('Shortcode with default attributes is [aol ads="all" count="-1" excerpt="yes" type="ad" display="full"]. Use only required attributes.' ,'ApplyOnline')
            ),
            array(
                'question' => __('Can I display only application form using shortocode?' ,'ApplyOnline'),
                'answer' => __(' Yes, [aol_form id="0"] is the shortcode to display a particular application form in WordPress pages or posts. Use correct form id in the shortocode.' ,'ApplyOnline')
            ),
            array(
                'question' => __('Can I list ads without any fancy styling?' ,'ApplyOnline'),
                'answer' => __('Yes, use shortcode with "style" attribute to list ads with bullets i.e. [aol display="list"]. To generate an ordered list add another attribute "list-style" i.e. [aol display="list" list-style="ol"].' ,'ApplyOnline')
            ),
            array(
                'question' => __('Filters under ApplyOnline section are not accessible.' ,'ApplyOnline'),
                'answer' => __('Try deactivating & then reactivating this plugin.' ,'ApplyOnline')
            ),
            array(
                'question' => __("I Have enabled the filters but they are not visible on the 'ads' page." ,'ApplyOnline'),
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
            <h3><?php _e('How to create an ad?' ,'ApplyOnline'); ?></h3>
            <?php _e('In your WordPress admin panel, go to "All Ads" menu with globe icon and add a new ad listing here.', 'ApplyOnline'); ?>

            <h3><?php _e('How to show ad listings on the front-end?' ,'ApplyOnline'); ?></h3>
            <!-- @todo Fix empty return value of aol_slug option. !-->
            <?php _e('You may choose either option.' ,'ApplyOnline') ?>
            <ol>
                <li><?php _e('Write [aol] shortcode in an existing page or add a new page and write shortcode anywhere in the page editor. Now click on VIEW to see all of your ads on front-end.?' ,'ApplyOnline') ?>
                <li><?php echo sprintf(__('The url %s lists all the applications using your theme&#39;s default look and feel. %s(If above not working, try saving %s permalinks %s without any change)' ,'ApplyOnline'), '<b><a href="'.get_post_type_archive_link( 'aol_ad' ).'" target="_blank" >'.get_post_type_archive_link( 'aol_ad' ).'</a></b>', '<br />&nbsp; &nbsp;&nbsp;', '<a href="'.get_admin_url().'options-permalink.php"  >', '</a>'); ?></li>
            </ol>
            <h3><?php _e('Application form submission fails and shows Session Expired error.', 'ApplyOnline'); ?></h3>
            <?php _e('If you have firewall installed such as WordFence or CloudFlare then disabling Security Nonce under General tab will be helpful.', 'ApplyOnline'); ?>
            
            <h3><?php _e('Ads archive page on front-end shows 404 error or Nothing Found.' ,'ApplyOnline') ?></h3>
            <?php printf(__('Try saving %spermalinks%s without any change.' ,'ApplyOnline'), '<a href="'.get_admin_url().'options-permalink.php"  >', '</a>'); ?>
            
            <h3><?php _e('I have a long application form to be filled, how can i facilitate applicant to fill it conveniently?' ,'ApplyOnline'); ?></h3>
            <?php printf(__('With %sApplication Tracking System%s extention, applicant can save/update incomplete form for multiple times before final submission.' ,'ApplyOnline'), '<a href="https://wpreloaded.com/product/apply-online-application-tracking-system/" target="_blank" class="strong">', '</a>'); ?>
            
            <h3><?php _e('Can I show selected ads on front-end?' ,'ApplyOnline'); ?></h3>
            <?php _e('Yes, you can show any number of ads on your website by using shortcode with "ads" attribute. Ad ids must be separated with commas i.e. [aol ads="1,2,3" type="ad"]. Default type is "ad".' ,'ApplyOnline'); ?>

            <h3><?php _e('Can I show ads without excerpt/summary?' ,'ApplyOnline'); ?></h3>
            <?php _e('Yes, use shortcode with "excerpt" attribute i.e. [aol excerpt="no"]' ,'ApplyOnline'); ?>

            <h3><?php _e('What attributes can i use in the shortcode?' ,'ApplyOnline'); ?></h3>
            <?php printf(__('Shortcode with all attributes and default values is %s. Use only required attributes.' ,'ApplyOnline'), '[aol count="-1" ads="all" excerpt="yes" type="ad" display="full"]'); ?>
            <ul>
                <li><?php _e('"count" is used to control number of ads shown in the ads list. e.g. count="10" will show latest 10 ads.', 'ApplyOnline'); ?></li>
                <li><?php _e('"ads" is used to show selected ads. This attribute accepts ads ids e.g. ads="5,10,70" will show three ads with given ids.', 'ApplyOnline'); ?></li>
                <li><?php _e('"excerpt" is used to show or hide excerpt in each ad section. It excepts two values yes or no.', 'ApplyOnline'); ?></li>
                <li><?php _e('"type" attribute is used to show ads from selected ad type only e.g. type="admission"', 'ApplyOnline'); ?></li>
                <li><?php _e('"display" attribute is used to control the output style of the shortcode. It accepts two display types full or list, ', 'ApplyOnline'); ?></li>
            </ul>

            <h3><?php _e('Can I display application form only using shortocode?' ,'ApplyOnline'); ?></h3>
            <?php _e(' Yes, [aol_form id="0"] is the shortcode to display a particular application form in WordPress pages or posts. Use correct form id in the shortocode.' ,'ApplyOnline'); ?>
            
            <h3><?php _e('Can I list ads without any fancy styling?' ,'ApplyOnline'); ?></h3>
            <?php _e('Yes, use shortcode with "style" attribute to list ads with bullets i.e. [aol display="list"]. To generate an ordered list add another attribute "list-style" i.e. [aol display="list" list-style="ol"].' ,'ApplyOnline'); ?>
            
            <h3><?php _e('Filters under ApplyOnline section are not accessible.' ,'ApplyOnline'); ?></h3>
            <?php _e('Try deactivating & then reactivating this plugin.' ,'ApplyOnline'); ?>
            
            <h3><?php _e("I Have enabled the filters but they are not visible on the 'ads' page." ,'ApplyOnline'); ?></h3>
            <?php _e('Possible reasons for not displaying ad filters are given as under:' ,'ApplyOnline'); ?>

            <ol>
                <li><?php _e('Filters are visible when you show your ad on front-end using [aol] shortcode only. ' ,'ApplyOnline'); ?></li>
                <li><?php _e('Make sure Filters are enable under ApplyOnline/Settings/AdTypes section in wordpress Admin Panel.' ,'ApplyOnline'); ?></li>
                <li><?php _e('On Ad Editor screen in the right siedebar, there is an option to mark the ad for a filter e.g. Categories, Types or Locations.' ,'ApplyOnline'); ?></li>
            </ol>
            
            <h3><?php _e('Is plugin not working accordingly or generating 500 internal server error?' ,'ApplyOnline') ?></h3>
            <?php echo sprintf(__("You may need to resolve a theme or plugin conflict with ApplyOnline plugin. %s Click Here %s to fix this conflict." ,'ApplyOnline'), '<a href="https://wpreloaded.com/wordpress-theme-or-plugin-conflicts-and-their-solution/" target="_blank">', '</a>'); ?>            
            
            <h3><?php _e('I am facing a different problem. I may need a new feature in the plugin.' ,'ApplyOnline') ?></h3>
            <?php echo sprintf(__("Please contact us through %s plugin's website %s for more information." ,'ApplyOnline'), '<a href="https://wpreloaded.com/contact-us/" target="_blank">', '</a>'); ?>
        </div>    
        <?php
    }
    
    private function tab_extend(){
        ?>
        <div class="card" style="max-width:100%">
            <p><?php echo __('Looking for more options to put additional power in your hands?' ,'ApplyOnline') ?></p>
            <p><?php printf(__("There's a range of ApplyOnline extensions availabel. %sClick Here%s for docs and extensions." ,'ApplyOnline'), '<a href="https://wpreloaded.com/shop" target="_blank">', '</a>'); ?></p>
        </div>            
        <?php 
    }
 }