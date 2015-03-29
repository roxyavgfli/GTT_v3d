<?php

namespace main\dbBundle\Func;

use main\dbBundle\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use main\dbBundle\Entity;
use main\dbBundle\Entity\Composants;
use main\dbBundle\Entity\Partenaire;

class GlobalFunctions extends Controller {

    /**
     * The array with natures
     * @var Array
     */
    static private $natures = array(
        "Absence",
        "Internal",
        "Product",
        "Pre Sale",
        "Project"
    );

    /**
     * Return Natures
     * @return Array Natures
     */
    static function getNature() {
        return GlobalFunctions::$natures;
    }

    static function getToday() {
        return date("Y/m/d");
    }

    static function getDateOneMonthAgo() {
        return date("Y/m/d", strtotime("-1 month"));
    }

    /**
     * Action to check if session and permissions are OK to do action
     * @param String $permissionToCheck String which must be "user", "team leader", "administrator"
     * @param Session $session the session of the user
     * @param EntityManager $em Entity Manager
     * @return void
     */
    static public function SessionCheck($permissionToCheck, $session, $em) {
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        if ($session->has('login')) {
            $login = $session->get('login');
            $usermail = $login->getMail();
            $password = $login->getPassword();
            $roles = $login->getPermission();
            $user = $repository->findOneBy(array('mail' => $usermail, 'mdp' => $password));
            return GlobalFunctions::testPermissions($user, $permissionToCheck, $roles, $em);
        }
        else {
            return -1;
        }
    }

    /**
     * Used to get an array with permission of the user with openned session
     * @param Session $session the session of the user
     * @return Array Permissions of the user
     */
    static public function getUserRoles($session) {
        return $session->get('login')->getPermission();
    }

    /**
     * Used to know if an user is in team or not
     * @param Utilisateur $user The user
     * @return Boolean true if user in team false else
     */
    static public function isUserInTeam($user) {
        return (!($user->getEquipeId() == 0 || $user->getEquipeId() == NULL));
    }

    /**
     * Used to get the Utilisateur object that owns the session
     * @param Session $session the session of the user
     * @param EntityManager $em Entity Manager
     * @return Utilisateur $user
     */
    static public function getCurrentUser($session, $em) {
        $login = $session->get('login');
        $usermail = $login->getMail();
        $password = $login->getPassword();
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        return ($repository->findOneBy(array('mail' => $usermail, 'mdp' => $password)));
    }

    /**
     * Used to get an array with all users
     * @param EntityManager $em Entity Manager
     * @return Array<Utilisateurs> 
     */
    static public function getAllUsers($em) {
        $repository = $em->getRepository('maindbBundle:Utilisateur');
        return $repository->findAll();
    }

    /**
     * Used to get an array with all users
     * @param EntityManager $em Entity Manager
     * @return Array<Service>
     */
    static public function getAllServices($em) {
        $repository = $em->getRepository('maindbBundle:Service');
        return $repository->findAll();
    }

    /**
     * Used to test if $user has permission
     * @param Utilisateur $user the user to check permissions
     * @param String $permissionToCheck the String that must be "user", "team leader", "administrator"
     * @param Array<Roles> $roles the roles to check 
     * @param EntityManager $em Entity Manager
     * @return Integer -2 if $permissionToCheck not in $roles
     */
    static function testPermissions($user, $permissionToCheck, $roles, $em) {
        if ($user) {
            $permissions = GlobalFunctions::getPermissionUser($user, $em);
            $isallowed = GlobalFunctions::testIncludePermission($permissions, $permissionToCheck, $roles);
            if ($isallowed == 0) {
                return -2;
            }
        }
    }

    /**
     * Used to test if permission in permissions
     * @param Array<String, String> $permissions the array of <nom, permission>
     * @param String $permissionToCheck the String to test
     * @return Integer 1 if $permissionToCheck and 0 else
     */
    static function testIncludePermission($permissions, $permissionToCheck) {
        foreach ($permissions as $permission) {
            if ($permission['nom'] == $permissionToCheck) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * used to get roles from an user
     * @param Utilisateur $user given user to get permission (roles)
     * @param EntityManager $em Entity Manager
     * @return Array<String, String> $roles Array that contains different roles from an user
     */
    static function getPermissionUser($user, $em) {
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

    /**
     * Function used to generate an array of dates betwin $strDateFrom and $strDateTo
     * @param String $strDateFrom First date
     * @param String $strDateTo Last date
     * @return Array() array with dates
     */
    static function createDateRangeArray($strDateFrom, $strDateTo) {
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

    /**
     * Function to know if date is week end or not
     * @param String $date the date
     * @return Boolean
     */
    function isWeekend($date) {
        return (date('N', strtotime($date)) >= 6);
    }

    /**
     * Function used to return array with content of $reponame
     * @param EntityManager $em The entity manager
     * @param String $reponame The name of the repository
     * @return Array Content of $reponame
     */
    static function getFromRepository($em, $reponame) {
        $repository = 'maindbBundle:' . $reponame;
        if ($reponame == 'Version') {
            $query = $em->createQuery(
                    'SELECT elem
            FROM ' . $repository . ' elem
            WHERE elem.id != 0 AND elem.id != 1 AND elem.actif = 1
            ORDER BY elem.numero'
            );
            return ($query->getArrayResult());
        }
        else {
            $query = $em->createQuery(
                    'SELECT elem
            FROM ' . $repository . ' elem
            WHERE elem.id != 0 AND elem.id != 1 AND elem.actif = 1
            ORDER BY elem.nom'
            );
            return ($query->getArrayResult());
        }
    }

    /**
     * Function used to update via /update
     * @return String message
     */
    function update() {
        $em = $this->getDoctrine()->getEntityManager();
        $entityArray = ['Composants', 'Client', 'Equipe', 'Partenaire', 'Plateforme', 'Produit', 'Service', 'Societe', 'Version'];
        GlobalFunctions::updateUsersEquipe($em);
        foreach ($entityArray as $entity) {
            if (!GlobalFunctions::entityNoneExists($em, $entity)) {
                GlobalFunctions::updateEntity($em, $entity);
            }
        }
        GlobalFunctions::updateLinksOfProductsForNone($em);
        GlobalFunctions::updateTasks($em);
        return ("Update done");
    }

    /**
     * Function to set teams for users with none by default
     * @param EntityManager $em
     */
    static function updateUsersEquipe($em) {
        $users = $em->getRepository('maindbBundle:Utilisateur')->findAll();
        foreach ($users as $user) {
            if ($user->getEquipeId() == NULL || $user->getEquipeId() == 0) {
                $user->setEquipeId(1);
                $em->persist($user);
                $em->flush();
            }
        }
    }

    /**
     * Function used to Link Products to none 
     * @param EntityManager $em
     */
    static function updateLinksOfProductsForNone($em) {
        $arrayOfEntity = ['ProduitComposant', 'ProduitPlateforme', 'ProduitVersion'];
        foreach ($arrayOfEntity as $entity) {
            if (!GlobalFunctions::entityNoneLinkProductExists($em, $entity)) {
                GlobalFunctions::setOneLinkForNone($em, $entity);
            }
        }
    }
    
    /**
     * Function used to know if Link none exists for product
     * @param EntityManager $em The entity manager
     * @param String $entity The entity name
     * @return Boolean
     */
    static function entityNoneLinkProductExists($em, $entity) {
        $halfname = strtolower(str_replace('Produit', '', $entity));
        GlobalFunctions::removeAnnoyingValue($em, $entity);
        $test = $em->getRepository('maindbBundle:' . $entity)->findOneBy(Array('id' => 1, 'produitId' => 1, $halfname.'Id' => 1));
        return (!empty($test));
    }
    
    /**
     * Function used to remove a value that needs to be defined
     * @param EntityManager $em The entity manager
     * @param String $entity The entity 
     */
    static function removeAnnoyingValue($em, $entity){
        $halfname = strtolower(str_replace('Produit', '', $entity));
        $test = $em->getRepository('maindbBundle:' . $entity)->findOneBy(Array('produitId' => 1, $halfname.'Id' => 1));
        if ($test){
            $em->remove($test);
            $em->flush();
        }
    }

    /**
     * Function used to set links of product to none
     * @param EntityManager $em The entity manager
     * @param String $entity The entity name
     */
    static function setOneLinkForNone($em, $entity) {
        $backup = $em->getRepository('maindbBundle:' . $entity)->findAll();
        foreach (array_reverse($backup) as $element) {
            $element->setId($element->getId() + 1);
            $em->persist($element);
            $metadata = $em->getClassMetaData(get_class($element));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $em->flush();
        }
        GlobalFunctions::setProductLink($em, $entity);
    }

    /**
     * Function used to do the manipulation in order to link product none
     * @param EntityManager $em The entity manager
     * @param String $entity The entity name
     */
    static function setProductLink($em, $entity) {
        if ($entity == 'ProduitComposant') {
            $entityNew = new Entity\ProduitComposant();
            $entityNew->setComposantId(1);
            $entityNew->setId(1);
            $entityNew->setProduitId(1);
            $em->persist($entityNew);
            $metadata = $em->getClassMetaData(get_class($entityNew));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $em->flush();
        }
        elseif ($entity == 'ProduitPlateforme') {
            $entityNew = new Entity\ProduitPlateforme();
            $entityNew->setPlateformeId(1);
            $entityNew->setId(1);
            $entityNew->setProduitId(1);
            $em->persist($entityNew);
            $metadata = $em->getClassMetaData(get_class($entityNew));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $em->flush();
        }
        elseif ($entity == 'ProduitVersion') {
            $entityNew = new Entity\ProduitVersion();
            $entityNew->setId(1);
            $entityNew->setProduitId(1);
            $entityNew->setVersionId(1);
            $em->persist($entityNew);
            $metadata = $em->getClassMetaData(get_class($entityNew));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $em->flush();
        }
    }

    /**
     * Function to set entities with the 1 id reserved for 'none'
     * @param EntityManager $em The Entity Manager
     * @param String $entity The entity 
     */
    static function updateEntity($em, $entity) {
        $backup = $em->getRepository('maindbBundle:' . $entity)->findAll();
        foreach (array_reverse($backup) as $element) {
            $element->setIdWithDependency($element->getId() + 1, $em);
            $em->persist($element);
            $metadata = $em->getClassMetaData(get_class($element));
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $em->flush();
        }
        GlobalFunctions::setUpEntity($em, $entity);
    }

    /**
     * Function used to know if the entity countains the none
     * @param EntityManager $em The entityManager
     * @param String $entity The entity
     * @return Boolean true if none in entity exists false else
     */
    static function entityNoneExists($em, $entity) {
        if ($entity != 'Version') {
            $test = $em->getRepository('maindbBundle:' . $entity)->findOneBy(Array('id' => 1, 'nom' => 'none'));
            return (!empty($test));
        }
        else {
            $test = $em->getRepository('maindbBundle:' . $entity)->findOneBy(Array('id' => 1, 'numero' => 'none'));
            return (!empty($test));
        }
    }

    /**
     * Function used to set entities 
     * @param EntityManager $em The Entity Manager
     * @param String $entity The name of entity
     */
    static function setUpEntity($em, $entity) {
        if ((!($em->getRepository('maindbBundle:' . $entity)->findOneBy(array('id' => 1))))) {
            if ($entity == 'Composants') {
                $entityNew = new Composants();
                $entityNew->setActif(1);
                $entityNew->setNom('none');
                $entityNew->setId(1);
                $em->persist($entityNew);
                $metadata = $em->getClassMetaData(get_class($entityNew));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
            }
            elseif ($entity == 'Client') {
                $entityNew = new Entity\Client();
                $entityNew->setActif(1);
                $entityNew->setNom('none');
                $entityNew->setId(1);
                $entityNew->setPartenaireId(1);
                $em->persist($entityNew);
                $metadata = $em->getClassMetaData(get_class($entityNew));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
            }
            elseif ($entity == 'Partenaire') {
                $entityNew = new Partenaire();
                $entityNew->setActif(1);
                $entityNew->setNom('none');
                $entityNew->setId(1);
                $em->persist($entityNew);
                $metadata = $em->getClassMetaData(get_class($entityNew));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
            }
            elseif ($entity == 'Plateforme') {
                $entityNew = new Entity\Plateforme();
                $entityNew->setActif(1);
                $entityNew->setNom('none');
                $entityNew->setId(1);
                $em->persist($entityNew);
                $metadata = $em->getClassMetaData(get_class($entityNew));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
            }
            elseif ($entity == 'Produit') {
                $entityNew = new Entity\Produit();
                $entityNew->setActif(1);
                $entityNew->setNom('none');
                $entityNew->setId(1);
                $em->persist($entityNew);
                $metadata = $em->getClassMetaData(get_class($entityNew));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
            }
            elseif ($entity == 'Societe') {
                $entityNew = new Entity\Societe();
                $entityNew->setActif(1);
                $entityNew->setNom('none');
                $entityNew->setId(1);
                $em->persist($entityNew);
                $metadata = $em->getClassMetaData(get_class($entityNew));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
            }
            elseif ($entity == 'Version') {
                $entityNew = new Entity\Version();
                $entityNew->setActif(1);
                $entityNew->setNumero('none');
                $entityNew->setId(1);
                $em->persist($entityNew);
                $metadata = $em->getClassMetaData(get_class($entityNew));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
            }
            elseif ($entity == 'Service') {
                $entityNew = new Entity\Service();
                $entityNew->setActif(1);
                $entityNew->setNom('none');
                $entityNew->setId(1);
                $entityNew->setSocieteId(1);
                $em->persist($entityNew);
                $metadata = $em->getClassMetaData(get_class($entityNew));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
            }
            elseif ($entity == 'Equipe') {
                $entityNew = new Entity\Equipe();
                $entityNew->setActif(1);
                $entityNew->setNom('none');
                $entityNew->setId(1);
                $entityNew->setServiceId(1);
                $em->persist($entityNew);
                $metadata = $em->getClassMetaData(get_class($entityNew));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
            }
        }
    }
    
    /**
     * Function used to set correctly the id in the tasks reported
     * @param EntityManager $em The entity manager
     */
    static function updateTasks($em){
        $tasks = $em->getRepository('maindbBundle:Tachesimple')->findAll();
        foreach ($tasks as $task){
            GlobalFunctions::updateOneTask($em, $task);
        }
    }
    
    /**
     * Function used to set task's values null to the good value 1
     * @param EntityManager $em The entity manager
     * @param Tachesimple $task The task to modify
     */
    static function updateOneTask($em, $task){
        if ($task->getClientId()== NULL || $task->getClientId()== 0){
            $task->setClientId(1);
        }
        if ($task->getComposantId()== NULL || $task->getComposantId()== 0){
            $task->setComposantId(1);
        }
        if ($task->getPartenaireId()== NULL || $task->getPartenaireId() == 0){
            $task->setPartenaireId(1);
        }
        if ($task->getPlateformeId()== NULL || $task->getPlateformeId() == 0){
            $task->setPlateformeId(1);
        }
        if ($task->getProduitId()==NULL || $task->getProduitId() == 0){
            $task->setProduitId(1);
        }
        if ($task->getVersionId()==NULL || $task->getVersionId() == 0){
            $task->setVersionId(1);
        }
        if ($task->getProduitId()==NULL || $task->getProduitId() == 0){
            $task->setProduitId(1);
        }
        /*$task->setClientId(1);
        $task->setComposantId(1);
        $task->setPartenaireId(1);
        $task->setPlateformeId(1);
        $task->setProduitId(1);
        $task->setVersionId(1);
        $task->setProduitId(1);
        $em->persist($task);
        $em->flush();*/
    }

}
