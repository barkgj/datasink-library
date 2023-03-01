<?php

// https://example.org/?brk=datasink-gui&page=x

//require_once("/srv/generic/libraries-available/nxs-authorization/nxs-authorization.php");
//nxs_authorization_require_OR_operator(array("superadmin", "fromwithininfrastructure", "specialips"));

function brk_datasink_gui_bootstrap()
{
	// escape all slashes that are posted
	// functions::ensureslashesstripped();
	
	$page = $_REQUEST["page"];
	if ($page == "")
	{
	}
	else
	{
		$sanitized_page = "datasink_gui_{$page}";
		// add more sanitization here as needed
		$extension_path = dirname(__FILE__) . "/{$sanitized_page}.php";
		if (file_exists($extension_path))
		{
			require_once($extension_path);
		}
		else
		{
			//
		}
		
		$functionnametoinvoke = "brk_datasink_gui_{$page}";
		if (function_exists($functionnametoinvoke))
		{
			$subresult = call_user_func($functionnametoinvoke, $args);
		}
		else
		{
			echo "file: $extension_path<br /><br />";
			echo "function not yet implemented;<br /><br />";
			echo "<div style='margin-left: 50px; font-family: courier; background-color: #eee;'>";
			echo "function $functionnametoinvoke()<br />";
			echo "{<br />";
			echo "&nbsp;&nbsp;// ... to be implemented<br />";
			echo "}<br />";
			echo "</div>";
			echo "<br />";
			echo "<br />";
		}
	}
		
	echo ":)";
	die();
}

brk_datasink_gui_bootstrap();