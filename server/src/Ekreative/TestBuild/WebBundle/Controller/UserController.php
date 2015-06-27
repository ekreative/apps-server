<?php

namespace Ekreative\TestBuild\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Ekreative\RedmineLoginBundle\Form\LoginType;
use Symfony\Component\Security\Core\Security;

class UserController extends Controller
{
    /**
     * @Route("/user",name="userInfo")
     * @Template()
     */
    public function indexAction()
    {

        // $this->getUser();

        return array('user' => $this->getUser());
    }

}
