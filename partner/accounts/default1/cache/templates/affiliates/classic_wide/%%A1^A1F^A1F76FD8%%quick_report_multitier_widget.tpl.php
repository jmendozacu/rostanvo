<?php /* Smarty version 2.6.18, created on 2012-07-13 09:49:06
         compiled from quick_report_multitier_widget.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'quick_report_multitier_widget.tpl', 7, false),)), $this); ?>
<!-- quick_report_multitier_widget -->

<div class="OverviewDataBox">
    <div class="OverviewDataBoxContent">
        <div class="OverviewHeader">
            <strong>
                <?php echo smarty_function_localize(array('str' => 'Commissions Multi-Tier'), $this);?>

            </strong>
            <?php echo "<div id=\"expandButton\"></div>"; ?>
        </div>
        <?php echo "<div id=\"content\"></div>"; ?>
        <br>
        <br>
        * <span class="NumberDataRed"><?php echo smarty_function_localize(array('str' => 'Refunds are marked with red color'), $this);?>
</span><br>
        ** <span class="NumberDataOrange"><?php echo smarty_function_localize(array('str' => 'Chargebacks are marked with orange color'), $this);?>
</span>
    </div>
</div>