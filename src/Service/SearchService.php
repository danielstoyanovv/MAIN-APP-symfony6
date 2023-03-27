<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SearchService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param string $term
     * @param array $data
     * @return void
     */
    public function processValidate(string $term, array $data): void
    {
        if (empty($term)) {
            throw new UnprocessableEntityHttpException("'term' is required, example: '/api/search/email'");
        } else {
            if (!in_array(
                $term,
                [
                    'email',
                    'firstName',
                    'lastName'
                ]
            )) {
                throw new UnprocessableEntityHttpException("allowed terms are: 'email', 'firstName', 'lastName'");
            }

            match ($term) {
                "email" => empty($data['email']) ? throw new
                UnprocessableEntityHttpException("'email' is not supported") : null,
                "firstName" => empty($data['firstName']) ? throw new
                UnprocessableEntityHttpException("'firstName' is not supported") : null,
                "lastName" => empty($data['lastName']) ? throw new
                    UnprocessableEntityHttpException("'lastName' is not supported") : null
            };
        }
    }

    /**
     * @param string $term
     * @param array $data
     * @return User[]|array|object[]
     */
    public function searchUsers(string $term, array $data)
    {
        $users = [];
        match ($term) {
            "email" => $users = $this->entityManager->getRepository(User::class)->findBy(['email' => $data['email']]),
            "firstName" => $users = $this->entityManager->getRepository(User::class)->findBy(['firstName' => $data['firstName']]),
            "lastName" => $users = $this->entityManager->getRepository(User::class)->findBy(['lastName' => $data['lastName']])
        };

        return $users;
    }
}
