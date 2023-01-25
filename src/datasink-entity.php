<?php

namespace barkgj\datasink;

require_once __DIR__ . '/vendor/barkgj/functions-library/src/functions.php';

use barkgj\functions;
use barkgj\functions\filesystem;

final class entity
{
	public static function getentitymeta($entitytype)
	{
		// todo; move to ixplatform eventually...
		// or derive from the 'realm' which defines this in a plugin or so?
		$entities = array
		(
			"shortened_affiliate_url" => array
			(
				"identityfield" => "url",
				"onentitycreate_handlers" => array
				(
					array
					(
						"type" => "createtaskinstance",
						"fields" => array
						(
							"taskid" => 605
						)
					),
				)
			),
			"interface" => array
			(
				"identityfield" => "interface",
				"onentitycreate_handlers" => array
				(
				)
			),
			"solution_reference" => array
			(
				"identityfield" => "url",
				"onentitycreate_handlers" => array
				(
				)
			),
			"competitor_solution_reference" => array
			(
				"identityfield" => "url",
				"onentitycreate_handlers" => array
				(
				)
			),
			"competitor" => array
			(
				"identityfield" => "domain",
				"onentitycreate_handlers" => array
				(
					array
					(
						"type" => "createtaskinstance",
						"fields" => array
						(
							"taskid" => 600
						)
					),
				)
			),
			"sitemap_reference" => array
			(
				"identityfield" => "url",
				"onentitycreate_handlers" => array
				(
					array
					(
						"type" => "createtaskinstance",
						"fields" => array
						(
							"taskid" => 602
						)
					),
				)
			),
			"url_reference_in_sitemap" => array
			(
				"identityfield" => "url",
				"onentitycreate_handlers" => array
				(
					array
					(
						"type" => "createtaskinstance",
						"fields" => array
						(
							"taskid" => 603
						)
					),
				)
			),
			// a "market" which autocompletes in google (which is verified as being autocompleted)
			"autocompleted_keyword" => array
			(
				"identityfield" => "keyword",
				"onentitycreate_handlers" => array
				(
				)
			),
			// a "market" which autocompletes in google (which is verified as being autocompleted)
			"rejected_keyword" => array
			(
				"identityfield" => "keyword",
				"onentitycreate_handlers" => array
				(
				)
			),
			//
			"marketbase" => array
			(
				"identityfield" => "marketbase",
				"onentitycreate_handlers" => array
				(
				)
			),
			//
			"powertuple" => array
			(
				"identityfield" => "powertuple",
				"onentitycreate_handlers" => array
				(
				)
			),
			"irrelevanttuple" => array
			(
				"identityfield" => "irrelevanttuple",
				"onentitycreate_handlers" => array
				(
				)
			),
			"pricerangetuple" => array
			(
				"identityfield" => "pricerangetuple",
				"onentitycreate_handlers" => array
				(
				)
			),
			"brandtuple" => array
			(
				"identityfield" => "brandtuple",
				"onentitycreate_handlers" => array
				(
					array
					(
						"type" => "createtaskinstance",
						"fields" => array
						(
							"taskid" => 604
						)
					),
				)
			),
			//
			"realm" => array
			(
				"identityfield" => "realm",
				"onentitycreate_handlers" => array
				(
				)
			),	
		);
		
		// inject dynamic ones (sub taxonomies of marketbase's)
		if (functions::stringstartswith($entitytype, "marketbase>"))
		{
			$entities[$entitytype] = array
			(
				"identityfield" => $entitytype,
				"onentitycreate_handlers" => array
				(
				)
			);
		}	
		
		if (array_key_exists($entitytype, $entities))
		{
			$result = $entities[$entitytype];
		}
		else
		{
			$result = array
			(
				"identityfield" => "id",
			);
		}
		
		return $result;
	} 
	
	public static function getbasefolder($ensuretrailingdirectoryseperator)
	{
		$result = functions::getsitedatafolder(true) . "datasink" . DIRECTORY_SEPARATOR . "entity" . DIRECTORY_SEPARATOR;

		$sep = DIRECTORY_SEPARATOR;
		if ($ensuretrailingdirectoryseperator)
		{
			$result = rtrim($result, $sep) . $sep;
		}
		else
		{
			$result = rtrim($result, $sep);
		}

		return $result;
	}

	public static function debugentity($args)
	{
		$datasink_realm = $args["datasink_realm"];
		if ($datasink_realm == "")
		{
			functions::throw_nack("debugentity; datasink_realm not specified");
		}

		$datasink_entitytype = $args["datasink_entitytype"];
		if ($datasink_entitytype == "") { functions::throw_nack("debugentity; datasink_entitytype not set"); }
		
		$entitymeta = entity::getentitymeta($datasink_entitytype);
		if ($entitymeta == false) { functions::throw_nack("debugentity; entitytype not supported (1); $datasink_entitytype"); }
		
		$id = $args["id"];
		if (!isset($id)) { functions::throw_nack("debugentity; id not set"); }
		
		$hash = md5($id);
		
		$basefolder = entity::getbasefolder(true);
		$sep = DIRECTORY_SEPARATOR;
		$rawdatafile_path = "{$basefolder}{$datasink_realm}{$sep}entity{$sep}{$datasink_entitytype}-entity{$sep}{$hash}{$sep}{$hash}_data.raw";
		$exists = file_exists($rawdatafile_path);

		$result = array
		(
			"basefolder" => $basefolder,
			"rawdatafile_path" => $rawdatafile_path,
			"exists" => $exists,
		);

		return $result;
	}

	public static function entityexists($args)
	{
		$datasink_realm = $args["datasink_realm"];
		if ($datasink_realm == "")
		{
			functions::throw_nack("entityexists; datasink_realm not specified");
		}

		$datasink_entitytype = $args["datasink_entitytype"];
		if ($datasink_entitytype == "") { functions::throw_nack("entityexists; datasink_entitytype not set"); }
		
		$entitymeta = entity::getentitymeta($datasink_entitytype);
		if ($entitymeta == false) { functions::throw_nack("entityexists; entitytype not supported (1); $datasink_entitytype"); }
		
		$id = $args["id"];
		if (!isset($id)) { functions::throw_nack("entityexists; id not set"); }
		
		$hash = md5($id);
		
		$basefolder = entity::getbasefolder(true);
		$sep = DIRECTORY_SEPARATOR;
		$rawdatafile_path = "{$basefolder}{$datasink_realm}{$sep}entity{$sep}{$datasink_entitytype}-entity{$sep}{$hash}{$sep}{$hash}_data.raw";
		$result = file_exists($rawdatafile_path);
		return $result;
	}

	public static function getentitymetadataraw($args)
	{
		$result = array();
		
		$datasink_realm = $args["datasink_realm"];
		if ($datasink_realm == "")
		{
			functions::throw_nack("entity::getentitymetadataraw; datasink_realm not specified");
		}

		$datasink_entitytype = $args["datasink_entitytype"];
		if ($datasink_entitytype == "") { functions::throw_nack("datasink_entitytype not set"); }
		
		$entitymeta = entity::getentitymeta($datasink_entitytype);
		if ($entitymeta == false) { functions::throw_nack("entitytype not supported (1); $datasink_entitytype"); }
		
		$identity = $args["id"];
		if (!isset($identity)) { functions::throw_nack("entity::getentitymetadataraw; id not set"); }
		
		$hash = md5($identity);
		
		$basefolder = entity::getbasefolder(true);
		$sep = DIRECTORY_SEPARATOR;
		$rawdatafile_path = "{$basefolder}{$datasink_realm}{$sep}entity{$sep}{$datasink_entitytype}-entity{$sep}{$hash}{$sep}{$hash}_data.raw";

		$data_string = file_get_contents($rawdatafile_path);
		
		if (isset($args["output"]) && $args["output"] == "rawstring")
		{
			$result = $data_string;
		}
		else
		{
			$data = json_decode($data_string, true);
			$result = $data;
		}
			
		return $result;
	}

	public static function getentitiesraw($args)
	{
		$result = array();

		$datasink_realm = $args["datasink_realm"];
		if ($datasink_realm == "") { functions::throw_nack("entity::getentitiesraw datasink_realm not specified"); }
		
		$datasink_entitytype = $args["datasink_entitytype"];
		$entitymeta = entity::getentitymeta($datasink_entitytype);
		if ($entitymeta == false) { functions::throw_nack("entity::getentitiesraw; entitytype not supported (5); $datasink_entitytype"); }
		
		$datasink_include_meta = $args["datasink_include_meta"];
		if ($datasink_include_meta == "")
		{
			$datasink_include_meta = false;
		}
		
		//$entitymeta = entity::getentitymeta($datasink_entitytype);
		//$identityfield = $entitymeta["identityfield"];
		
		$basefolder = entity::getbasefolder(true);
		$sep = DIRECTORY_SEPARATOR;
		$hashtoidentity_index_path = "{$basefolder}{$datasink_realm}{$sep}entity{$sep}{$datasink_entitytype}-entity{$sep}{$datasink_entitytype}_hashtoidentity_index.json";
		$hashtoidentity_index_string = file_get_contents($hashtoidentity_index_path);
		$hashtoidentity_index = json_decode($hashtoidentity_index_string, true);

		foreach ($hashtoidentity_index["hashes"] as $hash => $identityfield_value)
		{
			if ($datasink_include_meta == true)
			{
				$getentitymetadatarawargs = array
				(
					"datasink_realm" => $datasink_realm,
					"datasink_entitytype" => $datasink_entitytype,
					"id" => $identityfield_value
				);

				$meta = entity::getentitymetadataraw($getentitymetadatarawargs);
				//
				$result[$identityfield_value] = $meta;
			}
			else
			{
				$result[$identityfield_value] = array();
			}
		}
		
		/*
		if (isset($args["order_keys_by"]))
		{
			$order_keys_by = $args["order_keys_by"];
			if (isset($order_keys_by))
			{
				if (false)
				{
					//
				}
				else if ($order_keys_by == "strlen")
				{
					$keys = array_map('strlen', array_keys($result));
					array_multisort($keys, SORT_DESC, $result);
				}
				else
				{
					functions::throw_nack("unsupported; order_keys_by; $order_keys_by");
				}
			}
		}
		*/
			
		return $result;
	}

	public static function storeentitydata($args)
	{
		$datasink_invokedbytaskid = $args["datasink_invokedbytaskid"];
		$datasink_invokedbytaskinstanceid = $args["datasink_invokedbytaskinstanceid"];

		$datasink_realm = $args["datasink_realm"];
		if ($datasink_realm == "") { functions::throw_nack("entity::storeentitydata; datasink_realm not set"); }

		$datasink_entitytype = $args["datasink_entitytype"];
		$entity_meta = entity::getentitymeta($datasink_entitytype);
		if ($entity_meta == false) { functions::throw_nack("entity::storeentitydata; datasink_entitytype not supported (3); '$datasink_entitytype'"); }
		
		$datasink_identityfield = $entity_meta["identityfield"];
		$datasink_alreadyfoundbehaviour = $args["datasink_alreadyfoundbehaviour"];
		$datasink_accoladesfoundbehaviour = $args["datasink_accoladesfoundbehaviour"];
		
		$basefolder = entity::getbasefolder(true);

		//
			
		$datasink_parameters_prefix = "datasink_";
		
		$entity_specific_parameters = array();
		foreach ($args as $key => $val)
		{
			if (functions::stringstartswith($key, $datasink_parameters_prefix))
			{
				// skip it
				continue;
			}
			else if ($key == "brk_json_output_format")
			{
				$result["ignored"][$key] = $val;
				
				// skip it
				continue;
			}
			else if ($key == "brk")
			{
				$result["ignored"][$key] = $val;
				
				// skip it
				continue;
			}
			
			$entity_specific_parameters[$key] = $val;		// for special chars, perhaps we first to utf8_encode the val?
		}
		$result["entity_specific_parameters"] = $entity_specific_parameters;
		
		$containsvariable = false;
		foreach ($entity_specific_parameters as $key => $val)
		{
			if (functions::stringcontains($val, "{", false))
			{
				$containsvariable = true;
				break;
			}
			else if (functions::stringcontains($val, "}", false))
			{
				$containsvariable = true;
				break;
			}
		}
		
		//
		
		$identityfield_value = $args[$datasink_identityfield];
		if ($identityfield_value == "")
		{
			functions::throw_nack("entity::storeentitydata; identity field '{$datasink_identityfield}' is required (empty or not set) while attempting to store datasink entity; args json=" . json_encode($args));
		}
		
		$entity_specific_hash = md5($identityfield_value);
		
		// handle MERGE for existing entities
		
		//
		$action = false;
		$sep = DIRECTORY_SEPARATOR;
		$hashtoidentity_index_path = "{$basefolder}{$datasink_realm}{$sep}entity{$sep}{$datasink_entitytype}-entity{$sep}{$datasink_entitytype}_hashtoidentity_index.json";
		filesystem::createcontainingfolderforfilepathifnotexists($hashtoidentity_index_path);
		$hashtoidentity_index_string = file_get_contents($hashtoidentity_index_path);
		$hashtoidentity_index = json_decode($hashtoidentity_index_string, true);
		
		if ($containsvariable)
		{
			if (false)
			{
				//
			}
			else if ($datasink_accoladesfoundbehaviour == "ACCEPT")
			{
				// accept accolades
			}
			else if ($datasink_accoladesfoundbehaviour == "SKIP")
			{
				$result["actions"] = "skip (unreplaced placeholder)";
				$action = "SKIP";
			}
			else if ($datasink_accoladesfoundbehaviour == "THROW_NACK")
			{
				functions::throw_nack("entity::storeentitydata; accolades found; not allowed");
			}
			else
			{
				functions::throw_nack("entity::storeentitydata; unsupported datasink_accoladesfoundbehaviour; '$datasink_accoladesfoundbehaviour'");
			}
		}
		
		if ($action == "SKIP")
		{
			// skip it
		}
		else 
		{
			if ($hashtoidentity_index != null && array_key_exists($entity_specific_hash, $hashtoidentity_index["hashes"]))
			{
				if (false)
				{
					//
				}
				else if ($datasink_alreadyfoundbehaviour == "NACK")
				{
					functions::throw_nack("entity::storeentitydata; unable to proceed; entity already there, datasink_alreadyfoundbehaviour=nack, datasink_entitytype:{$datasink_entitytype} identityfield_value:{$identityfield_value}");
				}
				else if ($datasink_alreadyfoundbehaviour == "SKIP")
				{
					$result["actions"] = "skip";
					$action = "SKIP";
				}
				else if ($datasink_alreadyfoundbehaviour == "OVERRIDE")
				{
					//
					$result["actions"] = "commitupdate";
					$action = "COMMITUPDATE";
				}
				else if ($datasink_alreadyfoundbehaviour == "MERGE")
				{
					//
					$result["actions"] = "commitmerge";
					$action = "COMMITMERGE";
				}
				else
				{
					functions::throw_nack("entity::storeentitydata; datasink_alreadyfoundbehaviour not supported; '$datasink_alreadyfoundbehaviour'");
				}
			}
			else
			{
				$result["actions"] = "commitfirst";
				
				$action = "COMMITFIRST";
			}
		}
		
		// handle the hash to identity index
		if (true)
		{
			if (false)
			{
				//
			}
			else if ($action == "COMMITFIRST")
			{
				// its the first time; first store the hash as its not there yet
				$hashtoidentity_index["hashes"][$entity_specific_hash] = $identityfield_value;
				$hashtoidentity_index_string = json_encode($hashtoidentity_index);
				$file_result = file_put_contents($hashtoidentity_index_path, $hashtoidentity_index_string);
				if ($file_result == false)
				{
					functions::throw_nack("entity::storeentitydata; unable to store $hashtoidentity_index_path; $hashtoidentity_index_string");
				}
			}
			else if ($action == "COMMITUPDATE")
			{
				//
			}
			else if ($action == "COMMITMERGE")
			{
				//
			}
			else if ($action == "SKIP")
			{
				
				//
			}
			else
			{
				functions::throw_nack("entity::storeentitydata; unsupported action $action");
			}
		}
		
		// append existing values if the data needs to be merged
		if (true)
		{
			if ($action == "COMMITMERGE")
			{
				// first pull the existing metadata from the entity,
				// then blend it to the one to be committed
				$existing_args = array
				(
					"datasink_realm" => $datasink_realm,
					"datasink_entitytype" => $datasink_entitytype,
					"id" => $identityfield_value
				);
				$existing_metadata = entity::getentitymetadataraw($existing_args);
				foreach ($existing_metadata as $existing_key => $existing_value)
				{
					if (!isset($entity_specific_parameters[$existing_key]))
					{
						$entity_specific_parameters[$existing_key] = $existing_value;
					}
				}
			}
		}
		
		// handle the storing of the raw data
		if (true)
		{
			$rawdata = json_encode($entity_specific_parameters);
			if ($rawdata == false)
			{
				functions::throw_nack("entity::storeentitydata; invalid json?");
			}
			if ($rawdata == "")
			{
				functions::throw_nack("entity::storeentitydata; empty?");
			}		
			
			if (false)
			{
				//
			}
			else if ($action == "SKIP")
			{
				//
			}
			else if ($action == "COMMITFIRST" || $action == "COMMITUPDATE" || $action == "COMMITMERGE")
			{
				$sep = DIRECTORY_SEPARATOR;
				$rawdatafile_path = "{$basefolder}{$datasink_realm}{$sep}entity{$sep}{$datasink_entitytype}-entity{$sep}{$entity_specific_hash}{$sep}{$entity_specific_hash}_data.raw";
				filesystem::createcontainingfolderforfilepathifnotexists($rawdatafile_path);
				
				$file_result = file_put_contents($rawdatafile_path, $rawdata);
				if ($file_result == false)
				{
					functions::throw_nack("entity::storeentitydata; unable to store raw data; $rawdatafile_path; $rawdata");
				}
				$result["rawdatafile_path"] = $rawdatafile_path;
			}
			else
			{
				functions::throw_nack("entity::storeentitydata; unsupported action to store raw data; $action");
			}
		}
		
		// handle events
		if (true)
		{
			if (false)
			{
				//
			}
			else if ($action == "SKIP")
			{
				//
			}
			else if ($action == "COMMITFIRST")
			{
				/*
				$onentitycreate_handlers = $entity_meta["onentitycreate_handlers"];
				foreach ($onentitycreate_handlers as $onentitycreate_handler)
				{
					$onentitycreate_handler_type = $onentitycreate_handler["type"];
					if (false)
					{
						//
					}
					else if ($onentitycreate_handler_type == "createtaskinstance")
					{
						$taskid = $onentitycreate_handler["fields"]["taskid"];
						$assigned_to = "";
						$createdby_taskid = $datasink_invokedbytaskid;
						$createdby_taskinstanceid = $datasink_invokedbytaskinstanceid;
						$mail_assignee = false;
						$inputparameters = $entity_specific_parameters;
						
						$creation_result = createtaskinstance($taskid, $assigned_to, $createdby_taskid, $createdby_taskinstanceid, $mail_assignee, $inputparameters);
						//
					}
					else
					{
						functions::throw_nack("entity::storeentitydata; unsupported onentitycreate_handler_type; $onentitycreate_handler_type");
					}
				}
				*/
			}
			else if ($action == "COMMITUPDATE" || $action == "COMMITMERGE")
			{
				//
			}
			else
			{
				functions::throw_nack("entity::storeentitydata; unsupported action to store raw data; $action");
			}
		}
		
		return $result;
	}

	public static function deleteentity($args)
	{
		$datasink_entitytype = $args["datasink_entitytype"];
		$entity = $args["entity"];
		unset($args["entity"]);
		$entities = array($entity);
		$args["entities"] = $entities;
		$result = entity::deleteentities($args);
		return $result;
	}

	public static function deleteentities($args)
	{
		$datasink_invokedbytaskid = $args["datasink_invokedbytaskid"];
		$datasink_invokedbytaskinstanceid = $args["datasink_invokedbytaskinstanceid"];
		if ($datasink_invokedbytaskid == "") { functions::throw_nack("entity::storeentitydata; datasink_invokedbytaskid not set"); }
		if ($datasink_invokedbytaskinstanceid == "") { functions::throw_nack("entity::storeentitydata; datasink_invokedbytaskinstanceid not set"); }
		
		$entities = $args["entities"];
		
		$datasink_realm = $args["datasink_realm"];
		// fallback for now, should become a required input param
		$datasink_realm = $args["datasink_realm"];
		if ($datasink_realm == "")
		{
			functions::throw_nack("entity::deleteentities; datasink_realm not specified");
		}
		
		$datasink_entitytype = $args["datasink_entitytype"];
		$entity_meta = entity::getentitymeta($datasink_entitytype);
		if ($entity_meta == false) { functions::throw_nack("entity::storeentitydata; datasink_entitytype not supported (4); '$datasink_entitytype'"); }
		
		// remove the entries from the index (hash)
		if (true)
		{	
			$hashtoidentity_index_path = "/srv/mnt/resources/datasink/{$datasink_realm}/entity/{$datasink_entitytype}-entity/{$datasink_entitytype}_hashtoidentity_index.json";
			$hashtoidentity_index_string = file_get_contents($hashtoidentity_index_path);
			$hashtoidentity_index = json_decode($hashtoidentity_index_string, true);
			
			foreach ($entities as $entity)
			{
				$entity_specific_hash = md5($entity);
				unset($hashtoidentity_index["hashes"][$entity_specific_hash]);
			}
			
			$hashtoidentity_index_string = json_encode($hashtoidentity_index);
			$file_result = file_put_contents($hashtoidentity_index_path, $hashtoidentity_index_string);
			if ($file_result == false)
			{
				functions::throw_nack("entity::storeentitydata; unable to store $hashtoidentity_index_path; $hashtoidentity_index_string");
			}
		}
		
		// remove the rawdata
		foreach ($entities as $entity)
		{
			$entity_specific_hash = md5($entity);
			$rawdatafile_path = "/srv/mnt/resources/datasink/{$datasink_realm}/entity/{$datasink_entitytype}-entity/{$entity_specific_hash}/{$entity_specific_hash}_data.raw";
			unlink($rawdatafile_path);
			$rawdatafolder_path = "/srv/mnt/resources/datasink/{$datasink_realm}/entity/{$datasink_entitytype}-entity/{$entity_specific_hash}";
			rmdir($rawdatafolder_path);
		}
		
		$result = array();
		return $result;
	}

	/*
	public static function queryentities($args)
	{
		$datasink_realm = $args["datasink_realm"];
		if ($datasink_realm == "")
		{
			functions::throw_nack("entity::queryentities; datasink_realm not specified");
		}

		$datasink_entitytype = $args["datasink_entitytype"];


		
		$basefolder = entity::getbasefolder(true);
		$sep = DIRECTORY_SEPARATOR;
		$hashtoidentity_index_path = "{$basefolder}{$datasink_realm}{$sep}entity{$sep}{$datasink_entitytype}-entity{$sep}{$datasink_entitytype}_hashtoidentity_index.json";
		$hashtoidentity_index_string = file_get_contents($hashtoidentity_index_path);
		$hashtoidentity_index = json_decode($hashtoidentity_index_string, true);
		
		$query = $args["query"];
		$query_lower = strtolower($query);
		
		$result["count"] = 0;
		
		foreach ($hashtoidentity_index["hashes"] as $hash => $identityfield_value)
		{
			$identity = $identityfield_value;
			
			$shouldinclude = false;
			if ($query_lower == "")
			{
				$shouldinclude = true;
			}
			else if ($query_lower != "")
			{
				$sep = DIRECTORY_SEPARATOR;
				$rawdatafile_path = "{$basefolder}{$datasink_realm}{$sep}entity{$sep}{$datasink_entitytype}-entity{$sep}{$hash}{$sep}{$hash}_data.raw";
				$data_string = file_get_contents($rawdatafile_path);
				$data_string_lower = strtolower($data_string);
				
				if (functions::stringcontains($data_string_lower, $query_lower, false))
				{
					$shouldinclude = true;
				}
			}
			
			if ($shouldinclude)
			{
				$result["identities"][] = $identity;
				$entry = json_decode($data_string, true);
				$result["matches"][] = $entry;
				$result["urls"][] = $entry["url"];
				$result["count"]++;
			}
		}
		
		return $result;
	}
	*/
}