<?php

class Mage_Pap_Block_Paypal_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $standard = Mage::getModel('paypal/standard');
        
        //*******************************************
        // BEGIN PAP TRACKING EDITS
        //*******************************************
        $paypal_url = '';
        if (method_exists($standard, 'getPaypalUrl'))
        {
          // before 1.4
          $paypal_url = $standard->getPaypalUrl();
        }
        else
        {
          // after 1.4
          $paypal_url = $standard->getConfig()->getPaypalUrl();
        }
        
        //*******************************************
        // END PAP TRACKING EDITS
        //*******************************************
        
        $form = new Varien_Data_Form();
        $form->setAction($paypal_url)
            ->setId('paypal_standard_checkout')
            ->setName('paypal_standard_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
        foreach ($standard->getStandardCheckoutFormFields() as $field=>$value) {
            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }

        $html = '<html><body>';
        $html.= $this->__('You will be redirected to Paypal in a few seconds.');

        //*******************************************
        // BEGIN PAP TRACKING EDITS
        //*******************************************
        
        $config = Mage::getSingleton('pap/config'); // we'll need this
        
        // Get the order
        $orders = array();
        if (is_callable(array($this, 'getCheckout')) && is_callable(array($this->getCheckout(), 'getLastRealOrderId')))
        {
            // New method. Get the order directly.
            $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
            $orders = array(Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId));
        }
        else
        {
            // OLD METHOD: Get the order from a quote id

            $quote = $standard->getQuote();
            if ($quote)
            {
                if ($quote instanceof Mage_Sales_Model_Quote) {
                    $quoteId = $quote->getId();
                } else {
                    $quoteId = $quote;
                }
        
                if ($quoteId)
                {
                    // Get the order(s) for the quote
                    $orders = Mage::getResourceModel('sales/order_collection')
                        ->addAttributeToFilter('quote_id', $quoteId)
                        ->load();
                }
            }
        }

        // get raw data to submit from the collection of orders
        $items = array();
        foreach ($orders as $order)
        {
          if (!$order){continue;}
  
          if (!$order instanceof Mage_Sales_Model_Order) {
              $order = Mage::getModel('sales/order')->load($order);
          }
          
          if (!$order){continue;}
          
          $order = Mage::getModel('pap/pap')->getOrderSaleDetails($order);
          array_splice($items, -1, 0, $order);
        }

        // Add a special field to hold the affiliate tracking data
        $form->addField('pap_ab78y5t4a', 'hidden', array('name'=>'custom', 'id'=>'pap_ab78y5t4a', 'value'=>json_encode($items)));

        //*******************************************
        // END PAP TRACKING EDITS
        //*******************************************
        
        $html.= $form->toHtml();
        
        //*******************************************
        // BEGIN PAP TRACKING EDITS
        //*******************************************
        
        ob_start();
        ?>
        <script type="text/javascript">
            (function () {
                var papDomain = (("https:" == document.location.protocol) ? "https://":"http://");papDomain+="<?php echo preg_replace('~^(https?://)?~', '', $config->getRemotePath()); ?>";
                var papId = 'pap_x2s6df8d';
                // adjust the ID iff it would conflict with an existing element
                if ((function(elementId){var nodes=new Array();var tmpNode=document.getElementById(elementId);while(tmpNode){nodes.push(tmpNode);tmpNode.id="";tmpNode=document.getElementById(elementId);for(var x=0;x<nodes.length;x++){if(nodes[x]==tmpNode){tmpNode=false;}}}})('pap_x2s6df8d')) {papId += '_salestrack';}
                document.write(unescape("%3Cscript id='pap_x2s6df8d' src='" + papDomain + "/scripts/<?php echo $config->getTracksalescript(); ?>' type='text/javascript'%3E%3C/script%3E"));
            })();
        </script>
        <?php
        $script_block = ob_get_clean();
        
        // Append the script to make the affiliate tracking work
        $html .= $script_block;
        $html.= '<script type="text/javascript">';
        
        // Unfortunately, both the sale data, and the cookie data, must be passed through in the same field
        // but the sale data is known server side, and the cookie data can only be properly retrieved
        // client side. As a result, we have to stuff the sale data into the field, then the cookie can be
        // stuffed in after a delimiter. The delimiter must not occur in either set of data. The next line
        // specifies the delimiter. THIS MUST BE IDENTICAL TO THE COUNTERPART IN Paypal.php!!!!
        $html.= 'PostAffTracker.setAppendValuesToField(\'~~~a469ccb0-767c-4fed-96de-99427e1783aa~~~\');';

        // Write the tracking data to the PayPal form, rather than registering the sale immediately
        $html.= 'PostAffTracker.writeCookieToCustomField(\'pap_ab78y5t4a\');';
        $html.= '</script>';
        
        //*******************************************
        // END PAP TRACKING EDITS
        //*******************************************
        
        // Code to do the redirect
        $html.= '<script type="text/javascript">document.getElementById("paypal_standard_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}