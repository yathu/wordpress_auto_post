<?php
require_once("../wp-load.php");


$insertCategory = wp_insert_term('football2252', 'category', array(
//	'description' => 'Football Blogs',
//	'slug' => 'category-slug',
//  'parent' => 4 // must be the ID, not name
));

print_r($insertCategory);

//$cat_id = get_cat_ID('football');
//
//print_r($cat_id);