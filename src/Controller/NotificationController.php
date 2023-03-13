<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use App\Service\NotificationService;
use Symfony\Component\Serializer\SerializerInterface;

class NotificationController extends AbstractController
{
    #[Route('/api/notifications', name: 'api_notifications')]
    public function notifications(Request $request, LoggerInterface $logger,
                                 SerializerInterface $serializer, NotificationService $notificationService): Response
    {
        try {
            if ($request->getMethod() === 'GET') {
                if ($notificatios = $notificationService->getAllNotifications($this->getParameter('notification_app_url'))) {
                    $response = new Response($serializer->serialize($notificatios, 'json'));
                    $response->setStatusCode(200);

                    return $response;
                }
            }
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
        }

        $response = new Response( "Invalid credentials");
        $response->setStatusCode(422);
        return $response;
    }

    #[Route('/api/notification', name: 'api_notification_post')]
    public function notification(Request $request, LoggerInterface $logger,
                                 SerializerInterface $serializer, NotificationService $notificationService): Response
    {
        try {
          if ($request->getMethod() === 'POST') {
                $data = json_decode($request->getContent(), true);
                if ($data) {
                    if (!empty($data['message'])) {
                        $errors = $notificationService->processValidateSendPrivateMessage($data);
                        if (count($errors) > 0) {
                            $response = new JsonResponse($errors);
                            $response->setStatusCode(422);

                            return $response;
                        } else {
                            if ($notification = $notificationService->sendPrivateMessage($data, $this->getParameter('notification_app_url'))) {
                                $response = new Response($serializer->serialize($notification, 'json'));
                                $response->setStatusCode(200);

                                return $response;
                            }
                        }
                    } else {
                        $errors = $notificationService->processValidate($data);
                        if (count($errors) > 0) {
                            $response = new JsonResponse($errors);
                            $response->setStatusCode(422);

                            return $response;
                        } else {
                            if ($notification = $notificationService->createNotification($data, $this->getParameter('notification_app_url'))) {
                                $response = new Response($serializer->serialize($notification, 'json'));
                                $response->setStatusCode(200);

                                return $response;
                            }

                        }
                    }


                } else {
                    $response = new JsonResponse("No data is send");
                    $response->setStatusCode(422);

                    return $response;
                }

            }
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
        }

        $response = new Response( "Invalid credentials");
        $response->setStatusCode(422);
        return $response;
    }

    #[Route('/api/notification/{id}', name: 'api_notification_update')]
    public function notificationUpdate(Request $request, LoggerInterface $logger, SerializerInterface $serializer,
                                       NotificationService $notificationService)
    {
        try {
            if ($request->getMethod() === 'PATCH' && !empty($request->get('id')) ) {
                $data = json_decode($request->getContent(), true);

                if ($data) {
                    $errors = $notificationService->processValidate($data);
                    if (count($errors) > 0) {
                        $response = new JsonResponse($errors);
                        $response->setStatusCode(422);

                        return $response;
                    } else {
                        if ($notification = $notificationService->updateNotification($data, $request->get('id'), $this->getParameter('notification_app_url'))) {
                            $response = new Response($serializer->serialize($notification, 'json'));
                            $response->setStatusCode(200);

                            return $response;
                        }
                    }
                } else {
                    $response = new JsonResponse("No data is send");
                    $response->setStatusCode(422);

                    return $response;
                }

            }
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
        }


        $response = new Response( "Invalid credentials");
        $response->setStatusCode(422);
        return $response;
    }
}