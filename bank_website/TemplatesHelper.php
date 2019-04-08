<?php
    require_once('WebFilesystem.php');


	class TemplatesHelper {

		public static function ResolveDateTimeTemplate($pagePath) : ?string {
            $pagecontents = file_get_contents($pagePath);
            $pagecontents = str_replace("{DATE}", date("D/M/d"), $pagecontents);
            return str_replace("{TIME}", date("H:i:s"), $pagecontents);
        }

        public static function GetEntriesTemplates($entries, $path) : ?string {
            if(!is_array($entries)){
                print_r($entries);
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