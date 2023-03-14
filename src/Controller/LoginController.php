<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\TokenGenerator;
use App\Service\ApiTokenManager;
use Symfony\Component\Serializer\SerializerInterface;

class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login')]
    public function login(
        EntityManagerInterface $entityManager,
        Request $request,
        LoggerInterface $logger,
        UserPasswordHasherInterface $hasher,
        TokenGenerator $tokenGenerator,
        SerializerInterface $serializer,
        ApiTokenManager $apiTokenManager
    ): Response
    {
        try {
            if ($request->getMethod() === 'POST') {
                $entityManager->beginTransaction();
                $data = json_decode($request->getContent(), true);
                if (!empty($data['email']) && !empty($data['password'])) {
                    $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

                    if ($user && $hasher->isPasswordValid($user, $data['password'])) {
                        $tokenData = $apiTokenManager->createApiToken($data, $tokenGenerator, $user);
                        $entityManager->commit();
                        $response = new Response($serializer->serialize($tokenData, 'json'));
                        $response->setStatusCode(200);

                        return $response;
                    } else {
                        $response = new JsonResponse("Invalid credential");
                        $response->setStatusCode(422);

                        return $response;
                    }
                } else {
                    $response = new JsonResponse("'email' and 'password' are required field");
                    $response->setStatusCode(422);

                    return $response;
                }
            }
        } catch (\Exception $exception) {
            $response = new JsonResponse('Something happened, user is not logged in');
            $response->setStatusCode(500);
            $entityManager->rollback();
            $logger->error($exception->getMessage());
        }

        return $response;
    }
}
