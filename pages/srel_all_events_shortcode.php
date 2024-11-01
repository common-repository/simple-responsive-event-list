<?php

// The shortcode
function srel_all_events_shortcode() {


echo  '<div id="srel-main-div">'; 
echo '<div class="head-event"><h2>All Events</h2></div>';

	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1; 
	$today = strtotime('today'); 
	$srel_query_args = array( 
		'post_type' => 'event', 
	    'posts_per_page' => 10,
		'post_status' => 'publish', 
		'meta_key' => 'event-date', 
 		'paged' => $paged, 
	); 

	$srel_events = new WP_Query( $srel_query_args );

	if ( $srel_events->have_posts() ) : 
		while( $srel_events->have_posts() ): $srel_events->the_post(); 
	
		$event_date = get_post_meta( get_the_ID(), 'event-date', true ); 
		$event_time = get_post_meta( get_the_ID(), 'event-time', true ); 
		$event_location = get_post_meta( get_the_ID(), 'event-location', true ); 
		
		// display the event list
		echo  '<div class="srel-content">'; 
			echo '<div class="srel-content-left">';
				echo  '<div class="srel-thumbnail">';
					if ( has_post_thumbnail() ) { 
					   echo get_the_post_thumbnail(get_the_ID(),'full'); 
					} 
				echo  '</div>';
			echo '</div>';
			
			echo '<div class="srel-content-right">';
			        echo  '<h4>';
					echo  get_the_title();  
					echo  '</h4>';
					echo  '<p>';
					echo  'Date: '; 
					echo  date_i18n( get_option( 'date_format' ), $event_date );  
					echo  '</p>';
					if(!empty($event_time)){
						echo  '<p>';
						echo  'Time: '; 
						echo  $event_time; 
						echo  '</p>';
					}
					if(!empty($event_location)){
						echo  '<p>';
						echo  'Location: '; 
						echo  $event_location; 
						echo  '</p>';
					}
					echo '<div class="click-to-view"><a href="javascript:void(0)" onclick="showEventDeatails('.get_the_ID().')">view Deatils</a></div>';
					
			echo '</div>';	
			
			   //detailed pop up start
				echo  '<div style="display:none;" class="cls-event-detail" id="event-detail-pop-'.get_the_ID().'">';
				echo   '<div class="close-pop"><a href="javascript:void(0)" onclick="hideEventDatails('.get_the_ID().')" >X</a></div>';
					echo  '<div class="det-left">';
					if( has_post_thumbnail() ){ 
						echo   '<div class="eve-image">'.get_the_post_thumbnail(get_the_ID(), 'full') .'</div>';
					}
					
				echo  '</div>';
				echo '<div class="det-right">';
				echo '<div class="eve-title">'.get_the_title().'</div>';
				echo '<div class="eve-desc">'.get_the_content().'</div>';
				echo '<div class="eve-date"> Date: '.date_i18n( get_option( 'date_format' ), $event_date ).'</div>';
				echo '<div class="eve-time"> Time: '.$event_time.'</div>';
				echo '<div class="eve-location"> Location: '.$event_location.'</div>';
				echo  '</div>';
				
				echo  '</div>';
			  //detailed pop up end	
				
		echo  '</div>';
	
		endwhile; 
	
		// pagination 
		if (function_exists("srel_pagination")) {
		   echo  '<div class="event-pagination">';
		   echo  srel_pagination($srel_events->max_num_pages);
		   echo '</div>';
		} 
		
		echo '<div style="display:none;" class="pop-mask"></div>'; //mask
		echo '<script type="text/javascript">
		function showEventDeatails(memid){
		 jQuery("#event-detail-pop-"+memid).show();
		 jQuery(".pop-mask").show();
		}
        function hideEventDatails(memid){
		 jQuery("#event-detail-pop-"+memid).hide();
		 jQuery(".pop-mask").hide();
		}		
	</script>';
	
		wp_reset_postdata(); 
		else:

		echo  '<p>There are no events.</p>';
	endif; 
echo  '</div>';
} 

add_shortcode('list_all_events', 'srel_all_events_shortcode');

?>