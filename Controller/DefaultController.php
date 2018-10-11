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

        $this->view->valid = "valid: " . ($lic->isValid() ? "yes" : "no");
        
        $this->view->msg = "<br />status message: " . (is_null($lic->getStatusMessage()) ? "null" : $lic->getStatusMessage())
                . "<br />licenseData: " . (is_null($lic->getLicenseData()) ? "null" : json_encode($lic->getLicenseData()))
                ;
    }
    
}
