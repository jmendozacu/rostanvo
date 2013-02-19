<?php /* Smarty version 2.6.18, created on 2012-05-29 03:55:03
         compiled from installer_check_requirements.stpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'installer_check_requirements.stpl', 3, false),)), $this); ?>
<!-- installer_check_requirements -->
<fieldset>
 <legend><?php echo smarty_function_localize(array('str' => 'System Requirements'), $this);?>
</legend>
<p>
<?php echo smarty_function_localize(array('str' => 'If any of these items are not supported, your system does not meet the minimum requirements for installation. 
Please take appropriate actions to correct the errors. Failure to do so could lead to your PAP installation 
not functioning properly'), $this);?>

</p>

<table class="Requirements">
	<?php $_from = $this->_tpl_vars['requirements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['requirement']):
?>
        <tr class="Row Row<?php if ($this->_tpl_vars['requirement']->isValid()): ?>Ok<?php else: ?>Failed<?php endif; ?>">
            <td class="Cell<?php if ($this->_tpl_vars['requirement']->isValid()): ?>Ok<?php else: ?>Failed<?php endif; ?>"><div class="CellText"><?php if ($this->_tpl_vars['requirement']->isValid()): ?>OK<?php else: ?>FAILED<?php endif; ?></div></td>
            <td class="CellName"><?php echo $this->_tpl_vars['requirement']->getName(); ?>
</td>
            <td><?php if (! $this->_tpl_vars['requirement']->isValid()): ?><?php echo $this->_tpl_vars['requirement']->getFixDescription(); ?>
<?php endif; ?></td>
        </tr>
	<?php endforeach; endif; unset($_from); ?>
</table>

</fieldset>