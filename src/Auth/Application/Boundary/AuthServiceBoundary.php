<?php

namespace App\Auth\Application\Boundary;

use App\Auth\Domain\Entity\Token;
use Slim\Psr7\Request;

interface AuthServiceBoundary
{
    public function register(array $data): void;
    public function login(array $data): Token;
    public function refresh(string $refreshToken): Token;
    public function logout(array $data): void;
    public function activateRegistration(string $activationCode): Token;
    public function auth(string $token): bool;
    public function checkRequest(Request $request): bool;
}
