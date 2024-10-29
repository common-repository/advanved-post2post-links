<?php
/*
Plugin Name: Advanced Post2Post Links
Plugin URI: http://www.aarongloege.com/blog/web-development/wordpress/plugins/advanced-post2post-links/
Description: Easily insert links to other posts on your blog. Advanced features include adding attributes and link text.
Version: 1.0.1
Author: Aaron Gloege
Author URI: http://www.aarongloege.com/

===============================================================================

Copyright 2009  Aaron Gloege  (contact@aarongloege.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

===============================================================================
*/

if (!isset($wpdb->posts)) $wpdb->posts = $tableposts;

function ap2p_magic($matches) {
	global $wpdb;

	preg_match_all('/([.\w]*)="(.*)"/U', $matches[1], $attributes);

	foreach ((array)$attributes[1] as $key => $value) {
		$data[$value] = $attributes[2][$key];
	}
	
	if (empty($data)) return '';
	
	$post = get_post($data['postID']);
	if (empty($post->post_title)) return '';
	
	foreach ((array)$data as $key => $item) {
		if ($key != 'text' && $key != 'title' && $key != 'postID') {
			$attrs .= ' '.$key.'="'.$item.'"';	
		}
	}
	$title = ($data['title']) ? $data['title'] : $post->post_title;
	$text = ($data['text']) ? $data['text'] : $post->post_title;

	return '<a href="'.get_permalink($post->ID).'" title="'.$title.'"'.$attrs.'>'.$text.'</a>';
}

function ap2p_link($text) {
	return preg_replace_callback("/\[p2p ([^]]*)\]/i", "ap2p_magic", $text);
}

function ap2p_links() {
	if (strpos($_SERVER['REQUEST_URI'], 'post.php') || strpos($_SERVER['REQUEST_URI'], 'post-new.php')) {
		$posts = get_posts('numberposts=-1');
		foreach ((array)$posts as $p) {
			$js .= '<option value="'.$p->ID.'" title="'.$p->post_title.'">'.$p->post_title.'</option>';
		}
		$js = str_replace("'",  "\'", $js);
?>
<script language="javascript" type="text/javascript">
	jQuery("#ed_toolbar,td.mceToolbar.first").append('<select style="width:90px;margin:3px 2px 2px;" class="ed_button" id="ap2plinks" size="1" onChange="p2p_link();"><option selected="selected" value="">Page Links</option><?php echo $js;?></select>');
	function p2p_link() {
		if (jQuery('#ap2plinks').val()) {
			index = jQuery('#ap2plinks').attr('selectedIndex');
			title = jQuery('#ap2plinks option:eq('+index+')').text();
			edInsertContent(edCanvas, '[p2p postID="'+ jQuery('#ap2plinks').val() +'" text="'+ title +'"]');
		}
		jQuery('#ap2plinks').attr("selectedIndex", 0);
	}
</script>
<?php
	}
}

add_filter('the_content', 'ap2p_link', 10);
add_filter('the_excerpt', 'ap2p_link', 10);
add_filter('admin_footer', 'ap2p_links');

?>