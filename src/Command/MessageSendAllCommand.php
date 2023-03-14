<?php

namespace App\Command;

use App\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

#[AsCommand(
    name: 'app:message:send:all',
    description: 'Send message to all users',
)]
class MessageSendAllCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @var ContainerBagInterface
     */
    private $containerBag;

    public function __construct(
        EntityManagerInterface $entityManager,
        NotificationService $notificationService,
        ContainerBagInterface $containerBag,
        string $name = null
    )
    {
        $this->entityManager = $entityManager;
        $this->notificationService = $notificationService;
        $this->containerBag = $containerBag;
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($users =  $this->entityManager->getRepository(User::class)->findAll()) {
            foreach ($users as $user) {
                $this->notificationService->sendSystemMessage(
                    "Emergency message",
                    $user->getId(),
                    $this->containerBag->get('notification_app_url')
                );
            }
        }

        return Command::SUCCESS;
    }
}
