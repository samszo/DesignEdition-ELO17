<?php

namespace IdRefTest\Controller;

use OmekaTestHelper\Controller\OmekaControllerTestCase;

class IdRefControllerTest extends OmekaControllerTestCase
{
    public function setup()
    {
        parent::setup();
        $this->loginAsAdmin();
    }

    public function testSearchAction()
    {
        $this->dispatch('/admin/idref/search?term=hugo');
        $this->assertResponseStatusCode(200);
        $this->assertResponseHeaderContains('Content-Type', 'application/json; charset=utf-8');

        $results = json_decode($this->getResponse()->getContent());
        $this->assertNotNull($results);
        $this->assertTrue(is_array($results));
    }
}
