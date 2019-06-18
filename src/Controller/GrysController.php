<?php
namespace App\Controller;

use Exception;
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
    protected $err = [];
    
    /**
     * @Route("/", name="grys_default", host="grys.ovh")
     * 
     */
    public function home(Request $request) {
        return $this->render('grys.html.twig', ['err' => $this->err,]);
    }
    
    /**
     * @Route("/dl/{f}", name="grys_dl", host="grys.ovh")
     * 
     */
    public function dl(Request $request, $f) {
        try {
            $bp = $this->getParameter('kernel.project_dir').'/'.$this->getParameter('dir.downloads');
            if(!empty($bp) && !empty($f) && preg_match('`^([a-zA-Z0-9_-]+)\.([a-z]+)$`iU', $f)) {
                $path = $bp.'/'.$f;
                $ff = new File($path);
                if($ff->isFile()) {
                    $returns = $this->file($path);
                } else {
                    $this->err[] = 'Not found';
                    $returns = $this->home($request, $logger);
                }
            } else {
                $this->err[] = 'Invalid file';
                $returns = $this->home($request, $logger);
            }
        } catch(Exception $e) {
            $this->err[] = $e->getMessage();
            $returns = $this->home($request, $logger);
        }
        return $returns;
    }
}
