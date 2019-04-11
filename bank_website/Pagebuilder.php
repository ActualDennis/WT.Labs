<?php 
    require_once('PagePathFactory.php');

	class Pagebuilder {
        public static function BuildPage($pageName) : string {
            $pageFactory = new PagePathFactory();

            if($pageFactory->IsResolvablePage($pageName)){
                return TemplatesHelper::ResolveDefaultTemplates($pageFactory->GetPagePath($pageName));
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
        }


		private static function BuildFilesystemPage($fileSystemEntries) : ?string {
            if($fileSystemEntries == FALSE)
                return "Error happened while building a web-page. Probably you tried to access directory that doesn't exist.";
                
            $pageContents = file_get_contents("./templates/filesystem_template.html");
            
            return TemplatesHelper::ResolveFileSystemEntriesTemplate($fileSystemEntries, $pageContents);
        }

        private static function BuildSiteMapPage() : ?string{
            $pageContents = file_get_contents("./sitemap_folder/sitemap.html");

            return TemplatesHelper::ResolveSitemapTemplate($pageContents);
        }
        
	}
?>