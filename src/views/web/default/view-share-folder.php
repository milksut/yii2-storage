<?php

use portalium\storage\Module;
use portalium\theme\widgets\Html;
use portalium\theme\widgets\Panel;
use portalium\storage\bundles\StorageAsset;

/** @var \yii\web\View $this */
/** @var \portalium\storage\models\Storage $model */
/** @var \portalium\storage\models\StorageShare $share */
/** @var \yii\data\ActiveDataProvider $fileDataProvider */
/** @var \yii\data\ActiveDataProvider $directoryDataProvider */
/** @var bool $hasEditAccess */
/** @var bool $hasManageAccess */

StorageAsset::register($this);

$this->title = Module::t('Share Folder');
$this->params['breadcrumbs'][] = ['label' => Module::t('Storage'), 'url' => ['/storage/default/index']];
$this->params['breadcrumbs'][] = Html::encode($model->name); // Klasör adını breadcrumb'a dinamik yazdırdım

$viewMode = Yii::$app->request->get('viewMode');
if (!$viewMode) {
    $viewMode = Yii::$app->request->cookies->getValue('viewMode', 'list');
}

// --- 1. ADIM: Butonları (Actions) Diziye Toplama ---
$actions = [];

// Herkesin görebileceği butonlar
$actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-copy']), ['class' => 'btn btn-outline-secondary btn-sm', 'title' => Module::t('Make a copy'), 'onclick' => 'copyFolder(' . $model->id_storage . '); return false;']);
$actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-download']), ['class' => 'btn btn-outline-secondary btn-sm', 'title' => Module::t('Download')]);

// Sadece düzenleme yetkisi olanlar
if ($hasEditAccess) {
    $actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-pencil']), ['class' => 'btn btn-outline-secondary btn-sm', 'title' => Module::t('Rename'), 'onclick' => 'openRenameFolderModal(' . $model->id_storage . ')']);
    $actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-refresh']), ['class' => 'btn btn-outline-secondary btn-sm', 'title' => Module::t('Update')]);
}

// Sadece yönetme yetkisi olanlar
if ($hasManageAccess) {
    $actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-share-alt']), ['class' => 'btn btn-outline-secondary btn-sm', 'title' => Module::t('Share'), 'onclick' => 'openShareDirectoryModal(' . $model->id_storage . ')']);
    $actions[] = Html::button(Html::tag('i', '', ['class' => 'fa fa-trash']), ['class' => 'btn btn-outline-secondary btn-sm', 'title' => Module::t('Remove'), 'onclick' => 'deleteFolder(' . $model->id_storage . '); return false;']);
}

?>

<div class="container-fluid mt-3">

    <div class="d-flex justify-content-start mb-3">
        <?= Html::button(Html::tag('i', '', ['class' => 'fa fa-list me-1']) . Module::t('List View'), ['class' => 'btn btn-outline-primary btn-sm me-2', 'onclick' => 'setViewMode("list")']) ?>
        <?= Html::button(Html::tag('i', '', ['class' => 'fa fa-th me-1']) . Module::t('Grid View'), ['class' => 'btn btn-outline-secondary btn-sm', 'onclick' => 'setViewMode("grid")']) ?>
    </div>

    <?php Panel::begin([
        'title' => Module::t('Share Items') . ': ' . Html::encode($model->name), // Dosya/Klasör adı başlıkta
        'actions' => $actions // Panel sağ üstündeki butonlar
    ]); ?>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th><?= Module::t('NAME') ?></th>
                    <th><?= Module::t('SIZE') ?></th>
                    <th><?= Module::t('MODIFIED') ?></th>
                    <th><?= Module::t('OWNER') ?></th>
                    <th class="text-end"><?= Module::t('ACTIONS') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($directoryDataProvider->models as $dir): ?>
                <tr>
                    <td><i class="fa fa-folder text-warning me-2"></i><?= Html::encode($dir->name) ?></td>
                    <td>--</td>
                    <td><?= Yii::$app->formatter->asDate($dir->date_create, 'medium') ?></td>
                    <td><?= Html::encode($dir->user->username ?? 'Admin') ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-link text-muted"><i class="fa fa-ellipsis-v"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php foreach ($fileDataProvider->models as $file): ?>
                <tr>
                    <td>
                        <i class="<?= $file->getFileIconClass() ?> text-muted me-2"></i><?= Html::encode($file->title) ?>
                    </td>
                    <td>
                        <?= $file->getReadableFileSize() ?>
                    </td>
                    <td><?= Yii::$app->formatter->asDate($file->date_create, 'medium') ?></td>
                    <td><?= Html::encode($file->user->username ?? 'Admin') ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-link text-muted"><i class="fa fa-ellipsis-v"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php Panel::end(); ?>
</div>

<script>
    function setViewMode(mode) {
        document.cookie = "viewMode=" + mode + "; path=/; max-age=31536000";
        location.reload();
    }
</script>