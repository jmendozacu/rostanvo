<?php

class Magestore_Customerreward_Helper_Coupon extends Mage_Core_Helper_Data
{
	public function calcCode($expression){
		if ($this->isExpression($expression)){
			return preg_replace_callback('#\[([AN]{1,2})\.([0-9]+)\]#',array($this,'convertExpression'),$expression);
		}else{
			return $expression;
		}
	}
	
	public function convertExpression($param){
		$alphabet  = (strpos($param[1],'A'))===false ? '':'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$alphabet .= (strpos($param[1],'N'))===false ? '': '0123456789';
		return $this->getRandomString($param[2],$alphabet);
	}
	
	public function isExpression($string){
		return preg_match('#\[([AN]{1,2})\.([0-9]+)\]#',$string);
	}
}