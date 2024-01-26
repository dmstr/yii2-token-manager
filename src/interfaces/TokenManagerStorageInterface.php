<?php

namespace dmstr\tokenManager\interfaces;

interface TokenManagerStorageInterface extends TokenManagerInterface
{
    /**
     * Persist set token in (session) storage
     *
     * @return void
     */
    public function persistTokenInStorage($type): void;

    /**
     * Load saved token from (session) storage
     *
     * @param $type
     * @return bool
     */
    public function loadTokenFromStorage($type): bool;

    /**
     * Check whether the storage is enabled / disabled
     *
     * @return bool
     */
    public function isStorageEnabled(): bool;
}