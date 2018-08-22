<?php

global $checkPhpOpenCode;
$checkPhpOpenCode = "comWorld";
checkSecurePhp();



$mainEvent = "
<form method='POST' name='logSend'>
    <div id='logHolder' class='easyHolder'>
        <table>
            <tr>
                <td>
                    User
                </td>
                <td>
                    <input type='text' name='nameSend' id='nameSend' />
                </td>
            </tr>
            <tr>
                <td>
                    Pass
                </td>
                <td>
                    <input type='password' name='passSend' id='passSend' />
                </td>
            </tr>
            <tr>
                <td colspan=2 style='text-align:center'>
                    <input type='submit' name='go_login' value='Login' />
                </td>
            </tr>
        </table>
    </div>
</form>";


echo $mainEvent;
?>