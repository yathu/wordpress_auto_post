<?PHP
// require wp-load.php to use built-in WordPress functions
require_once("../wp-load.php");
/*******************************************************
 ** POST VARIABLES
 *******************************************************/
$postType = 'post'; // set to post or page
$userID = 1; // set to user id
$categoryID = '2'; // set to category id.
$postStatus = 'publish';  // set to future, draft, or publish
$leadTitle = 'Exciting new post today: test';
$leadContent = '<h1>Vacations</h1><p>Vacations are the best thing in this life.</p>';
$leadContent .= ' <!--more--> <p>Expensive they are, but they are totally worth it.</p>';
/*******************************************************
 ** TIME VARIABLES / CALCULATIONS
 *******************************************************/
// VARIABLES
$timeStamp = $minuteCounter = 0;  // set all timers to 0;
$iCounter = 1; // number use to multiply by minute increment;
$minuteIncrement = 1; // increment which to increase each post time for future schedule
$adjustClockMinutes = 0; // add 1 hour or 60 minutes - daylight savings
// CALCULATIONS
$minuteCounter = $iCounter * $minuteIncrement; // setting how far out in time to post if future.
$minuteCounter = $minuteCounter + $adjustClockMinutes; // adjusting for server timezone
$timeStamp = date('Y-m-d H:i:s', strtotime("+$minuteCounter min")); // format needed for WordPress
/*******************************************************
 ** WordPress Array and Variables for posting
 *******************************************************/
$new_post = array(
    'post_title' => $leadTitle,
    'post_content' => $leadContent,
    'post_status' => $postStatus,
    'post_date' => $timeStamp,
    'post_author' => $userID,
    'post_type' => $postType,
    'post_category' => array($categoryID),
    'tags_input' => array('tag,tag1,tag2')
);
/*******************************************************
 ** WordPress Post Function
 *******************************************************/
$post_id = wp_insert_post($new_post);
/*******************************************************
 ** SIMPLE ERROR CHECKING
 *******************************************************/
$finaltext = '';

if($post_id){

    $finaltext .= 'Yay, I made a new post.<br>';

    $image_url        = 'https://cdn.pixabay.com/photo/2020/01/31/07/26/japan-4807317_960_720.jpg'; // Define the image URL here
    $image_name       = 'test_post_img.png';
    $upload_dir       = wp_upload_dir(); // Set upload folder
    $image_data       = file_get_contents($image_url); // Get image data
    $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
    $filename         = basename( $unique_file_name ); // Create image file name

// Check folder permission and define file location
    if( wp_mkdir_p( $upload_dir['path'] ) ) {
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
    require_once(ABSPATH . 'wp-admin/includes/image.php');

// Define attachment metadata
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

// Assign metadata to attachment
    wp_update_attachment_metadata( $attach_id, $attach_data );

// And finally assign featured image to post
    set_post_thumbnail( $post_id, $attach_id );

} else{
    $finaltext .= 'Something went wrong and I didn\'t insert a new post.<br>';
}
echo $finaltext;
?>