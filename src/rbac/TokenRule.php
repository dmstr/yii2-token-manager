<?php

namespace dmstr\tokenManager\rbac;

use dmstr\tokenManager\exceptions\LoadTokenException;
use yii\rbac\Rule;
use Yii;

class TokenRule extends Rule
{
    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        try {
            $roles = \Yii::$app->tokenManager->getRoles();
        } catch (LoadTokenException $exception) {
            Yii::$app->getModule('audit')?->exception($exception);
            return false;
        }
        return in_array($item->name, $roles, true);
    }
}