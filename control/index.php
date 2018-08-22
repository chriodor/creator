<?php
global $checkPhpOpenCode;
$checkPhpOpenCode = "comWorld";
checkSecurePhp();


$checkExistingDb = checkExistingDb();


if ($checkExistingDb) {
    if ($_SESSION["sel_project_id"] > 0) {
        $selProj = singleQuery("proj_projects", "id = {$_SESSION["sel_project_id"]}");
    }

    $mainHeaderLoaders = array();
    if ($selProj["id"] > 0) {
        $mainHeaderLoaders[$selProj["name"]] = array(
            "plusStyle" => "float:left;",
            "class" => "singleHeader",
            "onclick" => "headerLoader('projects');",
        );
    }

    
    
    $headerPages = multiQuery("creator_pages");
    if (!empty($headerPages)) {
        foreach ($headerPages as $key => $value) {
            $mainHeaderLoaders[$value["name"]] = array(
                "onclick" => "headerLoader('{$value["content"]}');",
                "class" => "singleHeader"
            );
        }
    }

    $mainHeaderLoaders["X"] = array(
        "onclick" => "logout()",
        "title" => "Exit",
        "plusStyle" => "float:right;",
        "class" => "singleHeader"
    );



    $printHtml = "<form method='POST' name='loadHeader' action='$alapMainHttp' ><input type='hidden' name='headerLoaderInp' /><input type='hidden' name='headerLoaderGet' /></form>";

    if (!empty($mainHeaderLoaders)) {
        foreach ($mainHeaderLoaders as $key => $value) {
            $printHtml .= "<div class='{$value["class"]}' style='{$value["plusStyle"]}' title='{$value["title"]}' onclick=\"{$value["onclick"]}\">$key</div>";
        }
    }
    if (!isNullSet($params_from_post["headerLoaderInp"])) {
        $_SESSION["loadedFromHeader"] = $params_from_post["headerLoaderInp"];
    }

    $loadedFromHeader = "";
    if (!isNullSet($_SESSION["loadedFromHeader"])) {
        if (is_dir(${"dirControl_{$_SESSION["loadedFromHeader"]}"})) {
            //$loadedFromHeader = file_get_contents(${"dirControl_{$_SESSION["loadedFromHeader"]}"}."contents.php");
            $loadedFromHeader = ${"dirControl_{$_SESSION["loadedFromHeader"]}"} . "contents.php";
        } else {
            $loadedFromHeader = "";
        }
    }
} else {
    $loadedFromHeader = "{$alapMainDir}self_loader.php";
}
?>
<div class='headerLoader'>
    <?= $printHtml ?>
</div>
<div class='contentLoader'>
    <?php
    if ($loadedFromHeader) {
        include_once $loadedFromHeader;
    }
    ?>
</div>