<?php

namespace main\dbBundle\Func;

use main\dbBundle\Entity\UserRole;
use main\dbBundle\Entity\Role;
use main\dbBundle\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

Class InstallationFunctions extends Controller {
    
    /**
     * Used when installing the application to properly define roles
     * @param \main\dbBundle\Func\Request $request
     * @return int 1 if went ok -1 if error
     */
    static function removeRoles(Request $request, $em) {
        $repository = $em->getRepository('maindbBundle:Role');
        $roles = $repository->findAll();
        if ($request->getMethod() == 'POST' && $request->get('rolereset')) {
            if ($roles) {
                GlobalFunctions::removeRolesAction($roles);
            }
            GlobalFunctions::setRoles();
            GlobalFunctions::setAdministrator($request);
            return 1;
        }else{
            return -1;
        }
    }

    /**
     * Used to remove roles from db
     * @param Array $roles roles to remove
     * @param EntityManager $em Entity Manager
     */
    static function removeRolesAction($roles, $em) {
        foreach ($roles as $role) {
            $em->remove($role);
            $em->flush();
        }
    }

    /**
     * used to set roles properly
     * @param EntityManager $em Entity Manager
     */
    static function setRoles($em) {
        $newUserRoleUser = new Role();
        $newUserRoleUser->setNom('user');
        $newUserRoleUser->setId(1);
        $em->persist($newUserRoleUser);
        $newUserRoleTeamLeader = new Role();
        $newUserRoleTeamLeader->setNom('team leader');
        $newUserRoleTeamLeader->setId(2);
        $em->persist($newUserRoleTeamLeader);
        $newUserRoleAdministrator = new Role();
        $newUserRoleAdministrator->setNom('administrator');
        $newUserRoleAdministrator->setId(3);
        $em->persist($newUserRoleAdministrator);
        $metadata = $em->getClassMetaData(get_class($newUserRoleUser));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata2 = $em->getClassMetaData(get_class($newUserRoleTeamLeader));
        $metadata2->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata3 = $em->getClassMetaData(get_class($newUserRoleAdministrator));
        $metadata3->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $em->flush();
    }

    /**
     * Action to set an user administrator from query
     * @param \main\dbBundle\Func\Request $request the query
     * @param EntityManager $em Entity Manager
     */
    static function setAdministrator($request, $em) {
        $newAdmin = new Utilisateur();
        $newAdmin->setActif(1);
        $newAdmin->setMail($request->get('email'));
        $newAdmin->setMdp($request->get('password'));
        $newAdmin->setNom($request->get('ln'));
        $newAdmin->setPrenom($request->get('fn'));
        $newAdmin->setTrigramme($request->get('tri'));
        $em->persist($newAdmin);
        $em->flush();
        $newUserRole = new UserRole();
        $newUserRole->setUserId($newAdmin->getId());
        $newUserRole->setRoleId('1');
        $em->persist($newUserRole);
        $em->flush();
        $newUserRole2 = new UserRole();
        $newUserRole2->setUserId($newAdmin->getId());
        $newUserRole2->setRoleId('3');
        $em->persist($newUserRole2);
        $em->flush();
    }

    /**
     * Action to install application properly
     * @param \main\dbBundle\Func\Request $request
     * @return Integer -1 if installation ok, -2 if not
     */
    static function installAction(Request $request) {
        if (GlobalFunctions::installVerif() == 1) {
            return -1;
        }
        GlobalFunctions::removeRoles($request);
        return -2;
    }

    /**
     * Action to check if application is properly installed
     * @param EntityManager $em Entity Manager
     * @return Integer -1 if not ok 1 if ok
     */
    static function installVerif($em) {
        $repository = $em->getRepository('maindbBundle:Role');
        $rolesOk = false;
        $roles = $repository->findAll();
        if ($roles && sizeof($roles) == 3 && $roles[0]->getId() == 1 && $roles[0]->getNom() == 'user' && $roles[1]->getId() == 2 && $roles[1]->getNom() == 'team leader' && $roles[2]->getId() == 3 && $roles[2]->getNom() == 'administrator') {
            $rolesOk = true;
        }
        if ($rolesOk) {
            return (1);
        }
        else {
            return (-1);
        }
    }
}