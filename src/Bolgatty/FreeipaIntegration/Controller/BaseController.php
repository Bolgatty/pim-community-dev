<?php

namespace Bolgatty\FreeipaIntegration\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use LdapRecord\Connection;
use LdapRecord\Auth\BindException;
use LdapRecord\Models\ActiveDirectory\User;
/**
 * Configuration rest controller in charge of the shopify connector configuration managements
 */
class BaseController extends Controller
{   
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function connectWithLdap()
    {
        $ipa = $this->connect();
        // $user = $connection->find('cn=admin,dc=com');
        // dump($user);die;
        $user = "admin";
        $pass = 'firoj123';
        $auth = $ipa->connection()->authenticate($user, $pass);
        // $auth_info = $ipa->connection()->getAuthenticationInfo();
        $user_info = $ipa->user()->get('admin');
        dump($user_info);
        dump("reached here");die;
    }


    public function connect()
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