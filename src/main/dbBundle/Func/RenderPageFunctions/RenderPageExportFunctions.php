<?php

namespace main\dbBundle\Func\RenderPageFunctions;

use main\dbBundle\Func\GlobalFunctions;

class RenderPageExportFunctions{
    
    /**
     * Function used to render page in normal conditions
     * @param EntityManager $em The entity manager
     * @param Session $session The current session
     * @return RenderPage
     */
    function renderNormalPage($em, $session){
        $OneMonAgo = GlobalFunctions::getDateOneMonthAgo();
        $today = GlobalFunctions::getToday();
        $roles = GlobalFunctions::getUserRoles($session);
        $user = GlobalFunctions::getCurrentUser($session, $em);
        return $this->render('maindbBundle:Default:exportsimpletask.html.twig', array('roles' => $roles,
                    'name' => $user->getNom(),
                    'surname' => $user->getPrenom(),
                    'mail' => $user->getMail(),
                    'trigramme' => $user->getTrigramme(),
                    'natures' => GlobalFunctions::getNature(),
                    'utilisateurs' => GlobalFunctions::getAllUsers($em),
                    'utilisateurslength' => count(GlobalFunctions::getAllUsers($em)),
                    'services' => GlobalFunctions::getAllServices($em),
                    'serviceslength' => count(GlobalFunctions::getAllServices($em)),
                    'startdatesearch' => $OneMonAgo,
                    'endatesearch' => $today
        ));
    }
    
    /**
     * Function used to render page with error message
     * @param EntityManager $em The entity manager
     * @param Session $session The current session
     * @param String $errorMessage String with the message
     * @return RenderPage
     */
    function renderErrorPage($em, $session, $errorMessage){
        $OneMonAgo = GlobalFunctions::getDateOneMonthAgo();
        $today = GlobalFunctions::getToday();
        $roles = GlobalFunctions::getUserRoles($session);
        $user = GlobalFunctions::getCurrentUser($session, $em);
        return $this->render('maindbBundle:Default:exportsimpletask.html.twig', array('roles' => $roles,
                    'name' => $user->getNom(),
                    'surname' => $user->getPrenom(),
                    'mail' => $user->getMail(),
                    'trigramme' => $user->getTrigramme(),
                    'natures' => GlobalFunctions::getNature(),
                    'utilisateurs' => GlobalFunctions::getAllUsers($em),
                    'utilisateurslength' => count(GlobalFunctions::getAllUsers($em)),
                    'services' => GlobalFunctions::getAllServices($em),
                    'serviceslength' => count(GlobalFunctions::getAllServices($em)),
                    'startdatesearch' => $OneMonAgo,
                    'endatesearch' => $today,
                    'errorMessage' => $errorMessage
        ));
    }
    
}
