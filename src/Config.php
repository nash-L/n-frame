<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/20
 * Time: 11:33
 */

namespace NashFrame;


use M1\Env\Parser;
use NashFrame\Traits\InjectorTrait;
use NashInject\Injector;

class Config
{
    use InjectorTrait;

    protected $conf;

    /**
     * Config constructor.
     * @param Injector $injector
     */
    public function __construct(Injector $injector)
    {
        $this->conf = [];
        $this->setInjector($injector);
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
