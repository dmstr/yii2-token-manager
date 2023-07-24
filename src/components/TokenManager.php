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
 * @property UnencryptedToken $token
 */
class TokenManager extends BaseTokenManager implements TokenManagerStorageInterface
{

    /**
     * Suppress all exceptions
     *
     * @var bool
     */
    public bool $suppressExceptions = true;

    /**
     * @var string
     */
    public string $sessionComponentId = 'session';

    /**
     * @var string
     */
    public string $userComponentId = 'user';

    /**
     * session value identifier (key)
     */
    protected const TOKEN_MANAGER_SESSION_KEY = __CLASS__;

    /**
     * Static storage fallback if user session is disabled
     *
     * @var array
     */
    private static $_storage = [];

    // private properties for internal use
    private User $_user;
    private Session $_session;

    /**
     * @throws InvalidConfigException
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->_user = Instance::ensure($this->userComponentId, User::class);
        $this->_session = Instance::ensure($this->sessionComponentId, Session::class);
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
     * Persist set token in (session) storage
     *
     * @return void
     */
    public function persistTokenInStorage(): void
    {
        if ($this->getUser()->enableSession) {
            $this->getSession()->set(static::TOKEN_MANAGER_SESSION_KEY, $this->_token);
        } else {
          static::$_storage['token'] = $this->_token;
        }
    }

    /**
     * Load saved token from (session) storage
     *
     * @throws LoadTokenException
     * @return bool
     */
    public function loadTokenFromStorage(): bool
    {
        /** @var UnencryptedToken|null $token */
        if ($this->getUser()->enableSession) {
            $token = $this->getSession()->get(static::TOKEN_MANAGER_SESSION_KEY);
        } else {
            $token = static::$_storage['token'];
        }

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
     * @return Session
     */
    protected function getSession(): Session {
        return $this->_session;
    }

    /**
     * @return User
     */
    protected function getUser(): User {
        return $this->_user;
    }

    /**
     * Check if storage is enabled. This could either be session or a static property
     *
     * @return bool
     */
    public function isStorageEnabled(): bool
    {
        if ($this->getUser()->enableSession) {
            return $this->getSession()->getIsActive();
        }
        // use temporary static property for cache
        return true;
    }
}
