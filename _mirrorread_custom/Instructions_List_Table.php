<?php

define('WPSQT_MR_SQ_INS_TABLE', 'wp_wpqst_mr_survey_quiz_instructions');

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Instructions_List_Table extends WP_List_Table {
	
	
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'survey quiz instruction',     //singular name of the listed records
            'plural'    => 'survey quiz instructions',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }

    function column_default($item, $column_name){
		return print_r($item,true); //Show the whole array for troubleshooting purposes
    }
	
	function column_name($item) { 
	
		$actions = array(
            'edit'      => '<a href="'.WPSQT_URL_SQ_INS.'&edit='.$item['id'].'">Edit</a>',
            'delete'    => '<a href="'.WPSQT_URL_SQ_INS.'&delete='.$item['id'].'">Delete</a>' // FIXME - POST not a GET
        );
		
		return $item['name'].' '.$this->row_actions($actions);
	}
	function column_type($item) { return $item['type']; }
	function column_instructions($item) { return $item['instructions']; }

	function get_columns(){
        $columns = array(
            'name'     => 'Name',
            'type'    => 'Type',
            'instructions'  => 'Instructions'
        );
        return $columns;
    }

    function prepare_items() {
        global $wpdb;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $wpdb->get_results('SELECT * FROM '.WPSQT_MR_SQ_INS_TABLE, ARRAY_A);
    }
}
?>