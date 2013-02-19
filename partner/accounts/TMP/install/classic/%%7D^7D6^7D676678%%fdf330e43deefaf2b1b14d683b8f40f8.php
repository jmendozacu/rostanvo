<?php /* Smarty version 2.6.18, created on 2012-05-29 03:58:32
         compiled from text://fdf330e43deefaf2b1b14d683b8f40f8 */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'text://fdf330e43deefaf2b1b14d683b8f40f8', 2, false),)), $this); ?>
<font size="2">
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Dear merchant'), $this);?>
,</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Affiliate'), $this);?>
 <?php echo $this->_tpl_vars['firstname']; ?>
 <?php echo $this->_tpl_vars['lastname']; ?>
 <?php echo smarty_function_localize(array('str' => 'joined your campaign'), $this);?>
 <?php echo $this->_tpl_vars['campaignname']; ?>
.</span><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'This affiliate is waiting for your approval.'), $this);?>
</span><br/><br/>

<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'If you want to APPROVE affiliate to join camapign click here'), $this);?>
: </span><br/>
<span style="font-family: Arial;"><a href="<?php echo $this->_tpl_vars['affiliate_join_campaign_approve_link']; ?>
"><?php echo $this->_tpl_vars['affiliate_join_campaign_approve_link']; ?>
</a></span>
<br/><br/>

<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'If you want to DECLINE affiliate to join camapign click here'), $this);?>
: </span><br/>
<span style="font-family: Arial;"><a href="<?php echo $this->_tpl_vars['affiliate_join_campaign_decline_link']; ?>
"><?php echo $this->_tpl_vars['affiliate_join_campaign_decline_link']; ?>
</a></span>
<br/><br/>

<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Regards'), $this);?>
,</span><br/><br/>
<span style="font-family: Arial;"><?php echo smarty_function_localize(array('str' => 'Your'), $this);?>
 <?php echo $this->_tpl_vars['postAffiliatePro']; ?>
.</span>
</font>