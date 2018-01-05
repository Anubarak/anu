<?php
/**
 * Created by PhpStorm.
 * User: scham
 * Date: 05.01.2018
 * Time: 16:15
 */

namespace anu\helper;


class UploadFileHelper
{
    // privates
    //==========================================================
    private static $_uploadedFiles;
    private static $_restructuredFileArray;

    /**
     * @param $handle
     *
     * @return mixed
     */
    public static function getUploadedFile($handle)
    {
        if (isset(self::$_uploadedFiles[$handle])) {
            return self::$_uploadedFiles[$handle];
        }

        $files = self::getRestructuredFileArray();

        return $files[$handle] ?? null;
    }

    /**
     * @return array
     */
    public static function getRestructuredFileArray(): array
    {
        if (self::$_restructuredFileArray !== null) {
            return self::$_restructuredFileArray;
        }

        $output = [];
        foreach ($_FILES as $fileHandle => $value) {
            $max = \count($_FILES[$fileHandle]['name']);
            for ($i = 0; $i < $max; $i++) {
                $output[$fileHandle][$i] = [
                    'name'     => $_FILES[$fileHandle]['name'][$i],
                    'type'     => $_FILES[$fileHandle]['type'][$i],
                    'tmp_name' => $_FILES[$fileHandle]['tmp_name'][$i],
                    'error'    => $_FILES[$fileHandle]['error'][$i],
                    'size'     => $_FILES[$fileHandle]['size'][$i]
                ];
            }
        }

        self::$_restructuredFileArray = $output;

        return self::$_restructuredFileArray;
    }
}