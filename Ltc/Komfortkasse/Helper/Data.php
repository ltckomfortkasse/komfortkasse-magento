<?php

$path = Mage::getModuleDir('', 'Ltc_Komfortkasse');
require_once "{$path}/Helper/Komfortkasse.php";
/**
 * Komfortkasse
 * Magento Plugin - Helper_Data Class
 * @version 1.3.0.6-Magento */
class Ltc_Komfortkasse_Helper_Data extends Mage_Core_Helper_Abstract
{


    /**
     * Init.
     *
     * @return void
     */
    public function init()
    {
        Komfortkasse::init();

    }//end init()


    /**
     * Test.
     *
     * @return void
     */
    public function test()
    {
        Komfortkasse::test();

    }//end test()


    /**
     * Read orders.
     *
     * @return void
     */
    public function readorders()
    {
        Komfortkasse::readorders();

    }//end readorders()


    /**
     * Read refunds.
     *
     * @return void
     */
    public function readrefunds()
    {
        Komfortkasse::readrefunds();

    }//end readrefunds()


    /**
     * Update orders.
     *
     * @return void
     */
    public function updateorders()
    {
        Komfortkasse::updateorders();

    }//end updateorders()


    /**
     * Update refunds.
     *
     * @return void
     */
    public function updaterefunds()
    {
        Komfortkasse::updaterefunds();

    }//end updaterefunds()


    /**
     * Info.
     *
     * @return void
     */
    public function info()
    {
        Komfortkasse::info();

    }//end info()

    /**
     * Notify order.
     *
     * @return void
     */
    public function notifyorder($id)
    {
        Komfortkasse::notifyorder($id);

    }//end notifyorder()


    public function readinvoicepdf() {
        return Komfortkasse::readinvoicepdf();
    }




}//end class
