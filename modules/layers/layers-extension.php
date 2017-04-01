<?php
/**
* Layers Demo Extension
*
* What your plugin does
*
* @package Layers
* @since Layers 1.2.4
*
* http://docs.layerswp.com/create-an-extension-setup-your-plugin-class/#setup-class
*/

class IWM_Layers_Extension {

	// Setup Instance
	// http://docs.layerswp.com/create-an-extension-setup-your-plugin-class/#get_instance
	private static $instance;
	
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
			self::$instance->__construct();
		}
		return self::$instance;
	}
	
	// Constructor
	// http://docs.layerswp.com/create-an-extension-setup-your-plugin-class/#constructor
	
	private function __construct() {
		
		// Register custom widgets
		add_action( 'widgets_init' , array( $this, 'register_widgets' ), 50 );	
		if( 'layerswp' != get_template() ) return;
								
	}
 
	
	// Register Widgets
	// Not the same as register_widget()! 
	
	function register_widgets(){
		
		if( 'layerswp' !== get_template() ){
			return;
		} 
		else {
			require_once dirname(__FILE__) . '/layers-widget.php';
		}
		
	}
	
	
} // END Class


function layers_iwm_extension_init() {
	global $iwm_layers_extension;
	$iwm_layers_extension = IWM_Layers_Extension::get_instance();
}

add_action( 'plugins_loaded', 'layers_iwm_extension_init' );