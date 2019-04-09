<?php
require_once('WebFilesystem.php');

class WebfilesystemController{
    function __construct(){
       $this->WebFilesystem = new WebFilesystem();
    }

    private $WebFilesystem;

    public function GetResponse(){
        if(!isset($_GET['location'])){

            if(!isset($_POST['location'])){
                return;
            }

            $clientLocation = "/".trim($_POST['location'], "/");

            if(isset($_FILES['file'])){
                return $this->CreateUploadedFile($clientLocation);
            }

            return;
        }
        $clientLocation = "/".trim($_GET['location'], "/");
    
        if(isset($_GET['destination'])){
            return $this->Redirect($clientLocation);
        }
    
        if(isset($_GET['filesToDelete'])){
            return $this->DeleteFiles($clientLocation);
        }
    
        if(isset($_GET['whatToMove']) && isset($_GET['moveTo'])){
            return $this->MoveFiles($clientLocation);
        }
    }
    
    private function CreateUploadedFile($clientLocation){
        $result = $this->WebFilesystem->CreateFile($clientLocation);
        return $result->Message;
    }

    private function Redirect($clientLocation){
        $destination = $_GET['destination'];
        $result = $this->WebFilesystem->Redirect($destination, $clientLocation, $_GET['IsMovePage']);

        return json_encode(array(
            "Successfull" => $result->IsSuccessfull, 
            "ErrorMsg" => $result->ErrorMsg, 
            "Redirect_url" => $result->Redirect_url));
    }

    private function DeleteFiles($clientLocation){
        $filesToDelete = $_GET['filesToDelete'];
        $result = $this->WebFilesystem->DeleteFilesystemEntries($filesToDelete, $clientLocation);
        return json_encode(array("Successfull" => $result->IsSuccessfull, "ErrorMsg" => $result->ErrorMsg));
    }

    private function MoveFiles($clientLocation){
        $result = $this->WebFilesystem->MoveEntries($_GET['whatToMove'], $clientLocation, $_GET['moveTo']);
        return json_encode(array("Message" => $result->Message));
    }
}