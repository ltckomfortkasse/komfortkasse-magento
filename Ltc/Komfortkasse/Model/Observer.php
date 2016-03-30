<?php

/**
 * Komfortkasse
 * Magento Plugin - Observer Class
 *
 * @version 1.2.1.8-Magento */
class Ltc_Komfortkasse_Model_Observer
{


    /**
     * getRegName
     * 
     * @param Varien_Event_Observer $observer Observer
     * 
     * @return string
     */
    private function getRegName(Varien_Event_Observer $observer)
    {
        $id = $observer->getOrder()->getIncrementId();
        if ($id) {
            $regName = 'komfortkasse_order_status_'.$id;
            return $regName;
        }

    }//end getRegName()


    /**
     * noteNewOrder
     * 
     * @param Varien_Event_Observer $observer Observer
     * 
     * @return void
     */
    public function noteNewOrder(Varien_Event_Observer $observer)
    {
        $regName = self::getRegName($observer);
        if ($regName) {
            Mage::register($regName, '_new');
        }

    }//end noteNewOrder()


    /**
     * noteOrderStatus
     * 
     * @param Varien_Event_Observer $observer Observer
     * 
     * @return void
     */
    public function noteOrderStatus(Varien_Event_Observer $observer)
    {
        $regName = self::getRegName($observer);
        if ($regName && !Mage::registry($regName)) {
            Mage::register($regName, $observer->getOrder()->getStatus());
        }

    }//end noteOrderStatus()


    /**
     * checkOrderStatus
     * 
     * @param Varien_Event_Observer $observer Observer
     * 
     * @return void
     */
    public function checkOrderStatus(Varien_Event_Observer $observer)
    {
        $regName     = self::getRegName($observer);
        $orderStatus = Mage::registry($regName);
        if ($regName && $orderStatus) {
            if ($orderStatus != $observer->getOrder()->getStatus()) {
                $helper = Mage::helper('Ltc_Komfortkasse');
                $helper->notifyorder($observer->getOrder()->getIncrementId());
            }

            Mage::unregister($regName);
        }

    }//end checkOrderStatus()


}//end class