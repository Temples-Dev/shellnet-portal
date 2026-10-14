<?php

namespace App\Services\RouterOS;

use App\Models\Router;
use RouterOS\Client;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;

class RouterOSClientFactory
{
    /**
     * Build a connected RouterOS API client from a Router model.
     *
     * @throws ConnectException
     * @throws QueryException
     */
    public static function make(Router $router, int $timeout = 10): Client
    {
        return new Client([
            'host' => $router->clean_ip,
            'port' => $router->api_port,
            'user' => $router->username,
            'pass' => $router->password,
            'timeout' => $timeout,
            'attempts' => 1,
            'delay' => 0,
        ]);
    }
}
