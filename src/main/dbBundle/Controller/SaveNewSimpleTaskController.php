<?php

namespace main\dbBundle\Controller;

use main\dbBundle\Entity\UserRole;
use main\dbBundle\Entity\Tachesimple;
use main\dbBundle\Entity\Utilisateur;
use main\dbBundle\Entity\Role;
use main\dbBundle\Func\GlobalFunctions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SaveNewSimpleTaskController extends Controller {

    private $natures = array(
        "Absence",
        "Internal",
        "Product",
        "Pre Sale",
        "Project"
    );

    function createDateRangeArray($strDateFrom, $strDateTo) {
        // takes two dates formatted as YYYY-MM-DD and creates an
        // inclusive array of the dates between the from and to dates.
        // could test validity of dates here but I'm already doing
        // that in the main script

        $aryRange = array();

        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Y/m/d', $iDateFrom)); // first entry
            while ($iDateFrom < $iDateTo) {
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange, date('Y/m/d', $iDateFrom));
            }
        }
        return $aryRange;
    }

    function isWeekend($date) {
        return (date('N', strtotime($date)) >= 6);
    }

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

    public function saveAction(Request $request) {
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
                $userid = $user->getId();
                $permissions = $this->getPermissionUser($user);
                $isuser = false;
                $permissionToTest = "user";
                foreach ($permissions as $permission) {
                    if ($role[1] = 'user') {
                        $isuser = true;
                    }
                }
                if (!$isuser) {
                    return $this->render('maindbBundle:Default:errorpermission.html.twig');
                }
                if (!GlobalFunctions::isUserInTeam($user)) {
                    return $this->render('maindbBundle:Default:errorpermission2.html.twig');
                }
                if ($request->getMethod() == 'POST' && $request->get('naturetosave') && $request->get('date') && $request->get('label')) {
                    $newtask = New Tachesimple();
                    $newtask->setUserId($userid);
                    if ($request->get('finalcustomer'))
                        $newtask->setClientId($request->get('finalcustomer'));
                    else
                        $newtask->setClientId(1);
                    if ($request->get('component'))
                        $newtask->setComposantId($request->get('component'));
                    else
                        $newtask->setComposantId(1);
                    if ($request->get('partenaire'))
                        $newtask->setPartenaireId($request->get('partenaire'));
                    else
                        $newtask->setPartenaireId(1);
                    if ($request->get('plateforme'))
                        $newtask->setPlateformeId($request->get('plateforme'));
                    else
                        $newtask->setPlateformeId(1);
                    if ($request->get('product'))
                        $newtask->setProduitId($request->get('product'));
                    else
                        $newtask->setProduitId(1);
                    if ($request->get('version'))
                        $newtask->setVersionId($request->get('version'));
                    else
                        $newtask->setVersionId(1);
                    $newtask->setActif(1);
                    $newtask->setEditable(1);
                    $newtask->setCommentaire($request->get('comment'));
                    $time = $request->get('date');
                    $newtask->setDate($time);
                    $newtask->setNature($request->get('naturetosave'));
                    $newtask->setNom($request->get('label'));
                    $newtask->setActiviteId($request->get('activite'));
                    $newtask->setTempsPasse($request->get('temps'));
                    $em->persist($newtask);
                    $em->flush();
                }
                $repository = $em->getRepository('maindbBundle:Composants');
                $composants = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Ssphase');
                $ssphases = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Phase');
                $phases = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Tachesimple');
                $taches = $repository->findBy(array('actif' => 1, 'userId' => $userid));
                $repository = $em->getRepository('maindbBundle:Societe');
                $societes = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Service');
                $services = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Equipe');
                $equipes = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Activite');
                $activites = $repository->findBy(array('actif' => 1));
                $activites2 = $activites;
                $repository = $em->getRepository('maindbBundle:Partenaire');
                $partenaires = $repository->findBy(array('actif' => 1));
                //this is not to show modal
                $label = "";
                $date = "";
                $nature = "";
                $erreurtemps = "";
                $startdate = date("Y/m/d", strtotime("-1 week"));
                $endate = "9999/12/30";
                if ($request->get('startdate') && $request->get('startdate') != "") {
                    $time = $request->get('startdate');
                    $startdate = $time;
                }
                if ($request->get('endate') && $request->get('endate') != "") {
                    $time = $request->get('endate');
                    $endate = $time;
                }
                $em = $this->getDoctrine()->getManager();

                $naturesearched = "";

                if ($request->get('natureshearched') && $request->get('natureshearched') != "") {
                    $naturesearched = $request->get('natureshearched');
                    $query = $em->createQuery(
                            'SELECT t
                                    FROM maindbBundle:Tachesimple t
                                    WHERE t.date <= ?1 AND t.date >= ?2 AND t.nature = ?3 AND t.actif = 1 AND t.userId =?4
                                    GROUP BY t.date
                                    '
                    );
                    $usermail = $user->getMail();
                    $query->setParameter(1, $endate);
                    $query->setParameter(2, $startdate);
                    $query->setParameter(3, $naturesearched);
                    $query->setParameter(4, $user->getId());
                    $taches2 = $query->getArrayResult();
                    $tachestodisplay = Array();
                    foreach ($taches2 as $tache3) {
                        $tachetoadd = Array();
                        array_push($tachetoadd, $tache3['date']);
                        $query = $em->createQuery(
                                'SELECT t
                                    FROM maindbBundle:Tachesimple t
                                    WHERE t.date = ?1 AND t.nature = ?3 AND t.actif = 1 AND t.userId = ?4
                                    '
                        );
                        $usermail = $user->getMail();
                        $query->setParameter(1, $tache3['date']);
                        $query->setParameter(3, $naturesearched);
                        $query->setParameter(4, $user->getId());
                        $taches4 = $query->getArrayResult();
                        $tachesparjournées = Array();
                        $temppassé = 0;
                        $repository = $em->getRepository('maindbBundle:Tachesimple');
                        $tachesTempsPasse = $repository->findBy(Array('date' => $tache3['date'], 'actif' => 1, 'userId' => $user->getId()));
                        foreach ($tachesTempsPasse as $ttp) {
                            $temppassé = $temppassé + $ttp->getTempsPasse();
                        }
                        $taches = Array();
                        foreach ($taches4 as $tache5) {
                            array_push($tachesparjournées, $tache5);
                            array_push($taches, $tache5);
                        }
                        array_push($tachetoadd, $temppassé);
                        array_push($tachetoadd, $taches);
                        array_push($tachestodisplay, $tachetoadd);
                    }
                }
                else {
                    $query = $em->createQuery(
                            'SELECT t
                                    FROM maindbBundle:Tachesimple t
                                    WHERE t.date <= ?1 AND t.date >= ?2 AND t.actif = 1 AND t.userId = ?3
                                    GROUP BY t.date
                                    '
                    );
                    $usermail = $user->getMail();
                    $query->setParameter(1, $endate);
                    $query->setParameter(2, $startdate);
                    $query->setParameter(3, $user->getId());
                    $taches2 = $query->getArrayResult();
                    $query = $em->createQuery(
                            'SELECT MIN (t.date)
                                    FROM maindbBundle:Tachesimple t
                                    WHERE t.actif = 1 AND t.userId = ?3
                                    
                                    '
                    );
                    $query->setParameter(3, $user->getId());
                    $startdate2 = $query->getArrayResult();
                    $startdate2 = date("Y/m/d", strtotime("-1 week"));
                    if (!$startdate2) {
                        $startdate2 = $user->getDateInscription();
                    }
                    $query = $em->createQuery(
                            'SELECT MAX (t.date)
                                    FROM maindbBundle:Tachesimple t
                                    WHERE t.actif = 1 AND t.userId = ?3
                                    
                                    '
                    );
                    $query->setParameter(3, $user->getId());
                    $enddate2 = $query->getArrayResult();
                    $enddate2 = $enddate2[0][1];
                    $dateendreport = $enddate2;
                    $today = $today = date("Y/m/d");
                    if ($today > $enddate2) {
                        $enddate2 = $today;
                    }
                    if ($request->get('startdate') && $request->get('startdate') != "") {
                        $time = $request->get('startdate');
                        $startdate2 = $time;
                    }
                    if ($request->get('endate') && $request->get('endate') != "") {
                        $time = $request->get('endate');
                        $enddate2 = $time;
                        if ($enddate2 > $dateendreport) {
                            $enddate2 = $dateendreport;
                        }
                    }
                    $datestotest = $this->createDateRangeArray($startdate2, $enddate2);
                    ////////////////////////
                    $tachestodisplay = Array();

                    foreach ($datestotest as $tache3) {
                        $tachetoadd = Array();
                        array_push($tachetoadd, $tache3);
                        $query = $em->createQuery(
                                'SELECT t
                                    FROM maindbBundle:Tachesimple t
                                    WHERE t.date = ?1 AND t.actif = 1 AND t.userId = ?4
                                    '
                        );
                        $usermail = $user->getMail();
                        $query->setParameter(1, $tache3);
                        $query->setParameter(4, $user->getId());
                        $taches4 = $query->getArrayResult();
                        $tachesparjournées = Array();
                        $temppassé = 0;
                        $repository = $em->getRepository('maindbBundle:Tachesimple');
                        $tachesTempsPasse = $repository->findBy(Array('date' => $tache3, 'actif' => 1, 'userId' => $user->getId()));
                        foreach ($tachesTempsPasse as $ttp) {
                            $temppassé = $temppassé + $ttp->getTempsPasse();
                        }
                        $taches = Array();
                        foreach ($taches4 as $tache5) {
                            array_push($tachesparjournées, $tache5);
                            array_push($taches, $tache5);
                        }
                        array_push($tachetoadd, $temppassé);
                        array_push($tachetoadd, $taches);
                        if ($this->isWeekend($tache3)) {
                            array_push($tachetoadd, "WE");
                        }
                        array_push($tachestodisplay, $tachetoadd);
                    }
                }


                return $this->render('maindbBundle:Default:simpletimereport2.php.twig', array('roles' => $roles,
                            'name' => $user->getNom(),
                            'tasks' => $taches2,
                            'startdatesearch' => $startdate,
                            'composants' => $composants,
                            'ssphases' => $ssphases,
                            'phases' => $phases,
                            'endatesearch' => $endate,
                            'naturesearch' => $naturesearched,
                            'surname' => $user->getPrenom(),
                            'mail' => $user->getMail(),
                            'trigramme' => $user->getTrigramme(),
                            'societes' => $societes,
                            'services' => $services,
                            'equipes' => $equipes,
                            'error' => $error,
                            'natures' => $this->natures,
                            'activites' => $activites,
                            'label' => $label,
                            'date' => $date,
                            'natureselected' => $nature,
                            'partenaires' => $partenaires,
                            'erreurtemps' => $erreurtemps,
                            'natureedit' => '',
                            'activites2' => $activites2,
                            'tachestodisplay' => $tachestodisplay));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

}
