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

    private static $allowedMimeTypes = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'video/mp4', 'application/octet-stream');
    private $filesystem;

    public function __construct(Filesystem $filesystem) {

        $this->filesystem = $filesystem;
    }

    public function upload(UploadedFile $file, $filename, $headers = array()) {
        // Check if the file's mime type is in the list of allowed mime types.
        if (!in_array($file->getClientMimeType(), self::$allowedMimeTypes)) {
            //throw new \InvalidArgumentException(sprintf('Files of type %s are not allowed.', $file->getClientMimeType()));
        }

        // Generate a unique filename based on the date and add file extension of the uploaded file
        
        $adapter = $this->filesystem->getAdapter();

        if (!isset($headers['Content-Type'])) {
            $headers['contentType'] = $file->getClientMimeType();
        }

        $adapter->setMetadata($filename, $headers);

        $adapter->write($filename, file_get_contents($file->getPathname()));
        @unlink($file->getPathname());

        return $filename;
    }

    public function delete($file) {
        $adapter = $this->filesystem->getAdapter();
        $adapter->delete($file);
    }

}
