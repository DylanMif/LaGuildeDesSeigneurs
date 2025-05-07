<?php
namespace App\Service;
use App\Entity\User;
interface UserServiceInterface
{
    // Gets the token
    public function getToken(User $user);
    // Finds one by email
    public function findOneByEmail(string $token);
    // Parses the token
    public function parseToken(string $token);
}