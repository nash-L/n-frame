<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/20
 * Time: 11:07
 */

namespace NashFrame;

use Doctrine\Common\Annotations\AnnotationReader;
use NashFrame\Annotations\Route;
use NashFrame\Traits\InjectorTrait;
use NashFrame\Util\Http\Exception;
use NashFrame\Util\Http\Request;
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
     * @return mixed
     * @throws Exception
     * @throws \NashInject\Exception\InjectorException
     * @throws \ReflectionException
     */
    public function display(string $env = '.env')
    {
        $this->init($env);
        $routeInfo = $this->getInjector()->execute(function (Config $config, Request $request) {
            $dispatcher = cachedDispatcher(function(RouteCollector $routeCollector) use ($config) {
                $this->scanController($config->get('SYSTEM_CONTROLLER_DIR'), $config->get('SYSTEM_CONTROLLER_NAMESPACE'), $routeCollector, '');
            }, [
                'cacheFile' => $config->get('SYSTEM_RUNTIME_DIR') . DIRECTORY_SEPARATOR . 'route.cache',
                'cacheDisabled' => $config->get('SYSTEM_DEBUG'),
            ]);
            return $dispatcher->dispatch($request->getMethod(), rawurldecode($request->getUrl()));
        });
        return $this->run($routeInfo);
    }

    /**
     * @param string $controllerDirectory
     * @param string $controllerNamespace
     * @param RouteCollector $routeCollector
     * @param string $uriPrefix
     * @param AnnotationReader|null $reader
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function scanController(string $controllerDirectory, string $controllerNamespace, RouteCollector $routeCollector, string $uriPrefix = '', AnnotationReader $reader = null)
    {
        $files = scandir($controllerDirectory);
        $reader = $reader ?? new AnnotationReader;
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $tempFile = $controllerDirectory . DIRECTORY_SEPARATOR . $file;
            if (is_dir($tempFile)) {
                $this->scanController($tempFile, $controllerNamespace . '\\' . $file, $routeCollector, $uriPrefix . '/' . $this->transPath($file), $reader);
            } elseif (is_file($tempFile) && substr($file, -4) === '.php') {
                $classShortName = substr($file, 0, -4);
                if (class_exists($className = $controllerNamespace . '\\' . $classShortName)) {
                    $methods = get_class_methods($className);
                    foreach ($methods as $method) {
                        $defaultRoute = new Route(['value' => $uriPrefix . '/' . $this->transPath($classShortName) . '/' . $this->transPath($method)]);
                        $route = $reader->getMethodAnnotation(new \ReflectionMethod($className, $method), Route::class) ?? $defaultRoute;
                        $routeCollector->addRoute($route->methods ?? $defaultRoute->methods, $route->value ?? $defaultRoute->value, [$className, $method]);
                    }
                }
            }
        }
    }

    /**
     * @param $name
     * @return string
     */
    public function transPath($name)
    {
        return strtolower(preg_replace('/([A-Z])/', '-${1}', lcfirst($name)));
    }

    /**
     * @param array $routeInfo
     * @return mixed
     * @throws Exception
     * @throws \NashInject\Exception\InjectorException
     * @throws \ReflectionException
     */
    private function run(array $routeInfo)
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new Exception(404, '找不到指定的位置');
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new Exception(405, '请求方式不被允许');
            case Dispatcher::FOUND:
                return $this->getInjector()->execute(
                    [$this->getInjector()->make($routeInfo[1][0], $routeInfo[2]), $routeInfo[1][1]],
                    $routeInfo[2]
                );
        }
        throw new Exception('Internal Server Error', 500);
    }

    /**
     * @param string $env
     * @throws \NashInject\Exception\InjectorException
     */
    private function init(string $env)
    {
        $env = is_file($file = $this->rootPath . DIRECTORY_SEPARATOR . $env) ? file_get_contents($file) : $env;
        $this->getInjector()->share(Config::class, [], function (Config $config) use ($env) {
            $config->parse($env);
        });
        $this->getInjector()->share(Request::class, [
            'get' => $_GET, 'post' => $_POST, 'cookie' => $_COOKIE,
            'files' => $_FILES, 'server' => $_SERVER, 'raw' => file_get_contents('php://input')
        ]);
    }
}
