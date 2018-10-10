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
    private $status;

    
    private $error;

    /*
     * structure for $licenseData
     * 
     * string licenseId  : if ask for demo this will be only real licenseId
     * array data
     *      [
     *          int clientId  : 0 = Demo
     *          string clientName
     *          string installationId
     *          timestamp validToDate
     *          array modules
     *              [
     *                  array(int moduleId, string mudleName
     *                  ...
     *              ]
     *      ]
     * 
     * 
     */
    
    public function __construct() {
        $this->licenseFile = LICENSE_FILE;
        $this->localDataFile = LOCAL_DATA_FILE;
        $this->baseURL = BASE_URL;
        $this->moduleId = MODULE_ID;
        $this->licenseId = null;
        $this->licenseData = null;
        $this->status = null;
        $this->statusMessage = null;
        
        if(file_exists($this->licenseFile)) {
            if(!is_writable($this->licenseFile)) {
                die('Error: License file is not writable!');
            }
            $tmpObj=json_decode(file_get_contents($this->licenseFile));
            if(!is_null($tmpObj) && property_exists($tmpObj, 'licenseId')) {
                if(!is_null($tmpObj) && (strlen($tmpObj->licenseId) == LICENSE_LEN)) {
                    $this->licenseId = $tmpObj->licenseId;
                } else {
                    die("Error: Wrong data in license file!");
                }
            } else {
                die("Error: Wrong data in license file!");
            }
            if(file_exists($this->localDataFile)) {
                if(!is_writable($this->localDataFile)) {
                    die('Error: License file is not writable!');
                }
                $tmpObj=json_decode(file_get_contents($this->localDataFile));
                if(!is_null($tmpObj) && property_exists($tmpObj, $this->licenseId)) {
                    $tmpStr = $this->licenseId;
                    $this->licenseData = json_decode($this->encrypt_decrypt('decrypt', $tmpObj->$tmpStr));
                }
                if(!is_null($this->licenseData) && property_exists($this->licenseData, 'licenseId')) {
                    if($this->licenseData->licenseId != $this->licenseId) {
                        die("Error: Wrong data in license file!");
                    } else {
                        // check all licenses properties if not all get remote data
                        //if / valid ok
                        //else if expired or invalid  get remote data
                    }
                } else {
                    // get remote data
                }
            }
        } else {
            $this->status = false;
            $this->statusMessage = 'no license yet';
        }
    }
    
    public function getLicenseId() {
        return $this->licenseId;
    }
    
    public function setLicenseId($licenseId) {
        if(is_string($licenseId)) {
            $licenseId = strtolower(trim($licenseId));
            if(strlen($licenseId) === LICENSE_LEN){
                file_put_contents($this->licenseFile, json_encode(array('licenseId' => $this->licenseId))) or die("Error: Can't save license in license file!");
            }
        }
    }
    
    public function getLicenseData() {
        return $this->licenseData;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getStatusMessage() {
        return $this->statusMessage;
    }
    
    private function isSetted() {
        if(file_exists($this->licenseFile)) {
            if(!is_writable($this->licenseFile)) {
                die('Error: License file is not writable!');
            }
            $tmpObj=json_decode(file_get_contents($this->licenseFile));
            if(!is_null($tmpObj) && property_exists($tmpObj, 'licenseId')) {
                if(!is_null($tmpObj) && (strlen($tmpObj->licenseId) == LICENSE_LEN)) {
                    $this->licenseId = $tmpObj->licenseId;
                } else {
                    die("Error: Wrong data in license file!");
                }
            } else {
                die("Error: Wrong data in license file!");
            }
            return true;
        } else {
            return false;
        }
    }
    
    private function loadLicenseData() {
        return true;
    }
    
    private function loadLicenseRemoteData() {
        return true;
    }
    
    private function saveLicenseData() {
        
    }
    
    public function encrypt_decrypt($action, $string) {
        //BEGIN DEBUG
        return $string;
        //END DEBUG
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
    
    
    private function getInstalationId() {
        $installationId = md5(php_uname() . $_SERVER['DOCUMENT_ROOT']);
        return $installationId;
    }
    
    /**
     * method to setup base URL for API call
     * 
     * @param string $url: valid URL
     *
     * @return true/false if succeed/failed to setup base URL
     *  in case of fail an error message is set
     *  error details are available with method getError()
     */
    public function setBaseURL ($url) {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $this->baseURL = $url;
            $this->clearError();
            return true;
        } else {
            $this->setError(1011, "No valid URL provided for setBaseURL()");
            return false;
        }
    }
    
    private function getURL() {
        if(is_null($this->baseURL)) {
            $this->setError(1031, "Base URL is not set");
            return false;
        }
        if(is_null($this->licenseId)) {
            $this->setError(1032, "Licence id is not set");
            return false;
        }
        $this->clearError();
        $key = $this->licenseId . $this->getInstalationId();
        return $key;
    }
    
    /**
     * method to register filename which store license data and create file if needed
     * 
     * @param string $fileName: file name
     *
     * @return true/false if file exist and has write rigths or if file does not exist but was succesfully created
     *  in case of fail an error message is set
     *  error details are available with method getError()
     */
    public function setFile($fileName) {
        if(file_exists($fileName)) {
            if(is_writeable($fileName)) {
                $this->localDataFile = $fileName;
                $this->clearError();
                return true;
            } else {
                $this->setError(1041, "License file is not writable");
                return false;
            }
        } else {
            $fp = fopen($fileName, "w");
            if (!$fp) {
                $this->setError(1042, "Error to create license file");
                return false;
            }
            fclose($fp);
            $this->localDataFile = $fileName;
            $this->clearError();
            return true;
        }
    }
    
    private function setError($id, $msg) {
        $this->error = "Error LM" . $id . ": " . $msg;
    }
    
    private function clearError() {
        $this->error = null;
    }
    
    /**
     * method to read error message for the last called method
     * 
     * @param no parameters
     *
     * @return string
     */
    public function getError() {
        return (is_null($this->error) ? "" : $this->error);
    }
    
    private function clearData() {
        $this->licenseData = null;
    }
    
    /**
     * method to record first time or to reset license in license file
     * 
     * @param string $license: license ID
     *
     * @return true/false if license initialization succeed or not
     *  in case of fail an error message is set
     *  error details are available with method getError()
     */
    public function resetLicense($license) {
        if(strcmp($license, $this->askForDemoToken()) == 0) {
            $this->setError(1051, "Token to ask for demo license can't be initialized");
            return false;
        }
        if(is_null($this->localDataFile)) {
            $this->setError(1052, "License file is not declared");
            return false;
        }
        $fp = fopen($this->localDataFile, "r");
        if(!fp) {
            $this->setError(1053, "License file can't be read");
            return false;
        }
        $lines = array();
        while (!feof($fp)) {
            $line = rtrim(fgets($fp));
            if(strlen($line) >= LICENSE_LEN) {
                $licenseId = substr($line, 0, LICENSE_LEN);
                if(strcmp($license, $licenseId) != 0) {
                    array_push($lines, $line);
                }
            }
        }
        fclose($fp);
        array_push($lines, $license);
        $fp = fopen($this->localDataFile, "w");
        if(!fp) {
            $this->setError(1054, "License file can't be written");
            return false;
        }
        foreach($lines as $line) {
            fwrite($fp, $line . PHP_EOL);
        }
        fclose($fp);
        return true;
    }
    
     public function storeLicense() {
        if(strcmp($license, $this->askForDemoToken()) == 0) {
            $this->setError(1121, "Can't store unprocessed demo license request.");
            return false;
        }
        if(is_null($this->localDataFile)) {
            $this->setError(1122, "License file is not declared");
            return false;
        }
        $fp = fopen($this->localDataFile, "r");
        if(!fp) {
            $this->setError(1123, "License file can't be read");
            return false;
        }
        if(is_null($this->licenseData)) {
            $this->setError(1125, "No license data in memory to be saved");
            return false;
        }
        $lines = array();
        while (!feof($fp)) {
            $line = rtrim(fgets($fp));
            if(strlen($line) >= LICENSE_LEN) {
                $licenseId = substr($line, 0, LICENSE_LEN);
                if(strcmp($license, $licenseId) != 0) {
                    array_push($lines, $line);
                }
            }
        }
        fclose($fp);
        $line = $this->licenseData['licenseId'] . $this->encrypt_decrypt('encrypt', json_encode($this->licenseData));
        array_push($lines, $line);
        $fp = fopen($this->localDataFile, "w");
        if(!fp) {
            $this->setError(1054, "License file can't be written");
            return false;
        }
        foreach($lines as $line) {
            fwrite($fp, $line . PHP_EOL);
        }
        fclose($fp);
        return true;
    }
    
    public function askForDemoToken() {
        $s = "";
        $s = str_pad($s, LICENSE_LEN, "0");
        return $s;
    }
    
    /**
     * method to check if a license for a module is valid now
     * 
     * @param int $module: module ID
     *
     * @return true/false if license is valid or not
     *  in case of fail, license not found or license invalid an error message is set
     *  error details are available with method getError()
     */
    public function checkLicense($licenseId, $moduleId) {
        $valid = false;
        if(is_null($moduleId) || is_null($licenseId)) {
            $this->setError(1061, "Invalid parameter in isValid()");
            return false;
        }
        $this->licenseId = $licenseId;
        $this->moduleId = $moduleId;
        if(strcmp($licenseId, $this->askForDemoToken()) != 0) {
            $this->loadLocalData();
            if(!$this->validToday()) {
                $this->loadRemoteData();
            }
        } else {
            $this->loadRemoteData();
        }
        
        return $this->isValid();
    }
    
    /*
     * structure for $licenseData
     * 
     * string licenseId  : if ask for demo this will be only real licenseId
     * array data
     *      [
     *          int clientId  : 0 = Demo
     *          string clientName
     *          string installationId
     *          timestamp validToDate
     *          array modules
     *              [
     *                  array(int moduleId, string mudleName
     *                  ...
     *              ]
     *      ]
     * 
     * 
     */
    private function isValid() {
        if(is_null($this->licenseData)) {
            $this->setError(1071, "No license data loaded");
            return false;
        }
        if(is_null($this->licenseId)) {
            $this->setError(1072, "No license ID declared");
            return false;
        }
        if(is_null($this->moduleId)) {
            $this->setError(1073, "No module ID declared");
            return false;
        }
        if($this->validToday()) {
            if(array_key_exists('modules', $this->licenseData['data'])) {
                $modules = array();
                foreach($this->licenseData['data']['modules'] as $moduleData) {
                    array_push($modules, $moduleData['moduleId']);
                }
                if(in_array($this->moduleId, $modules)) {
                    $this->error = "Valid license";
                    return true;
                } else {
                    $this->setError(1074, "Module is not licensed");
                    return false;
                }
            }
        }
        $this->setError(1075, "Wrong structure of license data");
        return false;
    }
    
    private function validToday() {
        if(is_null($this->licenseData)) {
            $this->setError(1081, "No license data loaded");
            return false;
        }
        if(array_key_exists('data', $this->licenseData)) {
            if(array_key_exists('validToDate', $this->licenseData['data'])) {
                if(time() > $this->licenseData['data']['validToDate']) {
                    $this->setError(1082, "License expired");
                    return false;
                } else {
                    $this->clearError();
                    return true;
                }
            }
        }
        $this->clearData();
        $this->setError(1083, "Wrong structure of license data");
        return false;
    }
    
    private function loadLocalData() {
        $this->clearData();
        if(is_null($this->localDataFile)) {
            $this->setError(1091, "License file is not declared");
            return false;
        }
        if(is_null($this->licenseId)) {
            $this->setError(1092, "No license ID declared");
            return false;
        }
        if(strcmp($this->licenseId, $this->askForDemoToken()) == 0) {
            $this->setError(1093, "This is only a token to demand a demo license");
            return false;
        }
        $fp = fopen($this->localDataFile, "r");
        if(!fp) {
            $this->setError(1094, "License file can't be read");
            return false;
        }
        while (!feof($fp)) {
            $line = trim(fgets($fp));
            if(strlen($line) >= LICENSE_LEN) {
                $licenseId = substr($line, 0, LICENSE_LEN);
                if(strcmp($this->licenseId, $licenseId) == 0) {
                    $data = substr($line, LICENSE_LEN);
                    if(strlen($data) == 0) {
                        $this->getError(1095, "License initialized, no local license data");
                        return false;
                    }
                    $data = $this->encrypt_decrypt('decrypt', $data);
                    $this->licenseData = json_decode($data, true);
                    if(!is_array($this->licenseData)) {
                        $line = $this->licenseId;
                    }
                    $this->clearError();
                    fclose($fp);
                    return true;
                }
            }
        }
        fclose($fp);
        $this->setError(1099, "No local record for that license");
        return false;
    }
    
    private function loadRemoteData() {
        $this->clearData();
        if(is_null($this->baseURL)) {
            $this->setError(1101, "No URL declared for API call");
            return false;
        }
        if(is_null($this->licenseId)) {
            $this->setError(1101, "No license ID declared");
            return false;
        }
        $licenseId = strtolower($this->licenseId);
        $str = php_uname();
        $suffix = "";
        $licenseId = strtolower($this->licenseId);
        if(strcmp($this->licenseId, $this->askForDemoToken()) == 0) {
            if(is_null($this->moduleId)) {
                $this->setError(1102, "No module ID declared");
                return false;
            }
            $moduleId = strtolower($this->moduleId);
            $suffix .= '&module=' . $moduleId;
        } else {
            $str .= $_SERVER['DOCUMENT_ROOT'];
        }
        $installationId = md5($str);
        $key = strtolower($licenseId . $installationId . $suffix);
        $url = $this->baseURL . "?key=" . $key;
        $response = file_get_contents($url);
        $result = json_decode($response, true);
        if(!(isset($result['status']) && isset($result['status_message']) && isset($result['data']))) {
            $this->setError(1103, "Invalid structure received from remote license server");
            return false;
        }
        if(is_null($result['data'])) {
            $this->setError(1104, "Remote message: " . $result['status'] . " " . $result['status_message']);
            return false;
        }
        $this->licenseData = $result['data'];
        $this->storeLicense();
         
        return true;
    }
    
    
    
    public function getMessage() {
        return $this->message;
    }
    
    /**
     * method to encrypt or decrypt a plain text string
     * initialization vector(IV) has to be the same when encrypting and decrypting
     * 
     * @param string $action: can be 'encrypt' or 'decrypt'
     * @param string $string: string to encrypt or decrypt
     *
     * @return string
     */
    
    public function testIt() {
        return "It's ok!";
    }
}
