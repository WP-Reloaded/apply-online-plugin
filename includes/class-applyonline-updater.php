<?php
/**
 * The updater functionality of the plugin.
 *
 * @link       
 * @since      2.6.7.3
 *
 * @package    Applyonline
 * @subpackage Applyonline/updater
 */

/**
 * The updater functionality of the plugin.
 *
 * Defines the plugin name, version
 *
 * @package    Applyonline
 * @subpackage Applyonline/updater
 * @author     Farhan Noor <profiles.wordpress.org/farhannoor>
 */
class Applyonline_Updater{
    
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

        /**
	 * The version of this plugin last time saved in the db;
	 *
	 * @access   private
	 * @var      string    $version    The version saved in the database.
	 */
        protected $db_version;

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
            $this->db_version = get_option('aol_version', $version);
        }

        function get_version(){
            return $this->plugin_version;
        }

        function after_plugin_update(){
            $saved_version = $this->db_version;
            $version = $this->plugin_version;
            $done = FALSE;
            
            if( version_compare( '1.6', $saved_version, '>' ) ) {
                $this->bug_fix_before_16();
                $done = TRUE;
            }

            if( version_compare( '1.9.92', $saved_version, '>' ) ){
                $this->fix_roles();
                $done = TRUE;
            }

            if( version_compare( '2.1', $saved_version, '>' ) ){
                /*Merge Custom Filters to Default Filters*/
                $this->fix_filters();
                $done = TRUE;
            }
            
            if( version_compare( '2.6.7.3', $saved_version, '>' ) ){
                $this->fix_application_statuses();
                $done = TRUE;
            }
            
            if( $done === TRUE ){
                update_option('aol_version', $this->get_version(), TRUE);
            }
        }
        
        function fix_filters(){
               $default_filters = [
                    'category' => array('singular' => esc_html__('Category', 'apply-online'), 'plural' => esc_html__('Categories', 'apply-online')),
                    'type' => array('singular' => esc_html__('Type', 'apply-online'), 'plural' => esc_html__('Types', 'apply-online')),
                    'location' => array('singular' => esc_html__('Location', 'apply-online'), 'plural' => esc_html__('Locations', 'apply-online'))
                ];
                $custom_filters = get_option_fixed('aol_custom_filters', array());
                $filters = array_merge($default_filters, $custom_filters);
                //Update Option was not working for Existing options, hence it is 1st being deleted.
                delete_option('aol_ad_filters');
                update_option('aol_ad_filters', $filters);
                
                /*Merge Custom Statuses to Default Statuses*/
                $default_statuses = array('pending' => __('Pending', 'apply-online'), 'rejected'=> __('Rejected', 'apply-online'), 'shortlisted' => __('Shortlisted', 'apply-online'));
                $custom_statuses = get_option_fixed('aol_custom_statuses', array());
                $statuses = array_merge($default_statuses, $custom_statuses);
                //Update Option was not working for Existing options, hence it is 1st being deleted.
                delete_option('aol_custom_statuses');
                update_option('aol_custom_statuses', $statuses);
                
                //update_option('aol_mail_footer', "\n\nThank you\n".get_bloginfo('name')."\n".site_url()."n------\nPlease do not reply to the system generated message.");
        }

        function fix_application_statuses(){
            //$notices = (array)get_option( 'aol_admin_notices' );
            //update_option( 'aol_admin_notices', $notices[] = 'db_update_required' );
            global $wpdb;
            $qry = "UPDATE $wpdb->posts p
            INNER JOIN $wpdb->term_relationships tr 
                ON p.ID = tr.object_id
            INNER JOIN $wpdb->term_taxonomy tt 
                ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN $wpdb->terms t 
                ON tt.term_id = t.term_id
            SET p.post_status = t.slug
            WHERE p.post_type = 'aol_application'
              AND tt.taxonomy = 'aol_application_status';
            ";
            $wpdb->query($qry);
        }

        function fix_roles(){
            $role = get_role('administrator');
            $role->remove_cap( 'edit_ratings' ); //Fixing bug in version 1.9.92
        }
        
        /**
         * This function fixes a bug in versions prior to 1.6
         * 
         * The Bug: Application form fields(Post Metas) were serialized twice before save. 
         * 
         * The Fix: Check each app form field and converts it from dual serialized to single serialized value.
         * 
         * @since 1.6
         * 
         */
        function bug_fix_before_16(){
            global $wpdb;
            $fields = $wpdb->get_results("SELECT post_id, meta_key, meta_value FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON ID=post_id WHERE post_type = 'aol_ad' AND meta_key LIKE '_aol_app_%'");
            foreach ($fields as $field){
                if (is_string(unserialize($field->meta_value))) update_post_meta ($field->post_id, $field->meta_key, unserialize(unserialize($field->meta_value)));
            }
        }
}