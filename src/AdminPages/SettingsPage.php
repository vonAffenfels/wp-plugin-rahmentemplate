<?php

namespace WP\Plugin\Rahmentemplate\AdminPages;



use VAF\WP\Framework\Plugin;
use VAF\WP\Framework\Request;
use VAF\WP\Framework\Utils\Templates\Admin\Notice;
use WP\Plugin\Rahmentemplate\Settings\Params;
use WP\Plugin\Rahmentemplate\Templates\AdminPages\SettingsPageTemplate;

final class SettingsPage
{

    public function __construct(
        private readonly Plugin $plugin,
        private readonly Request $request,
        private readonly SettingsPageTemplate $pageTemplate,
        private readonly Params $params
    ) {
    }

    private function save(): void
    {
        $action = $this->request->getParam('action', Request::TYPE_POST, '');
        if ($action !== 'update') {
            return;
        }

        $params = $this->request->getParam(Params::FIELD_ENDPOINT, Request::TYPE_POST);
        if (!is_null($params) && $this->params->getEndpoint() !== $params) {
            $this->params->setEndpoint($params);
        }

        /** @var Notice $notice */
        $notice = $this->plugin->getContainer()->get('template.notice');
        $notice->output();
    }

    public function handle(): void
    {
        if ($this->request->isPost()) {
            $this->save();
        }

        $this->display();
    }

    private function display(): void
    {
        $this->pageTemplate->setEndpoint($this->params);
        $this->pageTemplate->output();
    }
}