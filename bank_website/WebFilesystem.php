<?php 
	class WebFilesystem {
		public static function GetDirectoryListing($serverPath, $OnlyDirs) {

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
        
        public static function Redirect($destination, $clientRelativePath, $IsManualDotsHandlingUsed){

            $normalizedDestination = $clientRelativePath == "" 
            ?
            SERVER_FILESYSTEM_LOCATION.$clientRelativePath.$destination 
            :
            SERVER_FILESYSTEM_LOCATION.$clientRelativePath."/".$destination; 

            $localPath = SERVER_DIR.$clientRelativePath."/".$destination;

            // echo $clientRelativePath." ";

            // echo $localPath." ";

            // echo $$normalizedDestination;

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

        public static function DeleteFilesystemEntries($entries, $clientRelativePath){
            $isAllFilesDeleted = true;

            foreach($entries as $entry){
                if($entry == ''){
                    $isAllFilesDeleted = false;
                    continue;
                }

                $localPath = SERVER_DIR.$clientRelativePath."/".$entry;
                $localPath = str_replace("//", "/", $localPath );

                if(is_file($localPath)){
                    if(!unlink($localPath)){
                        $isAllFilesDeleted = false;
                    }
                    continue;
                }

                if(is_dir($localPath)){

                  if(!WebFilesystem::DeleteDirRecursive($localPath))
                    $isAllFilesDeleted = false;

                }
            }
            if(!$isAllFilesDeleted)
              echo json_encode(array("Successfull" => false, "ErrorMsg" => "Not all files were deleted."));
            else
              echo json_encode(array("Successfull" => true, "ErrorMsg" => ""));
            
        }

        public static function CreateFile($clientRelativePath){
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

        public static function MoveEntries($entries,$clientLocation,$newLocation){
            if($clientLocation === "/")
                $clientLocation = "";

            $newLocation = str_replace("//", "/", $newLocation );
            $errorsHappened = false;

            foreach ($entries as $entry) {
                if($entry === "." || $entry === ".."){
                    $errorsHappened = true;
                    continue;
                }

                if(is_file(SERVER_DIR.$clientLocation."/".$entry)){
                    if(!rename(SERVER_DIR.$clientLocation."/".$entry, SERVER_DIR.$newLocation."/".$entry)){
                        $errorsHappened = true;
                    }
                    continue;
                }

               if(!WebFilesystem::CopyDirRecursive(SERVER_DIR."/".$clientLocation."/".$entry, SERVER_DIR.$newLocation)){
                    $errorsHappened = true;
               }

               WebFilesystem::RemoveDirectory(SERVER_DIR."/".$clientLocation."/".$entry);
            }

            if($errorsHappened){
                echo json_encode(array("Message" => "Errors happened while moving files."));
                return;
            }

            echo json_encode(array("Message" => "Successfully moved files."));
        }


        private static function RemoveDirectory($dir) {
            
            if (is_dir($dir)) {
                $files = scandir($dir);
                foreach ($files as $file)
                    if ($file != "." && $file != "..") filesystem::removeDirectory("$dir/$file");
                rmdir($dir);
            }
            else if (file_exists($dir)) unlink($dir);
        }

    
        private static function CopyDirRecursive($src,$dst) { 
            $dir = opendir($src); 
            @mkdir($dst); 
            $IsSuccessfull = true;
            while(false !== ( $file = readdir($dir)) ) { 
                if (( $file != '.' ) && ( $file != '..' )) { 
                    if ( is_dir($src . '/' . $file) ) { 
                        if(!WebFilesystem::CopyDirRecursive($src . '/' . $file,$dst . '/' . $file)){
                            $IsSuccessfull = false;
                        } 
                    } 
                    else { 
                        if(!copy($src . '/' . $file,$dst . '/' . $file))
                            $IsSuccessfull = false;                            
                    } 
                }
            } 
            closedir($dir); 
            return $IsSuccessfull; 
        } 
        
        private static function DeleteDirRecursive($target) {
            $successfull = true;
            if(is_dir($target)){
                $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
        
                foreach( $files as $file ){
                    if(!WebFilesystem::DeleteDirRecursive( $file )){
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