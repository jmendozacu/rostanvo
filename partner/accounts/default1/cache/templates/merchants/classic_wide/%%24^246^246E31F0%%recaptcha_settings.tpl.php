<?php /* Smarty version 2.6.18, created on 2012-07-11 05:36:47
         compiled from recaptcha_settings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'recaptcha_settings.tpl', 4, false),)), $this); ?>
<!--    recaptcha_settings     -->

<fieldset>
    <legend><?php echo smarty_function_localize(array('str' => 'ReCaptcha settings'), $this);?>
</legend>
    <br/>
    <?php echo "<div id=\"recaptcha_enabled\"></div>"; ?>
    <?php echo "<div id=\"recaptcha_public_key\"></div>"; ?>
    <?php echo "<div id=\"recaptcha_private_key\"></div>"; ?>
    <?php echo "<div id=\"recaptcha_theme\"></div>"; ?>
</fieldset>