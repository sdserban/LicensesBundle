<?php

namespace LicensesBundle\Controller;

use Pimcore\Controller\FrontendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pimcore\Model\DataObject;

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
     * @Route("/licenses/toto")
     */
    public function totoAction(Request $request)
    {
        return $this->json(["data" => "value"]);
    }
    
}
