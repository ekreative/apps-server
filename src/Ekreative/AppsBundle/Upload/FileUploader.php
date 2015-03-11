<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ekreative\AppsBundle\Upload;

use Gaufrette\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Description of FileUploader
 *
 * @author av_tehnik
 */
class FileUploader {

    private $filesystem;

    public function __construct(Filesystem $filesystem) {

        $this->filesystem = $filesystem;
    }

    public function upload(UploadedFile $file, $filename, $headers = array()) {
        
        $adapter = $this->filesystem->getAdapter();

        $headers['contentType'] = $file->getClientMimeType();

        $adapter->setMetadata($filename, $headers);
        
        $adapter->write($filename, file_get_contents($file->getPathname()));
        @unlink($file->getPathname());

        return $filename;
    }

    public function delete($file) {
        $adapter = $this->filesystem->getAdapter();
        $adapter->delete($file);
    }

    public function uploadString($content,$filename, $headers = array()) {
        $adapter = $this->filesystem->getAdapter();
        $adapter->setMetadata($filename, $headers);
        $adapter->write($filename, $content);

    }


    
}
