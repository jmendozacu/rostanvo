<?php /* Smarty version 2.6.18, created on 2012-07-11 05:34:37
         compiled from accounts_fields.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'accounts_fields.tpl', 3, false),)), $this); ?>
<!-- accounts_fields -->
<div class="TabDescription">
<h3><?php echo smarty_function_localize(array('str' => 'Available fields for account'), $this);?>
</h3>
<?php echo smarty_function_localize(array('str' => 'Choose which fields you want to use to store data for your network accounts. Some fields are mandatory, and you have up to 25 optional fields for which you can decide what information they will keep, if they will be optional, mandatory, or not displayed at all. These fields will be displayed in network account editation.'), $this);?>

</div>

<div class="ConfigFieldsForm">

<fieldset>
<legend><?php echo smarty_function_localize(array('str' => 'Available fields'), $this);?>
</legend>
<?php echo smarty_function_localize(array('str' => 'The gray fields are fixed, and they are always present. You cannot configure them.<br/>You can use up to 25 custom fields to get the information from your network accounts. Just click on the name, type or status to change the field attributes.'), $this);?>

<br/>
<br/>
<div style="font-weight: bold; margin: 0px">
    <div style="width: 100px; float: left; height:30px"><?php echo smarty_function_localize(array('str' => 'Code'), $this);?>
</div>
    <div style="width: 140px; float: left; height:30px"><?php echo smarty_function_localize(array('str' => 'Name'), $this);?>
</div>
    <div style="width: 280px; float: left; height:30px"><?php echo smarty_function_localize(array('str' => 'Status'), $this);?>
</div>
    <div style="width: 100px; float: left; height:30px"><?php echo smarty_function_localize(array('str' => 'Type'), $this);?>
</div>
    <div style="clear: both"></div>
</div>

<?php echo "<div id=\"username\"></div>"; ?>
<?php echo "<div id=\"agreement\"></div>"; ?>
<?php echo "<div id=\"data1\"></div>"; ?>
<?php echo "<div id=\"data2\"></div>"; ?>
<?php echo "<div id=\"data3\"></div>"; ?>
<?php echo "<div id=\"data4\"></div>"; ?>
<?php echo "<div id=\"data5\"></div>"; ?>
<?php echo "<div id=\"data6\"></div>"; ?>
<?php echo "<div id=\"data7\"></div>"; ?>
<?php echo "<div id=\"data8\"></div>"; ?>
<?php echo "<div id=\"data9\"></div>"; ?>
<?php echo "<div id=\"data10\"></div>"; ?>
<?php echo "<div id=\"data11\"></div>"; ?>
<?php echo "<div id=\"data12\"></div>"; ?>
<?php echo "<div id=\"data13\"></div>"; ?>
<?php echo "<div id=\"data14\"></div>"; ?>
<?php echo "<div id=\"data15\"></div>"; ?>
<?php echo "<div id=\"data16\"></div>"; ?>
<?php echo "<div id=\"data17\"></div>"; ?>
<?php echo "<div id=\"data18\"></div>"; ?>
<?php echo "<div id=\"data19\"></div>"; ?>
<?php echo "<div id=\"data20\"></div>"; ?>
<?php echo "<div id=\"data21\"></div>"; ?>
<?php echo "<div id=\"data22\"></div>"; ?>
<?php echo "<div id=\"data23\"></div>"; ?>
<?php echo "<div id=\"data24\"></div>"; ?>
<?php echo "<div id=\"data25\"></div>"; ?>

</fieldset>
</div>
<?php echo "<div id=\"saveButton\"></div>"; ?>