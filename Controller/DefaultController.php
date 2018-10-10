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
        $licenseStatus = 'no license';
        $lic = new Lmass();
        $lic->setLicenseId('abac0f33fc21');
        
        $this->view->msg = "status: " 
                . (is_null($lic->getStatus()) ? "null" : ($lic->getStatus() ? "true" : "false")) 
                . "<br />status message: " 
                . (is_null($lic->getStatusMessage()) ? "null" : $lic->getStatusMessage());
        
        $this->view->initial_licenseId = json_encode($lic->getLicenseId());
        $this->view->initial_licenseData = json_encode($lic->getLicenseData());
//        return $this->json(["data" => "value"]);
    }
    
}
