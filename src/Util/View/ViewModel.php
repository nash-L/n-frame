<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/31
 * Time: 19:39
 */

namespace NashFrame\Util\View;


class ViewModel
{
    protected $template;

    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function assign($key, $val)
    {
        $this->$key = $val;
    }
}
