<?php

namespace Slim\Swoole;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Swoole\Http\RequestCallback;

class ServerRequestFactory
{
    public static function createRequestCallback(App $app): RequestCallback
    {
        return RequestCallback::fromCallable(
            static fn (ServerRequestInterface $request): ResponseInterface => $app->handle($request)
        );
    }
}