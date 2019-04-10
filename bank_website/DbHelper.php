<?php

class DbHelper{

    public static $login = "denis";

    public static $pass = "12345678";

    private $dbConnection;

    public const defaultDbName = "webconfiguration";

    public function OpenConnection() : void {
        $this->dbConnection = new mysqli(Config::SERVER_WEBHOST_RELATIVE, DbHelper::$login, DbHelper::$pass, DbHelper::defaultDbName);
        if ($this->dbConnection->connect_error) {
            die("Connection failed: " .$this->dbConnection->connect_error);
        } 
    }

    public function GetConfigVar($varName){
        $sql = "SELECT Config_Value, Config_Varname FROM localconfiguration";
        $result = $this->dbConnection->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
               if($row["Config_Varname"] == $varName){
                    return $row["Config_Value"];
               }  
            }

            return "Undefined";
        }
        else{
            return "Undefined";
        }
    }

    public function CloseConnection(){
        $this->dbConnection->close();
    }
}