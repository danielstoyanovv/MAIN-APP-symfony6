<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SearchService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SearchController extends AbstractController
{
    #[Route('/api/search/{term}', name: 'api_search')]
    public function search(
        EntityManagerInterface $entityManager,
        Request $request,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        SearchService $searchService
    ): Response
    {
        try {
            if ($request->getMethod() === 'POST') {
                $data = json_decode($request->getContent(), true);
                if (!$data) {
                    $response = new JsonResponse("No data is send");
                    $response->setStatusCode(422);

                    return $response;
                } else {
                    $errors = $searchService->processValidate($request->get('term'), $data);
                    if (count($errors) > 0) {
                        $response = new JsonResponse($errors);
                        $response->setStatusCode(422);

                        return $response;
                    } else {
                        if ($users = $searchService->searchUsers($request->get('term'), $data)) {
                            $response = new Response($serializer->serialize($users, 'json'));
                            $response->setStatusCode(200);

                            return  $response;
                        } else {
                            $response = new JsonResponse("No users were found");
                            $response->setStatusCode(200);

                            return $response;
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            $response = new JsonResponse('Something happened');
            $response->setStatusCode(500);
            $logger->error($exception->getMessage());
        }

        return $response;
    }
}
