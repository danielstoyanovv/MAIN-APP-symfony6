<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use App\Service\NotificationService;

class NotificationController extends AbstractController
{
    #[Route('/api/notifications', name: 'api_notifications')]
    public function notifications(
        Request $request,
        LoggerInterface $logger,
        NotificationService $notificationService
    ): Response {
        try {
            if ($request->getMethod() === 'GET') {
                if ($notificatios = $notificationService->getAllNotifications($this->getParameter('notification_app_url'))) {
                    return $this->json($notificatios);
                }
            }
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
        }

        return $this->json('Invalid credentials', Response::HTTP_FORBIDDEN);
    }

    #[Route('/api/notification', name: 'api_notification_post')]
    public function notification(
        Request $request,
        LoggerInterface $logger,
        NotificationService $notificationService
    ): Response {
        try {
            if ($request->getMethod() === 'POST') {
                $data = json_decode($request->getContent(), true);

                if ($data) {
                    if (!empty($data['message'])) {
                        $errors = $notificationService->processValidateSendPrivateMessage($data);

                        if (count($errors) > 0) {
                            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
                        }

                        if ($notification = $notificationService->sendPrivateMessage($data, $this->getParameter('notification_app_url'))) {
                            return $this->json($notification, Response::HTTP_CREATED);
                        }
                    }
                    $errors = $notificationService->processValidate($data);

                    if (count($errors) > 0) {
                        return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    if ($notification = $notificationService->createNotification($data, $this->getParameter('notification_app_url'))) {
                        return $this->json($notification, Response::HTTP_CREATED);
                    }
                }

                return $this->json("No data is send", Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
        }

        return $this->json('Invalid credentials', Response::HTTP_FORBIDDEN);
    }

    #[Route('/api/notification/{id}', name: 'api_notification_update')]
    public function notificationUpdate(
        Request $request,
        LoggerInterface $logger,
        NotificationService $notificationService
    ) {
        try {
            if ($request->getMethod() === 'PATCH' && !empty($request->get('id'))) {
                $data = json_decode($request->getContent(), true);

                if ($data) {
                    $errors = $notificationService->processValidate($data);

                    if (count($errors) > 0) {
                        return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    if ($notification = $notificationService->updateNotification($data, $request->get('id'), $this->getParameter('notification_app_url'))) {
                        return $this->json($notification);
                    }
                }
                return $this->json("No data is send", Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
        }

        return $this->json('Invalid credentials', Response::HTTP_FORBIDDEN);
    }
}
