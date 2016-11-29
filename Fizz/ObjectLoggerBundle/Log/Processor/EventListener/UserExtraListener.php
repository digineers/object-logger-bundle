<?php

namespace Fizz\ObjectLoggerBundle\Log\Processor\EventListener;

use Fizz\ObjectLoggerBundle\Log\Processor\Event\EntityLogExtraEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Adds current user to extra data
 *
 * @author Richard Snijders <richard@fizz.nl>
 */
class UserExtraListener
{

    /**
     * @var TokenStorageInterface $tokenStorage
     */
    private $tokenStorage;


    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Adds extra data
     *
     * @param EntityLogExtraEvent $event
     */
    public function onExtra(EntityLogExtraEvent $event)
    {
        $token = $this->tokenStorage->getToken();
        if((null === $token || null === $user = $token->getUser()) || !$user instanceof UserInterface) {
            return;
        }
        $event->addExtra('user', $user->getUsername());
    }

}