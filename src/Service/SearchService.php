<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class SearchService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param string $term
     * @param array $data
     * @return array
     */
    public function processValidate(string $term, array $data): array
    {
        $errors = [];

        if (empty($term)) {
            $errors[] = "'term' is required, example: '/api/search/email'";
        } else {
            if (!in_array(
                $term,
                [
                    'email',
                    'firstName',
                    'lastName'
                ]
            )) {
                $errors[] = "allowed terms are: 'email', 'firstName', 'lastName'";
            }

            match ($term) {
                "email" => empty($data['email']) ? $errors[] = "'email' is not supported" : null,
                "firstName" => empty($data['firstName']) ? $errors[] = "'firstName' is not supported" : null,
                "lastName" => empty($data['lastName']) ? $errors[] = "'lastName' is not supported" : null
            };
        }

        return $errors;
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
