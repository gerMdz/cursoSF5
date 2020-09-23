<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
//use App\Exception\User\UserAlreadyExistException;
//use App\Messenger\Message\UserRegisteredMessage;
//use App\Messenger\RoutingKey;
use App\Exception\User\UserAlreadyExistException;
use App\Repository\UserRepository;
use App\Service\Password\EncoderService;
use App\Service\Request\RequestService;
use Doctrine\DBAL\DBALException;
//use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;

//use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
//use Symfony\Component\Messenger\MessageBusInterface;

class UserRegisterService
{
    private UserRepository $userRepository;
    private EncoderService $encoderService;
//    private MessageBusInterface $messageBus;

    public function __construct(
        UserRepository $userRepository,
        EncoderService $encoderService

    ) {
        $this->userRepository = $userRepository;
        $this->encoderService = $encoderService;

    }

    public function create(Request $request): User
    {

        $name = RequestService::getField($request, 'name', true,false );
        $email = RequestService::getField($request, 'email', true, false );
        $password = RequestService::getField($request, 'password', true, false );

        $user = new User($name, $email);
        $user->setPassword($this->encoderService->generateEncodedPassword($user, $password));

        try {
            $this->userRepository->save($user);
        } catch (DBALException $e) {
            throw UserAlreadyExistException::fromEmail($email);
        }

//        $this->messageBus->dispatch(
//            new UserRegisteredMessage($user->getId(), $user->getName(), $user->getEmail(), $user->getToken()),
//            [new AmqpStamp(RoutingKey::USER_QUEUE)]
//        );

        return $user;
    }
}
