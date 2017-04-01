<?php

/**
 * Element Controls
 */

global $wpdb;
global $table_name_imap;

$iwm_maps_created = $wpdb->get_results("SELECT * FROM $table_name_imap", ARRAY_A);

if(count($iwm_maps_created) == 0) {
	$iwm_maps =  array( array( 'value' => '', 'label' => __('-- Please create a map first --','iwm') ));

} else {

	$iwm_maps =  array( array( 'value' => '', 'label' => __('-- Please Select --','iwm') ));

}

foreach ($iwm_maps_created as $map) {
	$temp =  array( 'value' => $map['id'], 'label' => $map['name'] );
	array_push($iwm_maps, $temp);
}

return array(

	'heading' => array(
		'type'    => 'text',
		'ui' => array(
			'title'   => __( 'Title', 'my-extension' ),
			'tooltip' => __( 'Add a title above the map', 'iwm' ),
		),
		'context' => 'content',
	),
	
	'heading_color' => array(
	 	'type' => 'color',
	 	'ui' => array(
			'title'   => __( 'Title Color', 'iwm' )
		)
	),

	'align' => array(
	 	'type' => 'choose',
	 	'ui' => array(
			'title'   => __( 'Title Alignment', 'iwm' )
		),
		'options' => array(
	      	'columns' => '3',
	        'choices' => array(
	          array( 'value' => 'left',  'label' => __( 'Left', 'cornerstone' ),  'icon' => fa_entity( 'align-left' ) ),
	          array( 'value' => 'center',  'label' => __( 'Center', 'cornerstone' ),  'icon' => fa_entity( 'align-center' ) ),
	          array( 'value' => 'right', 'label' => __( 'Right', 'cornerstone' ), 'icon' => fa_entity( 'align-right' ) )
	        )
	    ),
	),

	'iwmid' => array(
		'type' => 'select',
		'ui' => array(
	  		'title' => __( 'Choose Map', 'iwm' ),
     		'tooltip' => __( 'Choose which previously created map to display. Will only be visible on live site.', 'iwm' ),
		),
		'options' => array(
		    'choices' => $iwm_maps
    )
  ),
	'map_padding' => array(
	 	'type' => 'dimensions',
	 	'ui' => array(
			'title'   => __( 'Map Padding', 'iwm' )
		)
	),

	
);