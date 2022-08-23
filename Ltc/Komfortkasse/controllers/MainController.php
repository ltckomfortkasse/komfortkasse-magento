<?php
/**
 * Komfortkasse
 * Magento Plugin - MainController
 *
 * @version 1.7.6-Magento */
class Ltc_Komfortkasse_MainController extends Mage_Core_Controller_Front_Action
{


    /**
     * Init.
     *
     * @return void
     */
    public function initAction()
    {
        $this->getResponse()->setBody(self::getHelper()->init());

    }

 // end initAction()


    /**
     * Test.
     *
     * @return void
     */
    public function testAction()
    {
        $this->getResponse()->setBody(self::getHelper()->test());

    }

 // end testAction()


    /**
     * Read orders.
     *
     * @return void
     */
    public function readordersAction()
    {
        $this->getResponse()->setBody(self::getHelper()->readorders());

    }

 // end readordersAction()


    /**
     * Read refunds.
     *
     * @return void
     */
    public function readrefundsAction()
    {
        $this->getResponse()->setBody(self::getHelper()->readrefunds());

    }

 // end readrefundsAction()


    /**
     * Update orders.
     *
     * @return void
     */
    public function updateordersAction()
    {
        $this->getResponse()->setBody(self::getHelper()->updateorders());

    }

 // end updateordersAction()


    /**
     * Update refunds.
     *
     * @return void
     */
    public function updaterefundsAction()
    {
        $this->getResponse()->setBody(self::getHelper()->updaterefunds());

    }

 // end updaterefundsAction()


    /**
     * Info.
     *
     * @return void
     */
    public function infoAction()
    {
        $this->getResponse()->setBody(self::getHelper()->info());

    }

 // end infoAction()

    /**
     * Read Config.
     *
     * @return void
     */
    public function readConfigAction()
    {
        $this->getResponse()->setBody(self::getHelper()->readconfig());

    }

    // end infoAction()


    /**
     * Get Helper.
     *
     * @return void
     */
    protected function getHelper()
    {
        return Mage::helper('Ltc_Komfortkasse');

    }

 // end getHelper()
    public function readinvoicepdfAction()
    {
        $content = self::getHelper()->readinvoicepdf();
        if (!$content)
            return;

        $contentType = 'application/pdf';
        $contentLength = strlen($content);

        $this->getResponse()->setHttpResponseCode(200)->setHeader('Pragma', 'public', true)->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)->setHeader('Content-type', $contentType, true)->setHeader('Content-Length', $contentLength, true)->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true)->setHeader('Last-Modified', date('r'), true);
        $this->getResponse()->setBody($content);
    }
}//end class

