<?php

/**
 * @since   1.8
 * @access public
 * 
 * @var   mix $option   Option.
 * @return  string
 */ 
function get_aol_option($option, $default = NULL){
     $options = get_option('aol_options');
     $val = isset($options[$option]) ? $options[$option] : $default;
     return $val;
 }
 
 /**
 * Retrieves an option value based on an option name.
 *
 * If the option does not exist or does not have a value, then the return value
 * will be false. This is useful to check whether you need to install an option
 * and is commonly used during installation of plugin options and to test
 * whether upgrading is required.
 *
 * If the option was serialized then it will be unserialized when it is returned.
 *
 * Any scalar values will be returned as strings. You may coerce the return type of
 * a given option by registering an {@see 'option_$option'} filter callback.
 *
 * @since 1.5.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $option  Name of option to retrieve. Expected to not be SQL-escaped.
 * @param mixed  $default Optional. Default value to return if the option does not exist.
 * @param bool  $can_be_emtpy Optional. Return default value if the option exist with empty string, empty array or null value. Default is FALSE.
 * @return mixed Value set for the option.
 */
 function get_option_fixed($option, $default = NULL, $can_be_emtpy = FALSE){
    $value = get_option($option, $default);
    
    if(empty($value) AND $can_be_emtpy === FALSE) $value = $default;
    
    return $value;
 }

/**
 * Returns rich application form. 
 *
 * @since    1.6
 * @access   public
 * @var      string    $post_id    Post id.
 * @return   array     Application form fields.
 */
function aol_form($post_id = 0){
    $aol = new SinglePostTemplate();
    return $aol->application_form($post_id);
}

/**
 * Use this form to create application form button(s). 
 * 
 * @param array $attributes
 * @return string attributes
 */
function get_aol_form_button($id = 'aol_app_submit_button', $title = NULL, $classes = NULL, $atts = array() ){
    if( $title == NULL ) $title = esc_attr__('Submit', 'ApplyOnline');
    if( $classes == NULL ) $classes = apply_filters('aol_form_button_classes', 'btn btn-primary btn-submit button submit fusion-button button-large aol-form-button '. esc_attr( get_option('aol_submit_button_classes') ) );
    
    $attributes = apply_filters('aol_form_button_attributes', $atts);
    $attributes = apply_filters('aol_form_button', $attributes);//depricated in the favour of aol_form_button_attributes since 2.2.3.1
    $output = NULL;
    foreach($attributes as $key => $val){
        //Sanitized attributes
        $output .= esc_attr($key).'="'.esc_attr($val).'" ';
    }
    return '<input type="submit" id='.esc_attr( $id ).' value="'. sanitize_text_field( $title ).'" class="'.esc_attr( $classes ).'" '.$output.' >';
}

function aol_form_button($id = 'aol_app_submit_button', $title = NULL, $classes = NULL, $atts = array()){
    echo get_aol_form_button($id, $title, $classes, $atts);
}

/**
 * Returns array of application form fields. 
 *
 * @since    1.6
 * @access   public
 * @var      string    $post_id    Post id.
 * @return   array     Application form fields.
 */

function aol_form_fields($post_id = 0){
    $aol = new SinglePostTemplate();
    return $aol->application_form_fields($post_id);
}

/**
 * Returns array of application features. 
 *
 * @since    1.6
 * @access   public
 * @var      string    $post_id    Post id.
 * @return   array     Application form fields.
 */
function aol_features($style = 'table'){
    $aol = new SinglePostTemplate();
    return $aol->ad_features(0, $style);
}

/**
 * Returns array of ad features. 
 *
 * @since    1.6
 * @access   public
 * @var      string    $post_id    Post id.
 * @return   array     Application form fields.
 */
function get_aol_ad_features($post_id){
    global $post;
    if(empty($post_id)) $post_id = $post->ID;
    $raw_fields = get_post_meta($post_id);
    $fields = array();
    $i=0;
    foreach($raw_fields as $key => $val){
        if(substr($key, 0, 13) == '_aol_feature_'){
            $fields[$key] = maybe_unserialize($val[0]); //
        }
    }
    
    return $fields;
}

/**
 * Returns array of application form fields in correct order. 
 *
 * @since    1.6
 * @access   public
 * @var      string    $post_id    Post id.
 * @return   array     Application form fields.
 */
function get_aol_ad_post_meta($post_id){
    $post_id = (int)$post_id;
    $form_fields = array();
    $keys_order = get_post_meta($post_id, '_aol_fields_order', TRUE);
    $metas = get_post_meta($post_id);
    //If fields order is not set in DB then fetch all form fields without order.
    if(empty($keys_order)){
        foreach ($metas as $key => $val){ 
            if(substr($key, 0, 9) == '_aol_app_') $form_fields[$key] = unserialize ($val[0]);
        }
    }
    //Get fields according to field order.
    else{ 
        foreach ($keys_order as $key){
            $form_fields[$key] = unserialize($metas[$key][0]);
        }
    }
    
    return $form_fields;
}
/*
 * Returns Ad types with relevent data.
 */
function aol_ad_types(){
    return get_option_fixed('aol_ad_types', array('ad' => array('singular' => esc_html__('Ad','ApplyOnline'), 'plural' => esc_html__('Ads','ApplyOnline'), 'description' => esc_html__('All Ads','ApplyOnline'), 'filters' => array())));
}

/**
 * Helper function to ad prefix to the post types.
 * 
 * Marked for removal in favor of aol_add_prefix()
 * 
 * @param string $value
 * @return string
 */
function add_aol_prefix($value){
    if(!strpos($value, 'aol_')) $value = 'aol_'.$value;
    return $value;
}

/**
 * Helper function to ad prefix to the post types.
 * 
 * @param string $value
 * @return string
 */
function aol_add_prefix($value){
    if(!strpos($value, 'aol_')) $value = 'aol_'.$value;
    return $value;
}

/**
 * This function removes aol_ prefix from the given input.
 * 
 * Marked for removal. us aol_remove_prefix() instead.
 * 
 * @param string $value string
 * @return string string
 */
function remove_aol_prefix($value){
    if(strpos($value, 'aol_') !== FALSE) $value = substr($value, 4);
    return $value;
}

/**
 * This function removes aol_ prefix from the given input.
 * 
 * @param string $value string
 * @return string string
 */
function aol_remove_prefix($value){
    if(strpos($value, 'aol_') !== FALSE) $value = substr($value, 4);
    return $value;
}

/**
 * This function returns aol_ prefix
 * 
 * @param string $value
 * @param string  $key
 * @return string aol_
 */
function aol_ad_prefix(&$value, $key){
    if(!strpos('aol_', $value)) $value = 'aol_'.$value;
}

/**
 * Returns Registered AOL Ad Types.
 *
 * @since 1.8
 * This function do not accept any parameters.
 * @return array Array of ad types
 */
function get_aol_ad_types(){
    $types = aol_ad_types();
    $types = array_keys($types);
    array_walk($types, 'aol_ad_prefix');
    return $types;
}

function aol_manager_capability(){
    return 'edit_applications';
}

/**
 * Returns Filters list. It do not accept any parameters.
 *
 * Filters are shown on the front-end when [aol] shortcode is used.
 *
 * @since 1.7
 *
 * @return array
 */
function aol_ad_filters(){
    $filters = get_option_fixed('aol_ad_filters', array());
    if(function_exists('pll_register_string')){
        foreach($filters as $filter): 
            pll_register_string('Ad Filters', sanitize_text_field( $filter['singular'] ), 'apply-online' );
        endforeach;        
    }
    return apply_filters('aol_ad_filters', $filters);
}

function aol_app_statuses(){
    $filters = array('pending' => esc_html__('Pending', 'ApplyOnline'), 'rejected'=> esc_html__('Rejected', 'ApplyOnline'), 'shortlisted' => esc_html__('Shortlisted', 'ApplyOnline'));
    $statuses = array_merge($filters, get_option_fixed('aol_custom_statuses', array()));
    return apply_filters('aol_app_statuses', $statuses);
}

/*
 * Change post status similsar to its terms. 
 *  
 */
function aol_set_object_terms($object_id, $tt_id, $taxonomy){
    if($taxonomy == 'added_term_relationship') wp_update_post(array('ID' => $object_id, 'post_status' => $tt_id[0]));
}
//add_action('set_object_terms','aol_set_object_terms', 10, 3);

/*
 * Return active status of current Application(CPT)
 * 
 */
function aol_app_statuses_active(){
    $statuses = aol_app_statuses();
    $active = apply_filters('aol_app_active_statuses', get_option_fixed('aol_app_statuses', $statuses));
    foreach ($statuses as $key => $val){
        if(!in_array(sanitize_key($key), $active)) unset($statuses[$key]);
    }
    return $statuses;
}

function aol_ad_current_filters(){
    $filters = aol_ad_filters();
    $set_filters = get_option_fixed('aol_ad_filters', array());
    foreach ($filters as $key => $val){
        if(!in_array(sanitize_key($key), $set_filters)) unset($filters[$key]);
    }
    return $filters;
}

function aol_ad_cpt_filters($cpt){
    $cpt = remove_aol_prefix($cpt);
    $filters = aol_ad_filters();
    $types = get_option_fixed(
            'aol_ad_types', 
            array(
                'ad' => array(
                    'singular' => esc_html__('Ad','ApplyOnline'), 
                    'plural' => esc_html__('Ads','ApplyOnline'), 
                    'filters' => array_keys( aol_ad_filters() )
                    )
                )
            );
    
    $cpt_filters = isset($types[$cpt]['filters']) ? (array)$types[$cpt]['filters']: array();
    
    //Remove filters that are not sett to the ad.
    foreach ($filters as $key => $val){
        if(!in_array(sanitize_key($key), $cpt_filters)) unset($filters[$key]);
    }
    return $filters;
}

function aol_sanitize_taxonomies($taxonomies){
    $tax_keys = array();
    foreach($taxonomies as $key => $tax){
        $tax_keys[] = 'aol_ad_'.sanitize_key($key);
    }
    return $tax_keys;
}

if ( ! function_exists( 'aol_set_current_menu' ) ) {

    function aol_set_current_menu( $parent_file ) {
        global $submenu_file, $current_screen, $pagenow;

        # Set the submenu as active/current while anywhere in your Custom Post Type (nwcm_news)
        if ( $current_screen->post_type == 'aol_ad' ) {
            if ( $pagenow == 'edit-tags.php' or $pagenow == 'term.php' ) {
                $submenu_file = 'edit-tags.php?taxonomy='.str_replace('edit-', '', $current_screen->id).'&post_type=' . $current_screen->post_type;
                $parent_file = 'aol-settings';
            }
        }
        return $parent_file;
    }
    add_filter( 'parent_file', 'aol_set_current_menu' );
}

function aol_array_check($array){
    if(!is_array($array)) $array = array();
    return $array;
}

function aol_sanitize_filters($types){
    foreach($types as $key => $type){
        $types[$key] = array_merge(array('filters' => null), $type);
    }
    return $types;
}

function aol_email_content_type(){
            return 'text/html';
        }

function aol_links_shortcode( $atts ) {
	$a = shortcode_atts( array(
            'href' => esc_html__('Link is missing', 'ApplyOnline'),
            'title' => esc_html__('Title is missing', 'ApplyOnline'),
            'target' => '_blank',
            
	), $atts );

        return '<a href="'.$a['href'].'" target="'.$a['target'].'">'.$a['title'].'</a>';
	//return "foo = {$a['foo']}";
}

function aol_form_field_check($fields){
    foreach($fields as $field):
        isset($field) ? $field : NULL;
    endforeach;
}
        
/*
 * @field   array   
 * $field
 */
function aol_form_generator($fields, $fieldset = 0, $prepend = NULL, $post_id = 0){
    $form_output = NULL;
    foreach($fields as $field):
        //$value = isset($field['value']) ? $field['value'] : NULL;
        $value = isset($field['val']) ? sanitize_text_field( $field['val'] ) : NULL;
        $placeholder   = isset($field['placeholder']) ? 'placeholder="'.sanitize_text_field( $field['placeholder'] ).'"' : '';
        $class         = isset($field['class']) ? esc_attr( $field['class'] ) : NULL;

        //Used by Tracker add-on to display saved value.
        //$value = apply_filters('aol_form_field_value', $value, $field['key'], $field['type'], $post_id);
        $type = esc_attr($field['type']);

        $field_key = sanitize_key($field['key']);
        
        $required = $attributes = $wrapper_class = NULL;
        if( isset( $field['required'] ) AND $field['required'] === '1' ){
            $required = '<span class="required-mark">*</span>';
            $attributes = 'required aria-required="true"';
            $class .= ' required';
            $wrapper_class = ' required';
        }

        $label = isset($field['label']) ? sanitize_text_field( $field['label'] ) : sanitize_text_field( str_replace('_',' ',$field['key']) );
        $description = isset($field['description']) ? sanitize_text_field( $field['description'] ) : NULL;
        $text = isset($field['text']) ? sanitize_textarea_field( $field['text'] ) : $description;
        $style = (isset($field['height']) and (int)$field['height'] > 0) ? 'height:'.(int)$field['height'].'px' : NULL;
        if(isset($field['limit']) AND !empty($field['limit'])){
            $limit = (int)$field['limit'];
            $limit_output = '<div class="the-count"><span class="current">'. strlen($value).'</span><span class="maximum">/'.$limit.'</span></div>';
        } else {
            $limit = $limit_output = NULL;
        }
        $wrapper_start = '<div class="form-group aol-form-group aol-'.$type.$wrapper_class.'" data-field="'.$prepend.$field_key.'"><label for="'. $field_key.'">'.$required.$field['label'].'</label>';
        //do_action('aol_form_before_input_field', $field, $post_id);
        $wrapper_end = '<small id="help'.$field_key.'" class="help-block">'.$description.'</small></div>';

        switch ($type){
            case 'paragraph':
                //$field['description'] = empty($field['description']) ? $label : $field['description'];
                add_shortcode('link', 'aol_links_shortcode');
                $form_output .= $wrapper_start.'<div id="'.$field_key.'" class="'.$class.' aol-textbox" style="'.$style.'">'. nl2br($text).'</div>'.$wrapper_end;
                remove_shortcode('link');
                break;

            case 'date':
                $form_output .= $wrapper_start. '<input type="text" '.$placeholder.' name="'.$prepend.$field_key.'" class="form-control datepicker '.$class.'" id="'.$prepend.$field_key.'" value="'.$value.'"  placeholder="'.esc_attr__('e.g.', 'ApplyOnline').' '.current_time(get_option('date_format')).'" '.$attributes.'  aria-describedby="help'.$field_key.'" >'.$wrapper_end;
                break;

            case 'dropdown':
                $form_output .= $wrapper_start.'<div id="'.$field_key.'" ><select name="'.$prepend.$field_key.'" id="'.$prepend.$field_key.'" class="form-control '.$class.'" id="'.$prepend.$field_key.'" '.$attributes.' aria-describedby="help'.$field_key.'">';
                foreach ($field['options'] as $key => $option) {
                    $checked = ($option == $value) ? 'selected="selected"': NULL; 
                    $form_output .= '<option class="" value="'.esc_attr($key).'" '.$checked.' >'. sanitize_text_field($option).' </option>';
                }
                $form_output .= '</select><span id="help'.$field_key.'" class="help-block">'.$description.'</span></div></div>';
                break;

            case 'radio':
                $form_output .= $wrapper_start. '<div id="'.$field_key.'" class="'.$class.'">';
                $i=0;
                $selection = !empty($field['preselect']) ? $field['preselect']  : ''; 
                foreach ($field['options'] as $key => $option) {
                    $checked = NULL;
                    if(empty($value) and ($i == 0 and $selection === '1' )) $checked = 'checked' ;
                    elseif($option == $value) $checked = 'checked';
                    $form_output .= '<label><input type="'.$type.'" name="'.$prepend.$field_key.'" class="aol-radio '.$field_key.' " value="'.$key.'" '.$checked.' > '.sanitize_text_field($option) .' &nbsp; &nbsp; </label>';
                    $i++;
                }
                $form_output .= '</div>'.$wrapper_end;
                break;
                
            case 'checkbox':
                $form_output .= $wrapper_start. '<div id="'.$field_key.'" class="'.$class.'" >';
                $i=0;
                foreach ($field['options'] as $key => $option) {
                    $checked = NULL;
                    if(!empty($value) AND in_array($option, $value)) $checked = 'checked';
                    $form_output .= '<label><input type="'.$type.'" name="'.$prepend.$field_key.'[]" class="aol-checkbox '.$field_key.' " value="'.$key.'" '.$checked.'> '.sanitize_text_field($option) .' &nbsp; &nbsp; </label>';
                    $i++;
                }
                $form_output .= '</div>'.$wrapper_end;
                break;
                /*
            case 'separator':
                $is_multi_steps = get_option('aol_multistep');
                $hide_section = $back = $multistep_output = NULL;
                if($is_multi_steps){
                    if($fieldset > 1) $back = '<button class="aol_multistep btn btn-default btn-previous pull-left" data-load="back"><span class="dashicons dashicons-arrow-left-alt2"></span> '.esc_html__('Previous', 'ApplyOnline').'</button>';
                    if($fieldset > 0){
                        $hide_section   = 'style="display:none;"';
                    }
                }
                
                $multistep_output = $back.'<button class="aol_multistep btn btn-default btn-next pull-right" data-load="next">'.esc_html__('Next', 'ApplyOnline').' <span class="dashicons dashicons-arrow-right-alt2"></span></button>';
                if($fieldset > 0)   $form_output.=  $multistep_output.'</fieldset>';

                $form_output.=  "<fieldset $hide_section><legend>".sanitize_text_field($label).'</legend>';
                $form_output.=  '<small id="help'.$field_key.'" class="section-info">'.sanitize_text_field($field['description']).'</small>';
                $fieldset++;
                break;
                 * 
                 */
            case 'separator':
                if($fieldset == 1) $form_output .=  '</fieldset>';
                $form_output .= '<fieldset><legend>'.$label.'</legend>';
                $form_output .= '<small id="help'.$field_key.'" class="help-block">'.$description.'</small>';
                $fieldset = 1;
                break;
                
            case 'hidden':
                $form_output .= '<input type="'.$type.'" '.$placeholder.' name="'.$prepend.$field_key.'" class="form-control '.$class.'" id="'.$field_key.'" value="'.$value.'" '.$attributes.'>';
                break;

            case 'text_area':
                $form_output .= $wrapper_start. '<textarea name="'.$prepend.$field_key.'" '.$placeholder.' class="form-control '.$class.'" id="'.$prepend.$field_key.'" '.$attributes.' aria-describedby="help'.$field_key.'" maxlength="'.$limit.'">'. $value.'</textarea>'.$limit_output.$wrapper_end;
                break;

            //case 'text':
            //case 'email':
            //case 'file':
            //case 'number':
            default:
                $form_output .= $wrapper_start. '<input type="'.$type.'" '.$placeholder.' name="'.$prepend.$field_key.'" class="form-control '.$class.'" id="'.$prepend.$field_key.'" value="'. $value.'" maxlength="'.$limit.'" '.$attributes.'>'.$limit_output.$wrapper_end;
                break;
        }
    endforeach;
    //if($fieldset > 0) $form_output.=  '<button class="aol_multistep btn btn-default btn-previous pull-left '.get_option('aol_multistep_button_classes').'" data-load="back"><span class="dashicons dashicons-arrow-left-alt2"></span> '.esc_html__('Previous', 'ApplyOnline').'</button></fieldset>';
    if($fieldset == 1) $form_output .= '</fieldset>';

    return $form_output;//ob_get_clean();
}

/*
 * returns domain name to use into email addresses.
 */
function aol_get_domain(){
    // Get the site domain and get rid of www.
    $sitename = strtolower( $_SERVER['SERVER_NAME'] );
    if ( substr( $sitename, 0, 4 ) == 'www.' ) {
        $sitename = substr( $sitename, 4 );
    }
    
    return $sitename;
}

/**
 * Returns array of a received application form data.
 *
 * @since    1.9.92
 * @access   public
 * @var      string    $post    Post Object.
 * @return   array     Application data.
 */
function aol_application_data($post){
    $keys = get_post_custom_keys( $post->ID );    
    if( in_array('ad_transcript', $keys) ){
        return aol_application_data_v2($post, $keys);
    }
    
    $keys_order = get_post_meta($post->post_parent, "_aol_fields_order", TRUE);
    //print_rich($keys); print_rich($keys_order); print_rich(array_mer($keys_order, $keys));
    
    //getting fields order from the order meta, but it may have missed few older form fields.
    //$keys_common = array_intersect($keys_order, $keys);
    
    //Recovering older form fields.
    $keys = array_merge($keys_order, array_diff($keys, $keys_order));

    //Preserving ex application fields that might be changed with ad modification
    //$keys = array_merge($keys_order, $keys);
    $parent = get_post_meta( $post->post_parent );
    $data = array();
    foreach ( $keys as $key ):
        if ( substr ( $key, 0, 9 ) == '_aol_app_' ){

            $val = get_post_meta ( $post->ID, $key, true );
            //Support to previuos versions where only URL was saved in the post meta.
            //if ( !filter_var($val, FILTER_VALIDATE_URL) === false ) $val = '<a href="'.$val.'" target="_blank">'.esc_html__('View','ApplyOnline').'</a> | <a href="'.esc_url ($val).'" >'.esc_html__('Download','ApplyOnline').'</a>';

            if(is_array($val)){
                //If the outputs is file attachment
                if(isset($val['file']) AND isset($val['type'])){
                    $val = '<a href="'. aol_crypt($val['file']).'" target="_blank">'.esc_html__('Attachment','ApplyOnline').'</a>';                    
                } elseif(isset($val['url']) AND isset($val['type'])){
                    $val = '<a href="'.esc_url($val['url']).'" target="_blank">"'.esc_html__('Attachment','ApplyOnline').'"</a>';                    
                } 

                //If output is a radio or checkbox.
                else $val = implode(', ', $val);
            } else {
                $val = sanitize_text_field($val);
            }
            $parent[$key][0] = isset($parent[$key][0]) ? maybe_unserialize($parent[$key][0]) : 'continue';
            $label = isset($parent[$key][0]['label'])? $parent[$key][0]['label'] : str_replace( '_', ' ', substr ( $key, 9 ) ); 
            $type = isset($parent[$key][0]['type']) ? $parent[$key][0]['type'] : NULL;
            $data[] = array('label' => $label, 'value' => $val, 'type' => $type);
        }
    endforeach;
    return $data;
}

function aol_application_data_v2($post, $keys){
    $meta = get_post_meta($post->ID, "ad_transcript", TRUE);
    foreach($meta as $key => $val){
        $meta[$key] = maybe_unserialize($val);
    }
    
    $keys_order = $meta['_aol_fields_order'];
    
    $data = array();
    foreach ( $keys_order as $key ):
        if ( substr ( $key, 0, 9 ) == '_aol_app_' ){

            $key = sanitize_key($key);
            $val = get_post_meta ( $post->ID, $key, true );
            
            //If the outputs is a file attachment
            switch ($meta[$key]['type']){
                case 'file':
                    $val = empty($val) ? NULL: aol_crypt($val['file']);
                    break;

                case 'checkbox':
                    $val = empty($val) ? NULL: implode(', ', $val);
                    break;
                
                case 'paragraph':
                    $val = empty($val) ? $meta[$key]['text'] : $val;
                    break;
                
                default :
                    $val  = empty($val) ? NULL: $val;
            }
            $data[] = array(
                'label' => isset($meta[$key]['label']) ? $meta[$key]['label'] : str_replace( '_', ' ', substr ( $key, 9 ) ),
                'value' => $val,
                'type' => $meta[$key]['type']);
        }
    endforeach;
    return $data;
}

function aol_application_table($post, $classes = 'aol-table widefat striped'){
    ob_start();
    ?>
<table class="<?php echo sanitize_text_field($classes); ?>">
        <?php
        $rows = aol_application_data($post);
        foreach ( $rows as $row ):
                echo '<tr>';
                    echo '<td>' . sanitize_text_field($row['label']) . '</td>';
                    echo '<td>';
                    if(empty($row['value'])) {
                        echo '<i class="text-secondary">- '.esc_html__('not provided', 'ApplyOnline').' -</i>';
                    } else {
                        echo ( $row['type'] == 'file' ) ? '<a href="'.esc_url(get_option('siteurl').'?aol_attachment='.$row['value'] ).'" target="_blank">'.esc_html__('Attachment','ApplyOnline').'</a>' : sanitize_textarea_field($row['value']);
                    }
                    echo '</td>';
                echo '</tr>';
        endforeach;;
        ?>
    </table>
    <?php
    return ob_get_clean();
}

/**
* Encrypt and decrypt
*
* @param string $string string to be encrypted/decrypted
* @param string $action what to do with this? e for encrypt, d for decrypt
*/
function aol_crypt( $string, $action = 'e' ) {
// you may change these values to your own
    $secret_key = wp_salt('my_simple_secret_key');
    $secret_iv = wp_salt('my_simple_secret_iv');
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $key = hash( 'sha256', $secret_key );
    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
    if( $action == 'e' ) {
    $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
    }
    else if( $action == 'd' ){
    $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
    }
    return $output;
}

function print_rich($var){
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

function get_aol_ad_options(){
    return apply_filters('aol_ad_options', array());
}

function get_aol_settings(){
    $default = array(
        'type' => 'text',
        'key' => NULL,
        'secret' => FALSE,
        'placeholder' => NULL,
        'value' => NULL,
        'label' => NULL,
        'helptext' => NULL,
        'icon' => NULL,
        'class' => NULL,
        'sanitize_callback' => 'sanitize_text_field'
        );
    $settings = apply_filters('aol_settings', array());
    foreach($settings as $key => $setting){
        //if( isset($setting['type']) AND $setting['type']=='textarea' AND !isset($setting['sanitize_callback']) ) $setting['sanitize_callback'] = 'sanitize_textarea_field';
        if( !isset($setting['value']) ) $setting['value'] = get_option($setting['key']);
        $settings[$key] = array_merge($default, $setting);
    }
    return $settings;
}

/**
 * Marked as deprecated. Use aol_mail_header() instead.
 * 
 * @param array $extra_headers
 * @return array Headers required by mail functions.
 */
function aol_from_mail_header($extra_headers = array()){
    // Get the site domain and get rid of www.
    $sitename = strtolower( $_SERVER['SERVER_NAME'] );
    if ( substr( $sitename, 0, 4 ) == 'www.' ) {
        $sitename = substr( $sitename, 4 );
    }
    $from_email = 'do-not-reply@' . $sitename;
    
    //Removed since 2.5.4
    //$headers = 'Content-Type: text/html'."\r\n";
    //$headers .= wp_specialchars_decode('From: '.get_bloginfo('name')." <$from_email>")."\r\n";
    //$headers .= implode(",\r\n", $extra_headers);
    
    //Introduced in  2.5.4
    $headers = array('Content-Type: text/html', "From: ". wp_specialchars_decode(get_bloginfo('name'))." <$from_email>");
    
    return array_merge($headers, $extra_headers); 
    
    //return array('Content-Type: text/html', "From:". wp_specialchars_decode(get_bloginfo('name'))." <$from_email>", implode(',', $extra_headers));
}

/**
 * 
 * @return type
 */
function aol_mail_header($extra_headers = array()){
    return aol_from_mail_header($extra_headers);
}

function aol_integration(){
    return apply_filters( "aol_integration", array() );
}

/*Quick hack for a fatal error on Application Editor*/
if( !function_exists('has_blocks') ){
    function has_blocks( $post = null ) {
	if ( ! is_string( $post ) ) {
		$wp_post = get_post( $post );
		if ( $wp_post instanceof WP_Post ) {
			$post = $wp_post->post_content;
		}
	}

	return false !== strpos( (string) $post, '<!-- wp:' );
    }
}

function aol_mail_footer(){
        $message  = "\n\n";
        $message  .= "Thank you";
        $message .= "\n";
        $message .= get_bloginfo('name')."\n";
        $message .= site_url()."\n";
        $message .= "------\n";
        $message .= "Please do not reply to this system generated message.";
    return $message;
}

if( !function_exists('unregister_post_type') ){
    function unregister_post_type( $post_type ) {
        global $wp_post_types;
        if ( isset( $wp_post_types[ $post_type ] ) ) {
            unset( $wp_post_types[ $post_type ] );
            flush_rewrite_rules();
            return true;
        }
    }
}

/**
 * @since   2.1
 * 
 * This function maps each element of multidimension array against a function provided as 1st parameter. 
 * Very helpful to sanitize whole array with a given function. * 
 * 
 * @param type $arr
 * @param type $func
 * @return type array
 */
function aol_sanitize_array( $arr, $func = 'sanitize_text_field' ){
    if( !(is_array($arr) or is_object($arr)) ) return sanitize_text_field($arr);

    $newArr = array();
    foreach( $arr as $key => $value )
    {
        $newArr[sanitize_key($key) ] = ( is_array( $value ) ? aol_sanitize_array( $value, $func ) : ( is_array($func) ? call_user_func_array($func, $value) : $func( $value ) ) );
    }

    return $newArr;
}

/**
 * 
 * @param type $option Required.
 * @param type $consider_empty
 * @return type
 */
function aol_empty_option_alert($option = NULL, $consider_empty = NULL){
    if(empty($option)) return;
    
    $val = sanitize_textarea_field(get_option($option));
    if(empty($option) or empty($val) or $val == $consider_empty) echo '<span class="dashicons dashicons-warning"></span>';
}

/**
* Case in-sensitive array_search() with partial matches
*
* @param string $needle   The string to search for.
* @param array  $haystack The array to search in.
*
* @author Bran van der Meer <branmovic@gmail.com>
* @since 29-01-2010
*/
function aol_array_find($needle, array $haystack){ 
    foreach ($haystack as $key => $value) {
        if (stripos($value, $needle) !== FALSE) {
            return $key;
        }
    }
    return false;
}

/**
 * This function recursively map an array.
 * 
 * @param type $func Function name
 * @param type $arr array to be mapped recursively
 * @return type
 */
function aol_array_map_r( $func, $arr ){
    $newArr = array();

    foreach( $arr as $key => $value ){
        $newArr[ $key ] = ( is_array( $value ) ? aol_array_map_r( $func, $value ) : ( is_array($func) ? call_user_func_array($func, $value) : $func( $value ) ) );
    }

    return $newArr;
}

//Pretty print for objects & arrays. This function is used for debuggin only for developers.
function aol_pretty_print($arr){
    $arr = is_object($arr) ? (array)$arr : maybe_unserialize($arr);
    $arr = is_object($arr) ? (array)$arr : $arr;
    $retStr = '<ul>';
    if (is_array($arr)){
        foreach ($arr as $key=>$val){
            $val = is_object($val) ? (array)$val : maybe_unserialize($val);
            $val = is_object($val) ? (array)$val : $val;
            if (is_array($val)){
                $retStr .= '<li>' . $key . ' => ' . aol_pretty_print($val) . '</li>';
            }else{
                $retStr .= '<li>' . $key . ' => ' . $val . '</li>';
            }
        }
    }
    $retStr .= '</ul>';
    echo $retStr;
}

function is_aol_admin_screen(){
    $screen_id = get_current_screen()->id;
    $ad_types = get_aol_ad_types();
    
    return (in_array($screen_id, $ad_types) OR strstr($screen_id, 'aol-settings') OR strstr($screen_id, 'aol_application') ) ? TRUE : FALSE;
}

function aol_sanitize_text_field($str, $strict = FALSE, $allowed = NULL){
    $str = sanitize_text_field($str);
    preg_replace('[^a-z0-9\s]/i', "", $str);
}
function aol_test(){
    echo 'Alhamdulillah, this is working';
}
