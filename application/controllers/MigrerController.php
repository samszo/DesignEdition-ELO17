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

    //Fonction copier coller
    function smartCopy($source, $dest, $options=array('folderPermission'=>0777,'filePermission'=>0777))
    {
    		$s = new Flux_Site();
    		$s->bTrace = true;
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
	    				echo "$source/$file ||| $__dest<br />";
	    				$result=$this->smartCopy($source."/".$file, $__dest, $options);
	    			}
	    		}
	    		closedir($dirHandle);

	    	} else {
	    		$s->Trace("Pas trouvé");
	    		$result=false;
	    	}
	    	echo ("FIN");

	    	return $result;
    }

}
