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

    public function GetNewsLetterEmails(){
        $sql = 
        "SELECT Subscriber_Id, Email FROM newslettersubscribers";

        $result = $this->dbConnection->query($sql);

        if(!$result)
            return false;

        $resultArr = array();

        while($row = $result->fetch_assoc()) {
            array_push($resultArr, $row["Email"]);
        }

        return $resultArr;
    }

    public function GetLogs(){
        $sql =
        "SELECT ActionName, UserAgent, DateVisited, website_users.IpAddress, website_actions.Name 
        FROM usersactions
        INNER JOIN website_users on usersactions.User_Id = website_users.User_Id
        INNER JOIN website_actions on usersactions.nonNormalizedAction_Id = website_actions.Action_Id";

        $result = $this->dbConnection->query($sql);

        $resultArr = array();

        while($row = $result->fetch_assoc()) {
            array_push($resultArr, $row);
        }

        return $resultArr;
    }

    public function LogAction(int $actionKey, string $action){
        $thisUserIP = $_SERVER['REMOTE_ADDR'];

        $sql =  
        "SELECT User_Id, IpAddress 
        FROM website_users
        WHERE IpAddress='$thisUserIP'";

        $result = $this->dbConnection->query($sql);

        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $userId = '';

        if(mysqli_num_rows($result) == 0){
            $sql = "INSERT INTO `website_users` (`User_Id`, `IpAddress`) VALUES (NULL, '$thisUserIP')";

            $result = $this->dbConnection->query($sql);

            $userId = mysqli_insert_id($this->dbConnection);
        }else{
            $row = $result->fetch_assoc();
            $userId = $row['User_Id'];
        }

        $sql = "INSERT INTO `usersactions` (`Action_Id`, `User_Id`, `UserAgent`, `ActionName`, `nonNormalizedAction_Id`, `DateVisited`) VALUES (NULL, $userId, '$userAgent', '$action', $actionKey, CURRENT_TIMESTAMP())";

        $this->dbConnection->query($sql);
    }

    public function RegisterUser($login, $passwordHash) : bool{
        $sql =
        "SELECT Client_Id,Login  
        FROM clients
        WHERE Login='$login'";

        $result = $this->dbConnection->query($sql);

        if(mysqli_num_rows($result) == 0) {
            $sql = "INSERT INTO `clients` (`Client_Id`, `Login`,`PasswordHash`) VALUES (NULL, '$login', '$passwordHash')";

            $this->dbConnection->query($sql);

            $Client_Id = mysqli_insert_id($this->dbConnection);
                                                                                        //non-admin
            $sql = "INSERT INTO `clients_roles` (`Client_Id`, `Role_Id`) VALUES ($Client_Id, 2)";

            $this->dbConnection->query($sql);

            return true;
        }

        return false;
    }

    public function CheckUserCredentials($login, $passwordHash) : bool{
        $sql =
        "SELECT Client_Id,Login, PasswordHash
        FROM clients
        WHERE (Login='$login') AND (PasswordHash='$passwordHash')";

        $result = $this->dbConnection->query($sql);

        return mysqli_num_rows($result) != 0;
    }

    public function GetUsersAndRoles() : array {
        $sql =
        "SELECT server_roles.Name,clients.Login
        FROM clients_roles
        INNER JOIN server_roles ON server_roles.Role_Id=clients_roles.Role_Id
        INNER JOIN clients ON clients.Client_Id=clients_roles.Client_Id";

        $result = $this->dbConnection->query($sql);

        $returned = array();

        while($row = $result->fetch_assoc()) {
            $temp = array();
            $temp["Username"] = $row['Login'];
            $temp["Role"] = $row['Name'];
            array_push($returned, $temp);
        }

        return $returned;
    }

    public function IsAdmin(array $usersAndRoles, string $userLogin){
        foreach ($usersAndRoles as $userRole){
            if($userRole['Username'] == $userLogin){
                return $userRole['Role'] == 'Admin';
            }
        }
    }

    public function CanBeRemoved($whoRemovesUsername, $targetUsername){
        $sql =
        "SELECT server_roles.Name,clients.Login
        FROM clients_roles
        INNER JOIN server_roles ON server_roles.Role_Id=clients_roles.Role_Id
        INNER JOIN clients ON clients.Client_Id=clients_roles.Client_Id
        WHERE (Login='$whoRemovesUsername') OR (Login='$targetUsername')";

        $result = $this->dbConnection->query($sql);


        if(mysqli_num_rows($result) != 2){
            return false;
        }

        $usersArray = array();

        while($row = $result->fetch_assoc()) {
            array_push($usersArray, $row);
        }

        //1. Only admin can remove users; 2. admin can only remove non-admins.

        if($usersArray[0]['Login'] == $whoRemovesUsername){
            if($usersArray[0]['Name'] == 'Admin' && $usersArray[1]['Name'] !== 'Admin'){
                return true;
            }
        }else {
            if($usersArray[1]['Name'] == 'Admin' && $usersArray[0]['Name'] !== 'Admin'){
                return true;
            }
        }

        return false;
    }

    public function DeleteClient($clientName){
        $sql = "DELETE FROM `clients` WHERE Login='$clientName' ";
        $this->dbConnection->query($sql);
    }

    public function MakeAdmin($whoChangesUsername, $targetUsername) : bool{
        $sql =
        "SELECT server_roles.Name,clients.Login, clients_roles.Client_Id
        FROM clients_roles
        INNER JOIN server_roles ON server_roles.Role_Id=clients_roles.Role_Id
        INNER JOIN clients ON clients.Client_Id=clients_roles.Client_Id
        WHERE (Login='$whoChangesUsername') OR (Login='$targetUsername')";

        $result = $this->dbConnection->query($sql);

        if(mysqli_num_rows($result) != 2){
            return false;
        }

        $usersArray = array();

        while($row = $result->fetch_assoc()) {
            array_push($usersArray, $row);
        }

        $sql = "UPDATE clients_roles
         SET Role_Id=1 WHERE Client_Id=";

        if($usersArray[0]['Login'] == $whoChangesUsername){

            if($usersArray[0]['Name'] == 'Admin' && $usersArray[1]['Name'] !== 'Admin'){
                $sql .= $usersArray[1]['Client_Id'];

                $this->dbConnection->query($sql);

                return true;
            }

        }else {

            if($usersArray[1]['Name'] == 'Admin' && $usersArray[0]['Name'] !== 'Admin'){
                $sql .= $usersArray[0]['Client_Id'];

                $this->dbConnection->query($sql);

                return true;
            }

        }

        return false;
    }

}