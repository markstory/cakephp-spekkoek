<?php
namespace Spekkoek\Test\TestApp\Controller\Admin;

use Cake\Controller\Controller;
use Cake\Network\Exception\NotFoundException;

/**
 * Admin\CommentsController class
 *
 */
class CommentsController extends Controller
{

    /**
     * index method
     *
     * @return \Cake\Network\Response
     */
    public function index()
    {
        $this->response->body('Hello Bob');
        return $this->response;
    }
}
