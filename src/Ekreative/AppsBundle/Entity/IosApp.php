<?php

namespace Ekreative\AppsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ekreative\AppsBundle\EkreativeAppsBundle;
use Ekreative\AppsBundle\Entity\App as App;
use Ekreative\AppsBundle\Helper\Helper;

/**
 * app
 */
class IosApp extends App {

    /**
     * @var \Ekreative\AppsBundle\Entity\IosFolder
     */
    private $folder;

    /**
     * Set folder
     *
     * @param \Ekreative\AppsBundle\Entity\IosFolder $folder
     * @return App
     */
    public function setFolder(\Ekreative\AppsBundle\Entity\IosFolder $folder = null) {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get folder
     *
     * @return \Ekreative\AppsBundle\Entity\IosFolder
     */
    public function getFolder() {
        return $this->folder;
    }

    /**
     * @var string
     */
    private $token;

    /**
     * Set token
     *
     * @param string $token
     * @return IosApp
     */
    public function setToken($token) {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * @var string
     */
    private $bundleIdentifier;

    /**
     * Set bundleIdentifier
     *
     * @param string $bundleIdentifier
     * @return IosApp
     */
    public function setBundleIdentifier($bundleIdentifier) {
        $this->bundleIdentifier = $bundleIdentifier;

        return $this;
    }

    /**
     * Get bundleIdentifier
     *
     * @return string
     */
    public function getBundleIdentifier() {
        return $this->bundleIdentifier;
    }

    public function __construct() {
        $this->setToken(md5(time()));
    }

    public function getFilename() {

        $identifier = $this->getBundleIdentifier();
        $version = $this->getVersion();
        $folder = $this->getFolder()->getName();
        return $folder . '-' . $version . '-' . $identifier.'.ipa';
    }
    
    
    public function getS3name() {
        $folder = $this->getFolder()->getId();
        return $folder . '/' . $this->getToken() . '.ipa';
    }
    
    public function getS3Plistname() {
        $folder = $this->getFolder()->getId();
        return $folder . '/' .$this->getToken(). '.plist';
    }

}
