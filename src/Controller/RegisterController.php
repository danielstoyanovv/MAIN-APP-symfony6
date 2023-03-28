<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use App\Service\RegisterService;
use App\Event\UserRegisteredEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'api_register')]
    public function register(
        EntityManagerInterface $entityManager,
        Request $request,
        LoggerInterface $logger,
        RegisterService $registerService,
        EventDispatcherInterface $eventDispatcher
    ): Response {
        try {
            if ($request->getMethod() === 'POST') {
                $entityManager->beginTransaction();
                $data = json_decode($request->getContent(), true);

                if ($data) {
                    $registerService->processValidate($data);
                    $user = $registerService->createUser($data);
                    $entityManager->commit();
                    $event = new UserRegisteredEvent($user);
                    $eventDispatcher->dispatch($event, UserRegisteredEvent::NAME);

                    return $this->json($user);
                }
                return $this->json("No data is send", Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $exception) {
            $entityManager->rollback();
            $logger->error($exception->getMessage());
            return $this->json($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json('Invalid credentials', Response::HTTP_FORBIDDEN);
    }
}
