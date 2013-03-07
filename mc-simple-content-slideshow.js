var $ = jQuery.noConflict();

$(document).ready(function() {

	// simplify settings variable
	var settings = mc_scs_ajax_vars.settings;
	var transition_speed = parseInt( settings.slider_transition_speed );
	
	// simplify slideshow object vars
	slideshow = new Object();
	slideshow.main = $( '.mc_scs_slideshow' );
	slideshow.wrapper = $( '.mc_scs_slideshow_wrapper' );
	slideshow.ul = $( '.mc_scs_slideshow_ul' );
	slideshow.navig_left = $( '.mc_scs_navig_left' );
	slideshow.navig_right = $( '.mc_scs_navig_right' );		
	slideshow.page_links = $( '.mc_scs_paging a' );
	
	// start timer
	var slide_interval;
	reset_interval();
	
	//stop timer when 'pause on hover' is active
	if ( settings.slider_pause_on_hover )
	{
		$( slideshow.main ).mouseover( function(){
			if ( typeof( slide_interval ) != 'undefined' )
				clearInterval( slide_interval );
		} );
		$( slideshow.main ).mouseleave( function(){
			reset_interval();
		} );
	}
	
	// mark first page link as selected
	if ( $( slideshow.page_links ).length > 0 )
		$( slideshow.page_links ).first().addClass( 'pager_active' );
	
	// get list item width
	var item_width = $(slideshow.ul).children('li').outerWidth();
		
	// if there's only one page, move the slider to original position and stop
	if ($(slideshow.ul).children('li').length <= 1)
	{
		$(slideshow.ul).css({'left' : '0px'});
		return false;
	}

	// get first and last slide
	var first = $(slideshow.ul).children('li:first');
	var last = $(slideshow.ul).children('li:last');
	
	// put last item before first 
	first.before(last);
	
	// in case of two pages, duplicate them
	// this is to ensure a smooth sliding transition in both directions
	if ($(slideshow.ul).children('li').length < 3)
	{
		last.before(first.clone());
		first.after(last.clone());
	}

	/* LEFT CLICK EVENT */ 
	$(slideshow.navig_left).click(function(){
		move( 'left' );	
		reset_interval();
		return false;
	});

	/* RIGHT CLICK EVENT */ 
	$(slideshow.navig_right).click(function(){
		move( 'right' );
		reset_interval();
		return false;
	});
	
	/* SWIPE EVENTS */
	if ( !settings.slider_disable_swipe )
	{
		$( slideshow.main ).touchwipe({
			wipeLeft: function() { move( 'left' ) },
			wipeRight: function() {  move( 'right' ) },
		});
		
		reset_interval();
	}
	
	
	/* PAGING LINK CLICK */
	$( slideshow.page_links ).click( function()
	{
		// get clicked and current link elements
		var clicked = $( this );
		var current = $( '.pager_active' );
		
		// calculate difference and initiate move
		var difference = clicked.html() - current.html();
		if ( difference > 0 )
			move( 'right', difference )
		else
			move( 'left', -difference );
			
		// switch active states
		clicked.addClass( 'pager_active' );
		current.removeClass( 'pager_active' );
		
		reset_interval();
		return false;
	} );
	


// *******************************************************************
// ------------------------------------------------------------------
//						TRANSITION FUNCTIONS
// ------------------------------------------------------------------
// *******************************************************************


/* SLIDE LEFT */
function slide_left( steps )
{	
	var left_indent = parseInt($(slideshow.ul).css('left')) - steps * item_width;
	$(slideshow.ul).animate(
		{'left' : left_indent},
		{
			queue: false, 
			duration: transition_speed,
			complete: function() {
				for ( var i=0; i < steps; i++ )
					$(slideshow.ul).children('li:last').after($(slideshow.ul).children('li:first'));
				$(slideshow.ul).css({'left' : '-' + item_width + 'px'});
			}
		}
	);
}


/* SLIDE RIGHT */
function slide_right( steps )
{
	var left_indent = parseInt($(slideshow.ul).css('left')) + steps * item_width;
	$(slideshow.ul).animate(
		{'left' : left_indent},
		{
			queue: false, 
			duration: transition_speed,
			complete: function() 
			{
				for ( var i=0; i < steps; i++ )
					$(slideshow.ul).children('li:first').before($(slideshow.ul).children('li:last'));
				$(slideshow.ul).css({'left' : '-' + item_width + 'px'});
			}
		}
	);
}


/* FADE LEFT */
function fade_left( steps )
{
	var left_indent = parseInt( $( slideshow.ul ).css( 'left' ) ) - steps * item_width;
	
	// clone slideshow and put it over the current one
	var temp_slideshow = $( slideshow.ul ).clone();	
	$( slideshow.ul ).parent().append( temp_slideshow );
	
	// reposition the current slideshow
	for ( var i=0; i < steps; i++ )
		$( slideshow.ul ).children( 'li:last' ).after($(slideshow.ul).children('li:first'));
	$( slideshow.ul ).css( { 'left' : '-' + item_width + 'px', 'position': 'absolute' } );
	
	// fade out cloned slideshow and remove it when animation is finished
	$( temp_slideshow ).fadeOut( transition_speed, function()
	{ 
		$( temp_slideshow ).remove();
	});
}


/* FADE RIGHT */
function fade_right( steps )
{
	var left_indent = parseInt( $( slideshow.ul ).css( 'left' ) ) + steps * item_width;
	
	// clone slideshow and put it over the current one
	var temp_slideshow = $( slideshow.ul ).clone();	
	$( slideshow.ul ).parent().append( temp_slideshow );
	
	// reposition the current slideshow
	for ( var i=0; i < steps; i++ )
		$( slideshow.ul ).children('li:first').before($( slideshow.ul ).children('li:last'));
	$( slideshow.ul ).css( { 'left' : '-' + item_width + 'px', 'position': 'absolute' } );
	
	// fade out cloned slideshow and remove it when animation is 
	var transition_speed = parseInt( settings.slider_transition_speed );
	$( temp_slideshow ).fadeOut( transition_speed, function()
	{ 
		$( temp_slideshow ).remove();
	});
}



// *******************************************************************
// ------------------------------------------------------------------
//						OTHER FUNCTIONS
// ------------------------------------------------------------------
// *******************************************************************


function reset_interval()
{
	if ( typeof( slide_interval ) != 'undefined' )
		clearInterval( slide_interval );

	if ( settings.slider_autostart )
		slide_interval = setInterval( function(){ move( 'right' ) }, 1000 * settings.slider_interval );
}

	
function move( direction, steps )
{
	// if steps of steps is not defined, assume 1
	// switching link active state was done on click
	if ( typeof( steps ) == 'undefined' )
	{
		steps = 1;
		// change the 'active' state of paging links	
		if ( $( slideshow.page_links ).length > 0 )
		{
			var active = $( '.pager_active' );	
			// if moving left
			if ( direction == 'left' )
			{
				// if first is active, pass the active state to last link
				if ( $( '.pager_active' ).is( $( slideshow.page_links ).first() ) )
					$( slideshow.page_links ).last().addClass( 'pager_active' );
				// else pass it to previous link
				else
					$( '.pager_active' ).prev().addClass( 'pager_active' );
				$( active ).removeClass( 'pager_active' );
			} else
			{
				// if last is active, pass the active state to first link
				if ( $( '.pager_active' ).is( $( slideshow.page_links ).last() ) )
					$( slideshow.page_links ).first().addClass( 'pager_active' );
				// else pass it to next link
				else
					$( '.pager_active' ).next().addClass( 'pager_active' );
				$( active ).removeClass( 'pager_active' );
			}
		}
	}

	// select appropriate action to move the slideshow
	switch ( settings.slider_transition )
	{
		case 'transition_slide' :
		{
			if ( direction == 'left' )
				slide_right( steps );
			else
				slide_left( steps );
			break;
		}
		case 'transition_fade' :
		{
			if ( direction == 'left' )
				fade_right( steps );
			else
				fade_left( steps );
			break;
		}
	}
	
	
	
	
}


});