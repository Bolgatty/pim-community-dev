<?php
namespace Bolgatty\WorkFlowBundle\Component\Authorization;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

/**
 * Decorates the authorization checker from symfony to be able to cache results.
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class AuthorizationChecker implements AuthorizationCheckerInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var array */
    private $cachedResults;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface         $tokenStorage
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->cachedResults = [];
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($attributes, $object = null): bool
    {
        if (null === $object) {
            return $this->authorizationChecker->isGranted($attributes, $object);
        }

        $index = $this->getArgumentsAsIndex($attributes, $object);
        if (!array_key_exists($index, $this->cachedResults)) {
            $isGranted = $this->authorizationChecker->isGranted($attributes, $object);
            $this->cachedResults[$index] = $isGranted;
        }

        return $this->cachedResults[$index];
    }

    /**
     * Returns arguments as a string
     *
     * @param array|string $attributes
     * @param mixed        $object
     *
     * @return string
     */
    private function getArgumentsAsIndex($attributes, $object): string
    {
        $userId = $this->getCurrentUserAsIndex();
        $attributesAsIndex = $this->getAttributesAsIndex($attributes);
        $objectAsIndex = $this->getObjectAsIndex($object);

        return sprintf('%s_%s_%s', $userId, $attributesAsIndex, $objectAsIndex);
    }

    /**
     * Get object as string to build the cache index.
     * We use the hash of the serialized object as a key because voters results are often based on object
     * content (properties), so when the content of an object changes, its cache key must be different.
     *
     * To avoid to serialize too big objects (for instance a product), we normalize them before.
     *
     * @param mixed $object
     *
     * @return string
     */
    private function getObjectAsIndex($object): string
    {
        if (is_object($object)) {
            try {
                // $normalizer = $this->normalizer->normalize($object, 'authorization');
            } catch (NotNormalizableValueException $e) {
                $normalizer = $object;
            }

            // return md5(serialize($normalizer));
        }

        return (string) $object;
    }

    /**
     * Get attributes as string to build the cache index.
     *
     * @param string|array $attributes
     *
     * @return string
     */
    private function getAttributesAsIndex($attributes): string
    {
        if (is_array($attributes)) {
            sort($attributes);
            $attributes = implode('_', $attributes);
        }

        return $attributes;
    }

    /**
     * Returns the current user index.
     *
     * @return string
     */
    private function getCurrentUserAsIndex(): string
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return sprintf('%s_%s', $user->getUsername(), (string) $user->getId());
    }
}
