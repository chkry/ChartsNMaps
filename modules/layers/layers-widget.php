<?php  /**
 * Layers Stories Widget
 *
 * This file is used to register and display the Layers widget.
 * http://docs.layerswp.com/development-tutorials-layers-builder-widgets/
 * 
 * @package Layers
 * @since Layers 1.0.0
 */
if( !class_exists( 'Layers_IWM_Widget' ) && class_exists( 'Layers_Widget' ) ) {

// http://docs.layerswp.com/development-tutorials-layers-builder-widgets/#widget-class
class Layers_IWM_Widget extends Layers_Widget {
 
        /**
        *  1 - Widget construction
		* http://docs.layerswp.com/development-tutorials-layers-builder-widgets/#1-widget-construction
        */
        function Layers_IWM_Widget(){
			$this->widget_title = __( 'Interactive Map' , 'iwm' );
			$this->widget_id = 'iwm_layers_widget';
			
			/* Widget settings. */ 

			$widget_ops = array( 
				  'classname' => 'obox-layers-' . $this->widget_id .'-widget', 
				  'description' => __( 'Display a previously created map', 'iwm')
			);
			
			/* Widget control settings. */ 

			$control_ops = array( 
				  'width' => '660', 
				  'height' => NULL, 
				  'id_base' => 'layers-widget-' . $this->widget_id 
			);
			
			/* Create the widget. */ 
			
			parent::__construct( 'layers' . '-widget-' . $this->widget_id , $this->widget_title, $widget_ops, $control_ops );
			
			/* Setup Widget Defaults */
			$this->defaults = array (
				'title' => __( 'Map', 'iwm' ),
				'excerpt' => __( '', 'iwm' ),
				'design' => array(
					'layout' => 'layout-boxed',
					'textalign' => 'text-left',
					'gutter' => 'on',
					'background' => array(
						'position' => 'center',
						'repeat' => 'no-repeat'
					),
					'fonts' => array(
						'align' => 'text-left',
						'size' => 'medium',
						'color' => NULL,
						'shadow' => NULL
					)
				)
			);
			
		} // END main function
		
 		/**
        *  2 - Widget form
        * http://docs.layerswp.com/development-tutorials-layers-builder-widgets/#2-widget-form
        * We use regulage HTML here, it makes reading the widget much easier 
        * than if we used just php to echo all the HTML out.
        * 
        */
        function form( $instance ){
			
		// $instance Defaults
			$instance_defaults = $this->defaults;
		
			// If we have information in this widget, then ignore the defaults
			if( !empty( $instance ) ) $instance_defaults = array();
		
			// Parse $instance
			$instance = wp_parse_args( $instance, $instance_defaults );
		
			extract( $instance, EXTR_SKIP );
			
			// Design Bar Components
			$design_bar_components = apply_filters(
				  'layers_' . $this->widget_id . '_widget_design_bar_components' ,
					  array(
						'layout',
						'fonts',
						'background',
						'advanced'
					  )
			);
			
			// Design Bar Components
			// We reference the Post Widget here, but remove show_tags and replace it with
			// show_credit and show_topics to work with our custom post type taxonomy and meta
			// http://docs.layerswp.com/development-tutorials-layers-builder-widgets/#widget-form-design-bar
			
			
			// Instantiate the Design Bar
			$this->design_bar(
				'side', // CSS Class Name
				  array(
					 'name' => $this->get_field_name( 'design' ),
					 'id' => $this->get_field_id( 'design' ),
				  ), // Widget Object
				 $instance, // Widget Values
				 $design_bar_components // Standard Components
				 
			);
			
			// Build Content Form 
			// http://docs.layerswp.com/development-tutorials-layers-builder-widgets/#content-options-form
			?>
			
			<div class="layers-container-large">
				<?php
					$this->form_elements()->header( 
						   array(
							'title' =>  __( 'Map' , 'layerswp' ),
							'icon_class' => 'post'
						   ) 
					);
				?>
				<section class="layers-accordion-section layers-content">
					<div class="layers-row layers-push-bottom">
		
					<p class="layers-form-item">
						  <?php echo $this->form_elements()->input(
							  array(
								  'type' => 'text',
								  'name' => $this->get_field_name( 'title' ) ,
								  'id' => $this->get_field_id( 'title' ) ,
								  'placeholder' => __( 'Enter title here' , 'layerswp' ),
								  'value' => ( isset( $title ) ) ? $title : NULL ,
								  'class' => 'layers-text layers-large'
							  )
						  ); ?>
					</p>
					
					<p class="layers-form-item">
						  <?php echo $this->form_elements()->input(
							  array(
								  'type' => 'textarea',
								  'name' => $this->get_field_name( 'excerpt' ) ,
								  'id' => $this->get_field_id( 'excerpt' ) ,
								  'placeholder' => __( 'Short Excerpt' , 'layerswp' ),
								  'value' => ( isset( $excerpt ) ) ? $excerpt : NULL ,
								  'class' => 'layers-textarea layers-large'
							  )
						  ); ?>
					</p>
					
					<?php

					global $wpdb;
					global $table_name_imap;

					$maps_created = $wpdb->get_results("SELECT * FROM $table_name_imap", ARRAY_A);

					$maps = array();

					$maps[0] = __('-- Please Select --','iwm');
					
					foreach ($maps_created as $map) {
						$maps[$map['id']] = $map['name'];
					}

					?>
					
                   

                    <?php 
                    if(count($maps) == 1) {

                    	$manage_url = get_admin_url().'admin.php?page=i_world_map_menu';

                    	echo '<p class="howto">'.
                    	__('There are no maps to display. Please create a map first.','iwm')
                    	.'</p>';
                    	echo '<p><a href="'.$manage_url.'" target="_blank">'.
                    	__('Add New Map','iwm')
                    	.'</a>';

                    } 

                    else {

                    	?>


                    <p class="layers-form-item">
						<?php echo __( 'Show Map' , 'iwm' ); ?>
                        <?php echo $this->form_elements()->input(
							array(
								'type' => 'select',
								'name' => $this->get_field_name( 'id' ) ,
								'id' => $this->get_field_id( 'id' ) ,
								'value' => ( isset( $instance['id'] ) ) ? $instance['id'] : '0' ,
								'options' => $maps
							)
                    	); ?>
                    </p>

                    <?php
                		}
                    ?>
					
					</div>
				</section>
			</div>
		
		<?php 
		} // Form
		
        /**
        *  3 - Update Options
		*  http://docs.layerswp.com/development-tutorials-layers-builder-widgets/#3-update-controls 
        */    
 
        function update($new_instance, $old_instance) {
		  if ( isset( $this->checkboxes ) ) {
			foreach( $this->checkboxes as $cb ) {
			  if( isset( $old_instance[ $cb ] ) ) {
				$old_instance[ $cb ] = strip_tags( $new_instance[ $cb ] );
			  }
			} // foreach checkboxes
		  } // if checkboxes
		
		  return $new_instance;
		} 
         
		
		/**
		*  4 - Widget front end display
		*  http://docs.layerswp.com/development-tutorials-layers-builder-widgets/#4-widget-front-end
		*/
		function widget( $args, $instance ) {
			
			// Turn $args array into variables.
			extract( $args );
			
			// $instance Defaults
			$instance_defaults = $this->defaults;
			
			// If we have information in this widget, then ignore the defaults
			if( !empty( $instance ) ) $instance_defaults = array();
			
			// Parse $instance
			$widget = wp_parse_args( $instance, $instance_defaults );
			
			// Apply Styling
			// http://docs.layerswp.com/development-tutorials-layers-builder-widgets/#colors-and-font-settings
			layers_inline_styles( '#' . $widget_id, 'background', array( 'background' => $widget['design'][ 'background' ] ) );
			layers_inline_styles( '#' . $widget_id, 'color', array( 'selectors' => array( '.section-title h3.heading' , '.section-title div.excerpt' ) , 'color' => $widget['design']['fonts'][ 'color' ] ) );
			layers_inline_styles( '#' . $widget_id, 'background', array( 'selectors' => array( '.thumbnail:not(.with-overlay) .thumbnail-body' ) , 'background' => array( 'color' => $this->check_and_return( $widget, 'design', 'column-background-color' ) ) ) );
			layers_inline_button_styles( '#' . $widget_id, 'button', array( 'selectors' => array( '.thumbnail-body a.button' ) ,'button' => $this->check_and_return( $widget, 'design', 'buttons' ) ) );
			// Apply the advanced widget styling
			$this->apply_widget_advanced_styling( $widget_id, $widget );
			

			
			// Begin query arguments
			// http://docs.layerswp.com/development-tutorials-layers-builder-widgets/#query-and-display-post-content
			
			$query_args = array();
			
			
			
			// Generate the widget container class
			// Do not edit
			$widget_container_class = array();
			$widget_container_class[] = 'widget row content-vertical-massive';
			$widget_container_class[] = $this->check_and_return( $widget , 'design', 'advanced', 'customclass' );
			$widget_container_class[] = $this->get_widget_spacing_class( $widget );
			$widget_container_class = implode( ' ', apply_filters( 'layers_post_widget_container_class' , $widget_container_class ) ); 


			/**
			*  Widget Markup
			*  http://docs.layerswp.com/development-tutorials-layers-builder-widgets/#widget-html
			*/
			?> 
            
			<section class=" <?php echo $widget_container_class; ?>" id="<?php echo $widget_id; ?>">
				<?php if( '' != $this->check_and_return( $widget , 'title' ) ||'' != $this->check_and_return( $widget , 'excerpt' ) ) { ?>
					<div class="container clearfix">	
						<?php 
					    // Generate the Section Title Classes
						$section_title_class = array();
						$section_title_class[] = 'section-title clearfix';
						$section_title_class[] = $this->check_and_return( $widget , 'design', 'fonts', 'size' );
						$section_title_class[] = $this->check_and_return( $widget , 'design', 'fonts', 'align' );
						$section_title_class[] = ( $this->check_and_return( $widget, 'design', 'background' , 'color' ) && 'dark' == layers_is_light_or_dark( $this->check_and_return( $widget, 'design', 'background' , 'color' ) ) ? 'invert' : '' );
						$section_title_class = implode( ' ', $section_title_class ); ?>
                        
						<div class="<?php echo $section_title_class; ?>">
							<?php if( '' != $widget['title'] ) { ?>
								<h3 class="heading"><?php echo esc_html( $widget['title'] ); ?></h3>
							<?php } ?>
							<?php if( '' != $widget['excerpt'] ) { ?>
								<div class="excerpt"><?php echo $widget['excerpt']; ?></div>
							<?php } ?>
						</div>    
					</div>
				<?php }
				
				// Begin Post Structure ?>	
                <div class="row <?php echo $this->get_widget_layout_class( $widget ); ?> ">
					

					<?php 
					echo do_shortcode('[show-map id="'.$instance['id'].'"]');
					?>
				
				</div>
				
			</section>

			
		<?php }
		
    } // Class
 
    // Register our widget
	// http://docs.layerswp.com/development-tutorials-layers-builder-widgets/#register-and-initialize
    register_widget('Layers_IWM_Widget'); 
} 
