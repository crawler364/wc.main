<?php

/**
 * Если символьный код не здан, транслетирует из названия. Проверяет код на уникальность, если не уникален пробует добавить "1".
 * Еще раз проверяет на уникальность, если опять не уникален, меняет "1" на "2" и тд.
 * AddEventHandler('iblock', 'OnBeforeIBlockElementAdd', [WC\Core\Handlers\UniqueSymbolCode::class, 'constructElement']);
 * AddEventHandler('iblock', 'OnBeforeIBlockSectionAdd', [WC\Core\Handlers\UniqueSymbolCode::class, 'constructSection']);
 * AddEventHandler('iblock', 'OnBeforeIBlockElementUpdate', [WC\Core\Handlers\UniqueSymbolCode::class, 'constructElement']);
 * AddEventHandler('iblock', 'OnBeforeIBlockSectionUpdate', [WC\Core\Handlers\UniqueSymbolCode::class, 'constructSection']);
 */

namespace WC\Core\Handlers;


use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\SectionTable;
use Cutil;

class UniqueSymbolCode
{
    // todo настройки в админку
    private static $arParams = [
        'max_len' => '100',
        'change_case' => 'L',
        'replace_space' => '-',
        'replace_other' => '',
        'delete_repeat_replace' => 'true',
    ];
    private static $iBlockId;
    private static $isElem;
    private static $isSect;

    public static function constructElement(&$arFields): void
    {
        self::$isElem = true;
        self::handler($arFields);
    }

    public static function constructSection(&$arFields): void
    {
        self::$isSect = true;
        self::handler($arFields);
    }

    private static function handler(&$arFields): void
    {
        if ($arFields['NAME']) {
            self::$iBlockId = $arFields['IBLOCK_ID'];
            $code = $arFields['CODE'] ?: Cutil::translit($arFields['NAME'], 'ru', self::$arParams);
            $arFields['CODE'] = self::checkCode($code);
        }
    }

    private static function checkCode($code, $i = null): string
    {
        $filter = ['IBLOCK_ID' => self::$iBlockId, 'CODE' => $code . $i];
        $select = ['IBLOCK_ID', 'ID'];

        if (self::$isElem) {
            $res = ElementTable::getList([
                'filter' => $filter,
                'select' => $select,
            ]);
        } elseif (self::$isSect) {
            $res = SectionTable::getList([
                'filter' => $filter,
                'select' => $select,
            ]);
        }

        if ($res->fetch()) {
            $i++;
            return self::checkCode($code, $i);
        }

        return $code . $i;
    }
}
