<?php
/**
 * Komfortkasse
 * Order Extension for Customer VitaminExpress
 *
 * @version 1.7.4-Magento
 */
class Komfortkasse_Order_Extension
{
    private static $DELIVERY_COUNTRIES = array ('CH','LI'
    );


    public static function isOpen($order)
    {
        return in_array($order ['delivery_countrycode'], $DELIVERY_COUNTRIES);

    }


    public static function extendOpenIDsQueryJoin($resource)
    {
        return ' join ' . $resource->getTableName('sales/order_address') . ' sa on sa.parent_id=o.entity_id and sa.address_type=\'shipping\' ';

    }


    public static function extendOpenIDsQueryWhere($type)
    {
        if ($type == 'INVOICE') {
            $r = '';
            $first = true;
            foreach (self::$DELIVERY_COUNTRIES as $c) {
                if (!$first)
                    $r .= ' or ';
                $r .= 'sa.country_id=\'' . $c . '\'';
                $first = false;
            }
            return $r;
        }

    }
}