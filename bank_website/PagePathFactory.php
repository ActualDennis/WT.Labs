<?php 

class PagePathFactory{
    public function GetPagePath(string $pageRelativeLink) : ?string{
        $pageUpper = strtoupper($pageRelativeLink);

        switch($pageUpper){
            case "ABOUT": return "./about_folder/about.html";
            case "LOGIN": return "./login_folder/login.html";
            case "SUPPORT": return "./support_folder/support.html";
            case "WELCOME":
            case "MAIN": return "./index_folder/index.html";
            default: return NULL;
        }
    }

    public function IsResolvablePage(string $pageRelativeLink) : bool {
        $pageUpper = strtoupper($pageRelativeLink);
        return $pageUpper == "ABOUT" || $pageUpper == "LOGIN" || $pageUpper == "SUPPORT" || $pageUpper == "WELCOME" || $pageUpper == "MAIN";
    }
}

?>