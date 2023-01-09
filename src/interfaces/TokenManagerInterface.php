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

    /**
     * List of permissions assigned to user via token
     *
     * @param string $name
     * @param $default
     *
     * @return mixed
     */
    public function getClaim(string $name, $default = null): mixed;
}