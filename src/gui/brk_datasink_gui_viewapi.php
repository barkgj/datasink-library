<?php

// https://global.nexusthemes.com/?nxs=datasink-gui&page=viewapi



function brk_datasink_gui_viewapi()
{
	echo "to be implemented";
	die();

	$datasink_args_json = $_REQUEST["datasink_args_json"];
	$datasink_args = json_decode($datasink_args_json, true);
	/* 
	$r = brk_datasink_getapidata($datasink_args);
	
	$view = $_REQUEST["view"];
	if ($view == "")
	{
		$view = "var_dump";
	}
	
	// precondition
	if ($entity == "") { echo "entity not set"; die(); }
	if ($identity == "") { echo "identity not set"; die(); }
	
	$args = array
	(
		"datasink_realm" => $realm,
		"datasink_entitytype" => $entity,
		"identity" => $identity,
	);
	$interface_meta = brk_datasink_getentitymetadataraw($args);
	
	echo "entity: {$entity}<br />";
	echo "identity: {$identity}<br />";
	echo "actions: <a href='{$deleteurl}'>delete</a><br />";
	echo "views: ";
	$availableviews = array("var_dump", "json_encode");
	$viewpieces = array();
	foreach ($availableviews as $availableview)
	{		
		if ($availableview == $view)
		{
			$viewpieces[]= "{$availableview} (current)";
		}
		else
		{
			$availableviewurl = nxs_geturlcurrentpage();
			$availableviewurl = nxs_addqueryparametertourl_v2($availableviewurl, "view", $availableview, true, true);	

			$viewpieces[]= "<a href='{$availableviewurl}'>{$availableview}</a>";
		}
	}
	echo implode(" | ", $viewpieces);
	echo "<br /><br />";
	
	if ($view == "var_dump")
	{
		var_dump($interface_meta);
	}
	else if ($view == "json_encode")
	{
		$json = json_encode($interface_meta, JSON_PRETTY_PRINT);
		echo "<textarea readonly style='width: 80vw; min-height:50vh;'>";
		echo $json;
		echo "</textarea>";
		echo "<br /><br />";
		
		$editurl = nxs_geturlcurrentpage();
		$editurl = nxs_addqueryparametertourl_v2($availableviewurl, "page", "editentity", true, true);	
		echo "<a href='{$editurl}'>edit json</a>";
		echo "<br /><br />";
	} */
}