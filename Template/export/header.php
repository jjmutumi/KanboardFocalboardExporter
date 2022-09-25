<li <?= $this->app->checkMenuSelection('FocalboardController', 'export') ?>>
    <?= $this->modal->replaceLink(t('Focalboard'), 'FocalboardController', 'export', ['plugin' => 'KanboardFocalboardExporter', 'project_id' => $project_id]) ?>
</li>