<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wpreloaded.com/farhan-noor
 * @since      1.0.0
 *
 * @package    Applyonline
 * @subpackage Applyonline/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Applyonline
 * @subpackage Applyonline/public
 * @author     Farhan Noor <profiles.wordpress.org/farhannoor>
 */
class Applyonline_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	protected $plugin_name;

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

                new SinglePostTemplate($plugin_name, $version); //Passing 2 parameters to the child
                new Applyonline_Shortcodes();
                new Applyonline_AjaxHandler();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

                wp_enqueue_style('aol-jquery-ui', plugin_dir_url(__FILE__).'css/jquery-ui.min.css');
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/applyonline-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/applyonline-public.js', array( 'jquery','jquery-ui-datepicker' ), $this->version, false );
                //wp_enqueue_script( 'aol-charcounter', plugin_dir_url( __FILE__ ) . 'js/cct_embed.min.js', array(), $this->version, false );
                $aol_js_vars = array(
                        'ajaxurl' => admin_url ( 'admin-ajax.php' ),
                        'date_format'   => get_option('aol_date_format', 'dd-mm-yy'),
                        'url'    => plugins_url(NULL, __DIR__),
                        'consent_text' => esc_html__('Do you really want to submit this form?', 'ApplyOnline'),
                        );
                wp_localize_script (
                    $this->plugin_name,
                    'aol_public', 
                    apply_filters('aol_js_vars', $aol_js_vars)
                );
	}
        
        public function check_ad_closing_status($query){
            $types = get_aol_ad_types();
            if(!is_admin() and isset($query->query['post_type']) and in_array($query->query['post_type'], $types)){
                global $wpdb;
                $closed = $wpdb->get_col("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_aol_ad_closing_date' AND meta_value != '' AND (meta_value BETWEEN 0 AND UNIX_TIMESTAMP()) AND post_id NOT IN (SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_aol_ad_close_type' AND meta_value != 'ad')");
                $query->set('post__not_in', $closed);
            }
        }
        /**
         * This function should be moved to the admin section.
         */
        function output_attachment(){
            if( isset($_REQUEST['aol_attachment']) AND (current_user_can('read_application') OR current_user_can('save_application')) ){
                
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

class SinglePostTemplate{
        var $plugin_name;
        var $version;
        public function __construct($plugin_name = null, $version = null) {
            $this->plugin_name = $version;
            $this->version = $plugin_name;
            add_filter('body_class', array($this, 'aol_body_classes'));
            add_filter( 'the_content', array($this, 'aol_the_content') );
        }
    
        function aol_body_classes($classes){
            $classes[] = $this->plugin_name;
            $classes[] = $this->plugin_name.'-'.$this->version;
            return $classes;
        }
        public function aol_ad_is_checked($i){
            if($i==0) $checked="checked";
            else $checked = NULL;
            return $checked;
        }

        public function application_form_fields($post_id = 0){
            //Get current post object on SINGLE Template file(e.g. single.php,  aol_ad-single.php).
            if(empty($post_id)){
                global $post;
                $post_id = $post->ID;
            }
            
            $field_types = array('text'=> esc_html__('Text','ApplyOnline'), 'checkbox'=>esc_html__('Check Box','ApplyOnline'), 'dropdown'=>esc_html__('Drop Down','ApplyOnline'), 'radio'=> esc_html__('Radio','ApplyOnline'), 'file'=> esc_html__('File','ApplyOnline'), 'separator' => esc_html__('Seprator','ApplyOnline'));
            
            $raw_fields = get_aol_ad_post_meta($post_id);
            $fields = array();
            $i=0;
            foreach($raw_fields as $key => $val){
                    $fields[$i] = $val; //
                    $fields[$i]['key'] = substr($key, 9); //Add key as array element for easy access.
                    if(isset($fields[$i]['options'])) $fields[$i]['options'] = array_combine(explode(',', $fields[$i]['options']), explode(',', $fields[$i]['options'])); //Add options as an array, Setting arrays keys & values alike.
                    if(!isset($fields[$i]['required'])) $fields[$i]['required'] = 1; //Fix for older versions (prior to 1.6.1 when REQUIRED field was not available)
                    if(isset($fields[$i]['type']) AND $fields[$i]['type']=='seprator') $fields[$i]['type'] = 'separator'; //Fixed bug before 1.9.6, spell mistake in the key.
                    $i++;
            } 
            return $fields;
        }        

        public function application_form($post_id = 0){
            
            if(empty($post_id) AND !is_singular()){ 
                return '<p id="aol_form_status alert alert-danger">'.__('Form ID is missing', 'ApplyOnline').'</p>';
            }
                        
            global $post;
            //Sanitizing & fetching post ID.
            $post_id = empty($post_id) ? (int)$post->ID : (int)$post_id;
            $date = get_post_meta($post_id, '_aol_ad_closing_date', TRUE);
            
            $fields = apply_filters('aol_form_fields', $this->application_form_fields($post_id), $post_id);

            //If Form has no fields.
            if( empty($fields) ) return NULL;

            $progress_bar = get_option('aol_progress_bar_color', array('foreground' => '#222222', 'background' => '#dddddd', 'counter' => '#888888'));
            ob_start();

            echo '<h3 class="aol-heading">'. esc_html_x('Apply Online', 'public', 'ApplyOnline').'</h3>';
            //If closing date has passed away.
            if( !empty($date) AND $date < time() )
                return '<span class="alert alert-warning">'. get_option_fixed('aol_application_close_message', __('We are no longer accepting applications for this ad.', 'ApplyOnline')).'</span>';
                $css_pattern = '/^#([0-9A-Fa-f]{3}){1,2}$/';
                $css_bg = preg_match($css_pattern, $progress_bar['background']) ? $progress_bar['background'] : NULL;
                $css_fg = preg_match($css_pattern, $progress_bar['foreground']) ? $progress_bar['foreground'] : NULL;
                $css_color = preg_match($css_pattern, $progress_bar['counter']) ? $progress_bar['counter'] : NULL;
            ?>
            <style>
                .aol-progress{background-color: <?php echo $css_bg; ?>}
                .aol-progress-count{background-color: <?php echo $css_fg; ?>}
                .aol-progress-counter{color: <?php echo $css_color; ?>}
            </style>
            <form class="aol_app_form aol_app_form_<?php echo (int)$post_id; ?>" name="aol_app_form" id="aol_app_form" enctype="multipart/form-data"  data-toggle="validator" onsubmit="aolSubmitForm(event)" action="#aol_app_form">
                <?php
                    do_action('aol_before_form_fields', $post_id);
                    
                    //Function returns sanitized data.
                    echo aol_form_generator($fields, 0, '_aol_app_', $post_id);
                    do_action('aol_after_form_fields', $post_id);
                    $aol_button_attributes = apply_filters('aol_form_button_attributes', array('value' => __('Submit', 'ApplyOnline'), 'class' => 'btn btn-primary btn-submit button submit fusion-button button-large aol-form-button '. get_option('aol_submit_button_classes')));
                    $aol_button_attributes = apply_filters('aol_form_button', $aol_button_attributes);//depricated in the favour of aol_form_button_attributes since 2.2.3.1
                    $attributes = NULL;
                    foreach($aol_button_attributes as $key => $val){
                        //Sanitized attributes
                        $attributes .= esc_attr($key).'="'.esc_attr($val).'" ';
                    }
                    ?>
                <p><small><i><?php echo sanitize_text_field( get_option('aol_required_fields_notice', 'Fields with (*) are compulsory.') ); ?></i></small></p>
                <input type="hidden" name="ad_id" value="<?php echo (int)$post_id; ?>" >
                <input type="hidden" name="action" value="aol_app_form" >
                <input type="hidden" name="wp_nonce" value="<?php echo wp_create_nonce( 'aol_form' ); ?>" >
                <?php if( get_option('aol_is_progress_bar') ): ?>
                    <div class="progress-wrapper">
                        <span><?php echo sanitize_text_field(get_option('aol_progress_bar_title', 'Application Progress')); ?></span>
                        <!--<progress value="0" max="100" style="width: 100%">3/5</progress>-->
                        <div class="aol-progress">
                            <div class="aol-progress-count"></div>
                            <span class="aol-progress-counter"></span>
                        </div>                    
                    </div>
                <?php endif; ?>
                <?php do_action('aol_before_submit_button', $post_id); ?> 
                <?php aol_form_button(); ?>
                <?php do_action('aol_after_submit_button', $post_id); ?>
            </form>
            <p id="aol_form_status"></p>
        <?php
            return apply_filters('aol_form', ob_get_clean(), $fields, $post_id);
        }

        public function ad_features($post_id = 0, $output = 'table') {
            //Get current post object on SINGLE Template file.
            global $post;
            if(empty($post_id)) $post_id = $post->ID;
            
            $fields = get_aol_ad_features($post_id);

            $metas = NULL;
            if( !empty($fields) ):
                
                switch ($output):
                    case 'heading':
                        $start_wrapper = '<div class="aol_ad_features">';
                        $close_wrapper = '</div>';
                        $row_start = '<h4>';
                        $separator = ':</h4><span>';
                        $row_close = '</span>';
                        break;
                    
                    case 'list':
                        $start_wrapper = '<ul class="aol_ad_features">';
                        $close_wrapper = '</ul>';
                        $row_start = '<li><b>';
                        $separator = ':</b> ';
                        $row_close = '</li>';
                        break;
                    
                    default:
                        $start_wrapper = '<table class="aol_ad_features">';
                        $close_wrapper = '</table>';
                        $row_start = '<tr><td>';
                        $separator = '</td><td>';
                        $row_close = '</td></tr>';
                endswitch;
                $metas = $start_wrapper;
                foreach($fields as $key => $val):
                        if(!is_array($val)) 
                            $val = array('label' => str_replace('_', ' ',substr($key, 13)), 'value' => $val);
                            
                        $metas.= $row_start.$val['label'].$separator.$val['value'].$row_close;
                endforeach;
                $metas.= $close_wrapper;
            endif;
          return $metas;
        }
        
        function ad_type_fix($val, $key){
            return 'aol_'.$val;
        }

        public function aol_the_content($content){
            global $post;
            $types = get_option_fixed('aol_ad_types', array('ad' => array('singular'=> esc_html__('Ad','ApplyOnline'), 'plural' => esc_html__('Ads','ApplyOnline'))));
            $aol_types = array();
            foreach($types as $type => $val){
                $aol_types[] = 'aol_'.$type;
            }
            if(!is_singular($aol_types)) return $content;
            
            global $template; 
            $features = $this->ad_features($post->ID);
            $title_features = empty($features) ? NULL : '<h4 class="aol-heading-features">'. get_option( 'aol_features_title', 'Salient Features' ).'</h4>';
            $form = $this->application_form();
            
            $not_working = '<ul><li>'.esc_html__('If the application does not load after a few seconds, please try the following:', 'ApplyOnline').'</li>';
            $not_working .= '<li>'.esc_html__('Open the application in a new tab. ', 'ApplyOnline').'</li>';
            $not_working .= '<li>'.esc_html__('Try using a different browser. ', 'ApplyOnline').'</li>';
            $not_working .= '<li>'.esc_html__('Try switching to a more stable network connection. ', 'ApplyOnline').'</li>';
            $not_working .= '<li>'.esc_html__('Report this problem to the development team. ', 'ApplyOnline').'</li></ul>';

            //Show this content if you are viewing aol_ad post type using single.php (not with single-aol_type.php)
            $aol_content;
            $this_template = substr(wp_basename($template), 7, -4);
            if(in_array($this_template, $aol_types) OR has_shortcode( $content, 'aol_form' )):
                $aol_content = $content;
            else: 
                $aol_content = '<div class="aol-single aol-wrapper">'.$content.$title_features.$features.$form.'</div>';
                $aol_content = apply_filters( 'aol_content', $aol_content, $content, $features, $form );
            endif;
            return $aol_content;
        }
}

class Applyonline_Shortcodes{
    function __construct() {
        add_shortcode( 'aol', array($this, 'aol') ); //archive of ads.
        add_shortcode( 'aol_ads', array($this, 'aol') ); //deprecated since 1.1
        add_shortcode( 'aol_ad', array($this, 'aol_ad') ); //Single ad with form.
        add_shortcode( 'aol_form', array($this, 'aol_form') ); //Single ad form only.
        add_shortcode( 'aol_filters', array($this, 'aol_filters') ); //Single ad form only.
        add_shortcode( 'aol_features', array($this, 'aol_features') );
    }
    
        /**
         * Shortcode Generator
         * @param type $atts
         * @return type
         */
        function aol( $atts ) {
            $archive_wraper_classes = apply_filters('aol_archive_wrapper_classes', array('aol-ad-outer-wrapper'));
            $wraper_classes = apply_filters('aol_ad_wrapper_classes', array('aol-ad-inner-wrapper'));
            //$title_wrapper = apply_filters('aol_ad_title_wrapper', 'div');
            $title_classes = apply_filters('aol_ad_title_wrapper_classes', array('panel-heading'));
            $body_classes = apply_filters('aol_ad_body_wrapper_classes', array('panel-body'));
            $thumb_wrapper = apply_filters('aol_ad_thumb_wrapper', 'div');
            $thumb_classes= apply_filters('aol_ad_thumb_classes', array('aol-thumbnail', 'pull-md-left', 'center-sm-block'));
            $footer_classes = apply_filters('aol_ad_footer_wrapper_classes', array('panel-footer'));
            
            $order = apply_filters('aol_grid_element_order', array('title', 'body_start', 'meta', 'thumbnail', 'excerpt', 'body_close', 'footer'));
            $a = shortcode_atts( array(
                'ads' => '', //Depricated since 2.2.3
                'include' => '', //Replaced ads attribute.
                'exclude'  => '',
                'excerpt' => 'yes',
                'display'    => 'full',
                'list-style' => 'ul',
                'count' => '-1',
                'filter' => 'yes',
                'type'  => 'ad',
                'author' => null
                ), $atts, 'aol' );

            $lstyle = ($a['list-style'] == 'ol') ? 'ol' : 'ul';
            $args = array(
                'posts_per_page'=> $a['count'],
                'post_type'     =>'aol_'.$a['type'],
                'author'    => $a['author'],
                'exclude'   => empty($a['exclude']) ? array() : explode(',',$a['exclude']),
                //'include'   => empty($a['include']) ? array() : explode(',',$a['include']),
                'include'   => empty($a['ads']) ? array() : explode(',',$a['ads']), // Depricated since version 2.5
            );
            /*
            if( !(empty($a['ads']) and empty($a['include'])) ){
                $a['show_filter'] = 'no';
            }
             * 
             */
            //Get list of taxanomies
            $taxes = get_object_taxonomies('aol_'.$a['type']);
            $args['s'] = $search_keyword = isset($_REQUEST['aol_seach_keyword']) ? $_REQUEST['aol_seach_keyword'] : NULL;
            $args['tax_query'] = array();
            foreach($taxes as $tax){
                $tax = substr($tax, 7);
                if(isset($_REQUEST[$tax]) AND $_REQUEST[$tax] != NULL) {
                    $args['tax_query'][] = array('taxonomy' => "aol_ad_$tax", 'terms'    => array((int)$_REQUEST[$tax]));
                }
            }

            //query_posts( $args );
            //global $post; 
            $args = apply_filters('aol_pre_get_posts', $args);
            $posts = get_posts($args);

            add_filter( 'excerpt_more', array($this, 'aol_excerpt_more') );
            //$show_filter = get_option('aol_show_filter', 1);
            $filters = aol_ad_cpt_filters($a['type']);
            $filter_count = count($filters);
            ob_start();
            do_action('aol_before_shortcode', $a, $filters);
            if(!(empty($filters) OR $a['filter'] == 'no' )){
                    echo '<div class="well well-lg">'; //Started well
                        echo '<form method="post" class="form-horizontal" id="aol_'.esc_attr($a['type']).'_form" action="#aol_'.esc_attr($a['type']).'_form">';
                            echo '<div class="form-group">'; //1st row Started'
                            //$col_count = $filter_count < 4 ? 12/($filter_count+1) : 3;
                            $col_count = floor(12/($filter_count));
                            //$offset = in_array($col_count, array(5,7,9)) ? 'aol-md-offset-1' : NULL;
                            //elseif($col_count == 5) 
                            $i = 0;
                            foreach ($filters as $key => $filter){
                                //Sanitizing Key beforehand.
                                $key = sanitize_key($key);
                                //$Fclass = ((isset($_REQUEST['filter']) AND $_REQUEST['filter']) == 'aol_ad_'. $key) ? 'selected' : NULL;
                                echo '<div class="aol-md-'.(int)$col_count.'">';
                                    echo '<select name="'.esc_attr($key).'" class="aol-filter-select form-control"><option value="">'. sprintf(__('%s - All', 'Filter Dropdown', 'ApplyOnline'), esc_html__($filter['plural'], 'ApplyOnline') ).'</option>';
                                    $args = array(
                                        'taxonomy' => 'aol_ad_'. $key,
                                        'hide_empty' => true,
                                    );
                                    $terms = get_terms($args);
                                    foreach ($terms as $term){
                                        $selected = (isset($_REQUEST[$key]) AND $term->term_id == (int)$_REQUEST[$key]) ? 'selected="selected"': NULL;
                                        echo '<option value="'.(int)$term->term_id.'" '.$selected.'>'.sanitize_text_field($term->name).'</option>';
                                    }
                                    echo '</select>'; 
                                echo '</div>';
                                $i++;
                            }
                            echo '</div>'; //Ended 1st row
                        echo '<div class="form-group">'; //2nd row started
                        echo '<div class="aol-md-10"><input type="text" name="aol_seach_keyword" class="form-control" placeholder="'.__('Search Keyword', 'ApplyOnline').'" value="'. esc_attr($search_keyword).'"></div>';
                        echo '<div class="aol-md-2"><button class="fusion-button button btn btn-info btn-block aol-filter-button">'.__('Filter', 'ApplyOnline').'</button></div>';
                        echo '</div></form>'; //2nd row closed, form closed 
                    echo '</div>'; //Ended Well
            }
            if(!empty($posts)):
                if($a['display'] == 'list') echo "<$lstyle>";
                do_action('aol_before_archive');
                echo '<div class="'. esc_attr(implode(' ', $archive_wraper_classes)).'">';
                $post_count = 0;
                foreach($posts as $post): setup_postdata($post);
                    $wrapper_inner_classes = apply_filters('aol_ad_inner_wrapper_classes', array('panel', 'panel-default'), $post);
                    /* Getting Post Status*/
                    $timestamp = (int)get_post_meta($post->ID, '_aol_ad_closing_date', true);
                    $timestamp = apply_filters('aol_ad_closing_date', $timestamp, $post);
                    if( $timestamp === 0 OR $timestamp > time() ) $status = 'open';
                    else $status = 'closed';
                    /* END Getting Post Status*/
                    
                    $terms = get_terms(array('object_ids' => $post->ID, 'hide_empty' => TRUE, 'taxonomy' => aol_sanitize_taxonomies($filters)));
                    
                    if($a['display'] == 'list'): echo '<li>'. apply_filters('aol_shortcode_list', '<a href="'.get_the_permalink($post).'">'.$post->post_title.'</a>').'</li>';
                    else:
                        do_action('aol_before_ad', $post_count, $post->post_count);
                        echo '<div class="'. esc_attr(implode(' ', $wraper_classes)).' aol_ad_'.$post->ID.'">';
                            echo '<div class="'. esc_attr( implode(' ', $wrapper_inner_classes) ).' '.$status.'">';
                            foreach($order as $index):
                                switch ($index):
                                    case 'title':
                                        $output = apply_filters('aol_shortcode_title', $post->post_title, $post, $title_classes);
                                        echo '<div class="'. esc_attr( implode(' ', $title_classes) ).'">'.$output.'</div>';
                                        break;

                                    case 'body_start' :
                                        echo '<div class="'.esc_attr( implode(' ', $body_classes) ).'">';
                                        do_action('aol_shortcode_before_body');
                                        break;

                                    case 'thumbnail' :
                                        if(has_post_thumbnail($post))  echo get_the_post_thumbnail($post->ID, apply_filters('aol_ad_thumbnail_size', 'thumbnail') , array('class' => implode(' ', $thumb_classes), 'title' => $post->post_title, 'alt' => $post->post_title));
                                        break;

                                    case 'meta' :
                                        echo apply_filters('aol_ad_meta', NULL, $post);
                                        break;

                                    case 'body_close':
                                        $body = array(
                                            'excerpt' => get_the_excerpt($post),
                                            'readmore' => sprintf(
                                                    '<a href="%s" ><button class="%s">%s</button></a>',
                                                    get_the_permalink($post),
                                                    'fusion-button button read-more btn btn-info',
                                                    __( 'Read More', 'ApplyOnline' )
                                                    )
                                            );
                                        $body = apply_filters('aol_shortcode_body', $body, $post);
                                        do_action('aol_shortcode_before_body', $post);
                                        if($a['excerpt'] != 'no') echo '<p>'. sanitize_text_field( $body['excerpt'] ).'</p>';
                                        echo '<div class="clearfix"></div>';
                                        echo apply_filters('aol_shortcode_button', $body['readmore']);
                                        do_action('aol_shortocde_after_body', $post);
                                        echo "</div>"; //Boody Wrapper
                                        break;

                                    case 'footer':
                                        if(empty($terms) or empty($filters)) break;
                                        echo '<div class="'. esc_attr( implode(' ', $footer_classes) ).'">';
                                        echo apply_filters('aol_shortcode_footer', $this->aol_filters_terms($terms, $post->ID), $post, $terms);
                                        echo "</div>";
                                    break;
                                endswitch;
                            endforeach;
                        echo "</div></div>"; //Closing inner & outer wrapers.
                        do_action('aol_after_ad', $post_count, $post->post_count);
                        if($a['display'] == 'list') echo "</$lstyle>";
                    endif; //End aol display check
                    $post_count++;
                endforeach; 
                echo "</div>"; //Outer Wrapper
                do_action('aol_after_archive', $post);
            else: echo get_option('aol_not_found_alert', 'Sorry, we could not find what you were looking for.');
            endif;
            wp_reset_postdata();
            $html = apply_filters('aol_shortcode', ob_get_clean());
            return '<div class="aol-archive aol-wrapper">'.$html.'</div>';
        }

        function aol_filters($atts){
            //@ad support for all ad types.
            //if(!is_singular('aol_ad')) return;
            
            $a = shortcode_atts( array(
                'style'   => 'csv',
                'ad'      => 0,
            ), $atts );
            if(empty($a['ad'])){
                global $post;
                $post_id = $post->ID;
            } else {
                    $post_id = (int)$a['ad'];
            }

            $filters = aol_ad_cpt_filters(get_post_type());
            $terms = get_terms(array('object_ids' => $post_id, 'hide_empty' => TRUE, 'taxonomy' => aol_sanitize_taxonomies($filters)));

            ob_start();
            echo '<div class="aol_meta">' ;
            echo $this->aol_filters_terms($terms, $post_id);
            echo '</div>';
            return ob_get_clean();
            
            ob_start();
            $terms = get_terms(array('object_ids' => $post_id, 'orderby' => 'term_group', 'hide_empty' => TRUE, 'taxonomy' => aol_sanitize_taxonomies($filters)));
            echo '<div class="aol_meta">' ;
                if( !(empty($terms) or empty($filters)) ):
                    $tax = NULL;
                    foreach ($terms as $term){
                            $title = NULL;
                            $separator = ', ';
                        if($tax != $term->taxonomy) {
                            $taxObj = get_taxonomy($term->taxonomy);
                            $pad = empty($tax) ? NULL : ' &nbsp;';
                            $title = $pad.'<h4 class="aol-ad-taxonomy">'.$taxObj->label.': </h4>';
                        }
                        echo $title.'<span>'.$term->name.$separator.'</span>'; 
                        $tax = $term->taxonomy;
                    } 
                endif;
            echo '</div>';
            return ob_get_clean();
        }

        function aol_filters_terms($terms, $post_id){
            do_action('aol_shortcode_before_terms', $post_id);
            $tax = $output = NULL;
            $terms = $this->organize_post_terms($terms);
            foreach ($terms as $term){
                    $title = NULL;
                    $separator = ', ';
                if($tax != $term->taxonomy) {
                    $pad = empty($tax) ? NULL : ' &nbsp;';
                    $taxObj = get_taxonomy($term->taxonomy);
                    $span = is_null($tax) ? '<span class="aol-tax-wrapper">' : '</span><span class="aol-tax-wrapper">';
                    $title = $span.$pad.'<strong class="aol-ad-taxonomy">'.__($taxObj->label, 'ApplyOnline').': </strong>';
                }
                $output.= $title.$term->name.$separator;
                $tax = __($term->taxonomy, 'ApplyOnline');
            }
            $output.= '</span>';
            do_action('aol_shortcode_after_terms', $post_id);
            return $output;
        }
        
        function organize_post_terms($terms_obj){
            $terms = new stdClass();
            $sort = array();
            
            foreach($terms_obj as $key => $term){
                $sort[$key] = $term->taxonomy;
            }
            asort($sort);
            $keys = array_keys($sort);
            $new_obj = array();
            foreach($keys as $key){
                array_push($new_obj, $terms_obj[$key]);
            }
            
            return $new_obj;
        }

        function aol_excerpt_more( $more ) {
                return '....';
            }

         //@todo Form generated with this shortcode may not submit & generate error: "Your form could not submit, Please contact us"
        function aol_form( $atts ){
            global $post;
            $id = is_singular('aol_ad') ? $post->ID: NULL;
            $a = shortcode_atts( array(
                //Check if shortcode is called on the Ads Page, ID is not required in that case.
                //@todo extend post type to all ad types.
                'id'   => $id,
            ), $atts );
            
            if(isset($a['id']))    return aol_form($a['id']);
        }         

        /*
         * @todo: this function should print complete ad with application form.
         */
        function aol_ad( $atts ) {
            $a = shortcode_atts( array(
                'id'   => NULL,
            ), $atts );

            if(isset($a['id'])) {
                $id = $a['id'];
                $post = get_post($id);
                return $post->post_content. aol_form($a['id']);
            }
        }
        
        function aol_features($atts){
            $a = shortcode_atts( array(
                'style'   => 'table',
            ), $atts );
            
            return aol_features($a['style']);
        }        
}

/**
  * This class is responsible to handle Ajax requests.
  * 
  * 
  * @since      1.0
  * @package    AjaxHandler
  * @author     Farhan Noor
  **/
 class Applyonline_AjaxHandler{
     
     /*
      * Upload meta, after a successfull file upload.
      */   
     var $uploads;
        
        public function __construct() {
            add_action( 'wp_ajax_aol_app_form', array($this, 'aol_save_form') );
            add_action( 'wp_ajax_nopriv_aol_app_form', array($this, 'aol_save_form') );
            add_filter( 'aol_form_errors', array($this, 'file_uploader'), 10,3 ); //Call file uploader when form is being processed.
        }
        
        function upload_path($uploads){
                $subdir = apply_filters('aol_upload_folder', 'applyonline');
                //$default = wp_upload_dir(); $default['basedir'];
                $aol_upload_path = get_option('aol_upload_path');
                $uploads['path'] = wp_normalize_path($uploads['basedir'] . '/' . $subdir);
                $uploads['subdir'] = wp_normalize_path( '/' . $subdir);
                $uploads['url'] = $uploads['baseurl']. '/' . $subdir;

                if(!empty($aol_upload_path)){
                    $uploads['basedir'] = $aol_upload_path;
                    $uploads['path'] = wp_normalize_path($aol_upload_path . '/' . $subdir);
                }
                return $uploads;
        }

        function file_uploader($errors, $post, $files){
            if(empty($files)) return $errors; //If no files are being uploaded, just quit.

            $upload_overrides = array( 'test_form' => false );

            /*Initialixing Variables*/
            //$errors = new WP_Error();
            $error_assignment = null;
            
            $uploads = array();
            $user = get_userdata(get_current_user_id());
        
            if ( ! function_exists( 'wp_handle_upload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }
            
            foreach($files as $key => $val):
                if(empty($val['name'])) continue;

                $field = get_post_meta($post['ad_id'], $key, TRUE);
                
                if( isset($field['allowed_file_types']) AND !empty($field['allowed_file_types']) ){
                    $allowed_types = str_replace(' ', '',  $field['allowed_file_types']);
                } else{
                    $allowed_types = str_replace(' ', '', get_option('aol_allowed_file_types'));
                }
                
                //Check if single File Type or multiple, if multiple convert it to an array.
                $allowed_types = strstr($allowed_types, ',') == FALSE ? array($allowed_types) : explode(',', $allowed_types );
                
                $upload_size = empty($field['allowed_size']) ? get_option_fixed('aol_upload_max_size', 1) : $field['allowed_size'];
                $max_upload_size = $upload_size*1048576; //Multiply by KBs
                
                if($max_upload_size < $val['size']){
                        $errors->add('max_size', sprintf(__( '%s is oversized. Must be under %s MB', 'ApplyOnline' ), $val['name'] , $upload_size));
                }

                /* Check File Size */
                $file_type_match = 0;
                $filetype = wp_check_filetype(  $val['name'] );
                $file_ext = strtolower($filetype['ext']);
                if( !in_array($file_ext, $allowed_types) ) $errors->add('file_type', sprintf(__( 'Invalid file %1$s. Allowed file types are: %2$s', 'ApplyOnline' ), $val['name'], implode (',', $allowed_types)));
                $errors = apply_filters('aol_before_file_upload_errors', $errors);
                if(empty($errors->errors)){
                    do_action('aol_before_file_upload', $key, $val, $post);
                                        
                    add_filter('upload_dir', array($this, 'upload_path')); //Change upload path.
                    $movefile = wp_handle_upload( $val, $upload_overrides );
                    if ( $movefile && ! isset( $movefile['error'] ) ) {
                        $uploads[$key] = $movefile;
                        $uploads[$key]['name'] = $val['name'];
                        //update_user_meta(get_current_user_id(), $key, $movefile['url'] );
                    } else {
                        /**
                         * Error generated by _wp_handle_upload()
                         * @see _wp_handle_upload() in wp-admin/includes/file.php
                         */
                         $errors->add('file_move', $val['name'].': '.$movefile['error']);
                    }
                }
            endforeach;
            //return array('errors' => $errors, 'uploads' => $uploads);
            $this->uploads = $uploads;
            return $errors;
        }
                        
        /**
         * The function processes and saves application form info in the database. 
         */
        public function aol_save_form( $form_data = NULL ){
            if( empty($form_data) ) $form_data = $_POST;
            $nonce = $form_data['wp_nonce'];
            if( !wp_verify_nonce($nonce, 'aol_form' ) /*and (int)get_option('aol_nonce_is_active', 1) == 1*/ ){
                $response = array( 'reason' => 'Session Expired', 'message' => __( 'Session Expired, please refresh this page and try again. If problem presists, please report this issue through Contact Us page. Thanks', 'ApplyOnline' ) );
                $this->response($response, 401);
            }
            $app_field = $app_data = array();
            /*Initializing Variables*/
            $errors = new WP_Error();
            $error_assignment = null;
            
            //Check for required fields
            //Get parent ad value for which the application is being submitted.
            $ad_id = (int)$form_data['ad_id'];
            
            //@todo: Save transcript in json/serialized format as one postmeta field instead of seperate field for each form field for ad.
            $transcript = $ad_transcript = get_post_meta($ad_id, '', TRUE);
            //Remove unnecessary array elements from the parent ad array.
            foreach($transcript as $key => $val):
                $key = sanitize_key($key);
                if( substr($key, 0, 9) != '_aol_app_' ){
                    unset($transcript[$key]);
                } else{
                    //$transcript[$key] =  apply_filters('aol_form_field_before_validation', maybe_unserialize( $val[0] ), $key, $val, $form_data[$key]);
                    $transcript[$key] =  maybe_unserialize( $val[0] );
                }
            endforeach;
            $form = apply_filters('aol_form_for_app_validation', $transcript, $form_data, $_FILES);
            foreach($form as $key => $val):
                $key = sanitize_key($key);

                    //Excludes separator & paragraph from validation & verification.
                    if(in_array($val['type'], array('separator', 'seprator', 'paragraph'))) continue;
                    
                    //Support for previous versions
                    if(!isset($val['label'])) $val['label'] = str_replace('_',' ', substr($key, 9));

                    //eMail validation.
                    if($val['type'] == 'email'){
                        if(!empty($form_data[$key]) and is_email($form_data[$key])==FALSE) $errors->add('email', sprintf(__('%s is invalid.', 'ApplyOnline'), '"'.$val['label'].'"'));
                    }

                    //File validation & verification.
                    if(isset($val['required']) AND $val['type'] == 'file'){
                        //if(!isset($_FILES[$key]['name'])) $errors->add('file', sprintf(__('%s is not a file.', 'ApplyOnline'), str_replace('_',' ', substr($key, 9))));
                        if((int)$val['required'] == 1 and empty($_FILES[$key]['name'])) $errors->add('required', sprintf(__('%s is required.', 'ApplyOnline'), '"'.$val['label'].'"'));
                    }

                    //chek required fields for non File Fields.
                    if( isset($val['required']) AND (int)$val['required'] == 1 and $val['type'] != 'file'){
                        $form_data[$key] = is_array($form_data[$key]) ? array_map('sanitize_text_field', $form_data[$key]) : sanitize_textarea_field($form_data[$key]);
                        if(empty($form_data[$key])) $errors->add('required', sprintf (__('%s is required.', 'ApplyOnline'), '"'.$val['label'].'"') );
                    }            
            endforeach;
            //Deprictated since 2.2.2. Will be deleted soon. Use aol_app_final_fields hook instead
            $app_data = apply_filters('aol_app_fields_to_process', $app_data, $form_data);
            $parent_id = $app_data['ad_id'] = (int)$form_data['ad_id'];

            //You can hook 3rd party form errors here.
            $errors = apply_filters('aol_form_errors', $errors, $form_data, $_FILES);

            $error_messages = $errors->get_error_messages();
            //$error_messages = array_merge($error_messages, $upload_error_messages);

            if( !empty($error_messages ) ){
                $error_html = implode('<br />', $error_messages);
                $response = array( 'reason' => esc_html__('Missing required fields', 'ApplyOnline'), 'message' => $error_html );    //generate the error response.
                
                //response output
                $this->response($response, 406);
            }
            
            //End - Check for required fields
            $applicant_emails = array();
            foreach($form as $key => $val){
                if( !isset($form_data[$key]) ) continue;
                $app_field = maybe_unserialize($val);
                
                //normalizing path & sanitizing data before input.
                $val = is_array($form_data[$key]) ? array_map('sanitize_text_field', $form_data[$key]) : sanitize_textarea_field($form_data[$key]);
                $key = sanitize_key($key);
                
                //Populating array with sanitized keys & values.
                $app_data[$key] = $val;

                /*get applicant's email for email notification*/
                if( $app_field['type'] == 'email' AND isset($app_field['notify']) AND $app_field['notify']==1 ){
                    $applicant_emails[] = $val;
                }
            }
            if(isset($this->uploads)){
                foreach($this->uploads as $name => $file){
                    //FILTER_SANITIZE_URL convert french file names to enlgish file names. 
                    $args = array('file'=> array('filter' => FILTER_SANITIZE_STRING, 'flags'), 'url' => FILTER_SANITIZE_STRING, 'type' => FILTER_SANITIZE_STRING, 'name' => FILTER_SANITIZE_STRING);
                    $app_data[sanitize_key($name)] = filter_var_array($file, $args);
                }
            } 

            $app_data = apply_filters('aol_app_final_fields', $app_data, $form_data);
            
            $args =  array(
                'post_type'     => 'aol_application',
                'post_parent'   => $parent_id,
                'post_title'    => get_the_title($parent_id),
                'post_status'   => 'publish',
                'tax_input'     => array('aol_application_status' => 'pending'),
            );
            do_action('aol_before_app_save', $app_data, $form_data); //Depricated Since 2.5
            do_action('aol_before_save_app', $app_data, $form_data);
            
            $args = apply_filters('aol_insert_app_data', $args, $app_data);
            
            $args['ID'] = $pid = wp_insert_post($args, TRUE);
            
            //If post is not saved and generates an error, return error message to the client and terminate further execution.
            if( is_wp_error($pid) ){
                $response = array( 'reason' => 'Something went wrong.', 'message' => $pid->get_error_message() ); // generate the error response.
                $this->response($response, 400);
            }

            foreach($app_data as $key => $val):
                update_post_meta($pid, $key, $val);
                $args['meta_input'][$key] = $val;
            endforeach;

            $parent = get_post($parent_id);
            update_post_meta($pid, 'aol_ad_id', $parent->ID);
            update_post_meta($pid, 'aol_ad_author', $parent->post_author);

            /* Saving Ad Transcript Since v2.2 */
            foreach($ad_transcript as $key => $val){
                if( substr($key, 0, 9) == '_aol_app_' OR $key == '_aol_fields_order' ) $ad_transcript[$key] = maybe_serialize($val[0]);
                else  unset($ad_transcript[$key]);
            }
            update_post_meta($pid, 'ad_transcript', $ad_transcript );
            /* End Saving Ad Transcript Since v2.2 */

            wp_set_post_terms( $pid, 'pending', 'aol_application_status' );

            do_action('aol_after_app_save', $pid, $app_data); //Depricated since 2.5
            do_action('aol_after_save_app', $pid, $app_data);
            //do_action('aol_after_app_save', $pid, $form_data);

            //Email notification Since v2.2 
            if( $args['post_status'] != 'draft'){
                $this->applicant_email_notification( $pid, $args, $applicant_emails );

                $recipients = sanitize_textarea_field( get_post_meta($parent_id, '_recipients_emails', true) );
                if( !empty($recipients) ) $recipients = explode("\n", str_replace(array("\r", " "),"", $recipients));
                $this->admin_email_notification($recipients, $pid, $args, $this->uploads, $parent->post_author);                    
            }

            $divert_page = get_option('aol_thankyou_page');

            empty($divert_page) ? $divert_link = null :  $divert_link = get_page_link($divert_page);
            $message = str_replace('[id]', $pid, get_option_fixed('aol_application_success_alert', __('Form has been submitted successfully with application id [id]. If required, we will get back to you shortly!', 'ApplyOnline')) );
            $response = array( 'divert' => $divert_link, 'hide_form'=>TRUE , 'message'=>$message );// generate the response.
            $this->response($response);
        }

        /**
         * Application success Email notification for Applicants
         * 
         * @param type $post_id
         * @param type $post
         * @param type $uploads
         * @return boolean
         */
        function applicant_email_notification($post_id, $post, $emails){
            if(empty($emails)) return;
            $post = (object)$post;
            
            $subject = str_replace('[title]', $post->post_title, get_option('aol_success_mail_subject', 'Your application for [title]') );

            $headers = aol_from_mail_header();
            $attachments = array();

            //@todo need a filter hook to modify content of this email message and to add a from field in the message.
            $message="<p>Hi there,</p>"
                ."<p>Thank you for showing your interest in the ad: [title]. Your application with id [id] has been received. We will review your application and contact you if required.</p>"
                .sprintf(__('Team %s'), get_bloginfo('name'))."<br/>"
                .site_url()."<br/>"
                ."Please do not reply to this system generated message.";

            $message = str_replace( array('[title]', '[id]'), array($post->post_title, $post->ID), get_option('aol_success_mail_message', $message) );
            $aol_email = apply_filters(
                        'aol_applicant_mail_notification', 
                        array('to' => $emails, 'subject' => $subject, 'message' => nl2br($message), 'headers' => $headers), 
                        $post_id,
                        $post
                    );

            do_action('aol_email_before', array('to' => $emails, 'subject' => $subject, 'message' => nl2br($message), 'headers' => $headers), $post_id, $post);

            //add_filter( 'wp_mail_content_type', 'aol_email_content_type' );

            wp_mail( $aol_email['to'], $aol_email['subject'], $aol_email['message'], $aol_email['headers']);

            //remove_filter( 'wp_mail_content_type', 'aol_email_content_type' );

            do_action('aol_email_after', $emails, $subject, nl2br($message), $headers);

            return true;
        }

        function admin_email_notification($emails, $post_id, $post, $uploads, $post_author ){
            $post = (object)$post;

            //send email alert.
            $post_url = admin_url("post.php?post=$post_id&action=edit");

            if( empty($emails) ){                
                //Get global recipients
                $emails_string = sanitize_textarea_field( get_option_fixed('aol_recipients_emails', get_option('admin_email')) );
                
                $emails = explode("\n", str_replace(array("\r", " "),"", $emails_string) );
            }

            $emails = array_map('sanitize_email', $emails);
            
            $author_notification = get_option('aol_ad_author_notification');
            if( $author_notification ){
                $author = get_userdata( $post_author );
                if( !in_array($author->user_email, $emails) ) array_push($emails, $author->user_email);
            }
            /*
            // Get the site domain and get rid of www.
            $sitename = strtolower( $_SERVER['SERVER_NAME'] );
            if ( substr( $sitename, 0, 4 ) == 'www.' ) {
                $sitename = substr( $sitename, 4 );
            }
            $from_email = 'do-not-reply@' . $sitename;
            $headers = array('Content-Type: text/html', "From: ". wp_specialchars_decode(get_bloginfo('name'))." <$from_email>");
             * 
             */

            //$subject = sprintf(__('New application for %s', 'ApplyOnline'), sanitize_text_field($post->post_title));
            $subject = str_replace( array('[id]' ,'[title]'), array($post->ID ,$post->post_title), get_option('aol_admin_mail_subject', 'New application [id] for [title]') );
            $headers = aol_from_mail_header();

            //@todo need a filter hook to modify content of this email message and to add a from field in the message.
            $message=   '<p>'.__('Hi,', 'ApplyOnline').'</p>'
                        .'<p>'
                        .sprintf(__('A new application for the ad %1$s received on %2$s website.', 'ApplyOnline'), '<b>'.$post->post_title.'</b>', '<b>'.get_bloginfo('name').'</b>')
                        .'</p><p>'
                        .sprintf(__('%sClick Here%s to access this application.', 'ApplyOnline'),'<b><a href="'.$post_url.'">', '</a></b>')
                        .'</p>'
                        .__('Thank you', 'ApplyOnline')
                        .'<br /><p>----<br />'
                        .sprintf(__('This is an automated response from Apply Online plugin on %s', 'ApplyOnline'), '<a href="'.site_url().'" >'.get_bloginfo('name').'</a>')
                        .'</p>';

            $message = apply_filters('aol_email_notification', $message, $post_id); //Deprecated.
            $aol_email = apply_filters(
                        'aol_email', 
                        array( 'to' => $emails, 'subject' => $subject, 'message' => $message, 'headers' => $headers, 'attachments' => array() ), 
                        $post_id,
                        $post,
                        $uploads
                    );

            do_action('aol_email_before', array('to' => $emails, 'subject' => $subject, 'message' => nl2br($message), 'headers' => $headers), $post_id, $post, $uploads);

            wp_mail( $aol_email['to'], $aol_email['subject'], $aol_email['message'], $aol_email['headers'], $aol_email['attachments']);
                        
            do_action('aol_email_after', $emails, $subject, nl2br($message), $headers);

            return true;
        }
        
        /**
         * This function returns REST or AJAX response back to the client and terminatex further execution of the calling function.
         * 
         * @param array $result The actual data that is being returned to the client. It is the body of the HTTP response, containing the information that needs to be received from the server
         * @param int $code HTTP response status code. Default is 200 successful. If $result variable is empty, it will return 404 not found status code.
         * @param string $header HTTP header to be sent to the requesting client.
         * 
         * @return NULL NONE This function terminates further execution hence it retunrs nothing.
         */
        function response( $result = [], $code = 200, $header = "Content-type:application/json" ){
            if( empty($result) ) $code = 404;

            http_response_code($code);
            header($header);
            exit( json_encode($result) );
        }
}