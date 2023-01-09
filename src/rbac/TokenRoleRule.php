<?php

namespace dmstr\tokenManager\rbac;

use dmstr\tokenManager\components\TokenManager;
use dmstr\tokenManager\exceptions\LoadTokenException;
use yii\base\InvalidConfigException;
use yii\rbac\Rule;
use Yii;

class TokenRoleRule extends Rule
{

    public string $tokenManager = 'tokenManager';

    /**
     * @inheritdoc
     *
     * @throws InvalidConfigException
     */
    public function execute($user, $item, $params)
    {
        try {
            /** @var TokenManager $tokenManager */
            $tokenManager = Yii::$app->get($this->tokenManager);
            $roles = $tokenManager->getRoles();
        } catch (LoadTokenException $exception) {
            Yii::error($exception->getMessage());
            return false;
        }
        return in_array($item->name, $roles, true);
    }
}