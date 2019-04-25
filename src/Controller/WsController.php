<?php
namespace App\Controller;

use App\Service\ExperimentSocket;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of WsController
 *
 * @author lpu8er
 * @Route("/ews")
 */
class WsController extends Controller {
    /**
     * @Route("/")
     * 
     */
    public function index(Request $request, ExperimentSocket $socket) {
        $returns = $socket->handshake($request);
        if(empty($returns)) {
            $returns = $this->createAccessDeniedException();
        }
        return $returns;
    }
}
