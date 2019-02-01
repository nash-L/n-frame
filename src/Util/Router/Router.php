<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/31
 * Time: 13:48
 */

namespace NashFrame\Util\Router;


use Doctrine\Common\Annotations\AnnotationReader;
use function FastRoute\cachedDispatcher;
use FastRoute\RouteCollector;
use NashFrame\Util\Router\Annotations\Route;

class Router
{
    protected $dispatcher;

    /**
     * Router constructor.
     * @param string $controllerDirectory
     * @param string $controllerNamespace
     * @param string $cacheFile
     * @param bool $cacheDisabled
     */
    public function __construct(string $controllerDirectory, string $controllerNamespace, string $cacheFile, bool $cacheDisabled = false)
    {
        class_exists(Route::class);
        $this->dispatcher = cachedDispatcher(function (RouteCollector $routeCollector) use ($controllerDirectory, $controllerNamespace) {
            $this->scanController(new AnnotationReader, $routeCollector, $controllerDirectory, $controllerNamespace);
        }, [
            'cacheFile' => $cacheFile,
            'cacheDisabled' => $cacheDisabled
        ]);
    }

    /**
     * @param string $method
     * @param string $uri
     * @return array
     * @throws \Exception
     */
    public function dispatch(string $method, string $uri)
    {
        $routeInfo = $this->dispatcher->dispatch($method, $uri);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                throw new \Exception("Not Found", 404);
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                throw new \Exception("Method Not Allowed", 405);
            case \FastRoute\Dispatcher::FOUND:
                return $routeInfo;
        }
        return $routeInfo;
    }

    /**
     * @param AnnotationReader $annotationReader
     * @param RouteCollector $routeCollector
     * @param string $controllerDirectory
     * @param string $controllerNamespace
     * @param string $uriPrefix
     * @param string $controllerPrefix
     * @throws \ReflectionException
     * @throws \Exception
     */
    protected function scanController(AnnotationReader $annotationReader, RouteCollector $routeCollector, string $controllerDirectory, string $controllerNamespace, string $uriPrefix = '', string $controllerPrefix = DIRECTORY_SEPARATOR)
    {
        if (!is_dir($controllerDirectory)) {
            throw new \Exception('找不到指定的控制器目录');
        }
        $files = scandir($controllerDirectory);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            } elseif (is_dir($path = $controllerDirectory . DIRECTORY_SEPARATOR . $file)) {
                $this->scanController($annotationReader, $routeCollector, $path, $controllerNamespace . '\\' . $file, $uriPrefix . '/' . $this->transPath($file), $controllerPrefix . $file . DIRECTORY_SEPARATOR);
            } elseif (
                substr($file, -4) === '.php'
                && ($classShortName = substr($file, 0, -4))
                && class_exists($className = $controllerNamespace . '\\' . $classShortName)
            ) {
                $methods = get_class_methods($className);
                foreach ($methods as $method) {
                    $defaultTemplate = substr($controllerPrefix, 1) . $classShortName . DIRECTORY_SEPARATOR . $method;
                    if ($route = $annotationReader->getMethodAnnotation(new \ReflectionMethod($className, $method), Route::class)) {
                        $httpMethods = $route->methods ? $route->methods : ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD'];
                        $httpUri = $route->value ? $route->value : ($uriPrefix . '/' . $this->transPath($classShortName) . '/' . $this->transPath($method));
                        $routeCollector->addRoute($httpMethods, $httpUri, [$className, $method, $defaultTemplate]);
                    } else {
                        $routeCollector->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD'], $uriPrefix . '/' . $this->transPath($classShortName) . '/' . $this->transPath($method), [$className, $method, $defaultTemplate]);
                    }
                }
            }
        }
    }

    /**
     * @param string $path
     * @return string
     */
    protected function transPath(string $path)
    {
        return strtolower(preg_replace('/([A-Z])/','-${1}',lcfirst($path)));
    }
}
