function show_register_page() {
    $.ajax({
        url:"/index.php?script=TemplatesHelper.php",
        type: "GET",
        dataType: 'text',
        data: {Template: 'Register' },
        success: function(result){
            if(result == ""){
                alert("Error happened. Please reload the page.");
                return;
            }

            $('#tile_login').remove();
            $('#login_tile_wrapper').append(result);
        },
        error: function(err){alert('Server sent data in unknown format.');}
    });
}

function register_submit() {
    let login = $('#logintile_register_login')[0];
    let pass = $('#logintile_register_pass')[0];
    let repeatPass =$('#logintile_register_repeatpass')[0];

    if(pass.value !== repeatPass.value ){
        alert("Passwords are not equal.");
        return;
    }

    $.ajax({
        url:"/index.php?script=Login.php",
        type: "POST",
        dataType: 'json',
        data: {RegisterLogin: login.value, RegisterPassword: pass.value },
        success: function(result){
            if(!result.IsSuccessfull){
                alert(result.ErrorMessage);
                return;
            }

            alert("Successfully registered.");
        },
        error: function(err){alert('Server sent data in unknown format.');}
    });
}

function login() {
    let log = $('#login_login')[0].value;
    let pass = $('#login_password')[0].value;
    let NotMyPc = $('#login_NotMyComputer')[0];
    let rememberUser = $('#login_RememberUser')[0];

    $.ajax({
        url:"/index.php?script=Login.php",
        type: "POST",
        dataType: 'json',
        data: {Login: log, Password: pass, NotMyComputer: NotMyPc.checked, RememberUser: rememberUser.checked },
        success: function(result){
            if(!result.IsSuccessfull){
                alert(result.ErrorMessage);
                return;
            }

            location.href = "/controlpanel";
        },
        error: function(err){alert('Server sent data in unknown format.');}
    });
}

function delete_client(e) {

    let login = '';

    if($(e.target).is('.button__text')){

        login = $(e.target.parentNode.parentNode).find('span.entry_login')[0].childNodes[0].data;

    }else if($(e.target).is('.btn-deleteclient')){

        login = $(e.target.parentNode).find('span.entry_login')[0].childNodes[0].data;

    }

    $.ajax({
        url:"/index.php?script=Login.php",
        type: "POST",
        dataType: 'json',
        data: {UserToRemove: login },
        success: function(result){
            if(!result.IsSuccessfull){
                alert(result.ErrorMessage);
                return;
            }

            location.reload();
        },
        error: function(err){alert('Server sent data in unknown format.');}
    });
}

function make_admin(e) {
    let login = '';

    if($(e.target).is('.button__text')){

        login = $(e.target.parentNode.parentNode).find('span.entry_login')[0].childNodes[0].data;

    }else if($(e.target).is('.btn-makeadmin')){

        login = $(e.target.parentNode).find('span.entry_login')[0].childNodes[0].data;

    }

    $.ajax({
        url:"/index.php?script=Login.php",
        type: "POST",
        dataType: 'json',
        data: {UserToMakeAdmin: login },
        success: function(result){
            if(!result.IsSuccessfull){
                alert(result.ErrorMessage);
                return;
            }

            location.reload();
        },
        error: function(err){alert('Server sent data in unknown format.');}
    });
}