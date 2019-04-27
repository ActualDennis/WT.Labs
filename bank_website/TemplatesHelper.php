<?php
    require_once('WebFilesystem.php');
    require_once('config.php');
    require_once('DbHelper.php');

	class TemplatesHelper {

		public static function ResolveDateTimeTemplate($pageContents) : ?string {
            $pageContents = str_replace("{DATE}", date("D/M/d"), $pageContents);
            return str_replace("{TIME}", date("H:i:s"), $pageContents);
        }

        public static function ResolveDefaultTemplates($pagecontents) : ?string {
            $pagecontents = str_replace("{DATE}", date("D/M/d"), $pagecontents);
            $pagecontents = str_replace("{TIME}", date("H:i:s"), $pagecontents);

            $fileRegex = '/{ *\t*\n*FILE *\t*\n*= *\t*\n*"([^"]*)" *\t*\n*}/';

            do{
                preg_match($fileRegex, $pagecontents, $matches);

                if(!empty($matches)){
                    $pagecontents = preg_replace($fileRegex, file_get_contents(Config::SERVER_CORE_DIR.$matches[1]), $pagecontents, 1);
                }    
            } while(!empty($matches));

            $configRegex = '/{ *\t*\n*CONFIG *\t*\n*= *\t*\n*"([^"]*)" *\t*\n*}/';

            do{
                preg_match($configRegex, $pagecontents, $matches);
    
                if(!empty($matches)){
                    $pagecontents = preg_replace($configRegex, "'".Config::getUserDefinedConstant($matches[1])."'", $pagecontents, 1);
                }
            } while(!empty($matches));

            $varRegex = '/{ *\t*\n*VAR *\t*\n*= *\t*\n*"([^"]*)" *\t*\n*}/';

            do{
                preg_match($varRegex, $pagecontents, $matches);

                if(!empty($matches)){
                    $pagecontents = preg_replace($varRegex, "'".Config::getRuntimeVar($matches[1])."'", $pagecontents, 1);
                }
            } while(!empty($matches));

            $dbRegex = '/{ *\t*\n*DB *\t*\n*= *\t*\n*"([^"]*)" *\t*\n*}/';

            do{
                preg_match($dbRegex, $pagecontents, $matches);

                if(!empty($matches)){
                    $db = new DbHelper();
                    $db->OpenConnection();
                    $pagecontents = preg_replace($dbRegex, "'".$db->GetConfigVar($matches[1])."'" , $pagecontents, 1);
                    $db->CloseConnection();
                }
            }while(!empty($matches));

            $ifWithElseRegex = '/{ *\t*\n*IF *\t*\n*"([^"]*)" *\t*\n*}([^{]*){ *\t*\n*ELSE *\t*\n*}([^{]*){ *\t*\n*ENDIF *\t*\n*}/';

            do{
                preg_match($ifWithElseRegex, $pagecontents, $matches);

                if(!empty($matches)){
                    $result = eval($matches[1]);
    
                    if($result){
                        $pagecontents = preg_replace($ifWithElseRegex, $matches[2] , $pagecontents, 1);
                    }
                    else{
                        $pagecontents = preg_replace($ifWithElseRegex, $matches[3] , $pagecontents, 1);
                    }
                }
            } while(!empty($matches));

            $ifWithoutElseRegex = '/{ *\t*\n*IF *\t*\n*"([^"]*)" *\t*\n*}([^{]*){ *\t*\n*ENDIF *\t*\n*}/';

            do{
                preg_match($ifWithoutElseRegex, $pagecontents, $matches);

                if(!empty($matches)){
                    $result = eval($matches[1]);
    
                    if($result){
                        $pagecontents = preg_replace($ifWithoutElseRegex, $matches[2] , $pagecontents, 1);
                    }
                    else{
                        $pagecontents = preg_replace($ifWithoutElseRegex, "" , $pagecontents, 1);
                    }
                }
            } while(!empty($matches));

            $styleRegex = '/{STYLE=([^,^}]*)([,])?(MEDIA=([^}]*))?}/';

            do{
                preg_match($styleRegex, $pagecontents, $matches);

                if(!empty($matches)){     

                    if(isset($matches[2])){
                        $pagecontents = preg_replace($styleRegex, '<link rel="stylesheet" href="'.$matches[1].'" media="'.$matches[4].'">' , $pagecontents, 1);
                    }else{
                        $pagecontents = preg_replace($styleRegex, '<link rel="stylesheet" href="'.$matches[1].'">' , $pagecontents, 1);
                    }
                }

            } while(!empty($matches));

            return $pagecontents;
        }

        public static function GetLoginRegisterTemplate(){
            return file_get_contents("./templates/login_register_template.html");
        }

        public static function ResolveSitemapTemplate($pagecontents) : string{
            $db = new DbHelper();
            $db->OpenConnection();
            $result = $db->GetSiteMapCategories();
            $db->CloseConnection();

            preg_match('/{SITEMAPENTRIES=([^}]*)}/', $pagecontents, $matches);

            $categoryTemplate = file_get_contents($matches[1]);

            preg_match('/{ENTRIES=([^}]*)}/', $categoryTemplate, $categoryEntriesMatches);

            $categoryEntryTemplate = file_get_contents($categoryEntriesMatches[1]);
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
    
                    $tempCategoryTemplate = preg_replace('/{ENTRIES=([^}]*)}/',  $entries, $tempCategoryTemplate);
    
                    $resolved .= $tempCategoryTemplate;
                }
            }

            return preg_replace('/{SITEMAPENTRIES=([^}]*)}/',  $resolved, $pagecontents);
        }

        public static function ResolveFileSystemEntriesTemplate($filesystemEntries, $pageContents) : string{
            return preg_replace('/{ENTRIES=([^}]*)}/', $filesystemEntries, $pageContents);
        }

        public static function GetEntriesTemplates($entries, $path) : ?string {
            if(!is_array($entries)){
                return $entries;
            }

            $pageContents = file_get_contents(Config::FILESYSTEM_HTMLPATH);

            preg_match('/{ENTRIES=([^}]*)}/', $pageContents, $matches);

            $entryHtml = file_get_contents($matches[1]);
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
            $entryHtml = file_get_contents(Config::FILESYSTEM_MOVEENTRY_HTMLPATH);
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

        public static function GetControlPanelPage(){
		    if(!isset($_COOKIE[Config::COOKIE_LOGIN_NAME])){
		        return "This is an example of how unauthorized user will see this page.";
            }

            $db = new DbHelper();
            $db->OpenConnection();
            $usersAndRoles = $db->GetUsersAndRoles();
            $db->CloseConnection();

            if(!$db->IsAdmin($usersAndRoles, $_COOKIE[Config::COOKIE_LOGIN_NAME])){
                return "This is an example of how non-admin will see this page.";
            }

		    $resultPage = file_get_contents("./controlpanel_folder/controlpanel.html");
		    $entryTemplate = file_get_contents("./controlpanel_folder/controlpanel_entry.html");

            $tempEntries = '';

            foreach ($usersAndRoles  as $userRole){
                $tempEntry = str_replace("{LOGIN}", $userRole['Username'], $entryTemplate);
                $tempEntries .= str_replace("{ROLE}", $userRole['Role'],  $tempEntry);
            }

            return TemplatesHelper::ResolveDefaultTemplates(str_replace("{CONTENT}",$tempEntries, $resultPage));
        }

        
	}
?>