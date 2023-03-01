<?php

namespace barkgj\datasink;

//require_once __DIR__ . '/vendor/barkgj/functions-library/src/functions.php';

use barkgj\functions;
use barkgj\functions\filesystem;
use barkgj\datasink\entity;

final class url
{
	public static function getbasefolder($ensuretrailingdirectoryseperator)
	{
		$result = functions::getsitedatafolder(true) . "datasink" . DIRECTORY_SEPARATOR . "url" . DIRECTORY_SEPARATOR;

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

	public static function storeurldata($args)
	{
		$datasink_time = time();

		$datasink_realm = $args["datasink_realm"];
		if ($datasink_realm == "") { functions::throw_nack("datasink_realm not specified"); }

		$datasink_url = $args["datasink_url"];
		if ($datasink_url == "") { functions::throw_nack("datasink_url not specified"); }
		$hostname = parse_url($datasink_url, PHP_URL_HOST);
		
		// fetch the data
		$result["invoked_url"] = $datasink_url;
		$start = microtime(true);
		// invoke the api
		// TODO: consider to use a lambda web proxy in between; https://ianwhitestone.work/free-python-proxy-server/
		
		// todo; allow user to override this
		$useragent = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36";

		$context = stream_context_create
		(
			array
			(
				"http" => array
				(
					"header" => "User-Agent: {$useragent}"
				)
			)
		);

		$rawdata = file_get_contents($datasink_url, false, $context);
		$response_header = $http_response_header;
		$end = microtime(true);
		$duration = round(($end - $start) * 1000);
		
		if ($rawdata == false)
		{
			$error = error_get_last();
			functions::throw_nack("brk_datasink_storeurldata; invalid response; $datasink_url; jsonerror:" . json_encode($error) . " jsonresponse_header:" . json_encode($response_header));
		}
		if ($rawdata == "")
		{
			functions::throw_nack("brk_datasink_storeurldata; empty response; $datasink_url");
		}
		
		$result["rawdata"] = $rawdata;

		$hash = md5(serialize($datasink_url));

		$basefolder = url::getbasefolder(true);
		$sep = DIRECTORY_SEPARATOR;

		// 1
		$hashtourl_index_path = "{$basefolder}{$datasink_realm}{$sep}url{$sep}{$hostname}{$sep}hashtourl_index.json";
		filesystem::createcontainingfolderforfilepathifnotexists($hashtourl_index_path);
		if (file_exists($hashtourl_index_path))
		{
			//
			$hashtourl_index_string = file_get_contents($hashtourl_index_path);
			$hashtourl_index = json_decode($hashtourl_index_string, true);
		}
		else
		{
			$hashtourl_index = array();
			$hashtourl_index["hashes"] = array();
		}
		
		if (!in_array($hash, $hashtourl_index["hashes"]))
		{
			$hashtourl_index["hashes"][$hash] = $datasink_url;
			$hashtourl_index_string = json_encode($hashtourl_index);
			$file_result = file_put_contents($hashtourl_index_path, $hashtourl_index_string);
			if ($file_result == false)
			{
				functions::throw_nack("brk_datasink_storeurldata; unable to store $hashtourl_index_path; $hashtourl_index_string");
			}
		}
		else
		{
			// hash is already in there, ignore (this is ok, since we stored the rawdata time-based)
		}
		
		// 2
		$history_index_path = "{$basefolder}{$datasink_realm}{$sep}url{$sep}{$hostname}{$sep}{$hash}{$sep}{$hash}_history_index.json";
		
		//create folder if needed
		filesystem::createcontainingfolderforfilepathifnotexists($history_index_path);
		
		if (file_exists($history_index_path))
		{
			$history_index_string = file_get_contents($history_index_path);
			$history_index = json_decode($history_index_string, true);	
		}
		else
		{
			$history_index = array();
			$history_index["timestamps"] = array();
		}

		if (in_array($datasink_time, $history_index["timestamps"]))
		{
			functions::throw_nack("brk_datasink_storeurldata; timestamp already there? $history_index_path; $datasink_time");
		}
		$history_index["timestamps"][] = $datasink_time;
		$history_index_string = json_encode($history_index);
		$file_result = file_put_contents($history_index_path, $history_index_string);
		if ($file_result == false)
		{
			$error = error_get_last();
			functions::throw_nack("brk_datasink_storeurldata; unable to store $history_index_path; $history_index_string; $error");
		}
		
		// store raw data as versioned data
		// 3
		$rawdatafile_path = "{$basefolder}{$datasink_realm}{$sep}url{$sep}{$hostname}{$sep}{$hash}{$sep}{$hash}_{$datasink_time}_data.raw";
		$file_result = file_put_contents($rawdatafile_path, $rawdata);
		if ($file_result == false)
		{
			functions::throw_nack("brk_datasink_storeurldata; unable to store raw data; $rawdatafile_path; $rawdata");
		}
		
		// store metadata
		// 4
		$metadatafile_path = "{$basefolder}{$datasink_realm}{$sep}url{$sep}{$hostname}{$sep}{$hash}{$sep}{$hash}_{$datasink_time}_metadata.json";
		$metadata = array
		(
			"crc32" => crc32($rawdata),
			"size" => strlen($rawdata),
			"args" => $args,
			"api_invocation_duration_msecs" => $duration,
			"response_header" => $response_header
		);
		$metadatastring = json_encode($metadata);
		
		$file_result = file_put_contents($metadatafile_path, $metadatastring);
		if ($file_result == false)
		{
			functions::throw_nack("brk_datasink_storeurldata; unable to store $datafile_path; $datastring");
		}

		$result["metadata"] = $metadata;
		
		$result["datasink_time"] = $datasink_time;
		$result["history_index_path"] = $history_index_path;
		$result["rawdatafile_path"] = $rawdatafile_path;
		$result["metadatafile_path"] = $metadatafile_path;
		
		return $result;
	}

	public static function getavailabletimestamps($args)
	{
		$datasink_realm = $args["datasink_realm"];
		if ($datasink_realm == "") { functions::throw_nack("brk_datasink_url_getavailabletimestamps; datasink_realm not specified"); }

		$datasink_url = $args["datasink_url"];
		$hostname = parse_url($datasink_url, PHP_URL_HOST);
		if ($hostname == "") { functions::throw_nack("hostname not specified"); }
		$datasink_parameters_prefix = "datasink_";
		
		$hash = md5(serialize($datasink_url));
		
		$basefolder = url::getbasefolder(true);
		$sep = DIRECTORY_SEPARATOR;

		// 5
		$history_index_path = "{$basefolder}{$datasink_realm}{$sep}url{$sep}{$hostname}{$sep}{$hash}{$sep}{$hash}_history_index.json";
		if (file_exists($history_index_path))
		{
			$history_index_string = file_get_contents($history_index_path);
			$history_index = json_decode($history_index_string, true);
			$result = $history_index["timestamps"];	
		}
		else
		{
			$result = array();	// empty
		}
		
		return $result;
	}

	public static function geturldata($args)
	{
		$datasink_realm = $args["datasink_realm"];
		$datasink_url = $args["datasink_url"];
		$datasink_time = $args["datasink_time"];
		$datasink_return = $args["datasink_return"];
		
		if ($datasink_realm == "") { functions::throw_nack("brk_datasink_geturldata; datasink_realm not specified"); }
		if ($datasink_url == "") { functions::throw_nack("datasink_url not specified"); }
		$hostname = parse_url($datasink_url, PHP_URL_HOST);
		$hostname = functions::getdomainforhostname($hostname, "thrownack");
		if ($hostname == "") { functions::throw_nack("hostname not specified"); }
		if ($datasink_time == "") { functions::throw_nack("datasink_time not specified"); }
		if ($datasink_return == "") { functions::throw_nack("datasink_return not specified"); }
		
		$basefolder = url::getbasefolder(true);
		$sep = DIRECTORY_SEPARATOR;

		$datasink_parameters_prefix = "datasink_";
		
		$hash = md5(serialize($datasink_url));
		
		// 6
		$history_index_path = "{$basefolder}{$datasink_realm}{$sep}url{$sep}{$hostname}{$sep}{$hash}{$sep}{$hash}_history_index.json";
		if (file_exists($history_index_path))
		{
			$history_index_string = file_get_contents($history_index_path);
			$history_index = json_decode($history_index_string, true);
			$timestamps = $history_index["timestamps"];
			
			$foundinindex = false;
			
			if (false)
			{
				//
			}
			else if ($datasink_time == "MOST_RECENT")
			{
				if (count($timestamps) > 0)
				{
					$datasink_time = end($timestamps);
					$foundinindex = true;
				}
				else
				{
					$foundinindex = false;
				}
			}
			else if (brk_stringstartswith($datasink_time, "TIMESTAMP:"))
			{
				$datasink_time = str_replace("TIMESTAMP:", "", $datasink_time);
				$foundinindex = in_array($datasink_time, $timestamps);
			}
			else
			{
				functions::throw_nack("datasink_time not supported; $datasink_time");
			}
		}
		else
		{
			$foundinindex = false;
		}
		
		if ($foundinindex)
		{
			if (false)
			{
				//
			}
			else if ($datasink_return == "RAW")
			{
				// 7
				$rawdatafile_path = "{$basefolder}{$datasink_realm}{$sep}url{$sep}{$hostname}{$sep}{$hash}{$sep}{$hash}_{$datasink_time}_data.raw";
				if (file_exists($rawdatafile_path))
				{
					$data = file_get_contents($rawdatafile_path);
					return $data;
				}
				else
				{
					$result["datasink_meta"] = array
					(
						"foundinindex" => true,
						"found" => false,
						"history_index_path" => $history_index_path,
						"rawdatafile_path" => $rawdatafile_path
					);
				}
			}
			else if ($datasink_return == "META")
			{
				// 8
				$metadatafile_path = "{$basefolder}{$datasink_realm}{$sep}url{$sep}{$hostname}{$sep}{$hash}{$sep}{$hash}_{$datasink_time}_metadata.json";
				if (file_exists($metadatafile_path))
				{
					$metadata = file_get_contents($metadatafile_path);				
				}
			}
			else if ($datasink_return == "RAW_AND_META")
			{
				// 9
				$metadata = array();
				$metadatafile_path = "{$basefolder}{$datasink_realm}{$sep}url{$sep}{$hostname}{$sep}{$hash}{$sep}{$hash}_{$datasink_time}_metadata.json";
				if (file_exists($metadatafile_path))
				{
					$metadata_string = file_get_contents($metadatafile_path);
					$metadata = json_decode($metadata_string, true);
					
					// 10
					$rawdatafile_path = "{$basefolder}{$datasink_realm}{$sep}url{$sep}{$hostname}{$sep}{$hash}{$sep}{$hash}_{$datasink_time}_data.raw";
					if (file_exists($rawdatafile_path))
					{
						$data = file_get_contents($rawdatafile_path);
						$result["raw"] = $data;
						$result["metadata"] = $metadata;
						$result["found"] = true;
					}
					else
					{
						$result["found"] = false;
					}
				}
				else
				{
					$result["found"] = false;
				}
			}
			else
			{
				functions::throw_nack("brk_datasink_getapidata; datasink_return not supported: '$datasink_return'");
			}
		}
		else
		{
			if ($datasink_return == "RAW_AND_META")
			{
				$result["found"] = false;
			}
			else
			{
				$result["datasink_meta"] = array
				(
					"foundinindex" => false,
					"found" => false,
					"history_index_path" => $history_index_path,
				);
			}
		}
		
		return $result;
	}
}