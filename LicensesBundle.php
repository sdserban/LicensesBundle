<?php

namespace LicensesBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class LicensesBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/licenses/js/pimcore/startup.js'
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAdminIframePath()
    {
        return '/licenses/toto';
    }
}
