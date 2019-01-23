<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/20
 * Time: 20:43
 */

namespace App\Controller;


use NashFrame\Annotations\Route;

class Index
{
    public function main()
    {}

    /**
     * @Route("/user/{id:\d+}")
     * @param int $id
     * @return string
     */
    public function getId(int $id)
    {
        return 'Hello ' . $id;
    }
}