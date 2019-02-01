<?php
/**
 * Created by PhpStorm.
 * User: 18695
 * Date: 2019/1/20
 * Time: 20:43
 */

namespace App\Controller;



use NashFrame\Util\Router\Annotations\Route;
use NashFrame\Util\View\ViewModel;

class Index
{
    /**
     * @Route("/")
     */
    public function main(ViewModel $viewModel)
    {
        $viewModel->assign('name', 'nash');
        return $viewModel;
    }

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