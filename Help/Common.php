<?php
/**
 * Created by PhpStorm.
 * User: Icyboy <icyboy@me.com>
 * Date: 2016/5/19
 * Time: 14:16
 */

namespace Icyboy\Core\Help;

class Common
{
    /**
     * 递归过滤xss
     *
     * @param $array
     * @return array
     */
    public static function XssCleanRecurse($array)
    {
        $result = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::XssCleanRecurse($value);
            } else {
                $result[$key] = self::XssClean($value);
            }
        }

        return $result;
    }

    /**
     * 包装的xss方法
     *
     * @param $value
     * @return string
     */
    public static function XssClean($value)
    {
        return htmlentities($value);
    }

    /**
     * 校验是不是单条记录
     *
     * @param $record
     * @return bool
     */
    public static function CheckIsSingleRecord($record)
    {
        if (count($record) == count($record, 1)) {
            return true;
        }

        if (!isset($record[0])) {
            return true;
        }

        return false;
    }
}