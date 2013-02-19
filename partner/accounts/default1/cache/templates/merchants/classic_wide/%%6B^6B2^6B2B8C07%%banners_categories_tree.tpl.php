<?php /* Smarty version 2.6.18, created on 2012-07-11 05:35:05
         compiled from banners_categories_tree.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'banners_categories_tree.tpl', 4, false),)), $this); ?>
<!-- banners_categories_tree -->
<div class="AffiliateMenu">
    <fieldset>
        <legend><?php echo smarty_function_localize(array('str' => 'Categories'), $this);?>
</legend>
        <?php echo smarty_function_localize(array('str' => 'You can modify banner categories by dragging them.'), $this);?>
<br/>
        <?php echo smarty_function_localize(array('str' => 'Items can be removed by dragging to Trash'), $this);?>

        <hr>
        <?php echo "<div id=\"menuTree\"></div>"; ?>
        <hr>
        <?php echo "<div id=\"Trash\"></div>"; ?>
        <?php echo "<div id=\"WarningMessage\"></div>"; ?>
        <?php echo "<div id=\"SaveButton\"></div>"; ?>
        <?php echo "<div id=\"NewButton\"></div>"; ?>
    </fieldset>
</div>

<div class="clear"/>