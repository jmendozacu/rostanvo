<?php /* Smarty version 2.6.18, created on 2012-07-13 09:48:14
         compiled from request_new_password_form.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'request_new_password_form.tpl', 6, false),)), $this); ?>
<!-- request_new_password_form -->
<div class="LoginContent">
<div class="LoginMain">
<div class="LoginMainIn">
	
			<?php echo smarty_function_localize(array('str' => 'If you lost your password, just enter your username (email) and we will send you email with instructions how to reset your current password.'), $this);?>

			<?php echo "<div id=\"username\"></div>"; ?>
            <?php echo "<div id=\"lost_pw_captcha\"></div>"; ?>
			<?php echo "<div id=\"FormMessage\"></div>"; ?>
			<?php echo "<div id=\"SendButton\"></div>"; ?>
			<?php echo "<div id=\"backToLogin\"></div>"; ?>
			<div class="clear"></div>		
		
	
	<div class="SupportBrowsers">
	<div class="SupportStrap"><?php echo smarty_function_localize(array('str' => 'Best viewed in:'), $this);?>
</div>
	<a class="Firefox" title="Firefox"></a>
	<a class="Ie" title="Internet Explorer"></a>
	<a class="Safari" title="Safari"></a>
	<a class="Opera" title="Opera"></a>
	<a class="Chrome" title="Google Chrome"></a>	
	</div>
	
	
	<div class="clear"></div>
	
		</div>
	
	</div>
	</div>