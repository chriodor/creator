<?php    
            
global $checkPhpOpenCode;
$checkPhpOpenCode = "comWorld";
checkSecurePhp();

if ($params_from_post["sel_content_id_1"] > 0) {
    $_SESSION["sel_content_id_1"] = $params_from_post["sel_content_id_1"];
}


if ($params_from_post["add_new_creator_todo_1_bttn"] != "" && $params_from_post["add_new_creator_todo_1"] != "") {

    $newArray = array();
    $newArray["name"] = $params_from_post["add_new_creator_todo_1"];
    $ret = insertCompact("creator_todo", $newArray);
        
    $_SESSION["sel_content_id_1"] = $ret["id"];

}

include "{$dirControl_todo}visual/table_1.php";
 
echo "<script type='type/javascript'>
    function selectContent_1(sel_id){
        document.sel_loader_1.sel_content_id_1.value=sel_id;
        document.sel_loader_1.submit();
    }
</script>
<form method='POST' name='sel_loader_1'>
    <table style='width:100%;'>
        <tr>
            <td class='contentLoader'>
                $allContentsHtml_1
            </td>
        </tr>
    </table>
</form>"; 

?>