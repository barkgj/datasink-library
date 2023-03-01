<?php

// https://global.nexusthemes.com/?nxs=datasink-gui&page=viewentity

function brk_datasink_gui_deleteentity()
{
	$realm = $_REQUEST["realm"];
	$entity = $_REQUEST["entity"];
	$identity = $_REQUEST["identity"];
	
	// precondition
	if ($realm == "") { echo "realm not set"; die(); }
	if ($entity == "") { echo "entity not set"; die(); }
	if ($identity == "") { echo "identity not set"; die(); }
	
	$action = $_REQUEST["action"];
	if ($action != "")
	{
		if ($action == "DELETE")
		{
			echo "deleting... <br />";
			$args = array
			(
				"datasink_realm" => $realm,
				"datasink_invokedbytaskid" => "x",
				"datasink_invokedbytaskinstanceid" => "x",
				"datasink_entitytype" => $entity,
				"entities" => array($identity),
			);
			$r=brk_datasink_deleteentities($args);
			var_dump($r);
			echo "<br />";
			echo "done!";
			die();	
		}
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
	
	?>
	<form method='POST'>
		<input type=hidden name=action value='DELETE' />
		<input type=submit value='Confirm to delete' />
	</form>
	<?php
}