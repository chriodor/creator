<?php

global $checkPhpOpenCode;
$checkPhpOpenCode = "comWorld";
checkSecurePhp();

if ($params_from_post["sel_pages_id"] > 0) {
    $_SESSION["sel_pages_id"] = $params_from_post["sel_pages_id"];
}
if ($params_from_post["generate_pages_id"] > 0) {
    generateConfigFile();
    generatePage($params_from_post["generate_pages_id"]);
}

if ($params_from_post["sel_pages_new_name"] != "" && $params_from_post["sel_pages_content_name"] != "") {
    $newarray = array();
    $newarray["name"] = $params_from_post["sel_pages_new_name"];
    $newarray["content"] = $params_from_post["sel_pages_content_name"];
    $ret = insertCompact("creator_pages", $newarray);
}


$allTables = multiQuery("creator_pages", "1=1", "id", "name ASC");

$tableHtml = "";
if (!empty($allTables)) {
    $tableHtml .= "<div class='simpleLoader'><table class='simpleTable'>";
    foreach ($allTables as $key => $value) {
        $plusClass = "";
        if ($_SESSION["sel_pages_id"] == $value["id"]) {
            $plusClass = "selectedRow";
        }

        $jumpDocument = "document.loadHeader.headerLoaderInp.value='loader'; document.loadHeader.headerLoaderGet.value='{$value["id"]}'; document.loadHeader.submit(); ";
        
        $tableHtml .= "<tr class='$plusClass'>
                <td onclick='selectPages({$value["id"]})' style='width:25%'>{$value["name"]}</td>
                <td onclick=\"$jumpDocument\" style='width:5%'><img src='{$iconsHttp}arrow_right.png' title='Jump to loader' /></td>
                <td onclick='generatePages({$value["id"]})' style='width:70%'><img src='{$iconsHttp}cog.png' title='Generate page' /></td>
        </tr>";
    }
    $tableHtml .= "
        <tr>
            <td colspan=2>
                <input type='text' placeholder='Name' name='sel_pages_new_name' />
                <input type='text' placeholder='Content' name='sel_pages_content_name' />
                <input type='submit' name='sel_pages_new_submit' src='{$iconsHttp}add.png' value='Add new' />
            </td>
        </tr>
    </table>
</div>
</table></div>";
}


echo "
<script>
    function selectPages(sel_id){
        document.sel_pages.sel_pages_id.value=sel_id;
        document.sel_pages.submit();
    }
    function generatePages(sel_id){
        document.sel_pages.generate_pages_id.value=sel_id;
        document.sel_pages.submit();
    }
</script>
<form method='POST' name='sel_pages'>
    <input type='hidden' name='sel_pages_id'>
    <input type='hidden' name='generate_pages_id'>

    <table style='width:100%;'>
        <tr>
           <th>Tables</th>
        </tr>
        <tr>
            <td class='contentLoader' style='width:100%;'>
                $tableHtml
            </td>
        </tr>
    </table>
</form>";
?>