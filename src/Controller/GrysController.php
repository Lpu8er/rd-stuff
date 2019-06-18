<?php
namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
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
    public function home(Request $request, LoggerInterface $logger) {
        return $this->render('grys.html.twig');
    }
    
    /**
     * @Route("/dl/{f}", name="grys_dl", host="grys.ovh")
     * 
     */
    public function dl(Request $request, LoggerInterface $logger, $f) {
        $bp = $this->getParameter('dir.downloads');
        if(!empty($bp) && !empty($f) && preg_match('`^([a-zA-Z0-9_-]+)\.([a-z]+)$`iU', $f)) {
            $path = $bp.'/'.$f;
            $ff = new File($path);
            if($ff->isFile()) {
                $returns = $this->file($path);
            } else {
                $returns = $this->createNotFoundException('File "'.$path.'" not found');
            }
        } else {
            $returns = $this->home($request, $logger);
        }
        return $returns;
    }
}
