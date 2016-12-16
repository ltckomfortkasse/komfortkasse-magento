<?php
$path = Mage::getModuleDir('', 'Ltc_Komfortkasse');
require_once "{$path}/Helper/Komfortkasse.php";
/**
 * Komfortkasse
 * Magento Plugin - Helper_Data Class
 *
 * @version 1.7.6-Magento
 */
class Ltc_Komfortkasse_Helper_Data extends Mage_Core_Helper_Abstract
{


    /**
     * Init.
     *
     * @return void
     */
    public function init()
    {
        return Komfortkasse::init();

    }

 // end init()


    /**
     * Test.
     *
     * @return void
     */
    public function test()
    {
        return Komfortkasse::test();

    }

 // end test()


    /**
     * Read orders.
     *
     * @return void
     */
    public function readorders()
    {
        return Komfortkasse::readorders();

    }

 // end readorders()


    /**
     * Read refunds.
     *
     * @return void
     */
    public function readrefunds()
    {
        return Komfortkasse::readrefunds();

    }

 // end readrefunds()


    /**
     * Update orders.
     *
     * @return void
     */
    public function updateorders()
    {
        return Komfortkasse::updateorders();

    }

 // end updateorders()


    /**
     * Update refunds.
     *
     * @return void
     */
    public function updaterefunds()
    {
        return Komfortkasse::updaterefunds();

    }

 // end updaterefunds()


    /**
     * Info.
     *
     * @return void
     */
    public function info()
    {
        return Komfortkasse::info();

    }

 // end info()

    /**
     * Notify order.
     *
     * @return void
     */
    public function notifyorder($id)
    {
        return Komfortkasse::notifyorder($id);

    }

 // end notifyorder()
    public function readinvoicepdf()
    {
        return Komfortkasse::readinvoicepdf();

    }
}//end class
