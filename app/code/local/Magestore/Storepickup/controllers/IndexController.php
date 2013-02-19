<?php
class Magestore_Storepickup_IndexController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
    {					
		if(!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)){return;}
		$this->loadLayout();
		$this->getLayout()
				->getBlock('head')
				->setTitle(Mage::helper('core')->__('Our Stores'));
		$this->renderLayout();
    }
	public function changestoreAction()
	{	
		$is_storepickup = $this->getRequest()->getParam('is_storepickup');
		
		if($is_storepickup)
		{
			$data['is_storepickup'] = $is_storepickup;
			Mage::getSingleton('checkout/session')->setData('storepickup_session',$data);	
			return;
		}
		$data = Mage::getSingleton('checkout/session')->getData('storepickup_session');
		
		//storepickup
		$data['store_id'] = $this->getRequest()->getParam('store_id');
		
		Mage::getSingleton('checkout/session')->setData('storepickup_session',$data);
	}
	
	public function changedateAction()
	{
		try{
			$shipping_date = $this->getRequest()->getParam('shipping_date');
			$store_id = $this->getRequest()->getParam('store_id');

			$storepickup = Mage::getSingleton('checkout/session')->getData('storepickup_session');
			$storepickup['date'] = $shipping_date;
			Mage::getSingleton('checkout/session')->setData('storepickup_session',$storepickup);

			$html_select = Mage::helper('storepickup')->getTimeSelectHTML($shipping_date,$store_id);
	        $this->getResponse()->setBody($html_select);		
			}catch(Exception $e){
			Mage::getSingleton('checkout/session')->setData('myerror',$e->getMessage());
		}
	}
	public function changetimeAction()
	{
		$shipping_time = $this->getRequest()->getParam('shipping_time');
		
		$storepickup = Mage::getSingleton('checkout/session')->getData('storepickup_session');
		
		$storepickup['time'] = $shipping_time;
		
		Mage::getSingleton('checkout/session')->setData('storepickup_session',$storepickup);		
	}

}