Integration of Slim Framework and Swoole
----------------------------------------
This package provides an integration of [Slim Framework](https://www.slimframework.com/)
and event-based async [Swoole](https://github.com/swoole/swoole-src) library.

Install from composer:
```sh
composer require skoro/slim-swoole-integration
```

Example (`server.php`):
```php
$server = new \Swoole\Http\Server('localhost', 9501);
$app = \Slim\Factory\AppFactory::create();

$app->get('/', function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
    $response->getBody()->write('Hello');
    return $response;
});

$server->on('request', \Slim\Swoole\ServerRequestFactory::createRequestCallback($app));

$server->start();
```
After starting above server with `php server.php` command
it will be listening on `localhost` and `9501` port.

### Hot code reloading

Because Swoole works in a different way than PHP-FPM it doesn't reload the sources
you changed between the requests as it PHP-FPM does. When you changed the sources you have
to restart the server to apply your source changes and that can become annoying.

This library provides automatic server reloading depending on the file system changes,
but it relies on PHP pecl `inotify` extension, and it should be installed first.

Please keep in mind, Swoole cannot handle included PHP files before `WorkerStart` 
event: https://www.swoole.co.uk/docs/modules/swoole-server-reload#checking-loaded-files.

Example:
```php
// $server is created in the above example.
$server->on('start', function ($server) {
    $watcher = new \Slim\Swoole\FileWatchers\InotifyWatcher();
    $watcher->addFilePath('path to your project sources');

    // Reloader tracks the changes every 1000 ms.
    $reloader = new \Slim\Swoole\HotCodeReloader($watcher, $server, 1000);
    $reloader->start();
});
```
