<?php

namespace SearchTest\Controller\Admin;

require_once __DIR__ . '/../SearchControllerTestCase.php';

use Omeka\Mvc\Controller\Plugin\Messenger;
use SearchTest\Controller\SearchControllerTestCase;

class SearchPageControllerTest extends SearchControllerTestCase
{
    public function testAddAction()
    {
        $this->dispatch('/admin/search/page/add');
        $this->assertResponseStatusCode(200);

        $this->assertQuery('input[name="o:name"]');
        $this->assertQuery('input[name="o:path"]');
        $this->assertQuery('select[name="o:index_id"]');
        $this->assertQuery('select[name="o:form"]');

        $forms = $this->getServiceLocator()->get('FormElementManager');
        $form = $forms->get('Search\Form\Admin\SearchPageForm');

        $this->dispatch('/admin/search/page/add', 'POST', [
            'o:name' => 'TestPage2',
            'o:path' => 'search/test2',
            'o:index_id' => $this->searchIndex->id(),
            'o:form' => 'basic',
            'csrf' => $form->get('csrf')->getValue(),
        ]);
        $response = $this->api()->search('search_pages', [
            'name' => 'TestPage2'
        ]);
        $searchPages = $response->getContent();
        $searchPage = reset($searchPages);
        $this->assertRedirectTo($searchPage->adminUrl('configure'));
    }

    public function testConfigureAction()
    {
        $this->dispatch($this->searchPage->adminUrl('configure'));
        $this->assertResponseStatusCode(200);

        $this->assertQueryContentContains('h2', 'Facets');
        $this->assertQueryContentContains('h2', 'Sort fields');

        $forms = $this->getServiceLocator()->get('FormElementManager');
        $form = $forms->get('Search\Form\Admin\SearchPageConfigureForm', [
            'search_page' => $this->searchPage,
        ]);

        $this->dispatch($this->searchPage->url('configure'), 'POST', [
            'facet_limit' => '10',
            'csrf' => $form->get('csrf')->getValue(),
        ]);
        $this->assertRedirectTo("/admin/search");
    }
}