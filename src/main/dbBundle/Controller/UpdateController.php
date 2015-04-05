<?php

namespace main\dbBundle\Controller;

use main\dbBundle\Func\SimpleTaskFunctions\SimpleTaskControllerFunctions;
use main\dbBundle\Func\GlobalFunctions;
use main\dbBundle\Func\InstallationFunctions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UpdateController extends Controller {

    public function gestionupdateAction(Request $request) {
        $this->em = $this->getDoctrine()->getManager();
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $em = $this->getDoctrine()->getEntityManager();
        $session = $this->getRequest()->getSession();
        $message = null;
        if (InstallationFunctions::installVerif($em) == -1) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        if (GlobalFunctions::SessionCheck("user", $session, $em) == -1) {
            return $this->render('maindbBundle:Default:index.html.twig');
        }
        elseif (GlobalFunctions::SessionCheck("administrator", $session, $em) == -2) {
            return $this->render('maindbBundle:Default:errorpermission.html.twig');
        }
        $user = GlobalFunctions::GetCurrentUser($session, $em);
        $roles = GlobalFunctions::getUserRoles($session);
        if ($request->getMethod() == 'POST' && $request->get('update') == 1) {
            try {
                set_time_limit(20000);
                ini_set('memory_limit', '-1');
                $entity = GlobalFunctions::getEntitiesArray();
                //$updatemsg = GlobalFunctions::update($em);
                //$message = $message . $updatemsg;
                //GlobalFunctions::updateNullValues($em, $entity);
                //$message = $message . ", updated null values";
                GlobalFunctions::updateTasks($em);
                $message = $message . ", updated Simple Tasks null values";
            }
            catch (Exception $ex) {
                return $this->render('maindbBundle:Default:updatepage.html.twig', array('roles' => $roles,
                            'name' => $user->getNom(),
                            'startdatesearch' => SimpleTaskControllerFunctions::getStartDate($request),
                            'surname' => $user->getPrenom(),
                            'mail' => $user->getMail(),
                            'message' => $message,
                            'erroreMessage' => $ex->getMessage(),
                            'trigramme' => $user->getTrigramme()));
            }
        }

        return $this->render('maindbBundle:Default:updatepage.html.twig', array('roles' => $roles,
                    'name' => $user->getNom(),
                    'startdatesearch' => SimpleTaskControllerFunctions::getStartDate($request),
                    'surname' => $user->getPrenom(),
                    'mail' => $user->getMail(),
                    'message' => $message,
                    'trigramme' => $user->getTrigramme()));
    }

}
