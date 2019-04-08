<?php 
    class FileSystemActionResult{
        public $IsSuccessfull;

        public $ErrorMsg;

        public $Redirect_url;

        public $Message;
    }

	class WebFilesystem {
		public function GetDirectoryListing(string $serverPath, bool $OnlyDirs) {

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
        
        public function Redirect(string $destination,string $clientRelativePath,bool $IsManualDotsHandlingUsed) : FileSystemActionResult{

            $normalizedDestination = $clientRelativePath == "" 
            ?
            SERVER_FILESYSTEM_LOCATION.$clientRelativePath.$destination 
            :
            SERVER_FILESYSTEM_LOCATION.$clientRelativePath."/".$destination; 

            $localPath = SERVER_DIR.$clientRelativePath."/".$destination;

            if($IsManualDotsHandlingUsed == 'true'){
                if($destination == "."){
                    $result = new FileSystemActionResult();
                    $result->IsSuccessfull = true;
                    $result->ErrorMsg = "";
                    $result->Redirect_url = SERVER_FILESYSTEM_LOCATION.$clientRelativePath;

                    return $result;
                }

                if($destination == ".."){

                    if($clientRelativePath == "/" || $clientRelativePath == ""){
                        $result = new FileSystemActionResult();
                        $result->IsSuccessfull = true;
                        $result->ErrorMsg = "";
                        $result->Redirect_url = SERVER_FILESYSTEM_LOCATION;

                        return $result;
                    }

                    $temp = substr($normalizedDestination, 0 , strrpos($normalizedDestination, "/"));
                    $result = new FileSystemActionResult();
                    $result->IsSuccessfull = true;
                    $result->ErrorMsg = "";
                    $result->Redirect_url = substr($temp, 0 , strrpos($temp, "/"));

                    return $result;
                }
            }

            if(is_dir($localPath)){
                $result = new FileSystemActionResult();
                $result->IsSuccessfull = true;
                $result->ErrorMsg = "";
                $result->Redirect_url = $normalizedDestination;

                return $result;
            }

            if(is_file($localPath)){
                $result = new FileSystemActionResult();
                $result->IsSuccessfull = false;
                $result->ErrorMsg =  $destination." is a file.";
                $result->Redirect_url = "";

                return $result;
            }

            $result = new FileSystemActionResult();
            $result->IsSuccessfull = false;
            $result->ErrorMsg =  "Directory doesn't exist.";
            $result->Redirect_url = "";

            return $result;
        }

        public function DeleteFilesystemEntries($entries, $clientRelativePath) : FileSystemActionResult{
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

            if(!$isAllFilesDeleted){
                $result = new FileSystemActionResult();
                $result->IsSuccessfull = false;
                $result->ErrorMsg =  "Not all files were deleted.";
            }
            else{
                $result = new FileSystemActionResult();
                $result->IsSuccessfull = true;
                $result->ErrorMsg =  "";
            }
            
            return $result;
            
        }

        public function CreateFile($clientRelativePath) : FileSystemActionResult{
            $result = new FileSystemActionResult();
            
            if(!file_exists(SERVER_DIR.$clientRelativePath."/".$_FILES['file']['name'])){

                if(!move_uploaded_file($_FILES['file']['tmp_name'], SERVER_DIR.$clientRelativePath."/".$_FILES['file']['name'])){
                    $result->Message = "Error happened while moving tmp file to server's directory.: ".$_FILES['file']['name'];
                    return $result;
                }

                $result->Message = "Successfully uploaded file: ".$_FILES['file']['name'];
                return $result;
            }

            $result->Message = "File already exists: ".$_FILES['file']['name'];
            return $result;

        }

        public function MoveEntries($entries,$clientLocation,$newLocation) : FileSystemActionResult {
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

            $result = new FileSystemActionResult();

            if($errorsHappened){
                $result->Message = "Errors happened while moving files.";
                return $result;
            }

            $result->Message = "Successfully moved files.";
            return $result;
        }


        private function RemoveDirectory($dir) : void {
            
            if (is_dir($dir)) {
                $files = scandir($dir);
                foreach ($files as $file)
                    if ($file != "." && $file != "..") WebFilesystem::RemoveDirectory("$dir/$file");
                rmdir($dir);
            }
            else if (file_exists($dir)) unlink($dir);
        }

    
        private function CopyDirRecursive($src,$dst) : bool { 
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
        
        private function DeleteDirRecursive($target) : bool {
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