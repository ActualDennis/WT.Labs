<?php 
	class pagebuilder {
		public static function build_filesystem_page($fileSystemEntries) {
            if($fileSystemEntries == FALSE)
                return "Error happened while building a web-page.";
                
            $pageContents = file_get_contents("./templates/filesystem_template.html");

            echo str_replace("{ENTRIES}",$fileSystemEntries, $pageContents);
		}
	}
?>