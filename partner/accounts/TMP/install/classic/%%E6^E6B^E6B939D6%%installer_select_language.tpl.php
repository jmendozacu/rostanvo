<?php /* Smarty version 2.6.18, created on 2012-05-29 03:54:53
         compiled from installer_select_language.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'installer_select_language.tpl', 3, false),)), $this); ?>
<!-- installer_select_language -->
<fieldset>
    <legend><?php echo smarty_function_localize(array('str' => 'Choose Language'), $this);?>
</legend>
    <?php echo smarty_function_localize(array('str' => 'Please select the language to use during the Post Affiliate Pro installation steps'), $this);?>

    <?php echo "<div id=\"SelectLanguage\" class=\"LanguageListBox\"></div>"; ?>
</fieldset>
<div class="Note">
    <?php echo smarty_function_localize(array('str' => 'Note: If you need multilanguage support, you can add additional languages once application will be installed.'), $this);?>

</div> 
<div class="cleaner"></div>