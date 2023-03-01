<?php



function brk_datasink_gui_editentity()
{
	$entity = $_REQUEST["entity"];
	$identity = $_REQUEST["identity"];
	$realm = $_REQUEST["realm"];
		
	// precondition
	if ($entity == "") { echo "entity not set"; die(); }
	if ($identity == "") { echo "identity not set"; die(); }
	if ($realm == "") { echo "realm not set"; die(); }
	
	$action = $_REQUEST["action"];
	if ($action == "updateentity")
	{
		$entitydatajson = $_REQUEST["entitydatajson"];
		$entitydata = json_decode($entitydatajson, true);
		if ($entitydata == null) { echo "invalid json? or empty? not allowed!"; die(); } 
		if ($entitydata == "") { echo "invalid json? or empty? not allowed!"; die(); } 
		
		$store_args = array
		(
			"datasink_invokedbytaskid" => "x",
			"datasink_invokedbytaskinstanceid" => "x",
			"datasink_realm" => $realm,
			"datasink_entitytype" => $entity,
			"datasink_alreadyfoundbehaviour" => "OVERRIDE",	
		);
		$store_args = array_merge($store_args, $entitydata);
		$store_result = brk_datasink_storeentitydata($store_args);
		
		echo "store result:<br />";
		var_dump($store_result);
		
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
	
	echo "<br /><br />";
	
	$json = json_encode($interface_meta, JSON_PRETTY_PRINT);
	
	echo "<form method='POST'>";
	echo "<input type='hidden' name='action' value='updateentity' />";
	echo "<input type='hidden' name='entity' value='{$entity}' />";
	echo "<input type='hidden' name='identity' value='{$identity}' />";
	echo "<textarea name='entitydatajson' style='width: 80vw; min-height:50vh;'>";
	echo $json;
	echo "</textarea>";
	echo "<br /><br />";
	echo "<input type='submit' value='Save' />";
	echo "</form>";
	
	echo "<br /><br />";
}