<?php
/**
 * Smarty plugin
 * @package GwtPhpFramework
 */


/**
 * Smarty {localize} function plugin
 *
 * Type:     function<br>
 * Name:     localize<br>
 *
 * Examples:
 * <pre>
 * {ratioPercentage p1='25' p2='35'}
 * </pre>
 * @author   Andrej Harsani, Michal Bebjak   
 * @param    array
 * @param    Smarty
 * @return   string
 */
function smarty_function_ratioPercentage($params, &$smarty) {
	if ($params['p2']!=0) {
	    $number = round($params['p1']/$params['p2']*100,Pap_Common_Utils_CurrencyUtils::getDefaultCurrencyPrecision());
    	return Gpf_Common_NumberUtils::toStandardNumberFormat($number, Pap_Common_Utils_CurrencyUtils::getDefaultCurrencyPrecision()) . ' %';
	} else {
		return '0';
	}
}

?>
