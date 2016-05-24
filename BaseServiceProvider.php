<?php
/**
 * Created by PhpStorm.
 * User: Icyboy <icyboy@me.com>
 * Date: 2016/5/19
 * Time: 14:07
 */

namespace Icyboy\Core;

use Illuminate\Support\ServiceProvider;
use Monolog;

class BaseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;
        $errorCodeConfigPath = __DIR__ . '/config/error_code.php';
        $logConfigPath = __DIR__ . '/config/log.php';

        //避免array_merge数字索引被打乱
        $error_local_config = $this->app['config']->get('error_code', []);
        $app['config']->set('error_code', array_replace(require $errorCodeConfigPath, $error_local_config));

        $this->mergeConfigFrom($logConfigPath, 'log');

        $app->configureMonologUsing(function (Monolog\Logger $monoLog) use ($app) {
            $configureLogging = new Log\ConfigureLogging();
            return $configureLogging->configureHandlers($app, $monoLog);
        });

        //加载RequestId Middleware
        $app->middleware([
            Middleware\RequestIdMiddleware::class
        ]);
    }

    public function boot()
    {
        // 内部编码
        if ( function_exists('mb_internal_encoding') ) {
            mb_internal_encoding(env('ENCODING','utf-8'));
        }
        // 正则用编码
        if ( function_exists('mb_regex_encoding') ) {
            mb_regex_encoding(env('ENCODING','utf-8'));
        }
    }
}