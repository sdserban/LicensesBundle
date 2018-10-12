<?php

namespace LicensesBundle\Controller;

use Pimcore\Controller\FrontendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pimcore\Model\DataObject;
use LicensesBundle\Model\Lmass;

class DefaultController extends FrontendController
{
    /**
     * @Route("/licenses")
     */
    public function indexAction(Request $request)
    {
        return new Response('Hello world from licenses');
    }
    
     /**
     * @Route("/licenses/checkit")
     */
    public function checkitAction(Request $request)
    {
        $lic = new Lmass();
        
        if(isset($_POST['licenseId']) && isset($_POST['button'])) {
            $licenseId = $_POST['licenseId'];
            $button = $_POST['button'];
            switch($button) {
                case "close":
                    die();
                    break;
                case "save & check":
                    $lic->deleteLicense();
                    $lic->setLicenseId($licenseId);
                    $lic->resetData();
                    break;
                case "reset":
                    $lic->resetData();
                    break;
                case "get demo license":
                    $lic->getDemoLicense();
                    break;
                case "delete";
                    $lic->deleteLicense();
                    break;
                default:
                    die('unrecognized button');
                    break;
            }
        }
        
        $licenseData = $lic->getLicenseData();
        $licenseId = (is_null($licenseData->licenseId) ? "": $licenseData->licenseId);
        $licenseType = (is_null($licenseData) ? "none" : (($licenseData->clientId == 1) ? "demo" : "commercial"));
        $licenseClient = ($licenseType == "commercial" ? $licenseData->clientName : "none");
        $licensedModules = array();
        if(is_object($licenseData)) {
            if(property_exists($licenseData, 'modules')) {
                if(is_array($licenseData->modules)) {
                    foreach($licenseData->modules as $module) {
                        if(property_exists($module, 'moduleName')) {
                            array_push($licensedModules, $module->moduleName);
                        }
                    }
                }
            }
        }
        
        
        $status = ($lic->isValid() ? "valid" : "invalid");
        $statusMessage = $lic->getStatusMessage();
        
        $this->view->licenseId = $licenseId;
        $this->view->licenseType = $licenseType;
        $this->view->licenseClient = $licenseClient;
        $this->view->licensedModules = ((count($licensedModules) > 0) ? implode(", ", $licensedModules) : "none");
        $this->view->status = $status;
        $this->view->statusMessage = $statusMessage;
    }
    
}
