<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/31
 * Time: 13:41
 */

namespace NashFrame;


use Medoo\Medoo;
use NashFrame\Util\Http\Request;
use NashFrame\Util\Router\Router;
use NashFrame\Util\Config\Config;
use NashFrame\Util\View\ViewEngine;
use NashFrame\Util\View\ViewModel;
use NashInject\Injector;

class App extends Injector
{
    protected $event = [];

    /**
     * @return mixed
     * @throws \Throwable
     */
    public function display()
    {
        try {
            $request = $this->make(Request::class);
            list($method, $uri) = $request->getServer(['REQUEST_METHOD', 'REQUEST_URI']);
            $uriInfo = explode('?', $uri);
            $routeInfo = $this->make(Router::class)->dispatch(
                strtoupper($method),
                rawurldecode($uriInfo[0])
            );
            $templatePath = $routeInfo[1][2];
            $result = $this->run($routeInfo[1], array_merge($routeInfo[2], $request->getQuery()));
        } catch (\Throwable $e) {
            if (isset($this->event['exception']) && is_callable($this->event['exception'])) {
                $result = call_user_func($this->event['exception'], $e, $this);
                $templatePath = 'error';
            } else {
                throw $e;
            }
        }
        return $this->transResult($result, $templatePath);
    }

    /**
     * @param $handler
     * @param $vars
     * @return mixed
     * @throws \NashInject\Exception\InjectorException
     * @throws \ReflectionException
     */
    protected function run($handler, $vars)
    {
        $controller = $this->make($handler[0], $vars);
        return $this->execute([$controller, $handler[1]], $vars);
    }

    /**
     * @param $result
     * @param $templatePath
     * @return false|null|string
     * @throws \NashInject\Exception\InjectorException
     * @throws \ReflectionException
     */
    protected function transResult($result, $templatePath)
    {
        if (is_string($result)) {
            return $result;
        }
        if ($result instanceof ViewModel) {
            header('Content-Type: text/html;charset=utf-8');
            return $this->make(ViewEngine::class)->render(
                $result->getTemplate() ?? $templatePath,
                $result
            );
        } elseif (isset($result)) {
            header('Content-Type: text/json;charset=utf-8');
            return json_encode($result);
        }
        return null;
    }

    /**
     * @param string $eventName
     * @param callable $eventCall
     * @return $this
     */
    public function on(string $eventName, callable $eventCall)
    {
        $this->event[$eventName] = $eventCall;
        return $this;
    }

    /**
     * @param string $evnFile
     * @return App
     * @throws \NashInject\Exception\InjectorException
     * @throws \ReflectionException
     */
    public static function createFPM(string $evnFile): self
    {
        $app = new App;

        $app->share(Config::class, function () use ($evnFile) {
            return new Config($evnFile);
        });

        $app->share(Router::class, function (Config $config) {
            return new Router(
                $config->get('SYSTEM_CONTROLLER_DIR'),
                $config->get('SYSTEM_CONTROLLER_NAMESPACE'),
                $config->get('SYSTEM_RUNTIME_DIR') . '/route.cache',
                $config->get('SYSTEM_DEBUG')
            );
        });

        $app->share(Request::class, function () {
            return new Request($_GET, $_POST, $_SERVER, $_FILES, $_COOKIE, file_get_contents('php://input'));
        });

        $app->share(ViewEngine::class, function (Config $config) {
            return new ViewEngine(
                $config->get('VIEW_PATH'),
                $config->get('VIEW_CACHE_PATH'),
                $config->get('VIEW_EXTENSION'),
                $config->get('VIEW_CHARSET'),
                $config->get('SYSTEM_DEBUG')
            );
        });

        $app->share(Medoo::class, function (Config $config){
            $args = ['database_type' => $config->get('DB_TYPE')];
            if ($args['database_type'] === 'sqlite') {
                $args['database_file'] = $config->get('DB_FILE');
            } else {
                $args['database_name'] = $config->get('DB_DBNAME');
                $args['server'] = $config->get('DB_HOST');
                $args['username'] = $config->get('DB_USERNAME');
                $args['password'] = $config->get('DB_PASSWORD');
                if ($port = $config->get('DB_PORT')) {
                    $args['port'] = $port;
                }
                if ($charset = $config->get('DB_CHARSET')) {
                    $args['charset'] = $charset;
                }
                if ($driver = $config->get('DB_DRIVER')) {
                    $args['driver'] = $driver;
                }
            }
            return new Medoo($args);
        });

        $config = $app->make(Config::class);

        if (!$config->get('SYSTEM_DEBUG')) {
            $app->on('exception', function (\Throwable $throwable) {
                $code = $throwable->getCode();
                if ($code == 404 || $code == 500) {
                    header("HTTP/1.0 {$code} " . $throwable->getMessage());
                }
            });
        }
        return $app;
    }
}
