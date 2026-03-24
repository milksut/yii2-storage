<?php

use portalium\storage\models\Storage;
use portalium\storage\Module;
use portalium\theme\widgets\Html;
use portalium\theme\widgets\Panel;
use portalium\storage\models\StorageShare;
use portalium\storage\bundles\StorageAsset;
use portalium\widgets\Pjax;
use yii\helpers\Url;

/** @var \yii\web\View $this */
/** @var \portalium\storage\models\Storage $model */
/** @var \portalium\storage\models\StorageShare $share */
/** @var bool $hasEditAccess */
/** @var bool $hasManageAccess */

StorageAsset::register($this);

$this->title = Module::t('Shared Item');
$this->params['breadcrumbs'][] = ['label' => Module::t('Storage'), 'url' => ['/storage/default/index']];
$this->params['breadcrumbs'][] = Module::t('Shared Files');

$actions = [];

// Everyone: Download and Copy
$actions[] = Html::a(Html::tag('i', '', ['class' => 'fa fa-download']), 
    ['/storage/default/view-share', 'token' => $share->share_token, 'download' => true], 
    ['class' => 'btn btn-info btn-sm', 'title' => Module::t('Download')]);

$actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-copy']), 
    ['class' => 'btn btn-success btn-sm', 'title' => Module::t('Make a copy'), 'onclick' => 'copyFile(' . $model->id_storage . '); return false;']);

// Edit Permission: Rename and Update
if ($hasEditAccess) {
    $actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-pencil']), 
        ['class' => 'btn btn-warning btn-sm', 'title' => Module::t('Rename'), 'onclick' => 'openRenameModal(' . $model->id_storage . ')']);
    $actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-upload']), 
        ['class' => 'btn btn-primary btn-sm', 'title' => Module::t('Update'), 'onclick' => 'openUpdateModal(' . $model->id_storage . ')']);
}

// Manage Permission: Share and Delete
if ($hasManageAccess) {
    $actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-share-alt']), 
        ['class' => 'btn btn-info btn-sm', 'title' => Module::t('Share'), 'onclick' => 'openShareModal(' . $model->id_storage . ')']);
    $actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-trash']), 
        ['class' => 'btn btn-danger btn-sm', 'title' => Module::t('Remove'), 'onclick' => 'deleteFile(' . $model->id_storage . '); return false;']);
}
?>

<?= Html::beginTag('div', ['class' => 'container-fluid']); ?>

<?php Panel::begin([
    'title' => Html::encode($model->title),
    'actions' => $actions
]); ?>

    <?= Html::beginTag('div', ['class' => 'position-relative overflow-hidden', 'style' => 'height: 500px;']) ?>
        <?= Html::beginTag('div', ['class' => 'w-100 h-100 d-flex align-items-center justify-content-center ' . (!$model->isImage() ? 'bg-transparent text-secondary' : 'bg-transparent')]) ?>
            <?= Html::beginTag('div', ['class' => 'text-center w-100 h-100 d-flex align-items-center justify-content-center overflow-auto']) ?>
                <?php if ($model->isImage()): ?>
                    <?= Html::img(['/storage/default/view-share', 'token' => $share->share_token, 'download' => true], ['class' => 'img-fluid', 'style' => 'width: 100%; height: 100%; object-fit: contain;']) ?>
                <?php else: ?>
                    <?= Html::tag('i', '', ['class' => $model->getFileIconClass() . ' fa-5x mb-4 text-secondary']) ?>
                <?php endif; ?>
            <?= Html::endTag('div') ?>
        <?= Html::endTag('div') ?>
    <?= Html::endTag('div') ?>

<?php Panel::end(); ?>

<?= Html::beginTag('div', ['class' => 'd-flex justify-content-center mt-3']) ?>
    <?= Html::tag('span', $model->getReadableFileSize(), ['class' => 'small text-muted']) ?>
<?= Html::endTag('div') ?>

<?= Html::endTag('div'); // container-fluid end ?>

<?php
Pjax::begin(['id' => 'rename-file-pjax', 'history' => false, 'timeout' => false, 'enablePushState' => false]); Pjax::end();
Pjax::begin(['id' => 'update-file-pjax', 'history' => false, 'timeout' => false, 'enablePushState' => false]); Pjax::end();
Pjax::begin(['id' => 'share-file-pjax', 'history' => false, 'timeout' => false, 'enablePushState' => false]); Pjax::end();
?>

<?= Html::beginTag('div', ['class' => 'modal fade', 'id' => 'shareModal', 'tabindex' => '-1', 'aria-hidden' => 'true']) ?>
    <?= Html::beginTag('div', ['class' => 'modal-dialog modal-lg']) ?>
        <?= Html::tag('div', '', ['class' => 'modal-content']) ?>
    <?= Html::endTag('div') ?>
<?= Html::endTag('div') ?>

<?= Html::hiddenInput('current-share-id', $share->id_share, ['id' => 'current-share-id']) ?>
<?= Html::hiddenInput('current-storage-id', $model->id_storage, ['id' => 'current-storage-id']) ?>

<?php
Pjax::begin([
    'id' => 'list-item-pjax',
    'history' => false,
    'timeout' => false,
    'enablePushState' => false
]);
Pjax::end();
?>

<?php $this->registerJs("
    $.ajaxSetup({
        data: {
            '" . Yii::$app->request->csrfParam . "': '" . Yii::$app->request->getCsrfToken() . "'
        }
    });
"); ?>

<?= Html::tag('div', '', ['id' => 'toast-container', 'class' => 'toast-container position-fixed top-0 end-0 p-3']) ?>