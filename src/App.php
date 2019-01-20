<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/20
 * Time: 11:07
 */

namespace NashFrame;

use NashFrame\Traits\InjectorTrait;
use NashFrame\Util\Http\Request;
use NashFrame\Util\Http\Response;
use NashInject\Injector;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher;

use function FastRoute\cachedDispatcher;

final class App
{
    use InjectorTrait;

    private $rootPath;

    /**
     * App constructor.
     * @param string $rootPath
     * @throws \NashInject\Exception\InjectorException
     */
    public function __construct(string $rootPath)
    {
        $this->setInjector(new Injector);
        $this->rootPath = $rootPath;
    }

    /**
     * @param string $env
     * @throws \NashInject\Exception\InjectorException
     * @throws \ReflectionException
     */
    public function display(string $env = '/.env')
    {
        $this->init($env);
        $routeInfo = $this->getInjector()->execute(function (Config $config, Request $request) {
            $dispatcher = cachedDispatcher(function(RouteCollector $r) use ($config) {
                // 解析路由
            }, [
                'cacheFile' => $config->get('SYSTEM_RUNTIME') . '/route.cache',
                'cacheDisabled' => $config->get('SYSTEM_DEBUG'),
            ]);
            return $dispatcher->dispatch($request->getMethod(), rawurldecode($request->getUrl()));
        });
        $this->run($routeInfo);
    }

    /**
     * @param array $routeInfo
     */
    private function run(array $routeInfo)
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                // ... call $handler with $vars
                break;
        }
    }

    /**
     * @param string $env
     * @throws \NashInject\Exception\InjectorException
     */
    private function init(string $env)
    {
        $env = is_file($file = $this->rootPath . $env) ? file_get_contents($file) : $env;
        $this->getInjector()->share(Config::class, [], function (Config $config) use ($env) {
            $config->parse($env);
        });
        $this->getInjector()->share(Request::class, [
            'get' => $_GET, 'post' => $_POST, 'cookie' => $_COOKIE,
            'files' => $_FILES, 'server' => $_SERVER,
        ]);
        $this->getInjector()->share(Response::class);
    }
}
