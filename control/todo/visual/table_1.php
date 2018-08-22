<?php
 
$allContentsHtml_1 = "<div class='simpleLoader'><table class='simpleTable'>";
$allContents = multiQuery("creator_todo");
if (!empty($allContents)) {
    foreach ($allContents as $key => $value) {

        $plusClass = "";
        if ($_SESSION["sel_content_id_1"] == $value["id"]) {
            $plusClass = "selectedRow";
        }

        $allContentsHtml_1 .= "
        <tr class='$plusClass'>
            <td onclick='selectContent_1({$value["id"]})'>{$value["name"]}</td>
        </tr>
        ";
    }

}
 $allContentsHtml_1 .= "<tr>
    <td>
        <table style='width:100%'>
            <tr>
                <td style='width:80%; vertical-align:middle;'>
                    <input type='text' name='add_new_creator_todo_1' />
                </td> 
                <td style='width:20%;' >
                    <input type='submit' name='add_new_creator_todo_1_bttn' value='Add new' />
                </td>
            </tr>
        </table>
    </td>    
    ";

$allContentsHtml_1 .= "</table></div>";
 
?>