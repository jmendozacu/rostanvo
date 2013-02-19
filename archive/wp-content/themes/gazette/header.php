<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">

    <title>
		<?php if ( is_home() ) { ?><? bloginfo('name'); ?>&nbsp;|&nbsp;<?php bloginfo('description'); ?><?php } ?>
		<?php if ( is_search() ) { ?><? bloginfo('name'); ?>&nbsp;|&nbsp;Search Results<?php } ?>
		<?php if ( is_single() ) { ?><?php wp_title(''); ?>&nbsp;|&nbsp;<? bloginfo('name'); ?><?php } ?>
		<?php if ( is_page() ) { ?><? bloginfo('name'); ?>&nbsp;|&nbsp;<?php wp_title(''); ?><?php } ?>
		<?php if ( is_category() ) { ?><? bloginfo('name'); ?>&nbsp;|&nbsp;Archive&nbsp;|&nbsp;<?php single_cat_title(); ?><?php } ?>
		<?php if ( is_month() ) { ?><? bloginfo('name'); ?>&nbsp;|&nbsp;Archive&nbsp;|&nbsp;<?php the_time('F'); ?><?php } ?>
		<?php if (function_exists('is_tag')) { if ( is_tag() ) { ?><? bloginfo('name'); ?>&nbsp;|&nbsp;Tag Archive&nbsp;|&nbsp;<?php  single_tag_title("", true); } } ?>
    </title>

	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

	<link rel="stylesheet" type="text/css"  href="<?php bloginfo('stylesheet_url'); ?>" media="screen" />

	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

	<?php wp_head(); ?>

	<!--[if lte IE 6]>
	<script defer type="text/javascript" src="<?php bloginfo('template_directory'); ?>/images/pngfix.js"></script>
	<![endif]-->
	
	<?php include(TEMPLATEPATH . '/includes/stylesheet.php'); ?>	

	<script src="<?php bloginfo('template_directory'); ?>/includes/js/mootools.v1.11.js" type="text/javascript"></script>
    <script src="<?php bloginfo('template_directory'); ?>/includes/js/jd.gallery.js" type="text/javascript"></script>
    <script src="<?php bloginfo('template_directory'); ?>/includes/js/jd.gallery.transitions.js" type="text/javascript"></script>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/includes/js/mootabs1.2.js"></script>
	<script type="text/javascript" charset="utf-8">
		window.addEvent("domready", init);
		function init() {
			myTabs = new mootabs("myTabs1");
		}			
	</script>
	
    <link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/slideshow.css" type="text/css" media="screen" />

	<script type="text/javascript">

		sfHover = function() {
			var sfEls = document.getElementById("nav2").getElementsByTagName("LI");
			for (var i=0; i<sfEls.length; i++) {
				sfEls[i].onmouseover=function() {
					this.className+=" sfhover";
				}
				sfEls[i].onmouseout=function() {
					this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
				}
			}
		}
		if (window.attachEvent) window.attachEvent("onload", sfHover);
	
	</script>

</head>

<body>

<?php if ( is_home() ) { $GLOBALS['home'] = true; } ?>

<div id="page">

<div id="nav"> <!-- START TOP NAVIGATION BAR -->
	
		<div id="nav-left">
	
			<ul>
            
            	
				<?php wp_list_pages('depth=1&sort_column=menu_order&title_li=' . __('') . '' ); ?>		
			
			</ul>
		
		</div><!--/nav-left -->

		<div id="nav-right">		
		
			<form method="get" id="searchform" action="<?php bloginfo('home'); ?>/">
				
				<div id="search">
					<input type="text" value="Enter your search keywords here..." onclick="this.value='';" name="s" id="s" />
					<input name="" type="image" src="<?php bloginfo('stylesheet_directory'); ?>/styles/<?php echo "$style_path"; ?>/search.gif" value="Go" class="btn" />
				</div><!--/search -->
				
			</form>
		
		</div><!--/nav-right -->
		
	</div><!--/nav-->
	
	<div class="fix"></div>
	
	<div id="header"><!-- START LOGO LEVEL WITH RSS FEED -->
		
		<h1><a href="<?php echo get_option('home'); ?>/" title="<? bloginfo('name'); ?>"><img src="<?php bloginfo('stylesheet_directory'); ?>/styles/<?php echo "$style_path"; ?>/logo.gif" alt="" /></a></h1>
		
		<div id="topbanner">
			
			<?php
				
				// Get banner code //
				$banner_img = get_option('premiumnews_banner_image');
				$banner_url = get_option('premiumnews_banner_url');
			
			?>
			
			<a <?php do_action('premiumnews_external_ad_link'); ?> href="<?php echo "$banner_url"; ?>"><img src="<?php echo "$banner_img"; ?>" alt="" /></a>
			
		</div><!--/rss-->
		
	</div><!--/header -->
    
    	
	
	
	
	<div id="columns"><!-- START MAIN CONTENT COLUMNS -->