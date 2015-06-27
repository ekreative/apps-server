<?php

namespace Ekreative\TestBuild\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * App
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ekreative\TestBuild\CoreBundle\Entity\AppRepository")
 */
class App implements \JsonSerializable
{

    const TYPE_ANDROID = 'android';
    const TYPE_IOS = 'ios';


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="buildUrl", type="string", length=255)
     */
    private $buildUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", length=255)
     */
    private $version;

    /**
     * @var string
     *
     * @ORM\Column(name="buildNumber", type="string", length=255)
     */
    private $buildNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="qrcodeUrl", type="string", length=255)
     */
    private $qrcodeUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="bundleId", type="string", length=255)
     */
    private $bundleId;

    /**
     * @var string
     *
     * @ORM\Column(name="createdName", type="string", length=255)
     */
    private $createdName;

    /**
     * @var string
     *
     * @ORM\Column(name="createdId", type="integer")
     */
    private $createdId;

    /**
     * @var string
     *
     * @ORM\Column(name="projectId", type="integer")
     */
    private $projectId;

    /**
     * @var string
     *
     * @ORM\Column(name="iconUrl", type="string", length=255)
     */
    private $iconUrl;


    private $icon;

    private $build;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255)
     */
    private $token;
    /**
     * @var string
     *
     * @ORM\Column(name="created", type="datetime")
     */

    private $created;

    function __construct()
    {
        $this->setToken(md5(time() . rand(100, 1000)));
        $this->setIconUrl('http://lorempixel.com/57/57/cats/');
    }

    /**
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param string $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }


    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }


    /**
     * @return UploadedFile
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     */
    public function setIcon(UploadedFile $icon = null)
    {
        $this->icon = $icon;
    }

    /**
     * @return UploadedFile
     */
    public function getBuild()
    {
        return $this->build;
    }

    /**
     * @param mixed $build
     */
    public function setBuild(UploadedFile $build)
    {
        $this->build = $build;
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return App
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return App
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get buildUrl
     *
     * @return string
     */
    public function getBuildUrl()
    {
        return $this->buildUrl;
    }

    /**
     * Set buildUrl
     *
     * @param string $buildUrl
     *
     * @return App
     */
    public function setBuildUrl($buildUrl)
    {
        $this->buildUrl = $buildUrl;

        return $this;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version
     *
     * @param string $version
     *
     * @return App
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get buildNumber
     *
     * @return string
     */
    public function getBuildNumber()
    {
        return $this->buildNumber;
    }

    /**
     * Set buildNumber
     *
     * @param string $buildNumber
     *
     * @return App
     */
    public function setBuildNumber($buildNumber)
    {
        $this->buildNumber = $buildNumber;

        return $this;
    }

    /**
     * Get qrcodeUrl
     *
     * @return string
     */
    public function getQrcodeUrl()
    {
        return $this->qrcodeUrl;
    }

    /**
     * Set qrcodeUrl
     *
     * @param string $qrcodeUrl
     *
     * @return App
     */
    public function setQrcodeUrl($qrcodeUrl)
    {
        $this->qrcodeUrl = $qrcodeUrl;

        return $this;
    }

    /**
     * Get bundleId
     *
     * @return string
     */
    public function getBundleId()
    {
        return $this->bundleId;
    }

    /**
     * Set bundleId
     *
     * @param string $bundleId
     *
     * @return App
     */
    public function setBundleId($bundleId)
    {
        $this->bundleId = $bundleId;

        return $this;
    }

    /**
     * Get createdName
     *
     * @return string
     */
    public function getCreatedName()
    {
        return $this->createdName;
    }

    /**
     * Set createdName
     *
     * @param string $createdName
     *
     * @return App
     */
    public function setCreatedName($createdName)
    {
        $this->createdName = $createdName;

        return $this;
    }

    /**
     * Get createdId
     *
     * @return string
     */
    public function getCreatedId()
    {
        return $this->createdId;
    }

    /**
     * Set createdId
     *
     * @param string $createdId
     *
     * @return App
     */
    public function setCreatedId($createdId)
    {
        $this->createdId = $createdId;

        return $this;
    }

    /**
     * Get projectId
     *
     * @return string
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * Set projectId
     *
     * @param string $projectId
     *
     * @return App
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;

        return $this;
    }

    /**
     * Get iconUrl
     *
     * @return string
     */
    public function getIconUrl()
    {
        return $this->iconUrl;
    }

    /**
     * Set iconUrl
     *
     * @param string $iconUrl
     *
     * @return App
     */
    public function setIconUrl($iconUrl)
    {
        $this->iconUrl = $iconUrl;

        return $this;
    }


    public function isType($type)
    {
        return $this->getType() == $type;
    }



    /**
     * @var string
     *
     * @ORM\Column(name="plistUrl", type="string", length=255)
     */

    private $plistUrl;

    /**
     * @return mixed
     */
    public function getPlistUrl()
    {
        return $this->plistUrl;
    }

    /**
     * @param mixed $plistUrl
     */
    public function setPlistUrl($plistUrl)
    {
        $this->plistUrl = $plistUrl;
    }



    private function getFolderWithToken()
    {
        return '/' . $this->getProjectId() . '/' . $this->getToken();
    }

    public function getFilename()
    {

        $name = [$this->getFolderWithToken()];

        if ($this->isType(App::TYPE_ANDROID)) {
            $name[] = '.apk';
        } else if ($this->isType(App::TYPE_IOS)) {
            $name[] = '.ipa';
        }

        return implode('_', $name);
    }

    public function getPlistName()
    {
        return $this->getFolderWithToken().'.plist';
    }


    public function getDownloadNameFilename()
    {
        $name   = [$this->getVersion()];
        $name[] = $this->getCreated()->format('H:i:s_d-m-Y');
        if ($this->isType(App::TYPE_ANDROID)) {
            $name[] = '.apk';

        } else if ($this->isType(App::TYPE_ANDROID)) {
            $name[] = '.ipa';
        }

        return implode('_', array_filter($name));
    }


    public function getIconFileName()
    {
        return $this->getFolderWithToken().'_icon';
    }


    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return[
            'id'=>$this->getId(),
            'name'=>$this->getName(),
            'buildUrl'=>$this->getName(),
            'type'        => $this->getType(),
            'url'         => $this->getBuildUrl(),
            'plist'       => $this->getPlistUrl(),
            'version'     => $this->getVersion(),
            'build'       => $this->getBuildNumber(),
            'qrcode'      => $this->getQrcodeUrl(),
            'comment'     => $this->getComment(),
            'bundleid'    => $this->getBundleId(),
            'createdName' => $this->getCreatedName(),
            'createdId'   => $this->getCreatedId(),
            'projectid'   => $this->getProjectId(),
            'iconurl'     => $this->getIconUrl(),
            'date'=>        $this->getCreated()->format('U')
        ];
    }

    private $comment;

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }



}
