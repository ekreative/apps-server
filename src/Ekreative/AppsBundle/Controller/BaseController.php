<?php

namespace Ekreative\AppsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaseController extends Controller {

    const SESSION_KEY = 'folder_type';
    const FOLDER_ANDROID = 'Android';
    const FOLDER_IOS = 'Ios';

    protected function getCurrentFolderType() {
        $session = $this->get('session');
        return $session->get(self::SESSION_KEY, self::FOLDER_ANDROID);
    }

    /**
     * 
     * @return Sony\BackendBundle\Upload\FileUploader
     */
    protected function getFileUploader() {
        return $this->get('ekreative_apps.file_uploader');
    }
    
    
    protected function serveLink($currentFolderType) {

        return 'ekreative_folder_' . strtolower($currentFolderType) . '_index';
    }


}