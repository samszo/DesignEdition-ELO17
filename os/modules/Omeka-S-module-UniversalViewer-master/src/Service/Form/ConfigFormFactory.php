<?php
namespace UniversalViewer\Service\Form;

use Omeka\Module\Manager as ModuleManager;
use UniversalViewer\Form\ConfigForm;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ConfigFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $settings = $container->get('Omeka\Settings');
        $api = $container->get('Omeka\ApiManager');

        $moduleManager = $container->get('Omeka\ModuleManager');
        $module = $moduleManager->getModule('IiifServer');
        $iiifServerIsActive = $module && $module->getState() == ModuleManager::STATE_ACTIVE;

        $form = new ConfigForm;
        $form->setSettings($settings);
        $form->setApi($api);
        $form->setIiifServerIsActive($iiifServerIsActive);
        return $form;
    }
}
