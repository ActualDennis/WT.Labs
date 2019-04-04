
let IsFolderChosen = false;

function change_move_chosen_entry(elementId){

    var element = document.getElementById(elementId);

    if(element.classList.contains('entries__moveentry--chosen')){
        element.classList.remove('entries__moveentry--chosen');
        return;
    }

    element.classList.add('entries__moveentry--chosen');
}

function movefiles_movetodirectory(e){
    var entry_name = 
    $(e.target)
    .closest("span.entries__entry__name");

    var newLocation = entry_name[0].childNodes[0].data;

    $.ajax({
        url:"filesystem.php",
        type: "POST", 
        dataType: 'json',
        data: {location: window.location.href, destination:  newLocation, IsMovePage: true },
        success:function(result){

            if(!result.Successfull){
                alert(result.ErrorMsg);
                return;
            }

            let relativePath = result.Redirect_url.substring(result.Redirect_url.indexOf("filesystem") + "filesystem".length);

            update_move_listing(relativePath);
        },
        error: function(err){alert('Server sent data in unknown format.');}
    });
}

function update_move_listing(path) {
    $.ajax({
        url:"templaiter.php",
        type: "POST", 
        dataType: 'text',
        data: {location: path},  //load root directory at first.
        success:function(result){
            $(".entries__moveentry").remove();
            document.getElementById('move_modal_body_entries').insertAdjacentHTML('beforebegin', result);
            $(".entries__moveentry").dblclick((e) => movefiles_movetodirectory(e));
        },
        error: function(err){alert('Server sent data in unknown format.');}
     });
} 

