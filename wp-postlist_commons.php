<?php

function wp_postlist_cloud($limit = false) {
	if($limit) {
	    $settings = get_option('wp_postlist_array');
		$tags_limit = $settings['tags_limit'];
		$expand_text = $settings['expand_text'];
		
		$tags_count = count(wp_tag_cloud('number=0&format=array')); // WP 2.5+
		$moreLink = ($tags_limit && $tags_count > $tags_limit)?('<div class="pl_expand">'.
			'<a href="#_" onclick="this.innerHTML=\'Loading...\'; pl_showcloud();">'.
			$expand_text.'</a></div>'):'';
	} else {
		$tags_limit = 0;
		$moreLink = '';
	}
	
	$tags = wp_tag_cloud('number='.
		$tags_limit.'&format=array'); // WP 2.5+
	
	return implode(' ', $tags).$moreLink;
}

function wp_postlist_getposts($cat_id, $limit = false, $cat_count = 0) {
	if($limit) {
	    $settings = get_option('wp_postlist_array');
		$posts_limit = $settings['posts_limit'];
		$expand_text = $settings['expand_text'];
		$moreLink = ($cat_count > $posts_limit)?("\n\n".'<div class="pl_expand">'.
			'<a href="#_" onclick="this.innerHTML=\'Loading...\'; pl_showcat('.$cat_id.
			');">'.$expand_text.'</a></div>'):'';
	} else {
		$posts_limit = 0;
		$moreLink = '';
	}
	
	$posts = get_posts('numberposts='.$posts_limit.'&orderby=post_date&'.
		'order=DESC&category='.$cat_id);
	
	$postList = array();
	
	foreach($posts as $post) {
		$postList[] = '<span class="pl_date">'.
			date('Y/m/d', strtotime($post->post_date)).
			'</span> <a href="'.$post->guid.'">'.$post->post_title.'</a>';
	}
	
	return '<ul><li>'.implode('</li><li>', $postList).'</li></ul>'.$moreLink;
}

function wp_postlist_posts() {
    $settings = get_option('wp_postlist_array');
	$prefix = $settings['prefix'];
	$posts_limit = $settings['posts_limit'];
	
	$cats = get_categories();
	$uncategorized = array_shift($cats);
	array_push($cats, $uncategorized);
	
	$contents = array();
	$catList = array();
	
	foreach($cats as $cat) {
		$postList = wp_postlist_getposts($cat->cat_ID, true, $cat->count);
		
		$catId = 'cat_'.($cat->cat_ID);
		$cnt = '&nbsp;<span class="pl_cnt">('.$cat->count.')</span>';
		
		$contents[] = '<a href="#'.$catId.'">'.$cat->name.'</a>'.$cnt;
		$catList[] = '<h3 class="pl_cat_title"><a href="" id="'.$catId.'"></a>'.
			(strlen($prefix)?('<span class="pl_prefix">'.$prefix.'</span> '):'').
			$cat->name.$cnt.'&nbsp;<a href="#postlist_top" '.
			'style="text-decoration:none;">&uarr;</a></h3>'.
			'<div id="pl_'.$catId.'" class="pl_postlist">'.
			$postList.'</div>'."\n\n";
	}
	
	return '<div class="pl_contents">'.
		'<a href="" id="postlist_top"></a>'.implode(', ', $contents).
		'</div><div id="result"></div>'."\n\n".implode("\n", $catList);
}

?>