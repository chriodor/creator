<?php

global $checkPhpOpenCode;
$checkPhpOpenCode = "comWorld";
checkSecurePhp();


if ($params_from_post["sel_project_id"] > 0) {
    $_SESSION["sel_project_id"] = $params_from_post["sel_project_id"];
}

if($params_from_post["sel_project_new_name"] != ""){
    $newarray = array();
    $newarray["name"] = $params_from_post["sel_project_new_name"];
    insertCompact("proj_projects", $newarray);
}

$tableData = multiQuery("proj_projects");

$loadedContent = "
<div class='simpleLoader'>
    <table class='simpleTable'>
        <tr>
           <th>Projects</th>
        </tr>
    ";
if (!empty($tableData)) {
    foreach ($tableData as $key => $value) {
        
        $plusClass = "";
        if($_SESSION["sel_project_id"] == $value["id"]){
            $plusClass = "selectedRow";
        }
        
        $loadedContent .= "
            <tr>
               <td class='$plusClass' onclick='selectProject({$value["id"]})'>{$value["name"]}</td>
            </tr>";
    }
}
$loadedContent .= "
        <tr>
            <td>
                <input type='text' name='sel_project_new_name' />
                <input type='submit' name='sel_project_new_submit' src='{$iconsHttp}add.png' value='Add new' />
            </td>
        </tr>
    </table>
</div>
";


echo "
<script type='text/javascript'>
    function selectProject(sel_id){
        document.sel_project.sel_project_id.value=sel_id;
        document.sel_project.submit();
    }
</script>
<form method='POST' name='sel_project'>
    <input type='hidden' name='sel_project_id'>

    <table style='width:100%;'>
    <tr>
        <td class='contentLoader' style='width:30%;' rowspan=2>
            $loadedContent
        </td>
        <td class='contentLoader' style='width:70%;'>
            $projectContent
        </td>
    </tr>
    </table>
</form>
";
?>