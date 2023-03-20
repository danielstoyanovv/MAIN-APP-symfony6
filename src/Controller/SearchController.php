<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SearchService;
use Psr\Log\LoggerInterface;

class SearchController extends AbstractController
{
    #[Route('/api/search/{term}', name: 'api_search')]
    public function search(
        Request $request,
        LoggerInterface $logger,
        SearchService $searchService
    ): Response {
        try {
            if ($request->getMethod() === 'POST') {
                $data = json_decode($request->getContent(), true);
                if (!$data) {
                    return $this->json("No data is send", Response::HTTP_BAD_REQUEST);
                } else {
                    $errors = $searchService->processValidate($request->get('term'), $data);
                    if (count($errors) > 0) {
                        return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
                    } else {
                        if ($users = $searchService->searchUsers($request->get('term'), $data)) {
                            return $this->json($users);
                        } else {
                            return $this->json("No users were found");
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
        }

        return $this->json('Invalid credentials', Response::HTTP_FORBIDDEN);
    }
}
