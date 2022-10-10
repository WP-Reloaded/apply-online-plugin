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
        var i = 0;
        $(document).ready(function(){
            jQuery(document).on( 'click', '.aol .notice-dismiss', function() {

                jQuery.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'aol_dismiss_notice'
                    }
                })
            });
            
            $('.datepicker').datepicker({
                minDate:    +1,
                dateFormat: 'dd-mm-yy',
                changeMonth: true,
                changeYear: true
            });
            $("#app_form_fields").sortable({
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
                var fieldNameRawSingular=$('#ad_type_singular').val(); // Get Raw value.
                var fieldNameRawPlural=$('#ad_type_plural').val(); // Get Raw value.
                var fieldNameRawDesc=$('#ad_type_description').val(); // Get Raw value.
                
                var fieldNameSingular = sanitize_me(fieldNameRawSingular);


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
            $('#ad_types').on('click', 'li .aol-remove',function(){
                $(this).parentsUntil('ol', 'li').remove();
            });
            $('#ad_types').find('.default').click(function(){
                return false;
            });
                
            /*Ad editor Scripts*/

            /*Application Field Type change for new Field only*/
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
            
                       
            // Required Option
            $('.required_option').on('click', function(e){
               // e.preventDefault();
                if( $(this).prop('checked') ) {
                    $(this).val('1');
                } else {
                    $(this).val('0');
                }
                //tb_remove();
            });
            
            $('.toggle-required').on('click', function(){
                var required = $(this).prev('input').val();
                //alert(required);
                $(this).prev('input').val(required === '1'? '0': '1');
                $(this).toggleClass('button-disabled');
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
                //alert('xxx');
                $('.aol_form').css('dispaly','none');
            });
            
            /*Add Application Field (Group Fields)*/
            $('.addField').on('click', function(e){
                e.preventDefault();
                var tempID = $(this).data('temp');
                var wrapper = $(this).closest('.aol_form');
                
                //var fieldNameRaw = $('#adapp_name').val(); // Get Raw value.
                var fieldNameRaw = wrapper.find('.adapp_name').val(); // Get Raw value.
                var fielduidRaw = wrapper.find('.adapp_uid').val();
                //var fieldName = md5(fieldNameRaw)
                //var fieldID = sanitize_me(fieldNameRaw); //Replace white space with _.
                var fieldID = sanitize_me(fielduidRaw);
                if( tempID == '' ) var fieldID = '_aol_app_'+fieldID;
                else if( tempID == 'new' ) var fieldID = 'new[_aol_app_'+fieldID+']';
                else if( tempID != '' ) var fieldID = tempID+'[_aol_app_'+fieldID+']';
                var fieldType = wrapper.find('input[name="aol_type"]').val(); 
                var fieldPlaceholder = wrapper.find('.adapp_placeholder').val();
                var fieldClass = wrapper.find('.adapp_class').val();
                var required = wrapper.find('.adapp_required').val();
                var fieldOptions = wrapper.find('.adapp_field_options').val();
                var fieldDesccription = wrapper.find('.adapp_field_help').val();
                var fieldText = wrapper.find('.adapp_text').val();
                var fieldTextHeight = wrapper.find('.adapp_text_height').val();
                var fieldLimit = wrapper.find('.adapp_limit').val();
                if( required =='1'){
                    var checked_item ='checked';
                }else {
                    var checked_item    ='';
                }
                
                var  aol_defult_selection = '';
                if( wrapper.find(".adapp_preselect").prop('checked') ) {
                   var  aol_defult_selection = 'checked';
                } 
                //Highlight culprut
                $('#adapp_name').css('border','1px solid #f00');
                //var fieldTypeHtml = wrapper.find('.adapp_field_type').html();
                if(fieldID != '' && fieldNameRaw != '' && fieldType != ''){
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
                    var end_html    = '<div><button class="button button-primary aol-save-form" data-temp=""> Update Field </button></div></div></td></tr>';
                    //var selected_name   = '';
                    var placholder_html = '<div class="aol_placeholder"><label>Placeholder</label><input name="'+fieldID+'[placeholder]" class="_aol_field_placeholder" value="'+fieldPlaceholder+'" type="text"></div>';
                    var help_html   = '<div><label>Help Text</label><input type="text" class="adapp_field_help adapp_field_description" name="'+fieldID+'[description]" value="'+fieldDesccription+'"  /></div>';
                    var text_html   = '<div><label>Text</label><textarea class="adapp_text" name="'+fieldID+'[text]" >'+fieldText+'</textarea></div><div><label>Text Box Height</label><input type="number" class="adapp_text_height" name="'+fieldID+'[height]" value="'+fieldTextHeight+'" /></div>';
                    var class_html  = '<div><label>Classes</label><input name="'+fieldID+'[class]" value="'+fieldClass+'" type="text"></div>';
                    var option_html = '<div><label>Options</label><input type="text" class="adapp_field_options" name="'+fieldID+'[options]" value="'+fieldOptions+'" placeholder="Option1, Option2, Option3" /> </div>';
                    var char_limit = '<label>Charcter Limit</label><input class="adapp_charchter_limit" name="'+fieldID+'[limit]" value="'+fieldLimit+'" type="number" placeholder="No Limit">';                    
                    var defult_selection = '<div class="aol_add_field"><label>Pre Selected</label><input class="aol_preselect" name="'+fieldID+'[preselect]" type="checkbox" '+aol_defult_selection+' value="yes" /> </div>';
                    var required_html   = '<div><label>Required Field</label><input class="required_option" '+checked_item+' name="'+fieldID+'[required]" value="'+required+'" type="checkbox"></div>';                                       
                    var output;
                    
                    switch (select_value){
                        case 'text':
                        case 'text_area':
                          output = start_html+help_html+placholder_html+class_html+char_limit+required_html+end_html;
                          break;
                        case 'number':
                        case 'date':
                        case 'email':
                          output = start_html+help_html+placholder_html+class_html+required_html+end_html;
                          break;
                        case 'dropdown':
                        case 'checkbox':
                          output = start_html+help_html+option_html+class_html+required_html+end_html;
                          break;
                        case 'radio':
                          output = start_html+help_html+option_html+class_html+defult_selection+required_html+end_html;
                          break;
                        case 'file':
                          output = start_html+help_html+class_html+required_html+end_html;
                          break;
                        case 'separator':
                          output = start_html+help_html+class_html+end_html;
                          break;
                        case 'paragraph':
                          output = start_html+text_html+class_html+end_html;
                          break;
                     }
                    if( tempID != '' ) $('#' + tempID + ' .app_form_fields').append(output);
                    else $('#app_form_fields').append(output);

                    //$('#app_form_fields').append('<tr class="'+fieldID+'" data-id="'+fieldID+'"><td><label for="'+fieldID+'"><span class="dashicons dashicons-menu"></span> '+fieldNameRaw+'</label> &nbsp; </td><td class="aol-edit-form"><a href="#TB_inline?&width=400&height=550&inlineId='+fieldID+'" title="Edit Field" class="thickbox dashicons dashicons-edit"></a><span class="dashicons dashicons-trash aol-remove" title="Delete" ></span></td><td style="display:none" id="'+fieldID+'"><div class="aol_form"><div><label>Field Lable</label><input name="'+fieldName+'[label]" value="'+fieldNameRaw+'" type="text"></div><div class="aol_placeholder"><label>Placeholder</label><input name="'+fieldName+'[placeholder]" placeholder="Placeholder" class="_aol_field_placeholder" value="'+fieldPlaceholder+'" type="text"></div><div><label>Class</label><input placeholder="Class" name="'+fieldName+'[class]" value="'+fieldClass+'" type="text"></div><div><label>Required Options</label><input class="required_option" '+checked_item+' name="'+fieldName+'[required]" value="'+required+'" type="checkbox"></div><div><label>Type</label><select id="'+fieldID+'" class="adapp_field_type '+fieldID+'" name="'+fieldName+'[type]">'+fieldTypeHtml+'</select></div><div><label>Help Text</label><input type="text" class="adapp_field_help adapp_field_description" name="'+fieldName+'[description]" value="'+fieldDesccription+'" placeholder="Help text" /></div><div><label>Options</label><input type="text" class="adapp_field_options" name="'+fieldName+'[options]" value="'+fieldOptions+'" placeholder="Option1, Option2, Option3" /> </div><div><button type="button" class="button aol-save-form" data-temp=""><span class="dashicons dashicons-plus-alt"></span> Update Field </button></div></div></td></tr>');
                    $('#aol_keys_order_wrapper').append('<input type="hidden" id="aol_fields_order" name="_aol_fields_order[]" value="'+fieldID+'" />');
                    //$("."+fieldID+" ."+fieldType).attr('selected','selected');
                    $('#adapp_name, #adapp_field_help').val('');
                    $('#adapp_field_type').val('');
                    // edit form field
                    $('.aol_fields').on('click', function(){
                        var fieldType = $(this).data('id');
                    });
                    $('.toggle-required').on('click', function(){
                        var required = $(this).prev('input').val();
                        $(this).prev('input').val(required === '1'? '0': '1');
                        $(this).toggleClass('button-disabled');
                    });
                    $(this).parent('.aol_form').css('display','none');
                    $('.aol-selectors').find('.dashicons').parent('td').css('background', 'none');
                    $('.aol-selectors').find('.dashicons').parent('td').css('border', 'none');
                     $('#adapp_name').css('border','none');
                    tb_remove();
                }
                else{
                        wrapper.find('.adapp_name').css('border','1px solid #F00');
                        wrapper.find('.adapp_uid').css('border','1px solid #F00');
                    //if(fieldType == '') wrapper.find('#adapp_field_type').css('border','1px solid #F00');
                }
                return false;
            });
            
            //On Form Builder New Row Edit
            $('body').on('click', '.aol-save-form', function(e){
               //Change Row label as per new vale.
               var rowID = $(this).closest('.aol_form').data('id');
               var rowLabel = $(this).closest('.aol_form').find('input[name="'+rowID+'[label]"]').val();
               $('#app_form_fields tr.'+rowID+' label').html(rowLabel);
                
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
                        $('.aol_help_text').css('display','block');
                        break;
                    case 'paragraph':
                        $('.aol_add_field').css('display','none');
                        $('.aol_text').css('display','block');
                        $('.aol_text_height').css('display','block');
                        break;

                    case 'checkbox':
                        $('.aol_add_field').css('display','none');
                        $('.aol_help_text').css('display','block');
                        $('.aol_required').css('display','block');
                        $('.aol_options').css('display','block');
                        $('.aol_orientation').css('display', 'block');
                    break;
                    
                    case 'dropdown':
                        $('.aol_add_field').css('display','none');
                        $('.aol_help_text').css('display','block');
                        $('.aol_required').css('display','block');
                        $('.aol_options').css('display','block');
                        break;
                        
                    case 'radio':
                        $('.aol_add_field').css('display','none');
                        $('.aol_help_text').css('display','block');
                        $('.aol_required').css('display','block');
                        $('.aol_options').css('display','block');
                        $('.aol_preselect').css('display','block');
                        $('.aol_orientation').css('display', 'block');
                        break;

                    case 'text':
                    case 'text_area':
                        $('.aol_add_field').css('display','none');
                        $('.aol_help_text').css('display','block');
                        $('.aol_required').css('display','block');
                        $('.aol_placeholder').css('display','block');
                        $('.aol_limit').css('display','block');
                        break;

                    case 'number':
                    case 'email':
                        $('.aol_add_field').css('display','none');
                        $('.aol_help_text').css('display','block');
                        $('.aol_required').css('display','block');
                        $('.aol_placeholder').css('display','block');
                        break;

                    case 'date':
                    case 'file':
                        $('.aol_add_field').css('display','none');
                        $('.aol_help_text').css('display','block');
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
                    $('#ad_features').append('<li class="'+fieldName+'"><label for="'+fieldName+'">'+fieldNameRaw+'</label> &nbsp; <input type="text" name="_aol_feature_'+fieldName+'[label]" value="'+fieldNameRaw+'" placeholder="Label" > &nbsp;  <input type="text" name="_aol_feature_'+fieldName+'[value]" value="'+fieldVal+'" laceholder="Value" > &nbsp; <div class="button aol-remove">Delete</div></li>');
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
                var required = $(this).prev('input').val();
                $(this).prev('input').val(required === '1'? '0': '1');
                $(this).toggleClass('button-disabled');
            });
            /*END Ad editor Scripts*/
            
            /*Settings Tabs*/
            $('.aol-settings').children('.tab-data:first').show();
            $('.aol-primary').children('.nav-tab').click(function(){
                $('.aol-primary').find('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');

                var target = $(this).data("id");

                $('.aol-settings').children('.tab-data').hide();
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
                if(decision == false || temp == ''){
                    dd.val('');
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
                    dd.val(''); alert('False');
                    return;
                };
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'aol_ad_form_render',
                        ad_id: ad_id,
                    },
                    beforeSend: function(){
                        $('#app_form_fields').html('<tr><td><img src="'+decodeURI(aol_admin.aol_url)+'images/loading.gif"></tr></td>');
                        //$('#app_form_fields').html('<img src="'+decodeURI(aol_admin.aol_url)+'images/loading.gif">');
                    },
                    success:function(response){
                        //dd.val('');
                        //$('.template_loading_status').text('Form Template loaded successfully.');
                        $('#app_form_fields').html(response);
                    }
                });
            });

            $('.aol-import-form').select2({
                placeholder: "Import an Existing Form",
                ajax: {
                    url: ajaxurl,
                    delay: 1500,
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
            
            /*Conditional Fields*/
            var fields_order_keys = $(".adapp_label").map(function (idx, ele) {
                return $(ele).attr('name');
             });
             var fields_order_values = $(".adapp_label").map(function (idx, ele) {
                return $(ele).val();
             });
                          
             $.each(fields_order_keys, function(index, val) {
                var key = val.replace("[label]", '');
                val = fields_order_values[index];
                //field_order[val] = fields_order_values[index];
                $('.conditional_field').append('<option value="'+key+'">'+val+'</option>');
            });
            //var fields_order = $('#aol_fields_order').val();
            
        }); //Document Ready State.
        
        
                   
})( jQuery );
function sanitize_me(field){
    field = field.trim();
    field = field.replace(/\s/g, "_");
    field = field.toLowerCase();
    field = field.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return field;
}

function aol_form(element, fieldID = null, fieldNameRaw = null, fieldPlaceholder = null, fieldDesccription = null, fieldClass = null, checked_item = null, fieldOptions = null, aol_defult_selection = null, fieldType = null, required = null){
    var fieldName = 'name="'+fieldID+'"';
    var array = {
        start_html   : '<tr class="'+fieldID+'" data-id="'+fieldID+'"><td><label for="'+fieldID+'"><span class="dashicons dashicons-menu"></span> '+fieldNameRaw+'</label></td><td class="aol-edit-form"><a href="#TB_inline?&width=400&height=550&inlineId='+fieldID+'" title="Edit Field" class="thickbox dashicons dashicons-edit"></a><span class="dashicons dashicons-trash aol-remove" title="Delete" ></span></td><td style="display:none" id="'+fieldID+'"><div class="aol_form"><div>'+fieldID+'</div><div class="aol_form_row"><label>Lable</label><input '+fieldName+'[label]" value="'+fieldNameRaw+'" type="text"></div><input type="hidden" '+fieldName+'[type]" value="'+fieldType+'">',
        end_html     : '<div><button type="button" class="button aol-save-form" data-temp=""><span class="dashicons dashicons-plus-alt"></span> Update Field </button></div></div></td></tr>',
        placeholder  : '<div class="aol_placeholder"><label>Placeholder</label><input '+fieldName+'[placeholder]" class="_aol_field_placeholder" value="'+fieldPlaceholder+'" type="text"></div>',
        help         : '<div><label>Help Text</label><input type="text" class="adapp_field_help adapp_field_description" '+fieldName+'[description]" value="'+fieldDesccription+'"  /></div>',
        classes      : '<div><label>Classes</label><input '+fieldName+'[class]" value="'+fieldClass+'" type="text"></div>',
        required     : '<div><label>Required Field</label><input class="required_option" '+checked_item+' '+fieldName+'[required]" value="'+required+'" type="checkbox"></div>',
        options      : '<div><label>Options</label><input type="text" class="adapp_field_options" '+fieldName+'[options]" value="'+fieldOptions+'" placeholder="Option1, Option2, Option3" /> </div>',
        char_limit   : '<label>Charcter Limit</label><input id="adapp_charchter_limit" class="adapp_charchter_limit" '+fieldName+'[options]" value="'+fieldOptions+'" type="number" placeholder="No Limit">',
        defult       : '<div class="aol_defult_selection aol_add_field"><label>Defult Selection</label><input class="required_defult_selection" '+fieldName+'[preselected]" type="checkbox" '+aol_defult_selection+' id="aol_defult_selection" value="yes" /> </div>'
    }
    return array[element];
}