<?php

class DbHelper{

    public static $login = "denis";

    public static $pass = "12345678";

    private $dbConnection;

    public const defaultDbName = "BankDb";

    public function OpenConnection() : void {
        $this->dbConnection = new mysqli(Config::SERVER_WEBHOST_RELATIVE, DbHelper::$login, DbHelper::$pass, DbHelper::defaultDbName);
        if ($this->dbConnection->connect_error) {
            die("Connection failed: " .$this->dbConnection->connect_error);
        } 
    }
    
    public function CloseConnection(){
        $this->dbConnection->close();
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

    public function GetSiteMapCategories() : array {
        $sql = 
        "SELECT sitehrefs.Link_Id, sitehrefs.Href, sitehrefs.LinkName, categories.Category_Id, categories.CategoryName  
        FROM sitehrefscategories
        INNER JOIN sitehrefs ON sitehrefs.Link_Id=sitehrefscategories.Link_Id
        INNER JOIN categories ON categories.Category_Id=sitehrefscategories.Category_Id";

        $result = $this->dbConnection->query($sql);
        $categories = array();
        $category = array();
        $lastCategoryId = 0;

        while($row = $result->fetch_assoc()) {

            if($row["Category_Id"] != $lastCategoryId && $lastCategoryId != 0){

                $lastCategoryId = $row["Category_Id"];
                array_push($categories, $category);
                $category = array();
            }else if ($lastCategoryId == 0){
                $lastCategoryId = $row["Category_Id"];
            }
            $category["CategoryName"] = $row["CategoryName"];
            $keyValuePair = array();
            $keyValuePair["Href"] = $row["Href"];
            $keyValuePair["LinkName"] = $row["LinkName"];

            array_push($category, $keyValuePair);
        }

        array_push($categories, $category);
        
        return $categories;
    }

    public function RegisterForNewsLetter($email) : bool {
        $sql = 
        "SELECT Subscriber_Id, Email FROM newslettersubscribers
         WHERE Email='$email'";


        $result = $this->dbConnection->query($sql);

        if(!$result)
            return false;
    
        if(mysqli_num_rows($result) != 0){
            return false;
        }

        $sql = 
        "INSERT INTO `newslettersubscribers` (`Subscriber_Id`, `Email`) VALUES (NULL, '$email')";

        $result = $this->dbConnection->query($sql);

        if(!$result){
            return false;
        }

        return true;
    }
}