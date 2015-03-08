<?php

add_filter("wpsqt-csv-pre-generate", "wpsqt_mr_survey_quiz_generate_csv", 10, 2);

function wpsqt_mr_survey_quiz_generate_csv($_, $id) {

    global $wpdb;

    $results = $wpdb->get_results('SELECT * FROM '.WPSQT_TABLE_RESULTS.' WHERE item_id = "'.$id.'"', ARRAY_A);

    $len = count($results);

    $personTemplate = [];
    for ($i = $len - 1; $i >= 0; $i--) {

        $result = $results[$i];
        $person = unserialize_to_array( $result["person"] );

        if ($person) {
            $personTemplate = array_keys($person);
            break;
        }
    }

    $sectionsTemplateDisplay = [];
    $sectionsTemplate = [];

    for ($i = $len - 1; $i >= 0; $i--) {

        $result = $results[$i];
        $sections = unserialize_to_array( $result["sections"] );

        if ($sections) {

            foreach( $sections as $section ) {

                $sectionId = $section["id"];
                $sectionTemplate = [];

                foreach( $section["answers"] as $questionId => $answer ) {

                    $sectionsTemplateDisplay[] = getByItemId($section["questions"],$questionId)["name"];
                    $sectionTemplate[] = $questionId;
                }

                $sectionsTemplate[$sectionId] = $sectionTemplate;
            }

            break;
        }
    }

    $headers = array_merge(["id"], $personTemplate, ["Date taken", "Time taken", "Score", "Total", "Percentage", "Pass", "Status"], $sectionsTemplateDisplay);

    $rows = [];
    foreach( $results as $result ){

        $row = [];

        $row[] = $result["id"];

        $person = unserialize_to_array( $result["person"] );
        if (!$person) { $person = []; }
        foreach( $personTemplate as $personItem ) {
            $row[] = isset( $person[$personItem] ) ? $person[$personItem] : "";
        }

        $row[] = date('d-m-y G:i:s',$result['datetaken']);
        $row[] = $result['timetaken'];
        $row[] = $result['score'];
        $row[] = $result['total'];
        $row[] = $result['percentage'] . "%";
        $row[] = $result['pass'] ? "Pass" : "Fail";
        $row[] = $result['status'];

        $sections = unserialize_to_array( $result["sections"] );
        if (!$sections) { $sections = []; }
        foreach( $sectionsTemplate as $sectionId => $sectionTemplate ) {

            $section = getByItemId( $sections, $sectionId );
            $answers = $section === FALSE ? [] : $section["answers"];

            foreach( $sectionTemplate as $questionId ) {
                $row[] = isset( $answers[$questionId]["given"][0] ) ? choiceToLetter( $answers[$questionId]["given"][0] ) : "";
            }
        }

        $section = unserialize_to_array( $result["sections"] );

        $rows[] = $row;
    }

    return array_map( "lineToStrLine", array_merge( [$headers], $rows ) );
}

function unserialize_to_array( $str ) {

    $u = unserialize( $str );
    return is_array( $u ) ? $u : $u->toArray();
}

function lineToStrLine($line) { return implode( ",", $line ); }

function getByItemId($items, $id) {

    foreach($items as $item) {

        if ($item["id"] == $id) {
            return $item;
        }
    }
    return FALSE;
}

function choiceToLetter( $choice ) {
    return chr( ord("a") + $choice );
}