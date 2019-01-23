<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/23
 * Time: 22:50
 */

namespace NashFrame\Annotations;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("METHOD")
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

    /**
     * Route constructor.
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (!isset($values['methods'])) {
            $this->methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD'];
        } elseif (is_string($values['methods'])) {
            $this->methods = explode(',', $values['methods']);
        } elseif (is_array($values['methods'])) {
            $this->methods = $values['methods'];
        } else {
            throw new \InvalidArgumentException(
                sprintf('@Route expects either a string value, or an array of strings, "%s" given.',
                    is_object($values['methods']) ? get_class($values['methods']) : gettype($values['methods'])
                )
            );
        }
        $this->value = null;
        if (isset($values['value'])) {
            if (!is_string($values['value'])) {
                throw new \InvalidArgumentException(
                    sprintf('@Route expects either an array of strings, "%s" given.',
                        is_object($values['value']) ? get_class($values['value']) : gettype($values['value'])
                    )
                );
            }
            $this->value = $values['value'];
        }
    }
}
