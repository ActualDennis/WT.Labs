<?php 
    class FileSystemActionResult{
        public function __construct(bool $IsSuccessfull, string $ErrorMsg, string $Redirect_url, string $Message = "")
        {
            $this->IsSuccessfull = $IsSuccessfull;
            $this->ErrorMsg = $ErrorMsg;
            $this->Redirect_url = $Redirect_url;
            $this->Message = $Message;
        }

        public $IsSuccessfull;

        public $ErrorMsg;

        public $Redirect_url;

        public $Message;
    }

	class WebFilesystem {
		public function GetDirectoryListing(string $serverPath, bool $OnlyDirs) {

            $listing = scandir(Config::SERVER_DIR."/".$serverPath);

            if($listing == FALSE){
                return FALSE;
            }

            if($OnlyDirs){
                $result = array();
                
                foreach($listing as $entry){
                    if(is_dir(Config::SERVER_DIR."/".$serverPath."/".$entry) || $entry == "." || $entry == "..") {
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
            Config::SERVER_FILESYSTEM_LOCATION.$clientRelativePath.$destination 
            :
            Config::SERVER_FILESYSTEM_LOCATION.$clientRelativePath."/".$destination; 

            $localPath = Config::SERVER_DIR.$clientRelativePath."/".$destination;

            if($IsManualDotsHandlingUsed == 'true'){
                if($destination == "."){
                    return new FileSystemActionResult(true, "",  Config::SERVER_FILESYSTEM_LOCATION.$clientRelativePath);
                }

                if($destination == ".."){

                    if($clientRelativePath == "/" || $clientRelativePath == ""){
                        return new FileSystemActionResult(true, "",  Config::SERVER_FILESYSTEM_LOCATION);
                    }

                    $temp = substr($normalizedDestination, 0 , strrpos($normalizedDestination, "/"));
                    return new FileSystemActionResult(true, "",  substr($temp, 0 , strrpos($temp, "/")));
                }
            }

            if(is_dir($localPath)){
                return new FileSystemActionResult(true, "",  $normalizedDestination);
            }

            if(is_file($localPath)){
               return new FileSystemActionResult(false, $destination." is a file.",  "");
            }

            return new FileSystemActionResult(false, "Directory doesn't exist.",  "");
        }

        public function DeleteFilesystemEntries($entries, $clientRelativePath) : FileSystemActionResult{
            $isAllFilesDeleted = true;

            foreach($entries as $entry){
                if($entry == ''){
                    $isAllFilesDeleted = false;
                    continue;
                }

                $localPath = Config::SERVER_DIR.$clientRelativePath."/".$entry;
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
                return new FileSystemActionResult(false, "Not all files were deleted.",  "");
            }
            else{
                return new FileSystemActionResult(true, "",  "");
            }

        }

        public function CreateFile($clientRelativePath) : FileSystemActionResult{
            $result = new FileSystemActionResult();
            
            if(!file_exists(Config::SERVER_DIR.$clientRelativePath."/".$_FILES['file']['name'])){

                if(!move_uploaded_file($_FILES['file']['tmp_name'], Config::SERVER_DIR.$clientRelativePath."/".$_FILES['file']['name'])){
                    return new FileSystemActionResult(false, "", "", "Error happened while moving tmp file to server's directory.: ".$_FILES['file']['name']);
                }

                return new FileSystemActionResult(true, "", "", "Successfully uploaded file: ".$_FILES['file']['name']);
            }

            return new FileSystemActionResult(false, "", "", "File already exists: ".$_FILES['file']['name']);

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

                if(is_file(Config::SERVER_DIR.$clientLocation."/".$entry)){
                    if(!rename(Config::SERVER_DIR.$clientLocation."/".$entry, Config::SERVER_DIR.$newLocation."/".$entry)){
                        $errorsHappened = true;
                    }
                    continue;
                }

               if(!WebFilesystem::CopyDirRecursive(Config::SERVER_DIR."/".$clientLocation."/".$entry, Config::SERVER_DIR.$newLocation)){
                    $errorsHappened = true;
               }

               WebFilesystem::RemoveDirectory(Config::SERVER_DIR."/".$clientLocation."/".$entry);
            }

            if($errorsHappened){
                return new FileSystemActionResult(true, "", "", "Errors happened while moving files.");
            }else{
                return new FileSystemActionResult(true, "", "", "Successfully moved files.");
            }
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