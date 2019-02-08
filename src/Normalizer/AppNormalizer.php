<?php

namespace App\Normalizer;

use App\Entity\App;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AppNormalizer implements DenormalizerInterface, NormalizerInterface
{
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var App $app */
        $app = new App();

        foreach ($data as $fieldName => $fieldValue) {
            switch ($fieldName) {
                case 'name':
                    $app->setName($fieldValue);
                    break;
                case 'version':
                    $app->setVersion($fieldValue);
                    break;
                case 'buildNumber':
                    $app->setBuildNumber($fieldValue);
                    break;
                case 'bundleId':
                    $app->setBundleId($fieldValue);
                    break;
                case 'minSdkLevel':
                    $app->setMinSdkLevel($fieldValue);
                    break;
                case 'permission':
                    $app->setPermssions($fieldValue);
                    break;
                case 'debuggable':
                    $app->setDebuggable(!!$fieldValue);
                    break;
                case 'bundleName':
                    $app->setBundleName($fieldValue);
                    break;
                case 'bundleVersion':
                    $app->setBundleVersion($fieldValue);
                    break;
                case 'minimumOSVersion':
                    $app->setMinimumOSVersion($fieldValue);
                    break;
                case 'platformVersion':
                    $app->setPlatformVersion($fieldValue);
                    break;
                case 'bundleIdentifier':
                    $app->setBundleIdentifier($fieldValue);
                    break;
                case 'bundleDisplayName':
                    $app->setBundleDisplayName($fieldValue);
                    break;
                case 'bundleShortVersionString':
                    $app->setBundleShortVersionString($fieldValue);
                    break;
                case 'bundleSupportedPlatforms':
                    $app->setBundleSupportedPlatforms($fieldValue);
                    break;
                case 'supportedInterfaceOrientations':
                    $app->setSupportedInterfaceOrientations($fieldValue);
                    break;
                case 'size':
                    $app->setSize($fieldValue);
                    break;
                case 'type':
                    $app->setType($fieldValue);
                    break;
                case 'buildUrl':
                    $app->setBuildUrl($fieldValue);
                    break;
                case 'qrcodeUrl':
                    $app->setQrcodeUrl($fieldValue);
                    break;
                case 'createdName':
                    $app->setCreatedName($fieldValue);
                    break;
                case 'createdId':
                    $app->setCreatedId($fieldValue);
                    break;
                case 'projectId':
                    $app->setProjectId($fieldValue);
                    break;
                case 'iconUrl':
                    $app->setIconUrl($fieldValue);
                    break;
                case 'release':
                    $app->setRelease(!!$fieldValue);
                    break;
                case 'token':
                    $app->setToken($fieldValue);
                    break;
                case 'created':
                    try {
                        $app->setCreated(new \DateTime($fieldValue));
                    } catch (\Exception $e) {
                    }
                    break;
                case 'comment':
                    $app->setComment($fieldValue);
                    break;
                case 'ci':
                    $app->setCi(!!$fieldValue);
                    break;
                case 'ref':
                    $app->setRef($fieldValue);
                    break;
                case 'commit':
                    $app->setCommit($fieldValue);
                    break;
                case 'jobName':
                    $app->setJobName($fieldValue);
                    break;
                case 'appServer':
                    $app->setAppServer($fieldValue);
                    break;
                case 'plistUrl':
                    $app->setPlistUrl($fieldValue);
                    break;
                case 'jsonUrl':
                    $app->setJsonUrl($fieldValue);
                    break;
            }
        }

        return $app;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return App::class == $type;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        /** @var App $app */
        $app = &$object;

        $data = [
            'name' => $app->getName(),
            'version' => $app->getVersion(),
            'buildNumber' => $app->getBuildNumber(),
            'bundleId' => $app->getBundleId(),
            'minSdkLevel' => $app->getMinSdkLevel(),
            'permission' => $app->getPermssions(),
            'debuggable' => $app->isDebuggable(),
            'bundleName' => $app->getBundleName(),
            'bundleVersion' => $app->getBundleVersion(),
            'minimumOSVersion' => $app->getMinimumOSVersion(),
            'platformVersion' => $app->getPlatformVersion(),
            'bundleIdentifier' => $app->getBundleIdentifier(),
            'bundleDisplayName' => $app->getBundleDisplayName(),
            'bundleShortVersionString' => $app->getBundleShortVersionString(),
            'bundleSupportedPlatforms' => $app->getBundleSupportedPlatforms(),
            'supportedInterfaceOrientations' => $app->getSupportedInterfaceOrientations(),
            'size' => $app->getSize(),
            'type' => $app->getType(),
            'buildUrl' => $app->getBuildUrl(),
            'qrcodeUrl' => $app->getQrcodeUrl(),
            'createdName' => $app->getCreatedName(),
            'createdId' => $app->getCreatedId(),
            'projectId' => $app->getProjectId(),
            'iconUrl' => $app->getIconUrl(),
            'release' => $app->isRelease(),
            'token' => $app->getToken(),
            'created' => $app->getCreated()->format('c'),
            'comment' => $app->getComment(),
            'ci' => $app->isCi(),
            'ref' => $app->getRef(),
            'commit' => $app->getCommit(),
            'jobName' => $app->getJobName(),
            'appServer' => $app->getAppServer(),
            'plistUrl' => $app->getPlistUrl(),
            'jsonUrl' => $app->getJsonUrl()
        ];

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->supportsClass($data);
    }

    private function supportsClass($data)
    {
        return $data instanceof App;
    }
}
