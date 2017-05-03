<?php

/**
    * Récupère le contenu body d'une url
    *
    * @param string 	$url
    * @param array 		$param
    * @param boolean 	$cache
    * @param array	 	$rawData
    *
    * @return string
    */

 function getUrlBodyContent($url, $param=false, $cache=true, $method=null, $rawData=false) {
   $html = false;
   /*pas d'encodage explicite
   if(substr($url, 0, 7)!="http://")$url = urldecode($url);
   */
   if($cache){
     $c = str_replace("::", "_", __METHOD__)."_".md5($url);
     if($param)$c .= "_".$this->getParamString($param);
       $html = $this->cache->load($c);
   }
       if(!$html){
         $client = new Zend_Http_Client($url,array('timeout' => 30));
         if($param && !$method)$client->setParameterGet($param);
         if($param && $method==Zend_Http_Client::POST)$client->setParameterPost($param);
         if($rawData) $client->setRawData($rawData["value"], $rawData["type"]);
         try {
         $response = $client->request($method);
         $html = $response->getBody();
       }catch (Zend_Exception $e) {
         echo "Récupère exception: " . get_class($e) . "\n";
           echo "Message: " . $e->getMessage() . "\n";
       }
           if($cache)$this->cache->save($html, $c);
       }
   return $html;

 }
