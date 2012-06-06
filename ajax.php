<?php

$wp_config = preg_replace('|wp-content.*$|','', __FILE__) . 'wp-config.php';
$wppl_commons = str_replace('wp-postlist.php', 'wp-postlist_commons.php', __FILE__);

require_once($wp_config);
require_once($wppl_commons);

header('Content-type: text/javascript; charset='.get_settings('blog_charset'), true);
header('Cache-control: max-age=2600000, must-revalidate', true);

function error() {
	die( "alert('Bad dog!')" );
}

if(!isset($_POST['action'])) {
	error();
}

switch($_POST['action']) {
case 'show_cat':
	if(!isset($_POST['id'])) error();
	$id = $_POST['id'];
	if(!is_numeric($id) || $id < 0) error();
	$element_id = 'pl_cat_'.$id;
	$results = wp_postlist_getposts($id);
	break;
case 'show_cloud':
	$element_id = 'pl_cloud';
	$results = wp_postlist_cloud();
	break;
default:
	error();
	break;
}

// Compose JavaScript for return
$results = addcslashes($results, "\\'");

die( "document.getElementById('".$element_id."').innerHTML = '$results';" );

?>