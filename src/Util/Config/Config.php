<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/31
 * Time: 14:03
 */

namespace NashFrame\Util\Config;


use M1\Env\Parser;

class Config
{
    protected $conf;

    /**
     * Config constructor.
     * @param string $env
     */
    public function __construct(string $env)
    {
        $this->conf = [];
        is_file($env) ? $this->loadFile($env) : $this->parse($env);
    }

    /**
     * @param string $fileName
     */
    public function loadFile(string $fileName)
    {
        $this->parse(file_get_contents($fileName));
    }

    /**
     * @param string $content
     */
    public function parse(string $content)
    {
        $this->conf = array_merge($this->conf, Parser::parse($content, get_defined_constants(true)['user']));
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->conf[$key] ?? null;
    }
}
