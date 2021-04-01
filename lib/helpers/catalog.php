<?php

namespace WC\Core\Helpers;


use Bitrix\Main\Loader;

class Catalog
{
    public static function getProductRatio($productID): float
    {
        Loader::includeModule('catalog');
        $ratio = \Bitrix\Catalog\MeasureRatioTable::getList([
            'select' => ['ID', 'RATIO'],
            'filter' => ['=PRODUCT_ID' => $productID],
        ])->fetch();

        return $ratio ? (float)$ratio['RATIO'] : 1.00;
    }

    public static function formatWeight($weightInGrams): string
    {
        if ($weightInGrams >= 1000) {
            $weightFormatted = round($weightInGrams / 1000, 2) . ' кг';
        } else {
            $weightFormatted = round($weightInGrams, 2) . ' г';
        }
        return $weightFormatted;
    }
}
