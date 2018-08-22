<?php 
        
session_start();

global $securePhpOpenCode;
$securePhpOpenCode = "comWorld";

global $allowVisualDebug;
$allowVisualDebug = true;

global $mainProject, $alapMainDir, $alapMainHttp;
$mainProject = "creator";
$alapMainDir = "C:/xampp/htdocs/$mainProject/";
$alapMainHttp = "http://localhost/$mainProject/";

global $configDirEleres;
$configDirEleres = $alapMainDir . "config/";

global $jsDir, $jsMainFunction;
$jsHttp = $alapMainHttp."js/";
$jsHttpMainFunction = $jsHttp."functions.js";

global $bodyEndScript;

include_once "{$configDirEleres}error_handling.php";

global $db_host, $db_login, $db_pass, $db_database;

$db_host = "localhost";
$db_login = "root";
$db_pass = "";
$db_database = "creator";

include_once $configDirEleres."constants.php";

include_once "{$alapMainDir}functions.php";

global $controlPages;
$controlPages_dir = $alapMainDir . "control/";
$controlPages_html = $alapMainHttp . "control/";

global $mainStyleCss;
$mainStyleCss = "{$alapMainHttp}mainStyle.css";

global $imagesHttp, $iconsHttp;
$imagesHttp = "{$alapMainHttp}images/";
$iconsHttp = $imagesHttp."icons/";

$params_from_get = $params_from_post = $post_screen_array = array();
if(!empty($_GET)){
    foreach($_GET as $key => $gets){
        $params_from_get[htmlspecialchars($key)] = htmlspecialchars($gets);
    }
}

if(!empty($_POST)){
    recursivePostArray($_POST, "post_screen_array", $post_screen_array);
    $params_from_post = $post_screen_array["post_screen_array"];  
}

global $dirControl_projects, $httpControl_projects;
$dirControl_projects = $controlPages_dir . "projects/";
$httpControl_projects = $controlPages_html . "projects/";

global $dirControl_databases, $httpControl_databases;
$dirControl_databases = $controlPages_dir . "databases/";
$httpControl_databases = $controlPages_html . "databases/";

global $dirControl_pages, $httpControl_pages;
$dirControl_pages = $controlPages_dir . "pages/";
$httpControl_pages = $controlPages_html . "pages/";

global $dirControl_loader, $httpControl_loader;
$dirControl_loader = $controlPages_dir . "loader/";
$httpControl_loader = $controlPages_html . "loader/";

global $dirControl_todo, $httpControl_todo;
$dirControl_todo = $controlPages_dir . "todo/";
$httpControl_todo = $controlPages_html . "todo/";

global $dirControl_admin, $httpControl_admin;
$dirControl_admin = $controlPages_dir . "admin/";
$httpControl_admin = $controlPages_html . "admin/"; 

?>