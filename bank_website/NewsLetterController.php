<?php 
require_once('DbHelper.php');

class NewsLetterController{
    public function GetResponse() : string{
        if(!isset($_POST["email"])){
            return json_encode(array("Successfull" => false, "ErrorMsg" =>"No email was provided."));
        }

        $email = $_POST["email"];
        preg_match('/([^@]*)@([^@].).[\s\S]*/',$email, $matches);

        if(empty($matches)){
            return json_encode(array("Successfull" => false, "ErrorMsg" => $email." is not a valid e-mail address."));
        }

        $db = new DbHelper();

        $db->OpenConnection();
        $result = $db->RegisterForNewsLetter($email);
        $db->CloseConnection();

        if($result){
            return json_encode(array("Successfull" => true, "ErrorMsg" => ""));
        }
        else{
            return json_encode(array("Successfull" => false, "ErrorMsg" => "Failed to register this email. Probably you've already registered."));
        }

    }
}