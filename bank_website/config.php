<?php 

   class Config{
      public const SERVER_DIR = "H:/Server/public_storage";

      public const SERVER_CORE_DIR = "H:/Server/Apache/htdocs";

      public const SERVER_DIR_RELATIVE = "public_storage";

      public const SERVER_WEBHOST = "http://www.localhost";

      public const SERVER_WEBHOST_RELATIVE = "localhost";

      public const SERVER_FILESYSTEM_LOCATION = "http://www.localhost/filesystem";

      public const FILESYS_WEBPAGE = "filesystem";

      public const FILESYSTEM_HTMLPATH = "./filesystem_folder/filesystem.html";

      public const FILESYSTEM_MOVEENTRY_HTMLPATH = "./templates/filesystem_move_entry.html";

      public const COOKIE_UNTRUSTED_EXPIREIN = 600;

      public const COOKIE_TRUSTED_EXPIREIN = 7776000; // 90 days in seconds

      public const COOKIE_TEMPORARY_EXPIREIN = 3600; // 1 hour

      public  const COOKIE_LOGIN_NAME = 'bank_auth_credentials';

      public static $VARS = array("myVar" => "testVar2");

      public static function getUserDefinedConstant($constantName) {
         $reflection = new ReflectionClass(__CLASS__);
         return $reflection->getConstant($constantName);
      }

      public static function getRuntimeVar($varName) {
         return Config::$VARS[$varName] ?? "Undefined";
      }
   }
  
?>