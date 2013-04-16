<?php

/*
Plugin name: Simple content slideshow
Plugin version: 1.0
Author name: Marian Cerny
Author URL: http://mariancerny.com
Description: Create slideshows with any content and add them to your website using simple shortcodes. Supports slide and crossfade transitions and works on touch devices.
*/

class mc_scs_plugin
{


// *******************************************************************
// ------------------------------------------------------------------
//					CONSTRUCTOR AND INITIALIZATION
// ------------------------------------------------------------------
// *******************************************************************


	var $settings = array(
		'slider' => array(
			'title' => 'Slider settings',
			'output_function' => 'output_settings_section_general',
			'fields' => array(
				'slider_width' => array(
					'title' => 'Slider width',
					'type' => 'number',
					'value' => 600,
				),
				'slider_height' => array(
					'title' => 'Slider height',
					'type' => 'number',
					'value' => 400,
				),
				'slider_autostart' => array(
					'title' => 'Autostart slider',
					'type' => 'checkbox',
					'value' => 'on',
				),
				'slider_interval' => array(
					'title' => 'Slider interval (s)',
					'type' => 'number',
					'value' => 5,
				),
				'slider_transition' => array(
					'title' => 'Slider transition',
					'type' => 'radio',
					'value' => 'transition_slide',
					'options' => array(
						'transition_slide' => 'Slide',
						'transition_fade' => 'Fade'
					),
				),
				'slider_transition_speed' => array(
					'title' => 'Transition speed (ms)',
					'type' => 'number',
					'value' => 1000,
				),
				'slider_disable_swipe' => array(
					'title' => 'Disable swipe',
					'type' => 'checkbox',
					'value' => '',
					'description' => 'Content cannot be scrolled vertically using the slider area. If your slider is too big, you may want to disable swipe.'
				),
				'slider_show_paging' => array(
					'title' => 'Show page numbers',
					'type' => 'checkbox',
					'value' => 'on',
				),
				'slider_show_navig' => array(
					'title' => 'Show navigation arrows',
					'type' => 'checkbox',
					'value' => 'on',
				),
				'slider_pause_on_hover' => array(
					'title' => 'Pause on hover',
					'type' => 'checkbox',
					'value' => '',
				),
			),
		),
		'items' => array(
			'title' => 'Items settings',
			'output_function' => 'output_settings_section_general',
			'fields' => array(
				// 'items_number' => array(
					// 'title' => 'Items per slide',
					// 'type' => 'number',
					// 'value' => 1,
					// 'description' => 'How many items are displayed on each slide.'
				// ),
				'items_orderby' => array(
					'title' => 'Order items by',
					'type' => 'radio',
					'value' => 'menu_order',
					'options' => array(
						'menu_order' => 'Menu order',
						'date' => 'Creation time',
						'title' => 'Alphabetical',
						'rand' => 'Random',
					),
				),
				'items_order' => array(
					'title' => 'Item order',
					'type' => 'radio',
					'value' => 'ASC',
					'options' => array(
						'ASC' => 'Ascending',
						'DESC' => 'Descending',
					),
				)
			),
		),
	);
	

	var $plugin_name;
	var $plugin_slug;
	var $plugin_url;
	var $plugin_version;
	var $plugin_namespace;
	
	
	function __construct()
	{	
		/* SET UP PLUGIN VARIABLES */
		$this->plugin_name = 'Simple content slideshow';
		$this->plugin_slug = 'mc-simple-content-slideshow';
		$this->plugin_url = plugins_url( '', __FILE__ );
		$this->plugin_version = '1.0';
		$this->plugin_namespace = 'mc_scs_';
		
		/* GET SETTINGS FROM DATABASE */
		$this->get_settings_from_db();
		
		/* ADD ACTIONS, SHORTCODES AND FILTERS */
		add_action( 'init', array( $this, 'register_custom_post_type' ) );
  		add_action( 'admin_menu', array( $this, 'register_settings') );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );
		add_shortcode('slideshow', array($this, 'slideshow_shortcode'));  
	}


// *******************************************************************
// ------------------------------------------------------------------
//							PUBLIC FUNCTIONS
// ------------------------------------------------------------------
// *******************************************************************





// *******************************************************************
// ------------------------------------------------------------------
//							PRIVATE FUNCTIONS
// ------------------------------------------------------------------
// *******************************************************************
	
	
	/* ENQUEUE STYLES AND SCRIPTS */
	function enqueue_styles_and_scripts()
	{
		// ENQUEUE JQUERY
		wp_enqueue_script( 'jquery' );
		
		// ENQUEUE STYLE
		// wp_enqueue_style( 'slider-style', $this->plugin_url . '/slider.css' );
		
		// ENQUEUE PLUGIN-NAME.JS SCRIPT
		wp_enqueue_script( 
			$this->plugin_slug, 
			$this->plugin_url . '/' . $this->plugin_slug . '.js',
			array( 'jquery' ),
			$this->plugin_version
		);
		
		// ENQUEUE TOUCHWIPE SCRIPT
		wp_enqueue_script( 
			'touchwipe', 
			$this->plugin_url . '/scripts/jquery.touchwipe.min.js',
			array( 'jquery' ),
			$this->plugin_version
		);
		
		// PASS PLUGIN SETTINGS TO THE SCRIPT
		$a_ajax_vars = array(
			'settings' => $this->get_settings_array(),
		);		
		wp_localize_script(
			$this->plugin_slug, 
			$this->plugin_namespace . 'ajax_vars', 
			$a_ajax_vars
		);
	}
	
	
	/* REGISTER CUSTOM POST TYPE */
	function register_custom_post_type()
	{
		$a_args = array(
			'labels' => array(
				'name' => 'Slideshows',
				'singular_name' => 'Slideshow',
				'new_item' => 'New Slideshow/Slide',
				'edit_item' => 'Edit Slideshow/Slide',
				'not_found' => 'No Slideshows',
				'add_new_item' => 'Add New Slideshow/Slide',
				'parent_item_colon' => 'Slideparent'
			),
			'description' => 'Top level posts are slideshows, children are individual slides of the given slideshow.',
			'public' => true,
			'publicly_queryable' => false,
			'show_in_nav_menus' => false,
			'hierarchical' => true,
			'supports' => array(
				'title', 'editor', 'thumbnail', 'page-attributes'
			),
			'menu_position' => 5,
		);
	
		register_post_type( $this->plugin_namespace . 'slideshow', $a_args );
	}
	
	
	/* SHORTCODE */
	function slideshow_shortcode( $atts )
	{
		extract( shortcode_atts( array(
			'name' => 'Slideshow',
			'width' => $this->get_setting( 'slider_width' ),
			'height' => $this->get_setting( 'slider_height' ),
			'show_paging' => $this->get_setting( 'slider_show_paging' ),
			'show_navig' => $this->get_setting( 'slider_show_navig' ),
			// 'number_of_items' => $this->get_setting( 'items_number' ),
			'orderby' => $this->get_setting( 'items_orderby' ),
			'order' => $this->get_setting( 'items_order' ),
		), $atts));
		
		$s_output = '';
		include 'slideshow.php';
		
		return $s_output;
	}
	
	
	/* ASSIGN SETTINGS FROM PLUGIN OPTIONS TO THE SETTINGS ARRAY */
	private function get_settings_from_db()
	{
		foreach ( $this->settings as $s_setting_key => $a_setting )
		{
			foreach( $a_setting['fields'] as $s_field_key => $m_field ) 
			{		
				// get options if they are set, or get defaults if not set
				$this->settings[$s_setting_key]['fields'][$s_field_key]['value'] 
					= get_option( $this->plugin_namespace . $s_field_key, $m_field['value'] );
					
				// write all options in DB in case they were not set
				update_option( $this->plugin_namespace . $s_field_key, $this->get_setting( $s_field_key ) );
			}
		}
	}
	
	
	/* GET ALL SETTINGS IN A SIMPLE KEY=>VALUE TYPE ARRAY  */ 
	private function get_settings_array()
	{
		$a_result = array();
	
		foreach ( $this->settings as $s_setting_key => $a_setting )
		{
			foreach( $a_setting['fields'] as $s_field_key => $m_field ) 
			{		
				$a_result[ $s_field_key ] = $this->get_setting( $s_field_key ) ;
			}
		}
		
		return $a_result;
	}
	
	
	/* RETURN THE VALUE OF A GIVEN SETTING FROM THE SETTINGS ARRAY */
	private function get_setting( $s_field_name )
	{
		foreach ( $this->settings as $a_setting )
		{
			if ( array_key_exists( $s_field_name, $a_setting['fields'] ) )
				return $a_setting['fields'][$s_field_name]['value'];
		}
		return false;
	}


// *******************************************************************
// ------------------------------------------------------------------
//							OPTIONS MENU
// ------------------------------------------------------------------
// *******************************************************************
	
	
	/* CREATE AN ENTRY IN THE SETTINGS MENU AND REGISTER/OUTPUT ALL SETTINGS */
	function register_settings() 
	{
		add_options_page(
			$this->plugin_name, 
			$this->plugin_name, 
			'manage_options', 
			$this->plugin_slug, 
			array( $this, 'output_options_page' )
		);
		
		// CREATE OPTIONS SECTIONS		
		foreach ( $this->settings as $s_section_name => $a_settings_section )
		{
				
			add_settings_section( 
				$this->plugin_namespace . $s_section_name, 
				$a_settings_section['title'], 
				array( $this, 'output_settings_section_general' ), 
				$this->plugin_slug
			);
			
			// CREATE OPTIONS FIELDS AND REGISTER SETTINGS
			foreach( $a_settings_section['fields'] as $s_field_name => $a_settings_field )
			{				
				add_settings_field(
					$this->plugin_namespace . $s_field_name, 
					$a_settings_field['title'],
					array($this, 'output_option'), 
					$this->plugin_slug, 
					$this->plugin_namespace . $s_section_name,
					array(
						'type' => $a_settings_field['type'],
						'name' => $s_field_name,
						'section' => $s_section_name,
						'description' => $a_settings_field['description'],
					)
				);
			
				register_setting( $this->plugin_namespace . 'settings', $this->plugin_namespace . $s_field_name );
			}
			
		}
		
	}
	
	/* OUTPUT OPTIONS PAGE */
	function output_options_page()
	{
		?>
		<div class="wrap">
		<h2><?php echo $this->plugin_name; ?> Settings</h2>
		
		<form method="post" action="options.php">
		
			<?php
			
			foreach ( $this->settings as $s_section_name => $a_settings_section )
				settings_fields( $this->plugin_namespace . 'settings' );
			
			do_settings_sections( $this->plugin_slug  );     
			submit_button(); 
			
			?>
		
		</form>
		</div>
		<?php
	}
	
	/* OUTPUT GENERAL SETTINGS SECTION */
	function output_settings_section_general()
	{
		echo '';
	}
	
	/* OUTPUT OPTION */
	function output_option( $args )
	{
		if ( $args['type'] == 'radio' )
		{
			$orig_value = get_option( $this->plugin_namespace . $args['name'] );
			
			// echo "<pre>"; print_r( $this->settings[$args['section']]['fields'][$args['name']]['options'] ); echo "</pre>";
			
			// echo $args['name'];
			
			foreach ( $this->settings[$args['section']]['fields'][$args['name']]['options'] as $key => $value )
			{
				$s_output = "<label for='" . $this->plugin_namespace . $key . "'>";
				$s_output .= "<input 
				type='radio' 
				name='". $this->plugin_namespace . $args['name'] ."' 
				value='" . $key . "' 
				id='" . $this->plugin_namespace . $key . "'";
						
				$s_output .= checked( $orig_value, $key, false );
				$s_output .= "'/>" . $value . " </label> <br/>";
			
				echo $s_output;
			}
		}
		else 
		{
			$s_output = "<input 
				name='" . $this->plugin_namespace . $args['name'] ."'
				id='" . $this->plugin_namespace . $args['name'] ."'
				type='" . $args['type']."'";
			
			if ( $args['type'] == 'checkbox' )
				$s_output .= checked( 'on', get_option( $this->plugin_namespace . $args['name'], false ), false );
			else
				$s_output .= "value='".get_option( $this->plugin_namespace . $args['name'] )."'";
				
			if ( !empty( $args['description'] ) )
				$s_output .= "/> (" . $args['description'] . ")";
			
			echo $s_output;	
		}
		
	}


}


// *******************************************************************
// ------------------------------------------------------------------
//						FUNCTION SHORTCUTS
// ------------------------------------------------------------------
// *******************************************************************

$mc_scs_plugin = new mc_scs_plugin();



?>