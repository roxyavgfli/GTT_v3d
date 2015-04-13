<?php

namespace main\dbBundle\Controller;

use main\dbBundle\Func\GlobalFunctions;
use main\dbBundle\Func\InstallationFunctions;
use main\dbBundle\Func\SimpleTaskFunctions\SimpleTaskControllerFunctions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class NewSimpleTaskController extends Controller {

    public function gestionnewsimpletimereportAction(Request $request) {
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
        $clients = GlobalFunctions::getFromRepository($em, 'Client');
        $produits = GlobalFunctions::getFromRepository($em, 'Produit');
        $versions = GlobalFunctions::getFromRepository($em, 'Version');
        $services = GlobalFunctions::getFromRepository($em, 'Service');
        $equipes = GlobalFunctions::getFromRepository($em, 'Equipe');
        $activites = GlobalFunctions::getFromRepository($em, 'Activite');
        $partenaires = GlobalFunctions::getFromRepository($em, 'Partenaire');
        $tachestodisplay = SimpleTaskControllerFunctions::mainTreatment($em, $request, $user, $session);
        $times = SimpleTaskControllerFunctions::getTimes($em, $request, $user);
        $disableproduits = null;
        $disableclients = null;
        if ($request->get('nature') == 'Product') {
            $disableclients = 1;
            $disableproduits = 0;
        }
        else if ($request->get('nature') == 'Pre Sale' || $request->get('nature') == 'Project') {
            $disableproduits = 1;
            $disableclients = 0;
        }
        $errormessage = SimpleTaskControllerFunctions::getErrorMessage($times);

        return $this->render('maindbBundle:Default:simpletimereport2.php.twig', array('roles' => $roles,
                    'tasks' => $tachestodisplay,
                    'startdatesearch' => SimpleTaskControllerFunctions::getStartDate($request, $session),
                    'composants' => $composants,
                    'ssphases' => $ssphases,
                    'phases' => $phases,
                    'endatesearch' => SimpleTaskControllerFunctions::getEndDate($request, $user, $em, $session),
                    'naturesearch' => SimpleTaskControllerFunctions::getNatureSearched($request),
                    'name' => $user->getNom(),
                    'surname' => $user->getPrenom(),
                    'mail' => $user->getMail(),
                    'trigramme' => $user->getTrigramme(),
                    'societes' => $societes,
                    'services' => $services,
                    'equipes' => $equipes,
                    'natures' => GlobalFunctions::getNature(),
                    'activites' => SimpleTaskControllerFunctions::getNaturesToDisplay($em, $request),
                    'activites2' => $activites,
                    'label' => $request->get('fname'),
                    'date' => $request->get('date'),
                    'natureselected' => $request->get('nature'),
                    'times' => $times,
                    'clients' => $clients,
                    'produits' => $produits,
                    'versions' => $versions,
                    'plateformes' => GlobalFunctions::getFromRepository($em, 'Plateforme'),
                    'partenaires' => $partenaires,
                    'erreurtemps' => $errormessage,
                    'natureedit' => '',
                    'produitsavecversions' => json_encode(SimpleTaskControllerFunctions::getArrayProduitsToDisplay($em)),
                    'clientsa' => json_encode(SimpleTaskControllerFunctions::getArrayClientToDisplay($em)),
                    'disableclients' => $disableclients,
                    'disableproduits' => $disableproduits,
                    'tachestodisplay' => $tachestodisplay));
    }

}
