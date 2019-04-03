<?php 
    if(!isset($_POST['location'])){
        return;
    }

    $clientLocation = trim($_POST['location'], "/");

    if(isset($_POST['destination'])){
        $destination = $_POST['destination'];
        filesystem::redirect($destination, $clientLocation);
    }

    if(isset($_POST['filesToDelete'])){
        $filesToDelete = $_POST['filesToDelete'];
        filesystem::delete_files($filesToDelete, $clientLocation);
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

            $localPath = SERVER_DIR.$clientRelativePath."/".$destination;

            //dot and two dots( . and ..) are handled by browser itself.

            if(is_dir($localPath)){
                echo json_encode(array("Successfull" => true, "ErrorMsg" => "", "Redirect_url" => $normalizedDestination));
                return;
            }

            if(is_file($localPath)){
                echo json_encode(array("Successfull" => false, "ErrorMsg" => $destination." is a file.", "Redirect_url" => ""));
                return;
            }

            echo json_encode(array("Successfull" => false, "ErrorMsg" => "Directory doesn't exist.", "Redirect_url" => ""));
        }

        public static function delete_files($files, $clientAbsolutePath){
            $isAllFilesDeleted = true;
            
            foreach($files as $file){
                $localPath = SERVER_DIR.$clientRelativePath."/".$file;

                if(is_file($file)){
                    if(!unlink($localPath)){
                        $isAllFilesDeleted = false;
                    }
                }else
                $isAllFilesDeleted = false;
            }
            if(!$isAllFilesDeleted)
              echo json_encode(array("Successfull" => false, "ErrorMsg" => "Not all files were deleted."));
            else
              echo json_encode(array("Successfull" => true, "ErrorMsg" => ""));
            
        } 
	}
?>