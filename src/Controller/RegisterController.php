<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\RegisterService;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\NotificationService;

class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'api_register')]
    public function register(
        EntityManagerInterface $entityManager,
        Request $request,
        LoggerInterface $logger,
        UserPasswordHasherInterface $hasher,
        SerializerInterface $serializer,
        RegisterService $registerService,
        NotificationService $notificationService
    ): Response
    {
        try {
            if ($request->getMethod() === 'POST') {
                $entityManager->beginTransaction();
                $data = json_decode($request->getContent(), true);

                if ($data) {
                    $errors = $registerService->processValidate($data);

                    if (count($errors) > 0) {
                        $response = new JsonResponse($errors);
                        $response->setStatusCode(422);

                        return $response;
                    }

                    $user = $registerService->createUser($data);
                    $entityManager->commit();

                    $notificationService->sendSystemMessage(
                        "Welcome to our platform",
                        $user->getId(),
                        $this->getParameter('notification_app_url')
                    );

                    $response = new Response($serializer->serialize($user, 'json'));
                    $response->setStatusCode(200);

                    return $response;
                } else {
                    $response = new JsonResponse("No data is send");
                    $response->setStatusCode(422);

                    return $response;
                }
            }
        } catch (\Exception $exception) {
            $response = new JsonResponse('Something happened, user is not created');
            $response->setStatusCode(500);
            $entityManager->rollback();
            $logger->error($exception->getMessage());
        }

        return $response;
    }
}
