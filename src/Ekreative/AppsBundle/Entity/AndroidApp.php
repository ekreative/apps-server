<?php

namespace Ekreative\AppsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ekreative\AppsBundle\EkreativeAppsBundle;
use Ekreative\AppsBundle\Entity\App as App;
use Ekreative\AppsBundle\Helper\Helper;

/**
 * Android app
 */
class AndroidApp extends App {

    /**
     * @var \Ekreative\AppsBundle\Entity\AndroidFolder
     */
    private $folder;

    /**
     * Set folder
     *
     * @param \Ekreative\AppsBundle\Entity\AndroidFolder $folder
     * @return App
     */
    public function setFolder(\Ekreative\AppsBundle\Entity\AndroidFolder $folder = null) {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get folder
     *
     * @return \Ekreative\AppsBundle\Entity\AndroidFolder
     */
    public function getFolder() {
        return $this->folder;
    }

    public function getFilename() {

        
        $name = [];
        $name[] = ucfirst($this->getFolder()->getName());
        $name[] = $this->getVersion();
        $name[] = $this->getDate()->format('H:i:s_d-m-Y');
        
        $name=array_filter($name);
        
        return implode('_', $name).'.apk';
    }

    public function getS3name() {
        $folder = $this->getFolder()->getId();
        return $folder . '/' . $this->getId() . '.apk';
    }

}
