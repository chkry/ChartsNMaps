<?php
/*
Plugin Name: Web Chart Maps
Plugin URI: www.github.com/chkry/webchartmaps
Description: Create interactive maps for your website, including posts, pages and widgets. You can set the view to the whole world, a continent, a specific country or any state. You can color full regions or just create markers on specific locations that will have information on hover and can also have actions on click.
The Web Chart Maps plugin uses the Google GeoChart API to render the maps.
Licence :
* MIT License

Copyright (c) [2015] [CHARTS N MAPS]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
 *
 * https://github.com/chkry/ChartsNMaps
 *
 *
Author: CHARTS N MAPS
Version: 1.0
Author URI: https://github.com/chkry/ChartsNMaps
*/
//Include files
//Widget code
require_once dirname(__FILE__) . '/modules/widget.php';

//Widget code for Layers 
require_once dirname(__FILE__) . '/modules/layers/layers-extension.php';

//Element code for Cornerstone
require_once dirname(__FILE__) .'/modules/cornerstone/init.php';





//Instalation Code
//Creates Table in the database


global $wpdb;
$table_name_imap = $wpdb->prefix . "i_world_map";
$iwmparam_array = array();

//used in beta features in case they are not yet deployed
//like the html tooltips
$apiver = "1";

function i_world_map_install() {
	
	global $wpdb;
	$table_name_imap = $wpdb->prefix . "i_world_map";
	$iwm_db_version = 5;
	
	
	$charset_collate = "";
	
    if ( ! empty ( $wpdb->charset ) )
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

    if ( ! empty ( $wpdb->collate ) )
        $charset_collate .= " COLLATE $wpdb->collate";
	
	
	$sql = "CREATE TABLE $table_name_imap (
					  id int(11) NOT NULL AUTO_INCREMENT,
					  name varchar(255) DEFAULT NULL,
					  description longtext,
					  use_defaults int(11) DEFAULT NULL,					  
					  bg_color varchar(100) DEFAULT NULL,
					  border_color varchar(100) DEFAULT NULL,
					  border_stroke varchar(100) DEFAULT NULL,
					  ina_color varchar(100) DEFAULT NULL,
					  act_color varchar(100) DEFAULT NULL,
					  marker_size int(11) DEFAULT NULL,	
					  width varchar(100) DEFAULT NULL,
					  height varchar(100) DEFAULT NULL,
					  aspect_ratio int(11) DEFAULT NULL,
					  interactive int(11) DEFAULT '1',
					  showtooltip int(11) DEFAULT '1',
					  region varchar(100) DEFAULT NULL,
					  display_mode varchar(100) DEFAULT NULL, 
					  map_action varchar(100) DEFAULT NULL,
					  places LONGTEXT NULL DEFAULT NULL,
					  image LONGTEXT NULL DEFAULT NULL,
					  custom_action LONGTEXT NULL DEFAULT NULL,
					  custom_css LONGTEXT NULL DEFAULT NULL,
					  created timestamp NULL DEFAULT CURRENT_TIMESTAMP,
					  UNIQUE KEY id (id)
    		) $charset_collate;";


	$currentdbversion = $iwm_db_version;
	$storeddbversion = false;
	$storeddbversionexists = get_option('i_world_map_db_version');
	
	
	if($storeddbversionexists != false) {
		$storeddbversion = $storeddbversionexists;
	}
	

	if ($storeddbversionexists != false && $storeddbversionexists != $currentdbversion) {

		//upgrade function
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		update_option( "i_world_map_db_version", $currentdbversion );			

	}

	
	if ($storeddbversionexists == false) {
		update_option("i_world_map_db_version", $currentdbversion);
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

	 }

			
}

//Install Unistall Hook
register_activation_hook(__FILE__, 'i_world_map_install');

//Run the Shortcode to Build Interactive Map
function shortcode_i_world_map( $atts ) {	

	$html = '';

	if(isset($atts['id'])) {
		$id = $atts['id'];
	} 

	else {

		global $wpdb;
		global $table_name_imap;
		$maps_created = $wpdb->get_results("SELECT * FROM $table_name_imap", ARRAY_A);
		$id = $maps_created[0]['id'];
		
	}


	

	//to have another map overlay we created a new parameter

	if(isset($atts['overlay'])) {

		

		$html .= '<div id="iwm_map_overlay">';
		$html .= build_i_world_map_exec($id,'shortcode','base',$atts);
		$html .= build_i_world_map_exec($atts['overlay'],'shortcode','data',$atts);
		$html .= '</div>';

		$html .= '
 		<!-- Map Overlay Styles -->
		<style type="text/css">
		 #iwm_map_overlay #map_canvas_'.$atts['overlay'].' {
		    pointer-events:none;
		   }

		 #iwm_map_overlay #map_canvas_'.$atts['overlay'].' path:not([fill-opacity="0.25"])  {
		    display:none;
		   }

		#iwm_map_overlay #map_canvas_'.$atts['overlay'].' g[clip-path] > * {
		   display:block;
		   pointer-events:visible;
		}

		 #iwm_map_overlay #map_canvas_'.$atts['overlay'].' path[fill-opacity="0.25"]  {
		    pointer-events:visible;
		   }

		 #iwm_map_overlay #map_canvas_'.$atts['overlay'].' text, 
		 #iwm_map_overlay #map_canvas_'.$atts['overlay'].' circle {
		    pointer-events:visible; 
		}
		</style>';

		return $html;

	} else {


		return build_i_world_map_exec($id,'shortcode',false,$atts);	

	}

	
}

//run php comand to Build Interactive Map
function build_i_world_map($id) {

	$atts = null;
	build_i_world_map_exec($id,'php',false,$atts);
}

//Add shortcode functionality
add_shortcode('show-map', 'shortcode_i_world_map');
add_filter('widget_text', 'do_shortcode');
add_filter( 'the_excerpt', 'do_shortcode');

//Extra Shortcodes
add_shortcode('show-map-list', 'iwm_build_i_world_map_list');
add_shortcode('show-map-dropdown', 'iwm_build_i_world_map_dropdown');
add_shortcode('show-map-count', 'iwm_build_i_world_map_count');



function iwm_build_i_world_map_count($atts) {
		
	$id = $atts['id'];
	
	global $wpdb;
	global $table_name_imap;
	$mapdata = $wpdb->get_row("SELECT * FROM $table_name_imap WHERE id = $id", ARRAY_A);
	$input = str_replace(array("\r\n", "\r", "\n"), ' ', addslashes($mapdata['places']));
	$places = explode(";", $input,-1);
	
	$html = count($places);
	
	return $html;
}


//Main Function to build the list - BETA
function iwm_build_i_world_map_list($atts) {
		
	$id = $atts['id'];
	
	global $wpdb;
	global $table_name_imap;
	$mapdata = $wpdb->get_row("SELECT * FROM $table_name_imap WHERE id = $id", ARRAY_A);
	$input = str_replace(array("\r\n", "\r", "\n"), ' ', addslashes($mapdata['places']));
	$places = explode(";", $input,-1);
	$map_action = $mapdata['map_action'];
	$target = "";
	if($map_action=="i_map_action_open_url_new") {$target ="target='_blank'"; }
	
	$html  = "";
	$html = $html."<ul id='iwm-list-".$id."' class='iwm-list'>";
	
	$orderoptions = array();

		$i = 1;
		foreach ($places as $place) { 
			$arr = explode(",",$place);
			$ttit  = $arr[1];
			$ttool = $arr[2];
			$ofinal = array(",", ";");
			$oreplace   = array("&#44", "&#59");
			$ttitle = str_replace($oreplace,$ofinal , $ttit);
			$ttooltip = str_replace($oreplace,$ofinal , $ttool);
			$index = trim($arr[0]);
			$oaction  = trim($arr[3]);
			$ofinal = array(",", ";");
			$oreplace   = array("&#44", "&#59");		
			$formatedactionv = str_replace($oreplace,$ofinal , $oaction);
			
			//$html = $html. "<li><a href='".$formatedactionv."' title='".$ttooltip."' ".$target.">".trim($ttitle)."</a></li>";
			//$html = $html. "<li><a onmouseover='iwm_setSelection(\"".$index."\",".$id.")' onclick='iwm_select(\"".$index."\",".$id.")' onmouseleave='iwm_clearSelection(".$id.")' title='".$ttooltip."'>".trim($ttitle)."</a></li>";
					
			$orderoptions[$arr[1]] = "<li><a onmouseover='iwm_setSelection(\"".$index."\",".$id.")' onclick='iwm_select(\"".$index."\",".$id.")' onmouseleave='iwm_clearSelection(".$id.")' title='".$ttooltip."'>".stripslashes(trim($ttitle))."</a></li>";

		}
					

	ksort($orderoptions);

	foreach ($orderoptions as $key => $value) {
		$html .= $value;
	}



	
	$html = $html."</ul>";
	return $html;
}

//function to build dropdown - BETA
function iwm_build_i_world_map_dropdown($atts) {
		
	$id = $atts['id'];
	
	global $wpdb;
	global $table_name_imap;
	$mapdata = $wpdb->get_row("SELECT * FROM $table_name_imap WHERE id = $id", ARRAY_A);
	$input = str_replace(array("\r\n", "\r", "\n"), ' ', addslashes($mapdata['places']));
	$places = explode(";", $input,-1);
	sort($places);
	$map_action = $mapdata['map_action'];
	$target = "";
	
	
	$html  = "";
	$before = "";
	$after = "";

	$html = $before;


	$html = $html."<select id='imap-dropdown-".$id."' onchange='iwm_select(value,".$id.")'>";
	$html = $html ."<option>Please Select...</option>";

	$orderoptions = array();

		$i = 1;
		foreach ($places as $place) { 
			$arr = explode(",",$place);
			$ttit  = $arr[1];
			$ttool = $arr[2];
			$ofinal = array(",", ";");
			$oreplace   = array("&#44", "&#59");
			$ttitle = str_replace($oreplace, $ofinal, $ttit);
			$ttooltip = str_replace($oreplace, $ofinal, $ttool);
			$index = trim($arr[0]);
			$oaction  = trim($arr[3]);
			$ofinal = array(",", ";");
			$oreplace   = array("&#44", "&#59");		
			$formatedactionv = str_replace($oreplace,$ofinal , $oaction);

			$formatedactionv = str_replace('\"','{quote}', $formatedactionv);

			if($formatedactionv!='') {
				//$html = $html. "<option value='".$formatedactionv."' id='imap".$id."-".trim($index)."'>".trim($ttitle)."</option>";
				//$html = $html. "<option id='imap".$id."-".trim($index)."' value='".$index."' title='".$ttooltip."'>".trim($ttitle)."</option>";
				
				$orderoptions[$arr[1]] = "<option id='imap".$id."-".trim($index)."' value='".$index."' title='".$ttooltip."'>".trim($ttitle)."</option>";

			}

			


			
					
	}

	ksort($orderoptions);

	foreach ($orderoptions as $key => $value) {
		$html .= $value;
	}
	
	$html = $html."</select>";

	$html = $html.$after;

	return $html;
}

//Main Function to build the map
function build_i_world_map_exec($id,$type,$overlay=false,$atts) {
	
	global $wpdb;
	global $table_name_imap;
	global $iwmparam_array;
	global $apiver;

	$options = get_option('i-world-map-settings');

	if($options == false) {
		i_world_map_defaults();
		$options = get_option('i-world-map-settings');
	}

	$mapdata = $wpdb->get_row("SELECT * FROM $table_name_imap WHERE id = $id", ARRAY_A);
	$input = $mapdata['places'];
	$id = $mapdata['id'];
	
	
	//Check if custom css for this map exist
	$styles = '';
	$overrideh = false;

	if($mapdata['custom_css']!='') {

		$cssarray = json_decode(stripslashes($mapdata['custom_css']),true);

		if(is_array($cssarray)) {
			$css = array_filter($cssarray,'iwm_array_empty');
		} 

		else {
			$css = array();
		}

		
		
		$inactivecolor = strtolower($mapdata['ina_color']);

		if(!empty($css)) {

				$styles = "<!-- Map Generated CSS --> \n <style>";
				$styles .= "\n.iwm_map_canvas { overflow:hidden; }";

				//set margin left
				if(isset($css['iwm_left']) && $css['iwm_left'] != '') {

					$styles .= "\n#map_canvas_".$id." { margin-left: ".$css['iwm_left']."%; }";

				}

				//set margin top
				if(isset($css['iwm_top']) &&$css['iwm_top'] != '') {

					$styles .= "\n#map_canvas_".$id." { margin-top: ".$css['iwm_top']."%; }";

				}

				//set size %
				if(isset($css['iwm_size']) &&$css['iwm_size'] != '' && $css['iwm_size'] != '100') {

					$styles .= "\n#map_canvas_".$id." { width: ".$css['iwm_size']."%; height: ".$css['iwm_size']."%; }";

				}


				//set vertical override size

				
				if(isset($css['iwm_hsize']) && $css['iwm_hsize'] !='' && $css['iwm_hsize'] != '61.7') {
					$overrideh = true;
					$styles .= '#iwm_'.$id.' .iwm_map_canvas:after { padding-top:'.$css['iwm_hsize'].'%; }';

				}
					

				//set hovercolor
				if(isset($css['hovercolor']) && $css['hovercolor'] != '') {

					
					if($mapdata['use_defaults'] == 1) {
						$inactivecolor = strtolower($options['default_ina_color']);				
					}

					//old simple way to implement hover
					//$styles .= '#map_canvas_'.$id.' path:not([fill^="'.$inactivecolor.'"]):hover { fill:'.$css['hovercolor'].'; }';

					//new way to implement hover
					$styles .= "#map_canvas_".$id." path[stroke-width^='3'] + path { display:none; }";
					$styles .= "#map_canvas_".$id." path[stroke-width^='3'] + path + path:not([fill^='".$inactivecolor."']) { display:none; }";
					$styles .= "#map_canvas_".$id." path[stroke-width^='3'] { fill:".$css['hovercolor'].";  }";
					$styles .= "#map_canvas_".$id." path[fill^='".$inactivecolor."'] { pointer-events: none; }";
					$styles .= "#map_canvas_".$id." path[fill^='none'] { pointer-events: none; }";


					$bw = 1;
					if(isset($css['bwidth']) && $css['bwidth']!='') {
						$bw = $css['bwidth'];
					}

					$styles .= "#map_canvas_".$id." path:not([fill^='".$inactivecolor."']) + path[stroke-width^='3'] { stroke-width:".$bw."; stroke-opacity:0; stroke:".$css['hovercolor']."; }";

					//for circle and text
					$styles .= "#map_canvas_".$id." circle[stroke-width='3'] { fill:".$css['hovercolor'].";  }";
					$styles .= "#map_canvas_".$id." text:hover { fill:".$css['hovercolor'].";  }";




				}

				//set cursor
				if(isset($css['showcursor']) && $css['showcursor'] == '1') {

					$styles .= '#map_canvas_'.$id.' path:not([fill^="'.$inactivecolor.'"]):hover { cursor:pointer; }';	
					$styles .= '#map_canvas_'.$id.' circle:hover { cursor:pointer; }';	
					$styles .= '#map_canvas_'.$id.' text:hover { cursor:pointer; }';			

				}

				//set border/path colour
				if(isset($css['bcolor']) && $css['bcolor']!='') {

					$styles .= '#map_canvas_'.$id.' path:not([id]) { stroke:'.$css['bcolor'].'; }';

				}

				//set border/path width
				if(isset($css['bwidth']) && $css['bwidth']!='') {

					$styles .= '#map_canvas_'.$id.' path:not([id]) { stroke-width:'.$css['bwidth'].'; }';

				}

				//set border/path width for inactive regions
				if(isset($css['biwidth']) && $css['biwidth']!='') {

					$styles .= '#map_canvas_'.$id.' path[fill^="'.$inactivecolor.'"] { stroke-width:'.$css['biwidth'].'; }';
					$styles .= '#map_canvas_'.$id.' path[fill^="'.$inactivecolor.'"]:hover { stroke-width:'.$css['biwidth'].'; }';
					$styles .= '#map_canvas_'.$id.' path[fill^="none"] { stroke-width:'.$css['biwidth'].'; stroke-opacity:0; }';

				}

				//set background image
				if(isset($css['bgimage']) && $css['bgimage']!='') {
					$mapdata['bg_color'] = 'transparent';
					$options['default_bg_color'] = 'transparent';
					$styles .= '#map_canvas_'.$id.' { background-image: url("'.$css['bgimage'].'"); }';

				}

				//set background image repeat
				if(isset($css['bgrepeat']) && $css['bgrepeat']!='') {
					if($css['bgrepeat']=='1') {
					$styles .= '#map_canvas_'.$id.' { background-repeat:repeat; }';
					} 
				} 
				if(!isset($css['bgrepeat'])) {
					$styles .= '#map_canvas_'.$id.' { background-repeat:no-repeat; background-size: 100% 100%; }';
				}

				//HTML Tooltips
				if(isset($css['tooltipfontfamily']) && $css['tooltipfontfamily'] != '') {
					$styles .= "\n#map_canvas_".$id." .google-visualization-tooltip * { font-family:'".$css['tooltipfontfamily']."' !important; }";
				}

				if(isset($css['tooltipfontsize']) && $css['tooltipfontsize'] != '') {
					$styles .= '#map_canvas_'.$id.' .google-visualization-tooltip * { font-size:'.$css['tooltipfontsize'].' !important; }';
				}

				if(isset($css['tooltipbg']) && $css['tooltipbg'] != '') {
					$styles .= '#map_canvas_'.$id.' .google-visualization-tooltip { background:'.$css['tooltipbg'].'; }';
				}

				if(isset($css['tooltipminwidth']) && $css['tooltipminwidth'] != '') {
					$styles .= '#map_canvas_'.$id.' .google-visualization-tooltip { min-width:'.$css['tooltipminwidth'].'; }';
				}

				if(isset($css['tooltiphidetitle']) && $css['tooltiphidetitle'] != '' && $css['tooltiphidetitle'] == 1) {
					$styles .= '#map_canvas_'.$id.' .google-visualization-tooltip-item:first-child { display:none;}';
				}

				if(isset($css['tooltipbordercolor']) && $css['tooltipbordercolor'] != '') {
					$styles .= '#map_canvas_'.$id.' .google-visualization-tooltip { border-color:'.$css['tooltipbordercolor'].'; }';
				}

				if(isset($css['tooltipborderwidth']) && $css['tooltipborderwidth'] != '') {
					$styles .= '#map_canvas_'.$id.' .google-visualization-tooltip { border-width:'.$css['tooltipborderwidth'].'; }';
				}

				if(isset($css['fontawesomeapply']) && $css['fontawesomeapply'] != '') {
					$styles .= '#map_canvas_'.$id.' text { font-family:fontAwesome; }';
				}

				if(isset($css['fontawesomeinclude']) && $css['fontawesomeinclude'] != '') {
					i_world_map_include_fontawesome();
				}


				$styles .= '</style>';

		}

		

	}


	/* Check if any of the entries is a group */
	if(strpos($input, 'group:') !== false ) {

		//if there's a group, we replicate the group entries
		$entries = explode(";", $input);

		$entries = array_slice($entries, 0, -1);

		$input = '';

		foreach ($entries as $entry) {
			
			if(strpos($entry, 'group:') !== false ) {

				$regentry = explode(',',$entry);
				$regioncode = $regentry[0];

				$regioncode = str_replace('group:', '', $regioncode);

				$newcodes = explode('|',$regioncode);

				foreach ($newcodes as $new) {
					$entry = $new.','.$regentry[1].','.$regentry[2].','.$regentry[3].','.$regentry[4];
					$input .= $entry.';';
				}

			} else {
				$input .= $entry.';';
			}

			
		}
	}

	/* Conditional tag to populate the map automatically, if using taxonomies as source */
	if($input=='taxonomy_count') {

		global $post;

		$input = '';

		$tax = 'tshowcase-categories';

		$args = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all');
		$countries = wp_get_post_terms($post->ID, $tax, $args);
  
		foreach($countries as $country) { 

			//model: Region Code, Tooltip Title, Tooltip info, Action Value (URL), Color Code;

			$input .= $country->slug.','.$country->name.',,'.get_term_link( $country->slug, $tax ).','.$options['default_act_color'].';';

			}
	}


	/* Conditional tag to populate the map automatically, if using categories as source */
	if($input=='categories_count') {

		$input = '';

		$args = array(
		  'orderby' => 'name',
		  'order' => 'ASC',
		  'hide_empty' => 0
		  );

		$categories = get_categories($args);
  
		foreach($categories as $category) { 

			//model: Region Code, Tooltip Title, Tooltip info, Action Value (URL), Color Code;

			$input .= $category->name.','.$category->name.','.$category->description.$category->count.','.get_category_link( $category->term_id ).','.$options['default_act_color'].';';

			}
	}

	/* Conditional tag to populate the map automatically, if using CUSTOM POST TYPE as source */
	if($input=='custom_post_type') {

		//EDIT HERE

		$cpt_id = 'iwm';
		$region_code_meta = 'wpcf-regioncode'; //custom meta field name to fetch region code;
		$tooltip_meta = 'wpcf-tooltip'; //custom meta field name to fetch tooltip info;
		$color_meta = 'wpcf-color'; //cutom meta field name to fetch color codes	

		//AVOID EDIT BELOW	

		$input = '';

		$args = array(
			'post_type' => $cpt_id,
			);

		$cpt = new WP_Query( $args );

		// The Loop
		if ( $cpt->have_posts() ) {
			
				while ( $cpt->have_posts() ) : $cpt->the_post();

					$regioncode = get_post_meta( get_the_ID(), $region_code_meta, true );
					$tooltiptitle = get_the_title();
					$tooltipinfo = get_post_meta( get_the_ID(), $tooltip_meta, true );;
					$actionvalue = do_shortcode(get_the_content());
					$colorcode = get_post_meta( get_the_ID(), $color_meta, true );

					//to clean the content from commas (,) and semi-colons (;)
					$oreplace = array(",", ";");
					$ofinal   = array("&#44", "&#59");
					$actionvalue = str_replace($oreplace, $ofinal, $actionvalue);

					//model: Region Code, Tooltip Title, Tooltip info, Action Value (URL), Color Code;
					$input .= $regioncode.','.$tooltiptitle.','.$tooltipinfo.','.$actionvalue.','.$colorcode.';';
					
				endwhile; 

		}

		/* Restore original Post Data */
		wp_reset_postdata();
  
	}

	//if we're using cornerstone, we add a different class for the preview
	if(isset($atts['cornerstone'])) {

		$placeholder = __("<div class='cs_iwm_placeholder'><img width='32px' alt='".$mapdata['name']." Placeholder' title='".$mapdata['name']."' src='".plugins_url('imgs/placeholder.png', __FILE__)."'><br>".$mapdata['name']."</div>",'iw_maps');

	}

	else {

		$placeholder = __("<div class='iwm_placeholder'><img width='32px' alt='".$mapdata['name']." Placeholder' title='".$mapdata['name']."' src='".plugins_url('imgs/placeholder.png', __FILE__)."'><br>".$mapdata['name']."</div>",'iw_maps');

	}


	//$input = str_replace(array("\r\n", "\r", "\n"), ' ', addslashes($mapdata['places']));

	//add custom css function
	add_action('wp_footer', 'i_world_map_custom_css_js',99);
	
		 
	

	if($options == false) {
		i_world_map_defaults();
		$options = get_option('i-world-map-settings');
	}

	$usehtml = (array_key_exists( 'default_usehtml', $options) ? $options['default_usehtml'] : "0" );


	$apiv = "1";

	if($usehtml==1){
		$apiv = $apiver;
	}
	
	if($mapdata['use_defaults'] == 1) {
	
		$bg_color = $options['default_bg_color'];
		$border_color = $options['default_border_color'];
		$border_stroke = $options['default_border_stroke'];
		$ina_color = $options['default_ina_color'];
		$act_color = $options['default_act_color'];
		$marker_size = $options['default_marker_size'];
		$width = $options['default_width'];
		$height = $options['default_height'];
		$aspect_ratio = $options['default_aspect_ratio'];
				
		
	} else {
		$bg_color = $mapdata['bg_color'];
		$border_color = $mapdata['border_color'];
		$border_stroke = $mapdata['border_stroke'];
		$ina_color = $mapdata['ina_color'];
		$act_color = $mapdata['act_color'];
		$marker_size = $mapdata['marker_size'];
		$width = $mapdata['width'];
		$height = $mapdata['height'];
		$aspect_ratio = $mapdata['aspect_ratio'];
		
		
	}

	if($overlay=='data') {
		$bg_color = 'transparent';
		$ina_color = 'transparent';
	}
	
	
	if(isset($options['default_responsive']) && $options['default_responsive']==1) {
		$width = "";
		$height = "";
		imap_include_responsive_js();
	}
	
	
		$interactive = $mapdata['interactive'];
		$tooltipt = $mapdata['showtooltip'];
	
		$diplaym = $mapdata['display_mode'];
		
		if($interactive == 0 || $overlay=='data') {
			$interactive = "false";
		}
		else {
			$interactive = "true";
		}
		
		if($tooltipt == 0) {
			$tooltipt = "none";
		}
		else if($tooltipt == 2) {
			$tooltipt = "selection";
		}
		else {
			$tooltipt = "focus";
		}


		

		
				
		$display_mode = $diplaym;
		$areashow = explode(",", $mapdata['region']);
		$region = $areashow[0]; 
		$resolution = $areashow[1];
		$map_action = $mapdata['map_action'];
		$custom_action = $mapdata['custom_action'];

		$projection = (array_key_exists( 'map_projection', $options) ? $options['map_projection'] : "mercator" );
		  
		$beforediv="";  
		$afterdiv ="";



		if(isset($atts['extras']) && $atts['extras'] == 'dropdown' && ($overlay=='base' || $overlay==false)) {
			$afterdiv .= iwm_build_i_world_map_dropdown($atts);
		}

		if($map_action != "none" || $map_action!='null' ) { 

 	
		  	if($map_action =='i_map_action_content_below' || $map_action =='i_map_action_content_below_scroll') {
				$afterdiv .="<div id='imap".$id."message'></div>";
				}
			if($map_action =='i_map_action_content_above') {
				$beforediv ="<div id='imap".$id."message'></div>";
				}

			if($map_action == 'i_map_action_colorbox_content' || $map_action == 'i_map_action_colorbox_iframe' || $map_action == 'i_map_action_colorbox_inline' || $map_action == 'i_map_action_colorbox_image') {
				iwm_enqueue_colorbox();
			}

		}

	$html = '';

	if($map_action == "i_map_action_custom"){

		$old_value = "ivalue_".$id."[selectedRegion]";
		$new_action = str_replace($old_value, "value", $custom_action);

		$html = '<script type="text/javascript">';
		$html .= 'function iwm_custom_action_'.$id.'(value) {';
		$html .= $new_action;
		$html .= '}</script>';
		
	}

	$controls = '';
	$usecontrols = false;
	$controlsposition = '';
	if(isset($atts['controls'])) {

		$ids = $id;
		i_world_map_include_panzoom();
		$usecontrols = true;

		$controlsposition = (isset($atts['controls'])) && ($atts['controls'] == 'top-left' || $atts['controls'] == 'top-right' || $atts['controls'] == 'bottom-left' || $atts['controls'] == 'bottom-right') || $atts['controls'] == 'center-left'|| $atts['controls'] == 'center-right'  ? $atts['controls'] : 'top-left';

		
	}
	

	//extra parameters
	$overlayid = isset($atts['overlay']) ? $atts['overlay'] : false;
	$magglass = isset($atts['mag-glass']) ? $atts['mag-glass'] : true;
	if($magglass === 'off' || $magglass === 'false') { $magglass=false; }
	$zfactor = isset($atts['mag-glass-zoom']) ? $atts['mag-glass-zoom'] : '5';
	$widthselector = isset($atts['width-selector']) ? $atts['width-selector'] : false;

	$apikey = isset($options['api_key']) ? $options['api_key'] : '';
	
   $new_iwm_array = array(
   						 "apiversion" => $apiv,
   						 "usehtml" => $usehtml,
   						 "id" => $id,
   						 "bgcolor"=>$bg_color,
						 "stroke"=>$border_stroke,
						 "bordercolor"=>$border_color,
						 "incolor"=>$ina_color,
						 "actcolor"=>$act_color,
						 "width"=>$width,
						 "height"=>$height,
						 "aspratio"=>$aspect_ratio,
						 "interactive"=>$interactive,
						 "tooltip"=>$tooltipt,
						 "region"=>$region,
						 "resolution"=>$resolution,
						 "markersize"=>$marker_size,
						 "displaymode"=>$display_mode,
						 "placestxt"=>$input,
						 "action"=>$map_action,
						 "custom_action"=>$custom_action,
						 "projection" => $projection,
						 "controls" => $usecontrols,
						 "controls_position" =>$controlsposition,
						 "overlay" => $overlayid,
						 "magglass" => $magglass,
						 "magglasszfactor" => $zfactor,
						 "widthselector" => $widthselector,
						 "apikey" => $apikey
						);


	array_push($iwmparam_array, $new_iwm_array);
		

	//if the theme loads content via ajax, we need to output some js variables in a different way	
	$ajax_enabled = false; 
	if(isset($options['ajax_enabled']) && $options['ajax_enabled']==1) {
		$ajax_enabled = true;	
	} 

	//load other scripts
	i_world_map_scripts($iwmparam_array);


	$style = '';
	if($overlay=='base') {
		$style .= "style='pointer-events:visible;' ";
	}

	$class = '';
	$style .= "class='iwm_map_canvas";
	if($overlay=='data') {
		$style .= " iwm_data";
	}
	//closing class=""
	$style .="'";

	//if the size height is overrided with css, we need extra class
	if($overrideh) {
		$beforediv .= '<div id="iwm_'.$id.'">';
		$afterdiv = '</div>'.$afterdiv;
	}

	


	if($type == "shortcode") {

		return $html.$styles.$beforediv."<div ".$style.">".$controls."<div id='map_canvas_".$id."' class='i_world_map ' ".$style.">".$placeholder."</div></div>".$afterdiv;
	} 
	if($type == "php") {
		echo $html.$styles.$beforediv."<div ".$style.">".$controls."<div id='map_canvas_".$id."' class='i_world_map ' ".$style.">".$placeholder."</div></div>".$afterdiv;
	}

	if($type == "nulloutput") {

		return;

	}
}

//ADMIN MENU

// create custom plugin settings menu

add_action('admin_menu', 'i_world_map_create_menu');



function i_world_map_create_menu() {

	//you can change capibility here
	//$capability = 'manage_options';
	$capability = apply_filters( 'i_world_map_capability', 'manage_options'); 
	
	if (current_user_can($capability)) {
	// Add the top-level admin menu
    $page_title = 'Interactive World Maps';
    $menu_title = 'Interactive Maps';
	
	
    $menu_slug = 'i_world_map_menu';
    $function = 'i_world_map_manage';
    $mainp = add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function,'','61.15');
	
	//sub menu main 
	$sub_menu_title = 'Manage Maps';
    $managep = add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function);
	
	$submenu_page_title = 'Add New';
    $submenu_title = 'Add New Map';
    $submenu_slug = 'iwm_add';
    $submenu_function = 'i_world_map_add_new';
    $addp = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
	
	
	$submenu_page_title = 'Settings';
    $submenu_title = 'Settings';
    $submenu_slug = 'iwm_settings';
    $submenu_function = 'i_world_map_settings_page';
    $defaultp = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);

			
	//call register settings function
	add_action( 'admin_init', 'register_i_world_map_settings' );
	add_action($addp, 'i_world_map_includes_add');
	add_action($defaultp, 'i_world_map_includes_def');
	}
}

//RESPONSIVE TESTING FEATURES
function imap_include_responsive_js() {
	wp_deregister_script('imapresponsive');
	wp_register_script( 'imapresponsive', plugins_url( 'includes/responsive.js', __FILE__ ),array('jquery','iwmjs'),false,true);
	wp_enqueue_script( 'imapresponsive' );
	
	wp_deregister_style('imapresponsivecss');
	wp_register_style( 'imapresponsivecss', plugins_url( 'includes/responsive.css', __FILE__ ), array(), '1.0.0', 'all');
	wp_enqueue_style( 'imapresponsivecss' );
	
}

function iwm_enqueue_colorbox() {

	wp_deregister_script('iwmcolorbox');
	wp_register_script( 'iwmcolorbox', plugins_url( 'includes/colorbox/jquery.colorbox-min.js', __FILE__ ),array('jquery'),false,true);
	wp_enqueue_script( 'iwmcolorbox' );

	wp_deregister_style('iwmcolorbox');
	wp_register_style( 'iwmcolorbox', plugins_url( 'includes/colorbox/colorbox.css', __FILE__ ), array(), '1.0.0', 'all');
	wp_enqueue_style( 'iwmcolorbox' );

}

//END RESPONSIVE TESTING FEATURES


//fontawesome only css

function i_world_map_include_fontawesome() {
	wp_register_style('i_world_map_fontawesome', plugins_url('includes/font-awesome/css/font-awesome.min.css', __FILE__), array(), '1.0.0', 'all');
    wp_enqueue_style('i_world_map_fontawesome');
}


function i_world_map_include_panzoom() {
		wp_register_script( 'iwmpanzoom', plugins_url( 'includes/jquery.panzoom.min.js' , __FILE__ ),array('jquery'),false,true );	 
		wp_enqueue_script( 'iwmpanzoom' );
}

function i_world_map_includes_def() {

	/*To handle the protocol */
	$protocol = is_ssl() ? 'https' : 'http';
	$gurl = $protocol."://www.google.com/jsapi";

	$loader = $protocol."://www.gstatic.com/charts/loader.js";

    /** Register */
    wp_register_style('i_world_map_css', plugins_url('admin.css', __FILE__), array(), '1.0.0', 'all');
	wp_register_script( 'jsapiloader', $loader,array(),false,false);
	wp_register_script( 'jsapi', $gurl,array('jsapiloader'),false,false);
	wp_register_script( 'jscolor', plugins_url( 'includes/jscolor.js' , __FILE__ ) );
	wp_register_script( 'jsadmin', plugins_url( 'includes/admin.js' , __FILE__ ),array('jquery','jsapi'),false,false );	 
	wp_register_script( 'jssettings', plugins_url( 'includes/settings.js' , __FILE__ ) );
	wp_register_style('i_world_map_fontawesome', plugins_url('includes/font-awesome/css/font-awesome.min.css', __FILE__), array(), '1.0.0', 'all');


	 
    /** Enqueue */
    wp_enqueue_style('i_world_map_css');
    wp_enqueue_style('i_world_map_fontawesome');
    wp_enqueue_script( 'jsapiloader' );
	wp_enqueue_script( 'jsapi' );
	
	wp_enqueue_script( 'jscolor' );
	//wp_enqueue_script( 'jsadmin' );
	wp_enqueue_script( 'jssettings' );
 
   }
   
function i_world_map_includes_add() {

	$protocol = is_ssl() ? 'https' : 'http';
	$gurl = $protocol."://www.google.com/jsapi";
	$loader = $protocol."://www.gstatic.com/charts/loader.js";

    /** Register */

	//wp_register_script( 'iwjsgeo', $protocol.'://maps.google.com/maps/api/js?sensor=false',array(),false,false);
	$options = get_option('i-world-map-settings');
	$key = isset($options['api_key']) ? $options['api_key'] : '';
    wp_register_script( 'iwjsgeo', $protocol.'://maps.googleapis.com/maps/api/js?key='.$key,array(),false,false);

    wp_register_style('i_world_map_css', plugins_url('admin.css', __FILE__), array(), '1.0.0', 'all');
    wp_register_style('i_world_map_styles_css', plugins_url('styles.css', __FILE__), array(), '1.0.0', 'all');
	wp_register_script( 'iwjsapiloader', $loader,array(),false,false);
	wp_register_script( 'iwjsapi', $gurl,array('iwjsapiloader'),false,false);
    wp_register_script( 'iwjscolor', plugins_url( 'includes/jscolor.js' , __FILE__ ) );
	wp_register_script( 'iwjsadmin', plugins_url( 'includes/admin.js' , __FILE__ ) );	 
	wp_register_style('i_world_map_fontawesome', plugins_url('includes/font-awesome/css/font-awesome.min.css', __FILE__), array(), '1.0.0', 'all');
	
	 //hack for tinyMCE
	$settings = array( 'teeny' => false, 'textarea_rows' => 3, 'tinymce' => true, 'media_buttons' => true );            
	wp_editor('iwm_hack', 'iwm_hackeditor' ,$settings);

    /** Enqueue */
	wp_enqueue_script( 'iwjsgeo' );
	wp_enqueue_script('iwjsapiloader');
	wp_enqueue_script('iwjsapi');	
    wp_enqueue_style('i_world_map_css');
    wp_enqueue_style('i_world_map_styles_css');
	wp_enqueue_script('iwjscolor');
	wp_enqueue_script('iwjsadmin');
	wp_enqueue_style('i_world_map_fontawesome');

   }

//To add the code button to the editor
function iwm_tinymce_external_plugins($plugins) {   

    $plugins['code'] = plugins_url( '/includes/tinymce.code.js', __FILE__);
    return $plugins;
}
add_filter('mce_external_plugins', 'iwm_tinymce_external_plugins');



function i_world_map_scripts($iwmparam_array = array()) {

	$protocol = is_ssl() ? 'https' : 'http';
	$options = get_option('i-world-map-settings');
	$key = isset($options['api_key']) ? $options['api_key'] : '';
	$addscript = isset($options['scriptadd']) && $options['scriptadd'] == '1' ? true : false;
    
    if($addscript) {
    	wp_deregister_script( 'iwmjsgeo' );	
	    wp_register_script( 'iwmjsgeo', $protocol.'://maps.googleapis.com/maps/api/js?key='.$key,array(),false,false);
	    wp_enqueue_script( 'iwmjsgeo' ); 
    }

	$gurl = $protocol."://www.google.com/jsapi";
	$loader = $protocol."://www.gstatic.com/charts/loader.js";

	wp_deregister_script( 'jsapiloader' );
    wp_register_script( 'jsapiloader', $loader,array(),false,true);
    wp_enqueue_script( 'jsapiloader' );

    wp_deregister_script( 'jsapifull' );
    wp_register_script( 'jsapifull', $gurl,array('jsapiloader'),false,true);
    wp_enqueue_script( 'jsapifull' );

    wp_deregister_script( 'iwmjs' );
	wp_register_script( 'iwmjs', plugins_url( '/includes/shortcode.js', __FILE__) , array('jsapifull') , '66', true);
	wp_enqueue_script( 'iwmjs' );

	wp_deregister_style( 'iwm_front_css' );
	wp_register_style('iwm_front_css', plugins_url('styles.css', __FILE__), array(), '1.0.0', 'all');
	wp_enqueue_style( 'iwm_front_css' );

	wp_localize_script('iwmjs', 'iwmparam', $iwmparam_array);


}    
 
 

//Manage Maps Screen
function i_world_map_manage() { 
	$alert = "";
	$alertred = "";
	
	if(isset($_GET['action']) && ($_GET['action'] == 'delete')) {
	delete_i_world_map($_GET['map']);
	$alert = "Map Deleted";
	}

	if(isset($_GET['action']) && ($_GET['action'] == 'duplicate')) {
	duplicate_i_world_map($_GET['map']);
	$alert = "Map Duplicated";
	}

	$dbversion = get_option('i_world_map_db_version');
	$iwm_db_version = 5;

	if($dbversion != $iwm_db_version) {
		$alertred = "Seems you might have changed the plugin files. Please desactivate and activate the plugin again to make sure there are no errors.";
	}
	
	
    $iwmaptable = new i_world_map_manage_table();   
    $iwmaptable->prepare_items();
    
    ?>
    <div class="wrap">
        <div id="interactive-world-maps" class="icon32"></div>
         <h2>Manage Maps</h2>
         <?php if($alert!="") {i_world_map_message($alert);} ?> 
         <?php if($alertred!="") {i_world_map_message_red($alertred);} ?> 
       
        <form id="iwm-filter" method="get">
            
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            
            <?php $iwmaptable->display() ?>
        </form>
        
        <br />
        <br />
        <?php $iwm_blog_id = get_current_blog_id(); ?>
        <a href="<?php echo get_admin_url( $iwm_blog_id, 'admin.php?page=iwm_add' ); ?>" class="button-primary">Add New Map</a>
        <br />
        <br />
        
    </div>
    <?php
}

//Add new map and edit screen
function i_world_map_add_new() { 

	if(isset($_POST['action'])) {
		
		global $wpdb;

		$name = stripslashes ($_POST['name']);
		$description = stripslashes ($_POST['description']);
		$use_defaults = stripslashes ($_POST['use_defaults']);
		$border_color = stripslashes ($_POST['border_color']);
		$border_stroke = stripslashes ($_POST['border_stroke']);
		$bg_color = stripslashes ($_POST['bg_color']);
		$ina_color = stripslashes ($_POST['ina_color']);
		$act_color = stripslashes ($_POST['act_color']);
		$marker_size = stripslashes ($_POST['marker_size']);
		$width = stripslashes ($_POST['width']);
		$height = stripslashes ($_POST['height']);
		
		$aspect_ratio = 0;
		$interactive = 0;
		$tooltipt = 0;
		
		if(isset($_POST['aspect_ratio'])) { $aspect_ratio = stripslashes ($_POST['aspect_ratio']); } 
		if(isset($_POST['interactive'])) { $interactive = stripslashes ($_POST['interactive']); } 
		if(isset($_POST['tooltipt'])) { $tooltipt = stripslashes ($_POST['tooltipt']); } 
		
		$region = stripslashes ($_POST['region']);
		$display_mode = stripslashes ($_POST['display_mode']);
		$places = stripslashes ($_POST['places']);
		$map_action = stripslashes ($_POST['map_action']);
		$custom_action = stripslashes ($_POST['custom_action']);

		$image = $_POST['mapimage'];
		$css = $_POST['customcss'];
		
		global $table_name_imap;
		
		if ($_POST['action'] == 'addmap') {
		
			if($wpdb->insert( 
			$table_name_imap, 
			array( 
				'name' => $name, 
				'description' => $description, 
				'use_defaults' => $use_defaults,
				'bg_color' => $bg_color,
				'border_color' => $border_color,
				'border_stroke' => $border_stroke,
				'ina_color' => $ina_color,
				'act_color' => $act_color,
				'marker_size' => $marker_size,
				'width' => $width,
				'height' => $height,
				'aspect_ratio' => $aspect_ratio,
				'interactive' => $interactive,
				'showtooltip' => $tooltipt,
				'region' => $region,
				'display_mode' => $display_mode,
				'custom_action' => $custom_action,
				'map_action' => $map_action,
				'places' => $places,
				'image' => $image,
				'custom_css' => $css
			)) == true) {
				$alert = "New Map Added";
			} else {
			$alert = "ERROR: Map NOT Added";	
			}
					
			i_world_map_build_form('edit-map',$wpdb->insert_id,$alert);			
			}
		
		if($_POST['action'] == 'editmap') {
		
			$id = $_POST['id'];
					
			if($wpdb->update( 
			$table_name_imap, 
			array( 
				'name' => $name, 
				'description' => $description, 
				'use_defaults' => $use_defaults,
				'bg_color' => $bg_color,
				'border_color' => $border_color,
				'border_stroke' => $border_stroke,
				'ina_color' => $ina_color,
				'act_color' => $act_color,
				'marker_size' => $marker_size,
				'width' => $width,
				'height' => $height,
				'aspect_ratio' => $aspect_ratio,
				'interactive' => $interactive,
				'showtooltip' => $tooltipt,
				'region' => $region,
				'display_mode' => $display_mode,
				'map_action' => $map_action,
				'custom_action' => $custom_action,
				'places' => $places,
				'image' => $image,
				'custom_css' => $css
			),array( 'id' => $id )) == true) {
			$alert = "Map Updated";
			} else {
			$alert = "Map NOT Updated";	
			}
				
		}
	}
	//special if condition to run after the new map is created
	if(isset($_POST['action']) && ($_POST['action'] == 'editmap') && (!isset($_GET['action'])) ) {
		$id = $_POST['id'];
		i_world_map_build_form('edit-map',$id,$alert);
	}
	

	if(isset($_GET['action']) && ($_GET['action'] == 'edit')) {
   		if(!isset($_POST)) { $alert = ""; } 
		if(!isset($alert)) { $alert = ""; } 
		if(!isset($id)) { $id = $_GET['map']; }
		
		i_world_map_build_form('edit-map',$id,$alert);
  		
			
	 } 
	 
	 if(!isset($_GET['action']) && (!isset($_POST['action']))) {
	 	if(!isset($alert)) { $alert = ""; } 
		i_world_map_build_form('post-map',0,$alert);
 }
}

function i_world_map_build_form($type,$id,$alert) { 
$options = get_option('i-world-map-settings');
$apikey = isset($options['api_key']) && $options['api_key']!='' ? true : false;
global $apiver;

$projection = (array_key_exists( 'map_projection', $options) ? $options['map_projection'] : "mercator" );

if($type == 'post-map') {
	
	$message = "Fill out the form and follow the instructions to create your Interactive Map";
	$formname = "addimap";
	settings_fields( 'i-world-map-plugin-settings' );
	$title = " Add New Interactive Map";
	
	$name = "";
	$description = "";
	$use_defaults = 1;
	$border_color = $options['default_border_color'];
	$border_stroke =  $options['default_border_stroke'];
	$bg_color = $options['default_bg_color'];
	$ina_color = $options['default_ina_color'];
	$act_color = $options['default_act_color'];
	$marker_size = $options['default_marker_size'];
	$width = $options['default_width'];
	$height = $options['default_height'];
	$aspect_ratio = $options['default_aspect_ratio'];
	$interactive = $options['default_interactive'];
	$tooltipt = $options['default_showtooltip'];
	$region = $options['default_region'];
	$display_mode = $options['default_display_mode'];
	$places = "";
	$customcss = "";
	$map_action = 'none';
	$custom_action = '';
	$submit_action = "addmap";	
	$submit_bt_value = "CREATE MAP";
	
}
if($type == 'edit-map') {	
	  
  global $wpdb;
  global $table_name_imap;
  $mapdata = $wpdb->get_row("SELECT * FROM $table_name_imap WHERE id = $id", ARRAY_A);  	
	
	$title = "Edit Map";
	
	$message = "To add this map to your website, just use the shortcode <span id='shc'>[show-map id='".$id."']</span> on your posts, pages or widgets, or add <span id='shc'>&lt;?php build_i_world_map(".$id."); ?&gt;</span> to your template.";
	
	if ( defined( 'WPB_VC_VERSION' ) ) {
		$message .= "<p> You can also use the <img src='".plugins_url('interactive-world-maps/imgs/visual_composer.png')."'> VISUAL COMPOSER to add this map to your page, by choosing the option 'Add Element > Interactive Map'.</p>";
	}
	
	$formname = "addimap";
	$name = $mapdata['name'];
	$description = $mapdata['description'];
	$use_defaults = $mapdata['use_defaults'];
	$border_color = $mapdata['border_color'];
	$border_stroke =  $mapdata['border_stroke'];
	$bg_color = $mapdata['bg_color'];
	$ina_color = $mapdata['ina_color'];
	$act_color = $mapdata['act_color'];
	$marker_size = $mapdata['marker_size'];
	$width = $mapdata['width'];
	$height = $mapdata['height'];
	$aspect_ratio = $mapdata['aspect_ratio'];
	$interactive = $mapdata['interactive'];;
	$tooltipt = $mapdata['showtooltip'];
	$region = $mapdata['region'];
	$display_mode = $mapdata['display_mode'];
	$places = $mapdata['places'];
	$customcss = $mapdata['custom_css'];
	$map_action = $mapdata['map_action'];
	$custom_action = $mapdata['custom_action'];
	$submit_action = "editmap";	
	$submit_bt_value = "UPDATE MAP";
	
}



?>
<div id="iwm-visit"><i class="fa fa-info-circle"></i> Visit the <a href="http://cmoreira.net/interactive-world-maps-demo/" target="_blank">Plugin Demo Site</a> for more information and tips on how to use it.</div>

<div class="wrap">
<div id="interactive-world-maps" class="icon32"></div>
<h2><?php echo $title; ?></h2>

<?php if ($alert!="") { ?>
<div id="message" class="updated"><?php echo $alert; ?></div>
<?php } ?>

<div id="iwm-message-intro"><?php echo $message; ?></div>

 
 
<form method="post" action="" id="<?php echo $formname; ?>" name="<?php echo $formname; ?>">
    
    <table width="100%" border="0" cellspacing="5" cellpadding="5">
      <tr>
        <td width="25%" style="min-width:180px;" valign="top"><h3>Details
        </h3>
          <table width="100%" border="0" cellspacing="2" cellpadding="2" class="stuffbox" id="name-table">
          <tr valign="top">
            <td>Name <br><input type="text" name="name" value="<?php echo $name; ?>" /></td>
          </tr>
          <tr valign="top">
            
            <td>Description<br>
            	
            	<textarea name="description" cols="20" rows="3"><?php echo $description; ?></textarea></td>
          </tr>
          <?php $editor = isset($options['default_editor']) ? '1' : '0'; ?>
          <input type="hidden" id="editor" name="editor" value="<?php echo $editor; ?>" />
         
        </table>
          <h3>Visual Settings </h3>
          <table width="100%" border="0" cellpadding="2" cellspacing="2" class="stuffbox" id="add-table">
             <tr valign="top">
           
            <td colspan="2"><input name="use_defaults" id="use_defaults" type="radio" value="1" <?php if($use_defaults==1) { ?>checked="checked"<?php } ?> onclick="hidecustomsettings();" />
              Default
                <input name="use_defaults" id="use_defaults" type="radio" value="0" <?php if($use_defaults==0) { ?>checked="checked"<?php } ?>onclick="showcustomsettings();"/>
              Custom</td>
          </tr></table>
          <div id="default-settings-table-add" class="stuffbox" style="display:none;">
          <table>
            
              <td>Background Color <br> <input type="text" name="bg_color" class="color {hash:true, adjust:false}" value="<?php echo $bg_color; ?>" onchange="drawVisualization();" /></td>
            </tr>
            <tr>
            	<td class="iwmsmall"><i class="fa fa-info-circle"></i> <?php echo __('Tip: In color fields you can also use the word "transparent" instead of a color code.'); ?></td>
            </tr>
            <tr valign="top">  
              <td>Border Color<br><input type="text" name="border_color" class="color {hash:true, adjust:false}" value="<?php echo $border_color; ?>" onchange="drawVisualization();" /></td>
            </tr>
            <tr valign="top">
              <td>Border Width (px)<br><input type="text" name="border_stroke" value="<?php echo $border_stroke; ?>" onchange="drawVisualization();" /></td>
            </tr>
            <tr valign="top">
              <td>Inactive Region Color<br><input type="text" name="ina_color" class="color {hash:true, adjust:false}" value="<?php echo $ina_color; ?>"onchange="drawVisualization();" /><input type="hidden" name="act_color" class="color {hash:true, adjust:false}" value="<?php echo $act_color; ?>"onchange="drawVisualization();" /></td>
            </tr>
  
            <tr valign="top" >
            
              <td>Marker Size<br><input type="text" name="marker_size" value="<?php echo $marker_size; ?>"onchange="drawVisualization();" /></td>
            </tr>
            
             <?php if(isset($options['default_responsive']) && $options['default_responsive']==1) { ?>
             
             <tr valign="top" >
              
              <td><span class="howto">The settings bellow will be ignored, since the Responsive Mode is ON.</span>

              	<input type="hidden" name="responsivemode" value="on">

              </td>
            

            </tr>
             
             <?php } else { 

             	?>

             	<input type="hidden" name="responsivemode" value="off">

             	<?php

             } ?>

             <input type="hidden" name="mapprojection" value="<?php echo $projection; ?>">
            
            <tr valign="top" >
              
              <td>Width (px)<br><input type="text" name="width" value="<?php echo $width; ?>" onchange="drawVisualization();" /></td>
            </tr>
            <tr valign="top" >
             
              <td>Height (px)<br><input type="text" name="height" value="<?php echo $height; ?>" onchange="drawVisualization();" /></td>
            </tr>
            <tr valign="top" >
             
              <td><input name="aspect_ratio"  id="aspratio" type="checkbox" value="1" <?php if($aspect_ratio==1) { ?>checked <?php } ?> onchange="drawVisualization();" /> Keep Aspect Ratio</td>
            </tr>

            
        </table>
        
        
        </div>
       
          <p class="submit">
      <input type="submit" class="button-primary" value="<?php echo $submit_bt_value; ?>" />
  </p>
        
        </td>
        <td width="75%" valign="top"><h3>Map Settings</h3>
          <table width="100%" border="0" cellspacing="5" cellpadding="5" class="stuffbox" id="add-table">
            <tr valign="top" >
              <td><strong>Region to Display</strong>
              <span class="howto">Choose the region you want the map to focus on</span>
          		</td>
              <td><strong>Display Mode</strong>
              	<span class="howto">Choose what type of interactive elements you will apply</span>
              </td>
              <td><strong>Active Region Action </strong>
              	<span class="howto">What to do when user clicks active region/marker</span>
              </td>
            </tr>
            <tr valign="top" >
              <td>
                <?php  i_world_map_build_region_select_options('region',$region,'isolinkcheck()'); ?>                <br />

             </td>
            
              <td>
                <select name="display_mode" onchange="isolinkcheck();">
                  <option value="regions"  <?php if($display_mode=='regions') { ?>selected="selected" <?php } ?> >Regions</option>
                  <option value="markers"  <?php if($display_mode=='markers') { ?>selected="selected" <?php } ?> >Round Markers (Text Code)</option>
                  <option value="markers02"  <?php if($display_mode=='markers02') { ?>selected="selected" <?php } ?>>Round Markers (Coordinates)</option>
                  <option value="text"  <?php if($display_mode=='text') { ?>selected="selected" <?php } ?> >Text Labels</option>
                  <option value="text02"  <?php if($display_mode=='text02') { ?>selected="selected" <?php } ?>>Text Labels (Coordinates)</option>
              </select></td>
             
              <td>
				<?php i_world_map_build_actions_select_options('map_action',$map_action,'isolinkcheck()'); ?>
				
			</td>
            </tr>

            <tr> <td colspan="3"><input name="interactive" type="checkbox"  id="interactive" onchange="drawVisualization();" value="1" <?php if($interactive==1) { ?>checked <?php } ?> /> Enable Region Hover effect
                
                <br />
                 
Tooltip <select name="tooltipt"  id="tooltipt" onchange="drawVisualization();">
	<option value="1" <?php if($tooltipt==1) { ?>selected="selected" <?php } ?>>Display on Hover</option>
	<option value="2" <?php if($tooltipt==2) { ?>selected="selected" <?php } ?>>Display on Click</option>
	<option value="0" <?php if($tooltipt==0) { ?>selected="selected" <?php } ?>>None</option>
</select><span class="iwmsmall"> (In Regions Mode 'Region Hover Effect' must be enabled for tooltip to work)</span>

 <?php if(!isset($options['default_usehtml'])) { ?>
 	<br />
	<span class="iwmsmall"> If you plan to use HTML code in your tooltips, you should enable the HTML Tooltips in the settings</span>

<?php	

		}

?>

</td> </tr> 

          </table>


          
          <span id="iso-code-msg"></span>

          <?php 

          $key = isset($options['api_key']) ? $options['api_key'] : '';
          if($key=='') {
          	?>

          	<div id="iwm-api-key-msg"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Some maps might need a <a href="http://cmoreira.net/interactive-world-maps-demo/advanced-tips/generate-api-key/" targer="_blank">Google Geocoding API Key</a>. If your map is not displaying, try adding an API Key in the settings page.</div>

          <?php
          }
          ?>
         
          <div class="stuffbox" id="custom-action">
          
          
          
          
          <table>
            <tr><td><strong>Insert Custom Javascript Action Here</strong><br />
            <textarea name="custom_action" cols="50" rows="4"><?php echo stripcslashes ( $custom_action); ?></textarea></td><td><span class="iwmsmall">You can use Javascript with the variable 'value' or the array ivalue_<?php if(isset($_GET['map'])) {echo $_GET['map']; } else { echo "ID"; }; ?>[selectedRegion] where variable selectedRegion is the code of the region clicked, and ivalue_<?php if(isset($_GET['map'])) {echo $_GET['map']; } else { echo "ID"; }; ?>[selectedRegion] (and <i>value</i>) correspond to the value inserted in the "Action Value" field. Example: alert(ivalue_<?php if(isset( $_GET['map'])) {echo $_GET['map'];} else { echo "ID"; }; ?>[selectedRegion]); OR alert(value);// will display a custom alert message.</span></td></tr></table></div>
            
             <div id="latlondiv">
          <table width="100%" border="0" cellspacing="5" cellpadding="5" class="latlon">
            <tr>
              <td>

              	<?php 
              	
              	
              	if($apikey) {

              		echo "	<strong>Use the form below to help you get the coordinates values</strong>";

              	} else {

              		echo "	<strong style='color:red;'>Add a valid Google Geocoding API Key in the settings page to use the converter below</strong>";
              	}

              	?>
              

              	<br /><i class="fa fa-globe"></i> Convert Address into Lat/Lon:
                <label for="mapsearch">
                  <input type="text" name="mapsearch" id="mapsearch">
                  <input type="button" class="button-secondary" name="convert" id="convert" value="Convert" onClick="getAddress()">
                </label> <span id="latlonvalues"></span>


            </td>
              
            </tr>
          </table>
          </div>
            
            
          <h3>Interactive Regions </h3>

          
<br />


 <a class="activeb" id="shsimple" onclick="showsimple()" >Simple</a> <a class="inactiveb" id="shadvanced" onclick="showadvanced()" >Advanced</a> 
			<div id="simple-table">
          <table width="100%" class="stuffbox" id="add-table">
          <tr valign="top">
            <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td style="width:10%;">Region Code: </td>
                <td style="width:20%;"> Title: </td>
                <td style="width:20%;">Tooltip: </td>
                <td style="width:25%;">Action Value: </td>
                <td style="width:10%;"> Color: </td>
                <td style="width:5%;" valign="baseline"></p></td>
              </tr>
              <tr>
                <td valign="top"><input name="cd" type="text" id="cd" /><br />
 <span class="iwmsmall">Follow the suggestions <br />above.<br />
 MANDATORY
 </span></td>
                <td valign="top"><input name="c" type="text" id="c" /><br />
 <span class="iwmsmall">It will be the first line <br />of the tooltip.</span></td>
                <td valign="top">

                <input name="t" type="text" id="t" /> 
                	<?php 
                	//To add tinyMCE editor
                	/*
                	$settings = array(
                		'quicktags' => array('buttons' => 'em,strong,link',),
                		'teeny' => true,
					    'tinymce' => array(
					        'width' => 30
					    ),
					    'textarea_rows' => 3,
					);

                	wp_editor( '', 't', $settings);

                	*/ ?>

                	<br />
 <span class="iwmsmall">It will be the second line <br />of the tooltip.</span></td>
                <td valign="top"><input name="u" type="text" id="u" size="20" />
                  <br />
                  <span class="iwmsmall">Paramater for the action. </span><br />
                  <span class="iwmsmall" id="actionvaluetip">Ex. Url for Open Url Action.</span></td>
                <td valign="top"><input name="cl" type="text" id="cl" class="color {hash:true, adjust:false}" value="<?php echo $act_color; ?>"  /></td>
                <td valign="top"><input type="button" class="button-secondary" title="Add New Entry" value="Add" onclick="addPlaceToTable();" /></td>
              </tr>
              <tr><td colspan="6" style="font-size:0.9em; height:10px; text-align:right;">Render HTML in Data Table? <input name="rendertags" id="rendertags" type="checkbox" value="1" onchange="iwmdataToTable()" /></td></tr>
                     
            </table>
            
            <div id="htmlplacetable"></div>
            
            </td>
          </tr>
         
          <input name="action" type="hidden" value="<?php echo $submit_action; ?>" />
          <?php if($type == 'edit-map') { ?>
          <input name="id" type="hidden" value="<?php echo $id; ?>" />
		  <?php  } ?>
        </table>
        </div>
        <div id="advanced-table">
          <table width="100%" border="0" cellspacing="5" cellpadding="5" id="add-table-advanced">
            <tr>
              <td><strong>Advanced Data Editor</strong><br />

             <span class="iwmsmall"> Here you can add or edit the CSV (comma-separated values) data that will be parsed to build the map. <br />
It should follow this format:<br />
Region Code, Tooltip Title, Tooltip Text, Action Value, HTML Color Value;<br />
It should not use quotes. Example:<br />
US, USA, Click to visit the White House Website, http://www.whitehouse.gov/,#6699CC;<br />
PT, Portugal, Click to visit Portugal's Government Website, http://www.portugal.gov.pt/,#660000;</span>  </td>
            </tr>
            <tr>
              <td><textarea name="places" id="places" onchange="iwmdataToTable();"><?php echo htmlspecialchars ($places); ?></textarea><br />
                <input type="button" class="button-secondary" value="Preview" onclick="iwmdataToTable();" /></td>
            </tr>
          </table>
          </div>

<h3>Preview </h3>

	<span class="iwmsmall"> <i class="fa fa-file-code-o"></i> The 'Active Region Action' will not work on this preview. 
		When an active region is clicked an alert message with the value inserted will display for debugging, or no alert, if no value exists. </span>

		 <?php 
		if(isset($options['default_usehtml']) && $options['default_usehtml']==1) { ?>
		<br>
		<span class="iwmsmall"><i class="fa fa-comment"></i> The HTML tooltip might look different on your site since it can inherit CSS rules from your theme. <br> You can create your own CSS rules to target the tooltip using the class '.google-visualization-tooltip'</span>
		<?php } ?>



		</div>





        			<div id="iwm-wrap-preview" > 
	        			<div id="visualization-wrap-responsive" >
	        				<div id="visualization"></div>
        				</div>
        			</div>


 		

        			
        	
        	<div>
	
	

         <?php 


         	//Code to break down custom css json
         	$cssarray = json_decode (stripslashes ($customcss ), true);

         	?>

         <h3> Custom CSS Generator (Beta) </h3> 

         <span class="iwmsmall"><strong>The options below are not supported by the Google Geochart API (which the plugin uses to generate the maps), so using these CSS techniques is an alternative unsuported solution that might not work as expected and has limitations. Use at your own risk.</strong> These customizations will not reflect on the image preview of the map. </span>
 

			

			<div id="iwmexpandcss"><a onclick="expandcustomcss()"><i class="fa fa-chevron-circle-right fa-lg"></i></i> Expand Custom CSS Options Box</a></div>

          <div class="stuffbox" id="iwm-custom-css">

          	<div>

	          <h4><i class="fa fa-square"></i> Change Crop / Zoom Effect </h4> 

	          <div id="iwm-control-box" class="stuffbox">
				Zoom <a onclick="iwmcsscontrol('widthplus')"><i class="fa fa-search-plus fa-2x"></i></a>
				<a onclick="iwmcsscontrol('widthminus')"><i class="fa fa-search-minus fa-2x"></i></a>
				Move <a onclick="iwmcsscontrol('down')"><i class="fa fa-arrow-circle-down fa-2x"></i></a>
				 <a onclick="iwmcsscontrol('up')"><i class="fa fa-arrow-circle-up fa-2x"></i></a>
				<a onclick="iwmcsscontrol('left')"><i class="fa fa-arrow-circle-left fa-2x"></i></a>
				<a onclick="iwmcsscontrol('right')"><i class="fa fa-arrow-circle-right fa-2x"></i></a>
				Height <a onclick="iwmcsscontrol('verticalplus')"><i class="fa fa-long-arrow-down fa-2x"></i></a>
				<a onclick="iwmcsscontrol('verticalminus')"><i class="fa fa-long-arrow-up fa-2x"></i></a>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Reset Values <a onClick="clearCropValues()"><i class="fa fa-times-circle fa-2x"></i></a>
				
			</div>

	          <span class="iwmsmall"><strong>The controls above will influence these values</strong>. Changing the following values will allow you change the crop of the map or create a zoom effect by hidding uncessary parts of the map with the overflow:hidden; rule. One of the biggest limitations of this hack is that it will also hide tooltips, if they display on the overflow area.</span>
	          <br> <br>
	          <table>
	          	<tr>
		         	<td class="iwm_stronger">Width/Height overflow: </td>
		         	<td><input type="number" name="iwm_size" size="10" value="<?php if (isset($cssarray['iwm_size'])) { echo $cssarray['iwm_size']; }  ?>" onchange="redrawcrop()"  <?php if(!isset($options['default_responsive'])) { ?> disabled <?php } ?>  > % </td>
				 	<td><span class="iwmsmall"> <?php if(!isset($options['default_responsive'])) { ?> DISABLED. Only works when responsive mode is enabled. <?php }  else { ?>100% is the default. Exciding size will be hidden, so bigger values will allow you do concentrate on different parts of the map.<?php } ?></span></td>
				 </tr><tr>
		         	<td class="iwm_stronger">Viewport Height: </td>
		         	<td><input type="number" step="any" name="iwm_hsize" size="10" value="<?php echo $cssarray['iwm_hsize'] ?>" onchange="redrawcrop()"> %</td>
		         	<td><span class="iwmsmall">Default is 61.7 (~ 5:3 aspect ratio). This field will manipulate the aspect ratio of the map viewport.</span></td>
		         </tr><tr>
	          		<td class="iwm_stronger">Left Margin</td>
	          		<td><input type="number" name="iwm_left" size="10" value="<?php echo $cssarray['iwm_left'] ?>" onchange="redrawcrop()"> % </td>
		         	<td><span class="iwmsmall">These values will move the map horizontaly. Use negative values to move the map left and positive to move the map to the right.</span></td>
		         </tr><tr>
		         	<td class="iwm_stronger">Top Margin: </td>
		         	<td><input type="number" name="iwm_top" size="10" value="<?php echo $cssarray['iwm_top'] ?>" onchange="redrawcrop()"> % </td>
		         	<td><span class="iwmsmall">These values will move the map verticaly. Use negative values to move the map up and positive to move the map down.</span></td>
		         </tr>
		     </table>

		     </div>
		     <div>
         		<h4><i class="fa fa-square"></i> Hover Options for Active Elements</h4>
         		 <span class="iwmsmall"> These options will create css that target the map shapes that do not have the inactive regions colour. The biggest limitation of this hovering hack, is that it will only apply the hover effect to map shapes, it is not capable of recognizing the full region shapes. For example, when hovering a group of islands, only the hovered island will change colour.</span>
	          	 <br> <br>
         		<table>
	          	<tr>
	          		<td class="iwm_stronger">Hover Colour: </td>
	          		<td><input name="hovercolor" type="text" id="hovercolor" size="15" class="color {hash:true, adjust:false}" value="<?php echo $cssarray['hovercolor'] ?>"  onchange="redrawcrop()" /></td>
         			<td><span class="iwmsmall">The active hovered map shapes will change to this colour.</span></td>
				 </tr><tr>
         			<td class="iwm_stronger">Display Hand Cursor:</td>
         			<td><input name="showcursor" id="showcursor" type="checkbox" value="1" onchange="redrawcrop()" <?php if($cssarray['showcursor']=='1') { echo "checked"; } ?> /></td>
         			<td><span class="iwmsmall">Active elements, like active regions, markers or text labels, will display the hand cursor.</span></td>
         		</tr>
		     	</table>


         		<h4><i class="fa fa-square"></i> Region Border Options </h4>
         		<span class="iwmsmall"> These options will target the SVG path shapes and change their fill and stroke values.</span>
	          	 <br> <br>
         		<table>
		          	<tr>
		          		<td class="iwm_stronger">Borders Colour: </td>
		          		<td><input name="bcolor" type="text" id="bcolor" size="15" class="color {hash:true, adjust:false}" value="<?php echo $cssarray['bcolor'] ?>"  onchange="redrawcrop()" /></td>
			        	<td><span class="iwmsmall">Country or region borders colour.</span></td>
			        	</tr><tr>
			        	<td class="iwm_stronger">Stroke Width (all): </td>
			        	<td><input type="number" step="1" name="bwidth" size="10" value="<?php echo $cssarray['bwidth'] ?>" onchange="redrawcrop()"></td>
			        	<td><span class="iwmsmall">Default is 1 for normal stage and 2 when hovering. Changing this will affect both stages of the shape.</span></td>
	         			</tr>
	         			
	         			<tr>
			        	<td class="iwm_stronger">Stroke Width (Inactive Only): </td>
			        	<td><input type="number" step="1" name="biwidth" size="10" value="<?php echo $cssarray['biwidth'] ?>" onchange="redrawcrop()"></td>
			        	<td><span class="iwmsmall">With this option we target only the inactive regions borders.</span></td>
	         		</tr>
	        
		     	</table>

		        <h4><i class="fa fa-square"></i> Background Options </h4>
		        <span class="iwmsmall"> You can also use an image as a background to your map. This will make the background colour transparent and add the image as the background of the map's container.</span>
	          	 <br> <br>
		        <table style="padding-bottom:20px;">
	          		<tr>
		          		<td class="iwm_stronger">Background Image: </td>
		          		<td><input type="text" name="bgimage" id="bgimage" value="<?php echo $cssarray['bgimage'] ?>" size="10" onchange="redrawcrop()"></td>
			        	<td><span class="iwmsmall">Please include full URL to the image you want to use.</span></td>
			        	</tr><tr>
			        	<td class="iwm_stronger">Background Repeat: </td>
			        	<td><input name="bgrepeat" id="bgrepeat" type="checkbox" value="1" onchange="redrawcrop()" <?php if($cssarray['bgrepeat']=='1') { echo "checked"; } ?> /></td>
			        	<td><span class="iwmsmall">If active, image will repeat. If disabled image will strech to 100% so it's also responsive.</span></td>
			        </tr>
			    </table>

			    <h4><i class="fa fa-square"></i> Tooltip Options </h4>

			    <?php  if(!isset($options['default_usehtml'])) { ?>

			    <i class="fa fa-exclamation-triangle" style="color:red;"></i> <span class="iwmsmall">These settings will not take effect since HTML tooltips are disabled in the settings.</span>

			    <?php } else { ?>

		        <span class="iwmsmall">You can create more rules creating custom css for the class .google-visualization-tooltip</span>
		        	          	
		        <?php } ?>
		        	          	 <br> <br>
		        <table style="padding-bottom:20px;">
	          		<tr>
		          		<td class="iwm_stronger">Font-Family: </td>
		          		<td><input type="text" name="tooltipfontfamily" id="tooltipfontfamily" value="<?php echo $cssarray['tooltipfontfamily'] ?>" size="10" onchange="redrawcrop()"></td>
			        	<td><span class="iwmsmall">Specify the font for the tooltip</span></td>
			        </tr><tr>
			        	<td class="iwm_stronger">Font-size: </td>
			        	<td><input name="tooltipfontsize" id="tooltipfontsize" type="text" value="<?php echo $cssarray['tooltipfontsize'] ?>"  onchange="redrawcrop()" /></td>
			        	<td><span class="iwmsmall">You should use the unit value also, like 12px or 1em.</span></td>
			        </tr><tr>
			        	<td class="iwm_stronger">Background Colour: </td>
			        	<td><input name="tooltipbg" id="tooltipbg" type="text" onchange="redrawcrop()" value="<?php echo $cssarray['tooltipbg']; ?>" class="color {hash:true, adjust:false}" /></td>
			        	<td><span class="iwmsmall"></span></td>
			        </tr>
			        <tr>
			        	<td class="iwm_stronger">Width: </td>
			        	<td><input name="tooltipminwidth" id="tooltipminwidth" value="<?php echo $cssarray['tooltipminwidth'] ?>" type="text"  onchange="redrawcrop()" /></td>
			        	<td><span class="iwmsmall">Set a minimum width for the tooltip. You should also use the unit value also, like 12px or 1em.</span></td>
			        </tr><tr>
			        	<td class="iwm_stronger">Border Colour: </td>
			        	<td><input name="tooltipbordercolor" id="tooltipbordercolor" type="text" onchange="redrawcrop()" value="<?php echo $cssarray['tooltipbordercolor'] ?>" class="color {hash:true, adjust:false}" /></td>
			        	<td><span class="iwmsmall"></span></td>
			        </tr>
			        <tr>
			        	<td class="iwm_stronger">Border Width: </td>
			        	<td><input name="tooltipborderwidth" id="tooltipborderwidth" value="<?php echo $cssarray['tooltipborderwidth'] ?>" type="text"  onchange="redrawcrop()" /></td>
			        	<td><span class="iwmsmall">Set a minimum width for the tooltip. You should also use the unit value also, like 12px or 1em.</span></td>
			        </tr><tr>
         			<td class="iwm_stronger">Hide Title:</td>
         			<td><input name="tooltiphidetitle" id="tooltiphidetitle" type="checkbox" value="1" onchange="redrawcrop()" <?php if($cssarray['tooltiphidetitle']=='1') { echo "checked"; } ?> /></td>
         			<td><span class="iwmsmall">When active, first line of the tooltip (the title field) will not display.</span></td>
         			</tr>
			    </table>

			    <h4><i class="fa fa-square"></i> FontIcon Usage </h4>
			    <span class="iwmsmall">The Geochart API doesn't allow us to use custom markers. However we can use the text labels mode and use fonticons. The plugin includes 'FontAwesome' and <a target="_blank" href="http://cmoreira.net/interactive-world-maps-demo/use-font-icon-as-marker/">you can read more about this technique here.</a></span>
			     <br> <br>
			    <table style="padding-bottom:20px;">
			    	<tr>
			    	<td class="iwm_stronger">Include FontAwesome:</td>
         			<td><input name="fontawesomeinclude" id="fontawesomeinclude" type="checkbox" value="1" onchange="redrawcrop()" <?php if(isset($cssarray['fontawesomeinclude']) && $cssarray['fontawesomeinclude']=='1') { echo "checked"; } ?> /></td>
         			<td><span class="iwmsmall">If active fontAwesome file will be included with the map. If your page already has fontAwesome included, you don't need to include it here.</span></td>
         			</tr>
			    	<tr>
			    	<td class="iwm_stronger">Apply FontAwesome:</td>
         			<td><input name="fontawesomeapply" id="fontawesomeapply" type="checkbox" value="1" onchange="redrawcrop()" <?php if(isset($cssarray['fontawesomeapply']) && $cssarray['fontawesomeapply']=='1') { echo "checked"; } ?> /></td>
         			<td><span class="iwmsmall">When active Text Labels can use FontAwesome icons (<a href="http://fortawesome.github.io/Font-Awesome/cheatsheet/" target="_blank">cheatsheet</a>)</span></td>
         			</tr>
			    </table>


			    <input type="button" class="button-secondary" name="iwm-custom-clear" id="iwm-custom-clear" value="Clear Values" onClick="clearCssValues()">
			    <input type="submit" class="button-primary" value="<?php echo $submit_bt_value; ?>" />
         	</div>
         </div>
         
		</td>
      </tr>
      <tr>
        <td colspan="2"></td>
      </tr>
    </table>
  
  	<input type="hidden" name="mapimage" id="mapimage" value="">
  	<input type="hidden" name="customcss" id="customcss" value='<?php echo $customcss ?>'>

</form>
</div>

<?php 
$apiv = "1";
$usehtml = "0";
if(isset($options['default_usehtml']) && $options['default_usehtml']==1) { 
$apiv = $apiver;
$usehtml = "1";
}	?>

<script type='text/javascript'>
/* <![CDATA[ */
var iwmparam = [{"apiversion":"<?php echo $apiv; ?>","usehtml":"<?php echo $usehtml; ?>"}];
/* ]]> */
</script>
	
<?php	

}




function register_i_world_map_settings() {
	//register our settings
	register_setting( 'i-world-map-plugin-settings', 'i-world-map-settings');
}

//register default values
register_activation_hook(__FILE__, 'i_world_map_defaults');
function i_world_map_defaults() {
	$tmp = get_option('i-world-map-settings');
    if(($tmp['empty']=='1')||(!is_array($tmp))) {
		delete_option('i-world-map-settings'); 
		$arr = array(	"default_bg_color" => "#FFFFFF",
						"default_border_color" => "#CCCCCC",
						"default_border_stroke" => "0",
						"default_ina_color" => "#F5F5F5",
						"default_act_color" => "#438094",
						"default_marker_size" => "10",
						"default_width" => "600",
						"default_height" => "400",
						"default_aspect_ratio" => "1",
						"default_interactive" => "1",
						"default_showtooltip" => "1",
						"default_editor" => "1",
						"default_display_mode" => "regions",
						"default_region" => "world, countries",
						"map_projection" => "mercator",
						"default_responsive" => "1",
						"empty" => "0",
						"scriptadd" => "1",
							
		);
		update_option('i-world-map-settings', $arr);
	}
}



function i_world_map_settings_page() {
	

?>

<form method="post" action="options.php" id="dsform">

<div class="iwm-wrap">
<div id="interactive-world-maps" class="icon32"></div>
<h2>Settings</h2>
<?php if(isset($_GET['settings-updated']) && $_GET['settings-updated']=="true") { 
$msg = "Settings Updated";
$type = "updated";
i_world_map_message($msg);
} 


?>



  <p>
          
            Edit the default settings for the maps. <br />
            When creating a map, you can choose to use the default visual settings or create custom ones.<br />
          </p>

    <table width="100%" border="0" cellspacing="10"  cellpadding="10">
      <tr>
        <td width="25%" style="vertical-align:top;">
 			 <?php settings_fields( 'i-world-map-plugin-settings' ); ?>
            <?php $options = get_option('i-world-map-settings'); ?>

           


            <h3>
            Default Visual Settings
             </h3>
		    <table width="100%" cellpadding="2" cellspacing="2" class="stuffbox" id="default-settings-table">
            <tr valign="top">
              <td width="10%" nowrap="nowrap" scope="row"><strong>Background Color</strong></td>
              <td width="20%"><input type="text" name="i-world-map-settings[default_bg_color]" class="color {hash:true, adjust:false}" value="<?php echo $options['default_bg_color']; ?>" onchange="drawVisualization();" /></td>
            </tr>
            <tr valign="top">
              <td width="10%" nowrap="nowrap" scope="row"><strong>Border Color</strong></td>
              <td width="20%"><input type="text" name="i-world-map-settings[default_border_color]" class="color {hash:true, adjust:false}" value="<?php echo $options['default_border_color']; ?>" onchange="drawVisualization();" /></td>
            </tr>
            <tr valign="top">
              <td width="10%" nowrap="nowrap"><strong>Border Width (px)</strong></td>
              <td width="20%"><input name="i-world-map-settings[default_border_stroke]" value="<?php echo $options['default_border_stroke']; ?>" size="5" onchange="drawVisualization();" type="number" min="0" max="100" /></td>
            </tr>
            <tr valign="top">
              <td width="10%" nowrap="nowrap" scope="row">&nbsp;</td>
              <td width="20%">&nbsp;</td>
            </tr>
            <tr valign="top">
              <td width="10%" nowrap="nowrap" scope="row"><strong>Inactive Region Color</strong></td>
              <td width="20%"><input type="text" name="i-world-map-settings[default_ina_color]" class="color {hash:true, adjust:false}" value="<?php echo $options['default_ina_color']; ?>" onchange="drawVisualization();" /></td>
            </tr>
            <tr valign="top">
              <td width="10%" nowrap="nowrap" scope="row"><strong>Active Region Color</strong></td>
              <td width="20%"><input type="text" name="i-world-map-settings[default_act_color]" class="color {hash:true, adjust:false}" value="<?php echo $options['default_act_color']; ?>" onchange="drawVisualization();" /></td>
            </tr>
            <tr valign="top">
              <td nowrap="nowrap" scope="row">&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr valign="top">
              <td nowrap="nowrap" scope="row"><strong>Marker Size (px)</strong></td>
              <td><input name="i-world-map-settings[default_marker_size]" value="<?php echo $options['default_marker_size']; ?>" onchange="drawVisualization();" type="number" min="1" max="100" /></td>
            </tr>
            <tr valign="top">
              <td width="10%" nowrap="nowrap" scope="row">&nbsp;</td>
              <td width="20%">&nbsp;</td>
            </tr>
            <tr valign="top">
              <td width="10%" nowrap="nowrap" scope="row"><strong>Width (px)</strong></td>
              <td width="20%"><input name="i-world-map-settings[default_width]" type="text" value="<?php echo $options['default_width']; ?>" size="5" onchange="drawVisualization();" type="number"/></td>
            </tr>
            <tr valign="top">
              <td width="10%" nowrap="nowrap"><strong>Height (px)</strong></td>
              <td width="20%"><input name="i-world-map-settings[default_height]" type="text" value="<?php echo $options['default_height']; ?>" size="5" onchange="drawVisualization();" type="number"/></td>
            </tr>
            <tr valign="top">
              <td width="10%" nowrap="nowrap" scope="row"><strong>Keep Aspect Ratio</strong></td>
              <td width="20%"><input name="i-world-map-settings[default_aspect_ratio]" id="aspratio" type="checkbox" value="1" <?php if($options['default_aspect_ratio']==1) { ?>checked <?php } ?> onchange="drawVisualization();" />
              
              </td>

               <tr>
            	<td colspan="2" class="iwmsmall"><i class="fa fa-info-circle"></i>  Tip: In color fields you can use the word 'transparent' </td>
            </tr>

            </tr>
            </table>
            
            <h3>Default Map Settings </h3>
            <p>Values will be pre-selected when creating a new map.</p>
          <table width="100%" id="default-settings-table" class="stuffbox">
            <tr valign="top">
              <td nowrap="nowrap" scope="row"><strong>Region to Show</strong><br />
                
                <?php  i_world_map_build_region_select_options('i-world-map-settings[default_region]',$options['default_region'],'drawVisualization()'); ?>              </td>
            </tr>
            <tr valign="top">
              <td scope="row">&nbsp;</td>
            </tr>
            <tr valign="top">
              <td scope="row"><strong>Display Mode</strong><br />
                <select name="i-world-map-settings[default_display_mode]" onchange="drawVisualization();">
                  <option value="regions"  <?php if($options['default_display_mode']=='regions') { ?>selected="selected" <?php } ?>>Regions</option>
                  <option value="markers"  <?php if($options['default_display_mode']=='markers') { ?>selected="selected" <?php } ?> >Markers</option>
                   <!-- <option value="text"  <?php if($options['default_display_mode']=='text') { ?>selected="selected" <?php } ?> >Text Label</option> -->
                
              </select></td>
            </tr>
            <tr valign="top">
              <td scope="row">&nbsp;</td>
            </tr>
            <tr valign="top">
              <td scope="row"><p><strong>Interactivity<br />
                </strong>
                <input name="i-world-map-settings[default_interactive]" id="interactive" type="checkbox" value="1" <?php if($options['default_interactive']==1) { ?>checked <?php } ?> onchange="drawVisualization();" />Enable<br />
                <input name="i-world-map-settings[default_showtooltip]" id="showtooltip" type="checkbox" value="1" <?php if($options['default_showtooltip']==1) { ?>checked <?php } ?> onchange="drawVisualization();" />Show Tooltip
              </p>
              </td>
            </tr>
           
          </table>

          

         

          
          <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
          </p>
        </td>
        <td width="75%" valign="top"><!-- <h3>Default Settings Preview</h3>          <div id="visualization"></div> -->
        
         <h3>General Settings</h3>
          <table id="default-settings-table" class="stuffbox" width="100%">

          	<tr><td>

          		 <p><strong>Google Maps API Key </strong></p>
              <p>
                <input name="i-world-map-settings[api_key]" type="text" value="<?php if(isset($options['api_key'])) { echo $options['api_key']; }  ?>" />
              <a href="http://cmoreira.net/interactive-world-maps-demo/advanced-tips/generate-api-key/" target="_blank">How to Generate an API Key</a><br>
              <span class="howto">Some maps might need to 'Geocode' the region code used and it will use the Google Geocoding API, which needs and API Key to work.</span> 
              </p>
              <p>
                <input name="i-world-map-settings[scriptadd]" id="scriptadd" type="checkbox" value="1" <?php if(isset($options['scriptadd']) && $options['scriptadd']==1) { ?>checked <?php } ?> />
              Load Google Maps API File with Key<br>
              <span class="howto">If you already have a script using Google Maps with a valid API key, you might need to disable this option.</span> 
              </p>


          <p><strong>Map Projection</strong></p>
              <p>
                <select name="i-world-map-settings[map_projection]" id="map_projection" onchange="drawVisualization();"> 
                	<option value="mercator" <?php if(isset($options['map_projection']) && $options['map_projection']=='mercator') echo "selected='selected'"; ?>>Mercator</option>
                	<option value="kavrayskiy-vii" <?php if(isset($options['map_projection']) && $options['map_projection']=='kavrayskiy-vii') echo "selected='selected'"; ?>>Kavrayskiy-vii</option>
                	<option value="albers" <?php if(isset($options['map_projection']) && $options['map_projection']=='albers') echo "selected='selected'"; ?>>Albers</option>
                	<option value="lambert" <?php if(isset($options['map_projection']) && $options['map_projection']=='lambert') echo "selected='selected'"; ?>>Lambert</option>
                </select>
                <span class="howto"> Select the map projection format. Currently supported <a href="http://en.wikipedia.org/wiki/Mercator_projection" target="_blank">Mercator</a>, <a href="http://en.wikipedia.org/wiki/Kavrayskiy_VII_projection" target="_blank">Kavrayskiy_VII</a>, <a href="http://en.wikipedia.org/wiki/Albers_projection" target="_blank">Albers</a> and <a href="http://en.wikipedia.org/wiki/Lambert_conformal_conic_projection" target="_blank">Lambert</a>.</span>
              </p>
          </td></tr>

          <tr><td>
          <p><strong>Responsive Maps (Beta Feature)</strong></p>
              <p>
                <input name="i-world-map-settings[default_responsive]" id="responsive" type="checkbox" value="1" <?php if(isset($options['default_responsive']) && $options['default_responsive']==1) { ?>checked <?php } ?> />
              Redraw Map when viewport size changes<br>
              <span class="howto">When enabled the script will ignore the width/height settings of the map and ocupy 100% of the available space. When the window size changes it will try to redraw the map again to fit the available size.</span> 
              </p>
          </td></tr>

          <tr><td>
          <p><strong>HTML Tooltips </strong></p>
              <p>
                <input name="i-world-map-settings[default_usehtml]" id="usehtml" type="checkbox" value="1" <?php if(isset($options['default_usehtml']) && $options['default_usehtml']==1) { ?>checked <?php } ?> />
              Render HTML in the tooltips.<br>
              <span class="howto">Consider that the tooltip will inherit styles from your theme that might affect the way the tooltip displays. You can target the tooltip with CSS using the class <i>.google-visualization-tooltip</i>.</span> 
              </p>
          </td></tr>

          <tr><td>
          <p><strong>WYSIWYG Editor </strong></p>
              <p>
                <input name="i-world-map-settings[default_editor]" id="useeditor" type="checkbox" value="1" <?php if(isset($options['default_editor']) && $options['default_editor']==1) { ?>checked <?php } ?> />
              Display simple editor in Administration <br>
              <span class="howto">If active when you edit the map entries, the tooltip field (if html is enabled) and the action value field (if the action accepts html content) will display a simple editor instead of the simple textarea</span> 
              </p>
          </td></tr>

          <tr><td>
          <p><strong>Ajax Loading Fix </strong></p>
              <p>
                <input name="i-world-map-settings[ajax_enabled]" id="ajax_enabled" type="checkbox" value="1" <?php if(isset($options['ajax_enabled']) && $options['ajax_enabled']==1) { ?>checked <?php } ?> />
              Load files across site (not recommended)<br>
              <span class="howto">Some themes load content via ajax and fail to load the necessary files for the map to work. With this option enabled the map files will load by default, so they exist on the page when the map needs to display. This is not recomended, unless there isn't an alternative. If your theme allows you to disable ajax loading for specific URLs that would be a better option.</span> 
              </p>

              
          </td></tr>

      </table>


         <h3>Custom Styles & Script</h3>
          <table id="default-settings-table" class="stuffbox" width="100%">
          <tr><td>



          <p><strong>Custom CSS</strong></p>
              <p>
                <textarea name="i-world-map-settings[custom_css]" id="iwm_custom_css"><?php if(isset($options['custom_css'])) { echo $options['custom_css']; } ?></textarea>
              Include this CSS in pages where maps are displayed.<br>
              <span class="howto">If you want to include custom css together with your maps you can include the css here. <a href="http://cmoreira.net/interactive-world-maps-demo/advanced-customization/" target="_blank">You can see some examples of custom CSS in the official website of the plugin.</a></span> 
              </p>
          </td></tr>

          <tr><td>



          <p><strong>Custom Javascript</strong></p>
              <p>
                <textarea name="i-world-map-settings[custom_js]" id="iwm_custom_js"><?php if(isset($options['custom_js'])) { echo $options['custom_js']; } ?></textarea>
              Include this Javascript in pages where maps are displayed.<br>
              <span class="howto">If you want to include custom javascript together with your maps you can include the code here. </span> 
              </p>
          </td></tr>

      </table>

        </td>
      	


      </tr>
      

      </table>
      </td></tr>
    </table>
  <p>&nbsp; </p>
</div>
</form>
<?php } 


//Add Settings link to active plugins menu
add_filter('plugin_action_links', 'i_world_map_action_links', 10, 2);

function i_world_map_action_links($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=iwm_settings">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}


//Display custom CSS
function i_world_map_custom_css_js () {
	$options = get_option('i-world-map-settings');
	$css = isset($options['custom_css']) ? $options['custom_css'] : '';
	if($css!=''){
		echo '
		<!-- Custom Styles for Interactive World Maps -->
		<style type="text/css">
		'.$css.'
		</style>';
	}

	//display custom js
	$js = isset($options['custom_js']) ? $options['custom_js'] : '';
	if($js!=''){
		echo "
		<!-- Custom Javascript for Interactive World Maps -->
		<script type='text/javascript'>
		".$js."
		</script>";
	}

}




/*************************** TABLE CLASS ********************************/
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class i_world_map_manage_table extends WP_List_Table {
    
     
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'map',     //singular name of the listed records
            'plural'    => 'maps',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
    
    
   function column_default($item, $column_name){
        switch($column_name){
            case 'shortcode':
               return "[show-map id='".$item['id']."']";

            case 'image': if(isset($item['image']) && $item['image']!='') { return "<img src='".$item['image']."' width='200px'>"; } else { return ''; }
               
			
			case 'date':
               return $item['created'];
			   
            default:
                 return $item[$column_name]; 
        }
    }
    
        
    function column_name($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a title="Edit this map" href="?page=iwm_add&action=%s&map=%s">Edit</a>','edit',$item['id']),
            'duplicate'    => sprintf('<a title="Make a copy of this map" href="?page=%s&action=%s&map=%s">Duplicate</a>',$_REQUEST['page'],'duplicate',$item['id']),
            'delete'    => sprintf('<a title="Delete this map" href="?page=%s&action=%s&map=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),

        );
       
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['name'],
            /*$2%s*/ $item['id'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }
	
	
	      
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")

            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }
    
    
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			// 'id'     => 'ID',
            'name'     => 'Name',
            'description'    => 'Description',
            'shortcode'  => 'Shortcode',
            'image'  => 'Preview',
			'created'  => 'Date'
        );
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'name'    => array('name',false),
            'description'  => array('description',false),
			 'created'     => array('created',true),     //true means its already sorted

        );
        return $sortable_columns;
    }
    
    
    function get_bulk_actions() {
        $actions = array(
            'bulk-delete'    => 'Delete'
        );
        return $actions;
    }
    
    
    
    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
       if( 'bulk-delete'===$this->current_action() ) {
           
			foreach($_GET['map'] as $map) {
			delete_i_world_map($map);	
			}
			
			$alert = "Map(s) Deleted";
			i_world_map_message($alert);
        }        
    }
    
    
    function prepare_items() {        
       
        $per_page = 25;     
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();     
        
        
        $this->_column_headers = array($columns, $hidden, $sortable);  
       
        $this->process_bulk_action();       
      
	    global $wpdb;
        global $table_name_imap;
		$query = "SELECT * FROM " .$table_name_imap;
		$data = $wpdb->get_results($query, ARRAY_A);      
                
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'created'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
         
        $current_page = $this->get_pagenum();        
        $total_items = count($data);     
        
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);        
        
        $this->items = $data;      
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $per_page,                     
            'total_pages' => ceil($total_items/$per_page)   
        ) );
    }
    
}

//Function to delete maps
function delete_i_world_map($id) {
	global $wpdb;
	global $table_name_imap;
	$wpdb->query( 
	$wpdb->prepare( 
		"
		 DELETE FROM $table_name_imap WHERE id = %d
		 ",
	     $id 
        )
	);
	
}

//Function to duplicate maps
function duplicate_i_world_map($id) {
	global $wpdb;
	global $table_name_imap;


	$wpdb->query( 
	$wpdb->prepare( 
		"insert into $table_name_imap (`name`, `description`, `use_defaults`, `bg_color`, `border_color`, `border_stroke`, `ina_color`, `act_color`, `marker_size`, `width`, `height`, `aspect_ratio`, `interactive`, `showtooltip`, `region`, `display_mode`, `map_action`, `places`, `image`, `custom_action`, `custom_css`)
		select `name`, `description`, `use_defaults`, `bg_color`, `border_color`, `border_stroke`, `ina_color`, `act_color`, `marker_size`, `width`, `height`, `aspect_ratio`, `interactive`, `showtooltip`, `region`, `display_mode`, `map_action`, `places`, `image`, `custom_action`, `custom_css`
		from $table_name_imap
		where id = %d",
	     $id 
        )
	);

	$wpdb->update( 
	$table_name_imap, 
	array( 
		'name' => $wpdb->get_var( "SELECT name FROM $table_name_imap WHERE id = $wpdb->insert_id" ).' (Copy)'
	), 
	array( 'id' => $wpdb->insert_id )
	);
	
}


//To Show styled messages
function i_world_map_message($msg) { ?>
  <div id="message" class="updated"><p><?php echo $msg; ?></p></div>
<?php	
}

function i_world_map_message_red($msg) { ?>
  <div id="message" class="error"><p><?php echo $msg; ?></p></div>
<?php	
}





//Add Extra Menu to Admin Bar
function i_map_admin_bar() {
 global $wp_admin_bar;
 
 $wp_admin_bar->add_menu( array(
  'id' => 'i_world_map',
  'title' => 'i World Maps',
  'href' => admin_url('admin.php?page=i_world_map_menu')
 ) );
 
   $wp_admin_bar->add_menu( array(
  'parent' => 'i_world_map',
  'id' => 'i_world_map_manage',
  'title' => 'Manage',
  'href' => admin_url('admin.php?page=i_world_map_menu')
 ) );
 
 
  $wp_admin_bar->add_menu( array(
  'parent' => 'i_world_map',
  'id' => 'i_world_map_add',
  'title' => 'Add New',
  'href' => admin_url('admin.php?page=iwm_add')
 ) );
 
   $wp_admin_bar->add_menu( array(
  'parent' => 'i_world_map',
  'id' => 'i_world_map_default',
  'title' => 'Settings',
  'href' => admin_url('admin.php?page=iwm_settings')
 ) );
 
}

// If you want to add a menu to your admin bar, uncomment the line below
//add_action( 'admin_bar_menu', 'i_map_admin_bar', '61' );

function i_world_map_build_actions_select_options($name,$selected, $onchange) {
	
	$actions = array (
				array('name' => 'None', 'value' => 'none'),
				array('name' => 'Open URL (same window)', 'value' => 'i_map_action_open_url'),
				array('name' => 'Open URL (new window)', 'value' => 'i_map_action_open_url_new'),
				array('name' => 'Alert Message', 'value' => 'i_map_action_alert'),
				array('name' => 'Display Content Above Map', 'value' => 'i_map_action_content_above'),
				array('name' => 'Display Content Below Map', 'value' => 'i_map_action_content_below'),
				array('name' => 'Display Content Below & scroll', 'value' => 'i_map_action_content_below_scroll'),
				array('name' => 'Display Content in Lightbox', 'value' => 'i_map_action_colorbox_content'),
				array('name' => 'Display URL in Lightbox (iframe)', 'value' => 'i_map_action_colorbox_iframe'),
				array('name' => 'Display Image in Lightbox', 'value' => 'i_map_action_colorbox_image'),
				array('name' => 'Display inline content in Lightbox', 'value' => 'i_map_action_colorbox_inline'),
				array('name' => 'Custom Action', 'value' => 'i_map_action_custom'),
	);
?>
            <select name="<?php echo $name; ?>" <?php if($onchange!="") echo 'onchange="'.$onchange.'"'; ?> >
                <?php
				foreach ($actions as $action) { ?>				
                <option value="<?php echo $action['value']; ?>" <?php if($selected==$action['value']) echo "selected='selected'"; ?> ><?php echo $action['name']; ?></option>
                <?php } ?>
                </select>
                <?php
                
                }


function i_world_map_build_region_select_options($name,$selected, $onchange) {
				$regions = array (
					array('name' => 'World', 'value' => 'world,countries'),
					array('name' => 'World - Continent Regions', 'value' => 'world,continents'),
					array('name' => 'World - Subcontinents Regions', 'value' => 'world,subcontinents'),
					array('name' => 'Africa', 'value' => '002,countries'),
					array('name' => 'Africa - Subcontinents Regions', 'value' => '002,subcontinents'),
					array('name' => 'Africa - Northern Africa', 'value' => '015,countries'),
					array('name' => 'Africa - Western Africa', 'value' => '011,countries'),
					array('name' => 'Africa - Middle Africa', 'value' => '017,countries'),
					array('name' => 'Africa - Eastern Africa', 'value' => '014,countries'),
					array('name' => 'Africa - Southern Africa', 'value' => '018,countries'),
					array('name' => 'Europe', 'value' => '150,countries'),
					array('name' => 'Europe - Subcontinents Regions', 'value' => '150,subcontinents'),
					array('name' => 'Europe - Northern Europe', 'value' => '154,countries'),
					array('name' => 'Europe - Western Europe', 'value' => '155,countries'),
					array('name' => 'Europe - Eastern Europe', 'value' => '151,countries'),
					array('name' => 'Europe - Southern Europe', 'value' => '039,countries'),
					array('name' => 'Americas', 'value' => '019,countries'),
					array('name' => 'Americas - Subcontinents Regions', 'value' => '019,subcontinents'),
					array('name' => 'Americas - Northern America', 'value' => '021,countries'),
					array('name' => 'Americas - Caribbean', 'value' => '029,countries'),
					array('name' => 'Americas - Central America', 'value' => '013,countries'),
					array('name' => 'Americas - South America', 'value' => '005,countries'),
					array('name' => 'Asia', 'value' => '142,countries'),
					array('name' => 'Asia - Subcontinents Regions', 'value' => '142,subcontinents'),					
					array('name' => 'Asia - Central Asia', 'value' => '143,countries'),
					array('name' => 'Asia - Eastern Asia', 'value' => '030,countries'),
					array('name' => 'Asia - Southern Asia', 'value' => '034,countries'),
					array('name' => 'Asia - South-Eastern Asia', 'value' => '035,countries'),
					array('name' => 'Asia - Western Asia', 'value' => '145,countries'),
					array('name' => 'Oceania', 'value' => '009,countries'),
					array('name' => 'Oceania - Subcontinents Regions', 'value' => '009,subcontinents'),
					array('name' => 'Oceania - Australia and New Zealand', 'value' => '053,countries'),
					array('name' => 'Oceania - Melanesia', 'value' => '054,countries'),
					array('name' => 'Oceania - Micronesia', 'value' => '057,countries'),
					array('name' => 'Oceania - Polynesia', 'value' => '061,countries'),
					array('name' => 'United States of America', 'value' => 'US,countries'),
					array('name' => 'United States of America - States', 'value' => 'US,provinces'),
					array('name' => 'United States of America - Metropolitan Areas', 'value' => 'US,metros'),
					array('name' => 'USA - Alabama - Metropolitan Areas', 'value' => 'US-AL,metros'),
					array('name' => 'USA - Alabama State', 'value' => 'US-AL,provinces'),
					array('name' => 'USA - Alaska - Metropolitan Areas', 'value' => 'US-AK,metros'),
					array('name' => 'USA - Alaska State', 'value' => 'US-AK,provinces'),
					array('name' => 'USA - Arizona - Metropolitan Areas', 'value' => 'US-AZ,metros'),
					array('name' => 'USA - Arizona State', 'value' => 'US-AZ,provinces'),
					array('name' => 'USA - Arkansas - Metropolitan Areas', 'value' => 'US-AR,metros'),
					array('name' => 'USA - Arkansas State', 'value' => 'US-AR,provinces'),
					array('name' => 'USA - California - Metropolitan Areas', 'value' => 'US-CA,metros'),
					array('name' => 'USA - California State', 'value' => 'US-CA,provinces'),
					array('name' => 'USA - Colorado - Metropolitan Areas', 'value' => 'US-CO,metros'),
					array('name' => 'USA - Colorado State', 'value' => 'US-CO,provinces'),
					array('name' => 'USA - Connecticut - Metropolitan Areas', 'value' => 'US-CT,metros'),
					array('name' => 'USA - Connecticut State', 'value' => 'US-CT,provinces'),
					array('name' => 'USA - Delaware - Metropolitan Areas', 'value' => 'US-DE,metros'),
					array('name' => 'USA - Delaware State', 'value' => 'US-DE,provinces'),
					array('name' => 'USA - District of Columbia - Metropolitan Areas', 'value' => 'US-DC,metros'),
					array('name' => 'USA - District of Columbia', 'value' => 'US-DC,provinces'),
					array('name' => 'USA - Florida - Metropolitan Areas', 'value' => 'US-FL,metros'),
					array('name' => 'USA - Florida State', 'value' => 'US-FL,provinces'),
					array('name' => 'USA - Georgia - Metropolitan Areas', 'value' => 'US-GA,metros'),
					array('name' => 'USA - Georgia State', 'value' => 'US-GA,provinces'),
					array('name' => 'USA - Hawaii - Metropolitan Areas', 'value' => 'US-HI,metros'),
					array('name' => 'USA - Hawaii State', 'value' => 'US-HI,provinces'),
					array('name' => 'USA - Idaho - Metropolitan Areas', 'value' => 'US-ID,metros'),
					array('name' => 'USA - Idaho State', 'value' => 'US-ID,provinces'),
					array('name' => 'USA - Illinois - Metropolitan Areas', 'value' => 'US-IL,metros'),
					array('name' => 'USA - Illinois State', 'value' => 'US-IL,provinces'),
					array('name' => 'USA - Indiana - Metropolitan Areas', 'value' => 'US-IN,metros'),
					array('name' => 'USA - Indiana State', 'value' => 'US-IN,provinces'),
					array('name' => 'USA - Iowa - Metropolitan Areas', 'value' => 'US-IA,metros'),
					array('name' => 'USA - Iowa State', 'value' => 'US-IA,provinces'),
					array('name' => 'USA - Kansas - Metropolitan Areas', 'value' => 'US-KS,metros'),
					array('name' => 'USA - Kansas State', 'value' => 'US-KS,provinces'),
					array('name' => 'USA - Kentucky - Metropolitan Areas', 'value' => 'US-KY,metros'),
					array('name' => 'USA - Kentucky State', 'value' => 'US-KY,provinces'),
					array('name' => 'USA - Louisiana - Metropolitan Areas', 'value' => 'US-LA,metros'),
					array('name' => 'USA - Louisiana State', 'value' => 'US-LA,provinces'),
					array('name' => 'USA - Maine - Metropolitan Areas', 'value' => 'US-ME,metros'),
					array('name' => 'USA - Maine State', 'value' => 'US-ME,provinces'),
					array('name' => 'USA - Maryland - Metropolitan Areas', 'value' => 'US-MD,metros'),
					array('name' => 'USA - Maryland State', 'value' => 'US-MD,provinces'),
					array('name' => 'USA - Massachusetts - Metropolitan Areas', 'value' => 'US-MA,metros'),
					array('name' => 'USA - Massachusetts State', 'value' => 'US-MA,provinces'),
					array('name' => 'USA - Michigan - Metropolitan Areas', 'value' => 'US-MI,metros'),
					array('name' => 'USA - Michigan State', 'value' => 'US-MI,provinces'),
					array('name' => 'USA - Minnesota - Metropolitan Areas', 'value' => 'US-MN,metros'),
					array('name' => 'USA - Minnesota State', 'value' => 'US-MN,provinces'),
					array('name' => 'USA - Mississippi - Metropolitan Areas', 'value' => 'US-MS,metros'),
					array('name' => 'USA - Mississippi State', 'value' => 'US-MS,provinces'),
					array('name' => 'USA - Missouri - Metropolitan Areas', 'value' => 'US-MO,metros'),
					array('name' => 'USA - Missouri State', 'value' => 'US-MO,provinces'),
					array('name' => 'USA - Montana - Metropolitan Areas', 'value' => 'US-MT,metros'),
					array('name' => 'USA - Montana State', 'value' => 'US-MT,provinces'),
					array('name' => 'USA - Nebraska - Metropolitan Areas', 'value' => 'US-NE,metros'),
					array('name' => 'USA - Nebraska State', 'value' => 'US-NE,provinces'),
					array('name' => 'USA - Nevada - Metropolitan Areas', 'value' => 'US-NV,metros'),
					array('name' => 'USA - Nevada State', 'value' => 'US-NV,provinces'),
					array('name' => 'USA - New Hampshire - Metropolitan Areas', 'value' => 'US-NH,metros'),
					array('name' => 'USA - New Hampshire State', 'value' => 'US-NH,provinces'),
					array('name' => 'USA - New Jersey - Metropolitan Areas', 'value' => 'US-NJ,metros'),
					array('name' => 'USA - New Jersey State', 'value' => 'US-NJ,provinces'),
					array('name' => 'USA - New Mexico - Metropolitan Areas', 'value' => 'US-NM,metros'),
					array('name' => 'USA - New Mexico State', 'value' => 'US-NM,provinces'),
					array('name' => 'USA - New York - Metropolitan Areas', 'value' => 'US-NY,metros'),
					array('name' => 'USA - New York State', 'value' => 'US-NY,provinces'),
					array('name' => 'USA - North Carolina - Metropolitan Areas', 'value' => 'US-NC,metros'),
					array('name' => 'USA - North Carolina State', 'value' => 'US-NC,provinces'),
					array('name' => 'USA - North Dakota - Metropolitan Areas', 'value' => 'US-ND,metros'),
					array('name' => 'USA - North Dakota State', 'value' => 'US-ND,provinces'),
					array('name' => 'USA - Ohio - Metropolitan Areas', 'value' => 'US-OH,metros'),
					array('name' => 'USA - Ohio State', 'value' => 'US-OH,provinces'),
					array('name' => 'USA - Oklahoma - Metropolitan Areas', 'value' => 'US-OK,metros'),
					array('name' => 'USA - Oklahoma State', 'value' => 'US-OK,provinces'),
					array('name' => 'USA - Oregon - Metropolitan Areas', 'value' => 'US-OR,metros'),
					array('name' => 'USA - Oregon State', 'value' => 'US-OR,provinces'),
					array('name' => 'USA - Pennsylvania - Metropolitan Areas', 'value' => 'US-PA,metros'),
					array('name' => 'USA - Pennsylvania State', 'value' => 'US-PA,provinces'),
					array('name' => 'USA - Rhode Island - Metropolitan Areas', 'value' => 'US-RI,metros'),
					array('name' => 'USA - Rhode Island State', 'value' => 'US-RI,provinces'),
					array('name' => 'USA - South Carolina - Metropolitan Areas', 'value' => 'US-SC,metros'),
					array('name' => 'USA - South Carolina State', 'value' => 'US-SC,provinces'),
					array('name' => 'USA - South Dakota - Metropolitan Areas', 'value' => 'US-SD,metros'),
					array('name' => 'USA - South Dakota State', 'value' => 'US-SD,provinces'),
					array('name' => 'USA - Tennessee - Metropolitan Areas', 'value' => 'US-TN,metros'),
					array('name' => 'USA - Tennessee State', 'value' => 'US-TN,provinces'),
					array('name' => 'USA - Texas - Metropolitan Areas', 'value' => 'US-TX,metros'),
					array('name' => 'USA - Texas State', 'value' => 'US-TX,provinces'),
					array('name' => 'USA - Utah - Metropolitan Areas', 'value' => 'US-UT,metros'),
					array('name' => 'USA - Utah State', 'value' => 'US-UT,provinces'),
					array('name' => 'USA - Vermont - Metropolitan Areas', 'value' => 'US-VT,metros'),
					array('name' => 'USA - Vermont State', 'value' => 'US-VT,provinces'),
					array('name' => 'USA - Virginia - Metropolitan Areas', 'value' => 'US-VA,metros'),
					array('name' => 'USA - Virginia State', 'value' => 'US-VA,provinces'),
					array('name' => 'USA - Washington - Metropolitan Areas', 'value' => 'US-WA,metros'),
					array('name' => 'USA - Washington State', 'value' => 'US-WA,provinces'),
					array('name' => 'USA - West Virginia - Metropolitan Areas', 'value' => 'US-WV,metros'),
					array('name' => 'USA - West Virginia State', 'value' => 'US-WV,provinces'),
					array('name' => 'USA - Wisconsin - Metropolitan Areas', 'value' => 'US-WI,metros'),
					array('name' => 'USA - Wisconsin State', 'value' => 'US-WI,provinces'),
					array('name' => 'USA - Wyoming - Metropolitan Areas', 'value' => 'US-WY,metros'),
					array('name' => 'USA - Wyoming State', 'value' => 'US-WY,provinces'),					
					array('name' => 'Afghanistan', 'value' => 'AF,countries'),
					array('name' => 'Afghanistan - Provinces', 'value' => 'AF,provinces'),
					array('name' => 'Aland Islands', 'value' => 'AX,countries'),
					array('name' => 'Aland Islands - Provinces', 'value' => 'AX,provinces'),
					array('name' => 'Albania', 'value' => 'AL,countries'),
					array('name' => 'Albania - Provinces', 'value' => 'AL,provinces'),
					array('name' => 'Algeria', 'value' => 'DZ,countries'),
					array('name' => 'Algeria - Provinces', 'value' => 'DZ,provinces'),
					array('name' => 'American Samoa', 'value' => 'AS,countries'),
					array('name' => 'American Samoa - Provinces', 'value' => 'AS,provinces'),
					array('name' => 'Andorra', 'value' => 'AD,countries'),
					array('name' => 'Andorra - Provinces', 'value' => 'AD,provinces'),
					array('name' => 'Angola', 'value' => 'AO,countries'),
					array('name' => 'Angola - Provinces', 'value' => 'AO,provinces'),
					array('name' => 'Anguilla', 'value' => 'AI,countries'),
					array('name' => 'Anguilla - Provinces', 'value' => 'AI,provinces'),
					//array('name' => 'Antarctica', 'value' => 'AQ,countries'),
					//array('name' => 'Antarctica - Provinces', 'value' => 'AQ,provinces'),
					array('name' => 'Antigua and Barbuda', 'value' => 'AG,countries'),
					array('name' => 'Antigua and Barbuda - Provinces', 'value' => 'AG,provinces'),
					array('name' => 'Argentina', 'value' => 'AR,countries'),
					array('name' => 'Argentina - Provinces', 'value' => 'AR,provinces'),
					array('name' => 'Armenia', 'value' => 'AM,countries'),
					array('name' => 'Armenia - Provinces', 'value' => 'AM,provinces'),
					array('name' => 'Aruba', 'value' => 'AW,countries'),
					array('name' => 'Aruba - Provinces', 'value' => 'AW,provinces'),
					array('name' => 'Australia', 'value' => 'AU,countries'),
					array('name' => 'Australia - Provinces', 'value' => 'AU,provinces'),
					array('name' => 'Austria', 'value' => 'AT,countries'),
					array('name' => 'Austria - Provinces', 'value' => 'AT,provinces'),
					array('name' => 'Azerbaijan', 'value' => 'AZ,countries'),
					array('name' => 'Azerbaijan - Provinces', 'value' => 'AZ,provinces'),
					array('name' => 'Bahamas', 'value' => 'BS,countries'),
					array('name' => 'Bahamas - Provinces', 'value' => 'BS,provinces'),
					array('name' => 'Bahrain', 'value' => 'BH,countries'),
					array('name' => 'Bahrain - Provinces', 'value' => 'BH,provinces'),
					array('name' => 'Bangladesh', 'value' => 'BD,countries'),
					array('name' => 'Bangladesh - Provinces', 'value' => 'BD,provinces'),
					array('name' => 'Barbados', 'value' => 'BB,countries'),
					array('name' => 'Barbados - Provinces', 'value' => 'BB,provinces'),
					array('name' => 'Belarus', 'value' => 'BY,countries'),
					array('name' => 'Belarus - Provinces', 'value' => 'BY,provinces'),
					array('name' => 'Belgium', 'value' => 'BE,countries'),
					array('name' => 'Belgium - Provinces', 'value' => 'BE,provinces'),
					array('name' => 'Belize', 'value' => 'BZ,countries'),
					array('name' => 'Belize - Provinces', 'value' => 'BZ,provinces'),
					array('name' => 'Benin', 'value' => 'BJ,countries'),
					array('name' => 'Benin - Provinces', 'value' => 'BJ,provinces'),
					array('name' => 'Bermuda', 'value' => 'BM,countries'),
					array('name' => 'Bermuda - Provinces', 'value' => 'BM,provinces'),
					array('name' => 'Bhutan', 'value' => 'BT,countries'),
					array('name' => 'Bhutan - Provinces', 'value' => 'BT,provinces'),
					array('name' => 'Bolivia, Plurinational State of', 'value' => 'BO,countries'),
					array('name' => 'Bolivia, Plurinational State of - Provinces', 'value' => 'BO,provinces'),
					array('name' => 'Bonaire, Sint Eustatius and Saba', 'value' => 'BQ,countries'),
					array('name' => 'Bonaire, Sint Eustatius and Saba - Provinces', 'value' => 'BQ,provinces'),
					array('name' => 'Bosnia and Herzegovina', 'value' => 'BA,countries'),
					array('name' => 'Bosnia and Herzegovina - Provinces', 'value' => 'BA,provinces'),
					array('name' => 'Botswana', 'value' => 'BW,countries'),
					array('name' => 'Botswana - Provinces', 'value' => 'BW,provinces'),
					array('name' => 'Bouvet Island', 'value' => 'BV,countries'),
					array('name' => 'Bouvet Island - Provinces', 'value' => 'BV,provinces'),
					array('name' => 'Brazil', 'value' => 'BR,countries'),
					array('name' => 'Brazil - Provinces', 'value' => 'BR,provinces'),
					array('name' => 'British Indian Ocean Territory', 'value' => 'IO,countries'),
					array('name' => 'British Indian Ocean Territory - Provinces', 'value' => 'IO,provinces'),
					array('name' => 'Brunei Darussalam', 'value' => 'BN,countries'),
					array('name' => 'Brunei Darussalam - Provinces', 'value' => 'BN,provinces'),
					array('name' => 'Bulgaria', 'value' => 'BG,countries'),
					array('name' => 'Bulgaria - Provinces', 'value' => 'BG,provinces'),
					array('name' => 'Burkina Faso', 'value' => 'BF,countries'),
					array('name' => 'Burkina Faso - Provinces', 'value' => 'BF,provinces'),
					array('name' => 'Burundi', 'value' => 'BI,countries'),
					array('name' => 'Burundi - Provinces', 'value' => 'BI,provinces'),
					array('name' => 'Cambodia', 'value' => 'KH,countries'),
					array('name' => 'Cambodia - Provinces', 'value' => 'KH,provinces'),
					array('name' => 'Cameroon', 'value' => 'CM,countries'),
					array('name' => 'Cameroon - Provinces', 'value' => 'CM,provinces'),
					array('name' => 'Canada', 'value' => 'CA,countries'),
					array('name' => 'Canada - Provinces', 'value' => 'CA,provinces'),
					array('name' => 'Cape Verde', 'value' => 'CV,countries'),
					array('name' => 'Cape Verde - Provinces', 'value' => 'CV,provinces'),
					array('name' => 'Cayman Islands', 'value' => 'KY,countries'),
					array('name' => 'Cayman Islands - Provinces', 'value' => 'KY,provinces'),
					array('name' => 'Central African Republic', 'value' => 'CF,countries'),
					array('name' => 'Central African Republic - Provinces', 'value' => 'CF,provinces'),
					array('name' => 'Chad', 'value' => 'TD,countries'),
					array('name' => 'Chad - Provinces', 'value' => 'TD,provinces'),
					array('name' => 'Chile', 'value' => 'CL,countries'),
					array('name' => 'Chile - Provinces', 'value' => 'CL,provinces'),
					array('name' => 'China', 'value' => 'CN,countries'),
					array('name' => 'China - Provinces', 'value' => 'CN,provinces'),
					array('name' => 'Christmas Island', 'value' => 'CX,countries'),
					array('name' => 'Christmas Island - Provinces', 'value' => 'CX,provinces'),
					array('name' => 'Cocos (Keeling) Islands', 'value' => 'CC,countries'),
					array('name' => 'Cocos (Keeling) Islands - Provinces', 'value' => 'CC,provinces'),
					array('name' => 'Colombia', 'value' => 'CO,countries'),
					array('name' => 'Colombia - Provinces', 'value' => 'CO,provinces'),
					array('name' => 'Comoros', 'value' => 'KM,countries'),
					array('name' => 'Comoros - Provinces', 'value' => 'KM,provinces'),
					array('name' => 'Congo', 'value' => 'CG,countries'),
					array('name' => 'Congo - Provinces', 'value' => 'CG,provinces'),
					array('name' => 'Congo, the Democratic Republic of the', 'value' => 'CD,countries'),
					array('name' => 'Congo, the Democratic Republic of the - Provinces', 'value' => 'CD,provinces'),
					array('name' => 'Cook Islands', 'value' => 'CK,countries'),
					array('name' => 'Cook Islands - Provinces', 'value' => 'CK,provinces'),
					array('name' => 'Costa Rica', 'value' => 'CR,countries'),
					array('name' => 'Costa Rica - Provinces', 'value' => 'CR,provinces'),
					array('name' => 'Cote d\'Ivoire ', 'value' => 'CI,countries'),
					array('name' => 'Cote d\'Ivoire  - Provinces', 'value' => 'CI,provinces'),
					array('name' => 'Croatia', 'value' => 'HR,countries'),
					array('name' => 'Croatia - Provinces', 'value' => 'HR,provinces'),
					array('name' => 'Cuba', 'value' => 'CU,countries'),
					array('name' => 'Cuba - Provinces', 'value' => 'CU,provinces'),
					array('name' => 'Curaçao', 'value' => 'CW,countries'),
					array('name' => 'Curaçao - Provinces', 'value' => 'CW,provinces'),
					array('name' => 'Cyprus', 'value' => 'CY,countries'),
					array('name' => 'Cyprus - Provinces', 'value' => 'CY,provinces'),
					array('name' => 'Czech Republic', 'value' => 'CZ,countries'),
					array('name' => 'Czech Republic - Provinces', 'value' => 'CZ,provinces'),
					array('name' => 'Denmark', 'value' => 'DK,countries'),
					array('name' => 'Denmark - Provinces', 'value' => 'DK,provinces'),
					array('name' => 'Djibouti', 'value' => 'DJ,countries'),
					array('name' => 'Djibouti - Provinces', 'value' => 'DJ,provinces'),
					array('name' => 'Dominica', 'value' => 'DM,countries'),
					array('name' => 'Dominica - Provinces', 'value' => 'DM,provinces'),
					array('name' => 'Dominican Republic', 'value' => 'DO,countries'),
					array('name' => 'Dominican Republic - Provinces', 'value' => 'DO,provinces'),
					array('name' => 'Ecuador', 'value' => 'EC,countries'),
					array('name' => 'Ecuador - Provinces', 'value' => 'EC,provinces'),
					array('name' => 'Egypt', 'value' => 'EG,countries'),
					array('name' => 'Egypt - Provinces', 'value' => 'EG,provinces'),
					array('name' => 'El Salvador', 'value' => 'SV,countries'),
					array('name' => 'El Salvador - Provinces', 'value' => 'SV,provinces'),
					array('name' => 'Equatorial Guinea', 'value' => 'GQ,countries'),
					array('name' => 'Equatorial Guinea - Provinces', 'value' => 'GQ,provinces'),
					array('name' => 'Eritrea', 'value' => 'ER,countries'),
					array('name' => 'Eritrea - Provinces', 'value' => 'ER,provinces'),
					array('name' => 'Estonia', 'value' => 'EE,countries'),
					array('name' => 'Estonia - Provinces', 'value' => 'EE,provinces'),
					array('name' => 'Ethiopia', 'value' => 'ET,countries'),
					array('name' => 'Ethiopia - Provinces', 'value' => 'ET,provinces'),
					array('name' => 'Falkland Islands (Malvinas)', 'value' => 'FK,countries'),
					array('name' => 'Falkland Islands (Malvinas) - Provinces', 'value' => 'FK,provinces'),
					array('name' => 'Faroe Islands', 'value' => 'FO,countries'),
					array('name' => 'Faroe Islands - Provinces', 'value' => 'FO,provinces'),
					array('name' => 'Fiji', 'value' => 'FJ,countries'),
					array('name' => 'Fiji - Provinces', 'value' => 'FJ,provinces'),
					array('name' => 'Finland', 'value' => 'FI,countries'),
					array('name' => 'Finland - Provinces', 'value' => 'FI,provinces'),
					array('name' => 'France', 'value' => 'FR,countries'),
					array('name' => 'France - Provinces', 'value' => 'FR,provinces'),
					array('name' => 'French Guiana', 'value' => 'GF,countries'),
					array('name' => 'French Guiana - Provinces', 'value' => 'GF,provinces'),
					array('name' => 'French Polynesia', 'value' => 'PF,countries'),
					array('name' => 'French Polynesia - Provinces', 'value' => 'PF,provinces'),
					array('name' => 'French Southern Territories', 'value' => 'TF,countries'),
					array('name' => 'French Southern Territories - Provinces', 'value' => 'TF,provinces'),
					array('name' => 'Gabon', 'value' => 'GA,countries'),
					array('name' => 'Gabon - Provinces', 'value' => 'GA,provinces'),
					array('name' => 'Gambia', 'value' => 'GM,countries'),
					array('name' => 'Gambia - Provinces', 'value' => 'GM,provinces'),
					array('name' => 'Georgia', 'value' => 'GE,countries'),
					array('name' => 'Georgia - Provinces', 'value' => 'GE,provinces'),
					array('name' => 'Germany', 'value' => 'DE,countries'),
					array('name' => 'Germany - Provinces', 'value' => 'DE,provinces'),
					array('name' => 'Ghana', 'value' => 'GH,countries'),
					array('name' => 'Ghana - Provinces', 'value' => 'GH,provinces'),
					array('name' => 'Gibraltar', 'value' => 'GI,countries'),
					array('name' => 'Gibraltar - Provinces', 'value' => 'GI,provinces'),
					array('name' => 'Greece', 'value' => 'GR,countries'),
					array('name' => 'Greece - Provinces', 'value' => 'GR,provinces'),
					array('name' => 'Greenland', 'value' => 'GL,countries'),
					array('name' => 'Greenland - Provinces', 'value' => 'GL,provinces'),
					array('name' => 'Grenada', 'value' => 'GD,countries'),
					array('name' => 'Grenada - Provinces', 'value' => 'GD,provinces'),
					array('name' => 'Guadeloupe', 'value' => 'GP,countries'),
					array('name' => 'Guadeloupe - Provinces', 'value' => 'GP,provinces'),
					array('name' => 'Guam', 'value' => 'GU,countries'),
					array('name' => 'Guam - Provinces', 'value' => 'GU,provinces'),
					array('name' => 'Guatemala', 'value' => 'GT,countries'),
					array('name' => 'Guatemala - Provinces', 'value' => 'GT,provinces'),
					array('name' => 'Guernsey', 'value' => 'GG,countries'),
					array('name' => 'Guernsey - Provinces', 'value' => 'GG,provinces'),
					array('name' => 'Guinea', 'value' => 'GN,countries'),
					array('name' => 'Guinea - Provinces', 'value' => 'GN,provinces'),
					array('name' => 'Guinea-Bissau', 'value' => 'GW,countries'),
					array('name' => 'Guinea-Bissau - Provinces', 'value' => 'GW,provinces'),
					array('name' => 'Guyana', 'value' => 'GY,countries'),
					array('name' => 'Guyana - Provinces', 'value' => 'GY,provinces'),
					array('name' => 'Haiti', 'value' => 'HT,countries'),
					array('name' => 'Haiti - Provinces', 'value' => 'HT,provinces'),
					array('name' => 'Heard Island and McDonald Islands', 'value' => 'HM,countries'),
					array('name' => 'Heard Island and McDonald Islands - Provinces', 'value' => 'HM,provinces'),
					array('name' => 'Holy See (Vatican City State)', 'value' => 'VA,countries'),
					array('name' => 'Honduras', 'value' => 'HN,countries'),
					array('name' => 'Honduras - Provinces', 'value' => 'HN,provinces'),
					array('name' => 'Hong Kong', 'value' => 'HK,countries'),
					array('name' => 'Hong Kong - Provinces', 'value' => 'HK,provinces'),
					array('name' => 'Hungary', 'value' => 'HU,countries'),
					array('name' => 'Hungary - Provinces', 'value' => 'HU,provinces'),
					array('name' => 'Iceland', 'value' => 'IS,countries'),
					array('name' => 'Iceland - Provinces', 'value' => 'IS,provinces'),
					array('name' => 'India', 'value' => 'IN,countries'),
					array('name' => 'India - Provinces', 'value' => 'IN,provinces'),
					array('name' => 'Indonesia', 'value' => 'ID,countries'),
					array('name' => 'Indonesia - Provinces', 'value' => 'ID,provinces'),
					array('name' => 'Iran, Islamic Republic of', 'value' => 'IR,countries'),
					array('name' => 'Iran, Islamic Republic of - Provinces', 'value' => 'IR,provinces'),
					array('name' => 'Iraq', 'value' => 'IQ,countries'),
					array('name' => 'Iraq - Provinces', 'value' => 'IQ,provinces'),
					array('name' => 'Ireland', 'value' => 'IE,countries'),
					array('name' => 'Ireland - Provinces', 'value' => 'IE,provinces'),
					array('name' => 'Isle of Man', 'value' => 'IM,countries'),
					array('name' => 'Isle of Man - Provinces', 'value' => 'IM,provinces'),
					array('name' => 'Israel', 'value' => 'IL,countries'),
					array('name' => 'Israel - Provinces', 'value' => 'IL,provinces'),
					array('name' => 'Italy', 'value' => 'IT,countries'),
					array('name' => 'Italy - Provinces', 'value' => 'IT,provinces'),
					array('name' => 'Jamaica', 'value' => 'JM,countries'),
					array('name' => 'Jamaica - Provinces', 'value' => 'JM,provinces'),
					array('name' => 'Japan', 'value' => 'JP,countries'),
					array('name' => 'Japan - Provinces', 'value' => 'JP,provinces'),
					array('name' => 'Jersey', 'value' => 'JE,countries'),
					array('name' => 'Jersey - Provinces', 'value' => 'JE,provinces'),
					array('name' => 'Jordan', 'value' => 'JO,countries'),
					array('name' => 'Jordan - Provinces', 'value' => 'JO,provinces'),
					array('name' => 'Kazakhstan', 'value' => 'KZ,countries'),
					array('name' => 'Kazakhstan - Provinces', 'value' => 'KZ,provinces'),
					array('name' => 'Kenya', 'value' => 'KE,countries'),
					array('name' => 'Kenya - Provinces', 'value' => 'KE,provinces'),
					array('name' => 'Kiribati', 'value' => 'KI,countries'),
					array('name' => 'Kiribati - Provinces', 'value' => 'KI,provinces'),
					array('name' => 'Korea, Democratic People\'s Republic of', 'value' => 'KP,countries'),
					array('name' => 'Korea, Democratic People\'s Republic of - Provinces', 'value' => 'KP,provinces'),
					array('name' => 'Korea, Republic of', 'value' => 'KR,countries'),
					array('name' => 'Korea, Republic of - Provinces', 'value' => 'KR,provinces'),
					array('name' => 'Kosovo', 'value' => 'XK,countries'),
					array('name' => 'Kuwait', 'value' => 'KW,countries'),
					array('name' => 'Kuwait - Provinces', 'value' => 'KW,provinces'),
					array('name' => 'Kyrgyzstan', 'value' => 'KG,countries'),
					array('name' => 'Kyrgyzstan - Provinces', 'value' => 'KG,provinces'),
					array('name' => 'Lao People\'s Democratic Republic', 'value' => 'LA,countries'),
					array('name' => 'Lao People\'s Democratic Republic - Provinces', 'value' => 'LA,provinces'),
					array('name' => 'Latvia', 'value' => 'LV,countries'),
					array('name' => 'Latvia - Provinces', 'value' => 'LV,provinces'),
					array('name' => 'Lebanon', 'value' => 'LB,countries'),
					array('name' => 'Lebanon - Provinces', 'value' => 'LB,provinces'),
					array('name' => 'Lesotho', 'value' => 'LS,countries'),
					array('name' => 'Lesotho - Provinces', 'value' => 'LS,provinces'),
					array('name' => 'Liberia', 'value' => 'LR,countries'),
					array('name' => 'Liberia - Provinces', 'value' => 'LR,provinces'),
					array('name' => 'Libya', 'value' => 'LY,countries'),
					array('name' => 'Libya - Provinces', 'value' => 'LY,provinces'),
					array('name' => 'Liechtenstein', 'value' => 'LI,countries'),
					array('name' => 'Liechtenstein - Provinces', 'value' => 'LI,provinces'),
					array('name' => 'Lithuania', 'value' => 'LT,countries'),
					array('name' => 'Lithuania - Provinces', 'value' => 'LT,provinces'),
					array('name' => 'Luxembourg', 'value' => 'LU,countries'),
					array('name' => 'Luxembourg - Provinces', 'value' => 'LU,provinces'),
					array('name' => 'Macao', 'value' => 'MO,countries'),
					array('name' => 'Macao - Provinces', 'value' => 'MO,provinces'),
					array('name' => 'Macedonia, the former Yugoslav Republic of', 'value' => 'MK,countries'),
					array('name' => 'Macedonia, the former Yugoslav Republic of - Provinces', 'value' => 'MK,provinces'),
					array('name' => 'Madagascar', 'value' => 'MG,countries'),
					array('name' => 'Madagascar - Provinces', 'value' => 'MG,provinces'),
					array('name' => 'Malawi', 'value' => 'MW,countries'),
					array('name' => 'Malawi - Provinces', 'value' => 'MW,provinces'),
					array('name' => 'Malaysia', 'value' => 'MY,countries'),
					array('name' => 'Malaysia - Provinces', 'value' => 'MY,provinces'),
					array('name' => 'Maldives', 'value' => 'MV,countries'),
					array('name' => 'Maldives - Provinces', 'value' => 'MV,provinces'),
					array('name' => 'Mali', 'value' => 'ML,countries'),
					array('name' => 'Mali - Provinces', 'value' => 'ML,provinces'),
					array('name' => 'Malta', 'value' => 'MT,countries'),
					array('name' => 'Malta - Provinces', 'value' => 'MT,provinces'),
					array('name' => 'Marshall Islands', 'value' => 'MH,countries'),
					array('name' => 'Marshall Islands - Provinces', 'value' => 'MH,provinces'),
					array('name' => 'Martinique', 'value' => 'MQ,countries'),
					array('name' => 'Martinique - Provinces', 'value' => 'MQ,provinces'),
					array('name' => 'Mauritania', 'value' => 'MR,countries'),
					array('name' => 'Mauritania - Provinces', 'value' => 'MR,provinces'),
					array('name' => 'Mauritius', 'value' => 'MU,countries'),
					array('name' => 'Mauritius - Provinces', 'value' => 'MU,provinces'),
					array('name' => 'Mayotte', 'value' => 'YT,countries'),
					array('name' => 'Mayotte - Provinces', 'value' => 'YT,provinces'),
					array('name' => 'Mexico', 'value' => 'MX,countries'),
					array('name' => 'Mexico - Provinces', 'value' => 'MX,provinces'),
					array('name' => 'Micronesia, Federated States of', 'value' => 'FM,countries'),
					array('name' => 'Micronesia, Federated States of - Provinces', 'value' => 'FM,provinces'),
					array('name' => 'Moldova, Republic of', 'value' => 'MD,countries'),
					array('name' => 'Moldova, Republic of - Provinces', 'value' => 'MD,provinces'),
					array('name' => 'Monaco', 'value' => 'MC,countries'),
					array('name' => 'Monaco - Provinces', 'value' => 'MC,provinces'),
					array('name' => 'Mongolia', 'value' => 'MN,countries'),
					array('name' => 'Mongolia - Provinces', 'value' => 'MN,provinces'),
					array('name' => 'Montenegro', 'value' => 'ME,countries'),
					array('name' => 'Montenegro - Provinces', 'value' => 'ME,provinces'),
					array('name' => 'Montserrat', 'value' => 'MS,countries'),
					array('name' => 'Montserrat - Provinces', 'value' => 'MS,provinces'),
					array('name' => 'Morocco', 'value' => 'MA,countries'),
					array('name' => 'Morocco - Provinces', 'value' => 'MA,provinces'),
					array('name' => 'Mozambique', 'value' => 'MZ,countries'),
					array('name' => 'Mozambique - Provinces', 'value' => 'MZ,provinces'),
					array('name' => 'Myanmar', 'value' => 'MM,countries'),
					array('name' => 'Myanmar - Provinces', 'value' => 'MM,provinces'),
					array('name' => 'Namibia', 'value' => 'NA,countries'),
					array('name' => 'Namibia - Provinces', 'value' => 'NA,provinces'),
					array('name' => 'Nauru', 'value' => 'NR,countries'),
					array('name' => 'Nauru - Provinces', 'value' => 'NR,provinces'),
					array('name' => 'Nepal', 'value' => 'NP,countries'),
					array('name' => 'Nepal - Provinces', 'value' => 'NP,provinces'),
					array('name' => 'Netherlands', 'value' => 'NL,countries'),
					array('name' => 'Netherlands - Provinces', 'value' => 'NL,provinces'),
					array('name' => 'New Caledonia', 'value' => 'NC,countries'),
					array('name' => 'New Caledonia - Provinces', 'value' => 'NC,provinces'),
					array('name' => 'New Zealand', 'value' => 'NZ,countries'),
					array('name' => 'New Zealand - Provinces', 'value' => 'NZ,provinces'),
					array('name' => 'Nicaragua', 'value' => 'NI,countries'),
					array('name' => 'Nicaragua - Provinces', 'value' => 'NI,provinces'),
					array('name' => 'Niger', 'value' => 'NE,countries'),
					array('name' => 'Niger - Provinces', 'value' => 'NE,provinces'),
					array('name' => 'Nigeria', 'value' => 'NG,countries'),
					array('name' => 'Nigeria - Provinces', 'value' => 'NG,provinces'),
					array('name' => 'Niue', 'value' => 'NU,countries'),
					array('name' => 'Niue - Provinces', 'value' => 'NU,provinces'),
					array('name' => 'Norfolk Island', 'value' => 'NF,countries'),
					array('name' => 'Norfolk Island - Provinces', 'value' => 'NF,provinces'),
					array('name' => 'Northern Mariana Islands', 'value' => 'MP,countries'),
					array('name' => 'Northern Mariana Islands - Provinces', 'value' => 'MP,provinces'),
					array('name' => 'Norway', 'value' => 'NO,countries'),
					array('name' => 'Norway - Provinces', 'value' => 'NO,provinces'),
					array('name' => 'Oman', 'value' => 'OM,countries'),
					array('name' => 'Oman - Provinces', 'value' => 'OM,provinces'),
					array('name' => 'Pakistan', 'value' => 'PK,countries'),
					array('name' => 'Pakistan - Provinces', 'value' => 'PK,provinces'),
					array('name' => 'Palau', 'value' => 'PW,countries'),
					array('name' => 'Palau - Provinces', 'value' => 'PW,provinces'),
					array('name' => 'Palestinian Territory, Occupied', 'value' => 'PS,countries'),
					array('name' => 'Palestinian Territory, Occupied - Provinces', 'value' => 'PS,provinces'),
					array('name' => 'Panama', 'value' => 'PA,countries'),
					array('name' => 'Panama - Provinces', 'value' => 'PA,provinces'),
					array('name' => 'Papua New Guinea', 'value' => 'PG,countries'),
					array('name' => 'Papua New Guinea - Provinces', 'value' => 'PG,provinces'),
					array('name' => 'Paraguay', 'value' => 'PY,countries'),
					array('name' => 'Paraguay - Provinces', 'value' => 'PY,provinces'),
					array('name' => 'Peru', 'value' => 'PE,countries'),
					array('name' => 'Peru - Provinces', 'value' => 'PE,provinces'),
					array('name' => 'Philippines', 'value' => 'PH,countries'),
					array('name' => 'Philippines - Provinces', 'value' => 'PH,provinces'),
					array('name' => 'Pitcairn', 'value' => 'PN,countries'),
					array('name' => 'Pitcairn - Provinces', 'value' => 'PN,provinces'),
					array('name' => 'Poland', 'value' => 'PL,countries'),
					array('name' => 'Poland - Provinces', 'value' => 'PL,provinces'),
					array('name' => 'Portugal', 'value' => 'PT,countries'),
					array('name' => 'Portugal - Provinces', 'value' => 'PT,provinces'),
					array('name' => 'Puerto Rico', 'value' => 'PR,countries'),
					array('name' => 'Puerto Rico - Provinces', 'value' => 'PR,provinces'),
					array('name' => 'Qatar', 'value' => 'QA,countries'),
					array('name' => 'Qatar - Provinces', 'value' => 'QA,provinces'),
					array('name' => 'Reunion !Réunion', 'value' => 'RE,countries'),
					array('name' => 'Reunion !Réunion - Provinces', 'value' => 'RE,provinces'),
					array('name' => 'Romania', 'value' => 'RO,countries'),
					array('name' => 'Romania - Provinces', 'value' => 'RO,provinces'),
					array('name' => 'Russian Federation', 'value' => 'RU,countries'),
					array('name' => 'Russian Federation - Provinces', 'value' => 'RU,provinces'),
					array('name' => 'Rwanda', 'value' => 'RW,countries'),
					array('name' => 'Rwanda - Provinces', 'value' => 'RW,provinces'),
					array('name' => 'Saint Barthélemy', 'value' => 'BL,countries'),
					array('name' => 'Saint Barthélemy - Provinces', 'value' => 'BL,provinces'),
					array('name' => 'Saint Helena, Ascension and Tristan da Cunha', 'value' => 'SH,countries'),
					array('name' => 'Saint Helena, Ascension and Tristan da Cunha - Provinces', 'value' => 'SH,provinces'),
					array('name' => 'Saint Kitts and Nevis', 'value' => 'KN,countries'),
					array('name' => 'Saint Kitts and Nevis - Provinces', 'value' => 'KN,provinces'),
					array('name' => 'Saint Lucia', 'value' => 'LC,countries'),
					array('name' => 'Saint Lucia - Provinces', 'value' => 'LC,provinces'),
					array('name' => 'Saint Martin (French part)', 'value' => 'MF,countries'),
					array('name' => 'Saint Martin (French part) - Provinces', 'value' => 'MF,provinces'),
					array('name' => 'Saint Pierre and Miquelon', 'value' => 'PM,countries'),
					array('name' => 'Saint Pierre and Miquelon - Provinces', 'value' => 'PM,provinces'),
					array('name' => 'Saint Vincent and the Grenadines', 'value' => 'VC,countries'),
					array('name' => 'Saint Vincent and the Grenadines - Provinces', 'value' => 'VC,provinces'),
					array('name' => 'Samoa', 'value' => 'WS,countries'),
					array('name' => 'Samoa - Provinces', 'value' => 'WS,provinces'),
					array('name' => 'San Marino', 'value' => 'SM,countries'),
					array('name' => 'San Marino - Provinces', 'value' => 'SM,provinces'),
					array('name' => 'Sao Tome and Principe', 'value' => 'ST,countries'),
					array('name' => 'Sao Tome and Principe - Provinces', 'value' => 'ST,provinces'),
					array('name' => 'Saudi Arabia', 'value' => 'SA,countries'),
					array('name' => 'Saudi Arabia - Provinces', 'value' => 'SA,provinces'),
					array('name' => 'Senegal', 'value' => 'SN,countries'),
					array('name' => 'Senegal - Provinces', 'value' => 'SN,provinces'),
					array('name' => 'Serbia', 'value' => 'RS,countries'),
					array('name' => 'Serbia - Provinces', 'value' => 'RS,provinces'),
					array('name' => 'Seychelles', 'value' => 'SC,countries'),
					array('name' => 'Seychelles - Provinces', 'value' => 'SC,provinces'),
					array('name' => 'Sierra Leone', 'value' => 'SL,countries'),
					array('name' => 'Sierra Leone - Provinces', 'value' => 'SL,provinces'),
					array('name' => 'Singapore', 'value' => 'SG,countries'),
					array('name' => 'Singapore - Provinces', 'value' => 'SG,provinces'),
					array('name' => 'Sint Maarten (Dutch part)', 'value' => 'SX,countries'),
					array('name' => 'Sint Maarten (Dutch part) - Provinces', 'value' => 'SX,provinces'),
					array('name' => 'Slovakia', 'value' => 'SK,countries'),
					array('name' => 'Slovakia - Provinces', 'value' => 'SK,provinces'),
					array('name' => 'Slovenia', 'value' => 'SI,countries'),
					array('name' => 'Slovenia - Provinces', 'value' => 'SI,provinces'),
					array('name' => 'Solomon Islands', 'value' => 'SB,countries'),
					array('name' => 'Solomon Islands - Provinces', 'value' => 'SB,provinces'),
					array('name' => 'Somalia', 'value' => 'SO,countries'),
					array('name' => 'Somalia - Provinces', 'value' => 'SO,provinces'),
					array('name' => 'South Africa', 'value' => 'ZA,countries'),
					array('name' => 'South Africa - Provinces', 'value' => 'ZA,provinces'),
					array('name' => 'South Georgia and the South Sandwich Islands', 'value' => 'GS,countries'),
					array('name' => 'South Georgia and the South Sandwich Islands - Provinces', 'value' => 'GS,provinces'),
					array('name' => 'South Sudan', 'value' => 'SS,countries'),
					array('name' => 'South Sudan - Provinces', 'value' => 'SS,provinces'),
					array('name' => 'Spain', 'value' => 'ES,countries'),
					array('name' => 'Spain - Provinces', 'value' => 'ES,provinces'),
					array('name' => 'Sri Lanka', 'value' => 'LK,countries'),
					array('name' => 'Sri Lanka - Provinces', 'value' => 'LK,provinces'),
					array('name' => 'Sudan', 'value' => 'SD,countries'),
					array('name' => 'Sudan - Provinces', 'value' => 'SD,provinces'),
					array('name' => 'Suriname', 'value' => 'SR,countries'),
					array('name' => 'Suriname - Provinces', 'value' => 'SR,provinces'),
					array('name' => 'Svalbard and Jan Mayen', 'value' => 'SJ,countries'),
					array('name' => 'Svalbard and Jan Mayen - Provinces', 'value' => 'SJ,provinces'),
					array('name' => 'Swaziland', 'value' => 'SZ,countries'),
					array('name' => 'Swaziland - Provinces', 'value' => 'SZ,provinces'),
					array('name' => 'Sweden', 'value' => 'SE,countries'),
					array('name' => 'Sweden - Provinces', 'value' => 'SE,provinces'),
					array('name' => 'Switzerland', 'value' => 'CH,countries'),
					array('name' => 'Switzerland - Provinces', 'value' => 'CH,provinces'),
					array('name' => 'Syrian Arab Republic', 'value' => 'SY,countries'),
					array('name' => 'Syrian Arab Republic - Provinces', 'value' => 'SY,provinces'),
					array('name' => 'Taiwan, Province of China', 'value' => 'TW,countries'),
					array('name' => 'Taiwan, Province of China - Provinces', 'value' => 'TW,provinces'),
					array('name' => 'Tajikistan', 'value' => 'TJ,countries'),
					array('name' => 'Tajikistan - Provinces', 'value' => 'TJ,provinces'),
					array('name' => 'Tanzania, United Republic of', 'value' => 'TZ,countries'),
					array('name' => 'Tanzania, United Republic of - Provinces', 'value' => 'TZ,provinces'),
					array('name' => 'Thailand', 'value' => 'TH,countries'),
					array('name' => 'Thailand - Provinces', 'value' => 'TH,provinces'),
					array('name' => 'Timor-Leste', 'value' => 'TL,countries'),
					array('name' => 'Timor-Leste - Provinces', 'value' => 'TL,provinces'),
					array('name' => 'Togo', 'value' => 'TG,countries'),
					array('name' => 'Togo - Provinces', 'value' => 'TG,provinces'),
					array('name' => 'Tokelau', 'value' => 'TK,countries'),
					array('name' => 'Tokelau - Provinces', 'value' => 'TK,provinces'),
					array('name' => 'Tonga', 'value' => 'TO,countries'),
					array('name' => 'Tonga - Provinces', 'value' => 'TO,provinces'),
					array('name' => 'Trinidad and Tobago', 'value' => 'TT,countries'),
					array('name' => 'Trinidad and Tobago - Provinces', 'value' => 'TT,provinces'),
					array('name' => 'Tunisia', 'value' => 'TN,countries'),
					array('name' => 'Tunisia - Provinces', 'value' => 'TN,provinces'),
					array('name' => 'Turkey', 'value' => 'TR,countries'),
					array('name' => 'Turkey - Provinces', 'value' => 'TR,provinces'),
					array('name' => 'Turkmenistan', 'value' => 'TM,countries'),
					array('name' => 'Turkmenistan - Provinces', 'value' => 'TM,provinces'),
					array('name' => 'Turks and Caicos Islands', 'value' => 'TC,countries'),
					array('name' => 'Turks and Caicos Islands - Provinces', 'value' => 'TC,provinces'),
					array('name' => 'Tuvalu', 'value' => 'TV,countries'),
					array('name' => 'Tuvalu - Provinces', 'value' => 'TV,provinces'),
					array('name' => 'Uganda', 'value' => 'UG,countries'),
					array('name' => 'Uganda - Provinces', 'value' => 'UG,provinces'),
					array('name' => 'Ukraine', 'value' => 'UA,countries'),
					array('name' => 'Ukraine - Provinces', 'value' => 'UA,provinces'),
					array('name' => 'United Arab Emirates', 'value' => 'AE,countries'),
					array('name' => 'United Arab Emirates - Provinces', 'value' => 'AE,provinces'),
					array('name' => 'United Kingdom', 'value' => 'GB,countries'),
					array('name' => 'United Kingdom - Provinces', 'value' => 'GB,provinces'),
					array('name' => 'United States', 'value' => 'US,countries'),
					array('name' => 'United States - Provinces', 'value' => 'US,provinces'),
					array('name' => 'United States Minor Outlying Islands', 'value' => 'UM,countries'),
					array('name' => 'United States Minor Outlying Islands - Provinces', 'value' => 'UM,provinces'),
					array('name' => 'Uruguay', 'value' => 'UY,countries'),
					array('name' => 'Uruguay - Provinces', 'value' => 'UY,provinces'),
					array('name' => 'Uzbekistan', 'value' => 'UZ,countries'),
					array('name' => 'Uzbekistan - Provinces', 'value' => 'UZ,provinces'),
					array('name' => 'Vanuatu', 'value' => 'VU,countries'),
					array('name' => 'Vanuatu - Provinces', 'value' => 'VU,provinces'),
					array('name' => 'Venezuela, Bolivarian Republic of', 'value' => 'VE,countries'),
					array('name' => 'Venezuela, Bolivarian Republic of - Provinces', 'value' => 'VE,provinces'),
					array('name' => 'Viet Nam', 'value' => 'VN,countries'),
					array('name' => 'Viet Nam - Provinces', 'value' => 'VN,provinces'),
					array('name' => 'Virgin Islands, British', 'value' => 'VG,countries'),
					array('name' => 'Virgin Islands, British - Provinces', 'value' => 'VG,provinces'),
					array('name' => 'Virgin Islands, U.S.', 'value' => 'VI,countries'),
					array('name' => 'Virgin Islands, U.S. - Provinces', 'value' => 'VI,provinces'),
					array('name' => 'Wallis and Futuna', 'value' => 'WF,countries'),
					array('name' => 'Wallis and Futuna - Provinces', 'value' => 'WF,provinces'),
					array('name' => 'Western Sahara', 'value' => 'EH,countries'),
					array('name' => 'Western Sahara - Provinces', 'value' => 'EH,provinces'),
					array('name' => 'Yemen', 'value' => 'YE,countries'),
					array('name' => 'Yemen - Provinces', 'value' => 'YE,provinces'),
					array('name' => 'Zambia', 'value' => 'ZM,countries'),
					array('name' => 'Zambia - Provinces', 'value' => 'ZM,provinces'),
					array('name' => 'Zimbabwe', 'value' => 'ZW,countries'),
					array('name' => 'Zimbabwe - Provinces', 'value' => 'ZW,provinces'),


					
				); ?>
                <select name="<?php echo $name; ?>" id="<?php echo $name; ?>" <?php if($onchange!="") echo 'onchange="'.$onchange.'"'; ?> >
                <?php
				foreach ($regions as $region) { ?>				
                <option value="<?php echo $region['value']; ?>" <?php if($selected==$region['value']) echo "selected='selected'"; ?> ><?php echo $region['name']; ?></option>
                <?php } ?>
                </select>
                <?php
                
                }



//Retina Icons
$imap_wp_version =  floatval( get_bloginfo( 'version' ) );

if($imap_wp_version < 3.8) {
	add_action( 'admin_head', 'i_world_map_post_type_icon' );
}

else {

	add_action('admin_head', 'i_world_map_post_type_font_icon');

}

function i_world_map_post_type_font_icon() {
?>

		<style> 
			#adminmenu #toplevel_page_i_world_map_menu div.wp-menu-image:before { content: "\f319"; }
		</style>


<?php
}

//filter for css empty
function iwm_array_empty($var){
  return ($var !== NULL && $var !== FALSE && $var !== '');
}

 
function i_world_map_post_type_icon() {
    ?>
    <style>
        /* Admin Menu - 16px */
        #toplevel_page_i_world_map_menu .wp-menu-image {
            background: url('<?php echo plugins_url('interactive-world-maps/imgs/icon-16-sprite.png'); ?>') no-repeat 6px 6px !important;
        }
        #toplevel_page_i_world_map_menu:hover .wp-menu-image, #toplevel_page_i_world_map_menu.wp-has-current-submenu .wp-menu-image {
            background-position: 6px -26px !important;
        }

        /* Option Screen - 32px */
        #interactive-world-maps.icon32 {
                background-image: url('<?php echo plugins_url('interactive-world-maps/imgs/icon-32.png'); ?>') !important;
        }
       
        @media
        only screen and (-webkit-min-device-pixel-ratio: 1.5),
        only screen and (   min--moz-device-pixel-ratio: 1.5),
        only screen and (     -o-min-device-pixel-ratio: 3/2),
        only screen and (        min-device-pixel-ratio: 1.5),
        only screen and (                min-resolution: 1.5dppx) {
             
            /* Admin Menu - 16px @2x */
           #toplevel_page_i_world_map_menu .wp-menu-image {
                background-image: url('<?php echo plugins_url('interactive-world-maps/imgs/icon-16-sprite_2x.png'); ?>') !important;
				background-size: 16px 48px !important;
            }

            /* Option Screen - 32px @2x */
            #interactive-world-maps.icon32 {
                background-image: url('<?php echo plugins_url('interactive-world-maps/imgs/icon-32_2x.png'); ?>') !important;
                -webkit-background-size: 32px 32px;
                -moz-background-size: 32px 32px;
                background-size: 32px 32px;
            }   
               
        }
    </style>
<?php } 


// VISUAL COMPOSER CLASS

class iwm_VCExtendAddonClass {
    function __construct() {
        // We safely integrate with VC with this hook
        add_action( 'init', array( $this, 'integrateWithVC' ) );

    }
 
    public function integrateWithVC() {
        // Check if Visual Composer is installed
        if ( !defined('WPB_VC_VERSION') || !function_exists('vc_map')) {
            // Display notice that Visual Compser is required
            // add_action('admin_notices', array( $this, 'showVcVersionNotice' ));
            return;
        }
 

        global $wpdb;
		global $table_name_imap;

		$maps_created = $wpdb->get_results("SELECT * FROM $table_name_imap", ARRAY_A);

		$maps = array();

		$maps[__('Please Select...','vc_extend')] = 0;
		
		foreach ($maps_created as $map) {
			$maps[$map['name']] = $map['id'];
		}

		$manage_url = get_admin_url().'admin.php?page=i_world_map_menu';

		if(function_exists('vc_map')) {

			vc_map( array(
            "name" => __("Interactive Map", 'vc_extend'),
            "description" => __("Insert map previously created", 'vc_extend'),
            "base" => "show-map",
            "class" => "",
            //"front_enqueue_css" => plugins_url('includes/visual_composer.css', __FILE__),
            "front_enqueue_js" => plugins_url('includes/visual_composer.js', __FILE__),
            "icon" => plugins_url('imgs/icon-32.png', __FILE__),
            "category" => __('Content', 'js_composer'),
            "params" => array(
                array(
                  "admin_label" => true,
                  "type" => "dropdown",
                  "holder" => "hidden",
                  "class" => "",
                  "heading" => __("Map to display", 'vc_extend'),
                  "param_name" => "id",
                  "value" => $maps,
                  "description" => __("Choose one of the previously created maps. <br> <a href='".$manage_url."' target='_blank'>Click here to go to your Manage Maps page</a>", 'vc_extend')
              )
            ),
           
        	));

		}

        
    }
}
// Finally initialize code
new iwm_VCExtendAddonClass();



//AJAX WORKAROUND
add_action( 'init', 'iwm_ajax_workaround' );
function iwm_ajax_workaround() {
	$iwmoptions = get_option('i-world-map-settings');
	if(isset($iwmoptions['ajax_enabled']) && $iwmoptions['ajax_enabled']==1) {

		global $wpdb;
		global $table_name_imap;

		$maps_created = $wpdb->get_results("SELECT * FROM $table_name_imap", ARRAY_A);
		
		foreach ($maps_created as $map) {
			build_i_world_map_exec($map['id'],'nulloutput',false,null);
		}

	}
}


//we add the iwm admin styles for the customizer, in case it contains widgets, it will display the correct icon
add_action('customize_register','iwm_admin_styles_custom', 11 );
function iwm_admin_styles_custom() {
    	wp_deregister_style( 'iwm-admin-style' );
		wp_register_style( 'iwm-admin-style', plugins_url( 'admin.css', __FILE__) , array() , false, 'all');
		wp_enqueue_style( 'iwm-admin-style' );
}


?>