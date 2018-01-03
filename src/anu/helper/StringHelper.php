<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace anu\helper;

/**
 * StringHelper.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alex Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class StringHelper extends BaseStringHelper
{

    /**
     * Generates a valid v4 UUID string. See [http://stackoverflow.com/a/2040279/684]
     *
     * @return string The UUID.
     * @throws \Exception
     */
    public static function UUID(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            random_int(0, 0xffff), random_int(0, 0xffff),

            // 16 bits for "time_mid"
            random_int(0, 0xffff),

            // 16 bits for "time_hi_and_version", four most significant bits holds version number 4
            random_int(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low", two most significant bits holds zero and
            // one for variant DCE1.1
            random_int(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }

    /**
     * Simple check if a value is valid json See [https://stackoverflow.com/a/6041773/5604055]
     *
     * @param $string
     * @return bool valid json or not
     */
    public static function isJson($string){
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
