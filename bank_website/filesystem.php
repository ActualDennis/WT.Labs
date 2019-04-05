<?php 
    if(!isset($_POST['location'])){
        return;
    }

    $clientLocation = trim($_POST['location'], "/");

    if(isset($_POST['destination'])){
        $destination = $_POST['destination'];
        filesystem::redirect($destination, $clientLocation, $_POST['IsMovePage']);
        return;
    }

    if(isset($_POST['filesToDelete'])){
        $filesToDelete = $_POST['filesToDelete'];
        filesystem::delete_files($filesToDelete, $clientLocation);
        return;
    }

    if(isset($_FILES['file'])){
        filesystem::create_file($clientLocation);
        return;
    }

    if(isset($_POST['whatToMove']) && isset($_POST['moveTo'])){
        filesystem::move_entries($_POST['whatToMove'], $clientLocation, $_POST['moveTo']);
        return;
    }

	class filesystem {
		public static function get_listing($serverPath, $OnlyDirs) {

            $listing = scandir(SERVER_DIR."/".$serverPath);

            if($listing == FALSE){
                return FALSE;
            }

            if($OnlyDirs){
                $result = array();
                
                foreach($listing as $entry){
                    if(is_dir(SERVER_DIR."/".$serverPath."/".$entry) || $entry == "." || $entry == ".."){
                        array_push($result, $entry);
                    }
                }

                return $result;
            }


            return $listing;
        }
        
        public static function redirect($destination, $clientAbsolutePath, $IsManualDotsHandlingUsed){

            $clientRelativePath = substr($clientAbsolutePath, strlen(FILESYS_WEBPAGE) + strpos($clientAbsolutePath, FILESYS_WEBPAGE));

            $normalizedDestination = $clientRelativePath == "/" 
            ?
            SERVER_FILESYSTEM_LOCATION.$clientRelativePath.$destination 
            :
            SERVER_FILESYSTEM_LOCATION.$clientRelativePath."/".$destination; 

            $localPath = SERVER_DIR.$clientRelativePath."/".$destination;

            // echo $clientAbsolutePath;

            // echo $clientRelativePath;

            // echo $localPath;

            if($IsManualDotsHandlingUsed == 'true'){
                if($destination == "."){
                    echo json_encode(array("Successfull" => true, "ErrorMsg" => "", "Redirect_url" => SERVER_FILESYSTEM_LOCATION.$clientRelativePath));
                    return;
                }

                if($destination == ".."){

                    if($clientRelativePath == "/" || $clientRelativePath == ""){
                        echo json_encode(array("Successfull" => true, "ErrorMsg" => "", "Redirect_url" => SERVER_FILESYSTEM_LOCATION));
                        return;
                    }
                    $temp = substr($normalizedDestination, 0 , strrpos($normalizedDestination, "/"));
                    echo json_encode(array("Successfull" => true, "ErrorMsg" => "", "Redirect_url" => substr($temp, 0 , strrpos($temp, "/"))));
                    return;
                }
            }

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

        public static function create_file($clientAbsolutePath){
            $clientRelativePath = substr($clientAbsolutePath, strlen(FILESYS_WEBPAGE) + strpos($clientAbsolutePath, FILESYS_WEBPAGE));

            if(!file_exists(SERVER_DIR.$clientRelativePath."/".$_FILES['file']['name'])){
                
                if(!move_uploaded_file($_FILES['file']['tmp_name'], SERVER_DIR.$clientRelativePath."/".$_FILES['file']['name'])){
                    echo "Error happened while moving tmp file to server's directory.: ".$_FILES['file']['name'];
                    return;
                }

                echo "Successfully uploaded file: ".$_FILES['file']['name'];
                return;
            }

            echo "File already exists: ".$_FILES['file']['name'];

        }

        public static function move_entries($entries,$clientLocation,$newLocation){
            $newLocation = str_replace('/filesystem', "", $newLocation);

            foreach ($entries as $entry) {
                if(is_file(SERVER_DIR."/".$clientLocation."/".$entry)){
                    rename(SERVER_DIR."/".$clientLocation."/".$entry, SERVER_DIR.$newLocation."/".$entry);
                    continue;
                }

               filesystem::recurse_copy(SERVER_DIR."/".$clientLocation."/".$entry, SERVER_DIR.$newLocation);

               filesystem::rrmdir(SERVER_DIR."/".$clientLocation."/".$entry);
            }

            echo json_encode(array("Message" => "Successfully moved files."));
        }


        private static function rrmdir($dir) {
            
            if (is_dir($dir)) {
                $files = scandir($dir);
                foreach ($files as $file)
                    if ($file != "." && $file != "..") filesystem::rrmdir("$dir/$file");
                rmdir($dir);
            }
            else if (file_exists($dir)) unlink($dir);
        }

    
        private static function recurse_copy($src,$dst) { 
            $dir = opendir($src); 
            @mkdir($dst); 
            while(false !== ( $file = readdir($dir)) ) { 
                if (( $file != '.' ) && ( $file != '..' )) { 
                    if ( is_dir($src . '/' . $file) ) { 
                        filesystem::recurse_copy($src . '/' . $file,$dst . '/' . $file); 
                    } 
                    else { 
                        copy($src . '/' . $file,$dst . '/' . $file); 
                    } 
                } 
            } 
            closedir($dir); 
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