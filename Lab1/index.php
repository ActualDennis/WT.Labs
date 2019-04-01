<?php
    $pageName = '';

    if(!isset($_GET['page'])){
        print_r($_GET);
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
            $pagePath = "./index.html";
            break;
        }
        default:{
            echo $pageName;
            echo " was not found on the server."; 
            return;
        }
    }

    $pagecontents = file_get_contents($pagePath);

    $pagecontents = str_replace("{DATE}", date("D/M/d"), $pagecontents);
    $pagecontents = str_replace("{TIME}", date("H:i:s"), $pagecontents);
    echo $pagecontents;
?>