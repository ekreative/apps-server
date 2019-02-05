<?php

namespace App\Services;

use App\AWS\S3;
use App\Entity\App;

class AppDataManager
{
    /**
     * @var S3
     */
    private $s3Service;

    /**
     * AppDataManager constructor.
     * @param S3 $s3Service
     */
    public function __construct(S3 $s3Service)
    {
        $this->s3Service = $s3Service;
    }

    /**
     * @param App $app
     * @return string
     * @throws \Exception
     */
    public function saveJsonData(App $app)
    {
        $tempFile = tempnam('/tmp', 'json');

        if ($tempFile) {
            file_put_contents($tempFile, $app->jsonSerialize());

            $headers = [
                'ContentType' => 'application/json',
                'ContentDisposition' => 'attachment;filename="' . $app->getJsonName() . '"'
            ];

            $this->s3Service->upload($tempFile, $app->getJsonName(), $headers);
        }

        throw new \Exception('File not created');
    }

    public function getAppByToken($token)
    {
        return [];
    }

    public function getAppByCommit($commit, $jobName)
    {
        return [];
    }

    public function getAppForProject($projectId, $type, $ref, $jobName)
    {
        return [];
    }

    public function getAppsForProject($projectId, $type)
    {
        return [];
    }

    /**
     * @param $ipa
     * @param $bundleIdentifier
     * @param $version
     * @param $title
     *
     * @return string
     */
    public function getPlistString($ipa, $bundleIdentifier, $version, $title)
    {
        $imp = new \DOMImplementation();
        $dtd = $imp->createDocumentType('plist', '-//Apple//DTD PLIST 1.0//EN', 'http://www.apple.com/DTDs/PropertyList-1.0.dtd');
        $dom = $imp->createDocument('', '', $dtd);

        $dom->encoding = 'UTF-8';

        $dom->formatOutput = true;
        $dom->appendChild($element = $dom->createElement('plist'));
        $element->setAttribute('version', '1.0');

        $element->appendChild($dict = $dom->createElement('dict'));
        $dict->appendChild($dom->createElement('key', 'items'));
        $dict->appendChild($array = $dom->createElement('array'));

        $array->appendChild($mainDict = $dom->createElement('dict'));

        $mainDict->appendChild($dom->createElement('key', 'assets'));
        $mainDict->appendChild($array = $dom->createElement('array'));

        $array->appendChild($dict = $dom->createElement('dict'));
        $dict->appendChild($dom->createElement('key', 'kind'));
        $dict->appendChild($dom->createElement('string', 'software-package'));
        $dict->appendChild($dom->createElement('key', 'url'));
        $dict->appendChild($dom->createElement('string', $ipa));

        $mainDict->appendChild($dom->createElement('key', 'metadata'));

        $mainDict->appendChild($dict = $dom->createElement('dict'));
        $dict->appendChild($dom->createElement('key', 'bundle-identifier'));
        $dict->appendChild($dom->createElement('string', $bundleIdentifier));

        $dict->appendChild($dom->createElement('key', 'bundle-version'));
        $dict->appendChild($dom->createElement('string', $version));

        $dict->appendChild($dom->createElement('key', 'kind'));
        $dict->appendChild($dom->createElement('string', 'software'));

        $dict->appendChild($dom->createElement('key', 'title'));
        $dict->appendChild($titleElement = $dom->createElement('string'));

        $titleElement->appendChild($dom->createTextNode($title . '-v.' . $version));

        return $dom->saveXML();
    }
}