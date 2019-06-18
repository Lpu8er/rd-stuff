<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of GrysController
 *
 * @author lpu8er
 * @Route("/")
 */
class GrysController extends Controller {
    /**
     * @Route("/", name="grys_default", host="grys.ovh")
     * 
     */
    public function home(Request $request, \Psr\Log\LoggerInterface $logger) {
        return $this->render('grys.html.twig');
    }
}
