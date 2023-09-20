<?php

namespace dmstr\tokenManager\components;

use dmstr\tokenManager\exceptions\LoadTokenException;
use dmstr\tokenManager\interfaces\TokenManagerStorageInterface;
use Lcobucci\JWT\UnencryptedToken;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\web\Session;
use yii\web\User;

/**
 * @property-read null|\yii\web\User $user
 * @property-read array $roles
 * @property-read \yii\web\Session|null $session
 * @property UnencryptedToken $token
 */
class ConsoleTokenManager extends BaseTokenManager implements TokenManagerStorageInterface
{

    /**
     * Suppress all exceptions
     *
     * @var bool
     */
    public bool $suppressExceptions = true;

    /**
     * session value identifier (key)
     */
    public string $tokenManagerSessionKey = __CLASS__;

    /**
     * Static storage fallback if user session is disabled
     *
     * @var array
     */
    private static array $_storage = [];

    /**
     * @return void
     */
    public function init()
    {
        parent::init();
    }

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
        if ($this->isStorageEnabled() && $this->loadTokenFromStorage()) {
            return parent::getRoles();
        }
        return [];
    }

    /**
     * @inheritdoc
     * @throws LoadTokenException
     */
    public function getClaim(string $name, $default = null): mixed
    {
        if ($this->isStorageEnabled() && $this->loadTokenFromStorage()) {
            return parent::getClaim($name, $default);
        }
        return $default;
    }

    /**
     * @inheritdoc
     * @throws LoadTokenException
     */
    public function getToken(): UnencryptedToken
    {
        if ($this->isStorageEnabled() && !$this->loadTokenFromStorage()) {
            throw new LoadTokenException('Error while loading token data');
        }
        return parent::getToken();
    }

    /**
     * Persist set token in storage
     *
     * @return void
     */
    public function persistTokenInStorage(): void
    {
        static::$_storage['token'] = $this->_token;
    }

    /**
     * Load saved token from (session) storage
     *
     * @return bool
     * @throws LoadTokenException
     */
    public function loadTokenFromStorage(): bool
    {
        /** @var UnencryptedToken|null $token */
        $token = static::$_storage['token'] ?? null;
        if ($token instanceof UnencryptedToken) {
            $this->setToken($token);
            return true;
        } else {
            if (!$this->suppressExceptions) {
                throw new LoadTokenException();
            }
        }
        return false;
    }

    /**
     * Storage always enabled in console applications
     *
     * @return bool
     */
    public function isStorageEnabled(): bool
    {
        return true;
    }
}
