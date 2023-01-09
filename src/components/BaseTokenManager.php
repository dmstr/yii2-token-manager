<?php

namespace dmstr\tokenManager\components;

use Lcobucci\JWT\UnencryptedToken;
use dmstr\tokenManager\interfaces\TokenManagerInterface;
use yii\base\Component;

/**
 * @property UnencryptedToken $token
 */
abstract class BaseTokenManager extends Component implements TokenManagerInterface
{
    /**
     * Name of the claim which contains the roles
     *
     * @var string
     */
    public string $rolesClaimName = 'groups';

    /**
     * User auth token
     *
     * @var UnencryptedToken
     */
    protected UnencryptedToken $_token;

    /**
     * @inheritdoc
     */
    public function getToken(): UnencryptedToken
    {
        return $this->_token;
    }

    /**
     * @inheritdoc
     */
    public function setToken(UnencryptedToken $token): void
    {
        $this->_token = $token;
    }

    /**
     * @inheritdoc
     */
    public function getRoles(): array
    {
        return $this->getClaim($this->rolesClaimName, []);
    }

    /**
     * @inheritdoc
     */
    public function getClaim(string $name, $default = null): mixed
    {
        return $this->getToken()->claims()->get($name, $default);
    }
}