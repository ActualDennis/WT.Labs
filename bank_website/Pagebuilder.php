<?php 
	class Pagebuilder {
		public static function BuildFilesystemPage($fileSystemEntries) {
            if($fileSystemEntries == FALSE)
                return "Error happened while building a web-page. Probably you tried to access directory that doesn't exist.";
                
            $pageContents = file_get_contents("./templates/filesystem_template.html");

            return str_replace("{ENTRIES}",$fileSystemEntries, $pageContents);
		}
	}
?>