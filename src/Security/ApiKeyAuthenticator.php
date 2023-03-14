<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use App\Entity\ApiToken;
use App\Entity\User;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    /**
     * @var EntityManagerInterface;
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function supports(Request $request): ?bool
    {
        if ($request->getRequestUri() === '/api/login' && $request->getMethod() === 'POST') {
            return false;
        } elseif ($request->getRequestUri() === '/api/register' && $request->getMethod() === 'POST') {
            return false;
        }

        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function authenticate(Request $request): Passport
    {
        if ($request->headers->has('X-AUTH-TOKEN') === false) {
            throw new UserNotFoundException();
        }

        if ($apiTokenString = $request->headers->get('X-AUTH-TOKEN')) {
            return new Passport(
                new UserBadge($apiTokenString, function ($userIdentifier) use ($apiTokenString) {
                    $token = $this->entityManager->getRepository(ApiToken::class)->findOneBy(['token' => $apiTokenString]);
                    $user = $this->entityManager->getRepository(User::class)->findOneBy(['apiToken' => $token]);

                    if ($user === null) {
                        throw new UserNotFoundException();
                    }

                    return $user;
                }),
                new CustomCredentials(function ($credentials, UserInterface $user) {
                    if (!empty($user->getApiToken()) && $user->getApiToken()->hasExpired() === false) {
                        return true;
                    }

                    return false;
                }, $apiTokenString),
                []
            );
        }

        throw new UserNotFoundException();
    }
}
