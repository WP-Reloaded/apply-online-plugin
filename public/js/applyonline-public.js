(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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
        
        $(document).ready(function(){
            /*Avada Theme work around, when it shows multiple forms on single ads.*/
            $('.fusion-tb-header .aol-single, .fusion-tb-footer .aol-single').children().not('div').remove();

            $('.datepicker').datepicker({
                yearRange: "-99:+50",
                //dateFormat : aol_public.date_format,
                changeMonth: true,
                changeYear: true,
            });
            
            /*Setting Textarea Charchter Counter*/
            $('textarea, input[type=text]').keyup(function() {
    
                var characterCount = $(this).val().length,
                    current = $(this).parent().find('.current'),
                    maximum = $(this).parent().find('.maximum'),
                    theCount = $(this).parent().find('.the-count');

                current.text(characterCount);


                /*This isn't entirely necessary, just playin around*/
                if (characterCount < 70) {
                  current.css('color', '#666');
                }
                if (characterCount > 70 && characterCount < 90) {
                  current.css('color', '#6d5555');
                }
                if (characterCount > 90 && characterCount < 100) {
                  current.css('color', '#793535');
                }
                if (characterCount > 100 && characterCount < 120) {
                  current.css('color', '#841c1c');
                }
                if (characterCount > 120 && characterCount < 139) {
                  current.css('color', '#8f0001');
                }

                if (characterCount >= 140) {
                  maximum.css('color', '#8f0001');
                  current.css('color', '#8f0001');
                  theCount.css('font-weight','bold');
                } else {
                  maximum.css('color','#666');
                  theCount.css('font-weight','normal');
                }


            });
            /*Ends Textarea Charchter Counter*/
         
            /*Submit Application Form*/
            /*
            $( ".aol_app_form" ).submit(function(){
                var datastring = new FormData(document.getElementById("aol_app_form"));
                var request = $.ajax({
                    url: aol_public.ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: datastring,
                    //async: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function(){
                        $('#aol_form_status').removeClass();
                        $('#aol_form_status').html('<img src="'+aol_public.url+'/images/loading.gif" />');
                        $('#aol_form_status').addClass('alert alert-warning');
                        $(".aol-form-button").prop('disabled', true);
                    }
                });                        
                request.done( function(response, type, data){
                    $(document).trigger('afterAppSubmit', response); //Custom event  on ajax completion
                    if(response['success']==true){
                        $('#aol_form_status').removeClass();
                        $('#aol_form_status').addClass('alert alert-success');
                        $('#aol_form_status').html(response['message']);
                        $(".aol-form-button").prop('disabled', false);
                        if(response['hide_form']==true) $('.aol_app_form').slideUp(); //Show a sliding effecnt.

                        //Divert to thank you page. 
                        if(response.divert != null){
                            var page = response.divert;
                            window.location.href = stripslashes(page);
                        }
                    }
                });
                request.fail( function(xhr, type, errorText){
                        let response = JSON.parse(xhr.responseText);
                        $('#aol_form_status').removeClass();
                        $('#aol_form_status').addClass('alert alert-danger');
                        $('#aol_form_status').html('<h4>'+errorText+'</h4>'+response['message']);
                        $(".aol-form-button").prop('disabled', false);
                });
                return false;
            });
          */
            //Separator Code
            $('.aol_multistep').click(function(){
                $('fieldset').hide();
                var load = $(this).data('load');
                if( load == 'next' ) $(this).parent("fieldset").next("fieldset").show();
                else if( load == 'back' ) $(this).parent("fieldset").prev("fieldset").show(); else $(this).parent("fieldset").previous("fieldset").show();

                return false;
            });
          
            /* Progress Bar*/
            var fields_required = $('.aol-form-group.required');
            var fields_count = fields_required.length;
            if(fields_count > 0) {
                $('.progress-wrapper').show();
                update_progress_bar($, fields_required, fields_count);
                $(fields_required).find('input, textarea').change(function(){
                    update_progress_bar($, fields_required, fields_count);
                });                                                 
            }
            /*End Progress Bar*/
        })

})( jQuery );

function update_progress_bar($, field, fields_count){
    var filled = 0;
    $(field).each(function(){
        //If child input field is a checkbox or radio.
        if( $(this).find('input').attr('type') == 'checkbox' || $(this).find('input').attr('type') == 'radio' ){
            if($(this).find('input').is(':checked')) { // zero-length string AFTER a trim
                filled++;
            }
        }
        else{
            if($.trim( $(this).find('input, textarea').val() ).length ) { // zero-length string AFTER a trim
                filled++;
            }            
        }
    });
    filled_pecentage = (filled/fields_count)*100;
    $('.aol-progress-count').css('width',filled_pecentage+'%');
    $('.aol-progress-counter').text(filled+'/'+fields_count);
    //$('.aol_progress').val(filled+'/'+fields_count);
}

async function aolSubmitForm( event ) {
    if ( window.confirm(aol_public.consent_text) == false ) return;
    event.preventDefault();
    
    const submitButton = document.getElementById('aol_app_submit_button');
    const statusBar = document.getElementById('aol_form_status');
    const aolForm = event.target;
    
    //event.target.setAttribute('disabled', 'disabled');
    submitButton.setAttribute('disabled', 'disabled');
    statusBar.classList.remove('alert-danger'); //May be trying again after errors.
    statusBar.classList.add('alert');
    statusBar.classList.add('alert-warning');
    statusBar.innerHTML = '<img src="'+aol_public.url+'/images/loading.gif" />';
    
    const formData = new FormData(document.getElementById("aol_app_form"));
    //formData.append('note', 'hello_world');

    const response = await fetch(aol_public.ajaxurl, {
        method: 'POST',
        body: formData,
        headers: {'Accept': 'application/json'}
    });
    const data = await response.json();
    if( response.status == 200 ){

        statusBar.classList.remove('alert-warning');
        statusBar.classList.add('alert-info');
        statusBar.innerHTML = data['message'];
        if(data['hide_form'] == true){
            aolForm.classList.toggle('hideout');
        } //Show a sliding effecnt.

        //Divert to thank you page. 
        if(data.divert == true){
            var page = response.divert;
            window.location.href = stripslashes(page);
        }
    } else {
        statusBar.classList.remove('alert-warning');
        statusBar.classList.add('alert-danger');
        statusBar.innerHTML = '<h5 class="error-title">'+response.statusText+'</h5>'+data['message'];
        submitButton.removeAttribute('disabled');
    }
}

function stripslashes (str) {
            return (str + '').replace(/\\(.?)/g, function (s, n1) {
              switch (n1) {
              case '\\':
                return '\\';
              case '0':
                return '\u0000';
              case '':
                return '';
              default:
                return n1;
              }
            });
        }
        
function limitText(limitField, limitNum) {
    if (limitField.value.length > limitNum) {
        limitField.value = limitField.value.substring(0, limitNum);
    } 
}