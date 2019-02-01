<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/31
 * Time: 19:39
 */

namespace NashFrame\Util\View;


class ViewEngine
{
    protected $engine;

    /**
     * ViewEngine constructor.
     * @param string $viewPath
     * @param string $cachePath
     * @param string $extension
     * @param string $charset
     * @param bool $cacheDisabled
     */
    public function __construct(string $viewPath, string $cachePath, string $extension = '.html', string $charset = 'UTF-8', bool $cacheDisabled = false)
    {
        $params = [
            'loader' => new \Mustache_Loader_FilesystemLoader($viewPath, ['extension' => $extension]),
        ];
        if (!$cacheDisabled) {
            $params['cache'] = $cachePath;
            $params['cache_file_mode'] = 0666;
            $params['charset'] = $charset;
        }
        $this->engine = new \Mustache_Engine($params);
    }

    public function render($template, $data)
    {
        return $this->engine->loadTemplate($template)->render($data);
    }
}
