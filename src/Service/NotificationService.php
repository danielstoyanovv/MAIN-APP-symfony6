<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class NotificationService
{
    public function __construct(private HttpClientInterface $client, private CacheInterface $cache)
    {
    }

    /**
     * @param array $data
     * @return void
     */
    public function processValidate(array $data): void
    {
        if (empty($data['content'])) {
            throw new UnprocessableEntityHttpException("'content' is required field");
        }

        if (empty($data['type'])) {
            throw new UnprocessableEntityHttpException("'type' is required field");
        } else {
            if (!in_array($data['type'], ['system', 'private'])) {
                throw new UnprocessableEntityHttpException("'type' should be: 'system', 'private'");
            }
        }
    }

    /**
     * @param array $data
     * @return void
     */
    public function processValidateSendPrivateMessage(array $data): void
    {
        if (empty($data['to'])) {
            throw  new UnprocessableEntityHttpException("'to' is required field");
        }
    }


    /**
     * @param array $data
     * @param int $id
     * @param string $notificationAppUrl
     * @return mixed|null
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function updateNotification(array $data, int $id, string $notificationAppUrl)
    {
        $notification = null;

        if ($token = $this->getToken($notificationAppUrl)) {
            $notificationResponseJson = $this->client->request(
                "PATCH",
                $this->getNotificationAppApiUrl($notificationAppUrl) . '/' . $id,
                [
                    "headers" => [
                        "Content-Type" => "application/merge-patch+json",
                        "accept" => "application/ld+json",
                        "X-AUTH-TOKEN" => $token
                    ],
                    "json" => [
                        "content" => $data['content'],
                        "type" => $data['type'],
                        "isRead" => $data['isRead'] ?? null
                    ],
                    "verify_peer" => false,
                    "verify_host" => false
                ]
            );
            $notificationData = json_decode($notificationResponseJson->getContent(), true);

            if ($notificationData) {
                $notification =  $notificationData;
            }
        }

        return $notification;
    }

    /**
     * @param array $data
     * @param string $notificationAppUrl
     * @return mixed|null
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function createNotification(array $data, string $notificationAppUrl)
    {
        $notification = null;

        if ($token = $this->getToken($notificationAppUrl)) {
            $notificationResponseJson = $this->client->request(
                "POST",
                $this->getNotificationAppApiUrl($notificationAppUrl),
                [
                    "headers" => [
                        "Content-Type" => "application/ld+json",
                        "accept" => "application/ld+json",
                        "X-AUTH-TOKEN" => $token
                    ],
                    "json" => [
                        "content" => $data['content'],
                        "type" => $data['type'],
                        "userId" => $data['userId'] ?? null

                    ],
                    "verify_peer" => false,
                    "verify_host" => false
                ]
            );
            $notificationData = json_decode($notificationResponseJson->getContent(), true);

            if ($notificationData) {
                $notification =  $notificationData;
            }
        }

        return $notification;
    }

    /**
     * @param string $notificationAppUrl
     * @return mixed|null
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getAllNotifications(string $notificationAppUrl)
    {
        $notifications = null;

        if ($token = $this->getToken($notificationAppUrl)) {
            $notificationResponseJson = $this->client->request(
                "GET",
                $this->getNotificationAppApiUrl($notificationAppUrl),
                [
                    "headers" => [
                        "accept" => "application/ld+json",
                        "X-AUTH-TOKEN" => $token
                    ],
                    "verify_peer" => false,
                    "verify_host" => false
                ]
            );

            $notificationsData = json_decode($notificationResponseJson->getContent(), true);
            if ($notificationsData) {
                $notifications = $notificationsData;
            }
        }

        return $notifications;
    }

    /**
     * @param $apiUrl
     * @return mixed|null
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function getToken($apiUrl)
    {
        $this->cache->get('api_token', function (ItemInterface $item) use ($apiUrl) {
            $token = null;

            $item->expiresAfter(3600);

            $tokenResponseJson = $this->client->request(
                "POST",
                $apiUrl . "/api_token",
                [
                    "headers" => [
                        "Content-Type" => "application/ld+json",
                        "accept" => "application/ld+json"
                    ],
                    "json" => [
                        "email" => "admin@admin.com",
                        "password" => "123456"
                    ],
                    "verify_peer" => false,
                    "verify_host" => false
                ]
            );

            $tokenData = json_decode($tokenResponseJson->getContent(), true);

            if (!empty($tokenData['token'])) {
                $token = $tokenData['token'];
                $item->set($token);
            }

            return $token;
        });

        return $this->cache->getItem('api_token')->get();
    }

    private function getNotificationAppApiUrl($apiUrl)
    {
        return $apiUrl  . '/notifications';
    }

    /**
     * @param array $data
     * @param string $apiUrl
     * @return mixed|null
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function sendPrivateMessage(array $data, string $apiUrl)
    {
        $notificationData = [];
        $notificationData['content'] = $data['message'];
        $notificationData['type'] = 'private';
        $notificationData['userId'] = (int) $data['to'];

        return $this->createNotification($notificationData, $apiUrl);
    }

    /**
     * @param string $content
     * @param int $registeredUserId
     * @param string $apiUrl
     * @return mixed|null
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function sendSystemMessage(string $content, int $registeredUserId, string $apiUrl)
    {
        $notificationData = [];
        $notificationData['content'] = $content;
        $notificationData['userId'] = $registeredUserId;
        $notificationData['type'] = 'system';

        return $this->createNotification($notificationData, $apiUrl);
    }

    /**
     * @param int $userId
     * @param string $apiUrl
     * @return mixed|null
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getUserUnreadMessages(int $userId, string $apiUrl)
    {
        $messages = null;
        if ($token = $this->getToken($apiUrl)) {
            $messagesResponseJson = $this->client->request(
                "GET",
                $this->getNotificationAppApiUrl($apiUrl) . "?userId=" . $userId,
                [
                    "headers" => [
                        "Content-Type" => "application/ld+json",
                        "accept" => "application/ld+json",
                        "X-AUTH-TOKEN" => $token
                    ],
                    "verify_peer" => false,
                    "verify_host" => false
                ]
            );

            if ($messagesData = json_decode($messagesResponseJson->getContent(), true)) {
                $messages = $messagesData;
            }
        }

        return $messages;
    }
}
