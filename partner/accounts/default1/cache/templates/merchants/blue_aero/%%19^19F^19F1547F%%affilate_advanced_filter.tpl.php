<?php /* Smarty version 2.6.18, created on 2012-05-29 04:00:04
         compiled from affilate_advanced_filter.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'affilate_advanced_filter.tpl', 6, false),)), $this); ?>
<!-- affiliate_advanced_filter -->

			<div class="AffiliatesFilter">
			
		    <fieldset class="Filter">       
            <legend><?php echo smarty_function_localize(array('str' => 'Date joined'), $this);?>
</legend>
            <div class="Resize">
            <?php echo "<div id=\"dateinserted\"></div>"; ?>
            </div>
       		</fieldset>
        
        	<fieldset class="Filter">
            <legend><?php echo smarty_function_localize(array('str' => 'Affiliate status'), $this);?>
</legend>
            <div class="Resize">
            <?php echo "<div id=\"rstatus\"></div>"; ?>
            </div>
       		</fieldset>
        
        	<fieldset class="Filter">
            <legend><?php echo smarty_function_localize(array('str' => 'Custom filter'), $this);?>
</legend>
            <div class="Resize">
            <?php echo "<div id=\"custom\"></div>"; ?>
            </div>
       		</fieldset>
       		
       		<fieldset class="Filter">
            <legend><?php echo smarty_function_localize(array('str' => 'Statistics date range'), $this);?>
</legend>
            <div class="Resize">
            <?php echo "<div id=\"statsdaterange\"></div>"; ?>
            </div>
            </fieldset>
        
       		</div>
        
      		<div style="clear: both;"></div>