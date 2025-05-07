<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\UserServiceInterface;

final class UserController extends AbstractController
{
    public function __construct(
        private UserServiceInterface $userService
    ) {
    }

    #[Route('/signin',
        name: 'app_signin',
        methods: ['POST']
    )]
    public function signin(): JsonResponse
    {
        $user = $this->getUser();
        if(null !== $user) {
            return new JsonResponse([
                'token' => $this->userService->getToken($user),
            ]);
        }
        return new JsonResponse([
            'error' => 'User not found',
        ]);
    }
}
