<?php

class MigrerController extends Zend_Controller_Action
{
	var $bTrace = false;

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $s = new Flux_Site();

        $url = "http://gapai.univ-paris8.fr/DesignEdition/?page=commentjson";
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

    public function copieAction(){

    		$this->view->content = $this->smartCopy(ROOT_PATH."IMG/jpg/elo.jpg",UPLOAD_PATH."test.jpg");

    }

    public function commentAction(){

        $this->view->content = $this->smartCopy(ROOT_PATH."IMG/mp4/8110393.mp4",UPLOAD_PATH."video.mp4");

    }

    public function csvtoomekaAction(){
    
    		$s = new Flux_Site();
    		$s->bTrace = $this->bTrace;
    		$s->trace(__METHOD__." DEB");
    		
    		$s->trace("récupère les données de SPIP");
    		$url = "http://gapai.univ-paris8.fr/DesignEdition/?page=commentjson&var_mode=recalcul";
    		$s->trace($url);
	    $this->view->json = $s->getUrlBodyContent($url,false,false);
	    	$s->trace($this->view->json);
	    $data = json_decode($this->view->json);
	    	$s->trace("data=",$data);
	    
	    	$s->trace("construction du tableau pour OMEKA");
	    	$dataOmeka = array();
	    	foreach ($data as $d) {
	    		$arr = array("owner"=>$d->auteur,"dcterms:title"=>$d->titre,"dcterms:type"=>"image");
	    		$s->trace("ligne=",$arr);
	    		foreach ($d->doc as $c) {
	    			$path_parts = pathinfo($c->url);	    			
		    		$newUrl = UPLOAD_PATH.$path_parts['basename'];
		    		$this->smartCopy(ROOT_PATH.$c->url,$newUrl);
		    		$arr["Image"] = $newUrl;
	    		}
	    		$dataOmeka[]=$arr;
	    	}
	    	$s->trace("data Omeka",$dataOmeka);
	    	
	    	$s->trace("construction du csv");
	    	foreach ($dataOmeka as $arr) {
	    		$s->trace("Ligne",$arr);
	    		if(!$this->view->content)$this->view->content = $s->arrayToCsv(array_keys($arr),",").PHP_EOL;
	    		$this->view->content .= $s->arrayToCsv($arr,",").PHP_EOL;
	    		$s->trace($this->view->content);	    		 
	    	}
	    	
	    	$s->trace(__METHOD__." FIN");
	    	
    }
    
    
    //Fonction copier coller
    function smartCopy($source, $dest, $options=array('folderPermission'=>0777,'filePermission'=>0777))
    {
    		$s = new Flux_Site();
    		$s->bTrace = $this->bTrace;
    		$s->Trace("$source, $dest");
	    	$result=false;

	    	if (is_file($source)) {
	    		$s->Trace("fichier");
	    		if ($dest[strlen($dest)-1]=='/') {
	    			if (!file_exists($dest)) {
	    				cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
	    			}
	    			$__dest=$dest."/".basename($source);
	    		} else {
	    			$__dest=$dest;
	    		}
	    		$result=copy($source, $__dest);
	    		chmod($__dest,$options['filePermission']);

	    	} elseif(is_dir($source)) {
	    		$s->Trace("dossier");

	    		if ($dest[strlen($dest)-1]=='/') {
	    			if ($source[strlen($source)-1]=='/') {
	    				//Copy only contents
	    			} else {
	    				//Change parent itself and its contents
	    				$dest=$dest.basename($source);
	    				@mkdir($dest);
	    				chmod($dest,$options['filePermission']);
	    			}
	    		} else {
	    			if ($source[strlen($source)-1]=='/') {
	    				//Copy parent directory with new name and all its content
	    				@mkdir($dest,$options['folderPermission']);
	    				chmod($dest,$options['filePermission']);
	    			} else {
	    				//Copy parent directory with new name and all its content
	    				@mkdir($dest,$options['folderPermission']);
	    				chmod($dest,$options['filePermission']);
	    			}
	    		}
	    		$dirHandle=opendir($source);
	    		while($file=readdir($dirHandle))
	    		{
	    			if($file!="." && $file!="..")
	    			{
	    				if(!is_dir($source."/".$file)) {
	    					$__dest=$dest."/".$file;
	    				} else {
	    					$__dest=$dest."/".$file;
	    				}
	    				$s->Trace("$source/$file ||| $__dest<br />");
	    				$result=$this->smartCopy($source."/".$file, $__dest, $options);
	    			}
	    		}
	    		closedir($dirHandle);

	    	} else {
	    		$s->Trace("Pas trouvé");
	    		$result=false;
	    	}
	    	$s->Trace("résulat copie ".($result) ? 'OK' : 'KO');

	    	return $result;
    }

}
