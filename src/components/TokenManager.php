<?php

namespace dmstr\tokenManager\components;

use dmstr\tokenManager\exceptions\LoadTokenException;
use dmstr\tokenManager\interfaces\TokenManagerStorageInterface;
use Lcobucci\JWT\UnencryptedToken;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\web\Session;
use yii\web\User;
use Yii;

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
    public string $tokenManagerSessionKey = __CLASS__;

    /**
     * session key for id_token
     * @var string
     */
    public string $tokenManagerSessionKey_id = __CLASS__ . "_token_id";

    /**
     * session key for refresh_token
     * @var string
     */
    public string $tokenManagerSessionKey_refresh = __CLASS__ . "_token_refresh";

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
     * @return void
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->_user = Instance::ensure($this->userComponentId, User::class);
        $this->_session = Instance::ensure($this->sessionComponentId, Session::class);
    }

    /**
     * Convenience method to set all 3 tokens at the same time.
     * @param UnencryptedToken $token
     * @param UnencryptedToken $id_token
     * @param UnencryptedToken $refresh_token
     * @return void
     */
    public function setTokens(UnencryptedToken $token, UnencryptedToken $id_token, UnencryptedToken $refresh_token): void
    {
        self::setToken($token);
        self::setIdToken($id_token);
        self::setRefreshToken($refresh_token);
    }

    /**
     * @inheritdoc
     */
    public function setToken(UnencryptedToken $token): void
    {
        parent::setToken($token);
        $this->persistTokenInStorage();
    }

    /**
     * @inheritdoc
     */
    public function setIdToken(UnencryptedToken $token): void
    {
        parent::setIdToken($token);
        $this->persistTokenInStorage(self::TYPE_TOKEN_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRefreshToken(UnencryptedToken $token): void
    {
        parent::setRefreshToken($token);
        $this->persistTokenInStorage(self::TYPE_TOKEN_REFRESH);
    }

    /**
     * @inheritdoc
     *
     * @throws LoadTokenException if storage is enabled and token load failed
     */
    public function getRoles(): array
    {
        if ($this->loadTokenFromStorage()) {
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
        if ($this->loadTokenFromStorage()) {
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
        if (!$this->loadTokenFromStorage()) {
            throw new LoadTokenException('Error while loading token data');
        }
        return parent::getToken();
    }

    /**
     * @throws LoadTokenException
     */
    public function getIdToken(): UnencryptedToken
    {
        if (!$this->loadTokenFromStorage(self::TYPE_TOKEN_ID)) {
            throw new LoadTokenException('Error while loading id token data');
        }
        return parent::getIdToken();
    }

    /**
     * @throws LoadTokenException
     */
    public function getRefreshToken(): UnencryptedToken
    {
        if (!$this->loadTokenFromStorage(self::TYPE_TOKEN_REFRESH)) {
            throw new LoadTokenException('Error while loading refresh token data');
        }
        return parent::getRefreshToken();
    }

    /**
     * Persist set token in (session) storage
     *
     */
    public function persistTokenInStorage($type = self::TYPE_TOKEN): void
    {
        if ($this->isStorageEnabled()) {
            switch ($type) {
                case self::TYPE_TOKEN:
                    $this->getSession()->set($this->tokenManagerSessionKey, $this->_token);
                    break;
                case self::TYPE_TOKEN_ID:
                    $this->getSession()->set($this->tokenManagerSessionKey_id, $this->_token_id);
                    break;
                case self::TYPE_TOKEN_REFRESH:
                    $this->getSession()->set($this->tokenManagerSessionKey_refresh, $this->_token_refresh);
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_TOKEN:
                    static::$_storage[self::TYPE_TOKEN] = $this->_token;
                    break;
                case self::TYPE_TOKEN_ID:
                    static::$_storage[self::TYPE_TOKEN_ID] = $this->_token_id;
                    break;
                case self::TYPE_TOKEN_REFRESH:
                    static::$_storage[self::TYPE_TOKEN_REFRESH] = $this->_token_refresh;
                    break;
            }
        }
    }

    /**
     * Load saved token (Only Access Token) from (session) storage
     *
     * @return bool
     * @throws LoadTokenException
     */
    public function loadTokenFromStorage($type = self::TYPE_TOKEN): bool
    {
        /** @var UnencryptedToken|null $token */
        if ($this->isStorageEnabled()) {
            switch ($type) {
                case self::TYPE_TOKEN:
                    $token = $this->getSession()->get($this->tokenManagerSessionKey);
                    break;
                case self::TYPE_TOKEN_ID:
                    $token = $this->getSession()->get($this->tokenManagerSessionKey_id);
                    break;
                case self::TYPE_TOKEN_REFRESH:
                    $token = $this->getSession()->get($this->tokenManagerSessionKey_refresh);
                    break;
            }
        } else {
            switch ($type) {
                case self::TYPE_TOKEN:
                    $token = static::$_storage[self::TYPE_TOKEN] ?? null;
                    break;
                case self::TYPE_TOKEN_ID:
                    $token = static::$_storage[self::TYPE_TOKEN_ID] ?? null;
                    break;
                case self::TYPE_TOKEN_REFRESH:
                    $token = static::$_storage[self::TYPE_TOKEN_REFRESH] ?? null;
                    break;
            }
        }
        if ($token instanceof UnencryptedToken) {
            switch ($type) {
                case self::TYPE_TOKEN:
                    $this->setToken($token);
                    break;
                case self::TYPE_TOKEN_ID:
                    $this->setIdToken($token);
                    break;
                case self::TYPE_TOKEN_REFRESH:
                    $this->setRefreshToken($token);
                    break;
            }
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
    protected function getSession(): Session
    {
        return $this->_session;
    }

    /**
     * @return User
     */
    protected function getUser(): User
    {
        return $this->_user;
    }

    /**
     * Check if storage is enabled. This could either be session or a static property
     *
     * @return bool
     */
    public function isStorageEnabled(): bool
    {
        if (Yii::$app instanceof \yii\console\Application) {
            return false;
        }

        if ($this->getUser()->enableSession) {
            return $this->getSession()->getIsActive();
        }
        // use temporary static property for cache
        return false;
    }
}
