<?php

use portalium\storage\rbac\OwnRule;
use yii\db\Migration;

class m260123_104500_add_storage_api_permissions extends Migration
{
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;

        // Admin and User roles
        $admin = $auth->getRole('admin');
        $user = $auth->getRole('user');

        // Own Rule for user-specific permissions
        $ownRule = $auth->getRule('storageOwnRule');
        if (!$ownRule) {
            $ownRule = new OwnRule();
            $auth->add($ownRule);
        }


        return true;
    }

    public function safeDown()
    {
        $auth = \Yii::$app->authManager;

        // Remove all API permissions
        $permissions = [
            'storageApiDefaultGetFile',
            'storageApiDefaultGetFileOwn',
        ];

        foreach ($permissions as $permName) {
            $permission = $auth->getPermission($permName);
            if ($permission) {
                $auth->remove($permission);
            }
        }

        return true;
    }
}
