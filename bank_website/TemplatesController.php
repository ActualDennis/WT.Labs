<?php
require_once('WebFilesystem.php');
require_once('TemplatesHelper.php');

class TemplatesController{
    function __construct(){
        $this->WebFilesystem = new WebFilesystem();
    }
 
    private $WebFilesystem;

    public function GetResponse(){
        if(!isset($_GET['location'])){

            if(!isset($_GET['Template'])){
                return;
            }

            if($_GET['Template'] == 'Register'){
                return TemplatesHelper::GetLoginRegisterTemplate();
            }

            return;
        }

        $clientLocation = trim($_GET['location'], "/");
        $listing = $this->WebFilesystem->GetDirectoryListing($clientLocation, true);
        return TemplatesHelper::GetMoveEntryTemplates($listing, Config::SERVER_DIR."/".$clientLocation."/");
    }
}