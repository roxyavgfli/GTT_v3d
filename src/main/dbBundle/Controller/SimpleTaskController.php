<?php

namespace main\dbBundle\Controller;

use main\dbBundle\Func\SimpleTaskFunctions\SimpleTaskControllerFunctions;
use main\dbBundle\Func\GlobalFunctions;
use main\dbBundle\Func\InstallationFunctions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SimpleTaskController extends Controller {

    public function gestionsimpletimereportAction(Request $request) {
        $natures = GlobalFunctions::getNature();
        $em = $this->getDoctrine()->getEntityManager();
        $session = $this->getRequest()->getSession();
        if (InstallationFunctions::installVerif($em) == -1) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        if (GlobalFunctions::SessionCheck("user", $session, $em) == -1) {
            return $this->render('maindbBundle:Default:index.html.twig');
        }
        elseif (GlobalFunctions::SessionCheck("user", $session, $em) == -2) {
            return $this->render('maindbBundle:Default:errorpermission.html.twig');
        }
        $user = GlobalFunctions::GetCurrentUser($session, $em);
        if (!GlobalFunctions::isUserInTeam($user)){
            return $this->render('maindbBundle:Default:errorpermission2.html.twig');
        }
        $roles = GlobalFunctions::getUserRoles($session);
        $composants = GlobalFunctions::getFromRepository($em, 'Composants');
        $ssphases = GlobalFunctions::getFromRepository($em, 'Ssphase');
        $phases = GlobalFunctions::getFromRepository($em, 'Phase');
        $societes = GlobalFunctions::getFromRepository($em, 'Societe');
        $services = GlobalFunctions::getFromRepository($em, 'Service');
        $equipes = GlobalFunctions::getFromRepository($em, 'Equipe');
        $activites = GlobalFunctions::getFromRepository($em, 'Activite');
        $partenaires = GlobalFunctions::getFromRepository($em, 'Partenaire');
        $tachestodisplay = SimpleTaskControllerFunctions::mainTreatment($em, $request, $user);
        
        return $this->render('maindbBundle:Default:simpletimereport2.php.twig', array('roles' => $roles,
                    'name' => $user->getNom(),
                    'startdatesearch' => SimpleTaskControllerFunctions::getStartDate($request),
                    'composants' => $composants,
                    'ssphases' => $ssphases,
                    'phases' => $phases,
                    'endatesearch' => SimpleTaskControllerFunctions::getEndDate($request, $user, $em),
                    'naturesearch' => SimpleTaskControllerFunctions::getNatureSearched($request),
                    'surname' => $user->getPrenom(),
                    'mail' => $user->getMail(),
                    'trigramme' => $user->getTrigramme(),
                    'societes' => $societes,
                    'services' => $services,
                    'equipes' => $equipes,
                    'error' => "",
                    'natures' => $natures,
                    'activites' => $activites,
                    'label' => "",
                    'date' => "",
                    'natureselected' => "",
                    'partenaires' => $partenaires,
                    'erreurtemps' => "",
                    'natureedit' => '',
                    'activites2' => $activites,
                    'tachestodisplay' => $tachestodisplay));
    }

}
