#swmen

Swoole + Lumen

## Depends On

- php >= 5.5.9
- lumen >= 5.2.*
- ext-swoole >= 1.8.5

##Install

```shell
 composer install
```

由于Lumen不支持使用Http Kernel，所以你必须在App目录创建一个Application.php，内容如下

```php
<?php
/**
 * Created by PhpStorm.
 * User: Icyboy <icyboy@me.com>
 * Date: 2016/5/19
 * Time: 11:07
 */

namespace App;

class Application extends \Laravel\Lumen\Application
{

    public function getMiddleware()
    {
        return $this->middleware;
    }

    public function callTerminableMiddleware($response)
    {
        parent::callTerminableMiddleware($response);
    }
}
```
然后修改bootstrap目录下的app.php 
```php
//替换如下内容
//$app = new Laravel\Lumen\Application(
//    realpath(__DIR__.'/../')
//);

$app = new App\Application(
    realpath(__DIR__.'/../')
);
```

##Usage

```shell
 vendor/bin/swmen start | stop | reload | restart | quit
```

##Config

In .env , use SWMEN_* to config swoole server. For example

```
SWMEN_REACTOR_NUM=1
SWMEN_WORKER_NUM=4
SWMEN_BACKLOG=128
SWMEN_DISPATCH_MODE=1
```


###pid_file
-----------

```INI
 SWMEN_PID_FILE=/path/to/swmen.pid
```
default is at /lumen/storage/logs/swmen.pid

###gzip
-------

```INI
 SWMEN_GZIP=1
```

level is in the range from 1 to 9, bigger is compress harder and use more CPU time.

```INI
 SWMEN_GZIP_MIN_LENGTH=1024
```

Sets the mINImum length of a response that will be gzipped.

###deal\_with\_public
---------------------

Use this ***ONLY*** when developing

```INI
 SWMEN_DEAL_WITH_PUBLIC=true
```

###Swoole
---------

Eexample:

```INI
 SWMEN_HOST=0.0.0.0
```

Default host is 127.0.0.1:9050

See Swoole's document:

[简体中文](http://wiki.swoole.com/wiki/page/274.html)

[English](https://cdn.rawgit.com/tchiotludo/swoole-ide-helper/dd73ce0dd949870daebbf3e8fee64361858422a1/docs/classes/swoole_server.html#method_set)

##Work with nginx
-----------------

```Nginx
server {
	listen       80;
	server_name  localhost;

	root /path/to/swmen/public;

	location ~ \.(png|jpeg|jpg|gif|css|js)$ {
		break;
	}

	location / {
		proxy_set_header   Host $host:$server_port;
		proxy_set_header   X-Real-IP $remote_addr;
		proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
		proxy_http_version 1.1;

		proxy_pass http://127.0.0.1:8088;
	}
}
```

#License
[MIT](LICENSE)