<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\TokenGenerator;
use App\Service\ApiTokenManager;

class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login')]
    public function login(
        EntityManagerInterface $entityManager,
        Request $request,
        LoggerInterface $logger,
        UserPasswordHasherInterface $hasher,
        TokenGenerator $tokenGenerator,
        ApiTokenManager $apiTokenManager
    ): Response {
        try {
            if ($request->getMethod() === 'POST') {
                $entityManager->beginTransaction();
                $data = json_decode($request->getContent(), true);
                if (!empty($data['email']) && !empty($data['password'])) {
                    $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

                    if ($user && $hasher->isPasswordValid($user, $data['password'])) {
                        $tokenData = $apiTokenManager->createApiToken($data, $tokenGenerator, $user);
                        $entityManager->commit();
                        return $this->json($tokenData);
                    } else {
                        return $this->json("Invalid credential", Response::HTTP_UNAUTHORIZED);
                    }
                } else {
                    return $this->json("'email' and 'password' are required field", Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        } catch (\Exception $exception) {
            $entityManager->rollback();
            $logger->error($exception->getMessage());
        }

        return $this->json('Invalid credentials', Response::HTTP_FORBIDDEN);
    }
}
