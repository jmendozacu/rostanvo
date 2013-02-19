<?php

class Magestore_Customerreward_Model_Mysql4_Transaction extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('customerreward/transaction', 'transaction_id');
    }
    
    public function loadByOrder($transaction,$order,$action){
    	$condition = '%order_id='.$order->getId().'%';
    	$select = $this->_getReadAdapter()->select()
    		->from($this->getTable('customerreward/transaction'))
    		->where('action = ?',$action)
    		->where('extra_content LIKE ?',$condition);
   		$data = $this->_getReadAdapter()->fetchRow($select);
   		if (isset($data['transaction_id'])){
   			$transaction->load($data['transaction_id'])->addData($data);
   		}
   		return $this;
    }
    
    public function loadByProductId($transaction,$customerId,$productId,$action){
    	$condition = '%product_id='.$productId.'%';
    	$select = $this->_getReadAdapter()->select()
    		->from($this->getTable('customerreward/transaction'))
    		->where('action = ?',$action)
    		->where('customer_id = ?',$customerId)
    		->where('extra_content LIKE ?',$condition);
   		$data = $this->_getReadAdapter()->fetchRow($select);
   		if (isset($data['transaction_id'])){
   			$transaction->load($data['transaction_id'])->addData($data);
   		}
   		return $this;
    }
}