
let move_current_path = '/';

let amount_chosen = 0;

function change_move_chosen_entry(elementId){

    var element = document.getElementById(elementId);

    if(element.classList.contains('entries__moveentry--chosen')){
        element.classList.remove('entries__moveentry--chosen');
        --amount_chosen;
        return;
    }

    if(amount_chosen == 1)
        return;

    element.classList.add('entries__moveentry--chosen');
    ++amount_chosen;
}

function movefiles_movetodirectory(e){
    var entry_name =
    $(e.target.parentNode)
    .find("span.entries__entry__name");

    var newLocation = entry_name[0].childNodes[0].data;

    $.ajax({
        url:"/index.php?script=WebFilesystem.php",
        type: "GET", 
        dataType: 'json', //non-relative
        data: {location: move_current_path, destination:  newLocation, IsMovePage: true },
        success:function(result){
            $('#spinner_move').show();

            if(!result.Successfull){
                alert(result.ErrorMsg);
                return;
            }

            let relativePath = getRelativePath(result.Redirect_url);

            update_move_listing(relativePath);
        },
        error: function(err){alert('Server sent data in unknown format.');}
    });
}

function handle_move(){
    if(document.querySelectorAll('div.entries__entry--chosen').length > 0){
        update_move_listing("/");
        $('#move_modal').modal('show');
        $('#spinner_move').hide();
        return;
    }

    alert('Please choose some entries before moving them.');
}

function move_files(){

    let entriesNames = [];

    let moveToEntries = document.querySelectorAll('div.entries__moveentry--chosen span.entries__entry__name');

    moveToEntries.forEach(x => entriesNames.push(x.childNodes[0].data));    

    if(entriesNames.length == 0){
        alert('Please choose some folder to move to.');
        return;
    }

    let whatToMove = document.querySelectorAll('div.entries__entry--chosen span.entries__entry__name');

    let entriesToMove = [];

    whatToMove.forEach(x => entriesToMove.push(x.childNodes[0].data))

    $.ajax({
        url:"/index.php?script=WebFilesystem.php",
        type: "GET", 
        dataType: 'json',                                          
        data: {whatToMove: entriesToMove, moveTo: move_current_path.trimRight("/") + "/" + entriesNames[0], location: getRelativePath(location.href), IsMovePage: true},
        success:function(result){
                alert(result.Message);
                return;
        },
        error: function(err){alert('Server sent data in unknown format.');}
     });
}

function update_move_listing(path) {
    $.ajax({
        url:"/index.php?script=TemplatesHelper.php",
        type: "GET", 
        dataType: 'text',
        data: {location: path, request: "GetEntries"},  //load root directory at first.
        success:function(result){
            $(".entries__moveentry").remove();

            document.getElementById('move_modal_body_entries').insertAdjacentHTML('beforebegin', result);

            $(".entries__moveentry").dblclick((e) => movefiles_movetodirectory(e));

            move_current_path = path;

            amount_chosen = 0;

            $('#spinner_move').hide();
        },
        error: function(err){alert('Server sent data in unknown format.');}
     });
} 

