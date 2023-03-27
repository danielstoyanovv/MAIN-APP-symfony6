<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RegisterService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    /**
     * @param array $data
     * @return void
     */
    public function processValidate(array $data): void
    {
        if (empty($data['email'])) {
            throw new UnprocessableEntityHttpException("'email' is required");
        } else {
            if (!$this->checkIfEmailisValid($data['email'])) {
                throw new UnprocessableEntityHttpException("'email' is not valid email");
            }
        }

        if ($this->checkIfUserExists($data['email'])) {
            throw new UnprocessableEntityHttpException(sprintf(
                "Email '%s' is registered already",
                $data['email']
            ));
        }

        if (empty($data['firstName'])) {
            throw new UnprocessableEntityHttpException("'firstName' is required");
        }

        if (empty($data['lastName'])) {
            throw new UnprocessableEntityHttpException("'lastName' is required");
        }

        if (empty($data['password'])) {
            throw new UnprocessableEntityHttpException("'password' is required");
        } else {
            if (strlen($data['password']) < 6) {
                throw new UnprocessableEntityHttpException("'password' minimum length is 6 characters");
            }
        }
    }

    /**
     * @param string $email
     * @return bool
     */
    private function checkIfEmailisValid(string $email): bool
    {
        if ($this->validator->validate($email, new Email())->count() > 0) {
            return false;
        }

        return true;
    }

    /**
     * @param string $email
     * @return bool
     */
    private function checkIfUserExists(string $email): bool
    {
        if ($user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email])) {
            return true;
        }

        return false;
    }


    /**
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        $user = new User();
        $user->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setEmail($data['email'])
            ->setRoles(['ROLE_USER'])
            ->setPassword($this->userPasswordHasher->hashPassword(new User(), $data['password']));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
