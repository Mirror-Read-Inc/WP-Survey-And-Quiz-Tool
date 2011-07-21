<?php
	
	/**
	 * Displays the list of the quizzes and surveys
	 * if the list is empty it advises the user to
	 * create one or the other.
	 * 
	 * @author Iain Cambridge
	 * @copyright All rights reserved, Fubra Limited 2010-2011 (C)
  	 * @license http://www.gnu.org/licenses/gpl.html GPL v3 
  	 * @package WPSQT
	 */

class Wpsqt_Page_Main extends Wpsqt_Page {
	
	/**
	 * (non-PHPdoc)
	 * @see Wpsqt_Page::process()
	 */
	public function process(){
	
		$itemsPerPage = get_option("wpsqt_number_of_items");	
		$quizResults = Wpsqt_System::getAllItemDetails('quiz');
		$surveyResults = Wpsqt_System::getAllItemDetails('survey');
			
		$type = isset($_GET['type']) ? $_GET['type'] : '';
		$currentPage = isset($_GET['pageno'] )? $_GET['pageno'] : 1;
		$startNumber = ( ($currentPage - 1) * $itemsPerPage );	
		$quizNo   = sizeof($quizResults);
		$surveyNo = sizeof($surveyResults);
		$totalNo  = $quizNo + $surveyNo;
		
		switch ($type){		
			case 'quiz':
				$results = $quizResults;
				break;
			case 'survey':
				$results = $surveyResults;
				break;	
			default:
				$results = array_merge($quizResults,$surveyResults);
				break;	
		}
		
		$results = array_slice($results , $startNumber , $itemsPerPage );
		foreach( $results as &$result ){
			//$result = 
		}
		$numberOfPages = 1;//wpsqt_functions_pagenation_pagecount($totalNo, $itemsPerPage);
		
		$this->_pageVars = array( 'results' =>$results,
								  'numberOfPages' => $numberOfPages,
								  'startNumber' => $startNumber,
								  'currentPage' => $currentPage,
								  'quizNo' => $quizNo,
								  'surveyNo' => $surveyNo,
								  'totalNo' => $totalNo,
								  'type' => $type );
		
		if ( empty($results) && $type == 'all' ){		
			$this->_pageView = 'admin/main/empty.php';
		} else {	
			$this->_pageView = 'admin/main/list.php';
		}
		
	}
	
}