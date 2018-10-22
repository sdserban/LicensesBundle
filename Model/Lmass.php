<?php

namespace LicensesBundle\Model;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Lmass (License management application server side PDO version)
 *
 * @author Sorin Serban
 */

define("LICENSE_POSITION", 0);
define("LICENSE_LEN", 12); //mandatory even number, but preferably multiple of 4

define("INSTALATION_ID_POSITION", LICENSE_POSITION + LICENSE_LEN);
define("INSTALATION_ID_LEN", 32);

define("KEY_LEN", LICENSE_LEN + INSTALATION_ID_LEN);

define("LICENSE_FILE", "var/data/licenseFileForLicensesBundle.json");
define("LOCAL_DATA_FILE", "var/data/localData.json");
define("BASE_URL", "http://licenses.local/api.php");
define("MODULE_ID", "11");

class Lmass {
    private $licenseFile;
    private $localDataFile;
    private $baseURL;
    private $moduleId;
    private $licenseId; // fill with 0 if demand a demo license, real licenseId returned into licenseData
    private $licenseData;
    private $statusMessage;
    
    public function __construct() {
        $this->licenseFile = LICENSE_FILE;
        $this->localDataFile = LOCAL_DATA_FILE;
        $this->baseURL = BASE_URL;
        $this->moduleId = MODULE_ID;
        $this->licenseId = null;
        $this->licenseData = null;
        $this->statusMessage = null;
        
        if($this->isSetted()) {
            if(!$this->loadLicenseData()) {
                $this->loadLicenseRemoteData();
            }
            $this->saveLicenseData();
        }
    }
    
    public function getLicenseId() {
        return $this->licenseId;
    }
    
    public function setLicenseId($licenseId) {
        if(is_string($licenseId)) {
            $licenseId = strtolower(trim($licenseId));
            if(strlen($licenseId) == LICENSE_LEN){
                if(!is_null($this->licenseId)) {
                    $this->deleteLicenseData();
                }
                $this->licenseId = $licenseId;
                file_put_contents($this->licenseFile, json_encode(array('licenseId' => $this->licenseId))) or die("Error: Can't save license in license file!");
            }
        }
        return true;
    }
    
    public function deleteLicense() {
        file_put_contents($this->licenseFile, "");
        $this->deleteLicenseData();
        $this->licenseId = null;
        $this->licenseData = null;
        return true;
    }
    
    public function getLicenseData() {
        return $this->licenseData;
    }

    public function getStatusMessage() {
        return $this->statusMessage;
    }
    
    public function isValid() {
        if($this->isSetted()) {
            if(!is_null($this->licenseData)) {
                if(property_exists($this->licenseData, 'licenseId') &&
                   property_exists($this->licenseData, 'clientId') &&
                   property_exists($this->licenseData, 'clientName') &&
                   property_exists($this->licenseData, 'installationId') &&
                   property_exists($this->licenseData, 'validToDate') &&
                   property_exists($this->licenseData, 'modules') &&
                   is_array($this->licenseData->modules)) {
                    if($this->licenseId != $this->licenseData->licenseId) {
                        $this->statusMessage = "licenseId doesn't match";
                        return false;
                    }
                    $demo = ($this->licenseData->clientId == 1); // demo license
                    if($this->getInstalationId($demo) != $this->licenseData->installationId) {
                        $this->statusMessage = "installation key doesn't match";
                        return false;
                    }
                    if(time() > $this->licenseData->validToDate) {
                        $this->statusMessage = "license is expired";
                        return false;
                    }
                    $tmpArray = array();
                    foreach($this->licenseData->modules as $module) {
                        if(property_exists($module, 'moduleId')) {
                            array_push($tmpArray, $module->moduleId);
                        }
                    }
                    if(!in_array($this->moduleId, $tmpArray)) {
                        $this->statusMessage = "this module isn't part of the license";
                        return false;
                    }
                    $this->statusMessage = "license is valid";
                    return true;
                }
            } else {
                $this->statusMessage = "license data missing";
                return false;
            }
        } else {
            $this->statusMessage = "license isn't setted yet";
            return false;
        }
    }
    
    public function isSetted() {
        if(file_exists($this->licenseFile)) {
            if(!is_writable($this->licenseFile)) {
                die('Error: License file is not writable!');
            }
            $tmpObj=json_decode(file_get_contents($this->licenseFile));
            if(!is_null($tmpObj) && property_exists($tmpObj, 'licenseId')) {
                if(!is_null($tmpObj) && (strlen($tmpObj->licenseId) == LICENSE_LEN)) {
                    $this->licenseId = $tmpObj->licenseId;
                } else {
                    $this->statusMessage = "Error: Wrong data in license file!";
                    return false;
                }
            } else {
                $this->statusMessage = "Error: Wrong data in license file!";
                return false;
            }
            $this->statusMessage = "license is setted";
            return true;
        } else {
            $this->statusMessage = "license file doesn't exist";
            return false;
        }
    }
    
    public function resetData() {
        if($this->isSetted()) {
            $this->loadLicenseRemoteData();
            $this->saveLicenseData();
        }
    }
    
    public function getDemoLicense() {
        if(!$this->isSetted()) {
            $this->licenseId = str_repeat("0", LICENSE_LEN); //token for demo license request
            return $this->loadLicenseRemoteData();
        }
    }
    
    private function getInstalationId($demo = false) {
        $str = php_uname(); // unique for server
        if(is_bool($demo) && $demo) {
            $str .= $_SERVER['DOCUMENT_ROOT']; // unique for installation, not used for demo license which is license per server
        }
        $installationId = md5($str);
        return $installationId;
    }
    
    private function encrypt_decrypt($action, $string) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'This is secret key';                     //Change the string for production
        $secret_iv = 'This is secret initialization vector';    //Change the string for production
        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
    
    private function loadLicenseData() {
        if(file_exists($this->localDataFile)) {
            if(!is_writable($this->localDataFile)) {
                $this->statusMessage = "Error: License file is not writable!";
                return false;
            }
            $tmpObj=json_decode(file_get_contents($this->localDataFile));
            if(!is_null($tmpObj) && property_exists($tmpObj, $this->licenseId)) {
                $tmpStr = $this->licenseId;
                $this->licenseData = json_decode($this->encrypt_decrypt('decrypt', $tmpObj->$tmpStr));
            }
            if(!is_null($this->licenseData) && property_exists($this->licenseData, 'licenseId')) {
                if($this->licenseData->licenseId != $this->licenseId) {
                    $this->statusMessage = "Error: Wrong data in license file!";
                    return false;
                }
            } else {
                $this->statusMessage = "Error: Wrong data in license file!";
                return false;
            }
            $this->statusMessage = "local license data are loaded";
            return true;
        } else {
            $this->statusMessage = "local license data file doesn't exist yet";
            return false;
        }
    }
    
    private function loadLicenseRemoteData() {
        if(($this->licenseId == str_repeat("0", LICENSE_LEN)) || $this->isSetted()) {
            $this->statusMessage = "remote license data will be loaded";
            $lic = $this->licenseId;
            $key = $this->getInstalationId($this->licenseId == str_repeat("0", LICENSE_LEN));
            $mod = $this->moduleId;
            $url = $this->baseURL . "?lic=$lic&mod=$mod&key=$key";
            $reponse = file_get_contents($url);
            $tmpObj = json_decode($reponse);
            if(!is_null($tmpObj) && 
                    property_exists($tmpObj, 'status') && 
                    property_exists($tmpObj, 'status_message') && 
                    property_exists($tmpObj, 'data')) {
                $this->statusMessage = $tmpObj->status_message;
                if(isset($tmpObj->data) && property_exists($tmpObj->data, 'demo') && $tmpObj->data->demo) {
                    $key = $this->getInstalationId(true);
                    $url = $this->baseURL . "?lic=$lic&mod=$mod&key=$key";
                    $this->statusMessage = "remote license data will be loaded for demo license";
                    $tmpObj = json_decode(file_get_contents($url));
                    if(!is_null($tmpObj) && 
                        property_exists($tmpObj, 'status') && 
                        property_exists($tmpObj, 'status_message') && 
                        property_exists($tmpObj, 'data')) {
                        $this->statusMessage = $tmpObj->status_message;
                        $this->setLicenseId($tmpObj->data->licebseId);
                    }
                }
                if(isset($tmpObj->data) && property_exists($tmpObj->data, 'licenseId')) {
                    $this->licenseID = $tmpObj->data->licenseId;
                    $this->setLicenseId($tmpObj->data->licenseId);
                    $this->licenseData = $tmpObj->data;
                    $this->saveLicenseData();
                    $this->statusMessage = "license data loaded remote and saved local";
                    return true;
                }
            } else {
                $this->statusMessage = "invalid remote answer";
                return false;
            }
        }
        $this->statusMessage = "license isn't setted yet";
        return false;
    }
    
    private function saveLicenseData() {
        if(is_null($this->licenseData)) {
            return false;
        }
        $tmpObj = null;
        if(file_exists($this->localDataFile)) {
            if(!is_writable($this->localDataFile)) {
                die('Error: License file is not writable!');
            }
            $tmpObj=json_decode(file_get_contents($this->localDataFile));
        }
        if(!is_object($tmpObj)) {
            $tmpObj = (object)[];
        }
        $tmpStr = $this->licenseId;
        $tmpObj->$tmpStr = json_encode($this->encrypt_decrypt('encrypt', json_encode($this->licenseData)));
        file_put_contents($this->localDataFile, json_encode($tmpObj)) or die("Error: Can't save license in license file!");
        return true;
    }
    
    private function deleteLicenseData() {
        if(is_null($this->licenseId)) {
            $this->statusMessage = "license isn't setted yet";
            return false;
        }
        if(file_exists($this->localDataFile)) {
            if(!is_writable($this->localDataFile)) {
                die('Error: License file is not writable!');
            }
            $tmpObj=json_decode(file_get_contents($this->localDataFile));
        } else {
            $tmpObj = (object)[];
        }
        
        $tmpStr = $this->licenseId;
        if(!is_null($tmpObj) && property_exists($tmpObj, $tmpStr)) {
            unset($tmpObj->$tmpStr);
        }
        file_put_contents($this->localDataFile, json_encode($tmpObj)) or die("Error: Can't save license in license file!");
        $this->licenseData = null;
        return true;
    }
}
