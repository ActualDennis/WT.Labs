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

        public static function delete_files($entries, $clientAbsolutePath){
            $isAllFilesDeleted = true;

            $clientRelativePath = substr($clientAbsolutePath, strlen(FILESYS_WEBPAGE) + strpos($clientAbsolutePath, FILESYS_WEBPAGE));

            foreach($entries as $entry){
                if($entry == ''){
                    $isAllFilesDeleted = false;
                    continue;
                }

                $localPath = SERVER_DIR.$clientRelativePath."/".$entry;

                if(is_file($localPath)){
                    if(!unlink($localPath)){
                        $isAllFilesDeleted = false;
                    }
                    continue;
                }

                if(is_dir($localPath)){

                  if(!filesystem::delete_folder_recursive($localPath))
                    $isAllFilesDeleted = false;

                }
            }
            if(!$isAllFilesDeleted)
              echo json_encode(array("Successfull" => false, "ErrorMsg" => "Not all files were deleted."));
            else
              echo json_encode(array("Successfull" => true, "ErrorMsg" => ""));
            
        }
        
        private static function delete_folder_recursive($target) {
            $successfull = true;
            if(is_dir($target)){
                $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
        
                foreach( $files as $file ){
                    if(!filesystem::delete_folder_recursive( $file )){
                        $successfull = false;
                    }      
                }
        
                rmdir( $target );
                return $successfull;
            } elseif(is_file($target)) {
                if(!unlink( $target )){
                    return false;
                } 
                
                return true; 
            }
        }
	}
?>