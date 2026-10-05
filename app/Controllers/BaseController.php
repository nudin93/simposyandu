<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 */
abstract class BaseController extends Controller
{
    protected $request;
    protected $helpers = ['posyandu', 'permission', 'opensid', 'legacy_db'];
    protected $session;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->session = session();
    }

    protected function view(string $name, array $data = []): string
    {
        return view($name, $data);
    }
}
