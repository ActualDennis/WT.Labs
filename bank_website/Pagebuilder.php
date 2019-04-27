<?php 
    require_once('PagePathFactory.php');
    require_once('config.php');

	class Pagebuilder {
        public static function BuildPage($pageName) : string {
            $pageFactory = new PagePathFactory();

            if($pageFactory->IsResolvablePage($pageName)){
                ActionsLogger::Log(WebsiteActions::Visited, $_GET['page']);
                return TemplatesHelper::ResolveDefaultTemplates(file_get_contents($pageFactory->GetPagePath($pageName)));
            }

            $pageNameNormalized = strtoupper($pageName);

            if($pageNameNormalized == "FILESYSTEM"){

                if(!isset($_GET['loc'])){
                    return "Specify directory with 'loc' parameter.";
                }

                $fileSystem = new WebFilesystem();
                $result = $fileSystem->GetDirectoryListing($_GET['loc'], false);
                $result = TemplatesHelper::GetEntriesTemplates($result, Config::SERVER_DIR.$_GET['loc'].'/');

                return Pagebuilder::BuildFilesystemPage($result);
            }

            if($pageNameNormalized == "SITEMAP"){
                return Pagebuilder::BuildSiteMapPage();
            }

            if($pageNameNormalized == "CONTROLPANEL"){
                return Pagebuilder::BuildControlPanelPage();
            }
        }


		private static function BuildFilesystemPage($fileSystemEntries) : ?string {
            if($fileSystemEntries == FALSE)
                return "Error happened while building a web-page. Probably you tried to access directory that doesn't exist.";
                
            $pageContents = file_get_contents(Config::FILESYSTEM_HTMLPATH);

            ActionsLogger::Log(WebsiteActions::VisitedFilesystem, $_GET['loc']);

            $pageContents = TemplatesHelper::ResolveDefaultTemplates(file_get_contents(Config::FILESYSTEM_HTMLPATH));
            
            return TemplatesHelper::ResolveFileSystemEntriesTemplate($fileSystemEntries, $pageContents);
        }

        private static function BuildControlPanelPage(){
            return TemplatesHelper::GetControlPanelPage();
        }

        private static function BuildSiteMapPage() : ?string{
            $pageContents = file_get_contents("./sitemap_folder/sitemap.html");

            ActionsLogger::Log(WebsiteActions::Visited, $_GET['page']);

            return TemplatesHelper::ResolveSitemapTemplate($pageContents);
        }
        
	}
?>