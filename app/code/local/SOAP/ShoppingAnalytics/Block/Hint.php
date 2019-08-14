<?php
/**
 * @author SOAP Media
 */
class SOAP_ShoppingAnalytics_Block_Hint
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'shoppinganalytics/system/config/fieldset/hint.phtml';

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
    
    public function getShoppingAnalyticsVersion()
    {
        return (string) Mage::getConfig()->getNode('modules/SOAP_ShoppingAnalytics/version');
    }
}