<?php
/**
 * Created by PhpStorm.
 * User: Icyboy <icyboy@me.com>
 * Date: 2016/5/19
 * Time: 14:10
 */

return [

    //支持Single,Daily,Syslog
    'log_type'      => env('LOG_TYPE', 'Syslog'),

    /**
     * 应用程序名称
     */
    'app_name'      => env('APP_NAME', 'swmen'),

    /**
     * 日志位置
     */
    'log_path'      => env('LOG_PATH', ''),

    /**
     * 日志文件名称
     */
    'log_name'      => env('APP_NAME', 'swmen'),

    /**
     * 日志文件最大数
     */
    'log_max_files' => env('LOG_DAY', 30),

    /**
     * 格式化日志
     */
    'log_format'    => env('LOG_FORMAT', "[%datetime%] %HTTP_X_REQUEST_ID% %channel%.%level_name%: %message% %context% %extra%\n"),
];