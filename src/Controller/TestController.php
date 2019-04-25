<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of TestController
 *
 * @author lpu8er
 * @Route("/test")
 */
class TestController extends Controller {
    /**
     * @Route("")
     * 
     */
    public function home(Request $request) {
        return $this->render('test.html.twig');
    }
}
