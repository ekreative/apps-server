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

    public function __construct()
    {

        $this->setToken(sha1(uniqid(mt_rand(), true)));
    }

//    /**
//     * @var string
//     */
//    private $file;
//
//    public function updateFile() {
//        $this->file = $this->getUploadedFile()->getClientOriginalName();
//    }



    public function upload() {
        // the file property can be empty if the field is not required
        if (null === $this->getUploadedFile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues
        // move takes the target directory and then the
        // target filename to move to

        $this->getUploadedFile()->move( $this->getUploadRootDirLocal(), $this->getToken().'.ipa');

        copy($this->getUploadRootDirLocal() . $this->getToken().'.ipa', $this->getAbsolutePath(), stream_context_create(array(
            's3' => array(
                'ACL' => 'public-read'
            )
        )));

        unlink($this->getUploadRootDirLocal() . $this->getToken().'.ipa');
        $this->uploadPlist();


//        $this->setFile($this->getUploadedFile()->getClientOriginalName());
//
//        // set the path property to the filename where you've saved the file
//        $this->path = $this->getUploadedFile()->getClientOriginalName();
//
//        // clean up the file property as you won't need it anymore
//        $this->file = null;
    }

    private function uploadPlist(){
        $plistString = Helper::getPlistString($this->getWebAbsolutePath(), $this->getBundleIdentifier(), $this->getVersion(), $this->getFolder()->getName());
        file_put_contents($this->getUploadRootDirLocal() . $this->getToken() . '.plist', $plistString);
        copy($this->getUploadRootDirLocal() . $this->getToken() . '.plist', $this->getAbsolutePathPlist(), stream_context_create(array(
            's3' => array(
                'ACL' => 'public-read'
            )
        )));
        unlink($this->getUploadRootDirLocal() . $this->getToken() . '.plist');

    }

    protected function getUploadRootDirLocal() {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web/iosapps/';
    }

    protected function getUploadRootDir() {
        // the absolute directory path where uploaded
        // documents should be saved
        return 's3://' . EkreativeAppsBundle::S3_BUCKET . '/' . $this->getUploadDir();
    }

    protected function getUploadRootDirPlist() {
        // the absolute directory path where uploaded
        // documents should be saved
        return 's3://' . EkreativeAppsBundle::S3_BUCKET . '/' . $this->getUploadDirPlist();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/ipa';
    }
    protected function getUploadDirPlist()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/plist';
    }

    public function getWebAbsolutePath()
    {
        return EkreativeAppsBundle::S3_WEB_PATH . '/' . $this->getUploadDir() . '/' . $this->getToken() . '.ipa';
    }

    public function getWebAbsolutePathPlist()
    {
        return EkreativeAppsBundle::S3_WEB_PATH . '/' . $this->getUploadDirPlist() . '/' . $this->getToken() . '.plist';
    }

    public function getAbsolutePath()
    {
        return null === $this->getToken()
            ? null
            : $this->getUploadRootDir().'/'.$this->getToken() . '.ipa';
    }

    public function getAbsolutePathPlist()
    {
        return null === $this->getToken()
            ? null
            : $this->getUploadRootDirPlist().'/'.$this->getToken() . '.plist';
    }

    public function removeUpload()
    {
        if ($ipa = $this->getAbsolutePath()) {
            unlink($ipa);
        }
        if ($plist = $this->getAbsolutePathPlist()) {
            unlink($plist);
        }
    }

}
