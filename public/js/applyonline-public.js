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
         $( ".aol_app_form" ).submit(function(){
            var datastring = new FormData(document.getElementById("aol_app_form"));
            $.ajax({
                    url: aol_public.ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: datastring,
                    //async: false,
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function(){
                        $('#aol_form_status').removeClass();
                        $('#aol_form_status').html('<img src="'+aol_public.url+'/images/loading.gif" />');
                        $('#aol_form_status').addClass('alert alert-warning');
                        $(".aol-form-button").prop('disabled', true);
                    },
                    success:function(response){
                        $(document).trigger('afterAppSubmit', response); //Custom event  on ajax completiong
                        
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
                        else if(response['success']==false){
                            $('#aol_form_status').removeClass();
                            $('#aol_form_status').addClass('alert alert-danger');
                            $('#aol_form_status').html(response['error']);
                            $(".aol-form-button").prop('disabled', false);
                        }
                        //If response is not jSon.
                        else{
                            $('#aol_form_status').addClass('alert alert-danger');
                            $('#aol_form_status').html('Form saved with errors. Please contact us for more information. ');
                            $(".aol-form-button").prop('disabled', false);
                        }
                    },
                    error: function(xhr, type, error){
                        $('#aol_form_status').removeClass();
                        $('#aol_form_status').addClass('alert alert-danger');
                        $('#aol_form_status').html('An unexpected error occured with error code: <u>' + xhr.status + " " + xhr.statusText+'</u>. '+error+'. Please contact us for more information.');
                        $(".aol-form-button").prop('disabled', false);
                    },
                    // Custom XMLHttpRequest
                    xhr: function () {
                      $('progress').attr({
                              value: 0,
                            });
                      var myXhr = $.ajaxSettings.xhr();
                      if (myXhr.upload) {
                        // For handling the progress of the upload
                        myXhr.upload.addEventListener('progress', function (e) {
                          if (e.lengthComputable) {
                            $('progress').attr({
                              value: e.loaded,
                              max: e.total,
                            });
                          }
                        }, false);
                      }
                      return myXhr;
                    },
            });
            return false;
          });
          
            //Separator Code
            $('.aol_multistep').click(function() {
              $('fieldset').hide();
              var load = $(this).data('load');
              if( load == 'next' ) $(this).parent("fieldset").next("fieldset").show();
              else if( load == 'back' ) $(this).parent("fieldset").prev("fieldset").show(); else $(this).parent("fieldset").previous("fieldset").show();
              
              return false;
          });
        })

})( jQuery );

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