<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class SearchService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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

            switch ($term) {
                case "email":
                    if (empty($data['email'])) {
                        $errors[] = "'email' is not supported";
                    }
                    break;
                case "firstName":
                    if (empty($data['firstName'])) {
                        $errors[] = "'firstName is not supported";
                    }
                    break;
                case "lastName":
                    if (empty($data['lastName'])) {
                        $errors[] = "'lastName is not supported";
                    }
                    break;
            }
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
        switch ($term) {
            case "email":
                $users = $this->entityManager->getRepository(User::class)->findBy(['email' => $data['email']]);
                break;
            case "firstName":
                $users = $this->entityManager->getRepository(User::class)->findBy(['firstName' => $data['firstName']]);
                break;
            case "lastName":
                $users = $this->entityManager->getRepository(User::class)->findBy(['lastName' => $data['lastName']]);
                break;
        }

        return $users;
    }
}
