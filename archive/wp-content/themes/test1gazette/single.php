<?php get_header(); ?>

		<div class="col1">

		<?php if (have_posts()) : ?>

			<?php while (have_posts()) : the_post(); ?>

				<div id="archivebox">

						<h3><em>Categorized |</em> <?php the_category(', ') ?></h3>
						<?php if (function_exists('the_tags')) { ?><div class="singletags"><?php the_tags('Tags : ', ', ', ''); ?></div><?php } ?>

				</div><!--/archivebox-->

				<div class="post-alt blog" id="post-<?php the_ID(); ?>">

					<h2><a title="Permanent Link to <?php the_title(); ?>" href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>

					<div class="entry">
						<?php the_content('<span class="continue">Continue Reading</span>'); ?>
					</div>

				</div><!--/post-->



		<?php endwhile; ?>



	<?php endif; ?>

		</div><!--/col1-->

<?php get_sidebar(); ?>

<?php get_footer(); ?>