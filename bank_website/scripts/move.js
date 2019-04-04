
function change_move_chosen_entry(elementId){

    var element = document.getElementById(elementId);

    if(element.classList.contains('entries__moveentry--chosen')){
        element.classList.remove('entries__moveentry--chosen');
        return;
    }

    element.classList.add('entries__moveentry--chosen');
}

function movefiles_movetodirectory(e){
    
}
