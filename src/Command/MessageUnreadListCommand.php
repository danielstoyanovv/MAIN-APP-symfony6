<?php

namespace App\Command;

use App\Entity\User;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

#[AsCommand(
    name: 'app:message:unread:list',
    description: 'Get all unread messages for a specific user',
)]
class MessageUnreadListCommand extends Command
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

    public function __construct(EntityManagerInterface $entityManager, NotificationService $notificationService,
                                ContainerBagInterface $containerBag, string $name = null)
    {
        $this->entityManager = $entityManager;
        $this->notificationService = $notificationService;
        $this->containerBag = $containerBag;
        parent::__construct($name);
    }
    protected function configure(): void
    {
        $this
            ->addArgument('user_id', InputArgument::REQUIRED, 'Argument description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('user_id');

        if ($id) {
            if (!$user = $this->entityManager->getRepository(User::class)->find($id)) {
                $io->error("This user did not exists");
                return Command::FAILURE;
            }

            $message = $this->notificationService->getUserUnreadMessages(
                $user->getId(),
                $this->containerBag->get('notification_app_url')
            );
            dd($message);
            return Command::SUCCESS;
        }
        return Command::SUCCESS;
    }
}
