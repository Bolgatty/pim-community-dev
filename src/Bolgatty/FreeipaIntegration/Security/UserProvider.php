<?php

namespace Bolgatty\FreeipaIntegration\Security;

use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Akeneo\UserManagement\Bundle\Security\UserProvider as PimUserProvider;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class UserProvider extends PimUserProvider
{
    private $request;
    
    public function setRequestInstance($request) 
    {
        $this->request = $request;
    }
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->userRepository->findOneByIdentifier($username);
        $username = $this->request->getCurrentRequest()->get('_username'); 
        $password = $this->request->getCurrentRequest()->get('_password'); 

        if (!$user || !$this->authenticateWithFreeIPA($username, $password)) {
            throw new UsernameNotFoundException(sprintf('User with username "%s" does not exist.', $username));
        }

        return $user;
    }

    private function authenticateWithFreeIPA($username, $password)
    {
        $connection = $this->makeConnectionWithFreeIPA();
        $auth = $connection->connection()->authenticate($username, $password);

        return $auth ? true : false;
    }

    private function makeConnectionWithFreeIPA()
    {
        $root = $_SERVER['DOCUMENT_ROOT'];
        $hostname = 'ipa.example.lan';
        $certificate = $root.'/crt/ca.crt';
        try {
            return new \FreeIPA\APIAccess\Main($hostname, $certificate);
        } catch (Exception $e) {
            dump("Error {$e->getCode()}: {$e->getMessage()}");die;
        }
    
    }
}
