<?php

declare(strict_types=1);

namespace dmstr\tokenManager\exceptions;

class InvalidTokenManagerComponent extends \Exception
{
    protected $message = 'Token manager is not instance of TokenManagerInterface';
}