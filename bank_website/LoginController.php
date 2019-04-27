<?php
require_once('DbHelper.php');

class LoginController
{
    public function GetResponse() : string {
        if(isset($_POST['RegisterLogin']) && isset($_POST['RegisterPassword'])){
            $db = new DbHelper();
            $db->OpenConnection();
            $result = $db->RegisterUser($_POST['RegisterLogin'], hash('sha256', $_POST['RegisterPassword']));
            $db->CloseConnection();

            if(!$result){
                return json_encode(array("IsSuccessfull" => false,"ErrorMessage" => "User with such login exists."));
            }

            return json_encode(array("IsSuccessfull" => true, "ErrorMessage" => ""));
        }

        if(isset($_POST['Login'])
        && isset($_POST['Password'])
        && isset($_POST['NotMyComputer'])
        && isset($_POST['RememberUser']))
        {
            $db = new DbHelper();
            $db->OpenConnection();
            $result = $db->CheckUserCredentials($_POST['Login'], hash('sha256', $_POST['Password']));
            $db->CloseConnection();

            if(!$result){
                return json_encode(array("IsSuccessfull" => false,"ErrorMessage" => "Wrong login / password pair."));
            }

            if($_POST['NotMyComputer'] == 'true'){
                setcookie(
                    Config::COOKIE_LOGIN_NAME,
                    $_POST['Login'],
                    time() + Config::COOKIE_UNTRUSTED_EXPIREIN , "", "", true
                );

                return json_encode(array("IsSuccessfull" => true, "ErrorMessage" => ""));
            }

            if($_POST['RememberUser'] == 'true'){
                setcookie(
                    Config::COOKIE_LOGIN_NAME,
                    $_POST['Login'],
                    time() + Config::COOKIE_TRUSTED_EXPIREIN
                );

                return json_encode(array("IsSuccessfull" => true, "ErrorMessage" => ""));
            }

            setcookie(
                Config::COOKIE_LOGIN_NAME,
                $_POST['Login'],
                time() + Config::COOKIE_TEMPORARY_EXPIREIN
            );

            return json_encode(array("IsSuccessfull" => true, "ErrorMessage" => ""));
        }


        return json_encode(array("IsSuccessfull" => false,"ErrorMessage" => "Wrong POST parameters were provided."));
    }
}