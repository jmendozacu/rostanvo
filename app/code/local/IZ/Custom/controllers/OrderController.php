<?php
class IZ_Custom_OrderController extends Mage_Core_Controller_Front_Action {
	public function cancelorderAction()
	{
		$order_id = intval($this->getRequest()->getParam('order_id'));
		$order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
		try	{
			if(!$order->canCancel())
			{
				Mage::getSingleton('core/session')->addNotice($this->__("Order No. {$order_id} can't be canceled!"));
				Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('sales/order/history/'));
			}
			else
			{
				$order->cancel();
				$order->setStatus('canceled_pendings');
				$order->save();

				Mage::getSingleton('core/session')->addSuccess($this->__("Order No. {$order_id} has been canceled!"));
				Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('sales/order/history/'));
			}
		}
		catch(exception $e) {
			Mage::log($e->getMessage(), null, 'system.log');
		}
	}
}
