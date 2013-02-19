<?php /* Smarty version 2.6.18, created on 2012-05-29 04:02:25
         compiled from recurring_commissions.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'recurring_commissions.tpl', 3, false),)), $this); ?>
<!-- recurring_commissions -->
<fieldset>
<legend><?php echo smarty_function_localize(array('str' => 'Recurring Commissions'), $this);?>
</legend>
<?php echo "<div id=\"RecurrenceType\" class=\"RecurrenceType\"></div>"; ?>
<?php echo "<div id=\"RecurringCommissionsPanel\"></div>"; ?>
</fieldset>