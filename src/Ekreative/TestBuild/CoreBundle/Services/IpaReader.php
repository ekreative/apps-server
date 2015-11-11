<?php

/**
 * Created by PhpStorm.
 * User: vitaliy
 * Date: 8/6/15
 * Time: 1:03 AM
 */


namespace Ekreative\TestBuild\CoreBundle\Services;


use CFPropertyList\CFPropertyList;

class IpaReader
{

    private $to;
    private $tmpDir;
    private $info = [];
    private $icon;
    private $plist;

    public function __construct($to)
    {

        if ( ! is_dir($to)) {
            throw new Exception('Tmp directory not found or not set');
        }

        $this->to  = $to;
    }


    /**
     * init function.
     *
     * @access public
     * @return void
     */
    public function read($path)
    {
        if ( ! file_exists($path)) {
            throw new Exception('Ipa File not found');
        }

        $this->unZipFiles($path);

        $this->readInfoPlist();

    }


    public function getIcon()
    {
        return $this->icon;
    }


    public function getBundleName()
    {
        return $this->info['CFBundleName'];
    }

    public function getBundleVersion()
    {
        return $this->info['CFBundleVersion'];
    }

    public function getMinimumOSVersion()
    {
        return $this->info['MinimumOSVersion'];
    }

    public function getPlatformVersion()
    {
        return $this->info['DTPlatformVersion'];
    }

    public function getBundleIdentifier()
    {
        return $this->info['CFBundleIdentifier'];
    }

    public function getBundleDisplayName()
    {
        return $this->info['CFBundleDisplayName'];
    }

    public function getBundleShortVersionString()
    {
        return $this->info['CFBundleShortVersionString'];
    }

    public function getBundleSupportedPlatforms()
    {
        return $this->info['CFBundleSupportedPlatforms'];
    }

    public function getSupportedInterfaceOrientations()
    {
        return $this->info['UISupportedInterfaceOrientations'];
    }


    private function unZipFiles($file)
    {

        $this->tmpDir = $this->to . microtime(true);

        mkdir($this->tmpDir, 0777, true);
        $zip = new \ZipArchive();
        $zip->open($file);
        $icon  = null;
        $plist = null;

        for ($i = 0; $i < $zip->numFiles; $i ++) {
            $filename = $zip->getNameIndex($i);

            if (strpos($filename, '57x57') > 0) {
                $icon = $filename;
            }
            if (strpos($filename, '57x57@2x') > 0) {
                $icon = $filename;
            }
            if (strpos($filename, '60x60@2x') > 0) {
                $icon = $filename;
            }
            if (strpos($filename, '76x76@2x') > 0) {
                $icon = $filename;
            }
            if ((strpos($filename, 'Info.plist') > 0) && substr_count($filename,'/')==2) {
                $plist = $filename;
            }
        }

        $zip->extractTo($this->tmpDir, [$icon, $plist]);

        $this->icon  = $this->tmpDir . '/' . $icon;
        $this->plist = $this->tmpDir . '/' . $plist;

    }

    public function clean($dir = null)
    {

        if (is_null($dir)) {
            $dir = $this->tmpDir;
        }

        if (is_dir($dir)) {
            $ignore[] = '/^\.$/';
            $ignore[] = '/^\.\.$/';
            $objects  = scandir($dir);
            foreach ($objects as $object) {
                $reduce = function ($found, $pattern) use ($object) {
                    return $found && ! preg_match($pattern, $object);
                };
                if (array_reduce($ignore, $reduce, true)) {
                    if (is_dir($dir . '/' . $object)) {
                        $this->clean($dir .  '/' . $object, $ignore);
                        rmdir($dir .  '/' . $object);
                    } else {
                        unlink($dir .  '/' . $object);
                    }
                }
            }
        }

    }


    private function readInfoPlist()
    {

        $info                                     = [];
        $info['CFBundleName']                     = null;
        $info['MinimumOSVersion']                 = null;
        $info['MinimumOSVersion']                 = null;
        $info['CFBundleVersion']                  = null;
        $info['CFBundleShortVersionString']       = null;
        $info['CFBundleSupportedPlatforms']       = null;
        $info['UISupportedInterfaceOrientations'] = null;
        $info['CFBundleDisplayName']              = null;
        $info['DTPlatformVersion']                = null;
        $info['CFBundleIdentifier']               = null;

        if (is_file($this->plist)) {

            $plist = new CFPropertyList($this->plist);
            $plist = $plist->toArray();

            $info['CFBundleName']                     = $plist['CFBundleName'];
            $info['CFBundleVersion']                  = $plist['CFBundleVersion'];
            $info['MinimumOSVersion']                 = $plist['MinimumOSVersion'];
            $info['DTPlatformVersion']                = $plist['DTPlatformVersion'];
            $info['CFBundleIdentifier']               = $plist['CFBundleIdentifier'];
            $info['CFBundleDisplayName']              = $plist['CFBundleDisplayName'];
            $info['CFBundleShortVersionString']       = $plist['CFBundleShortVersionString'];
            $info['CFBundleSupportedPlatforms']       = implode(',', $plist['CFBundleSupportedPlatforms']);
            if (array_key_exists('UISupportedInterfaceOrientations', $plist)) {
                $info['UISupportedInterfaceOrientations'] = implode(',', $plist['UISupportedInterfaceOrientations']);
            }
        }

        $this->info = $info;
    }


    public function unpackImage($path){


        if(PHP_OS=='Darwin'){
            $pngdefry = 'pngdefry-osx';
        }else{
        //}else if(PHP_OS=='Linux'){
            $pngdefry = 'pngdefry-linux';
        }

        $pipes = [];

        $suffix = microtime(true);

        $fileinfo = pathinfo($path);
        $process = proc_open( __DIR__ .'/'.$pngdefry.' -s '.$suffix.' -o '.$this->tmpDir.' '.$path , [], $pipes);

        if (is_resource($process)) {
            proc_close($process);
        }

        return $this->tmpDir.'/'.$fileinfo['filename'].$suffix.'.'.$fileinfo['extension'];

    }


}
