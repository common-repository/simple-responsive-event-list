<?php
/*
Plugin Name: Simple Responsive Event List
Plugin URI: http://www.netattingo.com/
Description: Plugin provides upcoming , past and all events list using shortcodes. 
Author: NetAttingo Technologies
Version: 1.0.0
Author URI: http://www.netattingo.com/
*/

//initialize constant
define('AWTS_DIR', plugin_dir_path(__FILE__));
define('AWTS_URL', plugin_dir_url(__FILE__));
define('AWTS_INCLUDE_DIR', plugin_dir_path(__FILE__).'pages/');
define('AWTS_INCLUDE_URL', plugin_dir_url(__FILE__).'pages/');

//add admin css
function srel_admin_css() {
  wp_register_style('srel_admin_css', plugins_url('/includes/admin-style.css',__FILE__ ));
  wp_enqueue_style('srel_admin_css');
}
add_action( 'admin_init','srel_admin_css');

// enqueue plugin scripts
function srel_frontend_scripts() {	
	if(!is_admin())	{
		wp_enqueue_style('srel_style', plugins_url('includes/front-style.css',__FILE__));
	}
}
add_action('wp_enqueue_scripts', 'srel_frontend_scripts');


function srel_enqueue_date_picker(){ 
	global $post_type; 
	if( 'event' != $post_type ) 
	return;
	wp_enqueue_script( 'srel_datepicker_script', plugins_url( 'includes/srel_datepicker.js' , __FILE__ ), array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), '1.0', true ); 
	wp_enqueue_style('srel_datepicker_style', plugins_url( 'includes/srel_datepicker.css',__FILE__));
}
add_action( 'admin_enqueue_scripts', 'srel_enqueue_date_picker' ); 


// create the eventspage in dashboard
function srel_custom_postype() { 
	$srel_labels = array( 
		'name' => __( 'Events', '' ), 
		'singular_name' => __( 'Event', '' ), 
		'add_new' => __( 'Add New', '' ), 
		'add_new_item' => __( 'Add New Event', '' ), 
		'edit_item' => __( 'Edit Event', '' ), 
		'new_item' => __( 'New Event', '' ), 
		'view_item' => __( 'View Event', '' ), 
		'search_items' => __( 'Search Events', '' ), 
		'not_found' => __( 'No events found', '' ), 
		'not_found_in_trash' => __( 'No events found in Trash', '' ), 
	); 
	$srel_args = array( 
		'label' => __( 'Events', '' ), 
		'labels' => $srel_labels, 
		'public' => true, 
		'can_export' => true, 
		'show_in_nav_menus' => false, 
		'show_ui' => true, 
		'capability_type' => 'post', 
		'menu_icon'   => 'dashicons-analytics',
		'supports'=> array('title', 'thumbnail', 'editor'), 
	); 
register_post_type( 'event', $srel_args); 
}
add_action( 'init', 'srel_custom_postype' ); 


// admin menu
function awts_menus() {
	//add_submenu_page("edit.php?post_type=event", "Team Setting", "Team Setting", "administrator", "awts-setting-page", "awts_pages");
	add_submenu_page("edit.php?post_type=event", "About Us", "About Us", "administrator", "about-us", "awts_pages");
}
add_action("admin_menu", "awts_menus");


//function menu pages
function awts_pages() {

   $setting = AWTS_INCLUDE_DIR.$_GET["page"].'.php';
   include($setting);

}


// create a metabox with date, time and location
function srel_metabox() { 
	add_meta_box( 
		'vsel-event-metabox', 
		__( 'Event Info', 'eventlist' ), 
		'srel_metabox_callback', 
		'event', 
		'side', 
		'default' 
	); 
} 
add_action( 'add_meta_boxes', 'srel_metabox' );


function srel_metabox_callback( $post ) { 
	// generate a nonce field 
	wp_nonce_field( 'srel_meta_box', 'srel_nonce' ); 
	
	// get previously saved meta values (if any) 
	$event_date = get_post_meta( $post->ID, 'event-date', true ); 
	$event_time = get_post_meta( $post->ID, 'event-time', true ); 
	$event_location = get_post_meta( $post->ID, 'event-location', true ); 
	$event_link = get_post_meta( $post->ID, 'event-link', true ); 

	// if there is saved date retrieve it, else set it to the current time 
	$event_date = ! empty( $event_date ) ? $event_date : time(); 
	
	

	// metabox fields
	?> 
	<script>
		jQuery(document).ready(function() {
		 jQuery('#vsel-date').datepicker({
		 dateFormat : 'dd-mm-yy'
		 });
		});
	</script>
	
	<p><label for="vsel-date"><?php _e( 'Event Date', '' ); ?></label> 
	<input class="widefat" id="vsel-date" type="text" name="vsel-date" required maxlength="10" placeholder="Date format: 20-10-2015" value="<?php echo date( 'd-m-Y', sanitize_text_field( $event_date ) ); ?>" /></p>
	<p><label for="vsel-time"><?php _e( 'Event Time', '' ); ?></label> 
	<input class="widefat" id="vsel-time" type="text" name="vsel-time" maxlength="150" placeholder="Example: 16.00 - 18.00" value="<?php echo sanitize_text_field( $event_time ); ?>" /></p>
	<p><label for="vsel-location"><?php _e( 'Event Location', '' ); ?></label> 
	<input class="widefat" id="vsel-location" type="text" name="vsel-location" maxlength="150" placeholder="Example: Times Square" value="<?php echo sanitize_text_field( $event_location ); ?>" /></p>
	<?php 
}


// save event
function srel_save_event_info( $post_id ) { 
	// check if nonce is set
	if ( ! isset( $_POST['srel_nonce'] ) ) {
		return;
	}
	// verify that nonce is valid
	if ( ! wp_verify_nonce( $_POST['srel_nonce'], 'srel_meta_box' ) ) {
		return;
	}
	// if this is an autosave, our form has not been submitted, so do nothing
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// check user permissions
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}
	// checking for the values and save fields 
	if ( isset( $_POST['vsel-date'] ) ) { 
		update_post_meta( $post_id, 'event-date', strtotime( $_POST['vsel-date'] ) ); 
	} 
	if ( isset( $_POST['vsel-time'] ) ) { 
		update_post_meta( $post_id, 'event-time', sanitize_text_field( $_POST['vsel-time'] ) ); 
	} 
	if ( isset( $_POST['vsel-location'] ) ) { 
		update_post_meta( $post_id, 'event-location', sanitize_text_field( $_POST['vsel-location'] ) ); 
	} 
	
} 
add_action( 'save_post', 'srel_save_event_info' );


// dashboard event columns
function srel_custom_columns( $defaults ) { 
	unset( $defaults['date'] ); 
	$defaults['event_date'] = __( 'Event Date', '' ); 
	$defaults['event_time'] = __( 'Event Time', '' ); 
	$defaults['event_location'] = __( 'Event Location', '' ); 
	return $defaults; 
} 
add_filter( 'manage_edit-event_columns', 'srel_custom_columns', 10 );

function srel_custom_columns_content( $column_name, $post_id ) { 
	if ( 'event_date' == $column_name ) { 
		$date = get_post_meta( $post_id, 'event-date', true ); 
		if($date != ''){
		echo date_i18n( get_option( 'date_format' ), $date ); 
		}

	} 
	if ( 'event_time' == $column_name ) { 
		$time = get_post_meta( $post_id, 'event-time', true ); 
		echo $time; 
	} 
	if ( 'event_location' == $column_name ) { 
		$location = get_post_meta( $post_id, 'event-location', true ); 
		echo $location; 
	} 
} 
add_action( 'manage_event_posts_custom_column', 'srel_custom_columns_content', 10, 2 );



//function for pagination
function srel_pagination($pages = '', $range = 4)
{   
     $showitems = ($range * 2)+1; 
	 $paged = (sanitize_text_field( $_GET['paged'] )) ? sanitize_text_field( $_GET['paged'] ) : 1;
     if(empty($paged)) $paged = 1;
     if($pages == '')
     {
         global $wp_query;
         $pages = $wp_query->max_num_pages;
         if(!$pages)
         {
             $pages = 1;
         }
     }  
     if(1 != $pages)
     {
         echo "<div class=\"pagination\"><span>Page ".$paged." of ".$pages."</span>";
         if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<a href='".get_pagenum_link(1)."'>&laquo; First</a>";
         if($paged > 1 && $showitems < $pages) echo "<a href='".get_pagenum_link($paged - 1)."'>&lsaquo;&lsaquo;</a>"; //previous
         for ($i=1; $i <= $pages; $i++)
         {
             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
             {
                 echo ($paged == $i)? "<span class=\"current\">".$i."</span>":"<a href='".get_pagenum_link($i)."' class=\"inactive\">".$i."</a>";
             }
         }
         if ($paged < $pages && $showitems < $pages) echo "<a href=\"".get_pagenum_link($paged + 1)."\">&rsaquo;&rsaquo;</a>"; //next
         if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($pages)."'>Last &raquo;</a>";
         echo "</div>\n";
     }
}




// include the shortcode files
include('pages/srel_all_events_shortcode.php');  //for all events
include('pages/srel_upcoming_events_shortcode.php'); // for upcomming events
include('pages/srel_past_events_shortcode.php');  // for past events


?>