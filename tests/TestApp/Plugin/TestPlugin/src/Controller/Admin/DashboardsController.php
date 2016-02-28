<?php
namespace TestPlugin\Controller\Admin;

use Cake\Controller\Controller;

class DashboardsController extends Controller
{
    public function index()
    {
        $this->autoRender = false;
    }
}
