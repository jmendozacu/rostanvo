<?php /* Smarty version 2.6.18, created on 2012-07-13 09:28:48
         compiled from home_panel_content.stpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'home_panel_content.stpl', 4, false),array('modifier', 'number_span', 'home_panel_content.stpl', 36, false),array('modifier', 'currency_span', 'home_panel_content.stpl', 70, false),)), $this); ?>
<!-- home_panel_content -->

<?php if ($this->_tpl_vars['pendingTasks'] != false): ?>
<div class="StatsSectionTitle"><?php echo smarty_function_localize(array('str' => 'Tasks waiting for approval'), $this);?>
</div>
<table width="600" class="StatsSummaries">
  <tr class="light">
    <td align="left" width="20%" nowrap><a href="#Affiliate-Manager"><?php echo smarty_function_localize(array('str' => 'Affiliates'), $this);?>
</a></td>
    <td align="right"><span class="NumberData"><?php echo $this->_tpl_vars['pendingTasks']['affiliates']; ?>
</span></td>
    <td align="left" width="20%" nowrap><a href="#Transaction-Manager"><?php echo smarty_function_localize(array('str' => 'Commissions'), $this);?>
</a></td>
    <td align="right"><span class="NumberData"><?php echo $this->_tpl_vars['pendingTasks']['commissions']; ?>
</span></td>
  </tr>
  <tr class="dark">
    <td align="left" width="20%" nowrap><a href="#Direct-Links-Manager"><?php echo smarty_function_localize(array('str' => 'DirecLink Urls'), $this);?>
</a></td>
    <td align="right"><span class="NumberData"><?php echo $this->_tpl_vars['pendingTasks']['links']; ?>
</span></td>
    <td align="left" width="20%" nowrap><a href="#Mail-Outbox"><?php echo smarty_function_localize(array('str' => 'Unsent emails'), $this);?>
</a></td>
    <td align="right"><span class="NumberData"><?php echo $this->_tpl_vars['pendingTasks']['emails']; ?>
</span></td>
  </tr>  
</table>
<br/><br/>
<?php endif; ?>
<div class="StatsSectionTitle"><?php echo smarty_function_localize(array('str' => 'Traffic overview'), $this);?>
</div>
<table width="600" class="StatsSummaries">
  <tr class="gray" >
    <td class="white" rowspan="2"></td>
    <td align="center" width="16%" colspan="3"><?php echo smarty_function_localize(array('str' => 'Today'), $this);?>
</td>
    <td align="center" width="16%" colspan="3"><?php echo smarty_function_localize(array('str' => 'This month'), $this);?>
</td>
  </tr>
  <tr class="gray" >
    <td align="center"><?php echo smarty_function_localize(array('str' => 'raw'), $this);?>
</td>
    <td align="center" colspan="2"><?php echo smarty_function_localize(array('str' => 'unique'), $this);?>
</td>
    <td align="center" ><?php echo smarty_function_localize(array('str' => 'raw'), $this);?>
</td>
    <td align="center" colspan="2"><?php echo smarty_function_localize(array('str' => 'unique'), $this);?>
</td>
  </tr>
  <tr class="light">
    <td align="left" width="30%" nowrap><?php echo smarty_function_localize(array('str' => 'Impressions'), $this);?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayImpressions']->count->raw)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
    <td align="center" colspan="2"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayImpressions']->count->unique)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthImpressions']->count->raw)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
    <td align="center" colspan="2"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthImpressions']->count->unique)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
  </tr>
  <tr class="dark">
    <td align="left" width="30%" nowrap><?php echo smarty_function_localize(array('str' => 'Clicks'), $this);?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayClicks']->count->raw)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
    <td align="center" colspan="2"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayClicks']->count->unique)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthClicks']->count->raw)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
    <td align="center" colspan="2"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthClicks']->count->unique)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
  </tr>
  

  <tr class="gray" >
    <td align="center"></td>
    <td align="center"><?php echo smarty_function_localize(array('str' => 'approved'), $this);?>
</td>
    <td align="center"><?php echo smarty_function_localize(array('str' => 'paid'), $this);?>
</td>
    <td align="center"><?php echo smarty_function_localize(array('str' => 'pending'), $this);?>
</td>
    <td align="center"><?php echo smarty_function_localize(array('str' => 'approved'), $this);?>
</td>
    <td align="center"><?php echo smarty_function_localize(array('str' => 'paid'), $this);?>
</td>
    <td align="center"><?php echo smarty_function_localize(array('str' => 'pending'), $this);?>
</td>
  </tr>
  <tr class="light">
    <td align="left" width="30%" nowrap><?php echo smarty_function_localize(array('str' => 'Number of Sales'), $this);?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todaySales']->count->approved)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todaySales']->count->paid)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todaySales']->count->pending)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthSales']->count->approved)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthSales']->count->paid)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthSales']->count->pending)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
  </tr>
  <tr class="dark">
    <td align="left" width="30%" nowrap><?php echo smarty_function_localize(array('str' => 'Total cost of Sales'), $this);?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todaySales']->totalCost->approved)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todaySales']->totalCost->paid)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todaySales']->totalCost->pending)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthSales']->totalCost->approved)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthSales']->totalCost->paid)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthSales']->totalCost->pending)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
  </tr>
  <?php if ($this->_tpl_vars['actionCommissionsEnabled'] == 'Y'): ?>
      <tr class="light">
        <td align="left" width="30%" nowrap><?php echo smarty_function_localize(array('str' => 'Number of Actions'), $this);?>
</td>
        <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayActionCommissions']->count->approved)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
        <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayActionCommissions']->count->paid)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
        <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayActionCommissions']->count->pending)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
        <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthActionCommissions']->count->approved)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
        <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthActionCommissions']->count->paid)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
        <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthActionCommissions']->count->pending)) ? $this->_run_mod_handler('number_span', true, $_tmp) : smarty_modifier_number_span($_tmp)); ?>
</td>
      </tr>
      <tr class="dark">
        <td align="left" width="30%" nowrap><?php echo smarty_function_localize(array('str' => 'Total cost of Actions'), $this);?>
</td>
        <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayActionCommissions']->totalCost->approved)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
        <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayActionCommissions']->totalCost->paid)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
        <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayActionCommissions']->totalCost->pending)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
        <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthActionCommissions']->totalCost->approved)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
        <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthActionCommissions']->totalCost->paid)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
        <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthActionCommissions']->totalCost->pending)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
      </tr>
  <?php endif; ?>
  <tr class="light">
    <td align="left" width="30%" nowrap><?php echo smarty_function_localize(array('str' => 'All Commissions'), $this);?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayCommissions']->commission->approved)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayCommissions']->commission->paid)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayCommissions']->commission->pending)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthCommissions']->commission->approved)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthCommissions']->commission->paid)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthCommissions']->commission->pending)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
  </tr>
  <tr class="dark">
    <td align="left" width="30%" nowrap><?php echo smarty_function_localize(array('str' => 'All Refunds'), $this);?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayRefunds']->commission->approved)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayRefunds']->commission->paid)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayRefunds']->commission->pending)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthRefunds']->commission->approved)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthRefunds']->commission->paid)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthRefunds']->commission->pending)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
  </tr>
  <tr class="light">
    <td align="left" width="30%" nowrap><?php echo smarty_function_localize(array('str' => 'All Chargebacks'), $this);?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayChargebacks']->commission->approved)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayChargebacks']->commission->paid)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['todayChargebacks']->commission->pending)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthChargebacks']->commission->approved)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthChargebacks']->commission->paid)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
    <td align="center"><?php echo ((is_array($_tmp=$this->_tpl_vars['thisMonthChargebacks']->commission->pending)) ? $this->_run_mod_handler('currency_span', true, $_tmp) : smarty_modifier_currency_span($_tmp)); ?>
</td>
  </tr>
</table>