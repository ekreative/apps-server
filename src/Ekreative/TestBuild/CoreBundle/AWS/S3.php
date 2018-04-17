<?php

namespace Ekreative\TestBuild\CoreBundle\AWS;

use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;

class S3
{
    private $s3;
    private $bucketName;
    private $baseUrl;
    private $logger;

    public function __construct(S3Client $s3, $baseUrl, $bucketName, LoggerInterface $loggerInterface)
    {
        $this->bucketName = $bucketName;
        $this->baseUrl = $baseUrl;
        $this->s3 = $s3;
        $this->logger = $loggerInterface;
    }

    public function upload($path, $filename, $headers = [])
    {
        try {
            $config = [
                'Bucket' => $this->bucketName,
                'Key' => trim($filename, '/'),
                'Body' => fopen($path, 'r'),
                'ACL' => 'public-read'
            ];

            foreach ($headers as $key => $header) {
                $config[$key] = $header;
            }

            $result = $this->s3->putObject($config);

            return $result['ObjectURL'];
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload file', ['e' => $e]);
            throw $e;
        }
    }

    public function delete($filename)
    {
        $this->s3->deleteObject([
            'Bucket' => $this->bucketName,
            'Key' => trim($filename, '/'),
        ]);
    }
}
