<?php

namespace IdRef\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Client;
use Zend\View\Model\JsonModel;

class IdRefController extends AbstractActionController
{
    const IDREF_SUGGEST_URL = 'http://www.idref.fr/Sru/Solr?wt=json&version=2.2&start=0&rows=50&indent=on&fl=affcourt_z';

    protected $httpClient;

    public function searchAction()
    {
        $client = $this->getHttpClient();

        $term = $this->params()->fromQuery('term');
        $term = strtolower($term);

        $trimmed = trim($term);
        $trimmed_array = explode(' ', $trimmed);

        $query = implode(' AND ', $trimmed_array);
        $query = "persname_t: ($query*) AND recordtype_z:a";

        $client->setParameterGet(['q' => $query]);
        $json = json_decode($client->send()->getBody(), true);
        $results = [];

        foreach ($json['response']['docs'] as $d) {
            array_push($results, $d['affcourt_z']);
        }

        return new JsonModel($results);
    }

    protected function getHttpClient()
    {
        if (!isset($this->httpClient)) {
            $this->httpClient = new Client(self::IDREF_SUGGEST_URL);
        }

        return $this->httpClient;
    }
}

