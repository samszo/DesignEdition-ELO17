<?php

namespace PslSearchFormTest\Controller;

use PslSearchFormTest\Controller\PslSearchFormControllerTestCase;

class IndexControllerTest extends PslSearchFormControllerTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->api()->create('sites', [
            'o:title' => 'Test site',
            'o:slug' => 'test',
            'o:theme' => 'default',
        ]);

        $this->resetApplication();
    }

    public function testSearchAction()
    {
        $this->dispatch('/s/test/test/search');
        $this->assertResponseStatusCode(200);

        $this->assertQuery('input[name="q"]');
        $this->assertNotQuery('.search-results');

        $this->dispatch('/s/test/test/search', 'GET', ['q' => 'test']);
        $this->assertResponseStatusCode(200);
        $this->assertQuery('.search-results');
        $this->assertQuery('input[name="q"][value="test"]');
    }
}
