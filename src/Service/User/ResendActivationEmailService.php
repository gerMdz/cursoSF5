<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Exception\User\UserIsActiveException;
use App\Messenger\Message\UserRegisteredMessage;
use App\Messenger\RoutingKey;
use App\Repository\UserRepository;
use App\Service\Request\RequestService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use function sha1;
use function uniqid;

class ResendActivationEmailService
{
    private UserRepository $userRepository;
    private MessageBusInterface $messageBus;

    public function __construct(UserRepository $userRepository, MessageBusInterface $messageBus)
    {
        $this->userRepository = $userRepository;
        $this->messageBus = $messageBus;
    }

    /**
     * @param Request $request
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function resend(Request $request): void
    {


        $email = RequestService::getField($request, 'email');
        $user = $this->userRepository->findOneByEmailOrFail($email);

        if ($user->isActive()) {
            throw UserIsActiveException::fromEmail($email);
        }

        $user->setToken(sha1(uniqid()));
        $this->userRepository->save($user);

        $this->messageBus->dispatch(
            new UserRegisteredMessage((string)$user->getId(), $user->getName(), $user->getEmail(), $user->getToken()),
            [new AmqpStamp(RoutingKey::USER_QUEUE)]
        );
    }
}
