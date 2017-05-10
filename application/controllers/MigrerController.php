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


            //Fonction copier coller


            function smartCopy($source, $dest, $options=array('folderPermission'=>0777,'filePermission'=>0777))
        {
            $result=false;
            $source = "src= IMG/jpg/elo.jpg";
            $dest = "src= tmp/upload/";


            if (is_file($source)) {
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
                $result=false;
            }
            return $result;
        }
    }

}
