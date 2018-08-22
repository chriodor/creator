<?php

global $checkPhpOpenCode;
$checkPhpOpenCode = "comWorld";

checkSecurePhp();


if ($params_from_post["sel_content_id"] > 0) {
    $_SESSION["sel_content_id"] = $params_from_post["sel_content_id"];
}


if ($params_from_post["save_content_params"] != "" && $params_from_post["content_loader_data"] > 0 && $_SESSION["sel_content_id"] > 0) {
    $contLoader = singleQuery("content_loader_params", "content_id = {$_SESSION["sel_content_id"]}");
    $contLoader["content_id"] = $_SESSION["sel_content_id"];
    $contLoader["data_id"] = $params_from_post["content_loader_data"];
    $ret = insertCompact("content_loader_params", $contLoader);
}

if ($params_from_post["new_content_type_id"] != "" && $params_from_post["sel_content_new_submit"] != "") {
    $loaderType = singleQuery("creator_loader_types", 'id=' . $params_from_post["new_content_type_id"]);
    $newArray = array();
    $newArray["page_id"] = $_SESSION["loader_content_edit"];
    $newArray["type_id"] = $params_from_post["new_content_type_id"];
    $ret = insertCompact("creator_content", $newArray);

    $ret["name"] = $loaderType["name"] . "_" . $ret["id"];
    $ret = insertCompact("creator_content", $ret);
    $_SESSION["sel_content_id"] = $ret["id"];
}

if ($params_from_post["headerLoaderGet"] != "") {
    $_SESSION["loader_content_edit"] = $params_from_post["headerLoaderGet"];
}


if ($params_from_post["generate_content_id"] != "") {
    generateConfigFile();
    generatePage($params_from_post["generate_content_id"]);
}

$allContents = multiQuery("creator_content", "page_id={$_SESSION["loader_content_edit"]}");
$contentHtml = $contentParams = "";



$allTypes = multiQuery("creator_loader_types", "1=1", "id", "name ASC");

$pageMutat = "";
if ($_SESSION["loader_content_edit"] != "") {
    $pagesKeres = singleQuery("creator_pages", "id={$_SESSION["loader_content_edit"]}");
    $pageMutat = $pagesKeres["name"];
}

$allContentsHtml = "<div class='simpleLoader'><table class='simpleTable'>";
if (!empty($allContents)) {
    foreach ($allContents as $key => $value) {
        $plusClass = "";
        if ($_SESSION["sel_content_id"] == $value["id"]) {
            $plusClass = "selectedRow";
        }

        $allContentsHtml .= "<tr class='$plusClass'>
                <td onclick='selectContent({$value["id"]})' colspan=2>{$value["name"]}</td>
        </tr>";
    }
}

$newTypes = "<select name='new_content_type_id' style='width:200px' >";
if (!empty($allTypes)) {
    foreach ($allTypes as $key => $value) {
        $newTypes .= "<option value='{$value["id"]}'>{$value['name']}</option>";
    }
}
$newTypes .= "</select>";

$allContentsHtml .= "
        <tr>
            <td style='width:80%; vertical-align:middle;'>
                <!--input type='text' placeholder='Name' name='sel_content_new_name'  /-->
                $newTypes
            </td>
            <td style='width:20%;' >
                <input type='submit' name='sel_content_new_submit' src='{$iconsHttp}add.png' value='Add new' />
            </td>
        </tr>
    </table>
</div>
</table></div>";

if ($_SESSION["sel_content_id"] > 0) {
    $allContents = multiQuery("proj_tables");

    $contLoader = singleQuery("content_loader_params", "content_id = {$_SESSION["sel_content_id"]}");

    //$contentParams = "<div class='simpleLoader'><table class='simpleTable'>";
    $contentParams = "<div class='simpleLoader'><select name='content_loader_data' style='width:200px' >";
    if (!empty($allContents)) {
        foreach ($allContents as $key => $value) {
            $selected = "";
            if ($contLoader["data_id"] == $value["id"]) {
                $selected = "selected";
            }
            $contentParams .= "<option $selected value='{$value["id"]}'>{$value['name']}</option>";
        }
    }
    $contentParams .= "</select>
        <input type='submit' name='save_content_params' value='Save' />
        </div>";
}

echo "
<script>
    function selectContent(sel_id){
        document.sel_loader.sel_content_id.value=sel_id;
        document.sel_loader.submit();
    }
    function generatePages(sel_id){
        document.sel_loader.generate_content_id.value=sel_id;
        document.sel_loader.submit();
    }
</script>
<form method='POST' name='sel_loader'>
    <input type='hidden' name='sel_content_id'>
    <input type='hidden' name='generate_content_id'>

    <table style='width:100%;'>
        <tr>
            <td class='contentLoader' style='width:80%;' >
                <input type='button' name='generate_whole_page' value='Generate' onclick=\"generatePages('{$_SESSION["loader_content_edit"]}')\" />&nbsp;&nbsp;&nbsp;
                $pageMutat
            </td>
            <td style='width:20%;'>
                <table style='width:100%;'>
                    <tr>
                        <td class='contentLoader'>
                            $allContentsHtml
                        </td>
                    </tr>
                    <tr>
                        <td class='contentLoader'>
                            $contentParams
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class='contentLoader'>
                $contentHtml
            </td>
        </tr>
    </table>
</form>";
?>