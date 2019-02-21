<?php

namespace App\Services;

use App\AWS\S3;
use App\Entity\App;
use App\Factory\ReaderInterface;

class ExeReader implements ReaderInterface
{
    /**
     * @var S3
     */
    private $s3;

    public function __construct(S3 $s3)
    {
        $this->s3 = $s3;
    }

    /**
     * @param App $app
     * @return App
     * @throws \Exception
     */
    public function readData(App $app)
    {
        $app->setType(App::TYPE_EXE);
        $app->setVersion($this->getFileVersion($app->getBuild()->getRealPath()));
        $app->setBundleId($this->getFileName($app->getBuild()->getRealPath()));

        $headers = [
            'ContentType' => 'application/vnd.microsoft.portable-executable',
            'ContentDisposition' => 'attachment;filename="' . $app->getDownloadNameFilename() . '"'
        ];

        $app->setBuildUrl($this->s3->upload($app->getBuild()->getRealPath(), $app->getFilename(), $headers));
        unlink($app->getBuild()->getRealPath());

        return $app;
    }

    public function getExtension()
    {
        return 'exe';
    }

    private function getFileVersion($fileName)
    {
        return $this->getValueOfSeeking($fileName, "FileVersion");
    }

    private function getFileDescription($fileName)
    {
        return $this->getValueOfSeeking($fileName, "FileDescription");
    }

    private function getFileName($fileName)
    {
        return $this->getValueOfSeeking($fileName, "ProductName");
    }

    private function getValueOfSeeking($fileName, $seeking)
    {
        $handle = fopen($fileName, 'rb');
        if (!$handle) {
            return false;
        }
        $header = fread($handle, 64);

        if (substr($header, 0, 2) != 'MZ') {
            return false;
        }

        $peOffset = unpack("V", substr($header, 60, 4));
        if ($peOffset[1] < 64) {
            return false;
        }

        fseek($handle, $peOffset[1], SEEK_SET);
        $header = fread($handle, 24);

        if (substr($header, 0, 2) != 'PE') {
            return false;
        }

        $machine = unpack("v", substr($header, 4, 2));
        if ($machine[1] != 332) {
            return false;
        }

        $noSections = unpack("v", substr($header, 6, 2));
        $optHdrSize = unpack("v", substr($header, 20, 2));
        fseek($handle, $optHdrSize[1], SEEK_CUR);

        $resFound = false;
        for ($x = 0; $x < $noSections[1]; $x++) {
            $SecHdr = fread($handle, 40);
            if (substr($SecHdr, 0, 5) == '.rsrc') {
                $resFound = true;
                break;
            }
        }

        if (!$resFound) {
            return false;
        }

        $infoVirt = unpack("V", substr($SecHdr, 12, 4));
        $infoSize = unpack("V", substr($SecHdr, 16, 4));
        $infoOff = unpack("V", substr($SecHdr, 20, 4));

        fseek($handle, $infoOff[1], SEEK_SET);
        $info = fread($handle, $infoSize[1]);

        $numNamedDirs = unpack("v", substr($info, 12, 2));
        $numDirs = unpack("v", substr($info, 14, 2));

        $infoFound = false;
        for ($x = 0; $x < ($numDirs[1] + $numNamedDirs[1]); $x++) {
            $type = unpack("V", substr($info, ($x * 8) + 16, 4));
            if ($type[1] == 16) {
                $infoFound = true;
                $subOff = unpack("V", substr($info, ($x * 8) + 20, 4));
                break;
            }
        }

        if (!$infoFound) {
            return false;
        }

        $encodedKey = implode("\x00", str_split($seeking));
        $startOfSeekingKey = strpos($info, $encodedKey);
        if ($startOfSeekingKey !== false) {
            $ulgyRemainderOfData = substr($info, $startOfSeekingKey);
            $arrayOfValues = explode("\x00\x00\x00", $ulgyRemainderOfData);
            return preg_replace('/[^\PC\s]/u', '', trim($arrayOfValues[1]));
        }

        return false;
    }
}
