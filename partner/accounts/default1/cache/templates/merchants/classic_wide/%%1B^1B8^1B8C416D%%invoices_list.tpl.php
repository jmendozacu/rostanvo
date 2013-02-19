<?php /* Smarty version 2.6.18, created on 2012-07-11 05:36:07
         compiled from invoices_list.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'invoices_list.tpl', 4, false),)), $this); ?>
<!-- invoices_list -->
<div class="InvoicesList">
    <div class="actualPymentInfo">
    <?php echo smarty_function_localize(array('str' => 'License valid from'), $this);?>
: <?php echo "<div id=\"validFrom\" class=\"InlineBlockInvoice\"></div>"; ?> <?php echo smarty_function_localize(array('str' => 'to'), $this);?>
: <?php echo "<div id=\"validTo\" class=\"InlineBlockInvoice\"></div>"; ?> .<?php echo smarty_function_localize(array('str' => 'Last payment recieved at'), $this);?>
 <?php echo "<div id=\"lastPaymentCreated\" class=\"InlineBlockInvoice\"></div>"; ?>, (<?php echo smarty_function_localize(array('str' => 'Billing date'), $this);?>
 <?php echo "<div id=\"billingDate\" class=\"InlineBlockInvoice\"></div>"; ?>)
    </div>
    <div class="clear"></div>
    <?php echo "<div id=\"filter\"></div>"; ?>
    <?php echo "<div id=\"grid\"></div>"; ?>
</div>