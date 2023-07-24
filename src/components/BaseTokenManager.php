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
        return (array)$this->getClaim($this->rolesClaimName, []);
    }

    /**
     * @inheritdoc
     *
     * Name can be represented in dot notation like this claim1.subClaim
     */
    public function getClaim(string $name, $default = null): mixed
    {
        // split name into separate parts
        $parts = explode('.', $name);

        // check if there is at least one item
        $baseName = $parts[0] ?? null;
        if ($baseName === null) {
            return $default;
        }

        // remove first part because it is saved in $baseName
        unset($parts[0]);

        // set this as base value
        $baseValue = $this->getToken()->claims()->get($baseName, $default);

        // iterate over the rest of the parts
        foreach ($parts as $part) {
            // check if key exists. If not return default.
            if (!isset($baseValue[$part])) {
                return $default;
            }
            // check if value is array to continue. If not return value
            if (!is_array($baseValue[$part])) {
                return $baseValue;
            }
            $baseValue = $baseValue[$part];
        }
        // return the value
        return $baseValue;
    }
}
