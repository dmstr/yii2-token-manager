<?php

namespace dmstr\tokenManager\components;

use Lcobucci\JWT\UnencryptedToken;
use dmstr\tokenManager\exceptions\LoadTokenException;
use dmstr\tokenManager\interfaces\TokenManagerStorageInterface;
use Yii;

/**
 * @property UnencryptedToken $token
 */
class TokenManager extends BaseTokenManager implements TokenManagerStorageInterface
{

    /**
     * session value identifier (key)
     */
    protected const TOKEN_MANAGER_SESSION_KEY = __CLASS__;

    /**
     * @inheritdoc
     */
    public function setToken(UnencryptedToken $token): void
    {
        parent::setToken($token);

        if ($this->isStorageEnabled()) {
            $this->persistTokenInStorage();
        }
    }

    /**
     * @inheritdoc
     *
     * @throws LoadTokenException if storage is enabled and token load failed
     */
    public function getRoles(): array
    {
        if ($this->isStorageEnabled()) {
            $this->loadTokenFromStorage();
        }

        return parent::getRoles();
    }

    public function getClaim(string $name, $default = null): mixed
    {
        if ($this->isStorageEnabled()) {
            $this->loadTokenFromStorage();
        }

        return parent::getClaim($name, $default);
    }

    /**
     * Persist set token in (session) storage
     *
     * @return void
     */
    public function persistTokenInStorage(): void
    {
        Yii::$app->getSession()->set(static::TOKEN_MANAGER_SESSION_KEY, $this->_token);
    }

    /**
     * Load saved token from (session) storage
     *
     * @throws LoadTokenException
     * @return void
     */
    public function loadTokenFromStorage(): void
    {
        /** @var UnencryptedToken|null $token */
        $token = Yii::$app->getSession()->get(static::TOKEN_MANAGER_SESSION_KEY);
        if ($token instanceof UnencryptedToken) {
            $this->setToken($token);
        } else {
            throw new LoadTokenException();
        }
    }

    /**
     * Check whether the user session is enabled / disabled
     *
     * @return bool
     */
    public function isStorageEnabled(): bool
    {
        return Yii::$app->getUser()->enableSession;
    }
}