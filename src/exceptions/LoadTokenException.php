<?php

namespace dmstr\tokenManager\exceptions;

use yii\base\Exception;

class LoadTokenException extends Exception
{
    public $message = 'Error while loading token from storage';
}