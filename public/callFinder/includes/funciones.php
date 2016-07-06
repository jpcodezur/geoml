<?php

include("cfg.php");

//Common
ini_set("mysql.trace_mode", "Off");

function DBConnect() {
    $link = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
    mysql_select_db(DB_DATABASE, $link);

    return $link;
}

function DBConnectAmazon() {
    $link = mysql_connect(DB_SERVER_AMAZON, DB_USER_AMAZON, DB_PASS_AMAZON);
    mysql_select_db(DB_DATABASE_AMAZON, $link);

    return $link;
}

function DBDisconnect($link) {
    return mysql_close($link);
}

function sqlMultipleCondition($attribs, $attrib_name, $and) {
    $attribSQL = "";
    $andSQL = "";
    if (is_array($attribs)) {
        for ($x = 0; $x < count($attribs); $x++) {
            $separador = ",";
            if ($x == 0) {
                $separador = "";
            }
            $attribSQL .= "$separador'$attribs[$x]'";
        }
        if ($x > 0) {
            if ($and) {
                $andSQL = " and";
            }
            $attribSQL = "$andSQL $attrib_name in($attribSQL)";
        }
    }

    return $attribSQL;
}

function sqlSimpleCondition($attrib, $attrib_name, $and) {
    if (!empty($attrib)) {
        $andSQL = "";
        if ($and) {
            $andSQL = " and";
        }

        return "$andSQL $attrib_name = '$attrib'";
    } else {
        return "";
    }
}

function dateDiff($dateFrom, $dateTo){
    $datesFrom = split("-",$dateFrom);
    $datesTo = split("-",$dateTo);
    @$timestampTotal = mktime(null, null, null, $datesTo[1], $datesTo[2], $datesTo[0]) - mktime(null, null, null, $datesFrom[1], $datesFrom[2], $datesFrom[0]);
    return ceil($timestampTotal / 86400);
}

?>
