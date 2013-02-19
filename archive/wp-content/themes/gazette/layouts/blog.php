<div class="box">

	<?php	
		include(TEMPLATEPATH . '/includes/version.php');
		
		$the_query = new WP_Query('cat=-'. $ex_feat . ',-' . $ex_vid . '&showposts=' . $showposts . '&orderby=post_date&order=desc');
		
		$counter = 0;
				
		while ($the_query->have_posts()) : $the_query->the_post(); $do_not_duplicate = $post->ID;
	?>
	
		<?php $counter++; ?>

		<div class="post-alt blog" <?php if ( ($counter == 4) ) { echo 'style="background:none !important;margin-bottom:0 !important;"'; ?><?php } ?>>

			<?php if ( get_post_meta($post->ID, 'thumb', true) ) { ?> <!-- DISPLAYS THE IMAGE URL SPECIFIED IN THE CUSTOM FIELD -->
				
				<img src="<?php echo get_post_meta($post->ID, "thumb", $single = true); ?>" alt="" class="th" />			
				
			<?php } else { ?> <!-- DISPLAY THE DEFAULT IMAGE, IF CUSTOM FIELD HAS NOT BEEN COMPLETED -->
				
				<img src="<?php bloginfo('template_directory'); ?>/images/no-img-thumb.jpg" alt="" class="th" />
				
			<?php } ?> 		
			
			<h2><a title="Permanent Link to <?php the_title(); ?>" href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>
			<h3 class="post_date">Posted on <?php the_time('d F Y'); ?></h3>
            <hr style="clear:both;" />

			<div class="entry">
				<?php the_content('<span class="continue">Continue Reading</span>'); ?> 
			</div>

			 <h3 class="posted">Posted in <?php the_category(', ') ?><span class="comments"><?php comments_popup_link('Comments (0)', 'Comments (1)', 'Comments (%)'); ?></span></h3>
		
		</div><!--/post-->		

	<?php endwhile; ?>	
	
	<div class="fix"></div>
	
	<?php $archives_page = get_option('premiumnews_archives_page') . '/'; ?>
	
	<p class="ar hl3"><a href="<?php echo "$archives_page"; ?>" class="more">See more articles in the archive</a></p>

</div>