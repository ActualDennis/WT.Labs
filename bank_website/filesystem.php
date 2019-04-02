<?php 
    if(isset($_POST['destination']) && isset($_POST['location'])){
        $destination = $_POST['destination'];
        $clientLocation = trim($_POST['location'], "/");
        filesystem::redirect($destination, $clientLocation);
    }

	class filesystem {
		public static function get_listing($serverPath) {
            $listing = scandir(SERVER_DIR."/".$serverPath);
            if($listing == FALSE){
                return FALSE;
            }
            return $listing;
        }
        
        public static function redirect($destination, $clientAbsolutePath){
            $clientRelativePath = substr($clientAbsolutePath, strlen(FILESYS_WEBPAGE) + strpos($clientAbsolutePath, FILESYS_WEBPAGE));

            $normalizedDestination = $clientRelativePath == "/" 
            ? 
            SERVER_FILESYSTEM_LOCATION.$clientRelativePath.$destination 
            :
            SERVER_FILESYSTEM_LOCATION.$clientRelativePath."/".$destination; 

            $localPath = SERVER_DIR."/".$destination;

            if(is_dir($localPath)){
                echo json_encode(array("Successfull" => true, "ErrorMsg" => "", "Redirect_url" => $normalizedDestination));
                return;
            }

            if(is_file($localPath)){
                echo json_encode(array("Successfull" => false, "ErrorMsg" => $destination." is a file.", "Redirect_url" => ""));
                return;
            }

            //dot and two dots( . and ..) are handled by browser itself.

            echo json_encode(array("Successfull" => false, "ErrorMsg" => "Directory doesn't exist.", "Redirect_url" => ""));
        }
	}
?>