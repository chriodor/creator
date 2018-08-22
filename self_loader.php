<?php

global $checkPhpOpenCode;
$checkPhpOpenCode = "comWorld";
checkSecurePhp();


if ($params_from_post["build_db"] == 1) {
    include $alapMainDir . "functions_rebuild_creator.php";

    rebuildDb();
}


$allContentsHtml = "
<script>
    function buildDb(type){
        document.db_loader.build_db.value=type;
        document.db_loader.submit();
    }
</script>

<div class='simpleLoader'><table style='width:100%'>
    <form method='POST' name='db_loader'>
        <input type='hidden' name='build_db' />
        <table style='width:100%'>
            <tr>
                <td style='width:100%;' >
                    <input type='submit' name='base_db' value='Bulid DB' style='width:100%' onclick='buildDb(1)' />
                </td>
            </tr>
        </table>
    </form>
</div>
";

echo $allContentsHtml;
?>