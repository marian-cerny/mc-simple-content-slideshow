<?php

// MAIN CONTAINER
$s_output .= "<div class='{$this->plugin_namespace}slideshow_wrapper'>";

// SLIDESHOW CONTAINER
$s_output .= "<div class='{$this->plugin_namespace}slideshow'>";

// STARTING UL
$s_output .= " 
	<ul class='{$this->plugin_namespace}slideshow_ul'>
";

	
	// get slideshow ID
	$o_slideshow = get_page_by_path( $name, OBJECT, $this->plugin_namespace . 'slideshow' );
	$i_slideshow_id = $o_slideshow->ID;	
	
	// create slideshow query object
	$a_args = array(
		'post_type' => $this->plugin_namespace . 'slideshow',
		'posts_per_page' => -1,
		'post_parent' => $i_slideshow_id,
		'orderby' => $orderby,
		'order' => $order,
	);		
	$o_slideshow_query = new WP_Query( $a_args );
	
	// echo 'order by: ' . $orderby;
	
	// set loop variables
	$i_post_counter = 0;
	$i_max_count = 1; //$number_of_items; 
	$i_page_counter = 1;
	
	$b_new_page = true;
	
	while ( $o_slideshow_query->have_posts() ) : $o_slideshow_query->the_post();
	
	
		// if new page, open new li element
		if ( $b_new_page )
		{
			$s_output .= "<li class='{$this->plugin_namespace}slideshow_li page_{$i_page_counter}'>";
			$b_new_page = false;
		}
		$i_post_counter++;
		
		// GET THUMBNAIL SOURCE
		if ( function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID) ) 
		{
		 	$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), full );
			$s_thumb_src = $thumbnail[0];
		}
		
		// BUILD CLIENT HEADING
		$s_title = get_the_title();		
		$s_slide_title = "<h3>";	
		$s_slide_title .= $s_title;	
		$s_slide_title .= "</h3>";
	
		// BUILD SLIDE OUTPUT
		$s_output .= "

			<div class='{$this->plugin_namespace}slideshow_entry' style='background: url({$s_thumb_src}) center no-repeat'>
			
				<div class='{$this->plugin_namespace}slideshow_entry_text'>	
					
					<div class='{$this->plugin_namespace}slideshow_entry_text_header'>
							{$s_slide_title}
					</div>
					
					<div class='{$this->plugin_namespace}slideshow_entry_text_content'>
							" . get_the_content() . "
					</div>
					
				</div>
				
			</div>
			<!-- eof slideshow_entry -->
		";
	
		// CLOSE LIST ITEM IF MAX COUNT IS REACHED
		if ( (($i_post_counter % $i_max_count) == 0) )
		{
			$i_page_counter++;
			$s_output .= "</li>";
			$b_new_page = true;
		}

	endwhile;
	
	wp_reset_query();
	
	// CLOSE LAST LIST ITEM
	if ( !$b_new_page )
		$s_output .= "</li>";

// CLOSE LIST
$s_output .= "</ul>";

// PAGING
if ( $show_paging )
{
	$s_output .= "<div class='{$this->plugin_namespace}paging'>";
	for ( $i=1; $i < $i_page_counter; $i++)
		$s_output .= "<a href='#' class='{$this->plugin_namespace}paging_link {$this->plugin_namespace}page_{$i}'>{$i}</a>";
	$s_output .= "</div>";
}

// LEFT NAVIG ARROW
$s_output .= "
	<div class='{$this->plugin_namespace}navig_left'>
		<img src='{$this->plugin_url}/img/slider_arrow_left.png' alt='&lt;&lt;' />		
	</div>
";

// RIGHT NAVIG ARROW
$s_output .= "
	<div class='{$this->plugin_namespace}navig_right'>
		<img src='{$this->plugin_url}/img/slider_arrow_right.png' alt='&gt;&gt;' />	
	</div>
";
  
// END UL WRAPPER
$s_output .= "</div>";
  
// END MAIN CONTAINER AND 
$s_output .= "</div>";
  
$s_output .= "<div class='space'></div>";



// *******************************************************************
// ------------------------------------------------------------------
//						STYLES
// ------------------------------------------------------------------
// *******************************************************************

// $i_item_width = ceil( $width / $number_of_items );
$s_navig_display = ( $show_navig ) ? '' : 'display: none';
$s_ul_left = ( $i_post_counter > 1 ) ? "-{$i_item_width}px;" : "0";
$i_item_width = $width;
$s_output .= "
<style type='text/css'>

.mc_scs_slideshow_wrapper {
	
}

.{$this->plugin_namespace}slideshow {
	width: {$width}px;
	height:{$height}px;
	overflow: hidden;  
	position: relative;
	margin: auto;
}

.{$this->plugin_namespace}slideshow_ul {
	left:{$s_ul_left};
	position:relative;  
	list-style-type: none; 
	margin: 0px; 
	width:9999px; 
}

.{$this->plugin_namespace}slideshow_li {
	width: {$width}px;
	height:{$height}px;
	float: left; 
	margin: 0;
	padding: 0px; 
	position: relative;
}

.{$this->plugin_namespace}slideshow_entry {
	background: url({$s_thumb_src}) center no-repeat;
	height: {$height}px;
	width: {$i_item_width}px;
}

.{$this->plugin_namespace}slideshow_entry_text {
	position: absolute;
	bottom: 3%;
	left: 2%;
	width: 50%;
	background: rgba(50,50,50,0.7);
	color: white;
	text-align: left;
	padding: 1ex 1em;
}

.{$this->plugin_namespace}navig_left, .{$this->plugin_namespace}navig_right {  
	position: absolute;
	top: 40%;
	cursor: pointer;
	z-index: 3;
	{$s_navig_display}
}

.{$this->plugin_namespace}navig_left {
	left: 2%;
}

.{$this->plugin_namespace}navig_right {
	right: 2%;
}

.{$this->plugin_namespace}paging {
	position: absolute;
	top: 5%;
	right: 2%;
	z-index: 3;
}


</style>
";









