<?php

class Magestore_Customerreward_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('customerreward/transaction')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Transactions'), Mage::helper('adminhtml')->__('Transaction'));
		return $this;
	}
 
	public function indexAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->_title($this->__('Customer reward'))
			->_title($this->__('Transactions'));
		$this->_initAction()
			->renderLayout();
	}
	
	protected function _resetTransaction()
	{
		$rCustomers = Mage::getModel('customerreward/customer')->getCollection();
		if(count($rCustomers)){
			foreach($rCustomers as $rCustomer){
				$rCustomer->delete();
			}
		}
		
		$transactions = Mage::getModel('customerreward/transaction')->getCollection();
		if(count($transactions)){
			foreach($transactions as $transaction){
				$transaction->delete();
			}
		}		
	}
	
	public function synchronizeAction()
	{
		return $this->_redirect('*/*/');
		
		$this->_resetTransaction();
		
		// synchronize affiliate
		$referrals = Mage::getModel('affiliate/referral')
										->getCollection()
										->addFieldToFilter('balance',array('gt'=>0))
										;
		$msTransaction = Mage::getModel('customerreward/transaction');
		foreach($referrals as $referral){
			$msTransaction->setTitle('Transferred from Affiliate system')
						  ->setStoreId(0)
						  ->setPointsChange((int)$referral->getBalance())
						  ->setPointsSpent(0)
						  ->setAction('admin')
						  ->setNotice('Transferred from account balance in Affiliate system')
						  ->setExpirationDate(null)
						//  ->setExtraContent($awTransaction->getComment())
						  ->setCreateAt(now())
						  ->setCustomerId($referral->getCustomerId())
							;
			$msTransaction->setIsExpired(false);
			$msTransaction->save();
			
			$msTransaction->setId(null);
		}
		// synchronize Reward Point
		$action_map = array('customer_register'=>'initialize',
							'order_invoiced'=>'invoice',
							'spend_on_order'=>'spend',
							'added_by_admin'=>'admin',
							'transaction_expired'=>'admin',
							'invitee_registered'=>'admin',
							'order_invoiced_by_referral'=>'offer',
							'review_approved'=>'review',
							'customer_subscription'=>'newsletter',
							'customer_participate_in_poll'=>'poll',
							'customer_tag_product'=>'tag',
						);
		try{
			
			$summaries = Mage::getModel('points/summary')->getCollection();
			$msTransaction = Mage::getModel('customerreward/transaction');
			$rCustomer = Mage::getModel('customerreward/customer');
			foreach($summaries as $summary){
				$rCustomer->setCustomerId($summary->getCustomerId())
						  ->setTotalPoints($summary->getPoints())
						  ->save()
						  ->setId(null)
							;
				
				$awTransactions = Mage::getModel('points/transaction')
												->getCollection()
												->addFieldToFilter('summary_id',$summary->getId());
				
				foreach($awTransactions as $awTransaction){
					$msTransaction->setTitle($awTransaction->getComment())
								  ->setStoreId($awTransaction->getStoreId())
								  ->setPointsChange($awTransaction->getBalanceChange())
								  ->setPointsSpent($awTransaction->getBalanceChangeSpent())
								  ->setAction($action_map[$awTransaction->getAction()])
								  ->setNotice($awTransaction->getNotice())
								  ->setExpirationDate($awTransaction->getExpirationDate())
								//  ->setExtraContent($awTransaction->getComment())
								  ->setCreateAt($awTransaction->getChangeDate())
								  ->setCustomerId($summary->getCustomerId())
									;
					if((int)$msTransaction->getExpirationDate() && strtotime($msTransaction->getExpirationDate()) < time()){
						$msTransaction->setIsExpired(true);
					} else {
						$msTransaction->setIsExpired(false);
					}
					
					$msTransaction->save()->setId(null);
				}				
			}
		
		}catch(Exception $e){
			
		}
		$this->_redirect('*/*/');
	}

	public function viewAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('customerreward/transaction')->load($id);

		if ($model->getId() || $id == 0){
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)){
				$model->setData($data);
			}

			Mage::register('transaction_data', $model);
			
			$this->_title($this->__('Customer reward'))
				->_title($this->__('Manage transaction'))
				->_title($model->getTitle());

			$this->loadLayout();
			$this->_setActiveMenu('customerreward/transaction');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Transaction Manager'), Mage::helper('adminhtml')->__('Transaction Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Transaction News'), Mage::helper('adminhtml')->__('Transaction News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('customerreward/adminhtml_transaction_edit'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customerreward')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function resetAction(){
		try{
			$reward_count_table = Mage::getSingleton('core/resource')->getTableName('customerreward/count');
			$reward_order_table = Mage::getSingleton('core/resource')->getTableName('customerreward/order');
			$reward_transaction_table = Mage::getSingleton('core/resource')->getTableName('customerreward/transaction');
			$reward_customer_table = Mage::getSingleton('core/resource')->getTableName('customerreward/customer');
			
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');
			
			$write->truncate($reward_count_table);
			$write->truncate($reward_order_table);
			$write->truncate($reward_transaction_table);
			$write->truncate($reward_customer_table);
			
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customerreward')->__('Transaction(s) was reseted successfully'));
		}catch (Exception $e){
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customerreward')->__('Error while resetting transaction(s)'));
		}
		$this->_redirectReferer();
	}
}