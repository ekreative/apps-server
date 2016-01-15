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
    private $basePath;

    public function __construct($to)
    {

        if ( ! is_dir($to)) {
            throw new Exception('Tmp directory not found or not set');
        }

        $this->to = $to;
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

    public function getAppServer()
    {
        return $this->info['APP_SERVER'];
    }


    private function unZipFiles($file)
    {


        $this->tmpDir = $this->to . microtime(true);

        mkdir($this->tmpDir, 0777, true);
        $zip = new \ZipArchive();
        $zip->open($file);
        $icon  = null;
        $plist = null;


        $unpackFiles = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if ((strpos($filename, 'Info.plist') > 0) && substr_count($filename, '/') == 2) {
                $plist          = $filename;
                $unpackFiles [] = $filename;
            }
            if ((strpos($filename, '.png') !== false) && substr_count($filename, '/') == 2) {
                $unpackFiles [] = $filename;
            }

        }

        $zip->extractTo($this->tmpDir, $unpackFiles);

        $this->basePath = $this->tmpDir . '/' . pathinfo($plist, PATHINFO_DIRNAME);

        $this->plist = $this->tmpDir . '/' . $plist;
        $this->readInfoPlist();


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
        $info['APP_SERVER'] = null;

        if (is_file($this->plist)) {

            $plist = new CFPropertyList($this->plist);
            $plist = $plist->toArray();

            $iconGroups = [];
            foreach ($plist as $key => $value) {
                if (strpos($key, 'CFBundleIcons') === 0) {
                    $iconGroups[] = $value;
                }
            }

            $this->icon = $this->getIconPath($iconGroups);

            $info['CFBundleName']               = array_key_exists('CFBundleName', $plist)                ? $plist['CFBundleName']               :'CFBundleName'   ;
            $info['CFBundleVersion']            = array_key_exists('CFBundleVersion', $plist)             ? $plist['CFBundleVersion']            :'CFBundleVersion'   ;
            $info['MinimumOSVersion']           = array_key_exists('MinimumOSVersion', $plist)            ? $plist['MinimumOSVersion']           :'MinimumOSVersion'   ;
            $info['DTPlatformVersion']          = array_key_exists('CFBundleIconFiles', $plist)           ? $plist['DTPlatformVersion']          :'DTPlatformVersion'   ;
            $info['CFBundleIdentifier']         = array_key_exists('CFBundleIdentifier', $plist)          ? $plist['CFBundleIdentifier']         :'CFBundleIdentifier'   ;
            $info['CFBundleDisplayName']        = array_key_exists('CFBundleDisplayName', $plist)         ? $plist['CFBundleDisplayName']        :'CFBundleDisplayName'   ;
            $info['CFBundleShortVersionString'] = array_key_exists('CFBundleShortVersionString', $plist)  ? $plist['CFBundleShortVersionString'] :'CFBundleShortVersionString'   ;
            $info['CFBundleShortVersionString'] = array_key_exists('CFBundleShortVersionString', $plist)  ? $plist['CFBundleShortVersionString'] :'CFBundleShortVersionString'   ;
            $info['CFBundleSupportedPlatforms'] = implode(',', $plist['CFBundleSupportedPlatforms']);
            $info['APP_SERVER']               = array_key_exists('APP_SERVER', $plist)                ? $plist['APP_SERVER']               :'APP_SERVER'   ;
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
                    $group         = $group['CFBundleIconFiles'];
                    $iconsGroups[] = $group;
                }
            }
        }

        $original = call_user_func_array('array_merge', $iconsGroups);
        $merged = array_flip($original);
        $names  = [
            'icon-72@2x',
            'icon-76',
            'icon-72',
            'icon-60',
            'icon-50',
            'icon-40',
            'icon@2x',
            'icon-small',
            'icon',
        ];

        $icon = null;

        foreach ($names as $name) {
            if ( ! $icon && array_key_exists($name, $merged)) {
                $icon = $name;
            }
        }


        if(!$icon)   {
            end($original);
            $icon = current($original);
        }


        $iconPath = $this->basePath . '/' . $icon . (strpos($icon, '.png') > 0 ? '' : '.png');

        if(!file_exists($iconPath)){

            $iconPath = $this->basePath . '/' . $icon. (strpos($icon, '.png') > 0 ? '' : '@2x.png');

        }


        return  $iconPath;
    }


    public function unpackImage($path)
    {

        if (PHP_OS == 'Darwin') {
            $pngdefry = 'pngdefry-osx';
        } else {
            //}else if(PHP_OS=='Linux'){
            $pngdefry = 'pngdefry-linux';
        }

        $pipes = [];

        $suffix = microtime(true);

        $fileinfo = pathinfo($path);

        $command =  __DIR__ . '/' . $pngdefry . ' -s ' . $suffix . ' -o ' . $this->tmpDir . ' ' . str_replace(" ", "\\ ",$path);
        $process  = proc_open($command , [], $pipes);

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
        if ( ! is_dir($dirPath)) {
            throw new \InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
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
