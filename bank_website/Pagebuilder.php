<?php 
    require_once('PagePathFactory.php');

	class Pagebuilder {
        public static function BuildPage($pageName) : string {
            $pageFactory = new PagePathFactory();

            if($pageFactory->IsResolvablePage($pageName)){
                return TemplatesHelper::ResolveDateTimeTemplate($pageFactory->GetPagePath($pageName));
            }

            if(strtoupper($pageName) == "FILESYSTEM"){

                if(!isset($_GET['loc'])){
                    return "Specify directory with 'loc' parameter.";
                }

                $fileSystem = new WebFilesystem();
                $result = $fileSystem->GetDirectoryListing($_GET['loc'], false);
                $result = TemplatesHelper::GetEntriesTemplates($result, SERVER_DIR.$_GET['loc'].'/');
                return Pagebuilder::BuildFilesystemPage($result);
            }
        }


		private static function BuildFilesystemPage($fileSystemEntries) : ?string {
            if($fileSystemEntries == FALSE)
                return "Error happened while building a web-page. Probably you tried to access directory that doesn't exist.";
                
            $pageContents = file_get_contents("./templates/filesystem_template.html");
            
            return str_replace("{ENTRIES}", $fileSystemEntries, $pageContents);
        }
        
	}
?>