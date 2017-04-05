<?php
namespace OmekaTheme\Helper;

use Zend\View\Helper\AbstractHelper;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Module\Manager as ModuleManager;

class CleanUrl extends AbstractHelper
{
    public function __invoke(AbstractResourceEntityRepresentation $resource)
    {
        $view = $this->getView();
        $serviceLocator = $view->getHelperPluginManager()->getServiceLocator();
        $moduleManager = $serviceLocator->get('Omeka\ModuleManager');
        $cleanUrl = $moduleManager->getModule('CleanUrl');
        if ($cleanUrl && $cleanUrl->getState() === ModuleManager::STATE_ACTIVE) {
            $url = $view->getResourceFullIdentifier($resource);
        }
        if (!$url) {
            $url = $resource->url();
        }

        return $url;
    }
}
