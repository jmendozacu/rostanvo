<?php

// Make absolutely sure we have the prerequisite
require_once BP . DS . 'app/code/core/Mage/Paypal/Model/Ipn.php';

/**
 * PayPal Instant Payment Notification processor model
 */
class Mage_Pap_Model_PaypalIpn extends Mage_Paypal_Model_Ipn
{
    /**
     * Post back to PayPal to check whether this request is a valid one
     *
     * @param Zend_Http_Client_Adapter_Interface $httpAdapter
     */
    /*
    protected function _postBack(Zend_Http_Client_Adapter_Interface $httpAdapter)
    {
        Mage::log("Begin PayPal PAP tracking: ".$this->_request['custom']."\n");

        // Give PAP access to the data so it can handle any needed commissions
        try // If "Something Bad" happens, it isn't worth dying over
        {
            $papModel = Mage::getModel('pap/pap');
            $postData = $this->_request;
            $customdata = explode('~~~a469ccb0-767c-4fed-96de-99427e1783aa~~~', $postData['custom']);
            $saleData = isset($customdata[0]) ? $customdata[0] : null;
            $cookieValue = isset($customdata[1]) ? $customdata[1] : null;
    
            // Convert the JSON data into a usable array
            $saleData = json_decode($saleData);
            $saleData = $this->ForceArray($saleData);
        }
        catch (Exception $e)
        {
            Mage::log('Caught exception while preparing to log PayPal sale to PAP: '.$e->getMessage()."\n");
        }

        parent::_postBack($httpAdapter);

        try // If "Something Bad" happens, it isn't worth dying over
        {
            Mage::log("Before PayPal PAP register sale\n");
            $papModel->registerSaleDetails($saleData, isset($cookieValue) ? $cookieValue : null);
            Mage::log("After successful PayPal PAP register sale\n");
        }
        catch (Exception $e)
        {
            Mage::log('Caught exception while trying to log PayPal sale to PAP: '.$e->getMessage()."\n");
        }
    }
    
    // recursively force objects to arrays
    protected function ForceArray($data)
    {
      if (is_object($data))
      {
        $data = (array)$data;
      }
      if (is_array($data))
      {
        foreach ($data as $key=>$val)
        {
          $data[$key] = $this->ForceArray($val);
        }
      }
      return $data;
    }
    */
    protected function _registerPaymentCapture()
    {
        try // If "Something Bad" happens, it isn't worth dying over
        {
            Mage::log("Before looking up PayPal PAP cookie\n");

            $papModel = Mage::getModel('pap/pap');
            $postData = $this->_request;
            $customdata = explode('~~~a469ccb0-767c-4fed-96de-99427e1783aa~~~', $postData['custom']);
//            $saleData = isset($customdata[0]) ? $customdata[0] : null;
            $cookieValue = isset($customdata[1]) ? $customdata[1] : null;
            
            Mage::log("Before PayPal PAP register sale for cookie $cookieValue\n");
            $papModel->registerOrder($this->_getOrder(), null, isset($cookieValue) ? $cookieValue : null);
            Mage::log("After successful PayPal PAP register sale for cookie $cookieValue\n");
        }
        catch (Exception $e)
        {
            Mage::log('Caught exception while trying to log PayPal sale to PAP: '.$e->getMessage()."\n");
        }

        parent::_registerPaymentCapture();
    }
}
