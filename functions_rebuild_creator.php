<?php

function rebuildDb() {

    global $db_host, $db_login, $db_pass, $db_database;

    if (!checkExistingDb()) {
        $con = mysqli_connect($db_host, $db_login, $db_pass, "information_schema");
        mysqli_set_charset($con, 'utf8');
        $query = "CREATE DATABASE $db_database CHARACTER SET utf8 COLLATE utf8_general_ci;";
        $queryConn = mysqli_query($con, $query);
        mysqli_close($con);
    }


    $con = mysqli_connect($db_host, $db_login, $db_pass, $db_database);
    mysqli_set_charset($con, 'utf8');


    $idStr = "`id` INT(11) NOT NULL DEFAULT '0'";
    $nameStr = "`name` VARCHAR(255) DEFAULT ''";
    $changeTypeStr = "`change_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP";
    $deletedStr = "`deleted` DATETIME NULL DEFAULT NULL";
    $primaryMisc = "PRIMARY KEY (`id`)";
    
    $newQuery = "CREATE TABLE proj_projects ($idStr, $nameStr, $changeTypeStr, $deletedStr, $primaryMisc);";
    $queryConn = mysqli_query($con, $newQuery);

    $miscStr = ", `project_id` INT(11) NOT NULL DEFAULT '0'";
    $newQuery = "CREATE TABLE proj_tables ($idStr, $nameStr, $changeTypeStr, $deletedStr $miscStr, $primaryMisc);";
    $queryConn = mysqli_query($con, $newQuery);

    $miscStr = ", `table_id` INT(11) NOT NULL DEFAULT '0'";
    $miscStr .= ", `type_id` INT(11) NOT NULL DEFAULT '0'";
    $miscStr .= ", `default_val` VARCHAR(255) DEFAULT ''";
    $miscStr .= ", `length` INT(11) NOT NULL DEFAULT '0'";
    $miscStr .= ", `index_type_id` INT(11) NOT NULL DEFAULT '0'";
    $newQuery = "CREATE TABLE proj_columns ($idStr, $nameStr, $changeTypeStr, $deletedStr $miscStr, $primaryMisc);";
    $queryConn = mysqli_query($con, $newQuery);

    //$miscStr = ", `table_id` INT(11) NOT NULL DEFAULT '0'";
    $newQuery = "CREATE TABLE proj_column_types ($idStr, $nameStr, $changeTypeStr, $deletedStr, $primaryMisc);";
    $queryConn = mysqli_query($con, $newQuery);

    $miscStr = ", `content` VARCHAR(255) DEFAULT ''";
    $newQuery = "CREATE TABLE creator_pages ($idStr, $nameStr, $changeTypeStr, $deletedStr $miscStr, $primaryMisc);";
    $queryConn = mysqli_query($con, $newQuery);

    $colTypes = array();
    $colTypes["id"] = TABLE_TYPE_INT;
    $colTypes["name"] = "INT";
    insertCompact("proj_column_types", $colTypes);

    $colTypes = array();
    $colTypes["id"] = TABLE_TYPE_VARCHAR;
    $colTypes["name"] = "VARCHAR";
    insertCompact("proj_column_types", $colTypes);

    $colTypes = array();
    $colTypes["id"] = TABLE_TYPE_DATETIME;
    $colTypes["name"] = "DATETIME";
    insertCompact("proj_column_types", $colTypes);

    $colTypes = array();
    $colTypes["id"] = TABLE_TYPE_BOOLEAN;
    $colTypes["name"] = "BOOLEAN";
    insertCompact("proj_column_types", $colTypes);

    $colTypes = array();
    $colTypes["id"] = TABLE_TYPE_TEXT;
    $colTypes["name"] = "TEXT";
    insertCompact("proj_column_types", $colTypes);
    
    //////////////////////////////
    //////////////////////////////
    //////////////////////////////
    $contentSingle = array();
    $contentSingle["name"] = "Projects";
    $contentSingle["content"] = "projects";
    $contentSingle = insertCompact("creator_pages", $contentSingle);
    
    $contentSingle = array();
    $contentSingle["name"] = "Databases";
    $contentSingle["content"] = "databases";
    $contentSingle = insertCompact("creator_pages", $contentSingle);
    
    
    //////////////////////////////
    //////////////////////////////
    //////////////////////////////
    $projCont = array();
    $projCont["name"] = "Creator";
    $projCont = insertCompact("proj_projects", $projCont);
    
    //////////////////////////////
    //////////////////////////////
    //////////////////////////////
    $contentSingle = array();
    $contentSingle["name"] = "proj_projects";
    $contentSingle["project_id"] = $projCont["id"];
    $contentSingle_projects = insertCompact("proj_tables", $contentSingle);
    
    $contentSingle = array();
    $contentSingle["name"] = "proj_tables";
    $contentSingle["project_id"] = $projCont["id"];
    $contentSingle_tables = insertCompact("proj_tables", $contentSingle);
    
    $contentSingle = array();
    $contentSingle["name"] = "proj_columns";
    $contentSingle["project_id"] = $projCont["id"];
    $contentSingle_columns = insertCompact("proj_tables", $contentSingle);
    
    $contentSingle = array();
    $contentSingle["name"] = "proj_column_types";
    $contentSingle["project_id"] = $projCont["id"];
    $contentSingle_col_types = insertCompact("proj_tables", $contentSingle);
    
    $contentSingle = array();
    $contentSingle["name"] = "creator_pages";
    $contentSingle["project_id"] = $projCont["id"];
    $contentSingle_creator = insertCompact("proj_tables", $contentSingle);
    
    //////////////////////////////
    //////////////////////////////
    //////////////////////////////
    generateNewTableColumns($contentSingle_projects["id"]);
    generateNewTableColumns($contentSingle_tables["id"]);
    generateNewTableColumns($contentSingle_columns["id"]);
    generateNewTableColumns($contentSingle_col_types["id"]);
    generateNewTableColumns($contentSingle_creator["id"]);
    
    //////////////////////////////
    //////////////////////////////
    //////////////////////////////
    $newarray = array();
    $newarray["name"] = "change_time";
    $newarray["table_id"] = $table_id;
    $newarray["type_id"] = TABLE_TYPE_DATETIME;
    $newarray["default_val"] = "CURRENT_TIMESTAMP";
    $newarray["length"] = $projTypes[$newarray["type_id"]]["length"];
    $ret = insertCompact("proj_columns", $newarray);
        
    mysqli_close($con);
}

?>