<?php

namespace Ekreative\AppsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * app
 */
class Gcm {

    private $devicetoken;
    
    private $data;
    
    private $apikey;
    
    public function getDevicetoken() {
        return $this->devicetoken;
    }

    public function setDevicetoken($devicetoken) {
        $this->devicetoken = $devicetoken;
    }

    public function getData() {
        return $this->data;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function getApikey() {
        return $this->apikey;
    }

    public function setApikey($apikey) {
        $this->apikey = $apikey;
    }


    
}
