<?php

namespace main\dbBundle\Controller;

use main\dbBundle\Func\FileFunctions;
use main\dbBundle\Func\RequestFunctions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use main\dbBundle\Func\RenderPageFunctions\RenderPageExportFunctions;

/*
 * Class To manage export
 */

class ExportActionController extends Controller {
    
    /**
     * 
     * @param Request $request
     * @return Integer
     */
    public function gestionExportAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $session = $this->getRequest()->getSession();
        if ($request->get('naturesexport') && $request->get('endate') && $request->get('startdate') && $request->get('exportType')) {
            $arrayToExport = Array();
            $allnature = RequestFunctions::getAllNature($request);
            if ($request->get('exportType') == 'all' && $allnature == 1) {
                array_push($arrayToExport, RequestFunctions::getAllTasksFromAllUsers($request));
            }elseif($request->get('exportType') == 'all') {
                array_push($arrayToExport, RequestFunctions::getAllTaskSelectedNaturesAllUsers($request));
            }elseif($request->get('exportType')=='users' && $allnature == 1){  
                $arrayToExport = RequestFunctions::getAllTaskAllNatureUsersDefined($request);
            }elseif($request->get('exportType')=='users'){
                $arrayToExport = RequestFunctions::getAllTaskNaturesDefinedUsersDefined($request);
            }elseif($request->get('exportType')=='services' && $allnature == 1){
                $arrayToExport = RequestFunctions::getAllTasksServicesDefinedAllNatures($request);
            }elseif($request->get('exportType')=='services'){
                $arrayToExport = RequestFunctions::getAllTasksServicesDefinedNaturesDefined($request);
            }
            if (!empty($arrayToExport[0][0])){
                $filename = FileFunctions::functionName();
                FileFunctions::writeToCsvFile($arrayToExport, $filename);
                FileFunctions::downloadCsvFile($filename);
                FileFunctions::removeFile($filename);
                return 1;
            }else{
                return -2;
            }
        }
        elseif (!$request->get('endate') && !$request->get('startdate')){
            return -1;
        }
    }

}
