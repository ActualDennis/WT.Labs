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

    public function SendLogs(){
        $db = new DbHelper();

        $db->OpenConnection();

        $logsToSend = $db->GetLogs();

        $db->CloseConnection();

        $content = '';

        foreach ($logsToSend as $log){
            $content .=
                $log['IpAddress']
                .": "
                .$log['Name']
                .$log['ActionName']
                ." "
                .$log['UserAgent']
                ." "
                .$log['DateVisited']
                ."\n";
        }

        $content = chunk_split(base64_encode($content));
        $uid = md5(uniqid(time()));
        $name = "banklogs.txt";

        // header
        $header = "From: ".$this->sendFrom." <".$this->sendFrom.">\r\n";
        $header .= "Reply-To: "."noreply@localhost.com"."\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";

        // message & attachment
        $nmessage = "--".$uid."\r\n";
        $nmessage .= "Content-type:text/plain; charset=iso-8859-1\r\n";
        $nmessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $nmessage .= "Logs report."."\r\n\r\n";
        $nmessage .= "--".$uid."\r\n";
        $nmessage .= "Content-Type: application/octet-stream; name=\"".$name."\"\r\n";
        $nmessage .= "Content-Transfer-Encoding: base64\r\n";
        $nmessage .= "Content-Disposition: attachment; filename=\"".$name."\"\r\n\r\n";
        $nmessage .= $content."\r\n\r\n";
        $nmessage .= "--".$uid."--";

        mail($this->sendFrom, "Logs", $nmessage, $header);
    }
}