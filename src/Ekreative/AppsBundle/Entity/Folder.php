<?php

namespace Ekreative\AppsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * folder
 */
class Folder {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return folder
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return folder
     */
    public function setDate($date) {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $app;

    /**
     * Constructor
     */
    public function __construct() {
        $this->app = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add app
     *
     * @param \Ekreative\AppsBundle\Entity\App $app
     * @return Folder
     */
    public function addApp(\Ekreative\AppsBundle\Entity\App $app) {
        $this->app[] = $app;

        return $this;
    }

    /**
     * Remove app
     *
     * @param \Ekreative\AppsBundle\Entity\App $app
     */
    public function removeApp(\Ekreative\AppsBundle\Entity\App $app) {
        $this->app->removeElement($app);
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
