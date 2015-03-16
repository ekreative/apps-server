<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiKeyAuthenticator
 *
 * @author vitaliy
 */

namespace Ekreative\AppsBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class UsernameUserProvider extends EntityRepository implements UserProviderInterface {

    private $em = null;

    public function __construct($em) {
        $this->em = $em;
    }

    public function getUsernameForApiKey($apiKey) {
        return $this->em->getRepository('EkreativeTrademenAdminBundle:Device')->getUserAuthToken($apiKey);
    }

    public function loadUserByUsername($username) {
        
        $user = $this->em->getRepository('EkreativeTrademenAdminBundle:BaseUser')->findOneBy(array('username' => $username));

        if ($user) {
            return $user;
        }

        return new User(
                $username, null,
                // the roles for the user - you may choose to determine
                // these dynamically somehow based on the user
                array('ROLE_USER')
        );
    }
    
 
    public function refreshUser(UserInterface $user) {
        // this is used for storing authentication in the session
        // but in this example, the token is sent in each request,
        // so authentication can be stateless. Throwing this exception
        // is proper to make things stateless
        throw new UnsupportedUserException();
    }

    public function supportsClass($class) {
        return 'Ekreative\TrademenAdminBundle\Entity\BaseUser' === $class;
    }

}

