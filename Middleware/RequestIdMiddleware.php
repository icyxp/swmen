<?php
/**
 * Created by PhpStorm.
 * User: Icyboy <icyboy@me.com>
 * Date: 2016/5/19
 * Time: 14:13
 */

namespace Icyboy\Core\Middleware;

use Closure;
use Rhumsaa\Uuid\Uuid;

class RequestIdMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!isset($_SERVER['HTTP_X_REQUEST_ID'])) {
            if ($request->header('HTTP_X_REQUEST_ID')) {
                $_SERVER['HTTP_X_REQUEST_ID'] = $request->headers->get('HTTP_X_REQUEST_ID');
            } else {
                $_SERVER['HTTP_X_REQUEST_ID'] = Uuid::uuid1()->toString();
            }
            $request->server->set('HTTP_X_REQUEST_ID', $_SERVER['HTTP_X_REQUEST_ID']);
        }

        $response = $next($request);
        $response->headers->set('HTTP_X_REQUEST_ID', $_SERVER['HTTP_X_REQUEST_ID'], false);

        return $response;
    }
}