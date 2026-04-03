<?php

use portalium\storage\Module;
use portalium\storage\models\StorageShare;
use portalium\theme\widgets\Html;
use yii\helpers\Url;

/**
 * Shares Table Partial View
 * @var $this yii\web\View
 * @var $shares array - Array of StorageShare models
 * @var $type string - 'file', 'directory', or 'storage'
 */

?>

<?= Html::beginTag('div', ['class' => 'table-responsive']) ?>
    <?= Html::beginTag('table', ['class' => 'table table-hover align-middle']) ?>
        <?= Html::beginTag('thead', ['class' => 'table-light']) ?>
            <?= Html::beginTag('tr') ?>
                <?= Html::tag('th', Html::tag('i', '', ['class' => 'fa fa-file me-1']) . Module::t('Item')) ?>
                <?= Html::tag('th', Html::tag('i', '', ['class' => 'fa fa-users me-1']) . Module::t('Shared With')) ?>
                <?= Html::tag('th', Html::tag('i', '', ['class' => 'fa fa-key me-1']) . Module::t('Permission')) ?>
                <?= Html::tag('th', Html::tag('i', '', ['class' => 'fa fa-calendar me-1']) . Module::t('Shared Date')) ?>
                <?= Html::tag('th', Html::tag('i', '', ['class' => 'fa fa-clock-o me-1']) . Module::t('Expires')) ?>
                <?= Html::tag('th', Html::tag('i', '', ['class' => 'fa fa-check me-1']) . Module::t('Status')) ?>
                <?= Html::tag('th', Module::t('Actions'), ['class' => 'text-end']) ?>
            <?= Html::endTag('tr') ?>
        <?= Html::endTag('thead') ?>
        <?= Html::beginTag('tbody') ?>
            <?php foreach ($shares as $share): ?>
                <?php
                // Determine item details based on type
                if ($type === 'file' && $share->storage) {
                    $itemName = $share->storage->title;
                    $itemIcon = 'fa-file';
                    $itemUrl = Url::to(['/storage/default/get-file', 'id' => $share->storage->id_storage]);
                    $itemExists = true;
                } elseif ($type === 'directory' && $share->directory) {
                    $itemName = $share->directory->name;
                    $itemIcon = 'fa-folder';
                    $itemUrl = Url::to(['/storage/default/index', 'id_directory' => $share->directory->id_directory]);
                    $itemExists = true;
                } elseif ($type === 'storage') {
                    $itemName = Module::t('Full Storage');
                    $itemIcon = 'fa-database';
                    $itemUrl = Url::to(['/storage/default/index']);
                    $itemExists = true;
                } else {
                    $itemName = Module::t('Deleted Item');
                    $itemIcon = 'fa-times';
                    $itemUrl = '#';
                    $itemExists = false;
                }

                $sharedWith = $share->getSharedWithName();
                $permission = $share->getPermissionLabel();
                $sharedDate = Yii::$app->formatter->asDatetime($share->date_create, 'medium');
                $expiresAt = $share->expires_at ? Yii::$app->formatter->asDatetime($share->expires_at, 'medium') : Module::t('Never');
                $isExpired = $share->isExpired();
                $isActive = $share->is_active == 1;
                ?>
                <?= Html::beginTag('tr', ['class' => (!$isActive || $isExpired) ? 'text-muted' : '', 'data-share-id' => $share->id_share]) ?>
                    <?= Html::beginTag('td') ?>
                        <?php if ($itemExists): ?>
                            <?= Html::a(
                                Html::tag('i', '', ['class' => 'fa ' . $itemIcon . ' me-2']) . Html::encode($itemName),
                                $itemUrl,
                                ['target' => '_blank']
                            ) ?>
                        <?php else: ?>
                            <?= Html::tag('span', 
                                Html::tag('i', '', ['class' => 'fa ' . $itemIcon . ' me-2 text-danger']) . Html::encode($itemName),
                                ['class' => 'text-muted']
                            ) ?>
                        <?php endif; ?>
                    <?= Html::endTag('td') ?>
                    <?= Html::beginTag('td') ?>
                        <?php if ($share->shared_with_type === StorageShare::TYPE_USER): ?>
                            <?= Html::tag('i', '', ['class' => 'fa fa-user me-1']) ?>
                        <?php elseif ($share->shared_with_type === StorageShare::TYPE_WORKSPACE): ?>
                            <?= Html::tag('i', '', ['class' => 'fa fa-users me-1']) ?>
                        <?php elseif ($share->shared_with_type === StorageShare::TYPE_LINK): ?>
                            <?= Html::tag('i', '', ['class' => 'fa fa-link me-1']) ?>
                        <?php endif; ?>
                        <?= Html::encode($sharedWith) ?>
                    <?= Html::endTag('td') ?>
                    <?= Html::beginTag('td') ?>
                        <?php
                        $badgeClass = 'bg-secondary';
                        if ($permission === Module::t('Manage')) {
                            $badgeClass = 'bg-danger';
                        } elseif ($permission === Module::t('Edit')) {
                            $badgeClass = 'bg-warning';
                        } elseif ($permission === Module::t('View')) {
                            $badgeClass = 'bg-info';
                        }
                        ?>
                        <?= Html::tag('span', $permission, ['class' => 'badge ' . $badgeClass]) ?>
                    <?= Html::endTag('td') ?>
                    <?= Html::tag('td', $sharedDate) ?>
                    <?= Html::beginTag('td') ?>
                        <?php if ($share->expires_at): ?>
                            <?php if ($isExpired): ?>
                                <?= Html::tag('span', $expiresAt, ['class' => 'text-danger']) ?>
                            <?php else: ?>
                                <?= $expiresAt ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <?= Html::tag('span', Module::t('Never'), ['class' => 'text-muted']) ?>
                        <?php endif; ?>
                    <?= Html::endTag('td') ?>
                    <?= Html::beginTag('td') ?>
                        <?php if ($isActive && !$isExpired): ?>
                            <?= Html::tag('span', Module::t('Active'), ['class' => 'badge bg-success']) ?>
                        <?php elseif ($isExpired): ?>
                            <?= Html::tag('span', Module::t('Expired'), ['class' => 'badge bg-danger']) ?>
                        <?php else: ?>
                            <?= Html::tag('span', Module::t('Revoked'), ['class' => 'badge bg-secondary']) ?>
                        <?php endif; ?>
                    <?= Html::endTag('td') ?>
                    <?= Html::beginTag('td', ['class' => 'text-end']) ?>
                        <?php if ($share->shared_with_type === StorageShare::TYPE_LINK && $itemExists && $isActive && !$isExpired): ?>
                            <?php $shareUrl = \yii\helpers\Url::to(['/storage/default/view-share', 'token' => $share->share_token], true); ?>
                            <?= Html::button(
                                '',
                                [
                                    'class' => 'fa fa-copy btn btn-sm btn-info',
                                    'title' => Module::t('Copy Link'),
                                    'onclick' => 'copyShareLinkUrl(' . json_encode($shareUrl) . ', this)',
                                ]
                            ) ?>
                        <?php endif; ?>
                        <?php if ($itemExists && $isActive && !$isExpired): ?>
                            <?= Html::a(
                                '',
                                $itemUrl,
                                [
                                    'class' => 'fa fa-external-link btn btn-sm btn-info',
                                    'title' => Module::t('Open'),
                                    'target' => '_blank',
                                ]
                            ) ?>
                        <?php endif; ?>
                        <?php if ($isActive): ?>
                            <?= Html::button(
                                '',
                                [
                                    'class' => 'fa fa-times btn btn-sm btn-danger',
                                    'title' => Module::t('Revoke Share'),
                                    'onclick' => "revokeShareConfirm({$share->id_share})",
                                ]
                            ) ?>
                        <?php endif; ?>
                    <?= Html::endTag('td') ?>
                <?= Html::endTag('tr') ?>
            <?php endforeach; ?>
        <?= Html::endTag('tbody') ?>
    <?= Html::endTag('table') ?>
<?= Html::endTag('div') ?>

<?php
// Pre-compute translated strings for use in JS
$jsMsgs = json_encode([
    'copied'       => Module::t('Link copied to clipboard!'),
    'copyFailed'   => Module::t('Failed to copy link'),
    'confirmRevoke'=> Module::t('Are you sure you want to revoke this share?'),
    'revoked'      => Module::t('Revoked'),
    'revokeFailed' => Module::t('Failed to revoke share!'),
    'error'        => Module::t('An error occurred'),
]);
$csrfParam = json_encode(\Yii::$app->request->csrfParam);
$csrfToken = json_encode(\Yii::$app->request->csrfToken);
$revokeUrl  = json_encode(\yii\helpers\Url::to(['/storage/default/revoke-share']));
?>
<?php $this->registerJs(<<<JS
(function() {
    var msgs = {$jsMsgs};
    var csrfParam = {$csrfParam};
    var csrfToken = {$csrfToken};
    var revokeUrl  = {$revokeUrl};

    window.copyShareLinkUrl = function(url, btn) {
        function onCopied() {
            if (btn) {
                var orig = btn.className;
                btn.classList.remove('btn-info');
                btn.classList.add('btn-success', 'fa-check');
                btn.classList.remove('fa-copy');
                setTimeout(function() {
                    btn.className = orig;
                }, 2000);
            }
        }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(onCopied).catch(function() {
                fallbackCopy(url, onCopied);
            });
        } else {
            fallbackCopy(url, onCopied);
        }
    };

    function fallbackCopy(text, onCopied) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try { document.execCommand('copy'); if (onCopied) onCopied(); }
        catch(e) { alert(msgs.copyFailed); }
        finally { document.body.removeChild(ta); }
    }

    window.revokeShareConfirm = function(shareId) {
        if (!confirm(msgs.confirmRevoke)) return;
        var postData = {};
        postData[csrfParam] = csrfToken;
        postData.id = shareId;
        $.ajax({
            url: revokeUrl,
            type: 'POST',
            data: postData,
            success: function(response) {
                if (response.success) {
                    var row = $('[data-share-id="' + shareId + '"]');
                    row.addClass('text-muted');
                    row.find('.badge.bg-success').removeClass('bg-success').addClass('bg-secondary').text(msgs.revoked);
                    row.find('.btn-danger').remove();
                    row.find('.fa-copy').closest('button').remove();
                } else {
                    alert(response.message || msgs.revokeFailed);
                }
            },
            error: function() { alert(msgs.error); }
        });
    };
})();
JS
); ?>
