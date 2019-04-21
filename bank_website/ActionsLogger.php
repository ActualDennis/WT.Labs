<?php

require_once('DbHelper.php');

abstract class WebsiteActions{
    const TriedToVisitWebpage = 7;
    const Visited = 2;
    const VisitedFilesystem = 3;
    const TriedToVisitFilesystem = 5;
    const UploadedFile = 4;
    const DeletedFiles = 6;
    const Clicked = 9;

    public static function IsDefined(int $val) : bool {
        return 
           $val == WebsiteActions::TriedToVisitWebpage 
        || $val == WebsiteActions::Visited 
        || $val == WebsiteActions::VisitedFilesystem 
        || $val == WebsiteActions::TriedToVisitFilesystem 
        || $val == WebsiteActions::UploadedFile 
        || $val == WebsiteActions::DeletedFiles;
    }
}

class ActionsLogger{

    public static function Log(int $LogKind, string $parameter) : void{

        if(!WebsiteActions::IsDefined($LogKind))
            return;

        $db = new DbHelper();
        $db->OpenConnection();
        $db->LogAction($LogKind, $parameter);
        $db->CloseConnection();
    }
}