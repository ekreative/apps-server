<?php
/**
 * Created by PhpStorm.
 * User: vitaliy
 * Date: 2/10/15
 * Time: 10:11 AM
 */

namespace Ekreative\TestBuild\CoreBundle\AWS;


use Appshed\SlideBundle\Entity\ImageInterface;
use Appshed\SlideBundle\Entity\Slide;
use Appshed\SlideBundle\Lib\Globals;
use Aws\S3\Enum\CannedAcl;
use Aws\S3\S3Client;

class S3
{

    private $s3;
    private $bucketName;
    private $baseUrl;

    function __construct(S3Client $s3, $baseUrl, $bucketName)
    {
        $this->bucketName = $bucketName;
        $this->baseUrl    = $baseUrl;
        $this->s3         = $s3;
    }

    public function upload($path, $filename, $headers = [])
    {
        $config = [
            'Bucket' => $this->bucketName,
            'Key'    => $filename,
            'Body'   => fopen($path, 'r'),
            'ACL'    => CannedAcl::PUBLIC_READ
        ];


        foreach ($headers as $key => $header) {
            $config[$key] = $header;
        }

        $result = $this->s3->putObject($config);

        return $result['ObjectURL'];
    }

    public function delete($filename)
    {
        $this->s3->deleteObject([
            'Bucket' => $this->bucketName,
            'Key'    => $filename,
        ]);
    }


}