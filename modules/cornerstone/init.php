<?php

if ( class_exists('Cornerstone_Plugin') ) {

	define( 'CS_IWM_PATH', plugin_dir_path( __FILE__ ) );
	define( 'CS_IWM_URL', plugin_dir_url( __FILE__ ) );

	add_action( 'cornerstone_register_elements', 'cs_iwm_register_elements' );
	add_filter( 'cornerstone_icon_map', 'cs_iwm_icon_map' );


}


function cs_iwm_register_elements() {
	cornerstone_register_element( 'CS_Interactive_World_Maps', 'interactive-world-maps', dirname(__FILE__)  );
}



function cs_iwm_icon_map( $icon_map ) {
	$icon_map['iwm'] = plugin_dir_url( __FILE__ ).'icon.svg';
	return $icon_map;
}

?>