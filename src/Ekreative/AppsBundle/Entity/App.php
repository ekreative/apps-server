<?php

namespace Ekreative\AppsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * app
 */
class App {

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
    private $comment;

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
     * Get file
     *
     * @return string 
     */
    public function getFile() {
        return $this->file;
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

    /**
     * Get version
     *
     * @return string 
     */
    public function getVersion() {
        return $this->version;
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
     * Get comment
     *
     * @return string 
     */
    public function getComment() {
        return $this->comment;
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

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @var \Ekreative\AppsBundle\Entity\Folder
     */
    private $folder;

    /**
     * Set folder
     *
     * @param \Ekreative\AppsBundle\Entity\Folder $folder
     * @return App
     */
    public function setFolder(\Ekreative\AppsBundle\Entity\Folder $folder = null) {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get folder
     *
     * @return \Ekreative\AppsBundle\Entity\Folder 
     */
    public function getFolder() {
        return $this->folder;
    }

    var $qrcode;

    public function getQrcode() {
        return $this->qrcode;
    }

    public function setQrcode($qrcode) {
        $this->qrcode = $qrcode;
    }

    
    private $uploadedFile;
    
    public function getUploadedFile() {
        return $this->uploadedFile;
    }

    public function setUploadedFile(\Symfony\Component\HttpFoundation\File\UploadedFile  $uploadedFile) {
        $this->uploadedFile = $uploadedFile;

    }

    
    
    public function updateFile() {
      $this->file = $this->getUploadedFile()->getClientOriginalName();
    }



    public function upload() {
        // the file property can be empty if the field is not required
        if (null === $this->getUploadedFile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues
        // move takes the target directory and then the
        // target filename to move to
        
        $this->getUploadedFile()->move( $this->getUploadRootDir(), $this->getId().'.apk');
        
        $this->setFile($this->getUploadedFile()->getClientOriginalName());

        // set the path property to the filename where you've saved the file
        $this->path = $this->getUploadedFile()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    protected function getUploadRootDir() {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web/apps/' .$this->getFolder()->getId();
    }

    
    
    
    
    
}
