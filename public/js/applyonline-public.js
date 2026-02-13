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

            });
            /*Ends Textarea Charchter Counter*/
         
            /*Submit Application Form*/
            $( ".aol_app_form" ).submit(function(){
                aolSubmitForm(event);
            });

            //Separator Code
            $('.aol_multistep').click(function(){
                $('fieldset').hide();
                var load = $(this).data('load');
                if( load == 'next' ) $(this).parent("fieldset").next("fieldset").show();
                else if( load == 'back' ) $(this).parent("fieldset").prev("fieldset").show(); else $(this).parent("fieldset").previous("fieldset").show();

                return false;
            });
          
            /* Progress Bar*/
            /*End Progress Bar*/
        })

})( jQuery );

document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('form.aol_app_form');

    // Only run if the form exists
    if (!form) return;

    const requiredFields = form.querySelectorAll('[required]');
    const progressBar   = form.querySelector('#progress-bar');
    const progressText  = form.querySelector('#progress-text');

    function aolupdateProgress() {
        let filled = 0;

        requiredFields.forEach(field => {
          if (field.checkValidity()) filled++;
        });

        const percent = Math.round((filled / requiredFields.length) * 100);
        //alert(percent);

        if (progressBar) progressBar.style.width = percent + '%';
        if (progressText) progressText.textContent = percent + '%';
  }

  form.addEventListener('input', aolupdateProgress);
  form.addEventListener('change', aolupdateProgress);

  aolupdateProgress();
});

//End Progress Bar

async function aolSubmitForm( event ) {
    if(!aolEmpty(aol_public.consent_text)){
        if ( window.confirm(aol_public.consent_text) == false ) return;        
    }
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
    let message = !aolEmpty(data['message']) ? data['message'] : 'Something went wrong. Please try again or contact support.';
    if( response.status == 200 ){
        statusBar.classList.remove('alert-warning');
        statusBar.classList.add('alert-info');
        statusBar.innerHTML = message;
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
        statusBar.innerHTML = '<h5 class="error-title">'+response.statusText+'</h5>'+message;
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

function aolEmpty(e) {
  switch (e) {
    case "":
    case 0:
    case "0":
    case null:
    case false:
    case undefined:
      return true;
    default:
      return false;
  }
}