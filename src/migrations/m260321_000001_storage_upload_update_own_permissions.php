<?php

use yii\db\Migration;
use portalium\storage\rbac\OwnRule;

class m260321_000001_storage_upload_update_own_permissions extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $rule = $auth->getRule('storageOwnRule');
        if (!$rule) {
            $rule = new OwnRule();
            $auth->add($rule);
        }
        $role = Yii::$app->setting->getValue('site::admin_role');
        $admin = (isset($role) && $role != '') ? $auth->getRole($role) : $auth->getRole('admin');

        $permissionsName = [
            'storageApiDefaultIndexOwn',
            'storageApiDefaultUpdateOwn',
            'storageApiDefaultDeleteOwn',
            'storageApiDefaultManageDirectoryOwn',
        ];
        foreach ($permissionsName as $permissionName) {
            $permission = $auth->createPermission($permissionName);
            $permission->description = $permissionName;
            $permission->ruleName = $rule->name;
            $auth->add($permission);
            $auth->addChild($admin, $permission);
            $childPermission = $auth->getPermission(str_replace('Own', '', $permissionName));
            $auth->addChild($permission, $childPermission);
        }

        $permissionsName = [
            'storageApiDefaultUploadOwn',
            'storageWebDefaultUploadOwn',
        ];
        foreach ($permissionsName as $permissionName) {
            $permission = $auth->createPermission($permissionName);
            $permission->description = $permissionName;
            $auth->add($permission);
            $auth->addChild($admin, $permission);
        }
        return true;
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $permissionNames = [
            'storageApiDefaultIndexOwn',
            'storageApiDefaultUpdateOwn',
            'storageApiDefaultDeleteOwn',
            'storageApiDefaultManageDirectoryOwn',
            'storageApiDefaultUploadOwn',
            'storageWebDefaultUploadOwn',
        ];
        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            if ($permission) {
                $auth->remove($permission);
            }
        }
        return true;
    }
}
