<?php
/*
Plugin Name: WP Post List
Plugin URI: http://github.com/dreikanter/wordpress-post-list
Description: This plugin generates posts list ordered by categories.
Version: 1.1
Author: Alex Musayev
Author URI: http://musayev.com
*/

$wppl_commons = str_replace('wp-postlist.php', 'wp-postlist_commons.php', __FILE__);
require_once($wppl_commons);

function wp_postlist_header()
{
    $settings = get_option('wp_postlist_array');
	$smooth_scrolling = ($settings['smooth_scrolling'] == 'on');
	if(!$smooth_scrolling) return;
	echo '<script type="text/javascript" src="'.get_settings('siteurl').
		'/wp-content/plugins/wp-postlist/moo1.2.js"></script>'."\n".
		'<script type="text/javascript">window.addEvent(\'domready\',function() '.
		'{ new SmoothScroll({ duration:700 }, window); });</script>'."\n";
	
	// use JavaScript SACK library for Ajax
	wp_print_scripts( array( 'sack' ));

	// Define custom JavaScript function

?><script type="text/javascript">
//<![CDATA[
function pl_showcloud()
{
	var mysack = new sack( 
	   "<?php bloginfo( 'wpurl' ); ?>/wp-content/plugins/wp-postlist/ajax.php" );    

	mysack.execute = 1;
	mysack.method = 'POST';
	mysack.setVar( 'action', 'show_cloud' );
	mysack.setVar( 'id', '' );
	mysack.onError = function() { alert('Ajax error.' )};
	mysack.runAJAX();

	return true;
}

function pl_showcat( cat_id )
{
	var mysack = new sack( 
	   "<?php bloginfo( 'wpurl' ); ?>/wp-content/plugins/wp-postlist/ajax.php" );    

	mysack.execute = 1;
	mysack.method = 'POST';
	mysack.setVar( 'action', 'show_cat' );
	mysack.setVar( 'id', cat_id );
	mysack.onError = function() { alert('Ajax error.' )};
	mysack.runAJAX();

	return true;
}
//]]>
</script><?php

}

function wp_postlist_filter($content) {
    $settings = get_option('wp_postlist_array');
	$tags_rep_str = $settings['tags_rep_str'];
	$posts_rep_str = $settings['posts_rep_str'];
	
	if(strlen($tags_rep_str) && 
		strpos($content, $tags_rep_str) !== false) {
		
		$tags = '<div class="pl_cloud">'.
			'<div id="pl_cloud">'.wp_postlist_cloud(true).'</div></div>';
		
		$content = str_replace($tags_rep_str, $tags, $content);
	}
	
	if(strlen($posts_rep_str) && 
		strpos($content, $posts_rep_str) !== false) {
		
		$posts = wp_postlist_posts();
		$content = str_replace($posts_rep_str, $posts, $content);
	}
	
	return $content;
}

function wp_postlist_remove_settings() {
	delete_option('wp_postlist_array');
}

function wp_postlist_options()
{
	global $wpdb;
	
    if(isset($_POST['submitted']))
	{
		if(strlen($_POST['prefix']) > 255) {
			$_POST['prefix'] = substr($_POST['prefix'], 0, 255);
		} elseif($_POST['prefix'] == '') {
			$_POST['prefix'] = '';
		}
		
		if(strlen($_POST['tags_rep_str']) > 255) {
			$_POST['tags_rep_str'] = substr($_POST['tags_rep_str'], 0, 255);
		} elseif($_POST['tags_rep_str'] == '') {
			$_POST['tags_rep_str'] = '[postlist]';
		}
		
		if(strlen($_POST['posts_rep_str']) > 255) {
			$_POST['posts_rep_str'] = substr($_POST['posts_rep_str'], 0, 255);
		} elseif($_POST['posts_rep_str'] == '') {
			$_POST['posts_rep_str'] = '[postlist]';
		}
		
		if(strlen($_POST['expand_text']) > 255) {
			$_POST['expand_text'] = substr($_POST['expand_text'], 0, 255);
		} elseif($_POST['expand_text'] == '') {
			$_POST['expand_text'] = 'Show all&nbsp;&rarr;';
		}
		
		if(is_numeric($_POST['tags_limit']) && ($_POST['tags_limit'] > 0)) {
			$_POST['tags_limit'] = (int)$_POST['tags_limit'];
		} else {
			$_POST['tags_limit'] = 0;
		}
		
		if(is_numeric($_POST['posts_limit']) && ($_POST['posts_limit'] > 0)) {
			$_POST['posts_limit'] = (int)$_POST['posts_limit'];
		} elseif(!is_numeric($_POST['posts_limit'])) {
			$_POST['posts_limit'] = 0;
		}
		
		if($_POST['smooth_scrolling'] == '') {
			$_POST['smooth_scrolling'] = 'no';
		}
		
		$settings = array (
			'prefix' => $_POST['prefix'],
			'tags_rep_str' => $_POST['tags_rep_str'],
			'posts_rep_str' => $_POST['posts_rep_str'],
			'expand_text' => $_POST['expand_text'],
			'tags_limit' => $_POST['tags_limit'],
			'posts_limit' => $_POST['posts_limit'],
			'smooth_scrolling' => $_POST['smooth_scrolling']
		);
		
		update_option('wp_postlist_array', $settings);
		
		echo '<div id="message" class="updated fade">'.
			'<p><strong>WP Post List plugin options updated.</strong></p></div>';
    }
	
	if(isset($_POST['wp_postlist_remove'])) {
		wp_postlist_remove_settings();
		echo '<div id="message" class="updated fade">'.
			'<p><strong>WP Post List settings data deleted.</strong></p></div>';
	}
	// $wpdb->escape
    $settings = get_option('wp_postlist_array');
	$prefix = stripcslashes($settings['prefix']);
	$tags_rep_str = stripcslashes($settings['tags_rep_str']);
	$posts_rep_str = stripcslashes($settings['posts_rep_str']);
	$expand_text = $settings['expand_text'];
	$tags_limit = $settings['tags_limit'];
	$posts_limit = $settings['posts_limit'];
	$smooth_scrolling = ($settings['smooth_scrolling'] == 'on');
	
    ?><div class="wrap"><form method="post" name="options" target="_self">

<h2>WP Post List options</h2>
<table class="form-table">
<tr valign="top"><th scope="row">Tag&nbsp;cloud&nbsp;replacement:</th>
<td><input name="tags_rep_str" type="text" style="width:28em;" value="<?php echo $tags_rep_str; ?>" /><br />
<span style="color:gray;">(this string in the page content will be replaced by the tag cloud; default value is <code>[tagcloud]</code>)</span></td></tr>

<tr valign="top"><th scope="row">Post&nbsp;list&nbsp;replacement:</th>
<td><input name="posts_rep_str" type="text" style="width:28em;" value="<?php echo $posts_rep_str; ?>" /><br />
<span style="color:gray;">(this string will be replaced by the posts list; default value is <code>[postlist]</code>)</span></td></tr>

<tr valign="top"><th scope="row">Category prefix:</th>
<td><input name="prefix" type="text" style="width:28em;" value="<?php echo str_replace('"', '\"', $prefix); ?>" /><br />
<span style="color:gray;">(optional; will be added to every category title)</span></td></tr>

<tr valign="top"><th scope="row">Expand link text:</th>
<td><input name="expand_text" type="text" style="width:28em;" value="<?php echo str_replace('"', '\"', $expand_text); ?>" /><br />
<span style="color:gray;">(optional; default value is "Show all&nbsp;&rarr;")</span></td></tr>

<tr valign="top"><th scope="row">Max tags number:</th>
<td><input name="tags_limit" type="text" style="width:10em;" value="<?php echo str_replace('"', '\"', $tags_limit); ?>" /><br />
<span style="color:gray;">(optional; 0 (default) means unlimited)</span></td></tr>

<tr valign="top"><th scope="row">Max posts number:</th>
<td><input name="posts_limit" type="text" style="width:10em;" value="<?php echo str_replace('"', '\"', $posts_limit); ?>" /><br />
<span style="color:gray;">(optional; 0 (default) means unlimited)</span></td></tr>

<tr valign="top"><th scope="row">Smooth scrolling</th>
<td><input type="checkbox" name="smooth_scrolling" <?php echo $smooth_scrolling?'checked="checked" ':''; ?>/>
</td></tr>

</table>

<p class="submit">
<input type="submit" name="wp_postlist_remove" class="button delete" value="Delete Settings" onclick="return confirm('Remove WP Post List settings from database? (cannot be undone)')" />
<input type="submit" name="submitted" value="Update Options &raquo;" style="margin-left:1em;" />
</p>

</form></div>
<?php 
}

function wp_postlist_add_to_menu()
{
    add_submenu_page('options-general.php', 'Sitemap Settings', 'Post List', 10, __FILE__, 'wp_postlist_options');
}

add_action('admin_menu', 'wp_postlist_add_to_menu');
add_action('wp_head', 'wp_postlist_header');
add_filter('the_content', 'wp_postlist_filter');

?>