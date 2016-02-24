<?php
namespace Spekkoek\Test\TestApp\Controller;

use Cake\Controller\Controller;
use Cake\Network\Exception\NotFoundException;

/**
 * BrakesController class
 *
 */
class BrakesController extends Controller
{

    /**
     * The default model to use.
     *
     * @var string
     */
    public $modelClass = 'Posts';

    /**
     * startup process.
     */
    public function startupProcess()
    {
        parent::startupProcess();
        if ($this->request->param('stop') === 'startup') {
            $this->response->body('startup stop');
            return $this->response;
        }
    }

    /**
     * shutdown process.
     */
    public function shutdownProcess()
    {
        parent::shutdownProcess();
        if ($this->request->param('stop') === 'shutdown') {
            $this->response->body('shutdown stop');
            return $this->response;
        }
    }

    public function index()
    {
        $this->autoRender = false;
    }
}
