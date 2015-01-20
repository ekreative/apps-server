<?php

namespace Ekreative\AppsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller {


    public function indexAction() {
        return $this->render('EkreativeAppsBundle:Main:index.html.twig', array());
    }


}
