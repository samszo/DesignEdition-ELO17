<?php

class MigrerController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $s = new Flux_Site();

        $url = "http://gapai.univ-paris8.fr/DesignEdition/?page=articlejson";
        $this->view->data = $s->getUrlBodyContent($url,false,false);
        $this->view->arr = json_decode($this->view->data);

    }

    if($this->_getParam('csv')){
    				foreach ($data as $s) {
    					if(!$this->view->content)$this->view->content = $data->arrayToCsv(array_keys($s),",").PHP_EOL;
    					$this->view->content .= $data->arrayToCsv($s,",").PHP_EOL;
    				}
    			}else
    				$this->view->content = json_encode($data);


}
