<div class="col2">

	<?php include('ads/ads-management.php'); ?>

	<?php include('ads/ads-300x250.php'); ?>

	<div id="myTabs1" class="sideTabs">

			<ul class="mootabs_title">
			<li title="pop"><span>Popular</span></li>
			<li title="feat"><span>Latest</span></li>
            <li title="comm"><span>Comments</span></li>
			<?php if (function_exists('wp_tag_cloud')) { ?><li title="tagcloud"><span>Tags</span></li><?php } ?>
            <li title="sub"><span>Subscribe</span></li>
		</ul>

		<div class="mootabs_panel" id="pop">
			<ul class="list1">
				<?php if (function_exists('akpc_most_popular')) { akpc_most_popular($limit = 10); } ?>
			</ul>
        </div>

		<div id="feat" class="mootabs_panel">
	        <ul class="list2">
				<?php
					$featuredcat = get_option('premiumnews_featured_category'); // ID of the Featured Category
					$the_query = new WP_Query('category_name=' . $featuredcat . '&showposts=10&orderby=post_date&order=desc');
					while ($the_query->have_posts()) : $the_query->the_post(); $do_not_duplicate = $post->ID;
				?>
				<li><a title="Permanent Link to <?php the_title(); ?>" href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></li>
				<?php endwhile; ?>
			</ul>
		</div>

		<div class="mootabs_panel" id="comm">
			<ul class="list3">
				<?php if (function_exists('mdv_most_commented')) {  mdv_most_commented(10); } ?>
			</ul>
		</div>

		<?php if (function_exists('wp_tag_cloud')) { ?>

			<div class="mootabs_panel" id="tagcloud">

				<ul>
					<?php wp_tag_cloud('smallest=10&largest=18'); ?>
				</ul>

			</div>

		<?php } ?>

        <div class="mootabs_panel" id="sub">
	        <ul id="rss">
				<li><h2>Stay up to date</h2><a href="<?php bloginfo('rss2_url'); ?>"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/ico-rss.gif" alt="" /></a></li>
				<li><a href="<?php bloginfo('rss2_url'); ?>">Subscribe to the RSS Feed</a></li>
				<li><a href="http://www.feedburner.com/fb/a/emailverifySubmit?feedId=<?php $feedburner_id = get_option('premiumnews_feedburner_id'); echo $feedburner_id; ?>" 	target="_blank">Subscribe to the feed via email</a></li>
			</ul>
        </div>

	</div>

    <div class="fix" style="height:15px !important;"></div>

	<?php include('ads/ads-bottom.php'); ?>

	<?php if (function_exists('get_flickrrss')) { ?>

		<div class="flickr">
			<?php $flickr_url = get_option('premiumnews_flickr_url'); ?>
			<h2>Our Flickr Photos <span class="flickr-ar"><a href="<?php echo "$flickr_url"; ?>">- See all photos</a></span></h2>
			<?php get_flickrrss(); ?>
		</div><!--/flickr-->

	<?php } ?>

	<div class="fix"></div>

	<div class="subcol fl hl3">

		<?php if (function_exists('dynamic_sidebar') && dynamic_sidebar(1) ) : else : ?>

			<div class="widget">

			</div><!--/widget-->

		<?php endif; ?>

	</div><!--/subcol-->

	<div class="subcol fr hl3">

		<?php if (function_exists('dynamic_sidebar') && dynamic_sidebar(2) ) : else : ?>

			<div class="widget">


			</div><!--/widget-->

		<?php endif; ?>

	</div><!--/subcol-->

</div><!--/col2-->
