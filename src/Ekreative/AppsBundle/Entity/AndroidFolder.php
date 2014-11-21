<?php

namespace Ekreative\AppsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Android folder
 */
class AndroidFolder extends Folder {

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $app;


    /**
     * Add app
     *
     * @param \Ekreative\AppsBundle\Entity\AndroidApp $app
     * @return Folder
     */
    public function addApp(\Ekreative\AppsBundle\Entity\AndroidApp $app) {
        $this->app[] = $app;

        return $this;
    }

    /**
     * Remove app
     *
     * @param \Ekreative\AppsBundle\Entity\AndroidApp $app
     */
    public function removeApp(\Ekreative\AppsBundle\Entity\AndroidApp $app) {
        $this->app->removeElement($app);
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->app = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Get app
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getApp() {
        return $this->app;
    }
}
