<?php
/**
 * Created by PhpStorm.
 * User: Icyboy <icyboy@me.com>
 * Date: 2016/5/19
 * Time: 14:15
 */

namespace Icyboy\Core\Help;

class ApiReponse
{
    /**
     * 成功
     *
     * @param $response_data
     * @param string $mapping_key
     * @return \Laravel\Lumen\Http\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function Success($response_data, $mapping_key = '')
    {
        if ($mapping_key) {
            $response_data = Transform::DatabaseRecordList2Output((array)$response_data, $mapping_key);
        }

        return response($response_data, 200);
    }

    /**
     * 参数验证错误
     *
     * @param $response_data
     * @return \Laravel\Lumen\Http\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function BadRequest($response_data)
    {
        return response($response_data, 400);
    }

    /**
     * 没有找到对应记录
     *
     * @param $response_data
     * @return \Laravel\Lumen\Http\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function NotFound($response_data)
    {
        return response($response_data, 404);
    }

    /**
     * 服务器错误
     *
     * @param $response_data
     * @return \Laravel\Lumen\Http\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function ServerError($response_data)
    {
        return response($response_data, 500);
    }

    /**
     * 拼装返回的数据
     *
     * @param $error_code
     * @param array $addition
     * @return array
     */
    public static function makeResponse($error_code, $addition = [])
    {
        $response = [
            'error_code' => $error_code,
            'message'    => array_get(config('error_code'), $error_code)
        ];

        if ($addition) {
            $response = array_merge($response, $addition);
        }

        return $response;
    }

    /**
     * 拼装列表数据
     *
     * @param $records
     * @param $mapping_key
     * @param $start
     * @param $rows
     * @param $total
     * @return array
     */
    public static function makeResponseList($records, $mapping_key, $start, $rows, $total)
    {
        return [
            'start'  => $start,
            'rows'   => min($rows, count($records)),
            'total'  => $total,
            'record' => Transform::DatabaseRecordList2Output($records, $mapping_key)
        ];
    }
}