<?php
use portalium\theme\widgets\Modal;
use yii\helpers\Url;

$trackAccessUrl = Url::to(['/storage/default/track-access']);
$this->registerJs("window.storageConfig = { trackAccessUrl: '$trackAccessUrl' };", \yii\web\View::POS_HEAD);

Modal::begin([
    'id' => 'file-preview-modal',
    'title' => '',
    'options' => ['class' => 'fade'],
    'bodyOptions' => ['class' => 'modal-body text-center'],
    'clientOptions' => [
        'backdrop' => 'static',
        'keyboard' => true,
    ],
    'dialogOptions' => ['class' => 'modal-dialog-centered modal-lg']
]);
?>

<div id="filePreviewContent">
    <!-- Content will be loaded with JS -->
</div>

<?php Modal::end(); ?>
