<?php

function updateTable($table_id) {

    $vanIlyen = singleQuery("proj_tables", "id=$table_id");

    $projTypes = multiQuery("proj_column_types");
    $projTypesByName = multiQuery("proj_column_types", "1=1", "name");

    if ($vanIlyen["name"] != "") {
        $vanError = singleQuery("proj_columns", "table_id = " . $vanIlyen["id"] . " AND type_id = 0");


        if (!($vanError["id"] > 0)) {
            $allCols = multiQuery("proj_columns", "table_id = " . $vanIlyen["id"]);
            $allColsByName = multiQuery("proj_columns", "table_id = " . $vanIlyen["id"], "name");

            $query = "SELECT * FROM information_schema.columns WHERE TABLE_NAME = '{$vanIlyen["name"]}'";
            $custVan = customQuery($query);

            if (!empty($allCols)) {
                if (!empty($custVan)) {

                    //ALTER TABLE `proj_columns` ADD `type_id` INT NOT NULL DEFAULT '0' AFTER `table_id`, ADD `default_val` VARCHAR(255) NOT NULL DEFAULT '' AFTER `type_id`, ADD `length` INT NOT NULL DEFAULT '0' AFTER `default_val`, ADD `index_type_id` INT NOT NULL DEFAULT '0' AFTER `length`;
                    //ALTER TABLE `proj_columns` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
                    //ALTER TABLE `proj_column_defaults` CHANGE `index_type` `index_type_id` INT(255) NOT NULL DEFAULT '0';

                    $indexLetezo = array();
                    foreach ($custVan as $key => $value) {

                        //először, ha nem létezik az oszlop
                        $indexLetezo[$value["COLUMN_NAME"]] = $value;
                    }
                    if (!empty($allColsByName)) {
                        foreach ($allColsByName as $colNames) {

                            if ($colNames["index_type_id"] == INDEX_TYPE_PRIMARY) {
                                
                            }

                            if (isset($indexLetezo[$value["COLUMN_NAME"]])) {
                                unset($indexLetezo[$colNames["name"]]);
                                continue;
                            }

                            $projType = $projTypes[$colNames["type_id"]];

                            $addStr = "";
                            if ($projType["default_val"] == "NULL") {
                                $addStr .= " DEFAULT NULL ";
                            } else {
                                $addStr .= " DEFAULT '{$projType["default_val"]}' ";
                            }

                            switch ($colNames["type_id"]) {
                                case TABLE_TYPE_INT:
                                    $newChangeLine = "ALTER TABLE {$vanIlyen["name"]} ADD {$colNames["name"]} INT $addStr NOT NULL; \n";
                                    break;
                                case TABLE_TYPE_VARCHAR:
                                    $newChangeLine = "ALTER TABLE {$vanIlyen["name"]} ADD {$colNames["name"]} VARCHAR({$projType["length"]}) $addStr NOT NULL; \n";
                                    break;
                                case TABLE_TYPE_DATETIME:
                                    $newChangeLine = "ALTER TABLE {$vanIlyen["name"]} ADD {$colNames["name"]} DATETIME NULL $addStr; \n";
                                    break;
                                case TABLE_TYPE_BOOLEAN:
                                    $newChangeLine = "ALTER TABLE {$vanIlyen["name"]} ADD {$colNames["name"]} TINYINT(1) $addStr; \n";
                                    break;
                                case TABLE_TYPE_TEXT:
                                    $newChangeLine = "ALTER TABLE {$vanIlyen["name"]} ADD {$colNames["name"]} TEXT $addStr NOT NULL; \n";
                                    break;
                            }
                            
                            if ($newChangeLine != "") {
                                $queryFut = connectAndQuery($newChangeLine);
                            }
                        }
                    }

                    if (!empty($indexLetezo)) {
                        foreach ($indexLetezo as $key => $value) {
                            $newChangeLine = "ALTER TABLE {$vanIlyen["name"]} DROP $key; ";
                            $queryFut = connectAndQuery($newChangeLine);
                        }
                    }

                    $vanIlyen["is_generated"] = true;

                    insertCompact("proj_tables", $vanIlyen);
                } else {

                    //CREATE TABLE `creator`.`proj_column_defaults` ( `id` INT NOT NULL DEFAULT '0' , `name` VARCHAR(255) NOT NULL DEFAULT '' , `deleted` DATETIME NULL DEFAULT NULL , `change_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `is_null` BOOLEAN NOT NULL DEFAULT FALSE , `length` INT NOT NULL DEFAULT '0' , `default_val` VARCHAR(255) NULL DEFAULT '' , `index_type` VARCHAR(255) NULL DEFAULT '' , PRIMARY KEY (`id`)) ENGINE = InnoDB;


                    $newQuery = "CREATE TABLE `creator`.`{$vanIlyen["name"]}`";


                    $endingIndexes = "";

                    $newQuery .= " ( ";
                    foreach ($allCols as $key => $value) {

                        $tipusHat = $projTypes[$value["type_id"]];

                        $tipusStr = $tipusHat["name"];

                        $defaultSzoveg = "";
                        switch ($value["default_val"]) {
                            case "":
                                if ($value["type_id"] == TABLE_TYPE_INT) {
                                    $defaultSzoveg = " NOT NULL DEFAULT '0' ";
                                } elseif ($value["type_id"] == TABLE_TYPE_DATETIME) {
                                    $defaultSzoveg = " NULL DEFAULT NULL ";
                                } else {
                                    if ($value["type_id"] == TABLE_TYPE_VARCHAR) {
                                        $tipusStr = " VARCHAR({$value["length"]}) ";
                                    }
                                    $defaultSzoveg = " NOT NULL DEFAULT '' ";
                                }
                                break;
                            case "NULL":
                                $defaultSzoveg = " NULL DEFAULT NULL ";
                                break;
                            case "CURRENT_TIMESTAMP":
                                $defaultSzoveg = " NOT NULL DEFAULT CURRENT_TIMESTAMP ";
                                break;
                            default:
                                $defaultSzoveg = " NOT NULL DEFAULT '{$value["default_val"]}' ";
                                break;
                        }


                        $newQuery .= "`{$value["name"]}` $tipusStr $defaultSzoveg ,";

                        if ($value["index_type_id"] == INDEX_TYPE_PRIMARY) {
                            $endingIndexes .= ", PRIMARY KEY (`{$value["name"]}`)";
                        }
                    }



                    $newQuery .= $endingIndexes . ") ENGINE = InnoDB;";

                    $newQuery = str_replace(",,", ",", $newQuery);

                    $newQuery = str_replace(",)", ")", $newQuery);

                    $custVan = connectAndQuery($newQuery);

                    $vanIlyen["is_generated"] = true;

                    insertCompact("proj_tables", $vanIlyen);
                }
            }
        } else {
            alert("Describe all columns!");
        }
    }
}

function generateNewTableColumns($table_id) {
    $projTypes = multiQuery("proj_column_types");

    $vanIlyen = singleQuery("proj_columns", "name='id' AND table_id=$table_id");
    if (!$vanIlyen["id"] > 0) {
        $newarray = array();
        $newarray["name"] = "id";
        $newarray["table_id"] = $table_id;
        $newarray["type_id"] = TABLE_TYPE_INT;
        $newarray["default_val"] = $projTypes[$newarray["type_id"]]["default_val"];
        $newarray["length"] = $projTypes[$newarray["type_id"]]["length"];
        $newarray["index_type_id"] = INDEX_TYPE_PRIMARY;
        $ret = insertCompact("proj_columns", $newarray);
    }

    $vanIlyen = singleQuery("proj_columns", "name='name' AND table_id=$table_id");
    if (!$vanIlyen["id"] > 0) {
        $newarray = array();
        $newarray["name"] = "name";
        $newarray["table_id"] = $table_id;
        $newarray["type_id"] = TABLE_TYPE_VARCHAR;
        $newarray["default_val"] = $projTypes[$newarray["type_id"]]["default_val"];
        $newarray["length"] = $projTypes[$newarray["type_id"]]["length"];

        $ret = insertCompact("proj_columns", $newarray);
    }

    $vanIlyen = singleQuery("proj_columns", "name='deleted' AND table_id=$table_id");
    if (!$vanIlyen["id"] > 0) {
        $newarray = array();
        $newarray["name"] = "deleted";
        $newarray["table_id"] = $table_id;
        $newarray["type_id"] = TABLE_TYPE_DATETIME;
        $newarray["default_val"] = $projTypes[$newarray["type_id"]]["default_val"];
        $newarray["length"] = $projTypes[$newarray["type_id"]]["length"];
        $ret = insertCompact("proj_columns", $newarray);
    }

    $vanIlyen = singleQuery("proj_columns", "name='change_time' AND table_id=$table_id");
    if (!$vanIlyen["id"] > 0) {
        $newarray = array();
        $newarray["name"] = "change_time";
        $newarray["table_id"] = $table_id;
        $newarray["type_id"] = TABLE_TYPE_DATETIME;
        $newarray["default_val"] = "CURRENT_TIMESTAMP";
        $newarray["length"] = $projTypes[$newarray["type_id"]]["length"];
        $ret = insertCompact("proj_columns", $newarray);
    }
}

function generateConfigFile() {


    $configFile = getcwd() . "/config/config.php";

    $configTart = "
        
session_start();

global \$securePhpOpenCode;
\$securePhpOpenCode = \"comWorld\";

global \$allowVisualDebug;
\$allowVisualDebug = true;

global \$mainProject, \$alapMainDir, \$alapMainHttp;
\$mainProject = \"creator\";
\$alapMainDir = \"C:/xampp/htdocs/\$mainProject/\";
\$alapMainHttp = \"http://localhost/\$mainProject/\";

global \$configDirEleres;
\$configDirEleres = \$alapMainDir . \"config/\";

global \$jsDir, \$jsMainFunction;
\$jsHttp = \$alapMainHttp.\"js/\";
\$jsHttpMainFunction = \$jsHttp.\"functions.js\";

global \$bodyEndScript;

include_once \"{\$configDirEleres}error_handling.php\";

global \$db_host, \$db_login, \$db_pass, \$db_database;

\$db_host = \"localhost\";
\$db_login = \"root\";
\$db_pass = \"\";
\$db_database = \"creator\";

include_once \$configDirEleres.\"constants.php\";

include_once \"{\$alapMainDir}functions.php\";

global \$controlPages;
\$controlPages_dir = \$alapMainDir . \"control/\";
\$controlPages_html = \$alapMainHttp . \"control/\";

global \$mainStyleCss;
\$mainStyleCss = \"{\$alapMainHttp}mainStyle.css\";

global \$imagesHttp, \$iconsHttp;
\$imagesHttp = \"{\$alapMainHttp}images/\";
\$iconsHttp = \$imagesHttp.\"icons/\";

\$params_from_get = \$params_from_post = \$post_screen_array = array();
if(!empty(\$_GET)){
    foreach(\$_GET as \$key => \$gets){
        \$params_from_get[htmlspecialchars(\$key)] = htmlspecialchars(\$gets);
    }
}

if(!empty(\$_POST)){
    recursivePostArray(\$_POST, \"post_screen_array\", \$post_screen_array);
    \$params_from_post = \$post_screen_array[\"post_screen_array\"];  
}";

    $allPages = multiQuery("creator_pages");
    if (!empty($allPages)) {
        foreach ($allPages as $key => $value) {
            $content = $value["content"];

            $configTart .= "\n\nglobal \$dirControl_$content, \$httpControl_$content;
\$dirControl_$content = \$controlPages_dir . \"$content/\";
\$httpControl_$content = \$controlPages_html . \"$content/\";";
        }
    }

    file_put_contents($configFile, "<?php $configTart \n\n?>");
}

function generatePage($pageId) {
    global $checkPhpOpenCode;
    $pageQuery = singleQuery("creator_pages", "id=" . $pageId);

    global ${"dirControl_{$pageQuery["content"]}"};

    $contentDir = ${"dirControl_{$pageQuery["content"]}"};

    if (!is_dir($contentDir)) {
        mkdir($contentDir);
    }
    if (!is_dir($contentDir . "visual/")) {
        mkdir($contentDir . "visual/");
    }

    //if (!is_file($contentDir . "contents.php")) {
    //load Sorrend
    $bodyContent = "";

    $headerContent = "   
            
global \$checkPhpOpenCode;
\$checkPhpOpenCode = \"$checkPhpOpenCode\";
checkSecurePhp();
";

    $contentsAll = multiQuery("creator_content", "page_id=" . $pageId);


    if (!empty($contentsAll)) {
        foreach ($contentsAll as $key => $value) {

            switch ($value["type_id"]) {
                case CONTENT_TYPE_TABLE:
                    $retTart = generate_tableType($value);

                    if (!empty($retTart["inHeader"])) {
                        foreach ($retTart["inHeader"] as $kulcs => $sessions) {
                            $headerContent .= "\n$sessions\n";
                        }
                    }

                    if (!empty($retTart["inVisual"])) {
                        foreach ($retTart["inVisual"] as $kulcs => $visuals) {
                            file_put_contents("{$contentDir}visual/$kulcs.php", "<?php\n $visuals \n?>");

                            $headerContent .= "\ninclude \"{\$dirControl_{$pageQuery["content"]}}visual/$kulcs.php\";\n";
                        }
                    }

                    $bodyContent .= $retTart["returnHtml"];

                    break;

                default:
                    break;
            }
        }
    }

    file_put_contents($contentDir . "contents.php", "<?php $headerContent $bodyContent \n\n?>");
    // }
}

function generate_tableType($contentSin) {


    $returnArr = array();


    $allContents = singleQuery("content_loader_params", "content_id = {$contentSin["id"]}");
    $dataSingle = singleQuery("proj_tables","id={$allContents["data_id"]}");
    
    $tempHtml = "
\$allContentsHtml_{$contentSin["id"]} = \"<div class='simpleLoader'><table class='simpleTable'>\";
\$allContents = multiQuery(\"{$dataSingle["name"]}\");
if (!empty(\$allContents)) {
    foreach (\$allContents as \$key => \$value) {

        \$plusClass = \"\";
        if (\$_SESSION[\"sel_content_id_{$contentSin["id"]}\"] == \$value[\"id\"]) {
            \$plusClass = \"selectedRow\";
        }

        \$allContentsHtml_{$contentSin["id"]} .= \"
        <tr class='\$plusClass'>
            <td onclick='selectContent_{$contentSin["id"]}({\$value[\"id\"]})'>{\$value[\"name\"]}</td>
        </tr>
        \";
    }

}
 \$allContentsHtml_{$contentSin["id"]} .= \"<tr>
    <td>
        <table style='width:100%'>
            <tr>
                <td style='width:80%; vertical-align:middle;'>
                    <input type='text' name='add_new_{$dataSingle["name"]}_{$contentSin["id"]}' />
                </td> 
                <td style='width:20%;' >
                    <input type='submit' name='add_new_{$dataSingle["name"]}_{$contentSin["id"]}_bttn' value='Add new' />
                </td>
            </tr>
        </table>
    </td>    
    \";

\$allContentsHtml_{$contentSin["id"]} .= \"</table></div>\";
";

    $returnArr["inVisual"]["table_{$contentSin["id"]}"] = $tempHtml;
    $returnArr["inHeader"][] = "if (\$params_from_post[\"sel_content_id_{$contentSin["id"]}\"] > 0) {
    \$_SESSION[\"sel_content_id_{$contentSin["id"]}\"] = \$params_from_post[\"sel_content_id_{$contentSin["id"]}\"];
}";
    $returnArr["inHeader"][] = "
if (\$params_from_post[\"add_new_{$dataSingle["name"]}_{$contentSin["id"]}_bttn\"] != \"\" && \$params_from_post[\"add_new_{$dataSingle["name"]}_{$contentSin["id"]}\"] != \"\") {

    \$newArray = array();
    \$newArray[\"name\"] = \$params_from_post[\"add_new_{$dataSingle["name"]}_{$contentSin["id"]}\"];
    \$ret = insertCompact(\"{$dataSingle["name"]}\", \$newArray);
        
    \$_SESSION[\"sel_content_id_{$contentSin["id"]}\"] = \$ret[\"id\"];

}";

    $returnHtml = "
echo \"<script type='type/javascript'>
    function selectContent_{$contentSin["id"]}(sel_id){
        document.sel_loader_{$contentSin["id"]}.sel_content_id_{$contentSin["id"]}.value=sel_id;
        document.sel_loader_{$contentSin["id"]}.submit();
    }
</script>
<form method='POST' name='sel_loader_{$contentSin["id"]}'>
    <table style='width:100%;'>
        <tr>
            <td class='contentLoader'>
                \$allContentsHtml_{$contentSin["id"]}
            </td>
        </tr>
    </table>
</form>\";";

    $returnArr["returnHtml"] = $returnHtml;

    return $returnArr;
}

?>