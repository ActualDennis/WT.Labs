<?php
    require_once('config.php');
    require_once('TemplatesHelper.php');
    require_once('WebFilesystem.php');
    require_once('Pagebuilder.php');
    require_once('WebfilesystemController.php');
    require_once('TemplatesController.php');
    require_once('NewsLetterController.php');
    require_once('mailSender.php');
    require_once('ActionsLogger.php');
    require_once('LoginController.php');

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
            case "Newsletter.php":{
                ActionsLogger::Log(WebsiteActions::Clicked, "Newsletter subscription.");
                $newsController = new NewsLetterController();
                echo $newsController->GetResponse();
                return;
            }
            case "Login.php":{
                $loginController = new LoginController();
                echo $loginController->GetResponse();
                return;
            }
        }
    }

    if(isset($_GET['sendnewsletter'])){
        $sender = new mailSender();

        $sender->SendNewsLetters("somemsg");
        
        return;
    }

    if(isset($_GET['sendlogs'])){
        $sender = new mailSender();

        $sender->SendLogs();

        return;
    }

    if(isset($_GET['page'])){
        $pageName = trim($_GET['page'], '/');
        $pagePath = '';

        ActionsLogger::Log(WebsiteActions::TriedToVisitWebpage, $_GET['page']);
        
        echo Pagebuilder::BuildPage($pageName);
        return;
    }

   echo "Content was not found on the server.";
