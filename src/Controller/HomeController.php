<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of HomeController
 *
 * @author lpu8er
 * @Route("/")
 */
class HomeController extends Controller {
    /**
     * @Route("/", name="rd_default", host="rd.lpu8er.net")
     * 
     */
    public function rdhome(Request $request, \Psr\Log\LoggerInterface $logger) {
        return $this->render('home.html.twig');
    }
    
    /**
     * @Route("/", name="base_default", host="lpu8er.net")
     * 
     */
    public function home(Request $request, \Psr\Log\LoggerInterface $logger) {
        return $this->render('home.html.twig');
    }
}
