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

// --- STEP 1: Collecting Buttons (Actions) into an Array ---
$actions = [];
// --- STEP 1: Simplifying Buttons (Actions) ---
$actions = [];

// Everyone: Download and Copy (Anyone with access to the file should be able to make a copy)
$actions[] = Html::a(Html::tag('i', '', ['class' => 'fa fa-download']), ['/storage/default/view-share', 'id' => $share->id_share, 'download' => true], ['class' => 'btn btn-outline-secondary btn-sm', 'title' => Module::t('Download')]);
$actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-copy']), ['class' => 'btn btn-outline-secondary btn-sm', 'title' => Module::t('Make a copy'), 'onclick' => 'copyFile(' . $model->id_storage . '); return false;']);

// Edit Permission: Rename and Update
if ($hasEditAccess) {
    $actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-pencil']), ['class' => 'btn btn-outline-secondary btn-sm', 'title' => Module::t('Rename'), 'onclick' => 'openRenameModal(' . $model->id_storage . ')']);
    $actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-upload']), ['class' => 'btn btn-outline-secondary btn-sm', 'title' => Module::t('Update'), 'onclick' => 'openUpdateModal(' . $model->id_storage . ')']);
}

// Manage Permission: Share and Delete
if ($hasManageAccess) {
    $actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-share-alt']), ['class' => 'btn btn-outline-secondary btn-sm', 'title' => Module::t('Share'), 'onclick' => 'openShareModal(' . $model->id_storage . ')']);
    $actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-trash']), ['class' => 'btn btn-outline-secondary btn-sm', 'title' => Module::t('Remove'), 'onclick' => 'deleteFile(' . $model->id_storage . '); return false;']);
}
?>

<?= Html::beginTag('div', ['class' => 'container-fluid']); ?>

<div class="mb-2">
    <span class="small text-muted"><?= $model->getReadableFileSize() ?></span>
</div>

<?php Panel::begin([
    'title' => Html::encode($model->title), // File name displayed here, in the top-left corner of the panel
    'actions' => $actions // Buttons displayed here, in the top-right corner of the panel
]); ?>

    <div class="position-relative overflow-hidden" style="height: 500px;">
        <div class="w-100 h-100 d-flex align-items-center justify-content-center <?= (!$model->isImage()) ? 'bg-transparent text-secondary' : 'bg-transparent' ?>">
            <div class="text-center w-100 h-100 d-flex align-items-center justify-content-center overflow-auto">
                <?php if ($model->isImage()): ?>
                    <?= Html::img(['/storage/default/view-share', 'id' => $share->id_share, 'download' => true], ['class' => 'img-fluid', 'style' => 'width: 100%; height: 100%; object-fit: contain;']) ?>
                <?php else: ?>
                    <i class="<?= $model->getFileIconClass() ?> fa-5x mb-4 text-secondary"></i>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php Panel::end(); ?>
<?= Html::endTag('div'); ?>

<?php
Pjax::begin(['id' => 'rename-file-pjax', 'history' => false, 'timeout' => false, 'enablePushState' => false]); Pjax::end();
Pjax::begin(['id' => 'update-file-pjax', 'history' => false, 'timeout' => false, 'enablePushState' => false]); Pjax::end();
Pjax::begin(['id' => 'share-file-pjax', 'history' => false, 'timeout' => false, 'enablePushState' => false]); Pjax::end();
?>

<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      </div>
  </div>
</div>

<input type="hidden" id="current-share-id" value="<?= $share->id_share ?>">
<input type="hidden" id="current-storage-id" value="<?= $model->id_storage ?>">

<?php
\portalium\widgets\Pjax::begin([
    'id' => 'list-item-pjax',
    'history' => false,
    'timeout' => false,
    'enablePushState' => false
]);
\portalium\widgets\Pjax::end();
?>
<?php $this->registerJs("
    // Automatically add CSRF token to all AJAX requests (including those inside Modals)
    $.ajaxSetup({
        data: {
            '" . Yii::$app->request->csrfParam . "': '" . Yii::$app->request->getCsrfToken() . "'
        }
    });
"); ?>
<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>