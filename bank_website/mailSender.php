<?php

require_once('DbHelper.php');

class mailSender{

    public $sendFrom = "admin@localhost.com";
    
    public function sendMessage(string $To, string $message, string $subject = "News from Bank website.") : bool {
        $header = "From:$this->sendFrom \r\n";
        $header .= "Cc:$To \r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html\r\n";

        return mail ($To,$subject,$message,$header);
    }

    public function SendNewsLetters(string $message){
        $db = new DbHelper();

        $db->OpenConnection();

        $emails = $db->GetNewsLetterEmails();

        $db->CloseConnection();

        if(empty($emails))
            return;

        foreach($emails as $email){
            $this->sendMessage($email, $message);
        }
    }
}