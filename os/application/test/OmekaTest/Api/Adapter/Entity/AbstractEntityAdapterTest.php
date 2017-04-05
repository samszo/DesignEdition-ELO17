<?php
namespace OmekaTest\Api\Adapter\Entity;

use Omeka\Api\Representation\RepresentationInterface;
use Omeka\Entity\AbstractEntity;
use Omeka\Test\TestCase;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractEntityAdapterTest extends TestCase
{
    const TEST_ENTITY_CLASS = 'OmekaTest\Api\Adapter\Entity\TestEntity';

    protected $adapter;

    public function setUp()
    {
        $this->adapter = $this->getMock(
            'Omeka\Api\Adapter\AbstractEntityAdapter',
            ['hydrate', 'getResourceName', 'getRepresentationClass',
                'getEntityClass', 'getEventManager']
        );
    }

    public function testSearch()
    {}

    public function testCreate()
    {
        /* ServiceManager **/

        // Service: Omeka\EntityManager
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('Omeka\Entity\EntityInterface'));
        $entityManager->expects($this->once())
            ->method('flush');

        // Service: MvcTranslator
        $translator = $this->getMock('Zend\I18n\Translator\Translator');

        // Service: Omeka\Acl
        $acl = $this->getMock('Omeka\Permissions\Acl');
        $acl->expects($this->once())
            ->method('userIsAllowed')
            ->with(
                $this->isInstanceOf('OmekaTest\Api\Adapter\Entity\TestEntity'),
                $this->equalTo('create')
            )
            ->will($this->returnValue(true));

        $eventManager = $this->getMock('Zend\EventManager\EventManager');
        $eventManager->expects($this->exactly(2))
            ->method('triggerEvent')
            ->with($this->isInstanceOf('Zend\EventManager\Event'));

        $serviceManager = $this->getServiceManager([
            'Omeka\EntityManager' => $entityManager,
            'MvcTranslator' => $translator,
            'Omeka\Acl' => $acl,
            'EventManager' => $eventManager,
        ]);
        $this->adapter->setServiceLocator($serviceManager);

        /* Adapter **/
        $this->adapter->expects($this->exactly(2))
             ->method('getEventManager')
             ->will($this->returnValue($eventManager));
        $this->adapter->expects($this->exactly(1))
            ->method('getEntityClass')
            ->will($this->returnValue(self::TEST_ENTITY_CLASS));
        $this->adapter->expects($this->once())
            ->method('getRepresentationClass')
            ->will($this->returnValue('OmekaTest\Api\Adapter\Entity\TestRepresentation'));

        /* Request **/

        $request = $this->getMock('Omeka\Api\Request');
        $request->expects($this->once())
            ->method('getOperation')
            ->will($this->returnValue(\Omeka\Api\Request::CREATE));

        /* ASSERTIONS **/

        $response = $this->adapter->create($request);
        $this->assertInstanceOf('Omeka\Api\Response', $response);
        $this->assertEquals('success', $response->getStatus());
        $this->assertInstanceOf(
            'OmekaTest\Api\Adapter\Entity\TestRepresentation',
            $response->getContent()
        );
    }
}

class TestEntity extends AbstractEntity
{
    public function getId() {}
}

class TestRepresentation implements RepresentationInterface
{
    public function setData($data) {}
    public function jsonSerialize() {}
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {}
    public function getServiceLocator() {}
    public function setEventManager(EventManagerInterface $eventManager) {}
    public function getEventManager() {}
}
