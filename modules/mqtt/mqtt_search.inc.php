<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  //searching 'TITLE' (varchar)
  global $title;
  if ($title!='') {
   $qry.=" AND (TITLE LIKE '%".DBSafe($title)."%' OR VALUE LIKE '%".DBSafe($title)."%' OR PATH LIKE '%".DBSafe($title)."%'";
   $qry.=" OR LINKED_OBJECT LIKE '".DBSafe($title)."'";
   $qry.=" OR LINKED_PROPERTY LIKE '".DBSafe($title)."'";
   $qry.=" OR LINKED_METHOD LIKE '".DBSafe($title)."'";
   $qry.=")";
   $out['TITLE']=$title;
  }

  global $location_id;
  if ($location_id) {
   $qry.=" AND LOCATION_ID='".(int)$location_id."'";
   $out['LOCATION_ID']=(int)$location_id;
  }

  if (IsSet($this->location_id)) {
   $location_id=$this->location_id;
   $qry.=" AND LOCATION_ID='".$this->location_id."'";
  } else {
   global $location_id;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['mqtt_qry'];
  } else {
   $session->data['mqtt_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_mqtt;
  if (!$sortby_mqtt) {
   $sortby_mqtt=$session->data['mqtt_sort'];
  } else {
   if ($session->data['mqtt_sort']==$sortby_mqtt) {
    if (Is_Integer(strpos($sortby_mqtt, ' DESC'))) {
     $sortby_mqtt=str_replace(' DESC', '', $sortby_mqtt);
    } else {
     $sortby_mqtt=$sortby_mqtt." DESC";
    }
   }
   $session->data['mqtt_sort']=$sortby_mqtt;
  }
  //if (!$sortby_mqtt) $sortby_mqtt="ID DESC";
  $sortby_mqtt="UPDATED DESC";
  $out['SORTBY']=$sortby_mqtt;

  global $tree;
  if (!isset($tree)) {
   $tree=(int)$session->data['MQTT_TREE_VIEW'];
  } else {
   $session->data['MQTT_TREE_VIEW']=$tree;
  }

  if (isset($_GET['tree'])) {
   $tree=(int)$_GET['tree'];
   $this->config['TREE_VIEW']=$tree;
   $this->saveConfig();
  } else {
   $tree = $this->config['TREE_VIEW'];
  }

  if ($tree) {
   $out['TREE']=1;
  }

  // SEARCH RESULTS
  if ($out['TREE']) {
   $sortby_mqtt='PATH';
  }
  $res=SQLSelect("SELECT * FROM mqtt WHERE $qry ORDER BY ".$sortby_mqtt);
  if ($res[0]['ID']) {
   if (!$out['TREE']) {
    paging($res, 50, $out); // search result paging
   }
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    //$tmp=explode(' ', $res[$i]['UPDATED']);
    //$res[$i]['UPDATED']=fromDBDate($tmp[0])." ".$tmp[1];
    $res[$i]['VALUE']=str_replace('":','": ',$res[$i]['VALUE']);

    if ($res[$i]['TITLE']==$res[$i]['PATH'] && !$out['TREE']) $res[$i]['PATH']='';
   }
   $out['RESULT']=$res;

   if ($out['TREE']) {
    $out['RESULT']=$this->pathToTree($res);
   }

  }


  $out['LOCATIONS']=SQLSelect("SELECT * FROM locations ORDER BY TITLE");

?>