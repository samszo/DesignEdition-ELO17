<?php

namespace OmekaTheme\Helper;

use Zend\View\Helper\AbstractHelper;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Module\Manager as ModuleManager;

class SearchForm extends AbstractHelper
{
    public function __invoke()
    {
        $view = $this->getView();
        $searchPageId = $view->themeSetting('search_page_id');
        $searchPage = $view->api()->read('search_pages', $searchPageId)->getContent();

        return $view->searchForm($searchPage);
    }
}
