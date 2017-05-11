<?php
/**
 * Created by PhpStorm.
 * User: Olivier
 * Date: 11/05/2017
 * Time: 14:59
 */

namespace Mukadi\APIAccessBundle\Model;


use Mukadi\APIAccessBundle\Security\ClientProvider;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

abstract class ApiAuthenticator implements SimplePreAuthenticatorInterface,AuthenticationFailureHandlerInterface{

    const REQUEST_AUTHENTICATED = 0;
    const UNKNOWN_REQUEST_SENDER = 1;
    const REQUEST_FAIL_AUTHENTICATED = 2;
    const UNSIGNED_REQUEST = 3;

    /**
     * @var ClientManagerInterface
     */
    protected $manager;

    public function __construct(ClientManagerInterface $manager){
        $this->manager = $manager;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof ClientProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of ApiClientProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }
        $apiKey = $token->getCredentials();
        /** @var Client $client */
        $client = $userProvider->loadUserByUsername($apiKey);

        return new PreAuthenticatedToken(
            $client,
            $apiKey,
            $providerKey,
            $client->getRoles()
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }


} 