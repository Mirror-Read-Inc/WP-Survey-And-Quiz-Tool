<?php

define("MR_COUNT_PEOPLE", "COUNT_PEOPLE");
define("MR_AVG_TIME_TAKEN", "AVG_TIME_TAKEN");
define("MR_AVG_SCORE", "AVG_SCORE");
define("MR_PERSON_FILTER", '%"this is the first time i have taken this test - y/n";__ANSWER__%');

function wpsqt_mr_TokenCountPeople() {
	$d = cachedGetData();
	return isset($d[MR_COUNT_PEOPLE]) ? $d[MR_COUNT_PEOPLE] : 0;
}
function wpsqt_mr_TokenAvgTimeTaken() {
	$d = cachedGetData();
	return isset($d[MR_AVG_TIME_TAKEN]) ? $d[MR_AVG_TIME_TAKEN] : 0;
}
function wpsqt_mr_TokenAvgScore() { 
	$d = cachedGetData();
	return isset($d[MR_AVG_SCORE]) ? $d[MR_AVG_SCORE] : 0;
}

function wpsqt_mr_replacement_tokens($tokens) {
	$tokens->addToken("COUNT_PEOPLE", "The number of people who have taken the test the first time.", "wpsqt_mr_TokenCountPeople");
	$tokens->addToken("AVG_TIME_TAKEN", "The average time taken of people who have taken the test the first time.", "wpsqt_mr_TokenAvgTimeTaken");
	$tokens->addToken("AVG_SCORE", "The number average score of people who have taken the test the first time.", "wpsqt_mr_TokenAvgScore");
	return $tokens;
}
add_filter("wpsqt_replacement_tokens", "wpsqt_mr_replacement_tokens");

$mr_cached_get_data = null;
$mr_cached_get_data_r = false;
function cachedGetData() {
	global $mr_cached_get_data;
	global $mr_cached_get_data_r;
	
	if (!$mr_cached_get_data_r) {
		$mr_cached_get_data_r = true;
		$mr_cached_get_data = getData();
	}
	
	return $mr_cached_get_data;
}

function getData() {
	global $wpdb;
	
	$wp_session = class_exists('WP_Session') ? WP_Session::get_instance() : array();
	$_SESSION = &$wp_session;
	$itemId = $_SESSION['wpsqt']['item_id'];
	
	$ys = array();
	foreach (array("YES", "Yes", "Y", "yes", "y") as $y) {
		$ys[] = str_replace("__ANSWER__", "s:" . strlen($y) . ":" . "\"$y\"", MR_PERSON_FILTER);
	}

	$filter = '';
	foreach ($ys as $y) {
		$filter = $filter ? $filter . " OR person like \"" . esc_sql($y) . "\"" : "person like \"" . esc_sql($y) . "\"";
	}
	
	return $wpdb->get_row(
		"
		SELECT COUNT( id ) AS " . MR_COUNT_PEOPLE . ", FLOOR(AVG( timetaken )) AS " . MR_AVG_TIME_TAKEN . ", FLOOR(AVG( SCORE )) AS " . AVG_SCORE . "
		FROM  " . WPSQT_TABLE_RESULTS . "
		WHERE item_id = $itemId AND ($filter)
		",
		ARRAY_A
	);
};
