<?php

namespace App\Services\RouterOS;

use App\Models\Router;
use RouterOS\Client;

class RouterOSClientFactory
{
    /**
     * Build a connected RouterOS API client from a Router model.
     *
     * @throws \RouterOS\Exceptions\ConnectException
     * @throws \RouterOS\Exceptions\QueryException
     */
    public static function make(Router $router, int $timeout = 10): Client
    {
        return new Client([
            'host'     => $router->clean_ip,
            'port'     => $router->api_port,
            'user'     => $router->username,
            'pass'     => $router->password,
            'timeout'  => $timeout,
            'attempts' => 1,
            'delay'    => 0,
        ]);
    }
}
