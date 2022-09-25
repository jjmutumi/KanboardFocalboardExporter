<?= $this->render('export/header', ['project' => $project, 'title' => "Focalboard"]) ?>

<p class="alert alert-info"><?= t('This exports tasks, subtasks, tags and external links.') ?></p>

<form class="js-modal-ignore-form" method="post" action="<?= $this->url->href('FocalboardController', 'export', ['plugin' => 'KanboardFocalboardExporter', 'project_id' => $project['id']]) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>
    <?= $this->form->hidden('project_id', $values) ?>
    <?= $this->form->hidden('from', $values) ?>
    <?= $this->form->hidden('to', $values) ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-blue js-form-export"><?= t('Export') ?></button>
        <?= t('or') ?>
        <?= $this->url->link(t('cancel'), 'FocalboardController', 'export', ['plugin' => 'KanboardFocalboardExporter', 'project_id' => $project['id']], false, 'js-modal-close') ?>
    </div>
</form>