<?php
    require('config.php');
    require('templaiter.php');
    require('filesystem.php');
    require('pagebuilder.php');

    $pageName = '';

    if(!isset($_GET['page'])){
        echo "Page was not found on the server.";
        return;
    }

    $pageName = trim($_GET['page'], '/');
    $pagePath = '';

    switch($pageName){
        case "about":{
            $pagePath = "./about_folder/about.html";
            break;
        }
        case "login":{
            $pagePath = "./login_folder/login.html";
            break;
        }
        case "support":{
            $pagePath = "./support_folder/support.html";
            break;
        }
        case "welcome":
        case "main":{
            $pagePath = "./index_folder/index.html";
            break;
        }
        case "filesystem":{
            if(!isset($_GET['loc'])){
                echo "Specify directory with 'loc' parameter.";
                return;
            }
            $result = filesystem::get_listing($_GET['loc']);
            $result = templaiter::resolve_filesystem_entries($result, SERVER_DIR.$_GET['loc'].'/');
            $result = pagebuilder::build_filesystem_page($result);
           
            break;
        }
        default:{
            echo $pageName;
            echo " was not found on the server."; 
            return;
        }
    }

    echo templaiter::resolve_date_time($pagePath);


?>