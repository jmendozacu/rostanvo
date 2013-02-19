<?php $no_show = get_option('premiumnews_not_mpu'); ?>

<?php if ( !$no_show ) { ?>

<?php $show_home_only = get_option('premiumnews_home_only'); ?>

<?php 

	if ( $show_home_only ) { 
	
	if ( $GLOBALS['home'] ) {

?>

<div id="mpu_banner">
  
	<?php
				
		// Get block code //
		$block_img = get_option('premiumnews_block_image');
		$block_url = get_option('premiumnews_block_url');
			
	?>
			
	<a <?php do_action('premiumnews_external_ad_link'); ?> href="<?php echo "$block_url"; ?>"><img src="<?php echo "$block_img"; ?>" alt="" /></a>

</div>

<?php } else { include('ads-top.php'); } } else { ?>

<div id="mpu_banner">
  
	<?php
				
		// Get block code //
		$block_img = get_option('premiumnews_block_image');
		$block_url = get_option('premiumnews_block_url');
			
	?>
			
	<a <?php do_action('premiumnews_external_ad_link'); ?> href="<?php echo "$block_url"; ?>"><img src="<?php echo "$block_img"; ?>" alt="" /></a>

</div>

<?php } } ?>