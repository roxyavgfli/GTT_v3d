<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use main\dbBundle\Entity\Activite;
use main\dbBundle\Entity\UserRole;
use main\dbBundle\Entity\Client;
use main\dbBundle\Entity\Composants;
use main\dbBundle\Entity\Partenaire;
use main\dbBundle\Entity\PhaseSsphaseActivite;
use main\dbBundle\Entity\Plateforme;
use main\dbBundle\Entity\Produit;
use main\dbBundle\Entity\ProduitComposant;
use main\dbBundle\Entity\ProduitPlateforme;
use main\dbBundle\Entity\ProduitVersion;
use main\dbBundle\Entity\Societe;
use main\dbBundle\Entity\Service;
use main\dbBundle\Entity\Phase;
use main\dbBundle\Entity\Equipe;
use main\dbBundle\Entity\Ssphase;
use main\dbBundle\Entity\Utilisateur;
use main\dbBundle\Entity\Version;
use main\dbBundle\modals\Login;
use main\dbBundle\Entity\Role;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

$natures = array(
    "Abscence",
    "Intern",
    "Product",
    "Pre Sale",
    "Project"
);

function installAction(Request $request) {
    //installing essential components
    if ($this->installVerif($request)) {
        return $this->render('maindbBundle:Default:index.html.twig');
    }
    $em = $this->getDoctrine()->getEntityManager();
    $repository = $em->getRepository('maindbBundle:Role');
    $rolesOk = false;
    $roles = $repository->findAll();
    if ($request->getMethod() == 'POST' && $request->get('rolereset')) {
        if ($roles) {
            foreach ($roles as $role) {
                $em->remove($role);
                $em->flush();
            }
        }
        $newuserrole = new Role();
        $newuserrole->setNom('user');
        $newuserrole->setId(1);
        $em->persist($newuserrole);
        $newuserrole = new Role();
        $newuserrole->setNom('team leader');
        $newuserrole->setId(2);
        $em->persist($newuserrole);
        $newuserrole = new Role();
        $newuserrole->setNom('administrator');
        $newuserrole->setId(3);
        $em->persist($newuserrole);
        $metadata = $em->getClassMetaData(get_class($newuserrole));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $em->flush();

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

        return $this->render('maindbBundle:Default:index.html.twig');
    }
    return $this->render('maindbBundle:Default:installation.html.twig');
}

function getPermissionUser(Utilisateur $user) {
    $em = $this->getDoctrine()->getManager();
    $query = $em->createQuery(
            'SELECT r.nom
                                    FROM maindbBundle:Role r, maindbBundle:Utilisateur u, maindbBundle:UserRole c
                                    WHERE u.mail = ?1 AND u.id = c.userId AND c.roleId = r.id
                                    ORDER BY r.nom
                                    '
    );
    $usermail = $user->getMail();
    $query->setParameter(1, $usermail);
    $roles = $query->getArrayResult();
    return $roles;
}

function installVerif(Request $request) {
    //installing essential components
    $em = $this->getDoctrine()->getEntityManager();
    //to edit when something better will be found... V2.O1 should be correcting it
    $repository = $em->getRepository('maindbBundle:Role');
    $rolesOk = false;
    $roles = $repository->findAll();

    if ($roles) {
        if (sizeof($roles) == 3) {
            if ($roles[0]->getId() == 1 && $roles[0]->getNom() == 'user' && $roles[1]->getId() == 2 && $roles[1]->getNom() == 'team leader' && $roles[2]->getId() == 3 && $roles[2]->getNom() == 'administrator') {

                $rolesOk = true;
            }
        }
    }
    if ($rolesOk) {
        return (1);
    }
    else {
        return (0);
    }
}
