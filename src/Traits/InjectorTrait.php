<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/20
 * Time: 11:57
 */

namespace NashFrame\Traits;


use NashInject\Injector;

trait InjectorTrait
{
    private $injector;

    /**
     * @param Injector $injector
     */
    public function setInjector(Injector $injector): void
    {
        $this->injector = $injector;
    }

    /**
     * @return Injector
     */
    public function getInjector(): Injector
    {
        return $this->injector;
    }
}
