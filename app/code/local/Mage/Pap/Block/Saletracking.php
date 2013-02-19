<?php

/*********************************************************************************
 * Copyright 2009 Priacta, Inc.
 * 
 * This software is provided free of charge, but you may NOT distribute any
 * derivative works or publicly redistribute the software in any form, in whole
 * or in part, without the express permission of the copyright holder.
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *********************************************************************************/

class Mage_Pap_Block_SaleTracking extends Mage_Core_Block_Text
{
    protected function _toHtml()
    {
        $config = Mage::getSingleton('pap/config');
        if (!$config->getTrackSales('javascript')) {
            return ''; // not allowed to track via Javascript
        }

        // Get the quote
        $quote = $this->getQuote();

        if ($quote)
        {
          // from there, get the quote ID
          if ($quote instanceof Mage_Sales_Model_Quote) {
              $quoteId = $quote->getId();
          } else {
              $quoteId = $quote;
          }
        }
        else
        {
          // Shouldn't happen, but Magento 1.4 has a bug that can cause problems
          // if the customer registers an account at checkout, so this will help
          // work around the problem.
          $quoteId = Mage::getSingleton('checkout/session')->getLastQuoteId();
        }

        if (!$quoteId) {
            return '';
        }

        // Get the order(s) for the quote
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToFilter('quote_id', $quoteId)
            ->load();

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

        // Build the script for this order information
        ob_start();
        ?>
          <?php echo $script_block; ?>
          <script type="text/javascript">
          <?php
          foreach($items as $idx=>$item)
          {
            $sale = "pap_sale".$idx; // calculated var name for each part of the sale
          ?>
            var <?php echo $sale; ?> = PostAffTracker.createSale();
            <?php echo $sale; ?>.setTotalCost('<?php echo addslashes($item['totalcost']); ?>');
            <?php echo $sale; ?>.setOrderID('<?php echo addslashes($item['orderid']); ?>');
            <?php if ($item['data1']) { ?> <?php echo $sale; ?>.setData1('<?php echo addslashes($item['data1']); ?>'); <?php } ?>
            <?php if ($item['data2']) { ?> <?php echo $sale; ?>.setData2('<?php echo addslashes($item['data2']); ?>'); <?php } ?>
            <?php if ($item['data3']) { ?> <?php echo $sale; ?>.setData3('<?php echo addslashes($item['data3']); ?>'); <?php } ?>
            <?php if ($item['data4']) { ?> <?php echo $sale; ?>.setData4('<?php echo addslashes($item['data4']); ?>'); <?php } ?>
            <?php if ($item['data5']) { ?> <?php echo $sale; ?>.setData5('<?php echo addslashes($item['data5']); ?>'); <?php } ?>
            <?php echo $sale; ?>.setProductID('<?php echo addslashes($item['productid']); ?>');
            <?php if ($item['couponcode']) { ?> try {<?php echo $sale; ?>.setCoupon('<?php echo addslashes($item['couponcode']); ?>'); } catch (err) {} <?php } ?>
            <?php if ($item['affiliateid']) { ?> <?php echo $sale; ?>.setAffiliateID('<?php echo addslashes($item['affiliateid']); ?>'); <?php } ?>
            <?php echo $sale; ?>.setCurrency('<?php echo addslashes(Mage::app()->getStore()->getBaseCurrencyCode()); ?>');
          <?php
          }
          ?>
          
          <?php if ($config->getChannelID()) { ?> PostAffTracker.setChannel('<?php echo addslashes($config->getChannelID()); ?>'); <?php } ?>
          
          PostAffTracker.register();
          </script>        
        <?php
        $text = ob_get_contents();
        ob_end_clean();
        
        $this->addText("
         <!-- BEGIN AFFILIATE TRACKING CODE -->
         ".$text."
         <!-- END AFFILIATE TRACKING CODE -->
         ");

        return parent::_toHtml();

    }
}
