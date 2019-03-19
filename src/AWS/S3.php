<?php

namespace App\AWS;

use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;

class S3
{
    private $s3;
    private $bucketName;
    private $baseUrl;
    private $logger;

    /**
     * S3 constructor.
     *
     * @param S3Client $s3
     * @param $baseUrl
     * @param $bucketName
     * @param LoggerInterface $loggerInterface
     */
    public function __construct(S3Client $s3, $baseUrl, $bucketName, LoggerInterface $loggerInterface)
    {
        $this->bucketName = $bucketName;
        $this->baseUrl = $baseUrl;
        $this->s3 = $s3;
        $this->logger = $loggerInterface;
    }

    /**
     * @param string $path
     * @param string $filename
     * @param array  $headers
     *
     * @return mixed
     *
     * @throws \Exception
     */
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

    /**
     * @param string $filename
     */
    public function delete($filename)
    {
        $this->s3->deleteObject([
            'Bucket' => $this->bucketName,
            'Key' => trim($filename, '/'),
        ]);
    }

    /**
     * @param array $parameters
     *
     * @return \Aws\ResultPaginator
     */
    public function getPaginator(array $parameters)
    {
        return $this->s3->getPaginator('ListObjectsV2', array_merge(['Bucket' => $this->bucketName], $parameters));
    }

    /**
     * @param array $parameters
     *
     * @return \Iterator
     */
    public function getIterator(array $parameters)
    {
        return $this->s3->getIterator('ListObjectsV2', array_merge(['Bucket' => $this->bucketName], $parameters));
    }

    /**
     * @param array $parameters
     *
     * @return \Aws\Result
     */
    public function getListObject(array $parameters)
    {
        return $this->s3->listObjectsV2(array_merge(['Bucket' => $this->bucketName], $parameters));
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getObjectByKey($key)
    {
        $result = $this->s3->getObject([
            'Bucket' => $this->bucketName,
            'Key' => $key,
        ]);

        return (string) $result['Body'];
    }
}
