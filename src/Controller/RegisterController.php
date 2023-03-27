<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\RegisterService;
use App\Service\NotificationService;

class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'api_register')]
    public function register(
        EntityManagerInterface $entityManager,
        Request $request,
        LoggerInterface $logger,
        UserPasswordHasherInterface $hasher,
        RegisterService $registerService,
        NotificationService $notificationService
    ): Response {
        try {
            if ($request->getMethod() === 'POST') {
                $entityManager->beginTransaction();
                $data = json_decode($request->getContent(), true);

                if ($data) {
                    $registerService->processValidate($data);

                    $user = $registerService->createUser($data);
                    $entityManager->commit();

                    $notificationService->sendSystemMessage(
                        "Welcome to our platform",
                        $user->getId(),
                        $this->getParameter('notification_app_url')
                    );

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
