<?php

namespace main\dbBundle\Controller;

use main\dbBundle\Func\RenderPageFunctions\RenderPageExportFunctions;
use main\dbBundle\Func\InstallationFunctions;
use main\dbBundle\Func\GlobalFunctions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/*
 * Class to manage exports
 */

class ExportSimpleTaskController extends Controller {

    
    /**
     * Function used to create the file and download it
     * @param Request $request
     * @return Page
     */
    public function gestionexportsimpletaskAction(Request $request) {       
        $em = $this->getDoctrine()->getEntityManager();
        $session = $this->getRequest()->getSession();
        if (InstallationFunctions::installVerif($em) == -1) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        if (GlobalFunctions::SessionCheck("administrator", $session, $em) == -1) {
            return $this->render('maindbBundle:Default:index.html.twig');
        }
        elseif (GlobalFunctions::SessionCheck("administrator", $session, $em) == -2) {
            return $this->render('maindbBundle:Default:errorpermission.html.twig');
        }
        if ($request->get('exportType')) {
            if (ExportActionController::gestionExportAction($request) == -1){
                $errorMessage = "Please provide interval to export";
                return RenderPageExportFunctions::renderErrorPage($em, $session, $errorMessage);
            }
        }
        return RenderPageExportFunctions::renderNormalPage($em, $session);
    }
}
