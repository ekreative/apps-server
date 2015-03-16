<?php

namespace Ekreative\AppsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ios folder
 */
class IosFolder extends Folder {


    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $app;


    /**
     * Add app
     *
     * @param \Ekreative\AppsBundle\Entity\IosApp $app
     * @return Folder
     */
    public function addApp(\Ekreative\AppsBundle\Entity\IosApp $app) {
        $this->app[] = $app;

        return $this;
    }

    /**
     * Remove app
     *
     * @param \Ekreative\AppsBundle\Entity\IosApp $app
     */
    public function removeApp(\Ekreative\AppsBundle\Entity\IosApp $app) {
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




    private $users;

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }





}
