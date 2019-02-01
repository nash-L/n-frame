<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/31
 * Time: 14:25
 */

namespace NashFrame\Util\Router\Annotations;


use Doctrine\Common\Annotations\Annotation;

/**
 * Class Route
 * @package NashFrame\Util\Router\Annotations
 * @Annotation
 * @Annotation\Target("METHOD")
 */
final class Route
{
    /**
     * @var string
     */
    public $value;

    /**
     * @var array
     */
    public $methods;
}
