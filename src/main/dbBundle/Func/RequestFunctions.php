<?php

namespace main\dbBundle\Func;

class RequestFunctions {

    /**
     * Function to get all tasks from all users between startdate and endate
     * @param \main\dbBundle\Func\Request $request
     * @return Array Tasks to put in file
     */
    function getAllTasksFromAllUsers($request) {
        $arrayToFill = array();
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery(
                'SELECT t.date, u.nom as nomuser, u.prenom, u.trigramme, soc.nom as nomsoc,  serv.nom as nomserv, eq.nom as nomeq, t.nature, c.nom as nomclient, p.nom as nompartenaire, prod.nom as nomprod, compo.nom as nomcompo, plate.nom as nomplate, vers.numero, a.nom as nomactivite, t.tempsPasse, t.nom, t.commentaire
            FROM maindbBundle:Tachesimple t, maindbBundle:Utilisateur u, maindbBundle:Activite a, maindbBundle:Client c, maindbBundle:Partenaire p, maindbBundle:Plateforme plate, maindbBundle:Produit prod, maindbBundle:Version vers, maindbBundle:Composants compo, maindbBundle:Equipe eq, maindbBundle:Service serv, maindbBundle:Societe soc
            WHERE t.date <= ?1 AND t.date >= ?2 AND t.actif = 1 AND t.userId = u.id AND t.activiteId = a.id AND t.clientId = c.id AND t.partenaireId = p.id AND t.plateformeId = plate.id AND t.produitId = prod.id AND t.versionId = vers.id AND t.composantId = compo.id AND u.equipeId = eq.id AND eq.serviceId = serv.id AND serv.societeId = soc.id
            ORDER BY u.nom'
        );
        $query->setParameter(1, $request->get('endate'));
        $query->setParameter(2, $request->get('startdate'));
        $raw = $query->getArrayResult();
        array_push($arrayToFill, $raw);
        return ($arrayToFill);
    }

    /**
     * Function to get all tasks from all users matching with given nature between startdate and endate
     * @param \main\dbBundle\Func\Request $request
     * @return Array Tasks to put in file
     */
    function getAllTaskSelectedNaturesAllUsers($request) {
        $em = $this->getDoctrine()->getEntityManager();
        $natures = $request->get('naturesexport');
        $arrayToFill = Array();
        foreach ($natures as $nature) {
            $query = $em->createQuery(
                    'SELECT t.date, u.nom as nomuser, u.prenom, u.trigramme, soc.nom as nomsoc,  serv.nom as nomserv, eq.nom as nomeq, t.nature, c.nom as nomclient, p.nom as nompartenaire, prod.nom as nomprod, compo.nom as nomcompo, plate.nom as nomplate, vers.numero, a.nom as nomactivite, t.tempsPasse, t.nom, t.commentaire
                FROM maindbBundle:Tachesimple t, maindbBundle:Utilisateur u, maindbBundle:Activite a, maindbBundle:Client c, maindbBundle:Partenaire p, maindbBundle:Plateforme plate, maindbBundle:Produit prod, maindbBundle:Version vers, maindbBundle:Composants compo, maindbBundle:Equipe eq, maindbBundle:Service serv, maindbBundle:Societe soc
                WHERE t.date <= ?1 AND t.date >= ?2 AND t.actif = 1 AND t.nature = ?3 AND t.userId = u.id AND t.activiteId = a.id AND t.clientId = c.id AND t.partenaireId = p.id AND t.plateformeId = plate.id AND t.produitId = prod.id AND t.versionId = vers.id AND t.composantId = compo.id AND u.equipeId = eq.id AND eq.serviceId = serv.id AND serv.societeId = soc.id
                ORDER BY u.nom'
            );
            $query->setParameter(1, $request->get('endate'));
            $query->setParameter(2, $request->get('startdate'));
            $query->setParameter(3, $nature);
            $raw = $query->getArrayResult();
            array_push($arrayToFill, $raw);
            return $arrayToFill;
        }
    }

    /**
     * Function to get all tasks from users given by request between startdate and endate
     * @param Request $request The request
     * @return Array Array that countains wanted tasks
     */
    function getAllTaskAllNatureUsersDefined($request) {
        $arrayToFill = array();
        $em = $this->getDoctrine()->getEntityManager();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        $users = $request->get('utilisateursexport');
        foreach ($users as $user) {
            $userDb = $repository->findOneBy(array('id' => $user));
            array_push($arrayToFill, RequestFunctions::QueryAllTaskAllNatureUsersDefined($request, $userDb, $em));
        }
        return($arrayToFill);
    }

    /**
     * Function that provide tasks for user between startdate and endate
     * @param Request $request
     * @param Utilisateur $user
     * @param EntityManager $em
     * @return Array Array that countains tasks for user $user
     */
    static function QueryAllTaskAllNatureUsersDefined($request, $user, $em) {
        $arrayToFill = array();
        $query = $em->createQuery(
                'SELECT t.date, u.nom as nomuser, u.prenom, u.trigramme, soc.nom as nomsoc,  serv.nom as nomserv, eq.nom as nomeq, t.nature, c.nom as nomclient, p.nom as nompartenaire, prod.nom as nomprod, compo.nom as nomcompo, plate.nom as nomplate, vers.numero, a.nom as nomactivite, t.tempsPasse, t.nom, t.commentaire
            FROM maindbBundle:Tachesimple t, maindbBundle:Utilisateur u, maindbBundle:Activite a, maindbBundle:Client c, maindbBundle:Partenaire p, maindbBundle:Plateforme plate, maindbBundle:Produit prod, maindbBundle:Version vers, maindbBundle:Composants compo, maindbBundle:Equipe eq, maindbBundle:Service serv, maindbBundle:Societe soc
            WHERE t.date <= ?1 AND t.date >= ?2 AND t.actif = 1 AND t.userId = ?3 AND t.userId = u.id AND t.activiteId = a.id AND t.clientId = c.id AND t.partenaireId = p.id AND t.plateformeId = plate.id AND t.produitId = prod.id AND t.versionId = vers.id AND t.composantId = compo.id AND u.equipeId = eq.id AND eq.serviceId = serv.id AND serv.societeId = soc.id
            ORDER BY u.nom'
        );
        $query->setParameter(1, $request->get('endate'));
        $query->setParameter(2, $request->get('startdate'));
        $query->setParameter(3, $user->getId());
        $raw = $query->getArrayResult();
        array_push($arrayToFill, $raw);
        return ($arrayToFill);
    }

    /**
     * Function to get all tasks for different users and different natures
     * @param Request $request The request
     * @return Array Array that countains all tasks for given users and given natures
     */
    function getAllTaskNaturesDefinedUsersDefined($request) {
        $em = $this->getDoctrine()->getEntityManager();
        $naturesToExport = $request->get('naturesexport');
        $users = $request->get('utilisateursexport');
        $arrayToFill = Array();
        foreach ($users as $user) {
            array_push($arrayToFill, RequestFunctions::getAllTaskOneNatureUserDefined($user, $naturesToExport, $request, $em));
        }
        return $arrayToFill;
    }

    /**
     * Function to get all tasks for one user and different natures
     * @param Utilisateur $user The user
     * @param Array $naturesToExport Array with natures to export
     * @param Request $request The request
     * @param EntityManager $em Entity Manager
     * @return Array Array that countains all tasks for one user with different natures
     */
    static function getAllTaskOneNatureUserDefined($user, $naturesToExport, $request, $em) {
        $arrayToFill = Array();
        foreach ($naturesToExport as $nature) {
            array_push($arrayToFill, RequestFunctions::queryAllTaskUserDefinedOneNature($user, $nature, $request, $em));
        }
        return $arrayToFill;
    }

    /**
     * Function to get all tasks for user matching with given nature
     * @param Utilisateur $user the user
     * @param String $nature the nature
     * @param Request $request the request
     * @param EntityManager $em Entity Manager
     * @return Array
     */
    static function queryAllTaskUserDefinedOneNature($user, $nature, $request, $em) {
        $query = $em->createQuery(
                'SELECT t.date, u.nom as nomuser, u.prenom, u.trigramme, soc.nom as nomsoc,  serv.nom as nomserv, eq.nom as nomeq, t.nature, c.nom as nomclient, p.nom as nompartenaire, prod.nom as nomprod, compo.nom as nomcompo, plate.nom as nomplate, vers.numero, a.nom as nomactivite, t.tempsPasse, t.nom, t.commentaire
            FROM maindbBundle:Tachesimple t, maindbBundle:Utilisateur u, maindbBundle:Activite a, maindbBundle:Client c, maindbBundle:Partenaire p, maindbBundle:Plateforme plate, maindbBundle:Produit prod, maindbBundle:Version vers, maindbBundle:Composants compo, maindbBundle:Equipe eq, maindbBundle:Service serv, maindbBundle:Societe soc
            WHERE t.date <= ?1 AND t.date >= ?2 AND t.actif = 1 AND t.userId = ?3 AND t.nature = ?4 AND t.userId = u.id AND t.activiteId = a.id AND t.clientId = c.id AND t.partenaireId = p.id AND t.plateformeId = plate.id AND t.produitId = prod.id AND t.versionId = vers.id AND t.composantId = compo.id AND u.equipeId = eq.id AND eq.serviceId = serv.id AND serv.societeId = soc.id
            ORDER BY u.nom'
        );
        $query->setParameter(1, $request->get('endate'));
        $query->setParameter(2, $request->get('startdate'));
        $query->setParameter(3, $user);
        $query->setParameter(4, $nature);
        return $query->getArrayResult();
    }

    function getAllTasksServicesDefinedAllNatures($request) {
        $em = $this->getDoctrine()->getEntityManager();
        $arrayToFill = Array();
        $services = $request->get('servicesexport');
        $users = RequestFunctions::getUsersFromServices($services, $em);
        foreach ($users as $user) {
            array_push($arrayToFill, RequestFunctions::QueryAllTaskAllNatureUsersDefined($request, $user, $em));
        }
        return $arrayToFill;
    }

    function getAllTasksServicesDefinedNaturesDefined($request) {
        $em = $this->getDoctrine()->getEntityManager();
        $arrayToFill = Array();
        $arrayToReturn = Array();
        $services = $request->get('servicesexport');
        $users = RequestFunctions::getUsersFromServices($services, $em);
        foreach ($users as $user) {
            foreach ($request->get('naturesexport') as $nature) {
                array_push($arrayToFill, RequestFunctions::queryAllTaskUserDefinedOneNature($user, $nature, $request, $em));
            }
        }
        array_push($arrayToReturn, $arrayToFill);
        return $arrayToReturn;
    }

    static function getUsersFromServices($services, $em) {
        $servicesDB = Array();
        $usersDB = Array();
        $repository = $em->getRepository('maindbBundle:Service');
        foreach ($services as $service) {
            array_push($servicesDB, $repository->findOneBy(Array("id" => $service)));
        }
        foreach ($servicesDB as $serviceDB) {
            $usersDB = array_merge($usersDB, RequestFunctions::getTeamsFromService($serviceDB, $em));
        }
        return $usersDB;
    }

    static function getTeamsFromService($service, $em) {
        $repository = $em->getRepository('maindbBundle:Equipe');
        $teamsInService = $repository->findBy(array('serviceId' => $service->getId()));
        return RequestFunctions::getUsersFromTeams($teamsInService, $em);
    }

    static function getUsersFromTeams($teams, $em) {
        $arrayToFill = array();
        $repositoryUsers = $em->getRepository('maindbBundle:Utilisateur');
        foreach ($teams as $team) {
            $utilisateurs = $repositoryUsers->findBy(array('equipeId' => $team->getId()));
            $arrayToFill = array_merge($arrayToFill, $utilisateurs);
        }
        return $arrayToFill;
    }

    /**
     * function to get allnature from request
     * @param type $request
     * @return int $allnature 0 if not all natures selected 1 if all nature selected
     */
    static function getAllNature($request) {
        $allnature = 0;
        foreach ($request->get('naturesexport') as $nature) {
            if ($nature == 'all') {
                $allnature = 1;
            }
        }
        return $allnature;
    }

}
