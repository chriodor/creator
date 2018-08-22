<?php


header('Cache-Control: no cache'); 
session_cache_limiter('private_no_expire');

include_once "config/config.php";

global $checkPhpOpenCode;
$checkPhpOpenCode = "comWorld";
checkSecurePhp();

if ($params_from_post["go_login"] != "") {
    if ($params_from_post["nameSend"] != "") {
        if ($params_from_post["passSend"] != "") {
            $_SESSION["login_as_user"] = 1;
            header("Location: $alapMainHttp");
        } else {
            alert("No password!");
        }
    } else {
        alert("No username!");
    }
}

$includeInBody = $alapMainDir . "login.php";
if ($_SESSION["login_as_user"] > 0) {
    if (isNullSet($_SESSION["currentDicussPage"])) {
        $_SESSION["currentDicussPage"] = "control/index.php";
    }

    $includeInBody = $_SESSION["currentDicussPage"];
}


?>
<!DOCTYPE html>
<html>
    <title>Creator</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="<?= $mainStyleCss ?>">
    <body>
        <script src='https://code.jquery.com/jquery-3.3.1.min.js'></script>
        <script src='<?= $jsHttpMainFunction ?>'></script>
        <?php
        if ($includeInBody != "") {
            include_once $includeInBody;
        }
        ?> 
        <script type='text/javascript'>
<?= $bodyEndScript ?>
        </script>
    </body>
</html>