<?php

namespace Ekreative\TestBuild\CoreBundle\Tests\Services;

use Doctrine\ORM\EntityManager;
use Ekreative\TestBuild\CoreBundle\AWS\S3;
use Ekreative\TestBuild\CoreBundle\Entity\App;
use Ekreative\TestBuild\CoreBundle\Services\BuildsUploader;
use Ekreative\TestBuild\CoreBundle\Services\IpaReader;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;

class BuildsUploaderTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidApk()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        $logger->expects($this->atLeastOnce())->method('error');

        $uploader = $this->getBuildsUploader($logger);

        $app = new App();
        $build = new UploadedFile(__DIR__ . '/../../../../../apps/invalid-xml.apk', 'invalid-xml.apk');

        $readAndroidData = $this->getPrivateMethod(BuildsUploader::class, 'readAndroidData');
        $readAndroidData->invokeArgs($uploader, [$app, $build]);
    }

    public function testApk()
    {
        $uploader = $this->getBuildsUploader();

        $app = new App();
        $build = new UploadedFile(__DIR__ . '/../../../../../apps/app.apk', 'app.apk');

        $readAndroidData = $this->getPrivateMethod(BuildsUploader::class, 'readAndroidData');
        $readAndroidData->invokeArgs($uploader, [$app, $build]);

        $this->assertEquals('https://api.soundcloud.com', $app->getAppServer());
    }

    public function testIpa()
    {
        $uploader = $this->getBuildsUploader();

        $app = new App();
        $build = new UploadedFile(__DIR__ . '/../../../../../apps/app.ipa', 'app.ipa');

        $readIosData = $this->getPrivateMethod(BuildsUploader::class, 'readIosData');
        $readIosData->invokeArgs($uploader, [$app, $build]);

        $this->assertEquals('https://admin.kidslox.com/api/', $app->getAppServer());
    }

    private function getBuildsUploader($loggerMock = null)
    {
        $tokenMock = $this->getMockBuilder(TokenInterface::class)->getMock();
        $tokenMock->expects($this->any())->method('getUser')->willReturn('user');

        $securityMock = $this->getMockBuilder(SecurityContext::class)->disableOriginalConstructor()->getMock();
        $securityMock->expects($this->any())->method('getToken')->willReturn($tokenMock);

        return new BuildsUploader($this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock(),
            $securityMock,
            $this->getMockBuilder(S3::class)->disableOriginalConstructor()->getMock(),
            new IpaReader('/tmp/'),
            $this->getMockBuilder(UrlGeneratorInterface::class)->disableOriginalConstructor()->getMock(),
            $loggerMock ?: $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock()
        );
    }

    protected static function getPrivateMethod($class, $name)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
