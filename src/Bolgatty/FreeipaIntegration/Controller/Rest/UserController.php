<?php

namespace Bolgatty\FreeipaIntegration\Controller\Rest;

use Akeneo\UserManagement\Bundle\Controller\Rest\UserController as MainUserController;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * User rest controller *
 * @author  Firoj Ahmad <firojahmad07@gmail.com>
 */
class UserController extends MainUserController
{

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $user = $this->factory->create();
        $content = json_decode($request->getContent(), true);
        $passwordViolations = $this->validatePasswordCreate($content);
        
        $freeIpaViolations = $this->validateUserWithFreeIPA($content);        
        unset($content['password_repeat']);

        $this->updater->update($user, $content);

        $violations = $this->validator->validate($user);

        if ($violations->count() > 0 || $passwordViolations->count() > 0 || $freeIpaViolations->count() > 0) {
            $normalizedViolations = [];
            foreach ($violations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['user' => $user]
                );
            }
            foreach ($passwordViolations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['user' => $user]
                );
            }
            foreach ($freeIpaViolations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['user' => $user]
                );
            }

            return new JsonResponse(['values' => $normalizedViolations], Response::HTTP_BAD_REQUEST);
        }

        $this->saver->save($user);

        return new JsonResponse($this->normalizer->normalize($user, 'internal_api'));
    }

    /**
     * @param array $data
     *
     * @return ConstraintViolationListInterface
     */
    private function validatePasswordCreate(array $data): ConstraintViolationListInterface
    {
        $violations = [];

        if (!isset($data['password'])) {
            return new ConstraintViolationList([]);
        }

        if (($data['password_repeat'] ?? '') !== $data['password']) {
            $violations[] = new ConstraintViolation('Passwords do not match', '', [], '', 'password_repeat', '');
        }

        return new ConstraintViolationList($violations);
    }


    /**
     * @param [] 
     * 
     * @return ConstraintViolationListInterface
     */
    private function validateUserWithFreeIPA(array $data): ConstraintViolationListInterface
    {

        $repeatPassword = isset($data['password_repeat']) ? $data['password_repeat'] : "";
        $password       = isset($data['password']) ? $data['password'] : "";
        $username       = isset($data['username']) ? $data['username'] : "";
        $violations     = [];
        if (!empty($repeatPassword) && !empty($password)) {
            $password = ($repeatPassword == $password ) ? $password : "";
            $ipaConnection = $this->connect();
            $auth = $ipaConnection->connection()->authenticate($username, $password);
            if (!$auth) {
                $violations[] = new ConstraintViolation('Invalid user details(Authentication failed with FreeIPA).', '', 
                    [], '', 'username', '');
            }

        }
        
        return new ConstraintViolationList($violations);

        
    }
    
    private function connect()
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
