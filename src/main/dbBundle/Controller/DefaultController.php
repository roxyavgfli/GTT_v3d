<?php

namespace main\dbBundle\Controller;

use main\dbBundle\Func\GlobalFunctions;
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
use main\dbBundle\Entity\ActiviteNature;
use main\dbBundle\Entity\Utilisateur;
use main\dbBundle\Entity\Version;
use main\dbBundle\modals\Login;
use main\dbBundle\Entity\Role;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

//Global code structure :
//    for each fonction 
//        - checking user connexion
//        - checking user permission
//        - do specific treatment


class DefaultController extends Controller {

    private $natures = array(
        "Absence",
        "Internal",
        "Product",
        "Pre Sale",
        "Project"
    );

    public function installAction(Request $request) {
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
            $newAdmin->setMdp(md5($request->get('password')));
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

    public function installVerif(Request $request) {
        //installing essential components
        $em = $this->getDoctrine()->getEntityManager();
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

    public function indexAction(Request $request) {
        //checking installation
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        //checking session
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($request->getMethod() == 'POST') {
            $session = $this->getRequest()->getSession();
            $session->clear();
            $usermail = $request->get('email');
            $password = md5($request->get('password'));
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user && $user->getActif() == 1) {
                $em = $this->getDoctrine()->getManager();
                $query = $em->createQuery(
                        'SELECT r.nom
                                    FROM maindbBundle:Role r, maindbBundle:Utilisateur u, maindbBundle:UserRole c
                                    WHERE u.mail = ?1 AND u.id = c.userId AND c.roleId = r.id
                                    ORDER BY r.nom
                                    '
                );

                $query->setParameter(1, $usermail);
                $roles = $query->getArrayResult();
                //login
                $login = new Login();
                $login->setMail($usermail);
                $login->setPassword($password);
                $login->setPermission($roles);

                $session->set('login', $login);

                return $this->render('maindbBundle:Default:home.html.twig', array('name' => $user->getNom(), 'surname' => $user->getPrenom(), 'roles' => $roles));
            }
            else {
                return $this->render('maindbBundle:Default:index.html.twig', array('name' => 'Login Failed'));
            }
        }
        //session already exists
        else {
            if ($session->has('login')) {
                $login = $session->get('login');
                $usermail = $login->getMail();
                $password = $login->getPassword();

                $roles = $login->getPermission();
                $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
                if ($user) {
                    return $this->render('maindbBundle:Default:home.html.twig', array('name' => $user->getNom(), 'surname' => $user->getPrenom(), 'roles' => $roles));
                }
            }
            return $this->render('maindbBundle:Default:index.html.twig');
        }
    }

    //logout action
    public function logoutAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $session = $this->getRequest()->getSession();
        $session->clear();
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    //function to get permission from user
    public function getPermissionUser(Utilisateur $user) {
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

    //action when profile page requested
    public function profileAction(Request $request) {
        //checking installation
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        //checking session
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $roles = $login->getPermission();
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            $equipename = "equipe";
            //if session is ok
            if ($user) {
                //treating modifications on user's profile
                if ($request->getMethod() == 'POST') {
                    $usermailnew = $request->get('emailnew');
                    $passwordnew = md5($request->get('passwordnew'));
                    $passwordtest = md5($request->get('passwordtest'));
                    if ($password == $passwordtest) {
                        if ($usermailnew) {
                            $em = $this->getDoctrine()->getEntityManager();
                            $mailtaken = $repository->findOneBy(array('mail' => $usermailnew));
                            if ($mailtaken) {
                                $error = "this email is already used.";
                                return $this->render('maindbBundle:Default:profile.html.twig', array('name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'error' => $error, 'equipename' => $equipename, 'roles' => $roles));
                            }
                            $user->setMail($usermailnew);
                            $login->setMail($usermailnew);
                        }
                        if ($passwordnew) {
                            $user->setMdp($passwordnew);
                            $login->setpassword($passwordnew);
                        }
                        $em->persist($user);
                        $em->flush();
                        $session->set('login', $login);
                        $message = "Saved.";
                        return $this->render('maindbBundle:Default:profile.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'message' => $message, 'equipename' => $equipename));
                    }
                    else {
                        $error = "Wrong password";
                        return $this->render('maindbBundle:Default:profile.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'error' => $error, 'equipename' => $equipename));
                    }
                }
                return $this->render('maindbBundle:Default:profile.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'equipename' => $equipename));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionusereditionAction(Request $request) {
        //checking installation
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        //checking session
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                // page reserved for admin use
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                $message = '';
                $message2 = '';
                $repository = $em->getRepository('maindbBundle:Utilisateur');
                $userToEdit = $repository->findOneBy(array('id' => $request->get('idToEdit')));
                if ($request->getMethod() == 'POST') {
                    if ($request->get('idToEdit')) {
                        $edited = false;
                        if ($request->get('emailEdited')) {
                            $userToEdit->setMail($request->get('emailEdited'));
                            $edited = true;
                        }
                        if ($request->get('fnEdited')) {
                            $userToEdit->setPrenom($request->get('fnEdited'));
                            $edited = true;
                        }
                        if ($request->get('lnEdited')) {
                            $userToEdit->setNom($request->get('lnEdited'));
                            $edited = true;
                        }
                        if ($request->get('trigramEdited')) {
                            $userToEdit->setTrigramme($request->get('trigramEdited'));
                            $edited = true;
                        }
                        $em->persist($userToEdit);
                        $em->flush();
                        if ($request->get('adminEdited') && $request->get('edition')) {
                            $repository = $em->getRepository('maindbBundle:UserRole');
                            $admin = $repository->findOneBy(array('userId' => $request->get('idToEdit'), 'roleId' => 3));
                            if (!$admin) {
                                $newadmin = new UserRole();
                                $newadmin->setUserId($request->get('idToEdit'));
                                $newadmin->setRoleId(3);
                                $em->persist($newadmin);
                                $em->flush();
                                $edited = true;
                            }
                        }
                        elseif ($request->get('edition')) {
                            $repository = $em->getRepository('maindbBundle:UserRole');
                            $admin = $repository->findOneBy(array('userId' => $request->get('idToEdit'), 'roleId' => 3));
                            if ($admin) {
                                $em->remove($admin);
                                $em->flush();
                                $edited = true;
                            }
                        }

                        if ($request->get('resetpw')) {
                            $userToEdit->setMdp(md5($userToEdit->getPrenom() . $userToEdit->getNom()));
                            $em->persist($userToEdit);
                            $em->flush();
                            $edited = true;
                            $message2 = 'remember new password is ' . $userToEdit->getPrenom() . $userToEdit->getNom();
                        }
                        if ($edited) {
                            $message = 'User successfully edited';
                        }
                        $permissions = $this->getPermissionUser($userToEdit);
                        $isadmin = false;
                        $permissionToTest = "administrator";
                        foreach ($permissions as $permission) {
                            foreach ($permission as $perm) {
                                if ($perm == 'administrator') {
                                    $isadmin = true;
                                }
                            }
                        }
                        if ($isadmin) {
                            $administratorToEdit = 1;
                        }
                        else {
                            $administratorToEdit = 0;
                        }
                        $actif = $userToEdit->getActif();
                        

                        return $this->render('maindbBundle:Default:gestionuseredit.html.twig', array('roles' => $roles,
                                    'name' => $user->getNom(),
                                    'surname' => $user->getPrenom(),
                                    'mail' => $user->getMail(),
                                    'trigramme' => $user->getTrigramme(),
                                    'fnToEdit' => $userToEdit->getPrenom(),
                                    'lnToEdit' => $userToEdit->getNom(),
                                    'mailToEdit' => $userToEdit->getMail(),
                                    'trigramToEdit' => $userToEdit->getTrigramme(),
                                    'idToEdit' => $userToEdit->getId(),
                                    'message' => $message,
                                    'message2' => $message2,
                                    'inactive' => $actif,
                                    'administratorToEdit' => $administratorToEdit));
                    }
                }
            }
        }
        return ($this->gestionuserlistAction($request));
    }

    public function gestiondeleteuserAction(Request $request) {
        //checking installation
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        //checking session
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                // page reserved for admin use
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                $message = '';
                $message2 = '';
                $repository = $em->getRepository('maindbBundle:Utilisateur');
                $userToEdit = $repository->findOneBy(array('id' => $request->get('idToEdit')));
                if ($request->getMethod() == 'POST' && $userToEdit->getActif()==0) {
                    $em->remove($userToEdit);
                    $em->flush();
                    return $this->gestionuserlistAction($request);
                }
            }
        }
        return ($this->gestionuserlistAction($request));
    }

    //function to get amount of users
    public function getTotalUsers() {
        $em = $this->getDoctrine()->getEntityManager();
        $countQuery = $em->createQueryBuilder()
                ->select('Count(c)')
                ->from('maindbBundle:Utilisateur', 'c');
        $finalQuery = $countQuery->getQuery();
        $total = $finalQuery->getSingleScalarResult();
        return $total;
    }

    //action when user list page is requested
    public function gestionuserlistAction(Request $request) {
        //checking installation
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $error = '';
        $firstload = $request->get('firstload');
        if (!($request->get('firstload')) && !($request->get('actif')) && !($request->get('group')) && !($request->get('mail')) && !($request->get('firstname')) && !($request->get('lastname'))) {
            $firstload = 1;
        }
        $debug = $firstload;
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        //checking session
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                //setting default filters
                $lastnamefilter = 1;
                $firstnamefilter = 1;
                $trigramfilter = 1;
                $mailfilter = 1;
                $groupfilter = 1;
                $actiffilter = 0;
                //changing filters when administrator changes filters
                if (!($firstload == 1) && !($request->get('lastname'))) {
                    $lastnamefilter = 0;
                }
                if (!($firstload == 1) && !($request->get('firstname'))) {
                    $firstnamefilter = 0;
                }
                if (!($firstload == 1) && !($request->get('trigram'))) {
                    $trigramfilter = 0;
                }
                if (!($firstload == 1) && !($request->get('mail'))) {
                    $mailfilter = 0;
                }
                if (!($firstload == 1) && !($request->get('group'))) {
                    $groupfilter = 0;
                }
                if (!($firstload == 1) && ($request->get('actif'))) {
                    $actiffilter = 1;
                }

                // looking for users according to search
                if (($request->get('search'))) {
                    $page = $request->get('page');
                    $countuser = $this->getTotalUsers();
                    $count_per_page = 1;
                    $totalpages = ceil($countuser / $count_per_page);
                    if (!$page) {
                        $page = 1;
                    }
                    if (!is_numeric($totalpages)) {
                        $page = 1;
                    }
                    else {
                        $page = floor($page);
                    }
                    if ($countuser <= $count_per_page) {
                        $page = 1;
                    }
                    if (($page * $count_per_page > $totalpages)) {
                        $page = ceil($totalpages);
                    }
                    $offset = 0;
                    if ($page > 1) {
                        $offset = $count_per_page * ($page - 1);
                    }
                    $parameter = $request->get('search');
                    $em = $this->getDoctrine()->getManager();
                    //query to get users having team according to search 
                    $query = $em->createQuery(
                            'SELECT c.id, c.nom, c.prenom, c.trigramme, c.mail, c.actif, e.nom AS team, r.roleId AS roleId
                                    FROM maindbBundle:Utilisateur c, maindbBundle:Equipe e, maindbBundle:UserRole r
                                    WHERE c.equipeId IS NOT NULL AND c.equipeId = e.id AND (c.mail LIKE ?1 OR c.nom LIKE ?1 OR c.prenom LIKE ?1 OR c.trigramme LIKE ?1 ) AND r.userId = c.id 
                                    GROUP BY c.id
                                    ORDER BY c.nom
                                    '
                    );
                    $parameters = '%' . $parameter . '%';
                    $query->setParameter(1, $parameters);


                    $users1 = $query->getArrayResult();
                    //querty to get users not having team
                    $query = $em->createQuery(
                            'SELECT c.id, c.nom, c.prenom, c.trigramme, c.mail, c.actif, r.roleId AS roleId
                                    FROM maindbBundle:Utilisateur c, maindbBundle:Equipe e, maindbBundle:UserRole r
                                    WHERE c.equipeId IS NULL AND c.equipeId = e.id AND (c.mail LIKE ?1 OR c.nom LIKE ?1 OR c.prenom LIKE ?1 OR c.trigramme LIKE ?1 ) AND r.userId = c.id 
                                    GROUP BY c.id
                                    ORDER BY c.nom
                                    '
                    );
                    $parameters = '%' . $parameter . '%';
                    $query->setParameter(1, $parameters);
                    $users2 = $query->getArrayResult();
                    //merging both queries
                    $users = array_merge($users1, $users2);
                    if ($request->getMethod() == 'POST') {
                        $todelete = $request->get('todelete');
                        if ($request->getMethod() == 'POST' && $request->get('todelete')) {
                            $repository = $em->getRepository('maindbBundle:Utilisateur');
                            $produitToDelete = $repository->findOneBy(array('id' => $todelete));
                            $permissions = $this->getPermissionUser($produitToDelete);
                            $isTl = false;
                            $permissionToTest = "team leader";
                            foreach ($permissions as $permission) {
                                if ($role[1] = 'team leader') {

                                    $isTl = true;
                                }
                            }
                            if ($isTl) {
                                $error = "cant set a team leader inactive";
                            }
                            else {
                                // DELETION
                                $produitToDelete->setActif(0);
                                $produitToDelete->setEquipeId(1);
                                $em->persist($produitToDelete);
                                $em->flush();
                                unset($todelete);
                            }
                        }
                    }
                    if (!($users)) {
                        return $this->render('maindbBundle:Default:gestionuserlist.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'totalUsers' => $countuser, 'totalpages' => $totalpages, 'currentpage' => $page, 'search' => $parameter, 'firstnamefilter' => $firstnamefilter, 'lastnamefilter' => $lastnamefilter, 'mailfilter' => $mailfilter, 'trigramfilter' => $trigramfilter, 'groupfilter' => $groupfilter, 'actiffilter' => $actiffilter, 'debug' => $debug));
                    }
                    else {
                        return $this->render('maindbBundle:Default:gestionuserlist.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'totalUsers' => $countuser, 'users' => $users, 'totalpages' => $totalpages, 'currentpage' => $page, 'search' => $parameter, 'firstnamefilter' => $firstnamefilter, 'lastnamefilter' => $lastnamefilter, 'mailfilter' => $mailfilter, 'trigramfilter' => $trigramfilter, 'groupfilter' => $groupfilter, 'actiffilter' => $actiffilter, 'debug' => $debug));
                    }
                }
                else {
                    $em = $this->getDoctrine()->getManager();
                    if ($request->getMethod() == 'POST') {
                        $todelete = $request->get('todelete');
                        if ($request->getMethod() == 'POST' && $request->get('todelete')) {
                            $repository = $em->getRepository('maindbBundle:Utilisateur');
                            $produitToDelete = $repository->findOneBy(array('id' => $todelete));
                            $permissions = $this->getPermissionUser($produitToDelete);
                            $isTl = false;
                            $permissionToTest = "team leader";
                            foreach ($permissions as $permission) {
                                foreach ($permission as $perm) {
                                    if ($perm == 'team leader') {
                                        $isTl = true;
                                    }
                                }
                            }
                            if ($isTl) {
                                $error = "cant set a team leader inactive";
                            }
                            else {
                                // DELETION
                                $produitToDelete->setActif(0);
                                $produitToDelete->setEquipeId(1);
                                $em->persist($produitToDelete);
                                $em->flush();
                                unset($todelete);
                            }
                        }
                    }
                    //query without filter for users having team
                    $query = $em->createQuery(
                            'SELECT c.id, c.nom, c.prenom, c.trigramme, c.mail, c.actif, e.nom AS team, r.roleId AS roleId
                                    FROM maindbBundle:Utilisateur c, maindbBundle:Equipe e, maindbBundle:UserRole r
                                    WHERE c.equipeId = e.id AND r.userId = c.id
                                    GROUP BY c.id
                                    ORDER BY c.nom
                                    '
                    );



                    $users1 = $query->getArrayResult();
                    //query without filter for users not having team
                    $query = $em->createQuery(
                            'SELECT c.id, c.nom, c.prenom, c.trigramme, c.mail, c.actif, r.roleId AS roleId
                                    FROM maindbBundle:Utilisateur c, maindbBundle:UserRole r
                                    WHERE c.equipeId IS NULL AND r.userId = c.id
                                    GROUP BY c.id
                                    ORDER BY c.nom
                                    '
                    );
                    $users2 = $query->getArrayResult();
                    $users = array_merge($users1, $users2);
                    //not used
                    $page = $request->get('page');
                    $countuser = count($users);
                    $count_per_page = 1;
                    $totalpages = ceil($countuser / $count_per_page);
                    if (!$page) {
                        $page = 1;
                    }
                    if (!is_numeric($totalpages)) {
                        $page = 1;
                    }
                    else {
                        $page = floor($page);
                    }
                    if ($countuser <= $count_per_page) {
                        $page = 1;
                    }
                    if (($page * $count_per_page > $totalpages)) {
                        $page = ceil($totalpages);
                    }
                    $offset = 0;
                    if ($page > 1) {
                        $offset = $count_per_page * ($page - 1);
                    }

                    return $this->render('maindbBundle:Default:gestionuserlist.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'totalUsers' => $countuser, 'users' => $users, 'totalpages' => $totalpages, 'currentpage' => $page, 'firstnamefilter' => $firstnamefilter, 'lastnamefilter' => $lastnamefilter, 'mailfilter' => $mailfilter, 'trigramfilter' => $trigramfilter, 'groupfilter' => $groupfilter, 'actiffilter' => $actiffilter, 'debug' => $debug, 'error' => $error));
                }
            }
        }

        return $this->render('maindbBundle:Default:index.html.twig');
    }

    // action when creating new user
    public function gestionusercreateAction(Request $request) {
        //checking installation
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        // checking session
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                // page reserved for admin use
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                $em = $this->getDoctrine()->getManager();
                $query = $em->createQuery(
                        'SELECT  e.nom AS team, e.id
                                    FROM  maindbBundle:Equipe e
                                    ORDER BY e.nom'
                );

                $teams = $query->getArrayResult();
                return $this->render('maindbBundle:Default:gestionusercreate.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'teams' => $teams));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    // action concerning activities
    public function gestionactiviteAction(Request $request) {
        //checking installation
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        //checking session
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                // admin only
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                // checking for new activity creation
                if ($request->getMethod() == 'POST') {
                    if ($request->get('name')) {
                        $phase = $request->get('phase');
                        $ssphase = $request->get('ssphase');
                        $nom = $request->get('name');
                        $activitenew = New Activite();
                        $activitenew->setActif(1);
                        $activitenew->setNom($nom);
                        $em->persist($activitenew);
                        $em->flush();

                        foreach ($request->get('nature') as $nat) {
                            $newlinknat = new ActiviteNature();
                            $newlinknat->setActiviteId($activitenew->getId());
                            $newlinknat->setNature($nat);
                            $em->persist($newlinknat);
                            $em->flush();
                        }
                        $activiteId = $activitenew->getId();
                        if ($request->get('subphaseoption')) {
                            $linknew = new PhaseSsphaseActivite();
                            $linknew->setPhaseId('');
                            $linknew->setSsPhaseId($ssphase);
                            $linknew->setActiviteId($activiteId);
                            $em->persist($linknew);
                            $em->flush();
                        }
                        else {
                            $linknew = new PhaseSsphaseActivite();
                            $linknew->setPhaseId('');
                            $linknew->setSsPhaseId('');
                            $linknew->setActiviteId($activiteId);
                            $em->persist($linknew);
                            $em->flush();
                        }
                    }
                    // deletion of activity
                    if ($request->getMethod() == 'POST' && $request->get('todelete')) {
                        $actirepo = $em->getRepository('maindbBundle:Activite');
                        $actitodelete = $actirepo->findOneBy(array('id' => $request->get('todelete')));
                        $actitodelete->setActif(0);
                        $em->persist($actitodelete);
                        $em->flush();
                    }
                }
                // queries to get needed data
                $query = $em->createQuery(
                        'SELECT  e, a.nom as nom2
                                    FROM  maindbBundle:Ssphase e, maindbBundle:Phase a
                                    WHERE e.actif = 1 AND e.phaseId = a.id
                                    ORDER BY e.nom'
                );

                $ssphases = $query->getArrayResult();
                $query = $em->createQuery(
                        'SELECT  e
                                    FROM  maindbBundle:Phase e
                                    WHERE e.actif = 1
                                    ORDER BY e.nom'
                );

                $phases = $query->getArrayResult();

                $activites = GlobalFunctions::getFromRepository($em, 'Activite');
                return $this->render('maindbBundle:Default:gestionactivite.html.twig', array('natures' => $this->natures, 'roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'activites' => $activites, 'phases' => $phases, 'ssphases' => $ssphases));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    //action when creating new user
    public function usercreationAction(Request $request) {
        //checking installation
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        // checking session
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                //admin only
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                $em = $this->getDoctrine()->getManager();
                //query to get all teams
                $query = $em->createQuery(
                        'SELECT  e.nom AS team, e.id
                                    FROM  maindbBundle:Equipe e
                                    ORDER BY e.nom'
                );

                $teams = $query->getArrayResult();
                //action to create user
                if ($request->getMethod() == 'POST') {
                    $em = $this->getDoctrine()->getEntityManager();
                    $usermailnew = $request->get('email');
                    $team = $request->get('team');
                    $mailtaken = $repository->findOneBy(array('mail' => $usermailnew));
                    //mail is primary key
                    if ($mailtaken) {
                        $error = "this email is already used.";
                        return $this->render('maindbBundle:Default:gestionusercreate.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'error' => $error, 'teams' => $teams));
                    }
                    $mail = $request->get('email');
                    $firstname = $request->get('firstName');
                    $lastname = $request->get('lastName');
                    $trigram = $request->get('trigram');
                    $equipeId = $request->get('team');
                    if (!$equipeId || $equipeId = NULL) {
                        $equipeId = 1;
                    }
                    $usernew = new Utilisateur();
                    $usernew->setMail($mail);
                    $usernew->setNom($lastname);
                    $usernew->setDateInscription(date('Y/m/d'));
                    $usernew->setPrenom($firstname);
                    $usernew->setTrigramme($trigram);
                    $usernew->setActif(1);
                    $usernew->setMdp(md5($firstname . $lastname));
                    $usernew->setEquipeId($equipeId);
                    $em = $this->getDoctrine()->getEntityManager();
                    $em->persist($usernew);
                    $em->flush();
                    $newrole = new UserRole();
                    $newrole->setRoleId('1');
                    $newrole->setUserId($usernew->getId());
                    $em->persist($newrole);
                    $em->flush();
                    // setting default 'user' role to new user
                    if ($request->getMethod() == 'POST' && $request->get('admin')) {
                        $newrole = new UserRole();
                        $newrole->setRoleId('3');
                        $newrole->setUserId($usernew->getId());
                        $em->persist($newrole);
                        $em->flush();
                    }
                    $em->persist($newrole);
                    $em->flush();
                    $message = "User created. Password is : " . $firstname . $lastname . ". Please keep " . $firstname . " aware.";
                    return $this->render('maindbBundle:Default:gestionusercreate.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'message' => $message, 'teams' => $teams));
                }
                else {

                    return ($this->gestionusercreateAction($request));
                }
            }
            else {
                $this->gestionusercreateAction($request);
            }
        }
        else {
            return $this->render('maindbBundle:Default:index.html.twig');
        }
    }

    // Action when trying to access an admin only page
    public function errorpermissionAction() {
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                return $this->render('maindbBundle:Default:errorpermission.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme()));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    // Action when creating a new composant
    public function gestioncomposantAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        // checking session
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                // admin only
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                // acti
                if ($request->getMethod() == 'POST') {
                    $todelete = $request->get('todelete');
                    if ($todelete) {
                        $repository = $em->getRepository('maindbBundle:Composants');
                        $produitToDelete = $repository->findOneBy(array('id' => $todelete));
                        // DELETION
                        $produitToDelete->setActif(0);
                        if ($todelete != 0) {
                            $em->persist($produitToDelete);
                            $em->flush();
                        }
                        unset($todelete);
                    }
                }
                if ($request->getMethod() == 'POST' && $request->get('idToEdit')) {
                    $repository = $em->getRepository('maindbBundle:Composants');
                    $itemToEdit = $repository->findOneBy(array('id' => $request->get('idToEdit')));
                    if ($request->getMethod() == 'POST' && $request->get('itemToEdit')) {
                        $itemToEdit->setNom($request->get('itemToEdit'));
                    }
                    $em->persist($itemToEdit);
                    $em->flush();
                }
                $newcomposant = $request->get('namenewcomposant');
                if ($newcomposant) {
                    $newcomposantcreation = New Composants();
                    $newcomposantcreation->setNom($newcomposant);
                    $newcomposantcreation->setActif(1);
                    $em->persist($newcomposantcreation);
                    $em->flush();
                    $composantsrepo = $em->getRepository('maindbBundle:Composants');
                    $query = $em->createQuery(
                            'SELECT  c
                                    FROM  maindbBundle:Composants c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                    );
                    $composants = GlobalFunctions::getFromRepository($em, 'Composants');
                    unset($newcomposant);
                    return $this->render('maindbBundle:Default:gestioncomposants.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'composants' => $composants));
                }
                $composantsrepo = $em->getRepository('maindbBundle:Composants');
                $query = $em->createQuery(
                        'SELECT  c 
                                    FROM  maindbBundle:Composants c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );

                $composants = GlobalFunctions::getFromRepository($em, 'Composants');
                return $this->render('maindbBundle:Default:gestioncomposants.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'composants' => $composants));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionplateformesAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                if ($request->getMethod() == 'POST' && $request->get('idToEdit')) {
                    $repository = $em->getRepository('maindbBundle:Plateforme');
                    $itemToEdit = $repository->findOneBy(array('id' => $request->get('idToEdit')));
                    if ($request->getMethod() == 'POST' && $request->get('itemToEdit')) {
                        $itemToEdit->setNom($request->get('itemToEdit'));
                    }
                    if ($request->getMethod() == 'POST' && $request->get('phaseedit')) {
                        $itemToEdit->setPhaseId($request->get('phaseedit'));
                    }
                    $em->persist($itemToEdit);
                    $em->flush();
                }
                if ($request->get('todelete') && $request->getMethod() == 'POST') {
                    $actirepo = $em->getRepository('maindbBundle:Plateforme');
                    $actitodelete = $actirepo->findOneBy(array('id' => $request->get('todelete')));
                    $actitodelete->setActif(0);
                    $em->persist($actitodelete);
                    $em->flush();
                }
                $newplateforme = $request->get('namenewplateforme');
                if ($newplateforme) {
                    $newplateformecreation = New Plateforme();
                    $newplateformecreation->setNom($newplateforme);
                    $newplateformecreation->setActif(1);
                    $em->persist($newplateformecreation);
                    $em->flush();
                    $composantsrepo = $em->getRepository('maindbBundle:Plateforme');
                    $query = $em->createQuery(
                            'SELECT  c
                                    FROM  maindbBundle:Plateforme c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                    );
                    $plateformes = GlobalFunctions::getFromRepository($em, 'Plateforme');
                    unset($newplateforme);
                    return $this->render('maindbBundle:Default:gestionplateformes.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'plateformes' => $plateformes));
                }
                $plateformesrepo = $em->getRepository('maindbBundle:Composants');
                $query = $em->createQuery(
                        'SELECT  c 
                                    FROM  maindbBundle:Plateforme c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );

                $plateformes = GlobalFunctions::getFromRepository($em, 'Plateforme');
                return $this->render('maindbBundle:Default:gestionplateformes.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'plateformes' => $plateformes));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionversionsAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                if ($request->getMethod() == 'POST' && $request->get('idToEdit')) {
                    $repository = $em->getRepository('maindbBundle:Version');
                    $itemToEdit = $repository->findOneBy(array('id' => $request->get('idToEdit')));
                    if ($request->getMethod() == 'POST' && $request->get('itemToEdit')) {
                        $itemToEdit->setNumero($request->get('itemToEdit'));
                    }
                    $em->persist($itemToEdit);
                    $em->flush();
                }
                $todelete = $request->get('todelete');
                if ($todelete && $request->getMethod() == 'POST') {
                    $repository = $em->getRepository('maindbBundle:Version');
                    $versionToDelete = $repository->findOneBy(array('id' => $todelete));
                    $versionToDelete->setActif(0);
                    $em->persist($versionToDelete);
                    $em->flush();
                    unset($todelete);
                }
                $newversion = $request->get('namenewversion');
                if ($newversion) {
                    $newversioncreation = New Version();
                    $newversioncreation->setNumero($newversion);
                    $newversioncreation->setActif(1);
                    $em->persist($newversioncreation);
                    $em->flush();
                    $versionsrepo = $em->getRepository('maindbBundle:Version');
                    $query = $em->createQuery(
                            'SELECT  c
                                    FROM  maindbBundle:Version c
                                    WHERE c.actif = 1
                                    ORDER BY c.numero'
                    );
                    $versions = GlobalFunctions::getFromRepository($em, 'Version');
                    unset($newversion);
                    return $this->render('maindbBundle:Default:gestionversions.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'versions' => $versions));
                }
                $versionsrepo = $em->getRepository('maindbBundle:Composants');
                $query = $em->createQuery(
                        'SELECT  c 
                                    FROM  maindbBundle:Version c
                                    WHERE c.actif = 1
                                    ORDER BY c.numero'
                );

                $versions = GlobalFunctions::getFromRepository($em, 'Version');
                return $this->render('maindbBundle:Default:gestionversions.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'versions' => $versions));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionpartenairesAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                if ($request->getMethod() == 'POST' && $request->get('idToEdit')) {
                    $repository = $em->getRepository('maindbBundle:Partenaire');
                    $itemToEdit = $repository->findOneBy(array('id' => $request->get('idToEdit')));
                    if ($request->getMethod() == 'POST' && $request->get('itemToEdit')) {
                        $itemToEdit->setNom($request->get('itemToEdit'));
                    }
                    $em->persist($itemToEdit);
                    $em->flush();
                }
                $todelete = $request->get('todelete');
                if ($request->getMethod() == 'POST') {
                    $todelete = $request->get('todelete');
                    if ($todelete) {
                        $repository = $em->getRepository('maindbBundle:Partenaire');
                        $produitToDelete = $repository->findOneBy(array('id' => $todelete));
                        // DELETION
                        $produitToDelete->setActif(0);
                        $em->persist($produitToDelete);
                        $em->flush();
                        unset($todelete);
                    }
                }
                $newpartenaire = $request->get('namenewpartenaire');
                if ($newpartenaire) {
                    $newpartenairecreation = New Partenaire();
                    $newpartenairecreation->setNom($newpartenaire);
                    $newpartenairecreation->setActif(1);
                    $em->persist($newpartenairecreation);
                    $em->flush();
                    $partenairessrepo = $em->getRepository('maindbBundle:Partenaire');
                    $query = $em->createQuery(
                            'SELECT  c
                                    FROM  maindbBundle:Partenaire c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                    );
                    $partenaires = GlobalFunctions::getFromRepository($em, 'Partenaire');
                    unset($newpartenaire);
                    return $this->render('maindbBundle:Default:gestionpartenaires.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'partenaires' => $partenaires));
                }
                $partenairesrepo = $em->getRepository('maindbBundle:Partenaire');
                $query = $em->createQuery(
                        'SELECT  c 
                                    FROM  maindbBundle:Partenaire c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );

                $partenaires = GlobalFunctions::getFromRepository($em, 'Partenaire');
                return $this->render('maindbBundle:Default:gestionpartenaires.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'partenaires' => $partenaires));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionSocietesAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                if ($request->getMethod() == 'POST' && $request->get('idToEdit')) {
                    $repository = $em->getRepository('maindbBundle:Societe');
                    $itemToEdit = $repository->findOneBy(array('id' => $request->get('idToEdit')));
                    if ($request->getMethod() == 'POST' && $request->get('itemToEdit')) {
                        $itemToEdit->setNom($request->get('itemToEdit'));
                    }
                    $em->persist($itemToEdit);
                    $em->flush();
                }
                if ($request->getMethod() == 'POST' && $request->get('todelete')) {
                    $actirepo = $em->getRepository('maindbBundle:Societe');
                    $actitodelete = $actirepo->findOneBy(array('id' => $request->get('todelete')));
                    $actitodelete->setActif(0);
                    $em->persist($actitodelete);
                    $em->flush();
                }
                $newsociete = $request->get('namenewsociete');
                if ($newsociete) {
                    $newsocietecreation = New Societe();
                    $newsocietecreation->setNom($newsociete);
                    $newsocietecreation->setActif(1);
                    $em->persist($newsocietecreation);
                    $em->flush();
                    $partenairessrepo = $em->getRepository('maindbBundle:Societe');
                    $query = $em->createQuery(
                            'SELECT  c
                                    FROM  maindbBundle:Societe c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                    );
                    $societes = $query->getArrayResult();
                    unset($newsociete);
                    return $this->render('maindbBundle:Default:gestionsocietes.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'societes' => $societes));
                }
                $societesrepo = $em->getRepository('maindbBundle:Societe');
                $query = $em->createQuery(
                        'SELECT  c 
                                    FROM  maindbBundle:Societe c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );

                $societes = $query->getArrayResult();
                return $this->render('maindbBundle:Default:gestionsocietes.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'societes' => $societes));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionphasesAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                if ($request->getMethod() == 'POST' && $request->get('idToEdit')) {
                    $repository = $em->getRepository('maindbBundle:Phase');
                    $itemToEdit = $repository->findOneBy(array('id' => $request->get('idToEdit')));
                    if ($request->getMethod() == 'POST' && $request->get('itemToEdit')) {
                        $itemToEdit->setNom($request->get('itemToEdit'));
                    }
                    $em->persist($itemToEdit);
                    $em->flush();
                }
                $todelete = $request->get('todelete');
                if ($request->getMethod() == 'POST') {
                    $todelete = $request->get('todelete');
                    if ($todelete) {
                        $query = $em->createQuery(
                                'SELECT  c 
                                    FROM  maindbBundle:Ssphase c
                                    WHERE c.actif = 1 AND c.phaseId = ?1
                                    ORDER BY c.nom'
                        );
                        $query->setParameter(1, $todelete);
                        $ssphasestodelete = $query->getArrayResult();
                        $ssphasesrepo = $em->getRepository('maindbBundle:Ssphase');
                        foreach ($ssphasestodelete as $phase) {
                            $ssphasetodeletenow = ($phase['id']);
                            $ifoundssphase = $ssphasesrepo->findOneBy(array('id' => $ssphasetodeletenow));
                            $ifoundssphase->setActif(0);
                            $em->persist($ifoundssphase);
                            $em->flush();
                        }
                        $societes = $query->getArrayResult();
                        $repository = $em->getRepository('maindbBundle:Phase');
                        $produitToDelete = $repository->findOneBy(array('id' => $todelete));
                        // DELETION
                        $produitToDelete->setActif(0);
                        $em->persist($produitToDelete);
                        $em->flush();
                        unset($todelete);
                    }
                }
                $newphase = $request->get('namenewphase');
                if ($newphase) {
                    $newphasecreation = New Phase();
                    $newphasecreation->setNom($newphase);
                    $newphasecreation->setActif(1);
                    $em->persist($newphasecreation);
                    $em->flush();
                    $phaserepo = $em->getRepository('maindbBundle:Phase');
                    $query = $em->createQuery(
                            'SELECT  c
                                    FROM  maindbBundle:Phase c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                    );
                    $phases = $query->getArrayResult();
                    unset($newphase);
                    return $this->render('maindbBundle:Default:gestionphases.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'phases' => $phases));
                }
                $phasesrepo = $em->getRepository('maindbBundle:Phase');
                $query = $em->createQuery(
                        'SELECT  c 
                                    FROM  maindbBundle:Phase c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );

                $phases = $query->getArrayResult();
                return $this->render('maindbBundle:Default:gestionphases.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'phases' => $phases));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionproduitsAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                $query = $em->createQuery(
                        'SELECT  c
                                    FROM  maindbBundle:Composants c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );
                $composants = GlobalFunctions::getFromRepository($em, 'Composants');
                $query = $em->createQuery(
                        'SELECT  c
                                    FROM  maindbBundle:Version c
                                    WHERE c.actif = 1
                                    ORDER BY c.numero'
                );
                $versions = GlobalFunctions::getFromRepository($em, 'Version');
                $query = $em->createQuery(
                        'SELECT  c
                                    FROM  maindbBundle:Plateforme c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );
                $plateformes = GlobalFunctions::getFromRepository($em, 'Plateforme');
                if ($request->getMethod() == 'POST') {
                    $todelete = $request->get('todelete');
                    if ($todelete) {
                        $repository = $em->getRepository('maindbBundle:Produit');
                        $produitToDelete = $repository->findOneBy(array('id' => $todelete));
                        // DELETION
                        $produitToDelete->setActif(0);
                        $em->persist($produitToDelete);
                        $em->flush();
                        unset($todelete);
                    }
                }

                $newproduit = $request->get('namenewproduit');
                $composantsselected = $request->get('composantsselected');
                $plateformesselected = $request->get('plateformesselected');
                $versionsselected = $request->get('versionsselected');

                if ($newproduit && $composantsselected && $plateformesselected && $versionsselected) {
                    $newproduitcreation = New Produit();
                    $newproduitcreation->setNom($newproduit);
                    $newproduitcreation->setActif(1);
                    $em->persist($newproduitcreation);
                    $em->flush();
                    foreach ($composantsselected as $composant) {
                        $newproduitcomposant = new ProduitComposant();
                        $newproduitcomposant->setComposantId($composant);
                        $newproduitcomposant->setProduitId($newproduitcreation->getId());
                        $em->persist($newproduitcomposant);
                        $em->flush();
                    }
                    foreach ($plateformesselected as $plateforme) {
                        $newproduitplateforme = new ProduitPlateforme();
                        $newproduitplateforme->setPlateformeId($plateforme);
                        $newproduitplateforme->setProduitId($newproduitcreation->getId());
                        $em->persist($newproduitplateforme);
                        $em->flush();
                    }
                    foreach ($versionsselected as $version) {
                        $newproduitversion = new ProduitVersion();
                        $newproduitversion->setVersionId($version);
                        $newproduitversion->setProduitId($newproduitcreation->getId());
                        $em->persist($newproduitversion);
                        $em->flush();
                    }
                    $produitsrepo = $em->getRepository('maindbBundle:Produit');
                    $query = $em->createQuery(
                            'SELECT  c
                                    FROM  maindbBundle:Produit c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                    );
                    $produits = GlobalFunctions::getFromRepository($em, 'Produit');
                    unset($newphase);
                    return $this->render('maindbBundle:Default:gestionproduits.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'produits' => $produits,
                                'versions' => $versions,
                                'plateformes' => $plateformes,
                                'composants' => $composants));
                }
                $produitsrepo = $em->getRepository('maindbBundle:Produit');
                $query = $em->createQuery(
                        'SELECT  c 
                                    FROM  maindbBundle:Produit c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );

                $produits = GlobalFunctions::getFromRepository($em, 'Produit');
                return $this->render('maindbBundle:Default:gestionproduits.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'produits' => $produits,
                            'versions' => $versions,
                            'plateformes' => $plateformes,
                            'composants' => $composants));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionclientsAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                if ($request->getMethod() == 'POST' && $request->get('todelete')) {
                    $actirepo = $em->getRepository('maindbBundle:Client');
                    $actitodelete = $actirepo->findOneBy(array('id' => $request->get('todelete')));
                    $actitodelete->setActif(0);
                    $em->persist($actitodelete);
                    $em->flush();
                }
                $query = $em->createQuery(
                        'SELECT  c
                                    FROM  maindbBundle:Partenaire c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );
                $partenaires = GlobalFunctions::getFromRepository($em, 'Partenaire');
                $todelete = $request->get('todelete');
                if ($request->getMethod() == 'POST' && $request->get('todelete')) {
                    $repository = $em->getRepository('maindbBundle:Client');
                    $clienttodelete = $repository->findOneBy(array('id' => $todelete));
                    $clienttodelete->setActif(0);
                    if ($todelete != 0) {
                        $em->persist($clienttodelete);
                        $em->flush();
                    }
                    unset($todelete);
                }
                if ($request->getMethod() == 'POST' && $request->get('idToEdit')) {
                    $repository = $em->getRepository('maindbBundle:Client');
                    $itemToEdit = $repository->findOneBy(array('id' => $request->get('idToEdit')));
                    if ($request->getMethod() == 'POST' && $request->get('itemToEdit')) {
                        $itemToEdit->setNom($request->get('itemToEdit'));
                    }
                    $itemToEdit->setPartenaireId('');
                    if ($request->getMethod() == 'POST' && $request->get('partenaireedit')) {
                        $itemToEdit->setPartenaireId($request->get('partenaireedit'));
                    }
                    $em->persist($itemToEdit);
                    $em->flush();
                    unset($todelete);
                }
                $newclient = $request->get('namenewclient');
                $partenaire = $request->get('partenaire');
                if ($newclient) {
                    $newclientcreation = New Client();
                    $newclientcreation->setNom($newclient);
                    if ($partenaire) {
                        $newclientcreation->setPartenaireId($partenaire);
                    }
                    $newclientcreation->setActif(1);
                    $em->persist($newclientcreation);
                    $em->flush();
                    $clientssrepo = $em->getRepository('maindbBundle:Client');
                    $query = $em->createQuery(
                            'SELECT  c
                                    FROM  maindbBundle:Client c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                    );
                    $clients = $query->getArrayResult();
                    unset($newclient);
                    return $this->render('maindbBundle:Default:gestionclients.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'clients' => $clients, 'partenaires' => $partenaires));
                }
                $clientssrepo = $em->getRepository('maindbBundle:Client');
                $query = $em->createQuery(
                        'SELECT  c 
                                    FROM  maindbBundle:Client c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );
                $clients = GlobalFunctions::getFromRepository($em, 'Client');
                return $this->render('maindbBundle:Default:gestionclients.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'clients' => $clients, 'partenaires' => $partenaires));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionssphasesAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                $query = $em->createQuery(
                        'SELECT  c
                                    FROM  maindbBundle:Phase c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );
                $phases = $query->getArrayResult();
                if ($request->getMethod() == 'POST' && $request->get('idToEdit')) {
                    $repository = $em->getRepository('maindbBundle:Ssphase');
                    $itemToEdit = $repository->findOneBy(array('id' => $request->get('idToEdit')));
                    if ($request->getMethod() == 'POST' && $request->get('itemToEdit')) {
                        $itemToEdit->setNom($request->get('itemToEdit'));
                    }
                    if ($request->getMethod() == 'POST' && $request->get('phaseedit')) {
                        $itemToEdit->setPhaseId($request->get('phaseedit'));
                    }
                    $em->persist($itemToEdit);
                    $em->flush();
                }
                $todelete = $request->get('todelete');
                if ($request->getMethod() == 'POST' && $request->get('todelete')) {
                    $repository = $em->getRepository('maindbBundle:Ssphase');
                    $ssphasetodelete = $repository->findOneBy(array('id' => $todelete));
                    $ssphasetodelete->setActif(0);
                    $em->persist($ssphasetodelete);
                    $em->flush();
                    unset($todelete);
                }
                $newssphase = $request->get('namenewssphase');
                $phase = $request->get('phase');
                if ($newssphase && $phase) {
                    $newssphasecreation = New Ssphase();
                    $newssphasecreation->setNom($newssphase);
                    $newssphasecreation->setPhaseId($phase);
                    $newssphasecreation->setActif(1);
                    $em->persist($newssphasecreation);
                    $em->flush();
                    $ssphasesrepo = $em->getRepository('maindbBundle:Ssphase');
                    $query = $em->createQuery(
                            'SELECT  p.nom as nom2 ,c
                                    FROM  maindbBundle:Ssphase c, maindbBundle:Phase p
                                    WHERE c.actif = 1 AND c.phaseId = p.id
                                    ORDER BY p.nom'
                    );
                    $ssphases = $query->getArrayResult();
                    unset($newssphase);
                    return $this->render('maindbBundle:Default:gestionssphases.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'ssphases' => $ssphases, 'phases' => $phases));
                }
                $ssphasesrepo = $em->getRepository('maindbBundle:Ssphase');
                $query = $em->createQuery(
                        'SELECT  p.nom as nom2 ,c
                                    FROM  maindbBundle:Ssphase c, maindbBundle:Phase p
                                    WHERE c.actif = 1 AND c.phaseId = p.id
                                    ORDER BY p.nom'
                );
                $ssphases = $query->getArrayResult();
                return $this->render('maindbBundle:Default:gestionssphases.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'ssphases' => $ssphases, 'phases' => $phases));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionservicesAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                if ($request->getMethod() == 'POST' && $request->get('idToEdit')) {
                    $repository = $em->getRepository('maindbBundle:Service');
                    $itemToEdit = $repository->findOneBy(array('id' => $request->get('idToEdit')));
                    if ($request->getMethod() == 'POST' && $request->get('itemToEdit')) {
                        $itemToEdit->setNom($request->get('itemToEdit'));
                    }
                    if ($request->getMethod() == 'POST' && $request->get('societeedit')) {
                        $itemToEdit->setSocieteId($request->get('societeedit'));
                    }
                    $em->persist($itemToEdit);
                    $em->flush();
                }
                $query = $em->createQuery(
                        'SELECT  c
                                    FROM  maindbBundle:Societe c
                                    WHERE c.actif = 1
                                    ORDER BY c.id'
                );
                $societes = $query->getArrayResult();
                $todelete = $request->get('todelete');
                if ($request->getMethod() == 'POST' && $request->get('todelete')) {
                    $repository = $em->getRepository('maindbBundle:Service');
                    $servicetodelete = $repository->findOneBy(array('id' => $todelete));
                    $servicetodelete->setActif(0);
                    $em->persist($servicetodelete);
                    $em->flush();
                    unset($todelete);
                }
                $newservice = $request->get('namenewservice');
                $societe = $request->get('societe');
                if ($newservice && $societe) {
                    $newservicecreation = New Service();
                    $newservicecreation->setNom($newservice);
                    $newservicecreation->setSocieteId($societe);
                    $newservicecreation->setActif(1);
                    $em->persist($newservicecreation);
                    $em->flush();
                    $servicesrepo = $em->getRepository('maindbBundle:Service');
                    $query = $em->createQuery(
                            'SELECT  c
                                    FROM  maindbBundle:Service c
                                    WHERE c.actif = 1
                                    ORDER BY c.id'
                    );
                    $services = $query->getArrayResult();
                    unset($newsservice);
                    return $this->render('maindbBundle:Default:gestionservices.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'services' => $services, 'societes' => $societes));
                }
                $servicesrepo = $em->getRepository('maindbBundle:Service');
                $query = $em->createQuery(
                        'SELECT  c
                                    FROM  maindbBundle:Service c
                                    WHERE c.actif = 1
                                    ORDER BY c.id'
                );
                $services = $query->getArrayResult();
                return $this->render('maindbBundle:Default:gestionservices.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'services' => $services, 'societes' => $societes));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function activitedetailsAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        $query = $em->createQuery(
                'SELECT  e, a.nom as nom2
                                    FROM  maindbBundle:Ssphase e, maindbBundle:Phase a
                                    WHERE e.actif = 1 AND e.phaseId = a.id
                                    ORDER BY e.nom'
        );

        $ssphases = $query->getArrayResult();
        $query = $em->createQuery(
                'SELECT  e
                                    FROM  maindbBundle:Phase e
                                    WHERE e.actif = 1
                                    ORDER BY e.nom'
        );

        $phases = $query->getArrayResult();
        $activites = GlobalFunctions::getFromRepository($em, 'Activite');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                if ($request->getMethod() == 'POST' && $request->get('namenew')) {
                    $repository = $em->getRepository('maindbBundle:Activite');
                    $activite = $repository->findOneBy(array('id' => $request->get('Id')));
                    $activite->setNom($request->get('namenew'));
                    $rowInRepository = $em->getRepository('maindbBundle:PhaseSsphaseActivite');
                    $row = $rowInRepository->findOneBy(array('activiteId' => $request->get('Id')));
                    if ($request->get('ssphasenew')) {
                        $row->setSsphaseId($request->get('ssphasenew'));
                    }
                    else {
                        $row->setSsphaseId(0);
                    }
                    if ($request->get('phasenew')) {
                        $row->setPhaseId($request->get('phasenew'));
                    }
                    else {
                        $row->setPhaseId(0);
                    }
                    if ($request->get('nature')) {
                        $repository = $em->getRepository('maindbBundle:ActiviteNature');
                        $oldnatures = $repository->findBy(array('activiteId' => $activite->getId()));
                        foreach ($oldnatures as $oldnat) {
                            $em->remove($oldnat);
                            $em->flush();
                        }
                        foreach ($request->get('nature') as $nat) {
                            $newlinknat = new ActiviteNature();
                            $newlinknat->setActiviteId($activite->getId());
                            $newlinknat->setNature($nat);
                            $em->persist($newlinknat);
                            $em->flush();
                        }
                    }
                    else {
                        $activite->setNature(null);
                    }

                    $em->flush();
                    $em->persist($row);
                    $em->flush();
                }

                $detailId = $request->get('Id');
                $activite = $repository->findOneBy(array('id' => $detailId));

                if ($detailId) {
                    $repository = $em->getRepository('maindbBundle:Activite');
                    if ($repository) {
                        $activite = $repository->findOneBy(array('id' => $detailId));
                        $natureold = $activite->getNature();
                        $rowInRepository = $em->getRepository('maindbBundle:PhaseSsphaseActivite');
                        $row = $rowInRepository->findOneBy(array('activiteId' => $detailId));
                        $activiteRepository = $em->getRepository('maindbBundle:Activite');
                        $activite = $activiteRepository->findOneBy(array('id' => $detailId));
                        $repository = $em->getRepository('maindbBundle:ActiviteNature');
                        $naturesRecup = $repository->findBy(array('activiteId' => $detailId));
                        $naturesToShow = Array();
                        foreach ($naturesRecup as $natRec) {
                            array_push($naturesToShow, $natRec->getNature());
                        }
                        $comma_separated = implode(",", $naturesToShow);
                        $ssphaseRepository = $em->getRepository('maindbBundle:Ssphase');
                        $ssphase = $ssphaseRepository->findOneBy(array('id' => $row->getSsphaseId()));
                        if ($ssphase) {
                            $defaultssphase = $ssphase->getId();
                            $ssphasenom = $ssphase->getNom();
                        }
                        else {
                            $defaultssphase = 'null';
                            $ssphasenom = '';
                        }
                        $phaseRepository = $em->getRepository('maindbBundle:Phase');
                        if ($ssphase) {
                            $phase = $phaseRepository->findOneBy(array('id' => $ssphase->getPhaseId()));
                            if ($phase) {
                                $defaultphase = $phase->getId();
                                $phasenom = $ssphase->getNom();
                            }
                            else {
                                $defaultphase = 'null';
                                $phasenom = '';
                            }
                        }
                        else {
                            $defaultphase = 'null';
                            $phasenom = '';
                        }
                        $query = $em->createQuery(
                                'SELECT  e, a.nom as nom2
                                    FROM  maindbBundle:Ssphase e, maindbBundle:Phase a
                                    WHERE e.actif = 1 AND e.phaseId = a.id
                                    ORDER BY e.nom'
                        );

                        $ssphases = $query->getArrayResult();
                        $query = $em->createQuery(
                                'SELECT  e
                                    FROM  maindbBundle:Phase e
                                    WHERE e.actif = 1
                                    ORDER BY e.nom'
                        );

                        $phases = $query->getArrayResult();
                        return $this->render('maindbBundle:Default:activitedetails.html.twig', array('roles' => $roles,
                                    'name' => $user->getNom(),
                                    'surname' => $user->getPrenom(),
                                    'mail' => $user->getMail(),
                                    'trigramme' => $user->getTrigramme(),
                                    'activite' => $activite->getNom(),
                                    'naturedetail' => $comma_separated,
                                    'natures' => $this->natures,
                                    'ssphase' => $ssphasenom,
                                    'phase' => $phasenom,
                                    'phases' => $phases,
                                    'ssphases' => $ssphases,
                                    'detailid' => $detailId,
                                    'defaultphase' => $defaultphase,
                                    'defaultssphase' => $defaultssphase,
                                    'natureold' => $natureold));
                    }
                }
                return $this->render('maindbBundle:Default:gestionactivite.html.twig', array('natures' => $this->natures, 'roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'activites' => $activites, 'phases' => $phases, 'ssphases' => $ssphases));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function produitsdetailsAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                $query = $em->createQuery(
                        'SELECT  c
                                    FROM  maindbBundle:Composants c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );
                $composants = GlobalFunctions::getFromRepository($em, 'Composants');
                $query = $em->createQuery(
                        'SELECT  c
                                    FROM  maindbBundle:Version c
                                    WHERE c.actif = 1
                                    ORDER BY c.numero'
                );
                $versions = GlobalFunctions::getFromRepository($em, 'Version');
                $query = $em->createQuery(
                        'SELECT  c
                                    FROM  maindbBundle:Plateforme c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );
                $plateformes = GlobalFunctions::getFromRepository($em, 'Plateforme');
                $query = $em->createQuery(
                        'SELECT  c
                                    FROM  maindbBundle:Produit c
                                    WHERE c.actif = 1
                                    ORDER BY c.nom'
                );
                $produits = GlobalFunctions::getFromRepository($em, 'Produit');
                if ($request->get('namenewproduit') && $request->get('Id')) {
                    $em = $this->getDoctrine()->getEntityManager();
                    $repository = $em->getRepository('maindbBundle:Produit');
                    $produitModif = $repository->findOneBy(array('id' => $request->get('Id')));
                    $produitModif->setNom($request->get('namenewproduit'));
                    $em->persist($produitModif);
                    $em->flush();
                }
                if ($request->get('composantsselected') && $request->get('Id')) {
                    $repository = $em->getRepository('maindbBundle:ProduitComposant');
                    $liens = $repository->findBy(array('produitId' => $request->get('Id')));
                    foreach ($liens as $lien) {
                        $em->remove($lien);
                        $em->flush();
                    }
                    foreach ($request->get('composantsselected') as $composantToAdd) {
                        $lien = new ProduitComposant();
                        $lien->setComposantId($composantToAdd);
                        $lien->setProduitId($request->get('Id'));
                        $em->persist($lien);
                        $em->flush();
                    }
                }
                if ($request->get('plateformesselected') && $request->get('Id')) {
                    $repository = $em->getRepository('maindbBundle:ProduitPlateforme');
                    $liens = $repository->findBy(array('produitId' => $request->get('Id')));
                    foreach ($liens as $lien) {
                        $em->remove($lien);
                        $em->flush();
                    }
                    foreach ($request->get('plateformesselected') as $composantToAdd) {
                        $lien = new ProduitPlateforme();
                        $lien->setPlateformeId($composantToAdd);
                        $lien->setProduitId($request->get('Id'));
                        $em->persist($lien);
                        $em->flush();
                    }
                }
                if ($request->get('versionsselected') && $request->get('Id')) {
                    $repository = $em->getRepository('maindbBundle:ProduitVersion');
                    $liens = $repository->findBy(array('produitId' => $request->get('Id')));
                    foreach ($liens as $lien) {
                        $em->remove($lien);
                        $em->flush();
                    }
                    foreach ($request->get('versionsselected') as $composantToAdd) {
                        $lien = new ProduitVersion();
                        $lien->setVersionId($composantToAdd);
                        $lien->setProduitId($request->get('Id'));
                        $em->persist($lien);
                        $em->flush();
                    }
                }


                if (($request->get('Id')) && ($request->get('Id') != '')) {
                    $query = $em->createQuery(
                            'SELECT  e
                                    FROM  maindbBundle:Produit e
                                    WHERE e.id = ?1
                                    ORDER BY e.nom'
                    );
                    $query->setParameter(1, $request->get('Id'));
                    $produit = $query->getArrayResult();
                    $query = $em->createQuery(
                            'SELECT  e
                                    FROM  maindbBundle:Composants e, maindbBundle:ProduitComposant a
                                    WHERE e.id = a.composantId AND a.produitId = ?1
                                    ORDER BY e.nom'
                    );
                    $query->setParameter(1, $request->get('Id'));
                    $composantss = $query->getArrayResult();
                    $query = $em->createQuery(
                            'SELECT  e
                                    FROM  maindbBundle:Plateforme e, maindbBundle:ProduitPlateforme a
                                    WHERE e.id = a.plateformeId AND a.produitId = ?1
                                    ORDER BY e.nom'
                    );
                    $query->setParameter(1, $request->get('Id'));
                    $plateformess = $query->getArrayResult();
                    $query = $em->createQuery(
                            'SELECT  e
                                    FROM  maindbBundle:Version e, maindbBundle:ProduitVersion a
                                    WHERE e.id = a.versionId AND a.produitId = ?1 AND e.actif = 1
                                    ORDER BY e.numero'
                    );
                    $query->setParameter(1, $request->get('Id'));
                    $versionss = $query->getArrayResult();
                    $compoleft = array();
                    $platleft = array();
                    $versleft = array();
                    foreach ($composants as $composant) {
                        if (!in_array($composant, $composantss)) {
                            array_push($compoleft, $composant);
                        }
                    }
                    foreach ($versions as $version) {
                        if (!in_array($version, $versionss)) {
                            array_push($versleft, $version);
                        }
                    }
                    foreach ($plateformes as $plateforme) {
                        if (!in_array($plateforme, $plateformess)) {
                            array_push($platleft, $plateforme);
                        }
                    }
                    return $this->render('maindbBundle:Default:produitsdetails.html.twig', array('roles' => $roles,
                                'name' => $user->getNom(),
                                'surname' => $user->getPrenom(),
                                'mail' => $user->getMail(),
                                'trigramme' => $user->getTrigramme(),
                                'detailid' => $request->get('Id'),
                                'product' => $produit,
                                'components' => $composantss,
                                'plateforms' => $plateformess,
                                'versions2' => $versionss,
                                'plateformes' => $plateformes,
                                'composants' => $composants,
                                'compoleft' => $compoleft,
                                'versleft' => $versleft,
                                'platleft' => $platleft,
                                'versions' => $versions));
                }
                else {
                    return $this->render('maindbBundle:Default:gestionproduits.html.twig', array('roles' => $roles, 'name' => $user->getNom(), 'surname' => $user->getPrenom(), 'mail' => $user->getMail(), 'trigramme' => $user->getTrigramme(), 'produits' => $produits,
                                'versions' => $versions,
                                'plateformes' => $plateformes,
                                'composants' => $composants));
                }
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionteamlistAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $firstload = $request->get('firstload');
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }


                if ($request->getMethod() == 'POST' && $request->get('todelete')) {
                    $repository = $em->getRepository('maindbBundle:Utilisateur');
                    $usersToRemove = $repository->findBy(array('equipeId' => $request->get('todelete')));
                    foreach ($usersToRemove as $user) {
                        $user->setEquipeId(0);
                        $em->persist($user);
                        $em->flush();
                        $repository = $em->getRepository('maindbBundle:UserRole');
                        $teamleader = $repository->findOneBy(array('userId' => $user->getId(), 'roleId' => 2));
                        if ($teamleader) {
                            $em->remove($teamleader);
                            $em->flush();
                        }
                    }
                    $repository = $em->getRepository('maindbBundle:Equipe');
                    $teamToDelete = $repository->findOneBy(array('id' => $request->get('todelete')));
                    $teamToDelete->setActif(0);
                    $em->persist($teamToDelete);
                    $em->flush();
                }

                $repository = $em->getRepository('maindbBundle:Societe');
                $societes = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Service');
                $services = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Equipe');
                $equipes = $repository->findBy(array('actif' => 1));


                return $this->render('maindbBundle:Default:gestionteamlist.html.twig', array('roles' => $roles,
                            'name' => $user->getNom(),
                            'surname' => $user->getPrenom(),
                            'mail' => $user->getMail(),
                            'trigramme' => $user->getTrigramme(),
                            'societes' => $societes,
                            'services' => $services,
                            'equipes' => $equipes));
            }
        }

        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionteamcreateAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                $message = '';
                $query = $em->createQuery(
                        'SELECT c.id, c.nom, c.prenom, c.trigramme, c.mail, c.actif
                                    FROM maindbBundle:Utilisateur c
                                    WHERE c.equipeId =0 AND c.actif = 1
                                    GROUP BY c.id
                                    ORDER BY c.nom
                                    '
                );
                $users = $query->getArrayResult();
                $repository = $em->getRepository('maindbBundle:Societe');
                $societes = $repository->findBy(array('actif' => 1));
                if ($request->getMethod() == 'POST' && $request->get('teamname') && $request->get('service') && $request->get('teamLeader')) {
                    $newTeamLeaderRole = new UserRole();
                    $newTeamLeaderRole->setUserId($request->get('teamLeader'));
                    $newTeamLeaderRole->setRoleId(2);
                    $em->persist($newTeamLeaderRole);
                    $em->flush();
                    $newteam = new Equipe();
                    $newteam->setActif(1);
                    $newteam->setNom($request->get('teamname'));
                    $newteam->setServiceId($request->get('service'));
                    $em->persist($newteam);
                    $em->flush();
                    $repository = $em->getRepository('maindbBundle:Utilisateur');
                    $teamLeader = $repository->findOneBy(array('id' => $request->get('teamLeader')));
                    $teamLeader->setEquipeId($newteam->getId());
                    $em->persist($teamLeader);
                    $em->flush();
                    $message = "Team successfully created";
                }
                if ($request->getMethod() == 'POST' && $request->get('company')) {
                    $companyelected = $request->get('company');
                    $repository = $em->getRepository('maindbBundle:Service');
                    $services = $repository->findBy(array('actif' => 1, 'societeId' => $companyelected));
                    return $this->render('maindbBundle:Default:gestionteamcreate.html.twig', array('roles' => $roles,
                                'name' => $user->getNom(),
                                'surname' => $user->getPrenom(),
                                'mail' => $user->getMail(),
                                'trigramme' => $user->getTrigramme(),
                                'societes' => $societes,
                                'societeselect' => $companyelected,
                                'services' => $services,
                                'users' => $users,
                                'message' => $message));
                }
                return $this->render('maindbBundle:Default:gestionteamcreate.html.twig', array('roles' => $roles,
                            'name' => $user->getNom(),
                            'surname' => $user->getPrenom(),
                            'mail' => $user->getMail(),
                            'trigramme' => $user->getTrigramme(),
                            'societes' => $societes,
                            'users' => $users,
                            'message' => $message));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionteamdetailsAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $error = "";
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isadmin) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                if ($request->getMethod() == 'POST' && $request->get('itemToEdit')) {
                    $repository = $em->getRepository('maindbBundle:Equipe');
                    $equipe = $repository->findOneBy(array('id' => $request->get('idToEdit')));
                    $equipe->setNom($request->get('itemToEdit'));
                    $em->persist($equipe);
                    $em->flush();
                }
                if ($request->getMethod() == 'POST' && $request->get('todelete') && $request->get('idteamtomodify')) {
                    $repository = $em->getRepository('maindbBundle:UserRole');
                    $oldTeamLeaderRole = $repository->findOneBy(array('userId' => $request->get('idteamtomodify'), 'roleId' => 2));
                    $em->remove($oldTeamLeaderRole);
                    $em->flush();
                    $newrole = new UserRole();
                    $newrole->setRoleId(2);
                    $newrole->setUserId($request->get('todelete'));
                    $em->persist($newrole);
                    $em->flush();
                }
                if ($request->get('id') && $request->get('useradd')) {
                    $repository = $em->getRepository('maindbBundle:Utilisateur');
                    $user = $repository->findOneBy(array('id' => $request->get('useradd')));
                    $user->setEquipeId($request->get('id'));
                    $em->persist($user);
                    $em->flush();
                }
                if ($request->get('id') && $request->get('todelete')) {
                    $repository = $em->getRepository('maindbBundle:UserRole');
                    $relation = $repository->findOneBy(array('userId' => $request->get('todelete'), 'roleId' => 2));
                    if (!$relation) {
                        $repository = $em->getRepository('maindbBundle:Utilisateur');
                        $user = $repository->findOneBy(array('id' => $request->get('todelete')));
                        $user->setEquipeId(0);
                        $em->persist($user);
                        $em->flush();
                    }
                    else {
                        $error = "Can't remove team leader from team.";
                    }
                }
                if ($request->get('id') || $request->get('equipeId')) {
                    $repository = $em->getRepository('maindbBundle:Equipe');
                    if ($request->get('id')) {
                        $equipe = $repository->findOneBy(array('id' => $request->get('id')));
                    }
                    else {
                        $equipe = $repository->findOneBy(array('id' => $request->get('equipeId')));
                    }
                    $repository = $em->getRepository('maindbBundle:Utilisateur');
                    if ($request->get('id')) {
                        $membres = $repository->findBy(array('equipeId' => $request->get('id')));
                    }
                    else {
                        $membres = $repository->findBy(array('equipeId' => $request->get('equipeId')));
                    }

                    $query = $em->createQuery(
                            'SELECT c.id, c.nom, c.prenom, c.trigramme, c.mail, c.actif
                                    FROM maindbBundle:Utilisateur c, maindbBundle:Equipe e
                                    WHERE c.equipeId IS NULL OR c.equipeId = 0 OR c.equipeId = 1 AND c.actif = 1
                                    GROUP BY c.id
                                    ORDER BY c.nom
                                    '
                    );
                    $users = $query->getArrayResult();
                    $query = $em->createQuery(
                            'SELECT c
                                    FROM maindbBundle:Utilisateur c, maindbBundle:Equipe e, maindbBundle:UserRole r
                                    WHERE c.equipeId =?1 AND c.actif = 1 AND c.id = r.userId AND r.roleId = 2
                                    GROUP BY c.id
                                    ORDER BY c.nom
                                    '
                    );
                    if ($request->get('equipeId')) {
                        $query->setParameter(1, $request->get('equipeId'));
                        $id = $request->get('equipeId');
                    }
                    else {
                        $query->setParameter(1, $request->get('id'));
                        $id = $request->get('id');
                    }
                    $teamleader = $query->getArrayResult();
                    return $this->render('maindbBundle:Default:gestionteamdetails.html.twig', array('roles' => $roles,
                                'name' => $user->getNom(),
                                'surname' => $user->getPrenom(),
                                'mail' => $user->getMail(),
                                'trigramme' => $user->getTrigramme(),
                                'equipe' => $equipe,
                                'membres' => $membres,
                                'users' => $users,
                                'id' => ($id),
                                'teamLeader' => $teamleader,
                                'error' => $error));
                }
                $repository = $em->getRepository('maindbBundle:Societe');
                $societes = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Service');
                $services = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Equipe');
                $equipes = $repository->findBy(array('actif' => 1));




                return $this->render('maindbBundle:Default:gestionteamlist.html.twig', array('roles' => $roles,
                            'name' => $user->getNom(),
                            'surname' => $user->getPrenom(),
                            'mail' => $user->getMail(),
                            'trigramme' => $user->getTrigramme(),
                            'societes' => $societes,
                            'services' => $services,
                            'equipes' => $equipes,
                            'error' => $error));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

    public function gestionsimpletimereportAction(Request $request) {
        if (!($this->installVerif($request))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $error = "";
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $permissions = $this->getPermissionUser($user);
                $isadmin = false;
                $permissionToTest = "administrator";
                foreach ($permissions as $permission) {
                    foreach ($permission as $per) {
                        if ($per == 'administrator') {
                            $isadmin = true;
                        }
                    }
                }
                if (!$isuser) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }



                $repository = $em->getRepository('maindbBundle:Societe');
                $societes = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Service');
                $services = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Equipe');
                $equipes = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Activite');
                $activites = $repository->findBy(array('actif' => 1));


                return $this->render('maindbBundle:Default:simpletimereport.php.twig', array('roles' => $roles,
                            'name' => $user->getNom(),
                            'surname' => $user->getPrenom(),
                            'mail' => $user->getMail(),
                            'trigramme' => $user->getTrigramme(),
                            'societes' => $societes,
                            'services' => $services,
                            'equipes' => $equipes,
                            'error' => $error,
                            'natures' => $this->natures,
                            'activites' => $activites));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

}
