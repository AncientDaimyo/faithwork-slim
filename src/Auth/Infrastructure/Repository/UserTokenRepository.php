<?php

namespace App\Auth\Infrastructure\Repository;

use App\Shared\Infrastructure\Repository\Repository;

class UserTokenRepository extends Repository
{
    protected string $table = 'user_tokens';

    public function getTokenByUserId($userId)
    {
        return $this->connection->fetchAssociative('SELECT * FROM ' . $this->table . ' WHERE user_id = :user_id', ['user_id' => $userId]);
    }

    public function deleteTokensByUserId($userId)
    {
        return $this->connection->executeStatement('DELETE FROM ' . $this->table . ' WHERE user_id = :user_id', ['user_id' => $userId]);
    }

    public function getByActivationCode(string $activationCode)
    {
        return $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->table, 'ut')
            ->leftJoin('ut', 'users', 'u', 'u.id = ut.user_id')
            ->where('u.activation_code = :activation_code')
            ->setParameter('activation_code', $activationCode)
            ->fetchAllAssociative();
    }

    public function saveToken(int $userId, string $authToken, string $refreshToken = null)
    {
        $sql = <<<SQL
INSERT INTO user_tokens (user_id, auth_token, refresh_token) VALUES (:user_id, :auth_token, :refresh_token)
ON DUPLICATE KEY UPDATE auth_token = :auth_token, refresh_token = :refresh_token
SQL;

        return $this->connection->executeStatement($sql, [
            'user_id' => $userId,
            'auth_token' => $authToken,
            'refresh_token' => $refreshToken,
        ]);
    }

    public function getUserIdByToken(string $token)
    {
        return $this->connection->createQueryBuilder()
            ->select('user_id')
            ->from($this->table, 'ut')
            ->where('ut.auth_token = :token')
            ->setParameter('token', $token)
            ->fetchAllAssociative();
    }
}
