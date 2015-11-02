<?php

/**
 * SpacefinderPlugin.class.php
 *
 * ...
 *
 * @author  Florian Bieringer <florian.bieringer@uni-passau.de>
 * @version 0.1a
 */

class SpacefinderPlugin extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();

        if ($GLOBALS['perm']->have_perm('admin')) {
            $navigation = new AutoNavigation(_('Studiengangsbelegung'));
            $navigation->setURL(PluginEngine::GetURL($this, array(), 'show/index'));
            Navigation::addItem('tools/spacefinderplugin', $navigation);
        }
    }

    public function initialize () {

    }

    public function perform($unconsumed_path)
    {
        $GLOBALS['perm']->check('admin');
        $this->setupAutoload();
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'show'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

    private function setupAutoload()
    {
        if (class_exists('StudipAutoloader')) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . $class . '.php';
            });
        }
    }
}
