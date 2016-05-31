<?php
/**
 * Created by PhpStorm.
 * User: Icyboy <icyboy@me.com>
 * Date: 2016/5/19
 * Time: 14:19
 */

namespace Icyboy\Core\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Laravel\Lumen\Application;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;

class ConfigureLogging
{
    protected $config;
    protected $defaultLogPath;
    protected $defaultLogName;
    protected $monoLog;
    protected $handler;
    protected $logPath;

    /**
     * 设置应用的Monolog处理程序
     *
     * @param  \Laravel\Lumen\Application $app
     * @param  \Monolog\Logger $monoLog
     * @return mixed
     */
    public function configureHandlers(Application $app, Logger $monoLog)
    {
        $method               = 'configure' . ucfirst($app['config']['log.log_type']) . 'Handler';
        $this->config         = $app->make('config');
        $this->defaultLogPath = $app->storagePath() . '/logs/';
        $this->defaultLogName = 'lumen';
        $this->monoLog        = $monoLog;

        if ($this->config->get('app.log_path')) {
            $this->logPath = rtrim($this->config->get('log.log_path'), '/') . '/';
        } else {
            $this->logPath = $this->defaultLogPath;
        }

        return $this->{$method}();
    }

    /**
     * 设置应用single模式下的Monolog处理程序
     *
     * @return mixed
     */
    protected function configureSingleHandler()
    {
        $path = $this->logPath . $this->config->get('log.log_name', $this->defaultLogName) . '.log';
        $this->handler = new StreamHandler($path);

        return $this->pushProcessorHandler();
    }

    /**
     * 设置应用daily模式下的Monolog处理程序
     *
     * @return mixed
     */
    protected function configureDailyHandler()
    {
        $path = $this->logPath . $this->config->get('log.log_name', $this->defaultLogName) . '.log';
        $this->handler = new RotatingFileHandler($path, $this->config->get('app.log_max_files', 5));

        return $this->pushProcessorHandler();
    }

    /**
     * 设置应用syslog模式下的Monolog处理程序
     *
     * @return mixed
     */
    protected function configureSyslogHandler()
    {
        $this->handler = new SyslogHandler($this->config->get('app.app_name',
            $this->defaultLogName));

        return $this->pushProcessorHandler();
    }

    /**
     * 注册Processor与Handler
     *
     * @return mixed
     */
    protected function pushProcessorHandler()
    {
        $this->handler->setFormatter($this->getDefaultFormatter());
        return $this->monoLog->pushProcessor(new LogProcessor($_SERVER))->pushHandler($this->handler);
    }

    /**
     * 设置一个默认的Monolog formatter实例
     *
     * @return \Monolog\Formatter\LineFormatter
     */
    protected function getDefaultFormatter()
    {
        $strFormat = $this->config->get('log.log_format', '');
        $strFormat && $strFormat = rtrim($strFormat, PHP_EOL) . PHP_EOL;

        return new LineFormatter($strFormat, null, true, true);
    }
}