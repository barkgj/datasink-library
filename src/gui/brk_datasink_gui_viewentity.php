<?php

// https://global.nexusthemes.com/?nxs=datasink-gui&page=viewentity



function brk_datasink_gui_viewentity()
{
	$realm = $_REQUEST["realm"];
	if ($realm == "")
	{
		// pick realm
		echo "realm not specified, select below:<br />";
		// todo: actually fetch info from datasink or pull from file system
		$realms = array("afnetix.bestwebsitetemplates", "(this list is hardcoded for now)");
		foreach ($realms as $availablerealm)
		{
			$url = nxs_geturlcurrentpage();
			$url = nxs_addqueryparametertourl_v2($url, "realm", $availablerealm, true, true);	

			echo "<a href='{$url}'>{$availablerealm}</a><br />";
		}
		die();
	}

	$entity = $_REQUEST["entity"];
	$identity = $_REQUEST["identity"];
	
	$deleteurl = "https://global.nexusthemes.com/?nxs=datasink-gui&page=deleteentity&realm={$realm}&identity={$identity}&entity={$entity}";
	
	$view = $_REQUEST["view"];
	if ($view == "")
	{
		$view = "var_dump";
	}
	
	// precondition
	if ($entity == "") 
	{
		// pick realm
		echo "entity not specified, select below:<br />";
		
		$dirs = glob("/srv/mnt/resources/datasink/{$realm}/entity/*" , GLOB_ONLYDIR);

		$availableentities = array();
		foreach ($dirs as $dir)
		{
			$entity = basename($dir);
			$entity = str_replace("-entity", "", $entity);
			$availableentities[] = $entity;
		}

		foreach ($availableentities as $availableentity)
		{
			$url = nxs_geturlcurrentpage();
			$url = nxs_addqueryparametertourl_v2($url, "entity", $availableentity, true, true);	

			echo "<a href='{$url}'>{$availableentity}</a><br />";
		}
		die();
	}
	if ($identity == "") 
	{
		echo "identity not specified, select below:<br />";

		$hashtoidentity_index_path = "/srv/mnt/resources/datasink/{$realm}/entity/{$entity}-entity/{$entity}_hashtoidentity_index.json";
		$hashtoidentity_index_string = file_get_contents($hashtoidentity_index_path);
		$hashtoidentity_index = json_decode($hashtoidentity_index_string, true);

		$hashes = $hashtoidentity_index["hashes"];
		foreach ($hashes as $hash => $identity)
		{
			$url = nxs_geturlcurrentpage();
			$url = nxs_addqueryparametertourl_v2($url, "identity", $identity, true, true);	

			echo "<a href='{$url}'>{$identity}</a><br />";	
		}
		die(); 
	}
	
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
	}
}