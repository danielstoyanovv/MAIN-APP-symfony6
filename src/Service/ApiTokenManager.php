<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\ApiToken;
use App\Entity\User;

class ApiTokenManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $data
     * @param TokenGenerator $tokenGenerator
     * @param User $user
     * @return ApiToken
     */
    public function createApiToken(array $data, TokenGenerator $tokenGenerator, User $user): ApiToken
    {
        $expiredDatetime = new \DateTime();
        $expiredDatetime
            ->setTimezone(new \DateTimeZone('Europe/Sofia'))
            ->add(new \DateInterval('P1D'));

        $tokenData = new ApiToken();
        $tokenData
            ->setToken($tokenGenerator->generate())
            ->setExpiresAt($expiredDatetime);
        if ($currentToken = $user->getApiToken()) {
            $user->setApiToken(null);
            $this->entityManager->remove($currentToken);
            $this->entityManager->flush();
        }
        $user->setApiToken($tokenData);

        $this->entityManager->persist($tokenData);
        $this->entityManager->flush();
        unset($tokenData->email);
        unset($tokenData->password);

        return $tokenData;

    }
}