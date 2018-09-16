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
     * @Route("/")
     * 
     */
    public function home(Request $request) {
        return $this->render('home.html.twig');
    }
}
