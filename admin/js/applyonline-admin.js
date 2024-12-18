(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-specific JavaScript source
	 * should reside in this file.
	 *
	 * Note that this assume you're going to use jQuery, so it prepares
	 * the $ function reference to be used within the scope of this
	 * function.
	 *
	 * From here, you're able to define handlers for when the DOM is
	 * ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * Or when the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and so on.
	 *
	 * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
	 * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
	 * be doing this, we should try to minimize doing that in our own work.
	 */ 
        $(document).on( 'click', '.aol .notice-dismiss', function() {
            jQuery.ajax({
                url: ajaxurl,
                data: {
                    action: 'aol_dismiss_notice'
                }
            })
        });

        
        var i = 0;
        $(document).ready(function(){
            /*Select2 API*/
            if(typeof $.fn.select2 === 'undefined') return;
            $('.aol_select2_filter').select2({
                placeholder: "Select here.",
                //selectOnClose: true
            });
            $('.aol-select2').select2({
                placeholder: "Please select an option",
            });
            
            $('.aol-select2-tags').select2({
                placeholder: "Please select an option",
                tags: true,
                tokenSeparators: [',', ' ']
            });
            /*End Select2 API*/
            
                        
            $('.datepicker').datepicker({
                minDate:    +1,
                dateFormat: 'dd-mm-yy',
                changeMonth: true,
                changeYear: true
            });
            $("#app_form_fields, .app_form_fields").sortable({
              placeholder: "ui-state-highlight",
              update: function( event, ui ) {
                  $('#aol_keys_order_wrapper').empty(); //
                  $( '#app_form_fields tr' ).each(function(){
                      var field = $(this).data( "id" );
                      $('#aol_keys_order_wrapper').append('<input type="hidden" id="aol_fields_order" name="_aol_fields_order[]" value="'+field+'" />');
                  });
              }
            });
                
            /*Ad Types Settings*/
            $('#ad_aol_type').click(function(){
                var fieldNameRawSingular = aol_sanitize_text_field( $('#ad_type_singular').val() ); // Get Raw value.
                var fieldNameRawPlural = aol_sanitize_text_field( $('#ad_type_plural').val() ); // Get Raw value.
                var fieldNameRawDesc = aol_sanitize_text_field( $('#ad_type_description').val() ); // Get Raw value.
                
                var fieldNameSingular = aol_sanitize_key(fieldNameRawSingular);


                if(fieldNameSingular != '' && fieldNameRawPlural != ''){
                    $('#ad_types').append('<li>'+fieldNameRawSingular+' ('+fieldNameRawPlural+') '+fieldNameRawDesc+' <input type="hidden" class="'+fieldNameSingular+' aol_ad_type" name="aol_ad_types['+fieldNameSingular+'][singular]" value="'+fieldNameRawSingular+'" /><input type="hidden" name="aol_ad_types['+fieldNameSingular+'][plural]" value="'+fieldNameRawPlural+'" /><input type="hidden" name="aol_ad_types['+fieldNameSingular+'][description]" value="'+fieldNameRawDesc+'" /> <p><i>Save changes to get shortcode & filters. </i></p> <div class="button button-small aol-remove button-danger">Delete</div></li>');
                    i++;
                    $('#ad_type_singular').val(''); // Get Raw value.
                    $('#ad_type_plural').val(''); // Get Raw value.
                    $('#ad_type_description').val(''); // Get Raw value.
                }
                else{
                    $('#adapp_name').css('border','1px solid #F00');
                }

            });
            
            /*Form Builder row removal*/
            $('#ad_types').on('click', 'li .aol-remove',function(){
                $(this).parentsUntil('ol', 'li').remove();
            });
            
            /*Form Builder default template*/
            $('#ad_types').find('.default').click(function(){
                return false;
            });
                
            /*Ad editor Scripts*/

            /*Form Builder Application Field Type change for new Field only*/
            $('.aol_fields').click(function(){
               //var fieldType=$(this).val();
               var fieldType = $(this).data('id');
               $('.aol_form').css('display','block');
               if(fieldType == 'checkbox' || fieldType == 'dropdown' || fieldType == 'radio'){
                    $('.aol_form_options').show();
                 }
                 else{
                    $('.aol_form_options').hide();
                 }

                 if(fieldType == 'checkbox' || fieldType == 'dropdown' || fieldType == 'radio' || fieldType == 'text_area' || fieldType == 'dropdown' || fieldType == 'file' || fieldType == 'separator' || fieldType == 'paragraph'  ){
                    $('.aol_placeholder').hide();
                 }
                 else{
                     $('.aol_placeholder').show();
                 }
            });
            
                       
            //Form Builder Required field Option
            $('.aol_checkbox').on('click', function(e){
               // e.preventDefault();
                if( $(this).prop('checked') ) {
                    $(this).val('1');
                } else {
                    $(this).val('0');
                }
                //tb_remove();
            });
            
            /*
            $('.aol_fields').on('click', function(e){
                var field   = $(this).data('id');
                $('#adapp_field_type').val(field).change();
                $('.aol_form').css('display','block');
            });
            */
//            
//            
//            $('.click_test').on('click', function(e){
//               var button           = aol_arrays('button');
//               var label            = aol_arrays('label');
//               var placeholder      = aol_arrays('placeholder');
//               var requied          = aol_arrays('requied');
//               alert(label+placeholder+requied+button);
//               $('.aol_form').html(label+placeholder+requied+button);
//               $('.textfield').trigger( "click" );
//               
//            });
            $('.textfield-poup').on('click', function(e){
                $('.aol_form').css('dispaly','none');
            });
            
            /* Form Builder (Group Fields): Add new row. */
            $('.addField').on('click', function(e){
                e.preventDefault();
                var tempID = aol_sanitize_text_field( $(this).data('temp') );
                var wrapper = $(this).closest('.aol_form');

                //var fieldNameRaw = $('#adapp_name').val(); // Get Raw value.
                var fieldNameRaw = aol_sanitize_text_field( wrapper.find('.adapp_name').val() ); // Get Raw value.
                //var fieldName = md5(fieldNameRaw)
                //var fieldID = aol_sanitize_key(fieldNameRaw); //Replace white space with _.
                var fieldID = aol_sanitize_key( wrapper.find('#adapp_uid').val() );
                if( tempID == '' ) var fieldID = '_aol_app_'+fieldID;
                else if( tempID == 'new' ) var fieldID = 'new[_aol_app_'+fieldID+']';
                else if( tempID != '' ) var fieldID = tempID+'[_aol_app_'+fieldID+']';
                var fieldType = aol_sanitize_key( wrapper.find('input[name="aol_type"]').val() ); 
                var fieldPlaceholder = aol_sanitize_text_field( wrapper.find('.adapp_placeholder').val() );
                var fieldClass = aol_sanitize_text_field( wrapper.find('.adapp_class').val() );
                var fieldFileTypes = aol_sanitize_text_field( wrapper.find('.adapp_file_types').val() );
                var fieldFileSize = parseInt( wrapper.find('.adapp_file_max_size').val() );
                var required = parseInt( wrapper.find('.adapp_required').val() );
                var notify_email = aol_sanitize_text_field( wrapper.find('.adapp_notification').val() );
                var fieldOptions = aol_sanitize_text_field( wrapper.find('.adapp_field_options').val() );
                var fieldDesccription = aol_sanitize_text_field( wrapper.find('.adapp_field_help').val() );
                var fieldText = aol_sanitize_text_field( wrapper.find('.adapp_text').val() );
                var fieldTextHeight = aol_sanitize_text_field( wrapper.find('.adapp_text_height').val() );
                var fieldLimit = parseInt( wrapper.find('.adapp_limit').val() );
                if( notify_email == '1'){
                    var notify_checked ='checked';
                }else {
                    var notify_checked ='';
                }
                if( required == '1'){
                    var checked_item ='checked';
                }else {
                    var checked_item ='';
                }
                
                var  aol_defult_selection = '';
                if( wrapper.find(".adapp_preselect").prop('checked') ) {
                   var  aol_defult_selection = 'checked';
                } 
                //Highlight culprut
                $('#adapp_name').css('border','1px solid #f00');
                //var fieldTypeHtml = wrapper.find('.adapp_field_type').html();
                if(fieldNameRaw == '' || fieldType == '' || fieldID == '' || fieldID == '_aol_app_'){
                     wrapper.find('.adapp_name, .adapp_uid').css('border','1px solid #F00');
                    //if(fieldType == '') wrapper.find('#adapp_field_type').css('border','1px solid #F00');
                }
                else{
                    var select_value = fieldType;
                    var start_html  ='<tr class="'+fieldID+'" data-id="'+fieldID+'">\n\
                                        <td><span class="dashicons dashicons-menu"></span> <label for="'+fieldID+'"> '+fieldNameRaw+'</label></td>\n\
                                        <td>\n\
                                                <div class="aol-edit-form"><a href="#TB_inline?&width=400&height=550&inlineId='+fieldID+'" title="Edit Field" class="thickbox dashicons dashicons-edit"></a><span class="dashicons dashicons-no aol-remove" title="Delete" ></span></div>\n\
                                                <div style="display:none" id="'+fieldID+'">\n\
                                                    <div class="aol_form" data-id="'+fieldID+'">\n\
                                                        <div class="form-group"><label>Unique ID</label><input type="text" disabled="" value="'+fieldID+'"></div>\n\
                                                        <div class="form-group">\n\
                                                            <label>Lable</label>\n\
                                                            <input class="adapp_label" name="'+fieldID+'[label]" value="'+fieldNameRaw+'" type="text">\n\
                                                        </div>\n\
                                                        <input type="hidden" name="'+fieldID+'[type]" value="'+fieldType+'">';
                    var end_html = '<div><button class="button button-primary aol-save-form" data-temp=""> Update Field </button></div></div></td></tr>';
                    //var selected_name   = '';
                    var placholder_html = '<div class="aol_placeholder"><label>Placeholder</label><input name="'+fieldID+'[placeholder]" class="_aol_field_placeholder" value="'+fieldPlaceholder+'" type="text"></div>';
                    var help_html   = '<div><label>Help Text</label><input type="text" class="adapp_field_help adapp_field_description" name="'+fieldID+'[description]" value="'+fieldDesccription+'"  /></div>';
                    var text_html   = '<div><label>Text</label><textarea class="adapp_text" name="'+fieldID+'[text]" >'+fieldText+'</textarea></div><div><label>Text Box Height</label><input type="number" class="adapp_text_height" name="'+fieldID+'[height]" value="'+fieldTextHeight+'" /></div>';
                    var class_html  = '<div><label>CSS Classes</label><input name="'+fieldID+'[class]" value="'+fieldClass+'" type="text"></div>';
                    var option_html = '<div><label>Options</label><input type="text" class="adapp_field_options" name="'+fieldID+'[options]" value="'+fieldOptions+'" placeholder="Option1, Option2, Option3" /> </div>';
                    var char_limit = '<label>Charcter Limit</label><input class="adapp_charchter_limit" name="'+fieldID+'[limit]" value="'+fieldLimit+'" type="number" placeholder="No Limit">';                    
                    var defult_selection = '<div class="aol_add_field"><label>Preselect</label><input class="aol_preselect" name="'+fieldID+'[preselect]" type="checkbox" '+aol_defult_selection+' value="1" /> </div>';
                    var notify_html = '<div class="aol_notification aol_add_field"><label for="aol_notification">Notify This Email</label><input name="'+fieldID+'[notify]" class="adapp_notification" type="checkbox" value="'+notify_email+'"  '+notify_checked+' /> </div>';
                    var file_html = '<div><label>*Allowed File Types</label><input class="adapp_file_types" name="'+fieldID+'[allowed_file_types]" value="'+fieldFileTypes+'" value="jpg,jpeg,png,doc,docx,pdf,rtf,odt,txt" required /></div>';
                    var size_html = '<div><label>*Max Size Limit</label><input class="adapp_file_max_size" name="'+fieldID+'[file_max_size]" value="'+fieldFileSize+'" value="" required /></div>';
                    var required_html   = '<div class="aol_required"><label>Required Field</label><input class="required_option" '+checked_item+' name="'+fieldID+'[required]" value="'+required+'" type="checkbox"></div>';                                       
                    var output;
                    
                    switch (select_value){
                        case 'text':
                        case 'text_area':
                          output = start_html+help_html+placholder_html+class_html+char_limit+required_html+end_html;
                          break;
                        case 'number':
                        case 'date':
                          output = start_html+help_html+placholder_html+class_html+required_html+end_html;
                          break;
                        case 'email':
                          output = start_html+help_html+placholder_html+class_html+notify_html+required_html+end_html;
                          break;
                        case 'dropdown':
                        case 'checkbox':
                          output = start_html+help_html+option_html+class_html+required_html+end_html;
                          break;
                        case 'radio':
                          output = start_html+help_html+option_html+class_html+defult_selection+required_html+end_html;
                          break;
                        case 'file':
                          output = start_html+help_html+class_html+file_html+size_html+required_html+end_html;
                          break;
                        case 'separator':
                          output = start_html+help_html+class_html+end_html;
                          break;
                        case 'paragraph':
                          output = start_html+text_html+class_html+end_html;
                          break;
                        case 'url':
                          output = start_html+class_html+end_html;
                          break;
                     }
                    if( tempID != '' ){
                        $('#' + tempID + ' .app_form_fields').append(output);
                    }
                    else{
                        $('#app_form_fields').append(output);
                    }
                    
                    //$('#app_form_fields').append('<tr class="'+fieldID+'" data-id="'+fieldID+'"><td><label for="'+fieldID+'"><span class="dashicons dashicons-menu"></span> '+fieldNameRaw+'</label> &nbsp; </td><td class="aol-edit-form"><a href="#TB_inline?&width=400&height=550&inlineId='+fieldID+'" title="Edit Field" class="thickbox dashicons dashicons-edit"></a><span class="dashicons dashicons-trash aol-remove" title="Delete" ></span></td><td style="display:none" id="'+fieldID+'"><div class="aol_form"><div><label>Field Lable</label><input name="'+fieldName+'[label]" value="'+fieldNameRaw+'" type="text"></div><div class="aol_placeholder"><label>Placeholder</label><input name="'+fieldName+'[placeholder]" placeholder="Placeholder" class="_aol_field_placeholder" value="'+fieldPlaceholder+'" type="text"></div><div><label>Class</label><input placeholder="Class" name="'+fieldName+'[class]" value="'+fieldClass+'" type="text"></div><div><label>Required Options</label><input class="required_option" '+checked_item+' name="'+fieldName+'[required]" value="'+required+'" type="checkbox"></div><div><label>Type</label><select id="'+fieldID+'" class="adapp_field_type '+fieldID+'" name="'+fieldName+'[type]">'+fieldTypeHtml+'</select></div><div><label>Help Text</label><input type="text" class="adapp_field_help adapp_field_description" name="'+fieldName+'[description]" value="'+fieldDesccription+'" placeholder="Help text" /></div><div><label>Options</label><input type="text" class="adapp_field_options" name="'+fieldName+'[options]" value="'+fieldOptions+'" placeholder="Option1, Option2, Option3" /> </div><div><button type="button" class="button aol-save-form" data-temp=""><span class="dashicons dashicons-plus-alt"></span> Update Field </button></div></div></td></tr>');
                    $('#aol_keys_order_wrapper').append('<input type="hidden" id="aol_fields_order" name="_aol_fields_order[]" value="'+fieldID+'" />');
                    //$("."+fieldID+" ."+fieldType).attr('selected','selected');
                    $('#aol_new_form').find('select, input').val('');
                    // edit form field
                    $('.aol_fields').on('click', function(){
                        var fieldType = $(this).data('id');
                    });
                    $(this).parent('.aol_form').css('display','none');
                    $('.aol-selectors').find('.dashicons').parent('td').css('background', 'none');
                    $('.aol-selectors').find('.dashicons').parent('td').css('border', 'none');
                     $('#adapp_name').removeAttr('style');
                    tb_remove();
                }
                return false;
            });
            
            /* Form Builder: Edit an existing row */
            $('body').on('click', '.aol-save-form', function(e){
               //Change Row label as per new value.
                var rowID = $(this).closest('.aol_form').data('id');
                var rowLabel = $(this).closest('.aol_form').find('input[name="'+rowID+'[label]"]').val();
                
                $('#app_form_fields tr.'+rowID+' label').html( aol_sanitize_text_field(rowLabel) );
                
                //Close the Row Editor
                e.preventDefault();
                tb_remove();
            });
            /* Application Field Type change for existing fields (Code generated by PHP). (Deprecated)*/
            $('.aol_fields').on('click', function() {
                //$(this).children().prop("checked", true);
                
                var fieldType = $(this).data('id');
                $('input[name="aol_type"]').val(fieldType);
                
                switch(fieldType){
                    case 'separator':
                        $('.aol_add_field').css('display','none');
                        $('.aol_file_types').css('display','none');
                        $('.aol_file_max_size').css('display','none');
                        $('.aol_help_text').css('display','block');
                        break;
                    case 'paragraph':
                        $('.aol_add_field').css('display','none');
                        $('.aol_file_types').css('display','none');
                        $('.aol_file_max_size').css('display','none');
                        $('.aol_text').css('display','block');
                        $('.aol_text_height').css('display','block');
                        break;

                    case 'checkbox':
                        $('.aol_add_field').css('display','none');
                        $('.aol_file_types').css('display','none');
                        $('.aol_file_max_size').css('display','none');
                        $('.aol_help_text').css('display','block');
                        $('.aol_required').css('display','block');
                        $('.aol_options').css('display','block');
                        $('.aol_orientation').css('display', 'block');
                    break;
                    
                    case 'dropdown':
                        $('.aol_add_field').css('display','none');
                        $('.aol_file_types').css('display','none');
                        $('.aol_file_max_size').css('display','none');
                        $('.aol_help_text').css('display','block');
                        $('.aol_required').css('display','block');
                        $('.aol_options').css('display','block');
                        break;
                        
                    case 'radio':
                        $('.aol_add_field').css('display','none');
                        $('.aol_file_types').css('display','none');
                        $('.aol_file_max_size').css('display','none');
                        $('.aol_help_text').css('display','block');
                        $('.aol_required').css('display','block');
                        $('.aol_options').css('display','block');
                        $('.aol_preselect').css('display','block');
                        $('.aol_orientation').css('display', 'block');
                        break;

                    case 'text':
                    case 'text_area':
                        $('.aol_add_field').css('display','none');
                        $('.aol_file_types').css('display','none');
                        $('.aol_file_max_size').css('display','none');
                        $('.aol_help_text').css('display','block');
                        $('.aol_required').css('display','block');
                        $('.aol_placeholder').css('display','block');
                        $('.aol_limit').css('display','block');
                        break;

                    case 'date':
                    case 'number':
                    case 'url':
                        $('.aol_add_field').css('display','none');
                        $('.aol_file_types').css('display','none');
                        $('.aol_file_max_size').css('display','none');
                        $('.aol_help_text').css('display','block');
                        $('.aol_required').css('display','block');
                        $('.aol_placeholder').css('display','block');
                        break;

                    case 'email':
                        $('.aol_add_field').css('display','none');
                        $('.aol_file_types').css('display','none');
                        $('.aol_file_max_size').css('display','none');
                        $('.aol_help_text').css('display','block');
                        $('.aol_required').css('display','block');
                        $('.aol_placeholder').css('display','block');
                        $('.aol_notification').css('display','block');
                        break;

                    case 'file':
                        $('.aol_add_field').css('display','none');
                        $('.aol_help_text').css('display','block');
                        $('.aol_file_types').css('display','block');
                        $('.aol_file_max_size').css('display','block');
                        $('.aol_required').css('display','block');
                        break;
                }
            });
            /*Add Feature*/
            $('#addFeature').click(function(){
                var fieldNameRaw=$('#adfeature_name').val(); // Get Raw value.
                var fieldNameRaw = fieldNameRaw.trim();    // Remove White Spaces from both ends.
                var fieldName = fieldNameRaw.replace(" ", "_"); //Replace white space with _.

                var fieldVal = $('#adfeature_value').val();
                var fieldVal = fieldVal.trim();

                if(fieldName != '' && fieldVal!=''){
                    $('#ad_features').append('<li class="'+fieldName+'"><input type="text" name="_aol_feature_'+fieldName+'[label]" value="'+fieldNameRaw+'" placeholder="Label" > &nbsp;  <input type="text" name="_aol_feature_'+fieldName+'[value]" value="'+fieldVal+'" laceholder="Value" > &nbsp; <div class="button aol-remove"><span class="dashicons dashicons-remove"></span> Delete</div></li>');
                    $('#adfeature_name').val(""); //Reset Field value.
                    $('#adfeature_value').val(""); //Reset Field value.
                }
            });
            /*Remove Job app or ad Feature Fields*/
            $('.adpost_fields').on('click', 'li .aol-remove',function(){
                $(this).parent('li').remove();
            });
            $('#app_form_fields, .app_form_fields').on('click', 'tr td .aol-remove',function(){
                $(this).parentsUntil('tbody', 'tr').remove();
            });
            
            //Toggle Required
            $('.adpost_fields').on('click', 'tr .toggle-required', function(){
                var required = parseInt($(this).prev('input').val());
                $(this).prev('input').val(required == 1? 0:1);
                $(this).toggleClass('button-disabled');
            });
            /*END Ad editor Scripts*/
            
            /*Settings Tabs*/
            $('.aol-tab').click(function(){
                $('.aol-tab').removeClass('active');
                $(this).addClass('active');

                var target = $(this).data("id");
                $('.aol-settings').children('.aol-tab-data').hide();
                $("#"+target).show();
            });
            /*End Settings Tabs*/
            
            /*Template Tabs*/
            $('.aol-template-wrapper').children('.templateForm:first').show();
            $('.aol-template-tabs').children('.nav-tab').click(function(){
                $('.aol-template-tabs').find('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');

                var target = $(this).data("id");

                $('.aol-template-wrapper').children('.templateForm').hide();
                $("#"+target).show();
            });
            
            //Remove Template.
            $('.templateForm').on('click', '.aol-remove', function(){
                var target = $(this).closest('.templateForm').attr('id');
                $('#' + target).remove();
                //Remove relevant tab too.
                $('.aol-template-tabs [data-id="'+target+'"]').remove();
                $('.aol-template-tabs [data-id="templateFormNew"]').addClass('nav-tab-active');
                $("#templateFormNew").show();
            });
            /*End Template Tabs*/
            
            $('#aol_submission_default').click(function(event){
                event.preventDefault();
                $('#aol_submission_default_message').val(aol_admin.app_submission_message);
                $('#aol_submission_default_message').text(aol_admin.app_submission_message);
            });
            
            $('#app_closed_alert_button').click(function(event){
                event.preventDefault();
                $('#app_closed_alert').val(aol_admin.app_closed_alert);
                $('#app_closed_alert').text(aol_admin.app_closed_alert);
            });
            
            $('#aol_required_fields_button').click(function(event){
                event.preventDefault();
                $('#aol_required_fields_notice').val(aol_admin.aol_required_fields_notice);
                $('#aol_required_fields_notice').text(aol_admin.aol_required_fields_notice);
            });
        
            /*Form Builder Template rendor*/
            $('#aol_template_loader').on( 'change', function() {
                var dd = $(this);
                var temp = $(this).val();
                var tempName = $('#aol_template_loader option[value='+temp+']').text();

                var decision = confirm("You will lose any existing form.");
                if(decision == false){
                    dd.find('.aol_default_option').attr('selected', 'selected');
                    return;
                }
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'aol_template_render',
                        template: temp,
                    },
                    beforeSend: function(){
                        $('.template_loading_status').html('<img src="'+decodeURI(aol_admin.aol_url)+'images/loading.gif">');
                        //$('#app_form_fields').html('<img src="'+decodeURI(aol_admin.aol_url)+'images/loading.gif">');
                    },
                    success:function(response){
                        //dd.val('');
                        $('.template_loading_status').text('Form Template loaded successfully.');
                        $('#app_form_fields').html(response);
                        dd.find('.aol_default_option').attr('selected', 'selected');
                    }
                });
            });
            
            
            /*END Form Builder Template rendor*/         
            var aolicon = $('.aol-selectors').find('.dashicons');
            aolicon.click(function(){
                aolicon.parent('td').css('background', 'none');
                aolicon.parent('td').css('border', 'none');
                $(this).parent('td').css('background', '#eee');
                $(this).parent('td').css('border', '1px solid #ccc');
            });
            
            /*Form Builder row hover*/
            $('#app_form_fields, .app_form_fields').on('mouseenter', 'tr', function(){
                $(this).find('.aol-edit-form').css('display', 'inline-block');
             }).on('mouseleave', 'tr', function(){
                $(this).find('.aol-edit-form').css('display', 'none');
            });
            /*
            aolicon.mouseover(fu.nction(){
                $(this).css('color', '#00F');
            });
            aolicon.mouseout(function(){
                $(this).css('color', '#555');
            });
            */
           
            /*Import Application Form*/
            /*Form Builder import Form*/
            $('.aol-import-form').on('select2:select', function (e){
                var data = e.params.data;
                //console.log(data);
                var dd = $(this);
                var ad_id = data.id;
                //var tempName = $('#aol_template_loader option[value='+temp+']').text();

                var decision = confirm("You will lose any existing form.");
                if(decision == false || ad_id == ''){
                    dd.find('.aol_default_option').attr('selected', 'selected');
                    return;
                };
                alert('Alhamdulillah');
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'aol_ad_form_render',
                        ad_id: ad_id,
                    },
                    beforeSend: function(){
                        $('.template_loading_status').html('<img src="'+decodeURI(aol_admin.aol_url)+'images/loading.gif">');
                        //$('#app_form_fields').html('<img src="'+decodeURI(aol_admin.aol_url)+'images/loading.gif">');
                    },
                    success:function(response){
                        $('.template_loading_status').text('Form imported successfully.');
                        $('#app_form_fields').html(response);
                        //dd.find('.aol_default_option').attr('selected', 'selected');
                        
                    }
                });
                dd.val(null);
            });
            
            $('.aol-import-form').select2({
                //placeholder: "Import an Exsting Form",
                placeholder: 'Select existing form',
                ajax: {
                    url: ajaxurl,
                    delay: 500,
                    data: function (params) {
                      var query = {
                        action: 'aol_all_ads',
                        search: params.term,
                      }

                      // Query parameters will be ?search=[term]&type=public
                      return query;
                    },
                    processResults: function (data) {
                    // Transforms the top-level key of the response object from 'items' to 'results'
                    return {
                      results: data
                    };
                  }
              }
            });
            
            $('#ad_aol_filter').click(function(){
                var Singular = aol_sanitize_text_field($('#ad_filter_singular').val() ); // Get Raw value.
                var Plural = aol_sanitize_text_field( $('#ad_filter_plural').val() );  // Get Raw value.
                //var fieldNameRawPlural=$('#ad_filter_plural').val(); // Get Raw value.
                
                var keySingular = aol_sanitize_key(Singular);
                //var fieldNamePlural = aol_sanitize_key(fieldNameRawPlural);

                if(Singular != '' && Plural != ''){
                    //$('#ad_custom_filters').append('<tr><td><input id="filter-'+keySingular+'" name="aol_ad_filters[]" value="'+keySingular+'" type="checkbox"> <label for="filter-'+keySingular+'">'+Singular+' filter</label></td><td><input type="text" class="'+keySingular+' aol_ad_filter" name="aol_ad_filters['+keySingular+'][singular]" value="'+Singular+'" placholder="Singular" /> <input type="text" class="'+keySingular+' aol_ad_filter" name="aol_ad_filters['+keySingular+'][plural]" value="'+Plural+'" placeholder="Plural" /></td><td><span class="dashicons dashicons-trash removeField button-trash"></span></td></tr>');
                    $('#ad_custom_filters').append('<tr><td><label for="filter-'+keySingular+'">'+Singular+'</label></td><td><input type="text" class="'+keySingular+' aol_ad_filter" name="aol_ad_filters['+keySingular+'][singular]" value="'+Singular+'" placholder="Singular" /> <input type="text" class="'+keySingular+' aol_ad_filter" name="aol_ad_filters['+keySingular+'][plural]" value="'+Plural+'" placeholder="Plural" /></td><td><span class="dashicons dashicons-trash removeField button-trash"></span></td></tr>');
                }
                else{
                    $('#ad_filter_singular').css('border','1px solid #F00');
                    $('#ad_filter_plural').css('border','1px solid #F00');
                }
                $('#ad_filter_singular, #ad_filter_plural').val('');
            });
            $('#ad_custom_filters').on('click', 'tr .removeField',function(){
                $(this).parentsUntil('table', 'tr').remove();
            });            

            /*Form Builder import Form*/
            $('#aol-apps-table-search').on('select2:select', function (e){
                var data = e.params.data;
                //console.log(data);
                var dd = $(this);
                var ad_id = data.id;
                //var tempName = $('#aol_template_loader option[value='+temp+']').text();
                window.location = '?post_type=aol_application&ad='+ad_id;
            });
            $('#aol-apps-table-search').select2({
                placeholder: "Filter Applications",
                ajax: {
                    url: ajaxurl,
                    delay: 2000,
                    data: function (params) {
                      var query = {
                        action: 'application_table_filter_result',
                        search: params.term
                      };

                      // Query parameters will be ?search=[term]&type=public
                      return query;
                    },
                    processResults: function (data) {
                    // Transforms the top-level key of the response object from 'items' to 'results'
                    return {
                      results: data
                    };
                  }
              }
            }); 
            
             /*Application Settings Tab*/
            $('#ad_aol_status').click(function(){
                var Singular = aol_sanitize_text_field( $('#ad_status_singular').val() ) ; // Get Raw value.
                //var fieldNameRawPlural=$('#ad_status_plural').val(); // Get Raw value.
                
                var keySingular = aol_sanitize_key(Singular);
                //var fieldNamePlural = aol_sanitize_key(fieldNameRawPlural);


                if(Singular != ''){
                    $('#aol_application_status_setting').append('<li><input type="hidden" class="'+keySingular+' aol_custom_statuses" name="aol_custom_statuses['+keySingular+']" value="'+Singular+'" /><input id="status-'+keySingular+'" name="aol_app_statuses[]" value="'+keySingular+'" type="checkbox"><label for="status-'+keySingular+'">Enable '+Singular+' status.</label> &nbsp; <span class="aol-remove dashicons dashicons-trash button-trash dashicons dashicons-dismiss"></span>');
                }
                else{
                    $('#ad_status_singular').css('border','1px solid #F00');

                }
                $('#ad_status_singular').val('');
            });
            $('#aol_application_status_setting').on('click', 'li .dashicons-dismiss',function(){
                $(this).parentsUntil('ul', 'li').remove();
            });
            
            /**
             * Over-write "ad_file_types" with Global Option "aol_ad_file_types".
             * 
             * @param {type} field
             * @returns {unresolved}
             */            

        }); //Document Ready State.        
})( jQuery );

function sanitize_xss(string) {
  const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#x27;',
      "/": '&#x2F;',
  };
  const reg = /[&<>"'/]/ig;
  return string.replace(reg, (match)=>(map[match]));
}

function aol_sanitize_text_field(field){
    //return field.replace(/[^a-zA-Z0-9 ]/g, '');
    field = field.trim();
    return field.replace(/[~`!@#$%^*(){}\[\]"'<>\/\\|]/g, "");
}

function aol_sanitize_key(field){
    field = aol_sanitize_text_field(field);
    field = field.replace(/\s/g, "_");
    field = field.toLowerCase();
    //field = field.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return field.replace(/[^a-z0-9_-]/g, '');
}

function aol_form(element, fieldID = null, fieldNameRaw = null, fieldPlaceholder = null, fieldDesccription = null, fieldClass = null, fieldFileTypes = null, fieldFileSize = null, checked_item = null, fieldOptions = null, aol_defult_selection = null, fieldType = null, required = null){
    var fieldName = 'name="'+fieldID+'"';
    var array = {
        start_html   : '<tr class="'+fieldID+'" data-id="'+fieldID+'"><td><label for="'+fieldID+'"><span class="dashicons dashicons-menu"></span> '+fieldNameRaw+'</label></td><td class="aol-edit-form"><a href="#TB_inline?&width=400&height=550&inlineId='+fieldID+'" title="Edit Field" class="thickbox dashicons dashicons-edit"></a><span class="dashicons dashicons-trash aol-remove" title="Delete" ></span></td><td style="display:none" id="'+fieldID+'"><div class="aol_form"><div>'+fieldID+'</div><div class="aol_form_row"><label>Lable</label><input '+fieldName+'[label]" value="'+fieldNameRaw+'" type="text"></div><input type="hidden" '+fieldName+'[type]" value="'+fieldType+'">',
        end_html     : '<div><button type="button" class="button aol-save-form" data-temp=""><span class="dashicons dashicons-plus-alt"></span> Update Field </button></div></div></td></tr>',
        placeholder  : '<div class="aol_placeholder"><label>Placeholder</label><input '+fieldName+'[placeholder]" class="_aol_field_placeholder" value="'+fieldPlaceholder+'" type="text"></div>',
        help         : '<div><label>Help Text</label><input type="text" class="adapp_field_help adapp_field_description" '+fieldName+'[description]" value="'+fieldDesccription+'"  /></div>',
        classes      : '<div><label>Classes</label><input '+fieldName+'[class]" value="'+fieldClass+'" type="text"></div>',
        types      : '<div><label>Types</label><input '+fieldName+'[allowed_file_types]" value="'+fieldFileTypes+'" type="text"></div>',
        types      : '<div><label>Size</label><input '+fieldName+'[file_max_size]" value="'+fieldFileSize+'" type="text"></div>',
        required     : '<div><label>Required Field</label><input class="required_option" '+checked_item+' '+fieldName+'[required]" value="'+required+'" type="checkbox"></div>',
        options      : '<div><label>Options</label><input type="text" class="adapp_field_options" '+fieldName+'[options]" value="'+fieldOptions+'" placeholder="Option1, Option2, Option3" /> </div>',
        char_limit   : '<label>Charcter Limit</label><input id="adapp_charchter_limit" class="adapp_charchter_limit" '+fieldName+'[options]" value="'+fieldOptions+'" type="number" placeholder="No Limit">',
        defult       : '<div class="aol_defult_selection aol_add_field"><label>Defult Selection</label><input class="required_defult_selection" '+fieldName+'[preselect]" type="checkbox" '+aol_defult_selection+' id="aol_defult_selection" value="yes" /> </div>'
    }
    return array[element];
}