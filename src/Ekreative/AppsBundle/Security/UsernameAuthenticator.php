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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class UsernameAuthenticator implements SimplePreAuthenticatorInterface, \Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface {

    protected $userProvider;

    public function __construct(ApiKeyUserProvider $userProvider) {
        $this->userProvider = $userProvider;
    }

    public function createToken(Request $request, $providerKey) {
        if (!$request->headers->has(self::HEADER_KEY)) {
            throw new BadCredentialsException('No API key found');
        }

        return new PreAuthenticatedToken(
                'anon.', $request->headers->get(self::HEADER_KEY), $providerKey
        );
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey) {
        $apiKey = $token->getCredentials();
        
        $username = $this->userProvider->getUsernameForApiKey($apiKey);
        
        if ($username == null) {
            throw new AuthenticationException(
                sprintf('API Key "%s" does not exist.', $apiKey)
            );
        }

        $user = $this->userProvider->loadUserByUsername($username);

        return new PreAuthenticatedToken(
                $user, $apiKey, $providerKey, $user->getRoles()
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey) {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        
        $response = array(
            'bad credentials'
        );
        return new JsonResponse($response, 403);
    }

}
