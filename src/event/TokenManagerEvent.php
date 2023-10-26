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

    public function __construct(UnencryptedToken $token, $config = [])
    {
        $this->token = $token;

        parent::__construct($config);
    }

    public function getToken(): UnencryptedToken
    {
        return $this->token;
    }

}
