<?php

namespace dmstr\tokenManager\interfaces;

interface TokenManagerStorageInterface extends TokenManagerInterface
{
    /**
     * Persist set token in (session) storage
     *
     * @return void
     */
    public function persistTokenInStorage(): void;

    /**
     * Load saved token from (session) storage
     *
     * @return bool
     */
    public function loadTokenFromStorage(): bool;

    /**
     * Check whether the storage is enabled / disabled
     *
     * @return bool
     */
    public function isStorageEnabled(): bool;
}