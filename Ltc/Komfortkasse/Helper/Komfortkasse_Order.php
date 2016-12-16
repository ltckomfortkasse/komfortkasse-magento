<?php


/**
 * Komfortkasse Order Class
 * in KK, an Order is an Array providing the following members:
 * number, date, email, customer_number, payment_method, amount, currency_code, exchange_rate, language_code, invoice_number, store_id
 * status: data type according to the shop system
 * delivery_ and billing_: _firstname, _lastname, _company, _street, _postcode, _city, _countrycode
 * products: an Array of item numbers
 * @version 1.7.6-Magento1
 */
$path = Mage::getModuleDir('', 'Ltc_Komfortkasse');
global $komfortkasse_order_extension;
$komfortkasse_order_extension = false;
if (file_exists("{$path}/Helper/Komfortkasse_Order_Extension.php") === true) {
    $komfortkasse_order_extension = true;
    include_once "{$path}/Helper/Komfortkasse_Order_Extension.php";
}
class Komfortkasse_Order
{


    private static function createInClause($arr)
    {
        if (!is_array($arr)) {
            $arr = explode(',', $arr);
        }
        $tmp = array ();
        foreach ($arr as $item) {
            $tmp [] = str_replace('\'', '', $item);
        }
        return '(\'' . implode('\', \'', $tmp) . '\')';

    }


    /**
     * Get open order IDs.
     *
     * @return string all order IDs that are "open" and relevant for transfer to kk
     */
    public static function getOpenIDs()
    {
        $ret = array ();

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $tableOrder = $resource->getTableName('sales/order');
        $tablePayment = $resource->getTableName('sales/order_payment');

        $minDate = date('Y-m-d', time() - 31536000); // 1 Jahr

        $m2e = Mage::helper('core')->isModuleEnabled('Ess_M2ePro') ? Mage::getModel('M2ePro/Order') : null;
        global $komfortkasse_order_extension;

        $shopIds = Komfortkasse_Config::getRequestParameter('s');
        if ($shopIds)
            $shopIds = Komfortkasse::kkdecrypt($shopIds);
        $store_codes = $shopIds ? explode(',', $shopIds) : array ();

        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $store_code = $store->getCode();
                    if (empty($store_codes) || in_array($store_code, $store_codes)) {

                        $store_id = $store->getId();
                        $store_id_order = array ();
                        $store_id_order ['store_id'] = $store_id;

                        if (Komfortkasse_Config::getConfig(Komfortkasse_Config::activate_export, $store_id_order)) {

                            $m2e_active = $m2e && Komfortkasse_Config::getConfig('payment/m2epropayment/active', $store_id_order);

                            $query = 'SELECT o.increment_id FROM ' . $tableOrder . ' o join ' . $tablePayment . ' p on p.parent_id = o.entity_id';
                            if ($m2e_active)
                                $query .= ' left join ' . $resource->getTableName('M2ePro/Order') . ' m on m.magento_order_id=o.entity_id left join ' . $resource->getTableName('M2ePro/Ebay_Order') . ' e on e.order_id=m.id ';
                            if ($komfortkasse_order_extension && method_exists('Komfortkasse_Order_Extension', 'extendOpenIDsQueryJoin') === true)
                                $query .= Komfortkasse_Order_Extension::extendOpenIDsQueryJoin($resource);

                            $query .= ' where o.store_id=' . $store_id . ' and created_at > ' . $minDate . ' and (';
                            $first = true;
                            $m2e_used = false;

                            // PREPAYMENT
                            $openOrders = Komfortkasse_Config::getConfig(Komfortkasse_Config::status_open, $store_id_order);
                            $paymentMethods = Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods, $store_id_order);
                            if ($openOrders && $paymentMethods) {
                                if (!$first)
                                    $query .= ' or ';
                                $query .= '(o.status in ' . self::createInClause($openOrders) . ' and p.method in ' . self::createInClause($paymentMethods);

                                if ($m2e_active && !$m2e_used && strstr($paymentMethods, 'm2epropayment') !== false) {
                                    $query .= ' and (p.method <> \'m2epropayment\' or e.payment_details like \'%"method":"%berweisung"%\') ';
                                    $m2e_used = true;
                                }
                                if ($komfortkasse_order_extension && method_exists('Komfortkasse_Order_Extension', 'extendOpenIDsQueryWhere') === true) {
                                    $queryExtended = Komfortkasse_Order_Extension::extendOpenIDsQueryWhere('PREPAYMENT');
                                    if ($queryExtended)
                                        $query .= ' and ( ' . $queryExtended . ' )';
                                }

                                $query .= ')';
                                $first = false;
                            }

                            // COD
                            $openOrders = Komfortkasse_Config::getConfig(Komfortkasse_Config::status_open_cod, $store_id_order);
                            $paymentMethods = Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods_cod, $store_id_order);
                            if ($openOrders && $paymentMethods) {
                                if (!$first)
                                    $query .= ' or ';
                                $query .= '(o.status in ' . self::createInClause($openOrders) . ' and p.method in ' . self::createInClause($paymentMethods);

                                if ($m2e_active && !$m2e_used && strstr($paymentMethods, 'm2epropayment') !== false) {
                                    $query .= ' and (p.method <> \'m2epropayment\' or e.payment_details like \'%"method":"Nachnahme"%\') ';
                                    $m2e_used = true;
                                }
                                if ($komfortkasse_order_extension && method_exists('Komfortkasse_Order_Extension', 'extendOpenIDsQueryWhere') === true) {
                                    $queryExtended = Komfortkasse_Order_Extension::extendOpenIDsQueryWhere('COD');
                                    if ($queryExtended)
                                        $query .= ' and ( ' . $queryExtended . ' )';
                                }

                                $query .= ')';
                                $first = false;
                            }

                            // INVOICE
                            $openOrders = Komfortkasse_Config::getConfig(Komfortkasse_Config::status_open_invoice, $store_id_order);
                            $paymentMethods = Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods_invoice, $store_id_order);
                            if ($openOrders && $paymentMethods) {
                                if (!$first)
                                    $query .= ' or ';
                                $query .= '(o.status in ' . self::createInClause($openOrders) . ' and p.method in ' . self::createInClause($paymentMethods);

                                if ($m2e_active && !$m2e_used && strstr($paymentMethods, 'm2epropayment') !== false) {
                                    $query .= ' and (p.method <> \'m2epropayment\' or e.payment_details like \'%"method":"Rechnung"%\') ';
                                    $m2e_used = true;
                                }
                                if ($komfortkasse_order_extension && method_exists('Komfortkasse_Order_Extension', 'extendOpenIDsQueryWhere') === true) {
                                    $queryExtended = Komfortkasse_Order_Extension::extendOpenIDsQueryWhere('INVOICE');
                                    if ($queryExtended)
                                        $query .= ' and ( ' . $queryExtended . ' )';
                                }

                                $query .= ')';
                                $first = false;
                            }

                            $query .= ')';
                            if (!$first) { // nur wenn min. eine teil-query angehängt wurde
                                $results = $readConnection->fetchAll($query);
                                foreach ($results as $result) {
                                    $ret [] = $result ['increment_id'];
                                }
                            }
                        }
                    }
                }
            }
        }

        return $ret;

    }

    // end getOpenIDs()


    /**
     * Get refund IDS.
     *
     * @return string all refund IDs that are "open" and relevant for transfer to kk
     */
    public static function getRefundIDs()
    {
        $ret = array ();

        $minDate = date('Y-m-d', time() - 31536000); // 1 Jahr

        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {

                    $store_id = $store->getId();
                    $store_id_order = array ();
                    $store_id_order ['store_id'] = $store_id;

                    if (!Komfortkasse_Config::getConfig(Komfortkasse_Config::activate_export, $store_id_order)) {
                        continue;
                    }

                    $paymentMethods = explode(',', Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods, $store_id_order));
                    $paymentMethodsInvoice = explode(',', Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods_invoice, $store_id_order));

                    $cmModel = Mage::getModel("sales/order_creditmemo");
                    $cmCollection = $cmModel->getCollection()->addFieldToFilter('store_id', $store_id)->addAttributeToFilter('created_at', array ('gt' => $minDate
                    ));

                    foreach ($cmCollection as $creditMemo) {
                        if ($creditMemo->getTransactionId() == null) {
                            $order = $creditMemo->getOrder();
                            try {
                                $method = $order->getPayment()->getMethodInstance()->getCode();
                            } catch ( Exception $e ) {
                                // payment method has been deleted
                                $method = null;
                            }
                            if ($method && (in_array($method, $paymentMethods, true) === true || in_array($method, $paymentMethodsInvoice, true) === true)) {
                                $cmId = $creditMemo->getIncrementId();
                                $ret [] = $cmId;
                            }
                        }
                    }
                }
            }
        }

        return $ret;

    }

    // end getRefundIDs()


    /**
     * Get order.
     *
     * @param string $number order number
     *
     * @return array order
     */
    public static function getOrder($number)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($number);
        if (empty($number) === true || empty($order) === true || $number != $order->getIncrementId()) {
            return null;
        }

        $conf_general = Mage::getStoreConfig('general', $order->getStoreId());

        $ret = array ();
        $ret ['store_id'] = $order->getStoreId();
        $ret ['invoice_date'] = null;
        $ret ['number'] = $order->getIncrementId();
        $ret ['status'] = $order->getStatus();
        $ret ['date'] = date('d.m.Y', strtotime($order->getCreatedAtStoreDate()->get(Zend_Date::DATE_MEDIUM)));
        $ret ['email'] = $order->getCustomerEmail();
        $ret ['customer_number'] = $order->getCustomerId();
        try {
            $ret ['payment_method'] = $order->getPayment()->getMethodInstance()->getCode();
        } catch ( Exception $e ) {
        }
        $ret ['amount'] = $order->getGrandTotal();
        $ret ['currency_code'] = $order->getOrderCurrencyCode();
        $ret ['exchange_rate'] = $order->getBaseToOrderRate();

        // Rechnungsnummer und -datum, evtl. Rechnungsbetrag
        $useInvoiceAmount = Komfortkasse::getOrderType($ret) == 'INVOICE' && Komfortkasse_Config::getConfig(Komfortkasse_Config::use_invoice_total, $ret);
        $considerCreditnotes = $useInvoiceAmount && Komfortkasse_Config::getConfig(Komfortkasse_Config::consider_creditnotes, $ret);
        $creditnotesAsInvoices = Komfortkasse_Config::getConfig(Komfortkasse_Config::creditnotes_as_invoices, $ret);
        $lastOnly = Komfortkasse_Config::getConfig(Komfortkasse_Config::last_receipt_only, $ret);
        $invoiceColl = $order->getInvoiceCollection();
        $creditColl = $order->getCreditmemosCollection();
        $invAmount = 0.0;

        if ($lastOnly) {
            // nur der letzte Beleg wird übertragen
            $newestInvoice = null;
            foreach ($invoiceColl as $invoice) {
                if (!$invoice->isCanceled()) {
                    if ($newestInvoice == null || $newestInvoice->getCreatedAt() < $invoice->getCreatedAt()) {
                        $newestInvoice = $invoice;
                    }
                }
            }
            $newestCredit = null;
            if ($creditnotesAsInvoices) {
                foreach ($creditColl as $credit) {
                    if ($newestCredit == null || $newestCredit->getCreatedAt() < $credit->getCreatedAt()) {
                        $newestCredit = $credit;
                    }
                }
            }
            if ($newestCredit != null || $newestInvoice != null) {
                if ($newestCredit == null || ($newestInvoice != null && $newestInvoice->getCreatedAt() > $newestCredit->getCreatedAt())) {
                    $ret ['invoice_number'] [] = $newestInvoice->getIncrementId();
                    $ret ['invoice_date'] = date('d.m.Y', strtotime($newestInvoice->getCreatedAt()));
                    $invAmount = $newestInvoice->getGrandTotal();
                } else if ($newestCredit != null) {
                    $ret ['invoice_number'] [] = $newestCredit->getIncrementId();
                    $ret ['invoice_date'] = date('d.m.Y', strtotime($newestCredit->getCreatedAt()));
                    $invAmount = $newestCredit->getGrandTotal();
                }
            }
        } else {
            // alle Belege werden übertragen
            if ($invoiceColl->getSize() > 0 || ($creditnotesAsInvoices && $creditColl->getSize() > 0)) {
                foreach ($invoiceColl as $invoice) {
                    if (!$invoice->isCanceled()) {
                        $ret ['invoice_number'] [] = $invoice->getIncrementId();
                        $invoiceDate = date('d.m.Y', strtotime($invoice->getCreatedAt()));
                        if ($ret ['invoice_date'] == null || strtotime($ret ['invoice_date']) < strtotime($invoiceDate)) {
                            $ret ['invoice_date'] = $invoiceDate;
                        }
                        $invAmount = $invAmount + $invoice->getGrandTotal();
                    }
                }
                if (!$creditnotesAsInvoices && $considerCreditnotes) {
                    foreach ($creditColl as $credit) {
                        $invAmount = $invAmount - $credit->getGrandTotal();
                    }
                }
                if ($creditnotesAsInvoices) {
                    foreach ($creditColl as $credit) {
                        $ret ['invoice_number'] [] = $credit->getIncrementId();
                        $invoiceDate = date('d.m.Y', strtotime($credit->getCreatedAt()));
                        if ($ret ['invoice_date'] == null || strtotime($ret ['invoice_date']) < strtotime($invoiceDate)) {
                            $ret ['invoice_date'] = $invoiceDate;
                        }
                        $invAmount = $invAmount + $credit->getGrandTotal();
                    }
                }
            }
        }
        if ($useInvoiceAmount && $invAmount > 0)
            $ret ['amount'] = $invAmount;

        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $ret ['delivery_firstname'] = utf8_encode($shippingAddress->getFirstname());
            $ret ['delivery_lastname'] = utf8_encode($shippingAddress->getLastname());
            $ret ['delivery_company'] = utf8_encode($shippingAddress->getCompany());
            $ret ['delivery_street'] = utf8_encode($shippingAddress->getStreetFull());
            $ret ['delivery_postcode'] = utf8_encode($shippingAddress->getPostcode());
            $ret ['delivery_city'] = utf8_encode($shippingAddress->getCity());
            $ret ['delivery_countrycode'] = utf8_encode($shippingAddress->getCountryId());
        }

        $billingAddress = $order->getBillingAddress();
        if ($billingAddress) {
            $ret ['language_code'] = substr($conf_general ['locale'] ['code'], 0, 2) . '-' . $billingAddress->getCountryId();
            $ret ['billing_firstname'] = utf8_encode($billingAddress->getFirstname());
            $ret ['billing_lastname'] = utf8_encode($billingAddress->getLastname());
            $ret ['billing_company'] = utf8_encode($billingAddress->getCompany());
            $ret ['billing_street'] = utf8_encode($billingAddress->getStreetFull());
            $ret ['billing_postcode'] = utf8_encode($billingAddress->getPostcode());
            $ret ['billing_city'] = utf8_encode($billingAddress->getCity());
            $ret ['billing_countrycode'] = utf8_encode($billingAddress->getCountryId());
        } else {
            $ret ['language_code'] = substr($conf_general ['locale'] ['code'], 0, 2);
        }

        foreach ($order->getAllItems() as $itemId => $item) {
            $sku = $item->getSku();
            if ($sku) {
                $ret ['products'] [] = $sku;
            } else {
                $ret ['products'] [] = $item->getName();
            }
        }

        $m2e = Mage::helper('core')->isModuleEnabled('Ess_M2ePro') ? Mage::getModel('M2ePro/Order') : null;
        if ($m2e && $m2e->load($order->getEntityId(), 'magento_order_id')) {
            $ebay = Mage::getModel('M2ePro/Ebay_Order');
            if ($ebay->load($m2e->getId(), 'order_id')) {
                $ret ['marketplace_number'] = $ebay->getEbayOrderId();
                $ret ['customer_number'] = $ebay->getBuyerUserId();
            }
        }

        global $komfortkasse_order_extension;
        if ($komfortkasse_order_extension && method_exists('Komfortkasse_Order_Extension', 'extendOrder') === true) {
            $ret = Komfortkasse_Order_Extension::extendOrder($order, $ret);
        }

        return $ret;

    }

    // end getOrder()


    /**
     * Get refund.
     *
     * @param string $number refund number
     *
     * @return array refund
     */
    public static function getRefund($number)
    {
        $resource = Mage::getSingleton('core/resource');
        $id = $resource->getConnection('core_read')->fetchOne('SELECT `entity_id` FROM `' . $resource->getTableName('sales/creditmemo') . "` WHERE `increment_id` = '" . $number . "'");

        $creditMemo = Mage::getModel('sales/order_creditmemo')->load($id);
        if (empty($number) === true || empty($creditMemo) === true || $number != $creditMemo->getIncrementId()) {
            return null;
        }

        $ret = array ();
        $ret ['number'] = $creditMemo->getOrder()->getIncrementId();
        // Number of the Creditmemo.
        $ret ['customer_number'] = $creditMemo->getIncrementId();
        $ret ['date'] = date('d.m.Y', strtotime($creditMemo->getCreatedAt()));
        $ret ['amount'] = $creditMemo->getGrandTotal();

        return $ret;

    }

    // end getRefund()


    /**
     * Update order.
     *
     * @param array $order order
     * @param string $status status
     * @param string $callbackid callback ID
     *
     * @return void
     */
    public static function updateOrder($order, $status, $callbackid)
    {
        if (!Komfortkasse_Config::getConfig(Komfortkasse_Config::activate_update, $order)) {
            return;
        }

        // Hint: PAID and CANCELLED are supported as of now.
        $order = Mage::getModel('sales/order')->loadByIncrementId($order ['number']);

        Mage::log('Komfortkasse: update order ' . $order->getIncrementId() . ' START', null, 'komfortkasse.log');
        Mage::dispatchEvent('komfortkasse_change_order_status_before', array ('order' => $order,'status' => $status,'callbackid' => $callbackid
        ));

        $stateCollection = Mage::getModel('sales/order_status')->getCollection()->joinStates();
        $stateCollection->addFieldToFilter('main_table.status', array ('like' => $status
        ));
        $state = $stateCollection->getFirstItem()->getState();

        if ($state == 'processing' || $state == 'closed' || $state == 'complete') {

            // If there is already an invoice, update the invoice, not the order.
            $invoiceColl = $order->getInvoiceCollection();
            if ($invoiceColl->getSize() > 0) {
                foreach ($order->getInvoiceCollection() as $invoice) {
                    Mage::log('Komfortkasse: update order ' . $order->getIncrementId() . ' invoice ' . $invoice->getIncrementId() . ' pay', null, 'komfortkasse.log');
                    $invoice->pay();
                    Mage::log('Komfortkasse: update order ' . $order->getIncrementId() . ' invoice ' . $invoice->getIncrementId() . ' addComment ' . $callbackid, null, 'komfortkasse.log');
                    $invoice->addComment($callbackid, false, false);
                    self::mysave($invoice);
                }
            } else {
                $payment = $order->getPayment();
                Mage::log('Komfortkasse: update order ' . $order->getIncrementId() . ' payment capture', null, 'komfortkasse.log');
                $payment->capture(null);

                if ($callbackid) {
                    $payment->setTransactionId($callbackid);
                    Mage::log('Komfortkasse: update order ' . $order->getIncrementId() . ' addTransaction', null, 'komfortkasse.log');
                    $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
                }
            }
            $order->save();
            $order = Mage::getModel('sales/order')->loadByIncrementId($order->getIncrementId());

            Mage::log('Komfortkasse: update order ' . $order->getIncrementId() . ' add status history ' . $status . ' / ' . $callbackid, null, 'komfortkasse.log');
            $history = $order->addStatusHistoryComment('' . $callbackid, $status);
            $order->setStatus($status);
            $order->save();
        } else if ($state == 'canceled') {

            if ($callbackid) {
                Mage::log('Komfortkasse: update order ' . $order->getIncrementId() . ' add status history ' . $status . ' / ' . $callbackid, null, 'komfortkasse.log');
                $history = $order->addStatusHistoryComment('' . $callbackid, $status);
            }
            if ($order->canCancel()) {
                Mage::log('Komfortkasse: update order ' . $order->getIncrementId() . ' cancel', null, 'komfortkasse.log');
                $order->cancel();
            }
            Mage::log('Komfortkasse: update order ' . $order->getIncrementId() . ' set status ' . $status, null, 'komfortkasse.log');
            $order->setStatus($status);
            $order->save();
        } else {

            Mage::log('Komfortkasse: update order ' . $order->getIncrementId() . ' add status history ' . $status . ' / ' . $callbackid, null, 'komfortkasse.log');
            $history = $order->addStatusHistoryComment('' . $callbackid, $status);
            $order->save();
        }

        Mage::dispatchEvent('komfortkasse_change_order_status_after', array ('order' => $order,'status' => $status,'callbackid' => $callbackid
        ));

        Mage::log('Komfortkasse: update order ' . $order->getIncrementId() . ' END. Status: ' . $order->getStatus, null, 'komfortkasse.log');

    }

    // end updateOrder()


    /**
     * Update order.
     *
     * @param string $refundIncrementId Increment ID of refund
     * @param string $status status
     * @param string $callbackid callback ID
     *
     * @return void
     */
    public static function updateRefund($refundIncrementId, $status, $callbackid)
    {
        $resource = Mage::getSingleton('core/resource');
        $id = $resource->getConnection('core_read')->fetchOne('SELECT `entity_id` FROM `' . $resource->getTableName('sales/creditmemo') . "` WHERE `increment_id` = '" . $refundIncrementId . "'");

        $creditMemo = Mage::getModel('sales/order_creditmemo')->load($id);

        $store_id = $creditMemo->getStoreId();
        $store_id_order = array ();
        $store_id_order ['store_id'] = $store_id;

        if (!Komfortkasse_Config::getConfig(Komfortkasse_Config::activate_update, $store_id_order)) {
            return;
        }

        if ($creditMemo->getTransactionId() == null) {
            $creditMemo->setTransactionId($callbackid);
        }

        $history = $creditMemo->addComment($status . ' [' . $callbackid . ']', false, false);

        $creditMemo->save();

    }

    // end updateRefund()


    /**
     * Call an object's save method
     *
     * @param unknown $object
     *
     * @return void
     */
    private static function mysave($object)
    {
        $object->save();

    }


    public static function getInvoicePdfPrepare()
    {

    }


    public static function getInvoicePdf($invoiceNumber, $orderNumber)
    {
        if ($invoiceNumber) {

            $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceNumber);
            $fileName = $invoiceNumber . '.pdf';

            if ($invoice && (!$orderNumber || $invoice->getOrderIncrementId() == $orderNumber)) {

                $pdfGenerated = false;

                // try easy pdf (www.easypdfinvoice.com)
                if (!$pdfGenerated) {
                    $pdfProModel = Mage::helper('core')->isModuleEnabled('VES_PdfPro') ? Mage::getModel('pdfpro/order_invoice') : false;
                    if ($pdfProModel !== false) {
                        $invoiceData = $pdfProModel->initInvoiceData($invoice);
                        $result = Mage::helper('pdfpro')->initPdf(array ($invoiceData
                        ));
                        if ($result ['success']) {
                            $content = $result ['content'];
                            $pdfGenerated = true;
                        }
                    }
                }

                // try Magento Standard
                if (!$pdfGenerated) {
                    $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf(array ($invoice
                    ));
                    $content = $pdf->render();
                }

            } else if (Komfortkasse_Config::getConfig(Komfortkasse_Config::creditnotes_as_invoices, self::getOrder($orderNumber))) {

                // try credit note
                $collection = Mage::getResourceModel('sales/order_creditmemo_collection')->addFieldToFilter('increment_id', $invoiceNumber);
                $credit = $collection->count() == 1 ? $collection->getFirstItem() : null;

                if ($credit && (!$orderNumber || $credit->getOrder()->getIncrementId() == $orderNumber)) {

                    $pdfGenerated = false;

                    // try easy pdf (www.easypdfinvoice.com)
                    if (!$pdfGenerated) {
                        $pdfProModel = $pdfProModel = Mage::helper('core')->isModuleEnabled('VES_PdfPro') ? Mage::getModel('pdfpro/order_creditmemo') : false;
                        if ($pdfProModel !== false) {
                            $creditData = $pdfProModel->initCreditmemoData($credit);
                            $result = Mage::helper('pdfpro')->initPdf(array ($creditData
                            ));
                            if ($result ['success']) {
                                $content = $result ['content'];
                                $pdfGenerated = true;
                            }
                        }
                    }

                    // try Magento Standard
                    if (!$pdfGenerated) {
                        $pdf = Mage::getModel('sales/order_pdf_creditmemo')->getPdf(array ($credit
                        ));
                        $content = $pdf->render();
                    }
                }
            }

            return $content;
        }

    }


    public static function isOpen($order)
    {
        if ($order ['payment_method'] == 'm2epropayment') {
            // m2e Bestellung nur relevant wenn eBay-Zahlungsart passend
            $mage = Mage::getModel('sales/order')->loadByIncrementId($order ['number']);
            $m2e = Mage::helper('core')->isModuleEnabled('Ess_M2ePro') ? Mage::getModel('M2ePro/Order') : null;
            if ($m2e && $m2e->load($mage->getEntityId(), 'magento_order_id')) {
                $ebay = Mage::getModel('M2ePro/Ebay_Order');
                if ($ebay->load($m2e->getId(), 'order_id')) {
                    $ebay_method = $ebay->getPaymentMethod();

                    $paymentMethods = Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods, $order);
                    if (strstr($paymentMethods, 'm2epropayment') !== false) {
                        return strstr($ebay_method, 'berweisung') !== false;
                    }
                    $paymentMethods = Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods_cod, $order);
                    if (strstr($paymentMethods, 'm2epropayment') !== false) {
                        return strstr($ebay_method, 'Nachnahme') !== false;
                    }
                    $paymentMethods = Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods, $order);
                    if (strstr($paymentMethods, 'm2epropayment') !== false) {
                        return strstr($ebay_method, 'Rechnung') !== false;
                    }
                }
            }
        }

        global $komfortkasse_order_extension;
        if ($komfortkasse_order_extension && method_exists('Komfortkasse_Order_Extension', 'isOpen') === true) {
            $ret = Komfortkasse_Order_Extension::isOpen($order);
        }

    }
}//end class
