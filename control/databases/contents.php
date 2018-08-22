<?php

global $checkPhpOpenCode;
$checkPhpOpenCode = "comWorld";
checkSecurePhp();

if ($params_from_post["drop_table_id"] > 0) {

    $vanTable = singleQuery("proj_tables", "id = " . $params_from_post["drop_table_id"]);

    deleteCompact("proj_tables", $params_from_post["drop_table_id"]);
    $allCols = multiQuery("proj_columns", "table_id = {$params_from_post["drop_table_id"]}");
    if (!empty($allCols)) {
        foreach ($allCols as $key => $value) {
            deleteCompact("proj_columns", $value["id"]);
        }
    }
    unset($_SESSION["sel_column_id"]);
    unset($_SESSION["sel_tables_id"]);

    if ($vanTable["is_generated"]) {
        connectAndQuery("DROP TABLE {$vanTable["name"]};");
    }
}



if ($params_from_post["drop_column_id"] > 0) {
    deleteCompact("proj_columns", $params_from_post["drop_column_id"]);
    
    $tableGenerate = singleQuery("proj_tables", "id=" . $_SESSION["sel_tables_id"]);
    $tableGenerate["is_generated"] = false;
    insertCompact("proj_tables", $tableGenerate);
}

if ($params_from_post["sel_tables_id"] > 0) {
    unset($_SESSION["sel_column_id"]);
    $_SESSION["sel_tables_id"] = $params_from_post["sel_tables_id"];
}

if ($params_from_post["sel_tables_new_name"] != "") {

    unset($_SESSION["sel_column_id"]);
    $newarray = array();
    $newarray["name"] = $params_from_post["sel_tables_new_name"];
    $newarray["project_id"] = $_SESSION["sel_project_id"];
    $ret = insertCompact("proj_tables", $newarray);
    $_SESSION["sel_tables_id"] = $ret["id"];
    generateNewTableColumns($ret["id"]);
}

if ($params_from_post["sel_column_id"] > 0) {
    $_SESSION["sel_column_id"] = $params_from_post["sel_column_id"];
}

if ($params_from_post["sel_columns_new_name"] != "") {
    $newarray = array();
    $newarray["name"] = $params_from_post["sel_columns_new_name"];
    $newarray["table_id"] = $_SESSION["sel_tables_id"];
    $ret = insertCompact("proj_columns", $newarray);
    $_SESSION["sel_column_id"] = $ret["id"];

    $tableGenerate = singleQuery("proj_tables", "id=" . $_SESSION["sel_tables_id"]);
    $tableGenerate["is_generated"] = false;
    insertCompact("proj_tables", $tableGenerate);
}

if ($params_from_post["generate_table_id"] > 0) {
    updateTable($params_from_post["generate_table_id"]);
}

if ($params_from_post["change_col_type"] > 0) {
    $toChangeCol = singleQuery("proj_columns", "id=".$params_from_post["change_col_type"]);
    $toChangeCol["type_id"] = $params_from_post["change_col_type_to"];
    insertCompact("proj_columns", $toChangeCol);
    
    $tableGenerate = singleQuery("proj_tables", "id=" . $_SESSION["sel_tables_id"]);
    $tableGenerate["is_generated"] = false;
    insertCompact("proj_tables", $tableGenerate);
}


$tableData = multiQuery("proj_tables", "project_id = " . $_SESSION["sel_project_id"]);

$loadedContent = "
<div class='simpleLoader'>
    <table class='simpleTable'>
        <tr>
           <th>Tables</th>
        </tr>";

if (!empty($tableData)) {
    foreach ($tableData as $key => $value) {

        $plusClass = "";
        if ($_SESSION["sel_tables_id"] == $value["id"]) {
            $plusClass = "selectedRow";
        }

        $generateIcon = "";
        if ($value["is_generated"] == 0) {
            $generateIcon = "<img onclick='generateTable({$value["id"]})' src='{$iconsHttp}cog.png' />";
        }

        $deleteIcon = "<img src='{$iconsHttp}cross.png' />";

        $loadedContent .= "
            <tr>
               <td class='$plusClass' onclick='selectTables({$value["id"]})'>{$value["name"]}</td>
               <td class='$plusClass'>$generateIcon</td>
               <td class='$plusClass' onclick='if(confirm(\"Are you sure?\")){dropTable({$value["id"]});}'>$deleteIcon</td>
            </tr>";
    }
}

$loadedContent .= "
        <tr>
            <td colspan=3>
                <input type='text' name='sel_tables_new_name' />
                <input type='submit' name='sel_tables_new_submit' src='{$iconsHttp}add.png' value='Add new' />
            </td>
        </tr>
    </table>
</div>
";

if ($_SESSION["sel_tables_id"] > 0) {
    
    $allDefaults = multiQuery("proj_column_types");

    $tableData = multiQuery("proj_columns", "table_id = " . $_SESSION["sel_tables_id"]);

    $columnContent = "
<div class='simpleLoader'>
    <table class='simpleTable'>
        <tr>
           <th>Columns</th>
        </tr>";
    if (!empty($tableData)) {
        foreach ($tableData as $key => $value) {

            $plusClass = "";
            if ($_SESSION["sel_column_id"] == $value["id"]) {
                $plusClass = "selectedRow";
            }

            $deleteIcon = "<img src='{$iconsHttp}cross.png' />";
            
            
            $typeCombo = "<select name='comboVal' onchange='changeColType({$value["id"]}, value)'>";
            $typeCombo .= "<option value='0'></option>";
            if (!empty($allDefaults)) {
                foreach ($allDefaults as $kulcs => $ertek) {
                    
                    $isSelect="";
                    if($value["type_id"] == $ertek["id"]){
                        $isSelect = "selected";
                    }
                    $typeCombo .= "<option value='{$ertek["id"]}' $isSelect>{$ertek["name"]}</option>";
                }
            }
            $typeCombo .= "</select>";

            $columnContent .= "
            <tr>
               <td class='$plusClass' onclick='selectColumn({$value["id"]})'>{$value["name"]}</td>
               <td class='$plusClass'>$typeCombo</td>
               <td class='$plusClass' onclick='dropColumn({$value["id"]})'>$deleteIcon</td>
            </tr>";
        }
    }

    $columnContent .= "
            <td colspan=2>
                <input type='text' name='sel_columns_new_name' style='width:30%;' />
                <input type='submit' name='sel_columns_new_submit' src='{$iconsHttp}add.png' value='Add new' />
            </td>
    </table>
</div>
";

    $tableGenContent = "
<div class='simpleLoader'>
    <table class='simpleTable'>
        <tr>
           <td></td>
        </tr>
    </table>
</div>
";
    if ($_SESSION["sel_column_id"] > 0) {


        $columnGenContent = "
<div class='simpleLoader'>
    <table class='simpleTable'>
        <tr>
           <td></td>
        </tr>
    </table>
</div>
";
    }
}

echo "
<script type='text/javascript'>
    function selectTables(sel_id){
        document.sel_table.sel_tables_id.value=sel_id;
        document.sel_table.submit();
    }
    function generateTable(sel_id){
        document.sel_table.generate_table_id.value=sel_id;
        document.sel_table.submit();
    }
    function selectColumn(sel_id){
        document.sel_table.sel_column_id.value=sel_id;
        document.sel_table.submit();
    }
    function dropTable(sel_id){
        document.sel_table.drop_table_id.value=sel_id;
        document.sel_table.submit();
    }
    function dropColumn(sel_id){
        document.sel_table.drop_column_id.value=sel_id;
        document.sel_table.submit();
    }
    function changeColType(sel_id, change_to){
        document.sel_table.change_col_type_to.value=change_to;
        document.sel_table.change_col_type.value=sel_id;
        document.sel_table.submit();
    }
</script>

<form method='POST' name='sel_table'>
    <input type='hidden' name='sel_tables_id'>
    <input type='hidden' name='generate_table_id'>
    <input type='hidden' name='sel_column_id'>
    <input type='hidden' name='drop_table_id'>
    <input type='hidden' name='drop_column_id'>
    <input type='hidden' name='change_col_type'>
    <input type='hidden' name='change_col_type_to'>

    <table style='width:100%;'>
        <tr>
            <td class='contentLoader' style='width:30%;' rowspan=2>
                $loadedContent
            </td>
            <td class='contentLoader' style='width:35%;' rowspan=2>
                $columnContent
            </td>
            <td class='contentLoader' style='width:35%;'>
                $tableGenContent
            </td>
        </tr>
        <tr>
            <td class='contentLoader'>
                $columnGenContent
            </td>
        </tr>
    </table>
</form>
";
?>