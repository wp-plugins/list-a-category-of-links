<?php
/*
Plugin Name: List all posts in a category
Plugin URI: http://herselfswebtools.com/2007/10/how-to-list-all-posts-from-a-specific-category-in-your-sidebar-in-wordpress.html
Description: Displays a link list of all posts in one specific category [ Original by Frucomerci.com ]
Author: Linda MacPhee-Cobb
Version: 2.0
Author URI: http://herselfswebtools.com
*/

// Post a page for user to pick a category to be listed
add_option('category_number', '5');


function pc_add_option_pages() {
	if (function_exists('add_options_page')) {
		add_options_page('List Posts in a Category', 'Posts by category', 8, __FILE__, 'pc_options_page');
	}		
}


function pc_options_page() {
	if (isset($_POST['info_update'])) {

		?>
		<div id="message" class="updated fade"><p><strong><?php 

		update_option('category_number', (string) $_POST["category_number"]);
		echo "Configuration Updated!";

	    ?></strong></p></div><?php

	} ?>

	<div class=wrap>

	<h2>List list of posts for one category</h2>

	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<input type="hidden" name="info_update" id="info_update" value="true" />

	<fieldset class="options"> 
	  <legend>Options</legend>
	  <table width="100%" border="0" cellspacing="0" cellpadding="6">

	  <tr valign="top"><td width="35%" align="right">
		  Category number
	  </td><td align="left">
		  <input name="category_number" type="text" size="50" value="<?php echo get_option('category_number') ?>"/>
	  </td></tr>
	  </table>
	</fieldset>

	<div class="submit">
		<input type="submit" name="info_update" value="<?php _e('Update options'); ?> &raquo;" />
	</div>
	</form>
	</div>
    <?php
}



function list_a_category() {

	global $wpdb, $post;
	
	$tp = $wpdb->prefix;
	$category_number = get_option('category_number');
	$sort_code = 'ORDER BY post_title DESC';
	$the_output = NULL;


	$last_posts = (array)$wpdb->get_results("
		SELECT post_date, ID, post_title, cat_name, cat_ID
		FROM {$tp}posts, {$tp}post2cat, {$tp}categories 
		WHERE {$tp}posts.ID = {$tp}post2cat.post_id 
		AND {$tp}categories.cat_ID = $category_number
		AND post_status = 'publish' 
		AND post_type != 'page' 
		AND post_date < NOW() 
		{$hide_check} 
		{$sort_code}
	");

	if (empty($last_posts)) {
		return NULL;
	}

	$the_output .= stripslashes($ddle_header); 

	$used_cats = array();;
	$i = 0;
	foreach ($last_posts as $posts) {
		if (in_array($posts->cat_name, $used_cats)) {
			unset($last_posts[$i]);
		} else {
			$used_cats[] = $posts->cat_name;
		}
		$i++;
	}
	$last_posts = array_values($last_posts);

	$the_output .= '<ul>';
	foreach ($last_posts as $posts) {

	  


    $where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish' AND post_category=5" , $r );
    $arcresults = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' AND ID IN (Select post_id FROM $wpdb->post2cat WHERE category_id =$posts->cat_ID) ORDER BY post_title ASC");
		
		foreach ( $arcresults as $arcresult ) {
			$the_output .= '<li><a href="' . get_permalink($arcresult->ID) . '">' . apply_filters('the_title', $arcresult->post_title) . '</a></li>';
		}
    
		$the_output .= '';
	}
	$the_output .= '</ul>';
	return $the_output;
}


function pc_generate($content) {
	$content = str_replace("<!-- listacategory -->", list_a_category(), $content);
	return $content;
}

add_filter('the_content', 'pc_generate');
add_action('admin_menu', 'pc_add_option_pages'); 

?>