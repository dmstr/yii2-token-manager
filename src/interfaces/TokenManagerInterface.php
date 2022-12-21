<?php

namespace dmstr\tokenManager\interfaces;

use Lcobucci\JWT\UnencryptedToken;

interface TokenManagerInterface
{
    /**
     * @return UnencryptedToken
     */
    public function getToken(): UnencryptedToken;

    /**
     * @param UnencryptedToken $token
     */
    public function setToken(UnencryptedToken $token): void;

    /**
     * List of roles assigned to user via token
     *
     * @return array
     */
    public function getRoles(): array;
}