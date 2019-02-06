<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * App.
 *
 */
class App implements \JsonSerializable
{
    const TYPE_ANDROID = 'android';
    const TYPE_IOS = 'ios';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $buildNumber;

    /**
     * @var string
     */
    private $bundleId;

    ////android

    /**
     * @var string
     */
    private $minSdkLevel;

    /**
     * @var string
     */
    private $permssions;

    /**
     * @var bool
     */
    private $debuggable;

    //// ios

    /**
     * @var int
     */
    private $bundleName;

    /**
     * @var int
     */
    private $bundleVersion;

    /**
     * @var int
     */
    private $minimumOSVersion;

    /**
     * @var int
     */
    private $platformVersion;

    /**
     * @var int
     */
    private $bundleIdentifier;

    /**
     * @var int
     */
    private $bundleDisplayName;

    /**
     * @var int
     */
    private $bundleShortVersionString;

    /**
     * @var int
     */
    private $bundleSupportedPlatforms;

    /**
     * @var int
     */
    private $supportedInterfaceOrientations;

    ////other

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $buildUrl;
    /**
     * @var string
     */
    private $qrcodeUrl;
    /**
     * @var string
     */
    private $createdName;

    /**
     * @var string
     */
    private $createdId;

    /**
     * @var string
     */
    private $projectId;

    /**
     * @var string
     */
    private $iconUrl;

    /**
     * @var bool
     */
    private $release;

    private $icon;

    private $build;

    /**
     * @var string
     */
    private $token;
    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var bool
     */
    private $ci;

    /**
     * @var string
     */
    private $ref;

    /**
     * @var string
     */
    private $commit;

    /**
     * @var string
     */
    private $jobName;

    /**
     * @var string
     */
    private $appServer;

    /**
     * @var string
     */
    private $plistUrl;

    /**
     * @var string
     */
    private $jsonUrl;

    /**
     * @var string
     */
    private $jsonName;

    public function __construct()
    {
        $this->setToken(md5(time() . rand(100, 1000)));
        $this->jsonName = PHP_INT_MAX - time();
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
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
        if (file_exists($build->getRealPath())) {
            $this->setSize(filesize($build->getRealPath()));
        }

        $this->build = $build;
    }

    public function getPublicAppSize()
    {
        return $this->formatBytes($this->getSize());
    }

    private function formatBytes($b)
    {
        if ($b < 1024) {
            return $b . ' B';
        } elseif ($b < 1048576) {
            return round($b / 1024, 2) . ' KB';
        } elseif ($b < 1073741824) {
            return round($b / 1048576, 2) . ' MB';
        } elseif ($b < 1099511627776) {
            return round($b / 1073741824, 2) . ' GB';
        } elseif ($b < 1125899906842624) {
            return round($b / 1099511627776, 2) . ' TB';
        } elseif ($b < 1152921504606846976) {
            return round($b / 1125899906842624, 2) . ' PB';
        } elseif ($b < 1180591620717411303424) {
            return round($b / 1152921504606846976, 2) . ' EB';
        } elseif ($b < 1208925819614629174706176) {
            return round($b / 1180591620717411303424, 2) . ' ZB';
        } else {
            return round($b / 1208925819614629174706176, 2) . ' YB';
        }
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
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
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
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
     * Get buildUrl.
     *
     * @return string
     */
    public function getBuildUrl()
    {
        return $this->buildUrl;
    }

    /**
     * Set buildUrl.
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
     * Get version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version.
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
     * Get buildNumber.
     *
     * @return string
     */
    public function getBuildNumber()
    {
        return $this->buildNumber;
    }

    /**
     * Set buildNumber.
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
     * Get qrcodeUrl.
     *
     * @return string
     */
    public function getQrcodeUrl()
    {
        return $this->qrcodeUrl;
    }

    /**
     * Set qrcodeUrl.
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
     * Get bundleId.
     *
     * @return string
     */
    public function getBundleId()
    {
        return $this->bundleId;
    }

    /**
     * Set bundleId.
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
     * Get createdName.
     *
     * @return string
     */
    public function getCreatedName()
    {
        return $this->createdName;
    }

    /**
     * Set createdName.
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
     * Get createdId.
     *
     * @return string
     */
    public function getCreatedId()
    {
        return $this->createdId;
    }

    /**
     * Set createdId.
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
     * Get projectId.
     *
     * @return string
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * Set projectId.
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
     * Get iconUrl.
     *
     * @return string
     */
    public function getIconUrl()
    {
        return $this->iconUrl ?: 'https://testbuild.rocks/images/icon.png';
    }

    /**
     * Set iconUrl.
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

    private function getBaseFolder()
    {
        return '/' . $this->getProjectId() . '/' . $this->getType() . '/';
    }

    private function getFolderWithToken()
    {
        return $this->getBaseFolder() . $this->getToken();
    }

    public function getFilename()
    {
        $name = [$this->getFolderWithToken()];

        if ($this->isType(self::TYPE_ANDROID)) {
            $name[] = '.apk';
        } elseif ($this->isType(self::TYPE_IOS)) {
            $name[] = '.ipa';
        }

        return implode('_', $name);
    }

    public function getPlistName()
    {
        return $this->getFolderWithToken() . '.plist';
    }

    private function getJsonName()
    {
        return $this->getBaseFolder() . 'json/' . $this->jsonName . '.json';
    }

    /**
     * @return string
     */
    public function getJsonUrl()
    {
        return $this->jsonUrl ? $this->jsonUrl : $this->getJsonName();
    }

    /**
     * @param string $jsonUrl
     * @return App
     */
    public function setJsonUrl($jsonUrl)
    {
        $this->jsonUrl = $jsonUrl;

        return $this;
    }

    public function getDownloadNameFilename()
    {
        $name = [$this->getVersion()];
        $name[] = $this->getCreated()->format('H:i:s_d-m-Y');
        if ($this->isType(self::TYPE_ANDROID)) {
            $name[] = '.apk';
        } elseif ($this->isType(self::TYPE_IOS)) {
            $name[] = '.ipa';
        }

        return implode('_', array_filter($name));
    }

    public function getIconFileName()
    {
        return $this->getFolderWithToken() . '_icon';
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'buildUrl' => $this->getName(),
            'type' => $this->getType(),
            'url' => $this->getBuildUrl(),
            'plist' => $this->getPlistUrl(),
            'version' => $this->getVersion(),
            'build' => $this->getBuildNumber(),
            'qrcode' => $this->getQrcodeUrl(),
            'comment' => $this->getComment(),
            'bundleid' => $this->getBundleId(),
            'createdName' => $this->getCreatedName(),
            'createdId' => $this->getCreatedId(),
            'projectid' => $this->getProjectId(),
            'iconurl' => $this->getIconUrl(),
            'date' => $this->getCreated()->format('U'),
            'release' => $this->getRelease(),
            'appServer' => $this->getAppServer(),
            'ref' => $this->getRef(),
            'commit' => $this->getCommit(),
            'jobName' => $this->getJobName(),
        ];
    }

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

    public function getRelease()
    {
        return $this->release;
    }

    /**
     * @return bool
     */
    public function isRelease()
    {
        return $this->release;
    }

    public function inverseRelease()
    {
        return $this->release = !$this->release;
    }

    /**
     * @param bool $release
     */
    public function setRelease($release)
    {
        $this->release = $release;
    }

    /**
     * @return string
     */
    public function getPermssions()
    {
        return $this->permssions;
    }

    /**
     * @param string $permssions
     */
    public function setPermssions($permssions)
    {
        $this->permssions = $permssions;
    }

    /**
     * @return string
     */
    public function getMinSdkLevel()
    {
        return $this->minSdkLevel;
    }

    /**
     * @param string $minSdkLevel
     */
    public function setMinSdkLevel($minSdkLevel)
    {
        $this->minSdkLevel = $minSdkLevel;
    }

    /**
     * @return bool
     */
    public function getDebuggable()
    {
        return $this->debuggable;
    }

    /**
     * @return bool
     */
    public function isDebuggable()
    {
        return $this->debuggable;
    }

    /**
     * @param bool $debuggable
     */
    public function setDebuggable($debuggable)
    {
        $this->debuggable = $debuggable;
    }

    /**
     * @return int
     */
    public function getBundleName()
    {
        return $this->bundleName;
    }

    /**
     * @param int $bundleName
     */
    public function setBundleName($bundleName)
    {
        $this->bundleName = $bundleName;
    }

    /**
     * @return int
     */
    public function getBundleVersion()
    {
        return $this->bundleVersion;
    }

    /**
     * @param int $bundleVersion
     */
    public function setBundleVersion($bundleVersion)
    {
        $this->bundleVersion = $bundleVersion;
    }

    /**
     * @return int
     */
    public function getMinimumOSVersion()
    {
        return $this->minimumOSVersion;
    }

    /**
     * @param int $minimumOSVersion
     */
    public function setMinimumOSVersion($minimumOSVersion)
    {
        $this->minimumOSVersion = $minimumOSVersion;
    }

    /**
     * @return int
     */
    public function getPlatformVersion()
    {
        return $this->platformVersion;
    }

    /**
     * @param int $platformVersion
     */
    public function setPlatformVersion($platformVersion)
    {
        $this->platformVersion = $platformVersion;
    }

    /**
     * @return int
     */
    public function getBundleIdentifier()
    {
        return $this->bundleIdentifier;
    }

    /**
     * @param int $bundleIdentifier
     */
    public function setBundleIdentifier($bundleIdentifier)
    {
        $this->bundleIdentifier = $bundleIdentifier;
    }

    /**
     * @return int
     */
    public function getBundleDisplayName()
    {
        return $this->bundleDisplayName;
    }

    /**
     * @param int $bundleDisplayName
     */
    public function setBundleDisplayName($bundleDisplayName)
    {
        $this->bundleDisplayName = $bundleDisplayName;
    }

    /**
     * @return int
     */
    public function getBundleShortVersionString()
    {
        return $this->bundleShortVersionString;
    }

    /**
     * @param int $bundleShortVersionString
     */
    public function setBundleShortVersionString($bundleShortVersionString)
    {
        $this->bundleShortVersionString = $bundleShortVersionString;
    }

    /**
     * @return int
     */
    public function getBundleSupportedPlatforms()
    {
        return $this->bundleSupportedPlatforms;
    }

    /**
     * @param int $bundleSupportedPlatforms
     */
    public function setBundleSupportedPlatforms($bundleSupportedPlatforms)
    {
        $this->bundleSupportedPlatforms = $bundleSupportedPlatforms;
    }

    /**
     * @return int
     */
    public function getSupportedInterfaceOrientations()
    {
        return $this->supportedInterfaceOrientations;
    }

    /**
     * @param int $supportedInterfaceOrientations
     */
    public function setSupportedInterfaceOrientations($supportedInterfaceOrientations)
    {
        $this->supportedInterfaceOrientations = $supportedInterfaceOrientations;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return bool
     */
    public function isCi()
    {
        return $this->ci;
    }

    /**
     * @param bool $ci
     *
     * @return App
     */
    public function setCi($ci)
    {
        $this->ci = $ci;

        return $this;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $ref
     *
     * @return App
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * @return string
     */
    public function getCommit()
    {
        return $this->commit;
    }

    /**
     * @param string $commit
     */
    public function setCommit($commit)
    {
        $this->commit = $commit;
    }

    /**
     * @return string
     */
    public function getJobName()
    {
        return $this->jobName;
    }

    /**
     * @param string $jobName
     */
    public function setJobName($jobName)
    {
        $this->jobName = $jobName;
    }

    /**
     * @return string
     */
    public function getAppServer()
    {
        return $this->appServer;
    }

    /**
     * @param string $appServer
     *
     * @return App
     */
    public function setAppServer($appServer)
    {
        $this->appServer = $appServer;

        return $this;
    }

    /**
     * @return string
     */
    public function getLinkJson()
    {
        $data = [
            'projectId' => $this->getProjectId(),
            'link' => ltrim($this->getJsonUrl(), '/')
        ];

        return json_encode($data, true);
    }
}
