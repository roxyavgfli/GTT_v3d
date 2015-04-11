<?php

namespace main\dbBundle\Func\SimpleTaskFunctions;

use main\dbBundle\Func\GlobalFunctions;

class SimpleTaskControllerFunctions {

    /**
     * Function to get Array with the tasks for ExportSimpleTaskController
     * @param EntityManager $em
     * @param Request $request
     * @param Utilisateur $user
     * @return Array
     */
    static function mainTreatment($em, $request, $user) {
        $startdate = SimpleTaskControllerFunctions::getStartDate($request);
        $endate = SimpleTaskControllerFunctions::getEndDate($request, $user, $em);
        $naturesearched = SimpleTaskControllerFunctions::getNatureSearched($request);
        SimpleTaskControllerFunctions::taskDelete($em, $request);
        $dates = SimpleTaskControllerFunctions::createDateRangeArray($startdate, $endate);
        $tasksToDisplay = SimpleTaskControllerFunctions::getTasksToDisplay($naturesearched, $dates, $em, $user);
        return $tasksToDisplay;
    }

    /**
     * Function used to know if $date is weekend
     * @param String $date The date to test
     * @return Boolean True if date is week end false else
     */
    static function isWeekEnd($date) {
        return (date('N', strtotime($date)) >= 6);
    }

    /**
     * Function used to get an array from dates between two dates
     * @param String $strDateFrom
     * @param String $strDateTo
     * @return Array Array that countains all dates between $strDateFrom and $strDateTo
     */
    static function createDateRangeArray($strDateFrom, $strDateTo) {
        $aryRange = array();
        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));
        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Y/m/d', $iDateFrom));
            while ($iDateFrom < $iDateTo) {
                $iDateFrom+=86400;
                array_push($aryRange, date('Y/m/d', $iDateFrom));
            }
        }
        return $aryRange;
    }

    /**
     * Function to delete a task if needed
     * @param EntityManager $em
     * @param Request $request
     */
    static function taskDelete($em, $request) {
        if ($request->getMethod() == 'POST' && $request->get('todelete')) {
            $repository = $em->getRepository('maindbBundle:Tachesimple');
            $tachetodelete = $repository->findOneBy(array('id' => $request->get('todelete')));
            $tachetodelete->setActif(0);
            $em->persist($tachetodelete);
            $em->flush();
        }
    }

    /**
     * function to return startdate
     * @param Request $request
     * @return String startdate
     */
    static function getStartDate($request) {
        $startdate = date("Y/m/d", strtotime("-1 week"));
        if ($request->get('startdate') && $request->get('startdate') != "") {
            $time = $request->get('startdate');
            $startdate = $time;
        }
        return $startdate;
    }

    /**
     * function to return endate
     * @param Request $request
     * @param Utilisateur $user
     * @param EntityManager $em
     * @return String endate
     */
    static function getEndDate($request, $user, $em) {
        $endate = SimpleTaskControllerFunctions::getMaxDateUser($user, $em);
        if ($request->get('endate') && $request->get('endate') != "") {
            $time = $request->get('endate');
            $endate = $time;
        }
        $today = date("Y/m/d");
        if ($today > $endate) {
            $endate = $today;
        }
        return $endate;
    }

    /**
     * Function to get the Max date registered
     * @param Utilisateur $user
     * @param EntityManager $em
     * @return Array
     */
    static function getMaxDateUser($user, $em) {
        $query = $em->createQuery(
                'SELECT MAX (t.date)
                                    FROM maindbBundle:Tachesimple t
                                    WHERE t.actif = 1 AND t.userId = ?3
                                    
                                    '
        );
        $query->setParameter(3, $user->getId());
        return $query->getArrayResult()[0][1];
    }

    /**
     * Function to get the naturesearched if it is defined
     * @param Request $request
     * @return String
     */
    static function getNatureSearched($request) {
        $naturesearched = "";
        if ($request->get('natureshearched') && $request->get('natureshearched') != "") {
            $naturesearched = $request->get('natureshearched');
        }
        return $naturesearched;
    }

    /**
     * Return tasks for the user $user beteween $startdate and $endate matching with $naturesearched
     * @param EntityManager $em The entity manager
     * @param Request $request The request
     * @param String $naturesearched The nature searched
     * @param String $endate The end date
     * @param String $startdate The start date
     * @param Utilisateur $user The user
     * @return Array with tasks for the user $user beteween $startdate and $endate matching with $naturesearched
     */
    static function getTachesUserNatureDefinedDatesDefined($em, $request, $naturesearched, $endate, $startdate, $user) {
        if ($naturesearched != "") {
            $naturesearched = $request->get('natureshearched');
            $query = $em->createQuery(
                    'SELECT t
                FROM maindbBundle:Tachesimple t
                WHERE t.date <= ?1 AND t.date >= ?2 AND t.nature = ?3 AND t.actif = 1 AND t.userId = ?4
                GROUP BY t.date'
            );
            $query->setParameter(1, $endate);
            $query->setParameter(2, $startdate);
            $query->setParameter(3, $naturesearched);
            $query->setParameter(4, $user->getId());
            return($query->getArrayResult());
        }
    }

    /**
     * Function used to get tasks to display
     * @param Array $dates Array with dates
     * @param EntityManager $em The entity manager
     * @param String $naturesearched The nature searched
     * @param Utilisateur $user The user
     * @return Array with the tasks to display for a nature defined
     */
    static function getTasksToDisplayNatureDefined($dates, $em, $naturesearched, $user) {
        $tachestodisplay = Array();
        foreach ($dates as $date) {
            $tachetoadd = Array();
            array_push($tachetoadd, $date);
            $timeSpent = SimpleTaskControllerFunctions::getTimeSpentDateDefined($em, $date, $user);
            $taches = Array();
            $tachesOfDay = SimpleTaskControllerFunctions::getTasksUserDateDefined($em, $date, $naturesearched, $user);
            foreach ($tachesOfDay as $tache) {
                array_push($taches, $tache);
            }
            array_push($tachetoadd, $timeSpent);
            array_push($tachetoadd, $taches);
            if (SimpleTaskControllerFunctions::isWeekEnd($date)) {
                array_push($tachetoadd, "WE");
            }
            array_push($tachestodisplay, $tachetoadd);
        }
        return $tachestodisplay;
    }

    /**
     * Function to get time spent for a day
     * @param EntityManager $em The entity manager
     * @param String $date The date
     * @param Utilisateur $user The user
     * @return Float The time spent for the date $date
     */
    static function getTimeSpentDateDefined($em, $date, $user) {
        $temppassé = 0;
        $repository = $em->getRepository('maindbBundle:Tachesimple');
        $tachesTempsPasse = $repository->findBy(Array('date' => $date, 'actif' => 1, 'userId' => $user->getId()));
        foreach ($tachesTempsPasse as $ttp) {
            $temppassé = $temppassé + $ttp->getTempsPasse();
        }
        return $temppassé;
    }

    /**
     * Function to return tasks for $user at $date matching with $naturesearched
     * @param EntityManager $em The entity manager
     * @param String $date The date
     * @param String $naturesearched The nature searched
     * @param Utilisateur $user The user
     * @return Array The array with tasks
     */
    static function getTasksUserDateDefined($em, $date, $naturesearched, $user) {
        $query = $em->createQuery(
                'SELECT t
                                    FROM maindbBundle:Tachesimple t
                                    WHERE t.date = ?1 AND t.nature = ?2 AND t.actif = 1 AND t.userId = ?3
                                    '
        );
        $query->setParameter(1, $date);
        $query->setParameter(2, $naturesearched);
        $query->setParameter(3, $user->getId());
        return($query->getArrayResult());
    }

    /**
     * Function used to get tasks for an user with dates defined
     * @param Array $dates The dates
     * @param EntityManager $em The entity manager
     * @param Utilisateur $user The user
     * @return Array
     */
    static function getTasksToDisplayNatureUndefined($dates, $em, $user) {
        $tachestodisplay = Array();
        foreach ($dates as $date) {
            $tachetoadd = Array();
            array_push($tachetoadd, $date);
            $timeSpent = SimpleTaskControllerFunctions::getTimeSpentDateDefined($em, $date, $user);
            $taches = Array();
            $tachesOfDay = SimpleTaskControllerFunctions::getTasksUserDateDefinedNatureUndefined($em, $date, $user);
            foreach ($tachesOfDay as $tache) {
                array_push($taches, $tache);
            }
            array_push($tachetoadd, $timeSpent);
            array_push($tachetoadd, $taches);
            if (SimpleTaskControllerFunctions::isWeekEnd($date)) {
                array_push($tachetoadd, "WE");
            }
            array_push($tachestodisplay, $tachetoadd);
        }
        return $tachestodisplay;
    }

    /**
     * Function to get Tasks for an user and a defined date
     * @param EntityManager $em The entity manager
     * @param String $date The date
     * @param Utilisateur $user The user
     * @return Array The array
     */
    static function getTasksUserDateDefinedNatureUndefined($em, $date, $user) {
        $query = $em->createQuery(
                'SELECT t
                                    FROM maindbBundle:Tachesimple t
                                    WHERE t.date = ?1 AND t.actif = 1 AND t.userId = ?2
                                    '
        );
        $query->setParameter(1, $date);
        $query->setParameter(2, $user->getId());
        return($query->getArrayResult());
    }

    /**
     * The function used to get Tasks to display
     * @param String $naturesearched
     * @param Array $dates The array with tasks to display
     * @param EntityManager $em The entity manager
     * @param Utilisateur $user The user
     * @return Array The tasks to display
     */
    static function getTasksToDisplay($naturesearched, $dates, $em, $user) {
        if ($naturesearched == "") {
            return SimpleTaskControllerFunctions::getTasksToDisplayNatureUndefined($dates, $em, $user);
        }
        else {
            return SimpleTaskControllerFunctions::getTasksToDisplayNatureDefined($dates, $em, $naturesearched, $user);
        }
    }

    /**
     * Function to get Array with clients and partners
     * @param EntityManager $em The entity manager
     * @return Array The array with client and partners
     */
    static function getArrayClientToDisplay($em) {
        $clientToDisplay = Array();
        $clientstrait = GlobalFunctions::getFromRepository($em, 'Client');
        foreach ($clientstrait as $client) {
            if ($client['id'] != 0) {
                $clideb = Array();
                array_push($clideb, $client['id'], $client['nom']);
                $partenaires = Array();
                $partenaire = Array();
                $repo = $em->getRepository('maindbBundle:Partenaire');
                $part = $repo->findOneBy(Array('id' => $client['partenaireId'], 'actif' => 1));
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
                array_push($clientToDisplay, $clideb);
            }
        }
        return $clientToDisplay;
    }

    /**
     * Function to get Array with Products and components
     * @param EntityManager $em The entity manager
     * @return Array The array with Products and components
     */
    static function getArrayProduitsToDisplay($em) {
        $produits = GlobalFunctions::getFromRepository($em, 'Produit');
        $produitsAvecComposants = Array();
        foreach ($produits as $produit) {
            if ($produit['id'] != 1) {
                $repository = $em->getRepository('maindbBundle:ProduitVersion');
                $idversion = $produit['id'];
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
                array_push($produitComp, $produit['id']);
                array_push($produitComp, $produit['nom']);
                array_push($produitComp, $versions);
                $repositoryPC = $em->getRepository('maindbBundle:ProduitComposant');
                $idproduit = $produit['id'];
                $produitscomposants = $repositoryPC->findBy(array('produitId' => $idproduit));
                $composants = Array();
                foreach ($produitscomposants as $produitscomposant) {
                    $composant = Array();
                    $repositoryC = $em->getRepository('maindbBundle:Composants');
                    $composant2 = $repositoryC->findOneBy(array('id' => $produitscomposant->getComposantId(), 'actif' => 1));
                    if ($composant2) {
                        array_push($composant, $composant2->getId());
                        array_push($composant, $composant2->getNom());
                        array_push($composants, $composant);
                    }
                }
                array_push($produitComp, $composants);
                $repositoryPP = $em->getRepository('maindbBundle:ProduitPlateforme');
                $produitsplateformes = $repositoryPP->findBy(array('produitId' => $idproduit));
                $plateformes = Array();
                foreach ($produitsplateformes as $produitsplateforme) {
                    $plateforme = Array();
                    $repositoryP = $em->getRepository('maindbBundle:Plateforme');
                    $plateforme2 = $repositoryP->findOneBy(array('id' => $produitsplateforme->getPlateformeId(), 'actif' => 1));
                    if ($plateforme2) {
                        array_push($plateforme, $plateforme2->getId());
                        array_push($plateforme, $plateforme2->getNom());
                        array_push($plateformes, $plateforme);
                    }
                }
                array_push($produitComp, $plateformes);
                array_push($produitsAvecComposants, $produitComp);
            }
        }
        return $produitsAvecComposants;
    }

    /**
     * Function to get different times left for date
     * @param EntityManager $em The Entity Manager
     * @param Request $request The request
     * @param Utilisateur $user The user
     * @return Array Different times left for date
     */
    static function getTimes($em, $request, $user) {
        $repository = $em->getRepository('maindbBundle:Tachesimple');
        $times = array();
        if ($request->get('date')) {
            $time = $request->get('date');
            $tachesofday = $repository->findBy(array('actif' => 1, 'userId' => $user->getId(), 'date' => $time));
            $temps = 0.0;
            foreach ($tachesofday as $task) {
                if ($task->getId()!=$request->get('idToEdit')) {
                    $temps = $temps + floatval($task->getTempsPasse());
                }
            }
            $tempsrestant = 1.0 - $temps;
            $i = 0;
            while ($i < $tempsrestant) {
                $i = $i + 0.250;
                array_push($times, strval($i));
            }
        }else {
            $times = array(0.25,0.5,0.75,1);
        }
        return $times;
    }
    
    static function getTimesEdition($em, $request, $user, $taskEdited) {
        $repository = $em->getRepository('maindbBundle:Tachesimple');
        $times = array();
        if ($taskEdited) {
            $time = $taskEdited->getDate();
            $tachesofday = $repository->findBy(array('actif' => 1, 'userId' => $user->getId(), 'date' => $time));
            $temps = 0.0;
            foreach ($tachesofday as $task) {
                if ($task->getId()!=$taskEdited->getId()) {
                    $temps = $temps + floatval($task->getTempsPasse());
                }
            }
            $tempsrestant = 1.0 - $temps;
            $i = 0;
            while ($i < $tempsrestant) {
                $i = $i + 0.250;
                array_push($times, strval($i));
            }
        }else {
            $times = array(0.25,0.5,0.75,1);
        }
        return $times;
    }

    /**
     * Function to get natures to display
     * @param EntityManager $em The entity manager
     * @param Request $request The request
     * @return Array the natures to display
     */
    static function getNaturesToDisplay($em, $request) {
        $repository = $em->getRepository('maindbBundle:ActiviteNature');
        $idactivitesToRecup = $repository->findBy(array('nature' => $request->get('nature')));
        $activites = Array();
        $repository2 = $em->getRepository('maindbBundle:Activite');
        foreach ($idactivitesToRecup as $ITR) {
            $element = $repository2->findOneBy(array('id' => $ITR->getActiviteId(), 'actif' => 1));
            if ($element) {
                array_push($activites, $element);
            }
        }
        return $activites;
    }

    /**
     * Function to get Error Message
     * @param Array $times The times available
     * @return String Error message
     */
    static function getErrorMessage($times) {
        if (!$times) {
            return "You have no time left for this day";
        }
        else {
            return '';
        }
    }

    /**
     * Function used to clear old values and set new ones
     * @param Request $request The request
     * @param EntityManager $em The entity manager
     * @param Utilisateur $user The user
     */
    static function editionTreatment($request, $em, $user) {
        if ($request->get('idtosaveedition') && $request->getMethod() == 'POST') {
            $repository = $em->getRepository('maindbBundle:Tachesimple');
            $tacheModif = $repository->findOneBy(array('id' => $request->get('idtosaveedition')));
            if ($tacheModif->getUserId() == $user->getId()) {
                if ($request->get('dateeditproduit')) {
                    $time = $request->get('dateeditproduit');
                    $query = $em->createQuery(
                            'SELECT t
                                    FROM maindbBundle:Tachesimple t
                                    WHERE t.date = ?1 AND t.actif = 1 AND t.userId = ?4 AND t.id != ?2
                                    GROUP BY t.date
                                    '
                    );
                    $query->setParameter(1, $time);
                    $query->setParameter(2, $request->get('idtosaveedition'));
                    $query->setParameter(4, $user->getId());
                    $tachesofday = $query->getResult();
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
                    }
                    else {
                        if ($request->get('temps')) {
                            if (in_array($request->get('temps'), $times)) {
                                $tacheModif->setDate($request->get('dateeditproduit'));
                                $tacheModif->setTempsPasse($request->get('temps'));
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
                            }
                        }
                    }
                }
                if ($request->get('nomedit')) {
                    $tacheModif->setNom($request->get('nomedit'));
                }
                if ($tacheModif->getNature() == 'Pre Sale' || $tacheModif->getNature() == 'Project') {
                    if ($request->get('editcustomer')) {
                        if ($request->get('finalcustomer')) {
                            $tacheModif->setClientId($request->get('finalcustomer'));
                            $tacheModif->setPartenaireId(1);
                        }
                        else {
                            $tacheModif->setClientId(1);
                            $tacheModif->setPartenaireId(1);
                        }
                        if ($request->get('partenaire')) {
                            $tacheModif->setPartenaireId($request->get('partenaire'));
                        }
                        else {
                            $tacheModif->setPartenaireId($request->get(1));
                        }
                    }
                }
                else {
                    if ($request->get('finalcustomer')) {
                        $tacheModif->setClientId($request->get('finalcustomer'));
                        $tacheModif->setPartenaireId(1);
                    }
                    else {
                        $tacheModif->setClientId(1);
                        $tacheModif->setPartenaireId(1);
                    }
                    if ($request->get('partenaire')) {
                        $tacheModif->setPartenaireId($request->get('partenaire'));
                    }
                    else {
                        $tacheModif->setPartenaireId(1);
                    }
                }
                if ($request->get('activite')) {
                    $tacheModif->setActiviteId($request->get('activite'));
                }
                else {
                    $tacheModif->setActiviteId(1);
                }
                if ($tacheModif->getNature() == 'Product') {
                    if ($request->get('subphaseoption')) {
                        if ($request->get('product')) {
                            $tacheModif->setProduitId($request->get('product'));
                        }
                        else {
                            $tacheModif->setProduitId(1);
                        }
                        if ($request->get('version')) {
                            $tacheModif->setVersionId($request->get('version'));
                        }
                        else {
                            $tacheModif->setVersionId(1);
                        }
                        if ($request->get('plateforme')) {
                            $tacheModif->setPlateformeId($request->get('plateforme'));
                        }
                        else {
                            $tacheModif->setPlateformeId(1);
                        }
                        if ($request->get('component')) {
                            $tacheModif->setComposantId($request->get('component'));
                        }
                        else {
                            $tacheModif->setComposantId(1);
                        }
                    }
                }
                else {
                    if ($request->get('product')) {
                        $tacheModif->setProduitId($request->get('product'));
                    }
                    else {
                        $tacheModif->setProduitId(1);
                    }
                    if ($request->get('version')) {
                        $tacheModif->setVersionId($request->get('version'));
                    }
                    else {
                        $tacheModif->setVersionId(1);
                    }
                    if ($request->get('plateforme')) {
                        $tacheModif->setPlateformeId($request->get('plateforme'));
                    }
                    else {
                        $tacheModif->setPlateformeId(1);
                    }
                    if ($request->get('component')) {
                        $tacheModif->setComposantId($request->get('component'));
                    }
                    else {
                        $tacheModif->setComposantId(1);
                    }
                }
                if ($request->get('comment')) {
                    $tacheModif->setCommentaire($request->get('comment'));
                }
                $em->persist($tacheModif);
                $em->flush();
            }
        }
    }

    /**
     * Function used to clear client and associate them with partner
     * @param EntityManager $em The entity manager
     * @param Array $clientstrait The clients to clear
     * @return Array The array with clients  and their partners
     */
    static function clearClients($em, $clientstrait) {
        $clientsa = Array();
        foreach ($clientstrait as $client) {
            $clideb = Array();
            array_push($clideb, $client['id'], $client['nom']);
            $partenaires = Array();
            $partenaire = Array();
            $repo = $em->getRepository('maindbBundle:Partenaire');
            $part = $repo->findOneBy(Array('id' => $client['partenaireId'], 'actif' => 1));
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
        return $clientsa;
    }
    
    /**
     * Function treating edition
     * @param Request $request The request
     * @param EntitManager $em The entity manager
     * @param Utilisateur $user The user
     * @param Array $roles The roles of the user
     * @param Array $activites2 The activities
     * @param Array $clients The clients
     * @param Array $ssphases The subphases
     * @param Array $phases The phases
     * @param Array $natures The natures
     * @return Page The page ton be render
     */
    function mainFunctionEditionIfEditionToBeMade($request, $em, $user, $roles, $activites2, $clients, $ssphases, $phases, $natures) {
        $equipes = GlobalFunctions::getFromRepository($em, 'Equipe');
        $societes = GlobalFunctions::getFromRepository($em, 'Societe');
        $services = GlobalFunctions::getFromRepository($em, 'Service');
        $startdate = SimpleTaskControllerFunctions::getStartDate($request);
        $endate = SimpleTaskControllerFunctions::getEndDate($request, $user, $em);
        $repositoryVersion = $em->getRepository('maindbBundle:Version');
        $versions = $repositoryVersion->findBy(array('actif' => 1));
        $repositoryProduit = $em->getRepository('maindbBundle:Produit');
        $produits = $repositoryProduit->findBy(array('actif' => 1));
        $repositoryPlateforme = $em->getRepository('maindbBundle:Plateforme');
        $plateformes = $repositoryPlateforme->findBy(array('actif' => 1));
        $repositoryPartenaire = $em->getRepository('maindbBundle:Partenaire');
        $partenaires = $repositoryPartenaire->findBy(array('actif' => 1));
        $repositoryTacheSimple = $em->getRepository('maindbBundle:Tachesimple');
        $taches = $repositoryTacheSimple->findBy(array('actif' => 1, 'userId' => $user->getId()));
        $repositoryProduitPlateforme = $em->getRepository('maindbBundle:ProduitPlateforme');
        $repositoryProduitComposant = $em->getRepository('maindbBundle:ProduitComposant');
        $produitscomposants = $repositoryProduitComposant->findAll();
        if ($request->get('idToEdit')) {
            $repository = $em->getRepository('maindbBundle:Tachesimple');
            $taskToEdit = $repository->findOneBy(array('id' => $request->get('idToEdit')));
            if ($taskToEdit->getNature() == 'Product') {
                $disableclients = 1;
                $disableproduits = 0;
            }
            else if ($taskToEdit->getNature() == 'Pre Sale' || $taskToEdit->getNature() == 'Project') {
                $disableproduits = 1;
                $disableclients = 0;
            }else{
                $disableproduits = 0;
                $disableclients = 0;
            }
            if ($taskToEdit->getuserId() == $user->getId()) {
                $naturetodefineactivite = $taskToEdit->getNature();
                $repositoryActiviteNature = $em->getRepository('maindbBundle:ActiviteNature');
                $idactivitesToRecup = $repositoryActiviteNature->findBy(array('nature' => $naturetodefineactivite));
                $activites = Array();
                $repositoryActivite = $em->getRepository('maindbBundle:Activite');
                foreach ($idactivitesToRecup as $ITR) {
                    $element = $repositoryActivite->findOneBy(array('id' => $ITR->getActiviteId(), 'actif' => 1));
                    if ($element) {
                        array_push($activites, $element);
                    }
                }

                $datestotest = SimpleTaskControllerFunctions::createDateRangeArray($startdate, $endate);
                $tachestodisplay = SimpleTaskControllerFunctions::getTasksToDisplayNatureUndefined($datestotest, $em, $user);


                $produitsAvecComposants = Array();
                foreach ($produits as $produit) {
                    $repositoryVersion = $em->getRepository('maindbBundle:ProduitVersion');
                    $idversion = $produit->getId();
                    $produitsversions = $repositoryVersion->findBy(array('produitId' => $idversion));
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
                    $produitscomposants = $repositoryProduitComposant->findBy(array('produitId' => $idproduit));
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
                    $produitsplateformes = $repositoryProduitPlateforme->findBy(array('produitId' => $idproduit));
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
                $naturesearched = "";
                $times = SimpleTaskControllerFunctions::getTimesEdition($em, $request, $user, $taskToEdit);
                $time = $request->get('date');

                if (!$times) {
                    $erreurtemps = "Sorry you have no time left";
                }

                $repositoryProduit = $em->getRepository('maindbBundle:produit');
                $oldprod = $repositoryProduit->findOneBy(Array('id' => $taskToEdit->getProduitId()));
                if ($oldprod) {
                    $oldprod = $oldprod->getNom();
                }
                else {
                    $oldprod = "None";
                }
                $repositoryVersion = $em->getRepository('maindbBundle:version');
                $oldvers = $repositoryVersion->findOneBy(Array('id' => $taskToEdit->getVersionId()));
                if ($oldvers) {
                    $oldvers = $oldvers->getNumero();
                }
                else {
                    $oldvers = "None";
                }
                $repositoryPlateforme = $em->getRepository('maindbBundle:plateforme');
                $oldplat = $repositoryPlateforme->findOneBy(Array('id' => $taskToEdit->getPlateformeId()));
                if ($oldplat) {
                    $oldplat = $oldplat->getNom();
                }
                else {
                    $oldplat = "None";
                }
                $repositoryComposants = $em->getRepository('maindbBundle:Composants');
                $oldcomp = $repositoryComposants->findOneBy(Array('id' => $taskToEdit->getComposantId()));
                if ($oldcomp) {
                    $oldcomp = $oldcomp->getNom();
                }
                else {
                    $oldcomp = "None";
                }
                $repositoryClient = $em->getRepository('maindbBundle:Client');
                $oldcustomer = $repositoryClient->findOneBy(Array('id' => $taskToEdit->getClientId()));
                if ($oldcustomer) {
                    $oldcustomera = $oldcustomer->getNom();
                    $oldcustomerid = $oldcustomer->getId();
                }
                else {
                    $oldcustomera = "None";
                    $oldcustomerid = null;
                }
                $repositoryPartenaire = $em->getRepository('maindbBundle:Partenaire');
                $oldpartenaire = $repositoryPartenaire->findOneBy(Array('id' => $taskToEdit->getPartenaireId()));
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
                            'tasks' => $tachestodisplay,
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
                            'error' => '',
                            'natures' => $natures,
                            'activites2' => $activites2,
                            'activites' => $activites,
                            'label' => $request->get('fname'),
                            'date' => $request->get('date'),
                            'natureselected' => $request->get('nature'),
                            'times' => $times,
                            'clients' => $clients,
                            'clientsa' => json_encode(SimpleTaskControllerFunctions::getArrayClientToDisplay($em)),
                            'produits' => $produits,
                            'versions' => $versions,
                            'plateformes' => $plateformes,
                            'partenaires' => $partenaires,
                            'erreurtemps' => '',
                            'natureedit' => $taskToEdit->getNature(),
                            'tacheedit' => $taskToEdit,
                            'produitsavecversions' => json_encode(SimpleTaskControllerFunctions::getArrayProduitsToDisplay($em)),
                            'oldprod' => $oldprod,
                            'oldvers' => $oldvers,
                            'oldplat' => $oldplat,
                            'oldcomp' => $oldcomp,
                            'oldpartenaire' => $oldpartenairea,
                            'oldpartenaireid' => $oldpartenaireid,
                            'oldcustomer' => $oldcustomera,
                            'oldcustomerid' => $oldcustomerid,
                            'edit' => 1,
                            'disableclients' => $disableclients,
                            'disableproduits' => $disableproduits,
                            'tachestodisplay' => $tachestodisplay));
            }
            else {
                return $this->render('maindbBundle:Default:errorpermission.html.twig');
            }
        }
    }

}
