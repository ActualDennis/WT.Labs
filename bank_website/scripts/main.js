var chosen_entry = '';
var IsClicked = false;

$(".entries__entry").dblclick((e) => moveto_directory(e));

function change_chosen_entry(elementId){
    if(IsClicked){
        moveto_directory();
        return;
    }

    var element = document.getElementById(elementId);

    if(element.classList.contains('entries__entry--chosen')){
        element.classList.remove('entries__entry--chosen');
        return;
    }

    element.classList.add('entries__entry--chosen');
}

function moveto_directory(e){
    var entry_name = 
        $(e.target)
        .closest("span.entries__entry__name");

    var newLocation = entry_name[0].childNodes[0].data;

    $.ajax({
        url:"filesystem.php",
        type: "POST", 
        dataType: 'json',
        data: {location: window.location.href, destination:  newLocation},
        success:function(result){

            if(!result.Successfull){
                alert(result.ErrorMsg);
                return;
            }

            window.location.href = result.Redirect_url;
       },
       error: function(err){alert('Server sent data in unknown format.');}
     });
}

function delete_files(){
    let filesToDelete = Array.from(document.querySelectorAll('div.entries__entry--chosen span.entries__entry__name')).map(el => el.childNodes[0].data);
    $.ajax({
        url:"filesystem.php", 
        type: "POST",
        dataType: 'json',
        data: {location: window.location.href, filesToDelete: filesToDelete},
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

String.prototype.trimRight = function(charlist) {
    if (charlist === undefined)
      charlist = "\s";

    return this.replace(new RegExp("[" + charlist + "]+$"), "");
  }; 