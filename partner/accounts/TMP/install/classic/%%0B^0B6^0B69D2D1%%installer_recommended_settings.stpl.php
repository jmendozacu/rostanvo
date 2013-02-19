<?php /* Smarty version 2.6.18, created on 2012-05-29 03:55:03
         compiled from installer_recommended_settings.stpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'localize', 'installer_recommended_settings.stpl', 3, false),)), $this); ?>
<!-- installer_recommended_settings -->
<fieldset>
 <legend><?php echo smarty_function_localize(array('str' => 'Recommended Settings'), $this);?>
</legend>
<p>
<?php echo smarty_function_localize(array('str' => 'These are the recommended settings for PHP in order to ensure full compatibility with PAP.
PAP will still operate even if your settings do not match.'), $this);?>

</p>

<table class="RecommendedSettings">
     <tr class="Head">
         <td></td>
         <td><?php echo smarty_function_localize(array('str' => 'Name'), $this);?>
</td>
         <td class="CellSwitch"><?php echo smarty_function_localize(array('str' => 'Recommended'), $this);?>
</td>
         <td class="CellSwitch"><?php echo smarty_function_localize(array('str' => 'Current'), $this);?>
</td>
     </tr>
	<?php $_from = $this->_tpl_vars['settings']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['setting']):
?>
        <tr class="Row Row<?php if ($this->_tpl_vars['setting']->isRecommended()): ?>Ok<?php else: ?>Failed<?php endif; ?>">
            <td class="Cell Cell<?php if ($this->_tpl_vars['setting']->isRecommended()): ?>Ok<?php else: ?>Failed<?php endif; ?>"><div class="CellText"><?php if ($this->_tpl_vars['setting']->isRecommended()): ?>OK<?php else: ?>FAILED<?php endif; ?></div></td>
            <td class="CellName"><?php echo $this->_tpl_vars['setting']->getName(); ?>
</td>
            <td class="CellSwitch Cell<?php if ($this->_tpl_vars['setting']->getRecommended()): ?>On<?php else: ?>Off<?php endif; ?>"><?php echo $this->_tpl_vars['setting']->getRecommendedAsText(); ?>
</td>
            <td class="CellSwitch Cell<?php if ($this->_tpl_vars['setting']->getCurrent()): ?>On<?php else: ?>Off<?php endif; ?>"><?php echo $this->_tpl_vars['setting']->getCurrentAsText(); ?>
</td>
        </tr>
	<?php endforeach; endif; unset($_from); ?>
</table>

</fieldset>