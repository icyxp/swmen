<?php
/**
 * Created by PhpStorm.
 * User: icyboy
 * Date: 16/5/19
 * Time: 下午3:35
 */

namespace Icyboy\Core\Help;

class Transform
{
    /**
     * 数据库字段转换为输出字段
     *
     * @param array $record
     * @param $mapping_key
     * @return array
     */
    public static function DatabaseRecordList2Output(array $record, $mapping_key)
    {
        if ($record) {
            if (Common::CheckIsSingleRecord($record)) {
                $record = self::_transform($record, $mapping_key);
            } else {
                foreach ($record as $key => $value) {
                    $record[$key] = self::_transform($value, $mapping_key);
                }
            }
        }

        return $record;
    }

    /**
     * 替换数据库单条记录
     *
     * @param $record
     * @param $mapping_key
     * @return array
     */
    private static function _transform($record, $mapping_key)
    {
        $mapping = config('mapping.' . $mapping_key);

        if (empty($mapping)) {
            return $record;
        }

        $schema = array_flip($mapping);

        $new = [];
        foreach ($record as $k => $value) {
            if (isset($schema[$k])) {
                $new[$schema[$k]] = $value;
            } else {
                $new[$k] = $value;
            }
        }

        return $new;
    }

    /**
     * 输入字段=>值转换为数据库字段=>值
     *
     * @param $inputs
     * @param $mapping_key
     * @return array
     */
    public static function Input2DatabaseRecord($inputs, $mapping_key)
    {
        $mapping = config('mapping.' . $mapping_key);

        if (empty($mapping)) {
            return $inputs;
        }

        $new = [];

        foreach ($inputs as $k => $input) {
            if (isset($mapping[$k])) {
                $new[$mapping[$k]] = $input;
            }
        }

        return $new;
    }

    /**
     * 将输入字段转换为数据库字段
     * @param $input
     * @param $mapping_key
     * @return array
     */
    public static function Input2DatabaseKey($input, $mapping_key)
    {
        $mapping = config('mapping.' . $mapping_key);

        if (empty($mapping)) {
            return $input;
        }

        if (is_array($input)) {
            $new = [];

            foreach ($input as $key) {
                if (isset($mapping[$key])) {
                    $new[] = $mapping[$key];
                }
            }

            return $new;

        } elseif(is_string($input)) {

            return isset($mapping[$input]) ? $mapping[$input] : '';
        }
    }

    /**
     * 将where条件的字段转换为数据库字段
     * [['some_field','=','some_value']]
     *
     * @param $array
     * @param $mapping_key
     * @return array
     */
    public static function Where2DatabaseRecord($array,$mapping_key)
    {
        $mapping = config('mapping.' . $mapping_key);

        if (empty($mapping)) {
            return $array;
        }

        $new = [];

        foreach ($array as $value) {
            $field = $value[0];
            if (isset($mapping[$field])) {
                $value[0] = $mapping[$field];
                $new[] = $value;
            }
        }

        return $new;
    }
}