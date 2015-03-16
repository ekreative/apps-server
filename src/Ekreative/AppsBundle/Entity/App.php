<?php

namespace Ekreative\AppsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * app
 */
class App {

    private $qrcode;
    /**
     * @var integer
     */
    private $id;
    /**
     * @var string
     */
    private $file;
    /**
     * @var string
     */
    private $version;
    /**
     * @var string
     */
    private $comment = null;
    /**
     * @var \DateTime
     */
    private $date;
    private $uploadedFile;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * Set file
     *
     * @param string $file
     * @return app
     */
    public function setFile($file) {
        $this->file = $file;

        return $this;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * Set version
     *
     * @param string $version
     * @return app
     */
    public function setVersion($version) {
        $this->version = $version;

        return $this;
    }

    public function setAlternativeComment($comment) {
       if($this->getComment()===null){
           $this->comment = $comment;
       }
    }
    
    /**
     * Get comment
     *
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return app
     */
    public function setComment($comment) {
        $this->comment = $comment;

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
     * Set date
     *
     * @param \DateTime $date
     * @return app
     */
    public function setDate($date) {
        $this->date = $date;

        return $this;
    }

    public function getQrcode() {
        return $this->qrcode;
    }

    public function setQrcode($qrcode) {
        $this->qrcode = $qrcode;
    }
    
    public function updateFile() {
      $this->file = $this->getUploadedFile()->getClientOriginalName();
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */

    public function getUploadedFile() {
        return $this->uploadedFile;
    }

    public function setUploadedFile(\Symfony\Component\HttpFoundation\File\UploadedFile  $uploadedFile) {
        $this->uploadedFile = $uploadedFile;

    }
    
}
