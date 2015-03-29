<?php

namespace main\dbBundle\Controller;

use main\dbBundle\Entity\Tachesimple;
use main\dbBundle\Func\GlobalFunctions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use main\dbBundle\Func\InstallationFunctions;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DuplicateSimpleTaskController extends Controller {

    public function gestionduplicatesimplereportAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        if (!(InstallationFunctions::installVerif($em))) {
            return $this->render('maindbBundle:Default:installation.html.twig');
        }
        $error = "";
        $session = $this->getRequest()->getSession();
        
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            if ($user) {
                $userid = $user->getId();
                $permissions = GlobalFunctions::getPermissionUser($user, $em);
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
                $erreurtemps = "";
                $criticalError = null;

                $disableproduits = null;
                $disableclients = null;
                if ($request->get('idtosaveedition') && $request->getMethod() == 'POST') {
                    $repository = $em->getRepository('maindbBundle:Tachesimple');
                    $tacheModif = $repository->findOneBy(array('id' => $request->get('idtosaveedition')));
                    if ($tacheModif->getNature() == 'Product') {
                        $disableclients = 1;
                        $disableproduits = 0;
                    }
                    else if ($tacheModif->getNature() == 'Pre Sale' || $tacheModif->getNature() == 'Project') {
                        $disableproduits = 1;
                        $disableclients = 0;
                    }
                    if ($tacheModif->getUserId() == $user->getId()) {
                        if ($request->get('dateeditproduit')) {
                            $tacheDup = New Tachesimple();
                            $time = $request->get('dateeditproduit');
                            $tachesofday = $repository->findBy(array('actif' => 1, 'userId' => $userid, 'date' => $time));
                            $temps = 0.0;
                            foreach ($tachesofday as $task) {
                                $temps = $temps + floatval($task->getTempsPasse());
                            }
                            $tempsrestant = 1.0 - $temps;
                            $i = 0;
                            $times = array();
                            while ($i < $tempsrestant) {
                                $i = $i + 0.250;
                                array_push($times, strval($i));
                            }
                            if (!$times) {
                                $erreurtemps = "Sorry you have no time left available for the day you wanted " . $request->get('dateeditproduit');
                                $criticalError = "1";
                            }
                            else {
                                if ($request->get('temps')) {
                                    if (in_array($request->get('temps'), $times)) {
                                        $tacheDup->setDate($request->get('dateeditproduit'));
                                        $tacheDup->setTempsPasse($request->get('temps'));
                                    }
                                    else {
                                        $comma_separated = implode(",", $times);
                                        $erreurtemps = "Sorry but you dont have enough time available on : "
                                                . $request->get('dateeditproduit')
                                                . " . Wanted : "
                                                . $request->get('temps')
                                                . " but only "
                                                . $comma_separated
                                                . " available.";
                                        $criticalError = "1";
                                    }
                                }
                            }
                        }
                        if ($request->get('nomedit')) {
                            $tacheDup->setNom($request->get('nomedit'));
                        }
                        if ($request->get('finalcustomer')) {
                            $tacheDup->setClientId($request->get('finalcustomer'));
                        }
                        else {
                            $tacheDup->setClientId(1);
                        }
                        if ($request->get('activite')) {
                            $tacheDup->setActiviteId($request->get('activite'));
                        }
                        else {
                            $tacheDup->setActiviteId(1);
                        }
                        if ($tacheModif->getNature() == 'Product') {
                            if ($request->get('subphaseoption') && $request->getMethod() == 'POST') {
                                if ($request->get('product')) {
                                    $tacheDup->setProduitId($request->get('product'));
                                }
                                else {
                                    $tacheDup->setProduitId(1);
                                }
                                if ($request->get('version')) {
                                    $tacheDup->setVersionId($request->get('version'));
                                }
                                else {
                                    $tacheDup->setVersionId(1);
                                }
                                if ($request->get('plateforme')) {
                                    $tacheDup->setPlateformeId($request->get('plateforme'));
                                }
                                else {
                                    $tacheDup->setPlateformeId(1);
                                }
                                if ($request->get('component')) {
                                    $tacheDup->setComposantId($request->get('component'));
                                }
                                else {
                                    $tacheDup->setComposantId(1);
                                }
                            }
                            else {
                                if ($request->get('idoldprod')) {
                                    $tacheDup->setProduitId($request->get('idoldprod'));
                                }
                                if ($request->get('idoldvers')) {
                                    $tacheDup->setVersionId($request->get('idoldvers'));
                                }
                                if ($request->get('idoldplat')) {
                                    $tacheDup->setPlateformeId($request->get('idoldplat'));
                                }
                                if ($request->get('idoldcomp')) {
                                    $tacheDup->setComposantId($request->get('idoldcomp'));
                                }
                            }
                        }
                        else {
                            if ($request->get('product')) {
                                $tacheDup->setProduitId($request->get('product'));
                            }
                            else {
                                $tacheDup->setProduitId(1);
                            }
                            if ($request->get('version')) {
                                $tacheDup->setVersionId($request->get('version'));
                            }
                            else {
                                $tacheDup->setVersionId(1);
                            }
                            if ($request->get('plateforme')) {
                                $tacheDup->setPlateformeId($request->get('plateforme'));
                            }
                            else {
                                $tacheDup->setPlateformeId(1);
                            }
                            if ($request->get('component')) {
                                $tacheDup->setComposantId($request->get('component'));
                            }
                            else {
                                $tacheDup->setComposantId(1);
                            }
                        }
                        if ($tacheModif->getNature() == 'Pre Sale' || $tacheModif->getNature() == 'Project') {
                            if ($request->get('editcustomer')) {
                                if ($request->get('finalcustomer')) {
                                    $tacheDup->setClientId($request->get('finalcustomer'));
                                }
                                else {
                                    $tacheDup->setClientId(1);
                                }
                                if ($request->get('partenaire')) {
                                    $tacheDup->setPartenaireId($request->get('partenaire'));
                                }
                                else {
                                    $tacheDup->setPartenaireId(1);
                                }
                            }
                            else {
                                if ($request->get('idoldcustomer')) {
                                    $tacheDup->setClientId($request->get('idoldcustomer'));
                                }
                                else {
                                    $tacheDup->setClientId(1);
                                }
                                if ($request->get('idoldpartenaire')) {
                                    $tacheDup->setPartenaireId($request->get('idoldpartenaire'));
                                }
                                else {
                                    $tacheDup->setPartenaireId(1);
                                }
                            }
                        }
                        else {
                            if ($request->get('finalcustomer')) {
                                $tacheDup->setClientId($request->get('finalcustomer'));
                            }
                            else {
                                $tacheDup->setClientId(1);
                            }
                            if ($request->get('partenaire')) {
                                $tacheDup->setPartenaireId($request->get('partenaire'));
                            }
                            else {
                                $tacheDup->setPartenaireId(1);
                            }
                        }

                        if ($request->get('comment')) {
                            $tacheDup->setCommentaire($request->get('comment'));
                        }
                        $tacheDup->setUserId($user->getId());
                        $tacheDup->setActif(1);
                        $tacheDup->setEditable(1);
                        $tacheDup->setNature($tacheModif->getNature());
                        if (!$criticalError) {
                            $em->persist($tacheDup);
                            $em->flush();
                        }
                    }
                }

                $repository = $em->getRepository('maindbBundle:Composants');
                $composants = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Ssphase');
                $ssphases = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Phase');
                $phases = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Societe');
                $societes = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Service');
                $services = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Equipe');
                $equipes = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Activite');
                $activites2 = $repository->findBy(array('actif' => 1));

                $repository = $em->getRepository('maindbBundle:Partenaire');
                $partenaires = $repository->findBy(array('actif' => 1));

                //this is not to show modal
                $label = "";
                $date = "";
                $nature = "";
                $startdate = date("Y/m/d", strtotime("-1 week"));
                $endate = "9999/12/30";
                $naturesearched = "";

                if ($request->get('idToEdit')) {
                    $repository = $em->getRepository('maindbBundle:Tachesimple');
                    $taskToEdit = $repository->findOneBy(array('id' => $request->get('idToEdit')));
                    if ($taskToEdit->getuserId() == $user->getId()) {
                        $naturetodefineactivite = $taskToEdit->getNature();
                        $repository = $em->getRepository('maindbBundle:ActiviteNature');
                        $idactivitesToRecup = $repository->findBy(array('nature' => $naturetodefineactivite));
                        $activites = Array();
                        $repository = $em->getRepository('maindbBundle:Activite');
                        foreach ($idactivitesToRecup as $ITR) {
                            $element = $repository->findOneBy(array('id' => $ITR->getActiviteId()));
                            array_push($activites, $element);
                        }
                        if ($request->get('startdate') && $request->get('startdate') != "") {
                            $time = $request->get('startdate');
                            $startdate = $time;
                        }
                        if ($request->get('endate') && $request->get('endate') != "") {
                            $time = $request->get('endate');
                            $endate = $time;
                        }
                        $em = $this->getDoctrine()->getManager();

                        if ($request->get('natureshearched') && $request->get('natureshearched') != "") {
                            $naturesearched = $request->get('natureshearched');
                            $query = $em->createQuery(
                                    'SELECT t
                                    FROM maindbBundle:Tachesimple t
                                    WHERE t.date <= ?1 AND t.date >= ?2 AND t.nature = ?3 AND t.actif = 1 AND t.userId = ?4
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
                            $datestotest = GlobalFunctions::createDateRangeArray($startdate2, $enddate2);
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
                                if (GlobalFunctions::isWeekend($tache3)) {
                                    array_push($tachetoadd, "WE");
                                }
                                array_push($tachestodisplay, $tachetoadd);
                            }
                        }
                        $repository = $em->getRepository('maindbBundle:Composants');
                        $composants = $repository->findBy(array('actif' => 1));
                        $repository = $em->getRepository('maindbBundle:Ssphase');
                        $ssphases = $repository->findBy(array('actif' => 1));
                        $repository = $em->getRepository('maindbBundle:Phase');
                        $phases = $repository->findBy(array('actif' => 1));
                        $repository = $em->getRepository('maindbBundle:Societe');
                        $societes = $repository->findBy(array('actif' => 1));
                        $repository = $em->getRepository('maindbBundle:Service');
                        $services = $repository->findBy(array('actif' => 1));
                        $repository = $em->getRepository('maindbBundle:Equipe');
                        $equipes = $repository->findBy(array('actif' => 1));
                        $repository = $em->getRepository('maindbBundle:Activite');
                        $repository = $em->getRepository('maindbBundle:Client');
                        $clientstrait = $repository->findBy(array('actif' => 1));
                        $clients = $clientstrait;
                        $clientsa = Array();
                        foreach ($clientstrait as $client) {
                            $clideb = Array();
                            array_push($clideb, $client->getId(), $client->getNom());
                            $partenaires = Array();
                            $partenaire = Array();
                            $repo = $em->getRepository('maindbBundle:Partenaire');
                            $part = $repo->findOneBy(Array('id' => $client->getPartenaireId(), 'actif' => 1));
                            if ($part) {
                                array_push($partenaire, $part->getId());
                                array_push($partenaire, $part->getNom());
                            }
                            else {
                                array_push($partenaire, null);
                                array_push($partenaire, null);
                            }
                            array_push($partenaires, $partenaire);
                            array_push($clideb, $partenaires);
                            array_push($clientsa, $clideb);
                        }
                        $repository = $em->getRepository('maindbBundle:Produit');
                        $produits = $repository->findBy(array('actif' => 1));
                        $produitsAvecComposants = Array();
                        foreach ($produits as $produit) {
                            $repository = $em->getRepository('maindbBundle:ProduitVersion');
                            $idversion = $produit->getId();
                            $produitsversions = $repository->findBy(array('produitId' => $idversion));
                            $versions = Array();
                            foreach ($produitsversions as $produitversion) {
                                $version = Array();
                                $repository = $em->getRepository('maindbBundle:Version');
                                $version2 = $repository->findOneBy(array('id' => $produitversion->getVersionId(), 'actif' => 1));
                                if ($version2) {
                                    array_push($version, $version2->getId());
                                    array_push($version, $version2->getNumero());
                                    array_push($versions, $version);
                                }
                            }
                            $produitComp = Array();
                            array_push($produitComp, $produit->getId());
                            array_push($produitComp, $produit->getNom());
                            array_push($produitComp, $versions);
                            $repository = $em->getRepository('maindbBundle:ProduitComposant');
                            $idproduit = $produit->getId();
                            $produitscomposants = $repository->findBy(array('produitId' => $idproduit));
                            $composants = Array();
                            foreach ($produitscomposants as $produitscomposant) {
                                $composant = Array();
                                $repository = $em->getRepository('maindbBundle:Composants');
                                $composant2 = $repository->findOneBy(array('id' => $produitscomposant->getComposantId(), 'actif' => 1));
                                if ($composant2) {
                                    array_push($composant, $composant2->getId());
                                    array_push($composant, $composant2->getNom());
                                    array_push($composants, $composant);
                                }
                            }
                            array_push($produitComp, $composants);
                            $repository = $em->getRepository('maindbBundle:ProduitPlateforme');
                            $idproduit = $produit->getId();
                            $produitsplateformes = $repository->findBy(array('produitId' => $idproduit));
                            $plateformes = Array();
                            foreach ($produitsplateformes as $produitsplateforme) {
                                $plateforme = Array();
                                $repository = $em->getRepository('maindbBundle:Plateforme');
                                $plateforme2 = $repository->findOneBy(array('id' => $produitsplateforme->getPlateformeId(), 'actif' => 1));
                                if ($plateforme2) {
                                    array_push($plateforme, $plateforme2->getId());
                                    array_push($plateforme, $plateforme2->getNom());
                                    array_push($plateformes, $plateforme);
                                }
                            }
                            array_push($produitComp, $plateformes);
                            array_push($produitsAvecComposants, $produitComp);
                        }
                        $repository = $em->getRepository('maindbBundle:Version');
                        $versions = $repository->findBy(array('actif' => 1));
                        $repository = $em->getRepository('maindbBundle:Plateforme');
                        $plateformes = $repository->findBy(array('actif' => 1));
                        $repository = $em->getRepository('maindbBundle:Partenaire');
                        $partenaires = $repository->findBy(array('actif' => 1));
                        $repository = $em->getRepository('maindbBundle:Tachesimple');
                        $taches = $repository->findBy(array('actif' => 1, 'userId' => $userid));
                        $repository = $em->getRepository('maindbBundle:Tachesimple');
                        $naturesearched = "";
                        $time = $request->get('date');
                        $tachesofday = $repository->findBy(array('actif' => 1, 'userId' => $userid, 'date' => $time));
                        $temps = 0.0;
                        foreach ($tachesofday as $task) {
                            $temps = $temps + floatval($task->getTempsPasse());
                        }
                        $tempsrestant = 1.0 - $temps;
                        $i = 0;
                        $times = array();
                        while ($i < $tempsrestant) {
                            $i = $i + 0.250;
                            array_push($times, strval($i));
                        }
                        if (!$times) {
                            $erreurtemps = "Sorry you have no time left";
                        }
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

                        if ($request->get('natureshearched') && $request->get('natureshearched') != "") {
                            $naturesearched = $request->get('natureshearched');
                            $query = $em->createQuery(
                                    'SELECT t
                                    FROM maindbBundle:Tachesimple t
                                    WHERE t.date <= ?1 AND t.date >= ?2 AND t.nature = ?3 AND t.actif = 1 AND t.userId = ?4
                                    GROUP BY t.date
                                    '
                            );
                            $usermail = $user->getMail();
                            $query->setParameter(1, $endate);
                            $query->setParameter(2, $startdate);
                            $query->setParameter(3, $naturesearched);
                            $query->setParameter(4, $user->getId());
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
                            $startdate2 = $startdate2[0][1];
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
                            $datestotest = GlobalFunctions::createDateRangeArray($startdate2, $enddate2);
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
                                if (GlobalFunctions::isWeekend($tache3)) {
                                    array_push($tachetoadd, "WE");
                                }
                                array_push($tachestodisplay, $tachetoadd);
                            }
                        }
                        $repository = $em->getRepository('maindbBundle:produit');
                        $oldprod = $repository->findOneBy(Array('id' => $taskToEdit->getProduitId()));
                        if ($oldprod) {
                            $oldproda = $oldprod->getNom();
                            $oldprodid = $oldprod->getId();
                        }
                        else {
                            $oldproda = "None";
                            $oldprodid = null;
                        }
                        $repository = $em->getRepository('maindbBundle:version');
                        $oldvers = $repository->findOneBy(Array('id' => $taskToEdit->getVersionId()));
                        if ($oldvers) {
                            $oldversa = $oldvers->getNumero();
                            $oldversid = $oldvers->getid();
                        }
                        else {
                            $oldversa = "None";
                            $oldversid = null;
                        }
                        $repository = $em->getRepository('maindbBundle:plateforme');
                        $oldplat = $repository->findOneBy(Array('id' => $taskToEdit->getPlateformeId()));
                        if ($oldplat) {
                            $oldplata = $oldplat->getNom();
                            $oldplatid = $oldplat->getId();
                        }
                        else {
                            $oldplata = "None";
                            $oldplatid = null;
                        }
                        $repository = $em->getRepository('maindbBundle:Composants');
                        $oldcomp = $repository->findOneBy(Array('id' => $taskToEdit->getComposantId()));
                        if ($oldcomp) {
                            $oldcompa = $oldcomp->getNom();
                            $oldcompid = $oldcomp->getid();
                        }
                        else {
                            $oldcompa = "None";
                            $oldcompid = null;
                        }
                        $repository = $em->getRepository('maindbBundle:Client');
                        $oldcustomer = $repository->findOneBy(Array('id' => $taskToEdit->getClientId()));
                        if ($oldcustomer) {
                            $oldcustomera = $oldcustomer->getNom();
                            $oldcustomerid = $oldcustomer->getId();
                        }
                        else {
                            $oldcustomera = "None";
                            $oldcustomerid = null;
                        }
                        $repository = $em->getRepository('maindbBundle:Partenaire');
                        $oldpartenaire = $repository->findOneBy(Array('id' => $taskToEdit->getPartenaireId()));
                        if ($oldpartenaire) {
                            $oldpartenairea = $oldpartenaire->getNom();
                            $oldpartenaireid = $oldpartenaire->getId();
                        }
                        else {
                            $oldpartenairea = "None";
                            $oldpartenaireid = null;
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
                                    'name' => $user->getNom(),
                                    'surname' => $user->getPrenom(),
                                    'mail' => $user->getMail(),
                                    'trigramme' => $user->getTrigramme(),
                                    'societes' => $societes,
                                    'services' => $services,
                                    'equipes' => $equipes,
                                    'error' => $error,
                                    'natures' => GlobalFunctions::getNature(),
                                    'activites2' => $activites2,
                                    'activites' => $activites,
                                    'label' => $request->get('fname'),
                                    'date' => $request->get('date'),
                                    'natureselected' => $request->get('nature'),
                                    'times' => $times,
                                    'clients' => $clients,
                                    'produits' => $produits,
                                    'versions' => $versions,
                                    'plateformes' => $plateformes,
                                    'partenaires' => $partenaires,
                                    'erreurtemps' => $erreurtemps,
                                    'natureedit' => $taskToEdit->getNature(),
                                    'tacheedit' => $taskToEdit,
                                    'dup' => "1",
                                    'produitsavecversions' => json_encode($produitsAvecComposants),
                                    'oldprod' => $oldproda,
                                    'oldprodid' => $oldprodid,
                                    'oldvers' => $oldversa,
                                    'oldversid' => $oldversid,
                                    'oldplat' => $oldplata,
                                    'oldplatid' => $oldplatid,
                                    'oldcomp' => $oldcompa,
                                    'oldcompid' => $oldcompid,
                                    'oldcustomer' => $oldcustomera,
                                    'oldcustomerid' => $oldcustomerid,
                                    'oldCiD' => $oldcustomerid,
                                    'oldpartenaire' => $oldpartenairea,
                                    'oldpartenaireid' => $oldpartenaireid,
                                    'clientsa' => json_encode($clientsa),
                                    'disableclients' => $disableclients,
                                    'disableproduits' => $disableproduits,
                                    'tachestodisplay' => $tachestodisplay));
                    }
                    else {
                        print_r("error : you dont have access to other's report");
                    }
                }
                $repository = $em->getRepository('maindbBundle:Composants');
                $composants = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Ssphase');
                $ssphases = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Phase');
                $phases = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Societe');
                $societes = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Service');
                $services = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Equipe');
                $equipes = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Activite');
                $activites = $repository->findBy(array('actif' => 1));
                $repository = $em->getRepository('maindbBundle:Partenaire');
                $partenaires = $repository->findBy(array('actif' => 1));

                //this is not to show modal
                $label = "";
                $date = "";
                $nature = "";
                $startdate = "0000/00/00";
                $endate = "9999/12/30";
                $naturesearched = "";
                if ($request->getMethod() == 'POST' && $request->get('todelete')) {
                    $repository = $em->getRepository('maindbBundle:Tachesimple');
                    $tachetodelete = $repository->findOneBy(array('id' => $request->get('todelete')));
                    $tachetodelete->setActif(0);
                    $em->persist($tachetodelete);
                    $em->flush();
                }

                if ($request->get('startdate') && $request->get('startdate') != "") {
                    $time = $request->get('startdate');
                    $startdate = $time;
                }
                if ($request->get('endate') && $request->get('endate') != "") {
                    $time = $request->get('endate');
                    $endate = $time;
                }
                $em = $this->getDoctrine()->getManager();

                if ($request->get('natureshearched') && $request->get('natureshearched') != "") {
                    $naturesearched = $request->get('natureshearched');
                    $query = $em->createQuery(
                            'SELECT t
                                    FROM maindbBundle:Tachesimple t
                                    WHERE t.date <= ?1 AND t.date >= ?2 AND t.nature = ?3 AND t.actif = 1 AND t.userId = ?4
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
                    $startdate2 = $startdate2[0][1];
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
                    $datestotest = GlobalFunctions::createDateRangeArray($startdate2, $enddate2);
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
                        if (GlobalFunctions::isWeekend($tache3)) {
                            array_push($tachetoadd, "WE");
                        }
                        array_push($tachestodisplay, $tachetoadd);
                    }
                }
                
                return $this->render('maindbBundle:Default:simpletimereport2.php.twig', array('roles' => $roles,
                            'name' => $user->getNom(),
                            'startdatesearch' => $startdate,
                            'composants' => $composants,
                            'ssphases' => $ssphases,
                            'phases' => $phases,
                            'endatesearch' => $endate,
                            'naturesearch' => $naturesearched,
                            'tasks' => $taches2,
                            'surname' => $user->getPrenom(),
                            'mail' => $user->getMail(),
                            'trigramme' => $user->getTrigramme(),
                            'societes' => $societes,
                            'services' => $services,
                            'equipes' => $equipes,
                            'error' => $error,
                            'natures' => GlobalFunctions::getNature(),
                            'activites' => $activites,
                            'activites2' => $activites,
                            'label' => $label,
                            'date' => $date,
                            'natureselected' => $nature,
                            'partenaires' => $partenaires,
                            'erreurtemps' => $erreurtemps,
                            'natureedit' => '',
                            'dup' => "1",
                            'tachestodisplay' => $tachestodisplay));
            }
        }
        return $this->render('maindbBundle:Default:index.html.twig');
    }

}
