<?php 
/**
 * Allows to have instructions for a quiz or survey that appears at the start of a quiz or survey.
 * 
 * This plugin provides an interface to create,edit, and delete instructions from the admin pages. 
 * A link is added as a submenu page in the admin bar for the WPQST plugin. This link provides access
 * to manage the instrucstions.
 *
 * To add instructions to quizes and/or surveys this plugin uses the "wpsqt_quiz_form" action 
 * provided by the WPSQT plugin.
 *
 * FIXME - delete should be HTTP POST and not HTTP GET
 */

global $wpdb;

function wpsqt_mr_survey_quiz_instructions_install() {

	global $wpdb;

	$wpdb->query("CREATE TABLE IF NOT EXISTS `". $wpdb->get_blog_prefix() . "wp_wpqst_mr_survey_quiz_instructions` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(512) NOT NULL,
					  `type` varchar(266) NOT NULL,
					  `instructions` text NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
};

define('WPSQT_MR_SQ_INS_TABLE', $wpdb->get_blog_prefix() . 'wp_wpqst_mr_survey_quiz_instructions');
define('WPSQT_PAGE_SQ_INS', WPSQT_PAGE_MAIN.'-mr-survey-quiz-instructions');
define('WPSQT_URL_SQ_INS', admin_url('admin.php?page='.WPSQT_PAGE_SQ_INS));

add_action('wpsqt_main_install', 'wpsqt_mr_survey_quiz_instructions_install');

add_action('plugins_loaded', 'wpqst_mr_plugins_loaded');
add_action('admin_init', 'wpqst_mr_admin_init');

add_action("wpsqt_quiz_form", 'wpsqt_mr_survey_quiz_instructions');
add_action("wpsqt_survey_form", 'wpsqt_mr_survey_quiz_instructions');

//=== Display Instructions for a quiz or survey ===
function wpsqt_mr_survey_quiz_instructions($arg) {
	global $wpdb;
	
	$name = $_SESSION['wpsqt']['current_id'];
	$id = $_SESSION['wpsqt'][$name]['details']['id'];
	$type = $_SESSION['wpsqt']['current_type'];
	
	$row = $wpdb->get_row('SELECT * FROM '.WPSQT_MR_SQ_INS_TABLE.' WHERE name = \''.$name.'\' AND type = \''.$type.'\'', ARRAY_A);
	if ($row !== null)
		echo '<div>'.nl2br($row['instructions']).'</div>';
}
//=== ===

//=== Admin Submenu page code to add,edit, and delete instructions ===

function wpqst_mr_plugins_loaded() {
	add_action('admin_menu', 'wpsqt_mr_add_submenu');
}

function wpsqt_mr_add_submenu() {
	
	$subpage = add_submenu_page(WPSQT_PAGE_MAIN, 
								'Survey Quiz Instructions',
								'Survey Quiz Instructions',
								'manage_options',
								WPSQT_PAGE_SQ_INS,
								'wpsqt_mr_survey_quiz_instructions_submenu');
	
	add_action('admin_print_styles-' . $subpage, 'wpqst_mr_admin_style');
}

function wpqst_mr_admin_style() {
	wp_register_style('wpqst-mr-admin-style', plugins_url('wpqst-mr-style.css', __FILE__));
	wp_enqueue_style('wpqst-mr-admin-style');
}

function wpqst_mr_admin_init() {
	
	if (!isset($_GET['page']) || empty($_GET['page']) || $_GET['page'] !== WPSQT_PAGE_SQ_INS)
		return;
	
	global $wpdb;

	if (isset($_POST['wpsqt-mr-add']) && !empty($_POST['wpsqt-mr-add'])) {
		
		$name = $_POST['wpsqt-mr-name'];
		$type = $_POST['wpsqt-mr-type'];
		
		$ret = $wpdb->insert(WPSQT_MR_SQ_INS_TABLE, 
							 array('name' => $name,
								   'type' => $type,
								   'instructions' => $_POST['wpsqt-mr-instructions'])
							);

		if ($ret === false) 
			$msg = array('success' => false, 'txt' => 'Failure! Unable to add new instructions for the '.$type.' &quot;'.$name.'&quot;.');
		else
			$msg = array('success' => true, 'txt' => 'Success! New instructions where added for the '.$type.' &quot;'.$name.'&quot;.');
	}
	else if (isset($_POST['wpsqt-mr-edit']) && !empty($_POST['wpsqt-mr-edit'])) {
		
		$name = $_POST['wpsqt-mr-name'];
		$type = $_POST['wpsqt-mr-type'];
		
		$ret = $wpdb->update(WPSQT_MR_SQ_INS_TABLE, 
							 array('name' => $name,
								   'type' => $type,
								   'instructions' => $_POST['wpsqt-mr-instructions']),
							 array('id' => $_POST['wpsqt-mr-id']),
							 null,
							 array('%d')
							);	 
						
		if ($ret === false) 
			$msg = array('success' => false, 'txt' => 'Failure! Unable to add update instructions for the '.$type.' &quot;'.$name.'&quot;.');
		else
			$msg = array('success' => true, 'txt' => 'Success! New instructions where updated for the '.$type.' &quot;'.$name.'&quot;.');
	}
	else if (isset($_GET['delete']) && !empty($_GET['delete'])) { // FIXME - POST not a GET
		
		$id = $_GET['delete'];
		$ret = $wpdb->query($wpdb->prepare('DELETE FROM '.WPSQT_MR_SQ_INS_TABLE.' WHERE id = %d', $id));
		
		if ($ret == false) 
			$msg = array('success' => false, 'txt' => 'Failure! Unable to delete instructions.');
		else
			$msg = array('success' => true, 'txt' => 'Success! Instructions where deleted.');
	}
	
	if (isset($msg))
		wp_cache_add('wpsqt-mr-msg', $msg);
		
	if ((isset($_POST['wpsqt-mr-add']) && !empty($_POST['wpsqt-mr-add'])) || 
	    (isset($_POST['wpsqt-mr-edit']) && !empty($_POST['wpsqt-mr-edit'])))
		add_action("admin_head","wpqst_mr_load_tiny_mce");
}

function wpqst_mr_load_tiny_mce() {
	wp_tiny_mce(false);
}

function wpsqt_mr_survey_quiz_instructions_submenu() {

	$msg = wp_cache_get('wpsqt-mr-msg');
	wp_cache_delete('wpsqt-mr-msg');

	if ('add' === $_GET['add']) {
	
		wpsqt_mr_survey_quiz_instructions_add_edit(null);
		return;
	}
	else if (isset($_GET['edit']) && !empty($_GET['edit'])) {
		global $wpdb;
		
		$row = $wpdb->get_row('SELECT * FROM '.WPSQT_MR_SQ_INS_TABLE.' WHERE id = '.$_GET['edit'], ARRAY_A);
		
		if ($row !== null) {
			wpsqt_mr_survey_quiz_instructions_add_edit($row);
			return;
		} else
			$msg =  array('success' => false, 'txt' => 'Failure! Unable to fetch the instructions to edit.');
	}

	wpsqt_mr_survey_quiz_instructions_view($msg);
}

function wpsqt_mr_survey_quiz_instructions_view($msg) {
	
	require_once('Instructions_List_Table.php');
	$data = new Instructions_List_Table();
	$data->prepare_items();
	?>
	
	<div id="icon-tools" class="icon32"></div>
	<h2>
		WP Survey And Quiz Tool - Survey &amp; Quizzes Instructions
		<a href="<?php echo WPSQT_URL_SQ_INS; ?>&add=add" class="button add-new-h2">Add New Instructions</a>
	</h2>
	
	<?php if ($msg !== false): ?>
		<p id="wpsqt-mr-msg" class="<?php echo ($msg['success']) ? 'success' : 'failure'; ?>"><?php echo $msg['txt']; ?></p>
	<?php endif; ?>
	
	<?php $data->display(); ?>
	<?php
}

function wpsqt_mr_survey_quiz_instructions_add_edit($row) {
	
	$action = (null === $row) ? 'add' : 'edit';
	if ('edit' === $action) extract($row);
	?>
		<div id="icon-tools" class="icon32"></div>
		<h2>WP Survey And Quiz Tool - Add New Instructions</h2>
		<form id="wpqst-mr-add-edit" method="post" action="<?php echo WPSQT_URL_SQ_INS; ?>">
			<?php if ('edit' === $action): ?>
				<input type="hidden" name="wpsqt-mr-id" value="<?php echo $id; ?>"/>
			<?php endif; ?>
			<div>
				<label for="wpsqt-mr-name">Name:
					<input id="wpsqt-mr-name" type="text" name="wpsqt-mr-name" value="<?php echo $name; ?>"/>
				</label>
			</div>
			<div>
				<label for="wpsqt-mr-type">Type:
					<select id="wpsqt-mr-type" name="wpsqt-mr-type">
						<option></option>
						<option <?php if ('quiz' === $type) echo 'selected'; ?>>quiz</option>
						<option <?php if ('survey' === $type) echo 'selected'; ?>>survey</option>
					</select>
				</label
			</div>
			<div>
				<p>Instructions:</p>
				<?php the_editor($instructions, 'wpsqt-mr-instructions'); ?>
			</div>
			<div id="wpqst-mr-submit"><input type="submit" name="wpsqt-mr-<?php echo $action; ?>" 
				  value="<?php echo ('add' === $action) ? 'Add New Instructions' : 'Save Changes'; ?>"/></div>
		</form>
	<?php
}
//=== ===
?>