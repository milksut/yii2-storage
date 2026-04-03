<?php

use portalium\storage\bundles\StorageAsset;
use portalium\storage\Module;
use portalium\theme\widgets\Html;
use portalium\widgets\Pjax;

/** @var $this yii\web\View */
/** @var yii\data\ActiveDataProvider $directoryDataProvider */
/** @var yii\data\ActiveDataProvider $fileDataProvider */
/** @var portalium\storage\models\StorageShare $share */
/** @var string $token */
/** @var int $current_directory */

StorageAsset::register($this);

$this->title = Module::t('Shared Folder');
$this->params['breadcrumbs'][] = $this->title;

$currentUrl = Yii::$app->request->url;
$this->registerJsVar('pjaxBaseUrl', \yii\helpers\Url::to($currentUrl));
$this->registerJsVar('isPicker', 0);
$this->registerJsVar('actionId', 'public-index');
$this->registerJsVar('currentFileExtensions', '');
?>
<div class="file-manager">
    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="fa fa-info-circle me-3" style="font-size: 1.5rem;"></i>
        <div>
            <strong><?= Module::t('Public Shared Folder') ?></strong><br>
            <?= Module::t('You are viewing a folder shared via public link. You can browse the contents and download files.') ?>
        </div>
    </div>
    
    <div class="file-list">
        <?php
        Pjax::begin([
            'id' => 'list-item-pjax',
            'timeout' => false,
            'enablePushState' => false,
        ]);

        echo $this->render('_item-list', [
            'directoryDataProvider' => $directoryDataProvider,
            'fileDataProvider' => $fileDataProvider,
            'isPicker' => false,
            'actionId' => 'public-index'
        ]);

        Pjax::end();
        ?>
    </div>
</div>
