<?php

namespace Kanboard\Plugin\KanboardFocalboardExporter;

use Kanboard\Core\Plugin\Base;

class Plugin extends Base
{
    public function initialize()
    {
        $this->route->addRoute('/focalboard/export', 'FocalboardController', 'export', 'KanboardFocalboardExporter');
        $this->template->hook->attach('template:export:header', 'KanboardFocalboardExporter:export/header');
    }

    public function getPluginName()
    {
        return 'Focalboard Exporter';
    }

    public function getPluginDescription()
    {
        return t('Create a JSON export that can be imported into Focalboard');
    }

    public function getPluginAuthor()
    {
        return 'Joseph N. Mutumi';
    }

    public function getPluginVersion()
    {
        return '0.0.1';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/jjmutumi/KanboardFocalboardExporter';
    }
}
