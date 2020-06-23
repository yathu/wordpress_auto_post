<?php
require_once( "../wp-load.php" );

header( "content-type: text/html; charset=UTF-8" );
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
<?php
include( 'simple_html_dom.php' );

$html = new simple_html_dom();
$html->load_file( 'https://tamilayurvedic.com/category/%e0%ae%85%e0%ae%b4%e0%ae%95%e0%af%81/%e0%ae%aa%e0%af%86%e0%ae%a3%e0%af%8d%e0%ae%95%e0%ae%b3%e0%af%8d-%e0%ae%ae%e0%ae%b0%e0%af%81%e0%ae%a4%e0%af%8d%e0%ae%a4%e0%af%81%e0%ae%b5%e0%ae%ae%e0%af%8d' );

$arrayname = [];

foreach ( $html->find( 'div.penci-archive__list_posts article h2 a' ) as $key=>$link ) {
	if ( isset( $link ) ) {

		$html->load_file( $link->href );

		foreach ( $html->find( 'h1.entry-title' ) as $title ) {
			$name = $title->innerText();
		}

		foreach ( $html->find( 'div.post-image img' ) as $img ) {
			$img_url = $img->src;
		}


		$category_arr = [];

		foreach ( $html->find( 'span.penci-cat-links a' ) as $tag ) {

			$category = $tag->innerText();

			$category_arr[] = [ $category ];
		}

		$content = '';
		foreach ( $html->find( 'div.entry-content p' ) as $p ) {
			$content .= '<p>' . $p->innerText() . '</p>';
		}


		$arrayname[] = [
			'name'          => $name,
			'category'      => $category_arr,
			'featuredImage' => $img_url,
			'contents'      => $content
		];
	}

	if ($key == 1){
		break;
	}
}

//        echo json_encode($arrayname, JSON_UNESCAPED_UNICODE );

foreach ( $arrayname as $key => $value ) {
	$post_title = $value['name'];
	$post_contents = $value['contents'];
	$post_featured_image = $value['featuredImage'];
	$post_categories = $value['category'];

	$newpost_cat_arr = getPostCategory($post_categories);


	postWp( $post_title, $newpost_cat_arr, $post_featured_image, $post_contents );
}


//check is category is there else create and get id
function getPostCategory($post_categories){

    $newpost_cat_arr = [];

	foreach ($post_categories as $categorys){

	    foreach ($categorys as $category){

            $cat_id = get_cat_ID($category);

            if ($cat_id != 0 ){
                $newpost_cat_arr[] = $cat_id;
            } else{

                $insertCategory = wp_insert_term($category, 'category', array());

                $newpost_cat_arr[] = $insertCategory['term_id'];

            }

        }
	}

	return $newpost_cat_arr;
}



function postWp( $post_title, $newpost_cat_arr, $post_featured_image, $post_contents ) {

	$postType   = 'post'; // set to post or page
	$userID     = 1; // set to user id
	$categoryID = '2'; // set to category id.
	$postStatus = 'publish';  // set to future, draft, or publish

	$leadTitle   = $post_title;
//	$leadContent = '<h1>Vacations</h1><p>Vacations are the best thing in this life.</p>';
//	$leadContent .= ' <!--more--> <p>Expensive they are, but they are totally worth it.</p>';

	$leadContent = preg_replace("/<img[^>]+\>/i", '', $post_contents);

	$timeStamp          = $minuteCounter = 0;  // set all timers to 0;
	$iCounter           = 1; // number use to multiply by minute increment;
	$minuteIncrement    = 1; // increment which to increase each post time for future schedule
	$adjustClockMinutes = 0; // add 1 hour or 60 minutes - daylight savings
// CALCULATIONS
	$minuteCounter = $iCounter * $minuteIncrement; // setting how far out in time to post if future.
	$minuteCounter = $minuteCounter + $adjustClockMinutes; // adjusting for server timezone
	$timeStamp     = date( 'Y-m-d H:i:s', strtotime( "+$minuteCounter min" ) ); // format needed for WordPress

	$new_post = array(
		'post_title'    => $leadTitle,
		'post_content'  => $leadContent,
		'post_status'   => $postStatus,
		'post_date'     => $timeStamp,
		'post_author'   => $userID,
		'post_type'     => $postType,
		'post_category' => $newpost_cat_arr,
		'tags_input'    => $newpost_cat_arr
//		'tags_input'    => array( 'tag,tag1,tag2' )
	);


	$post_id = wp_insert_post( $new_post );

	$finaltext = '';

	if ( $post_id ) {

		$finaltext .= 'Yay, I made a new post.<br>';

		$image_url        = $post_featured_image; // Define the image URL here
		$pathinfo = pathinfo($image_url);
		$image_name       = $pathinfo['filename'] . 'in tamil' . '.' . $pathinfo['extension'];

		$upload_dir       = wp_upload_dir(); // Set upload folder
		$image_data       = file_get_contents( $image_url ); // Get image data
		$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
		$filename         = basename( $unique_file_name ); // Create image file name

// Check folder permission and define file location
		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

// Create the image  file on the server
		file_put_contents( $file, $image_data );

// Check image file type
		$wp_filetype = wp_check_filetype( $filename, null );

// Set attachment data
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

// Create the attachment
		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

// Include image.php
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

// Define attachment metadata
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

// Assign metadata to attachment
		wp_update_attachment_metadata( $attach_id, $attach_data );

// And finally assign featured image to post
		set_post_thumbnail( $post_id, $attach_id );

	} else {
		$finaltext .= 'Something went wrong and I didn\'t insert a new post.<br>';
	}
	echo $finaltext;
}

?>
</body>
</html>
