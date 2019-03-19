<?php

namespace App\Services;

use App\AWS\S3;
use App\Entity\App;
use App\Factory\ReaderInterface;
use CFPropertyList\CFPropertyList;
use CFPropertyList\IOException;
use Psr\Log\LoggerInterface;

class IosReader implements ReaderInterface
{
    private $to = '/tmp/';
    private $tmpDir;
    private $info = [];
    private $icon;
    private $plist;
    private $basePath;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var S3
     */
    private $s3;

    /**
     * IosReader constructor.
     *
     * @param LoggerInterface $logger
     * @param S3              $s3
     */
    public function __construct(LoggerInterface $logger, S3 $s3)
    {
        $this->logger = $logger;
        $this->s3 = $s3;
    }

    /**
     * @param App $app
     *
     * @return App
     *
     * @throws \Exception
     */
    public function readData(App $app)
    {
        $build = $app->getBuild();

        $this->read($build->getRealPath());
        $app->setType(App::TYPE_IOS);
        $app->setBundleName($this->getBundleName());
        $app->setVersion($this->getBundleShortVersionString());
        $app->setMinimumOSVersion($this->getMinimumOSVersion());
        $app->setPlatformVersion($this->getPlatformVersion());
        $app->setBundleDisplayName($this->getBundleDisplayName());
        $app->setBuildNumber($this->getBundleVersion());
        $app->setBundleSupportedPlatforms($this->getBundleShortVersionString());
        $app->setSupportedInterfaceOrientations($this->getSupportedInterfaceOrientations());
        $app->setBundleId($this->getBundleIdentifier());
        $app->setAppServer($this->getAppServer());
        $icon = $this->getIcon();
        if ($icon) {
            $unpackedIcon = $this->unpackImage($this->getIcon());
            if (file_exists($unpackedIcon)) {
                try {
                    $iconUrl = $this->s3->upload($unpackedIcon, $app->getIconFileName(), BuildsUploader::ICON_HEADERS);
                    $app->setIconUrl($iconUrl);
                } catch (\Exception $e) {
                    $this->logger->warning('Failed to upload icon from ipa', [
                        'e' => $e
                    ]);
                }
            }
        }

        $headers = [
            'ContentType' => 'application/octet-stream',
            'ContentDisposition' => 'attachment;filename="' . $app->getDownloadNameFilename() . '"'
        ];

        $app->setBuildUrl($this->s3->upload($build->getRealPath(), $app->getFilename(), $headers));
        unlink($build->getRealPath());

        $tempFile = tempnam('/tmp', 'plist');

        $plist = $this
            ->getPlistString(
                $app->getBuildUrl(),
                $app->getBundleId(),
                $app->getVersion(),
                $build->getFilename()
            );

        file_put_contents($tempFile, $plist);
        $app->setPlistUrl($this->s3->upload($tempFile, $app->getPlistName(), $headers));
        unlink($tempFile);

        return $app;
    }

    public function getExtension()
    {
        return 'ipa';
    }

    /**
     * @param $ipa
     * @param $bundleIdentifier
     * @param $version
     * @param $title
     *
     * @return string
     */
    private function getPlistString($ipa, $bundleIdentifier, $version, $title)
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

    /**
     * init function.
     *
     * @param $path
     *
     * @throws \Exception
     */
    private function read($path)
    {
        if (!file_exists($path)) {
            throw new \Exception('Ipa File not found');
        }

        $this->unZipFiles($path);
    }

    private function getIcon()
    {
        return $this->icon;
    }

    private function getBundleName()
    {
        return $this->info['CFBundleName'];
    }

    private function getBundleVersion()
    {
        return $this->info['CFBundleVersion'];
    }

    private function getMinimumOSVersion()
    {
        return $this->info['MinimumOSVersion'];
    }

    private function getPlatformVersion()
    {
        return $this->info['DTPlatformVersion'];
    }

    private function getBundleIdentifier()
    {
        return $this->info['CFBundleIdentifier'];
    }

    private function getBundleDisplayName()
    {
        return $this->info['CFBundleDisplayName'];
    }

    private function getBundleShortVersionString()
    {
        return $this->info['CFBundleShortVersionString'];
    }

    private function getBundleSupportedPlatforms()
    {
        return $this->info['CFBundleSupportedPlatforms'];
    }

    private function getSupportedInterfaceOrientations()
    {
        return $this->info['UISupportedInterfaceOrientations'];
    }

    private function getAppServer()
    {
        return $this->info['APP_SERVER'];
    }

    /**
     * @param $file
     *
     * @throws \Exception
     */
    private function unZipFiles($file)
    {
        $this->tmpDir = $this->to . microtime(true);

        mkdir($this->tmpDir, 0777, true);
        $zip = new \ZipArchive();
        $zip->open($file);
        $icon = null;
        $plist = null;

        $unpackFiles = [];
        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $filename = $zip->getNameIndex($i);
            if ((strpos($filename, 'Info.plist') > 0) && 2 == substr_count($filename, '/')) {
                $plist = $filename;
                $unpackFiles[] = $filename;
            }
            if ((false !== strpos($filename, '.png')) && 2 == substr_count($filename, '/')) {
                $unpackFiles[] = $filename;
            }
        }

        $zip->extractTo($this->tmpDir, $unpackFiles);

        $this->basePath = $this->tmpDir . '/' . pathinfo($plist, PATHINFO_DIRNAME);

        $this->plist = $this->tmpDir . '/' . $plist;
        $this->readInfoPlist();
    }

    /**
     * @throws \Exception
     */
    private function readInfoPlist()
    {
        $info = [];
        $info['CFBundleName'] = null;
        $info['MinimumOSVersion'] = null;
        $info['MinimumOSVersion'] = null;
        $info['CFBundleVersion'] = null;
        $info['CFBundleShortVersionString'] = null;
        $info['CFBundleSupportedPlatforms'] = null;
        $info['UISupportedInterfaceOrientations'] = null;
        $info['CFBundleDisplayName'] = null;
        $info['DTPlatformVersion'] = null;
        $info['CFBundleIdentifier'] = null;
        $info['APP_SERVER'] = null;

        if (is_file($this->plist)) {
            try {
                $plist = new CFPropertyList($this->plist);
            } catch (IOException $e) {
                throw new \Exception('File could not be read');
            }
            $plist = $plist->toArray();

            $iconGroups = [];
            foreach ($plist as $key => $value) {
                if (0 === strpos($key, 'CFBundleIcons')) {
                    $iconGroups[] = $value;
                }
            }

            $this->icon = $this->getIconPath($iconGroups);

            $info['CFBundleName'] = array_key_exists('CFBundleName', $plist) ? $plist['CFBundleName'] : 'CFBundleName';
            $info['CFBundleVersion'] = array_key_exists('CFBundleVersion', $plist) ? $plist['CFBundleVersion'] : 'CFBundleVersion';
            $info['MinimumOSVersion'] = array_key_exists('MinimumOSVersion', $plist) ? $plist['MinimumOSVersion'] : 'MinimumOSVersion';
            $info['DTPlatformVersion'] = array_key_exists('CFBundleIconFiles', $plist) ? $plist['DTPlatformVersion'] : 'DTPlatformVersion';
            $info['CFBundleIdentifier'] = array_key_exists('CFBundleIdentifier', $plist) ? $plist['CFBundleIdentifier'] : 'CFBundleIdentifier';
            $info['CFBundleDisplayName'] = array_key_exists('CFBundleDisplayName', $plist) ? $plist['CFBundleDisplayName'] : 'CFBundleDisplayName';
            $info['CFBundleShortVersionString'] = array_key_exists('CFBundleShortVersionString', $plist) ? $plist['CFBundleShortVersionString'] : 'CFBundleShortVersionString';
            $info['CFBundleShortVersionString'] = array_key_exists('CFBundleShortVersionString', $plist) ? $plist['CFBundleShortVersionString'] : 'CFBundleShortVersionString';
            $info['CFBundleSupportedPlatforms'] = implode(',', $plist['CFBundleSupportedPlatforms']);
            $info['APP_SERVER'] = array_key_exists('APP_SERVER', $plist) ? $plist['APP_SERVER'] : 'APP_SERVER';
            if (array_key_exists('UISupportedInterfaceOrientations', $plist)) {
                $info['UISupportedInterfaceOrientations'] = implode(',', $plist['UISupportedInterfaceOrientations']);
            }
        }

        $this->info = $info;
    }

    private function getIconPath($groups)
    {
        $iconsGroups = [];
        foreach ($groups as $group) {
            if (array_key_exists('CFBundlePrimaryIcon', $group)) {
                $group = $group['CFBundlePrimaryIcon'];
                if (array_key_exists('CFBundleIconFiles', $group)) {
                    $group = $group['CFBundleIconFiles'];
                    $iconsGroups[] = $group;
                }
            }
        }

        $iconPath = null;
        $original = call_user_func_array('array_merge', $iconsGroups);
        $original = array_unique($original);
        $largest = 0;
        foreach ($original as $maybe) {
            $maybepaths = $this->iconToPaths($maybe);
            foreach ($maybepaths as $maybepath) {
                if (file_exists($maybepath)) {
                    $size = filesize($maybepath);
                    if ($size > $largest) {
                        $iconPath = $maybepath;
                        $largest = $size;
                    }
                }
            }
        }

        return  $iconPath;
    }

    private function iconToPath($icon, $ext = '.png')
    {
        return $this->basePath . '/' . $icon . (strpos($icon, '.png') > 0 ? '' : $ext);
    }

    private function iconToPaths($icon)
    {
        return [
            $this->iconToPath($icon),
            $this->iconToPath($icon, '@2x.png'),
            $this->iconToPath($icon, '@3x.png')
        ];
    }

    private function unpackImage($path)
    {
        if ('image/png' == mime_content_type($path)) {
            //sometime images not compressed
            return $path;
        }

        if (PHP_OS == 'Darwin') {
            $pngdefry = 'pngdefry-osx';
        } else {
            //}else if(PHP_OS=='Linux'){
            $pngdefry = 'pngdefry-linux';
        }

        $pipes = [];

        $suffix = microtime(true);

        $fileinfo = pathinfo($path);

        $command = __DIR__ . '/' . $pngdefry . ' -s ' . $suffix . ' -o ' . $this->tmpDir . ' ' . str_replace(' ', '\\ ', $path);
        $process = proc_open($command, [], $pipes);

        if (is_resource($process)) {
            proc_close($process);
        }

        return $this->tmpDir . '/' . $fileinfo['filename'] . $suffix . '.' . $fileinfo['extension'];
    }

    public function __destruct()
    {
        if ($this->tmpDir) {
            $this->deleteDir($this->tmpDir);
        }
    }

    private function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new \InvalidArgumentException("$dirPath must be a directory");
        }
        if ('/' != substr($dirPath, strlen($dirPath) - 1, 1)) {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
}
