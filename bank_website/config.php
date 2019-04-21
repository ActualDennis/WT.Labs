<?php 

   class Config{
      public const SERVER_DIR = "H:/Server/public_storage";

      public const SERVER_CORE_DIR = "H:/Server/Apache/htdocs";

      public const SERVER_DIR_RELATIVE = "public_storage";

      public const SERVER_WEBHOST = "http://www.localhost";

      public const SERVER_WEBHOST_RELATIVE = "localhost";

      public const SERVER_FILESYSTEM_LOCATION = "http://www.localhost/filesystem";

      public const FILESYS_WEBPAGE = "filesystem";

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