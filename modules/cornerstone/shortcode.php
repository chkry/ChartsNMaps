<?php

/**
 * Shortcode definition
 */

?>

<div style="<?php echo $atts['style'];?>" class="<?php echo $atts['class'];?>" id="<?php echo $atts['id'];?>" >
<?php if ( $atts['heading'] ) : ?>
	<h4 class="iwm-heading" style="color:<?php echo $atts['heading_color'];?>; text-align:<?php echo $atts['align'];?>"> <?php echo $atts['heading']; ?></h4>
<?php endif; ?>
<div style="padding:<?php echo $atts['map_padding'];?>">
<?php echo do_shortcode('[show-map cornerstone="true" id="'.$atts['iwmid'].'"]'); ?>
</div>
</div>
