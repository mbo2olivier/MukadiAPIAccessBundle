<?php
/**
 * Created by PhpStorm.
 * User: Olivier
 * Date: 08/05/2017
 * Time: 15:38
 */

namespace Mukadi\APIAccessBundle\Security;


use Mukadi\APIAccessBundle\Model\Client;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Mukadi\APIAccessBundle\Model\ClientManagerInterface;

class ClientProvider implements UserProviderInterface {

    /**
     * @var ClientManagerInterface
     */
    protected $manager;

    /**
     * @param ClientManagerInterface $manager
     */
    function __construct(ClientManagerInterface $manager)
    {
        $this->manager = $manager;
    }


    public function loadUserByUsername($username)
    {
        $client = $this->manager->find($username);

        if(!$client){
            throw new UsernameNotFoundException(
                sprintf('ID "%s" does not exist.', $username)
            );
        }

        return $client;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof Client) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === $this->manager->getClass();
    }


} 