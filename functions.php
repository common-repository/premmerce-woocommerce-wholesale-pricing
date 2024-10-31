<?php

use Premmerce\PriceTypes\Models\Model;

if (!function_exists( 'premmerce_get_price_types')) {

    function premmerce_get_price_types()
    {
        global $wpdb;

        $data = [];

        $sql = vsprintf(
            'SELECT `t1`.`ID` AS "ID", `t2`.`ID` AS "roleID", `t1`.`name`, `t2`.`role`
            FROM `%s` AS t1
            LEFT JOIN `%s` AS t2 ON t2.price_type_id = t1.id;',
            [
                $wpdb->prefix . Model::TBL_NAME_PRICE_TYPES,
                $wpdb->prefix . Model::TBL_NAME_PRICE_TYPES_ROLES,
            ]
        );

        $priceTypes = $wpdb->get_results($sql, ARRAY_A);

        if ($priceTypes) {
            foreach ($priceTypes as $key => $priceType) {
                $data[$priceType['ID']]['ID']   = $priceType['ID'];
                $data[$priceType['ID']]['name'] = $priceType['name'];
                $data[$priceType['ID']]['roles'][$priceType['roleID']] = $priceType['role'];
            }
        }

        return $data;
    }

}

if (!function_exists( 'premmerce_get_prices')) {

    function premmerce_get_prices($productId)
    {
        global $wpdb;

        $data = [];

        if ($productId) {

            $sql = "SELECT `ID` FROM ".$wpdb->prefix . Model::TBL_NAME_PRICE_TYPES.";";

            $priceTypesIds = $wpdb->get_col($sql);
            $productMeta = get_post_meta($productId);

            foreach ($priceTypesIds as $id) {
                $key = '_price_types_'.$id;
                if (array_key_exists($key,$productMeta)) {
                    $data[$id] = $productMeta[$key][0];
                }
            }
        }

        return $data;
    }

}