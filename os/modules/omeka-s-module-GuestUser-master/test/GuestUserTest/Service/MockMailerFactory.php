<?php

namespace GuestUserTest\Service;

use Interop\Container\ContainerInterface;
use Zend\Mail\Transport\Factory as TransportFactory;
use Zend\ServiceManager\Factory\FactoryInterface;
use GuestUserTest\Service\MockMailer;

class MockMailerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $transport = TransportFactory::create([]);
        $viewHelpers = $services->get('ViewHelperManager');
        $entityManager = $services->get('Omeka\EntityManager');

        return new MockMailer($transport, $viewHelpers, $entityManager, []);
    }
}
