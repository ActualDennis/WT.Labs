
function RemoveNewsLetterAlerts(){
    $('#newsletter_error').hide();
    $('#newsletter_success').hide();
}

function subscribeToNewsLetter(){
    let email = document.getElementById('newsLetterEmailInput').value;

    $.ajax({
        url: '/index.php?script=Newsletter.php', // point to server-side PHP script 
        type: 'POST',
        dataType: 'json',  // what to expect back from the PHP script, if anything
        data: {email: email},                         
        success: function(response){

            if(!response.Successfull){
                $("#newsletter_error-label").text(response.ErrorMsg);
                $('#newsletter_error').show();
                return;
            }

            $('#newsletter_error').hide();
            $('#newsletter_success').show();
        }
     });
}