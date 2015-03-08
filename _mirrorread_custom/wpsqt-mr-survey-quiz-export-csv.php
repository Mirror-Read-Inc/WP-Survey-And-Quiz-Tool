<?php

add_filter("wpsqt-export-csv", "wpsqt_mr_survey_quiz_export_csv", 10, 2);

function wpsqt_mr_survey_quiz_export_csv($str, $id) {

    $strLines = explode( "\r\n", $str );
    //print_r($strLines);die();
    $lines = array_map( "strLineToLine", $strLines );

    $header = $lines[0];
    $rows = array_slice($lines, 1);

    // process rows
    // ...

    $lines2 = array_merge( [$header], $rows );
    $strLines2 = array_map( "lineToStrLine", $lines2 );
    return implode( "\r\n", $strLines2 );
}

function strLineToLine($strLine) { return explode( ",", $strLine ); }
function lineToStrLine($line) { return implode( ",", $line ); }