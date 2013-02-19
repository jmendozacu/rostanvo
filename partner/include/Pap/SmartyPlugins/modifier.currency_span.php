<?php
/**
 * Smarty plugin
 * @package GwtPhpFramework
 */


/**
 * Smarty |currency_span modifier
 *
 * Type:     function<br>
 * Name:     localize<br>
 *
 * Examples:
 * <pre>
 * {$allCommission|currency_span}
 * </pre>
 * @author   Michal Bebjak
 * @param    string
 * @return   string
 */
function smarty_modifier_currency_span($number)
{
    $cssClass = 'CurrencyData';
    if ($number < 0) {
        $cssClass .= ' CurrencyData-negative';
    }
    $number = number_format($number, Pap_Common_Utils_CurrencyUtils::getDefaultCurrencyPrecision(), Gpf_Settings_Regional::getInstance()->getDecimalSeparator(), Gpf_Settings_Regional::getInstance()->getThousandsSeparator());
    return '<span class="'.$cssClass.'">'.Pap_Common_Utils_CurrencyUtils::stringToCurrencyFormat($number).'</span>';
}
?>
