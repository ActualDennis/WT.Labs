<?php
    require_once('config.php');
    require_once('TemplatesHelper.php');
    require_once('WebFilesystem.php');
    require_once('Pagebuilder.php');
    require_once('WebfilesystemController.php');
    require_once('TemplatesController.php');

    if(isset($_GET['script'])){

        $scriptName = substr($_GET['script'], 0 , strpos($_GET['script'], "php") + strlen("php"));
        switch($scriptName){
            case "WebFilesystem.php":{
                $fsController = new WebfilesystemController();
                echo $fsController->GetResponse();
                return;
            }
            case "TemplatesHelper.php":{
                $templatesController = new TemplatesController();
                echo $templatesController->GetResponse();
                return;
            }
        }
    }

    if(isset($_GET['page'])){
        $pageName = trim($_GET['page'], '/');
        $pagePath = '';
    
        echo Pagebuilder::BuildPage($pageName);
        return;
    }

   echo "Page was not found on the server.";
