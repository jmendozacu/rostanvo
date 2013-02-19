<?php get_header(); ?>

		<div class="col1">

			<?php include(TEMPLATEPATH . '/includes/featured.php'); ?>

            <?php
				$showplace = get_option('premiumnews_show_video_feat');
				if ( $showplace ) { include(TEMPLATEPATH . '/includes/video.php'); }
			?>

			<?php
				
				$layout = get_option('premiumnews_layout');

				include('layouts/'.$layout);
				
			?>
            
            <?php
				$showplace = get_option('premiumnews_show_video_feat');
				if ( !$showplace ) { include(TEMPLATEPATH . '/includes/video.php'); }
			?>

		</div><!--/col1-->

<?php get_sidebar(); ?>

<?php get_footer(); ?>