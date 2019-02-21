<?php

namespace App\Services;

use App\AWS\S3;
use App\Entity\App;
use App\PaginatorS3\Paginator;
use Aws\Result;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\SerializerInterface;

class AppDataManager
{
    const INDEX_FOLDER = 'index/';
    const COMMIT_FOLDER = 'commit/';
    const INFO_FOLDER = 'info/';

    /**
     * @var S3
     */
    private $s3Service;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * AppDataManager constructor.
     * @param S3 $s3Service
     * @param SerializerInterface $serializer
     */
    public function __construct(S3 $s3Service, SerializerInterface $serializer)
    {
        $this->s3Service = $s3Service;
        $this->serializer = $serializer;
    }

    /**
     * @param App $app
     * @throws \Exception
     */
    public function saveJsonData(App $app)
    {
        $this->save($app->getJsonUrl(), $this->serializer->serialize($app, 'json', []));
        $this->save($app->getInfoAllJson(), $this->serializer->serialize($app, 'json', []));
        $this->save(self::INDEX_FOLDER . $app->getToken() . '.json', $app->getLinkJson());
        if ($app->getCommit()) {
            if ($app->getJobName()) {
                $this->save(self::COMMIT_FOLDER . $app->getCommit() . '/' . $app->getJobName() . '.json', $app->getLinkJson());
            } else {
                $this->save(self::COMMIT_FOLDER . $app->getCommit() . '.json', $app->getLinkJson());
            }
        }

        if ($app->getRef()) {
            if ($app->getJobName()) {
                $this->save(self::INDEX_FOLDER . $app->getProjectId() . '/' . $app->getType() . '/' . $app->getRef() . '/' . $app->getJobName() . '/' . basename($app->getJsonUrl()) , $app->getLinkJson());
            }
            $this->save(self::INDEX_FOLDER . $app->getProjectId() . '/' . $app->getType() . '/' . $app->getRef() . '/' . basename($app->getJsonUrl()), $app->getLinkJson());
        }
    }

    /**
     * @param array $item
     * @throws \Exception
     */
    public function saveJsonDataFromArray(array $item)
    {
        /** @var App $app */
        $app = $this->serializer->denormalize($item, App::class, 'json', []);
        $this->saveJsonData($app);
    }

    /**
     * @param string $fileName
     * @param string $content
     * @throws \Exception
     */
    private function save(string $fileName, string $content)
    {
        $tempFile = tempnam('/tmp', 'json');

        if ($tempFile) {
            file_put_contents($tempFile, $content);

            $headers = [
                'ContentType' => 'application/json',
                'ContentDisposition' => 'attachment;filename="' . $fileName . '"'
            ];

            $this->s3Service->upload($tempFile, $fileName, $headers);
            unlink($tempFile);

            return;
        }

        throw new \Exception('File not created');
    }

    /**
     * @param string $token
     * @return object
     * @throws \Exception
     */
    public function getAppByToken($token)
    {
        $object = $this->s3Service->getObjectByKey(self::INDEX_FOLDER . $token . '.json');

        if (!$object) {
            throw new \Exception('Token not found');
        }

        $data = (array) json_decode($object);

        if ($data) {
            $content = $this->s3Service->getObjectByKey($data['link']);

            if ($content) {
                return $this->serializer->deserialize($content, App::class, 'json', []);
            }
        }

        throw new \Exception('Token not found');
    }

    /**
     * @param string $commit
     * @param string|null $jobName
     * @return object
     * @throws \Exception
     */
    public function getAppByCommit($commit, $jobName = null)
    {
        if ($jobName) {
            $object = $this->s3Service->getObjectByKey(self::COMMIT_FOLDER . $commit . '/' . $jobName . '.json');
        } else {
            $object = $this->s3Service->getObjectByKey(self::COMMIT_FOLDER . $commit . '.json');
        }

        if (!$object) {
            throw new \Exception('Token not found');
        }

        $data = (array) json_decode($object);

        if ($data) {
            $content = $this->s3Service->getObjectByKey($data['link']);

            if ($content) {
                return $this->serializer->deserialize($content, App::class, 'json', []);
            }
        }

        throw new \Exception('Token not found');
    }

    /**
     * @param $projectId
     * @param $type
     * @param $ref
     * @param $jobName
     * @return object
     * @throws \Exception
     */
    public function getAppForProject($projectId, $type, $ref, $jobName = null)
    {
        if ($jobName) {
            $param = [
                'Delimiter' => '/',
                'Prefix' => self::INDEX_FOLDER . $projectId . '/' . ($type ? ($type . '/') : self::INFO_FOLDER) . $ref . '/'. $jobName . '/',
                'MaxKeys' => 1
            ];
            /** @var Result $object */
            $list = $this->s3Service->getListObject($param);
            $key = $list->search('Contents[].Key');

            if ($key && $key = reset($key)) {
                $content = $this->s3Service->getObjectByKey($key);

                $data = (array) json_decode($content);

                if ($data) {
                    $content = $this->s3Service->getObjectByKey($data['link']);

                    if ($content) {
                        return $this->serializer->deserialize($content, App::class, 'json', []);
                    }
                }
            }
        } else {
            $param = [
                'Delimiter' => '/',
                'Prefix' => self::INDEX_FOLDER . $projectId . '/' . ($type ? ($type . '/') : self::INFO_FOLDER) . $ref . '/',
                'MaxKeys' => 1
            ];
            /** @var Result $object */
            $list = $this->s3Service->getListObject($param);
            $key = $list->search('Contents[].Key');

            if ($key && $key = reset($key)) {
                $content = $this->s3Service->getObjectByKey($key);

                $data = (array) json_decode($content);

                if ($data) {
                    $content = $this->s3Service->getObjectByKey($data['link']);

                    if ($content) {
                        return $this->serializer->deserialize($content, App::class, 'json', []);
                    }
                }
            }
        }

        throw new \Exception('Build not found');
    }

    /**
     * @param string $projectId
     * @param string $type
     * @param int $page
     * @return Paginator
     */
    public function getAppsForProject($projectId, $type = null, $page = 1)
    {
        $param = [
            'Delimiter' => '/',
            'Prefix' => $projectId . '/' . ($type ? ($type . '/') : self::INFO_FOLDER) ,
        ];

        return $this->getApps($param, $page);
    }

    /**
     * @param  array $param
     * @param int $page
     * @return Paginator
     */
    private function getApps(array $param, int $page)
    {
        $data = new ArrayCollection();
        $s3Paginator = new Paginator($this->s3Service, $page, $param);

        foreach ($s3Paginator->getData() as $object) {
            $data->add($this->serializer->deserialize($object, App::class, 'json', []));
        }

        $s3Paginator->setData($data);

        return $s3Paginator;
    }

    /**
     * @param App $app
     */
    public function deleteJsonFiles(App $app)
    {
        $this->s3Service->delete($app->getJsonUrl());
        $this->s3Service->delete($app->getInfoAllJson());
        $this->s3Service->delete(AppDataManager::INDEX_FOLDER . $app->getToken() . '.json');

        if ($app->getCommit()) {
            $this->s3Service->delete(AppDataManager::COMMIT_FOLDER . $app->getToken() . '.json');
        }

        if ($app->getRef()) {
            $this->s3Service->delete(AppDataManager::INDEX_FOLDER . $app->getProjectId() . '/' . $app->getType() . '/' . $app->getRef() . '/' . basename($app->getJsonUrl()));

            if ($app->getJobName()) {
                $this->s3Service->delete(AppDataManager::INDEX_FOLDER . $app->getProjectId() . '/' . $app->getType() . '/' . $app->getRef() . '/' . $app->getJobName() . '/' . basename($app->getJsonUrl()));
            }
        }
    }

    /**
     * @throws \Exception
     * @return int
     */
    public function saveDataToInfoFolder()
    {
        $param = [
            'Delimiter' => '/',
            'Prefix' => self::INDEX_FOLDER,
        ];

        $paginator = $this->s3Service->getPaginator($param);

        $i = 0;
        /** @var Result $item */
        foreach ($paginator->search('Contents[].Key') as $key) {

            $content = $this->s3Service->getObjectByKey($key);

            if ($content) {
                $data = (array) json_decode($content);

                $content = $this->s3Service->getObjectByKey($data['link']);

                $this->save($data['projectId'] . '/' . self::INFO_FOLDER . basename($data['link']), $content);
                $i++;
            }
        }

        return $i;
    }
}