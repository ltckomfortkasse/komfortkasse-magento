<?php

/**
 * Komfortkasse
 * Config Class
 * @version 1.7.2-Magento */
class Komfortkasse_Config
{
    const activate_export = 'payment/komfortkasse/activate_export';
    const activate_update = 'payment/komfortkasse/activate_update';
    const payment_methods = 'payment/komfortkasse/payment_methods';
    const status_open = 'payment/komfortkasse/status_open';
    const status_paid = 'payment/komfortkasse/status_paid';
    const status_cancelled = 'payment/komfortkasse/status_cancelled';
    const payment_methods_invoice = 'payment/komfortkasse/payment_methods_invoice';
    const status_open_invoice = 'payment/komfortkasse/status_open_invoice';
    const status_paid_invoice = 'payment/komfortkasse/status_paid_invoice';
    const status_cancelled_invoice = 'payment/komfortkasse/status_cancelled_invoice';
    const payment_methods_cod = 'payment/komfortkasse/payment_methods_cod';
    const status_open_cod = 'payment/komfortkasse/status_open_cod';
    const status_paid_cod = 'payment/komfortkasse/status_paid_cod';
    const status_cancelled_cod = 'payment/komfortkasse/status_cancelled_cod';
    const encryption = 'payment/komfortkasse/encryption';
    const accesscode = 'payment/komfortkasse/accesscode';
    const apikey = 'payment/komfortkasse/apikey';
    const publickey = 'payment/komfortkasse/publickey';
    const privatekey = 'payment/komfortkasse/privatekey';
    const use_invoice_total = 'payment/komfortkasse/use_invoice_total';
    const consider_creditnotes = 'payment/komfortkasse/consider_creditnotes';
    const creditnotes_as_invoices = 'payment/komfortkasse/creditnotes_as_invoices';
    const last_receipt_only = 'payment/komfortkasse/last_receipt_only';


    /**
     * Set Config.
     *
     *
     * @param string $constantKey Constant Key
     * @param string $value Value
     *
     * @return void
     */
    public static function setConfig($constantKey, $value)
    {
        Mage::getConfig()->saveConfig($constantKey, $value);
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();

    }

 // end setConfig()


    /**
     * Get Config.
     *
     *
     * @param string $constantKey Constant Key
     *
     * @return mixed
     */
    public static function getConfig($constantKey, $order=null)
    {
        $store_id = null;
        if ($order != null && isset($order['store_id'])) {
            $store_id = $order['store_id'];
        } else {
            // export und update werden in den getId Methoden nochmals extra ber�cksichtigt.
            if ($constantKey == self::activate_export)
                return true;
            if ($constantKey == self::activate_update)
                return true;
        }

        $value = Mage::getStoreConfig($constantKey, $store_id);

        return $value;

    }

 // end getConfig()


    /**
     * Get Request Parameter.
     *
     *
     * @param string $key Key
     *
     * @return string
     */
    public static function getRequestParameter($key)
    {
        return urldecode(Mage::app()->getRequest()->getParam($key));

    }

 // end getRequestParameter()


    /**
     * Get Magento Version.
     *
     *
     * @return string
     */
    public static function getVersion()
    {
        return Mage::getVersion();

    } // end getVersion()

    public static function output($s)
    {
        echo $s;
    }

}//end class