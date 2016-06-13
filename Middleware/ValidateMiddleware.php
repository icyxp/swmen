<?php
/**
 * Created by PhpStorm.
 * User: Icyboy <icyboy@me.com>
 * Date: 2016/5/19
 * Time: 14:12
 */

namespace Icyboy\Core\Middleware;

use Closure;
use Illuminate\Support\Facades\Validator;
use Icyboy\Core\Help\ApiResponse;
use Icyboy\Core\Help\Common;

class ValidateMiddleware
{
    //路由信息
    private $route_arr;

    //url上的请求参数
    private $query_params;

    //json请求参数
    private $json_params;

    //路由的方法
    private $method;

    //错误码
    private $error_code;

    //校验的配置文件
    private $validate_config;

    //这个路由的检验规则
    private $validator_config_for_this_route;

    //错误发生的次数
    private $error_number;

    //json的层级
    private $json_level;

    /**
     * 校验参数
     * JSON中的数据可能有多条记录，每条记录的判断相互独立
     *
     * @param $request
     * @param callable $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->_init($request);

        //没有这个路由的验证规则，退出不进行校验
        if (empty($this->validator_config_for_this_route)) {
            return $next($request);
        }

        //需要有JSON数据，但是没有，报错
        if (!$this->_checkJsonExist()) {
            $response = ApiResponse::makeResponse(40001);

            return ApiResponse::BadRequest($response);
        }

        //query中的参数，有错误就需要直接返回
        $rule = array_get($this->validator_config_for_this_route, 'query');
        if ($rule) {
            $this->query_params = $this->_validateWithConfig($this->query_params, $rule);

            if ($this->error_number > 0) {
                return ApiResponse::BadRequest($this->query_params);
            }
        }

        //json中的参数，如果是多级，有错误的标注，全部错误就返回
        $rule = array_get($this->validator_config_for_this_route, 'json');
        if ($rule) {
            //检查json的层级和指定的是否一致
            if (!$this->_checkJsonLevel($this->json_params, 'json_level')) {
                return ApiResponse::BadRequest(ApiResponse::makeResponse(40002));
            }

            //一维数组有错误就直接返回
            if ($this->json_level === 1) {
                $this->json_params = $this->_validateWithConfig($this->json_params, $rule);

                if ($this->error_number > 0) {
                    return ApiResponse::BadRequest($this->json_params);
                }

            } else {
                foreach ($this->json_params as $key => $object_params) {
                    $this->json_params[$key] = $this->_validateWithConfig($object_params, $rule);
                }

                if ($this->error_number === count($this->json_params)) {
                    return ApiResponse::BadRequest($this->json_params);
                }
            }
        }

        //返回过滤后的参数
        $this->_setXssCleanParam($request);

        return $next($request);
    }

    /**
     *
     * 初始化，加载配置文件
     */
    private function _init($request)
    {
        $this->error_code      = config('error_code');
        $this->error_number    = 0;
        $this->validate_config = config('validate');

        //请求的方式
        $this->method = strtolower($request->getMethod());

        //路径信息，包括[0=>1，1=>配置信息，2=>参数]
        $this->route_arr = $request->route();
        $route_params    = array_pop($this->route_arr);
        $route_config    = array_pop($this->route_arr);
        $route_as        = array_get($route_config, 'as');

        //这个路由的验证规则
        $key = $this->_getValidatorConfigKey($route_as);
        $key && $this->validator_config_for_this_route = array_get($this->validate_config, $key);

        //url参数
        $query_params = $request->query();
        //合并url参数
        $this->query_params = array_merge($query_params, $route_params);

        //json参数
        $this->json_params = $request->json()->all();
    }

    /**
     * 根据校验规则验证参数
     * @param $params array 需要校验的参数
     * @param $config array 校验规则
     * @return array
     */
    private function _validateWithConfig($params, $config)
    {
        $v = Validator::make($params, $config);

        if ($v->fails()) {
            $params = $this->_setValidationError($v->errors()->toArray());
            $this->error_number++;
        }

        return $params;
    }

    /**
     * 设置错误信息
     * @param string $error_detail 错误的详细信息
     * @return array
     */
    private function _setValidationError($error_detail = '')
    {
        $errorData = ApiResponse::makeResponse(40000);

        if (env('APP_SHOW_DEBUG', false)) {
            $errorData = array_add($errorData, 'detail', $error_detail);
        }


        return $errorData;
    }

    /**
     * 获取验证的配置信息
     * @param $key string 形如：XXXX.XXXX
     * @return bool|string
     */
    private function _getValidatorConfigKey($key)
    {
        if (!$key) {
            return false;
        }

        //如果有直接对应的，就返回
        if (array_get($this->validate_config, $key)) {
            return $key;
        }

        //否则返回返回XXX.base
        $key_arr = explode('.', $key);
        $new_key = $key_arr[0] . '.base';
        if (array_get($this->validate_config, $new_key)) {
            return $new_key;
        }

        return false;
    }

    /**
     * 判断json参数是否存在
     * @return bool
     */
    private function _checkJsonExist()
    {
        //post put方法必须有json
        if (($this->method === 'post' || $this->method === 'put') && empty($this->json_params)) {
            return false;
        }

        return true;
    }

    /**
     * 检查json层级
     *
     * @param $json_params
     * @param $key_name
     * @return bool
     */
    private function _checkJsonLevel($json_params, $key_name)
    {
        $this->_setJsonLevel($key_name);

        $isTop = Common::CheckIsSingleRecord($json_params);

        if ($this->json_level === 1 && !$isTop) {
            //需要的是一维数组，但是却提供了多维，报错
            return false;
        } elseif ($this->json_level !== 1 && $isTop) {
            //需要的是多维
            return false;
        } else {
            return true;
        }

    }

    /**
     * 检查有没有xss
     *
     * @param array $params
     * @return bool
     */
    private function _checkXss($params = [])
    {
        if (empty($params)) {
            $params = $this->query_params;
        }

        foreach ($params as $param) {
            if (Common::XssClean($param) != $param) {
                return true;
            }
        }

        return false;
    }

    /**
     * 设置json参数的层级
     * json的层级，是一级的还是多级的
     *
     * @param $key_name
     */
    private function _setJsonLevel($key_name)
    {
        $this->json_level = array_get($this->validator_config_for_this_route, $key_name);
    }

    /**
     * 将过滤后的参数写回request中
     * @param $request
     */
    private function _setXssCleanParam($request)
    {
        //返回过滤后的参数
        if ($request->isJson()) {
            $request->replace(Common::XssCleanRecurse($this->json_params));
        } else {
            $request->replace(Common::XssCleanRecurse($this->query_params));
        }
    }
}