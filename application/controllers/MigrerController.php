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
        $this->view->data = $s->getUrlBodyContent($url); 
        
    }


    
    
}

