var chosen_entry = '';

$(".entries__entry").dblclick((e) => moveto_directory(e));

$("#move_close_button").click(() => {
    $(".entries__moveentry").remove(); 
} );

function change_chosen_entry(elementId){
    var element = document.getElementById(elementId);

    if(element.classList.contains('entries__entry--chosen')){
        element.classList.remove('entries__entry--chosen');
        return;
    }

    element.classList.add('entries__entry--chosen');
}

function moveto_directory(e){
    var entry_name = 
        $(e.target.parentNode)
        .find("span.entries__entry__name");

    var newLocation = entry_name[0].childNodes[0].data;

    $.ajax({
        url:"filesystem.php",
        type: "POST", 
        dataType: 'json',
        data: {location: location.href, destination:  newLocation, IsMovePage: false },
        success: function(result){

            if(!result.Successfull){
                alert(result.ErrorMsg);
                return;
            }

            location.href = result.Redirect_url;
       },
       error: function(err){alert('Server sent data in unknown format.');}
     });
}


function delete_files(){
    let filesToDelete = Array.from(document.querySelectorAll('div.entries__entry--chosen span.entries__entry__name')).map(el => el.childNodes[0].data);

    if(filesToDelete.length == 0){
        alert('Choose files/folders to delete.');
        return;
    }

    $.ajax({
        url:"filesystem.php", 
        type: "POST",
        dataType: 'json',
        data: {location: location.href, filesToDelete: filesToDelete},
        success:function(result){

            if(!result.Successfull){
                alert(result.ErrorMsg);
                return;
            }
            
            location.reload();
       },
       error: function(err){alert('Server sent data in unknown format.');}
     });
}

function upload_file() {
    var file_data = $('#file_upload_input').prop('files')[0];   
    var form_data = new FormData(); 
    form_data.append('file', file_data);
    form_data.append('location', location.href);
    $.ajax({
        url: 'filesystem.php', // point to server-side PHP script 
        type: 'POST',
        dataType: 'text',  // what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,                         
        success: function(php_script_response){
            alert(php_script_response);

            location.reload();
        }
     });
} 

String.prototype.trimRight = function(charlist) {
    if (charlist === undefined)
      charlist = "\s";

    return this.replace(new RegExp("[" + charlist + "]+$"), "");
  }; 