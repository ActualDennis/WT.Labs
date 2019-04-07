<?php
    require_once('config.php');
    require_once('TemplatesHelper.php');
    require_once('WebFilesystem.php');
    require_once('Pagebuilder.php');

    if(isset($_GET['script'])){

        $scriptName = substr($_GET['script'], 0 , strpos($_GET['script'], "php") + strlen("php"));
        switch($scriptName){
            case "WebFilesystem.php":{

                $fileSystem = new WebFilesystem();

                if(!isset($_GET['location'])){

                    if(!isset($_POST['location'])){
                        return;
                    }

                    $clientLocation = "/".trim($_POST['location'], "/");

                    if(isset($_FILES['file'])){
                        $result = $fileSystem->CreateFile($clientLocation);
                        echo $result->Message;
                        return;
                    }

                    return;
                }
                $clientLocation = "/".trim($_GET['location'], "/");
            
                if(isset($_GET['destination'])){
                    $destination = $_GET['destination'];
                    $result = $fileSystem->Redirect($destination, $clientLocation, $_GET['IsMovePage']);

                    echo json_encode(array(
                        "Successfull" => $result->IsSuccessfull, 
                        "ErrorMsg" => $result->ErrorMsg, 
                        "Redirect_url" => $result->Redirect_url));

                    return;
                }
            
                if(isset($_GET['filesToDelete'])){
                    $filesToDelete = $_GET['filesToDelete'];
                    $result = $fileSystem->DeleteFilesystemEntries($filesToDelete, $clientLocation);
                    echo json_encode(array("Successfull" => $result->IsSuccessfull, "ErrorMsg" => $result->ErrorMsg));
                    return;
                }
            
                if(isset($_GET['whatToMove']) && isset($_GET['moveTo'])){
                    $result = $fileSystem->MoveEntries($_GET['whatToMove'], $clientLocation, $_GET['moveTo']);
                    echo json_encode(array("Message" => $result->Message));
                    return;
                }

                return;
            }
            case "TemplatesHelper.php":{
                if(!isset($_GET['location'])){
                    return;
                }
                $fileSystem = new WebFilesystem();
                $clientLocation = trim($_GET['location'], "/");
                $listing = $fileSystem->GetDirectoryListing($clientLocation, true);
                echo TemplatesHelper::GetMoveEntryTemplates($listing, SERVER_DIR."/".$clientLocation."/");
                return;
            }
        }
    }

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
            $fileSystem = new WebFilesystem();
            $result = $fileSystem->GetDirectoryListing($_GET['loc'], false);
            $result = TemplatesHelper::GetEntriesTemplates($result, SERVER_DIR.$_GET['loc'].'/');
            echo Pagebuilder::BuildFilesystemPage($result);
           
            return;
        }
        default:{
            echo $pageName;
            echo " was not found on the server."; 
            return;
        }
    }

    echo TemplatesHelper::ResolveDateTimeTemplate($pagePath);
