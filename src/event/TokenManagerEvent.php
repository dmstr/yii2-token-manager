<?php

namespace dmstr\tokenManager\event;

use Lcobucci\JWT\UnencryptedToken;
use yii\base\Event;

/**
 *
 * @property-read UnencryptedToken $token
 */
class TokenManagerEvent extends Event
{
    const EVENT_BEFORE_SET_TOKEN = 'beforeSetToken';
    const EVENT_AFTER_SET_TOKEN = 'afterSetToken';

    protected UnencryptedToken $token;
    protected UnencryptedToken $token_id;
    protected UnencryptedToken $token_refresh;

    public function __construct(UnencryptedToken $token, UnencryptedToken $_token_id, UnencryptedToken $_token_refresh, $config = [])
    {
        $this->token = $token;
        $this->token_id = $_token_id;
        $this->token_refresh = $_token_refresh;

        parent::__construct($config);
    }

    public function getToken(): UnencryptedToken
    {
        return $this->token;
    }

    public function getIdToken(): UnencryptedToken
    {
        return $this->token_id;
    }
    public function getRefreshToken(): UnencryptedToken
    {
        return $this->token_refresh;
    }
}
