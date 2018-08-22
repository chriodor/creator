<?php

if (is_file("functions_creator.php")) {
    include_once 'functions_creator.php';
}

function print_all_deb($arr, $mode = "") {
    global $allowVisualDebug;
    if ($allowVisualDebug) {
        switch ($mode) {
            case "1":
                var_dump($arr);
                break;
            default:
                print_r($arr);
                break;
        }
    }
}

function checkExistingDb() {

    global $db_host, $db_login, $db_pass, $db_database;

    $con = mysqli_connect($db_host, $db_login, $db_pass, "information_schema");
    mysqli_set_charset($con, 'utf8');
    $queryConn = mysqli_query($con, "SHOW DATABASES");

    if ($queryConn !== false) {
        while ($row = $queryConn->fetch_assoc()) {
            $newArray[] = $row;
        }
    }

    mysqli_close($con);
    
    if (!empty($newArray)) {
        foreach ($newArray as $key => $value) {
            if ($value["Database"] == $db_database) {
                return true;
            }
        }
    }
    return false;
}

function connectAndQuery($query) {

    global $db_host, $db_login, $db_pass, $db_database;

    $con = mysqli_connect($db_host, $db_login, $db_pass, $db_database);
    mysqli_set_charset($con, 'utf8');

    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    $ret = mysqli_query($con, $query);
    if (!$ret) {
        print_all_deb(mysqli_error($con));
        print_all_deb(debug_backtrace());
    }

    mysqli_close($con);

    return $ret;
}

function customQuery($query, $getret = true) {

    $retArray = $newArray = array();

    $queryConn = connectAndQuery($query);
    if ($queryConn !== false) {
        while ($row = $queryConn->fetch_assoc()) {
            $newArray[] = $row;
        }
    }
    $row = mysqli_fetch_assoc($queryConn);

    if (!empty($row)) {
        foreach ($row as $key => $value) {
            $retArray[$key] = $value;
        }
    }
    return $newArray;
}

function singleQuery($table, $where = "1=1") {

    $where = "deleted IS NULL AND ($where)";

    $query = "SELECT * FROM $table WHERE $where LIMIT 0, 1";

    $retArray = array();

    $queryConn = connectAndQuery($query);
    if ($queryConn !== false) {
        $row = mysqli_fetch_assoc($queryConn);

        if (!empty($row)) {
            foreach ($row as $key => $value) {
                $retArray[$key] = $value;
            }
        }
    }
    return $retArray;
}

function multiQuery($table, $where = "1=1", $index = "id", $order = "") {

    $where = "deleted IS NULL AND ($where)";

    if ($order != "") {
        $order = " ORDER BY $order ";
    }

    $query = "SELECT * FROM $table WHERE $where $order";
    $retArray = $newArray = array();

    $queryConn = connectAndQuery($query);
    if ($queryConn !== false) {
        while ($row = $queryConn->fetch_assoc()) {
            $newArray[] = $row;
        }
    }

    if (empty($newArray)) {
        
    }

    if ($index != "") {
        if (!empty($newArray)) {
            foreach ($newArray as $key => $value) {
                foreach ($value as $kulcs => $ertek) {
                    $retArray[$value[$index]][$kulcs] = $ertek;
                }
            }
        }
    }
    return $retArray;
}

function insertCompact($table, $array) {

    $kulcsok = $ertekek = $query = "";

    if (!empty($array)) {
        $gotSingle = array();
        if ($array["id"] > 0) {
            $where = "id = " . $array["id"];
            $gotSingle = singleQuery($table, $where);
        }

        if ($gotSingle["id"] > 0) {

            $kulcsok = $ertekek = "";
            foreach ($array as $key => $value) {
                if ($key != "" && $key != "id") {
                    if ($value == "") {
                        $ertekek .= "$key = NULL, ";
                    } else {
                        $ertekek .= "$key = '$value', ";
                    }
                }
            }

            $ertekek = str_replace(", )", "", "$ertekek)");


            $query = "UPDATE $table SET $ertekek WHERE id = " . $gotSingle["id"];

            $nextIncrement = $array["id"];
        } else {


            foreach ($array as $key => $value) {
                if ($key != "" && $key != "id") {
                    $kulcsok .= "$key, ";
                    $ertekek .= "'$value', ";
                }
            }

            $nextIncrement = getNextIncrement($table);

            $kulcsok = "id, $kulcsok";
            $ertekek = "$nextIncrement, $ertekek";
            $kulcsok = str_replace(", )", ")", "($kulcsok)");
            $ertekek = str_replace(", )", ")", "($ertekek)");

            $query = "INSERT INTO $table $kulcsok VALUES $ertekek";
        }

        $queryConn = connectAndQuery($query);

        return singleQuery($table, "id = $nextIncrement");
    }
}

function deleteCompact($table, $id) {
    $sorExists = singleQuery($table, "id = $id");
    if ($sorExists["id"] > 0) {
        $sorExists["deleted"] = now();
        insertCompact($table, $sorExists);
    } else {
        alert("Nothing to delete: $table, $id");
    }
}

function getNextIncrement($table, $column = "id") {

    $retVal = 1;
    if ($table != "") {
        $query = "SELECT MAX($column) as max_id FROM $table ";

        $queryConn = connectAndQuery($query);
        $row = mysqli_fetch_assoc($queryConn);

        return $row["max_id"] + 1;
    }
    return $retVal;
}

function now() {
    return date("Y-m-d H:i:s");
}

function today() {
    return year() . "-" . month() . "-" . day();
}

function year() {
    return date("Y");
}

function month() {
    return date("m");
}

function day() {
    return date("d");
}

function brecho($str) {
    echo "<br>" . $str . "<br>";
}

function datumEllenorzes($date) {
    $expDate = explode("-", $date);
    $nullify = false;

    if (strlen($expDate[0]) != 4) {
        $nullify = true;
    } else {
        if (checkdate($expDate[1], $expDate[2], $expDate[0])) {
            $nullify = false;
        } else {
            $nullify = true;
        }
    }

    if ($nullify) {
        return "";
    } else {
        return $date;
    }
}

function inStrForSearch($multiarray, $fromInd, $toInd) {
    $inStr = "";
    if (!empty($multiarray) && $fromInd != "" && $toInd != "") {

        $inStr .= " $toInd IN (";
        foreach ($multiarray as $key => $value) {
            if (!empty($value)) {
                foreach ($value as $kulcs => $ertek) {
                    if ($kulcs == $fromInd) {
                        $inStr .= "'$ertek', ";
                    }
                }
            }
        }
        $inStr .= ")";

        $inStr = str_replace(", )", ")", $inStr);


        if (trim($inStr) == "$toInd IN ()") {
            $inStr = "1=0";
        }
    } else {
        $inStr = "1=0";
    }
    return $inStr;
}

function hoursBetweenDates($date1, $date2) {
    return strtotime($date1) - strtotime($date2);
}

function approx_hoursBetweenDates($date1, $date2) {

    $dateStamp = date("Ymd:H:i", hoursBetweenDates($date1, $date2));

    $szetSzed = explode(":", $dateStamp);


    if ($szetSzed[0] != "19700101") {
        return "Több, mint egy napja";
    } elseif ($szetSzed[1] > 12) {
        return "Több, mint fél napja";
    } elseif ($szetSzed[1] > 1) {
        return "Több, mint egy órája";
    } else {
        return "Kevesebb, mint egy órája";
    }
}

function addDay($date, $amount) {

    $tmstmp = strtotime($date) + ($amount * 24 * 60 * 60);

    return date("Y-m-d H:i:s", $tmstmp);
}

function print_pre($str) {
    print_r("<pre>$str</pre>");
}

function cleanUserString($str) {
    $str = strip_tags($str);
    $str = stripslashes($str);
    $str = @mysqli_real_escape_string($str);
    $str = htmlspecialchars($str);
    return $str;
}

function checkSecurePhp() {
    global $securePhpOpenCode, $checkPhpOpenCode;

    if (!($securePhpOpenCode == $checkPhpOpenCode)) {
        echo "Security warning has been sent!";
        die;
    }
}

function recursivePostArray($array, $keyVal, &$saveArray) {
    if ($keyVal == "post_screen_array") {
        $saveArray[$keyVal] = $array;
        return;
    } else {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $retArray = recursivePostArray($value, $key, $saveArray);
                    $saveArray[cleanUserString($keyVal)] = $retArray;
                }
            }

            return $array;
        } else {

            return cleanUserString($array);
        }
    }
}

function alert($str) {
    global $bodyEndScript;
    if ($str != "") {
        $bodyEndScript = "alert('$str');";
    }
}

function isNullSet($smth) {
    if (!isset($smth) || $smth == "" || $smth == "0") {
        return true;
    }
    return false;
}

?>