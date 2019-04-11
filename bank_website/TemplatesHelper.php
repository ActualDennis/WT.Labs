<?php
    require_once('WebFilesystem.php');
    require_once('config.php');
    require_once('DbHelper.php');

	class TemplatesHelper {

		public static function ResolveDateTimeTemplate($pagePath) : ?string {
            $pagecontents = file_get_contents($pagePath);
            $pagecontents = str_replace("{DATE}", date("D/M/d"), $pagecontents);
            return str_replace("{TIME}", date("H:i:s"), $pagecontents);
        }

        public static function ResolveDefaultTemplates($pagePath) : ?string {
            $pagecontents = file_get_contents($pagePath);
            $pagecontents = str_replace("{DATE}", date("D/M/d"), $pagecontents);
            $pagecontents = str_replace("{TIME}", date("H:i:s"), $pagecontents);
            preg_match('/{ *\t*\n*FILE *\t*\n*= *\t*\n*"([^"]*)" *\t*\n*}/', $pagecontents, $matches);

            if(!empty($matches)){
                $pagecontents = preg_replace('/{ *\t*\n*FILE *\t*\n*= *\t*\n*"([^"]*)" *\t*\n*}/', file_get_contents(Config::SERVER_CORE_DIR.$matches[1]), $pagecontents);
            }

            preg_match('/{ *\t*\n*CONFIG *\t*\n*= *\t*\n*"([^"]*)" *\t*\n*}/', $pagecontents, $matches);

            if(!empty($matches)){
                $pagecontents = preg_replace('/{ *\t*\n*CONFIG *\t*\n*= *\t*\n*"([^"]*)" *\t*\n*}/', Config::getUserDefinedConstant($matches[1]), $pagecontents);
            }

            preg_match('/{ *\t*\n*VAR *\t*\n*= *\t*\n*"([^"]*)" *\t*\n*}/', $pagecontents, $matches);

            if(!empty($matches)){
                $pagecontents = preg_replace('/{ *\t*\n*VAR *\t*\n*= *\t*\n*"([^"]*)" *\t*\n*}/', Config::getRuntimeVar($matches[1]), $pagecontents);
            }

            preg_match('/{ *\t*\n*DB *\t*\n*= *\t*\n*"([^"]*)" *\t*\n*}/', $pagecontents, $matches);

            if(!empty($matches)){
                $db = new DbHelper();
                $db->OpenConnection();
                $pagecontents = preg_replace('/{ *\t*\n*DB *\t*\n*= *\t*\n*"([^"]*)" *\t*\n*}/', $db->GetConfigVar($matches[1]) , $pagecontents);
                $db->CloseConnection();
            }

            preg_match('/{ *\t*\n*IF *\t*\n*"([^"]*)" *\t*\n*}([\s\S]*){ *\t*\n*ELSE *\t*\n*}([^;]*);/', $pagecontents, $matches);

            if(!empty($matches)){
                $result = eval($matches[1]);
                if($result){
                    $pagecontents = preg_replace('/{ *\t*\n*IF *\t*\n*"([^"]*)" *\t*\n*}([^{]*){ *\t*\n*ELSE *\t*\n*}([^;]*);/', $matches[2] , $pagecontents);
                }
                else{
                    $pagecontents = preg_replace('/{ *\t*\n*IF *\t*\n*"([^"]*)" *\t*\n*}([^{]*){ *\t*\n*ELSE *\t*\n*}([^;]*);/', $matches[3] , $pagecontents);
                }
            }

            preg_match('/{ *\t*\n*IF *\t*\n*"([^"]*)" *\t*\n*}([^;]*);/', $pagecontents, $matches);

            if(!empty($matches)){
                $result = eval($matches[1]);

                if($result){
                    $pagecontents = preg_replace('/{ *\t*\n*IF *\t*\n*"([^"]*)" *\t*\n*}([^;]*);/', $matches[2] , $pagecontents);
                }
                else{
                    $pagecontents = preg_replace('/{ *\t*\n*IF *\t*\n*"([^"]*)" *\t*\n*}([^;]*);/', "" , $pagecontents);
                }
            }

            return $pagecontents;
        }

        public static function ResolveSitemapTemplate($pagecontents) : string{
            $db = new DbHelper();
            $db->OpenConnection();
            $result = $db->GetSiteMapCategories();
            $db->CloseConnection();

            $categoryTemplate = file_get_contents("./sitemap_folder/sitemap_categorytemplate.html");
            $categoryEntryTemplate = file_get_contents("./sitemap_folder/sitemap_categoryEntryTemplate.html");
            $resolved = '';

            foreach($result as $category){
                $tempCategoryTemplate = str_replace("{CATEGORYNAME}", $category["CategoryName"], $categoryTemplate);
                $entries = '';

                if(is_array($category)){
                    foreach($category as $entry){
                        if(!is_array($entry))
                            continue;

                        $tempEntryTemplate =  str_replace("{HREF}", $entry["Href"], $categoryEntryTemplate);
                        $tempEntryTemplate =  str_replace("{ENTRYNAME}", $entry["LinkName"], $tempEntryTemplate);
                        $entries .= $tempEntryTemplate;
                    }
    
                    $tempCategoryTemplate = str_replace("{ENTRIES}",  $entries, $tempCategoryTemplate);
    
                    $resolved .= $tempCategoryTemplate;
                }
            }

            return str_replace("{SITEMAPENTRIES}",  $resolved, $pagecontents);
        }

        public static function ResolveFileSystemEntriesTemplate($filesystemEntries, $pageContents) : string{
            return str_replace("{ENTRIES}", $filesystemEntries, $pageContents);
        }

        public static function GetEntriesTemplates($entries, $path) : ?string {
            if(!is_array($entries)){
                return $entries;
            }

            $entryHtml = file_get_contents("./templates/filesystem_entry.html");
            $result = '';
            $tempEntry = '';
            $current_entry = 0;
            foreach($entries as $entry){
                $tempEntry = str_replace("{NAME}", $entry, $entryHtml);

                if($entry == "." || $entry == ".."){
                    $tempEntry = str_replace("{SIZE}", "", $tempEntry);
                    $tempEntry = str_replace("{DATEMODIFIED}", "", $tempEntry);
                    $tempEntry = preg_replace('#<i class="(.*?)">(.*?)</i>#', '', $tempEntry);
                    $tempEntry = str_replace("{ENTRYID}", "entry".$current_entry, $tempEntry);
                    $result .= $tempEntry;
                    ++$current_entry;
                    continue;
                }

                $tempEntry = str_replace("{SIZE}", filesize($path.$entry), $tempEntry);
                $tempEntry = str_replace("{DATEMODIFIED}", date ("d/m/Y H:s", filemtime($path.$entry)), $tempEntry);
                $tempEntry = str_replace("{ICONCLASS}", is_dir($path.$entry) ? "fas fa-folder" : "fas fa-file", $tempEntry);
                $tempEntry = str_replace("{ENTRYID}", "entry".$current_entry, $tempEntry);
                $result .= $tempEntry;
                ++$current_entry;
            }

            return $result;
        }
        
        public static function GetMoveEntryTemplates($entries, $absolutePath) : ?string {
            $entryHtml = file_get_contents("./templates/filesystem_move_entry.html");
            $result = '';
            $tempEntry = '';
            $current_entry = 0;
            if($absolutePath)

            foreach($entries as $entry){
                $tempEntry = str_replace("{NAME}", $entry, $entryHtml);

                if($entry == "." || $entry == ".."){
                    $tempEntry = str_replace("{SIZE}", "", $tempEntry);
                    $tempEntry = str_replace("{DATEMODIFIED}", "", $tempEntry);
                    $tempEntry = preg_replace('#<i class="(.*?)">(.*?)</i>#', '', $tempEntry);
                    $tempEntry = str_replace("{ENTRYID}", "moveentry".$current_entry, $tempEntry);
                    $result .= $tempEntry;
                    ++$current_entry;
                    continue;
                }

                $tempEntry = str_replace("{SIZE}", filesize($absolutePath.$entry), $tempEntry);
                $tempEntry = str_replace("{DATEMODIFIED}", date ("d/m/Y H:s", filemtime($absolutePath.$entry)), $tempEntry);
                $tempEntry = str_replace("{ICONCLASS}", is_dir($absolutePath.$entry) ? "fas fa-folder" : "fas fa-file", $tempEntry);
                $tempEntry = str_replace("{ENTRYID}", "moveentry".$current_entry, $tempEntry);
                $result .= $tempEntry;
                ++$current_entry;
            }

            return $result;
        }
        
	}
?>