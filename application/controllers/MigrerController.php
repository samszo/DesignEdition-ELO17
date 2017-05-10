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
        $data = json_decode($this->view->data);
		if($this->_getParam('csv')){
	        	foreach ($data as $d) {
	        		$arr =  (array) $d;
	        		//print_r($arr)."<br/>";
	        		if(!$this->view->content)$this->view->content = $s->arrayToCsv(array_keys($arr),",").PHP_EOL;
	        		$this->view->content .= $s->arrayToCsv($arr,",").PHP_EOL;
	        	}
        }else
        		$this->view->content = json_encode($data);
        
    }

}
