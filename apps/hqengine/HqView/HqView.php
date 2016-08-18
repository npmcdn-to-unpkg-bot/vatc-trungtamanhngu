<?php

namespace HqEngine\HqView;

use HqEngine\HqDI\HqDIBehaviour;
use HqEngine\HqView\HqExtension;
use Phalcon\DI;
use Phalcon\Events\Manager;
use Phalcon\Mvc\View as PhalconView;
use Phalcon\Mvc\View\Engine\Php;

class HqView extends PhalconView {

    /**
     * Create view instance.
     * If no events manager provided - events would not be attached.
     *
     * @param DIBehaviour  $di             DI.
     * @param Config       $config         Configuration.
     * @param string       $viewsDirectory Views directory location.
     * @param Manager|null $em             Events manager.
     *
     * @return View
     */
    public static function factory($di, $config, $viewsDirectory, $em = null)
    {
        $view = new HqView();
        $view
                ->registerEngines([".phtml" => "Phalcon\Mvc\View\Engine\Php"])
                ->setViewsDir($viewsDirectory)
                ->cache(false);
        // Attach a listener for type "view".
        if ($em)
        {
            $em->attach(
                    "view", function ($event, $view) use ($di, $config) {
                if ($config->profiler && $di->has('profiler'))
                {
                    if ($event->getType() == 'beforeRender')
                    {
                        $di->get('profiler')->start();
                    }
                    if ($event->getType() == 'afterRender')
                    {
                        $di->get('profiler')->stop($view->getActiveRenderPath(), 'view');
                    }
                }
                if ($event->getType() == 'notFoundView')
                {
                    throw new \HqEngine\HqException('View not found - "' . $view->getActiveRenderPath() . '"');
                }
            }
            );
            $view->setEventsManager($em);
        }

        return $view;
    }

    /**
     * Pick view to render.
     *
     * @param array|string $renderView View to render.
     * @param string|null  $module     Specify module.
     *
     * @return PhalconView|void
     */
    public function pick($renderView, $module = null)
    {
        if ($module != null)
        {
            $renderView = '../../' . ucfirst($module) . '/views/' . strtolower($renderView);
        }

        parent::pick($renderView);
    }

}
