<?php

// https://global.nexusthemes.com/?nxs=datasink-gui&page=viewurl&datasink_url=...




// kudos to https://stackoverflow.com/questions/10309094/display-calendar-on-php
function brk_datasink_gui_generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array())
{
	$first_of_month = gmmktime(0, 0, 0, $month, 1, $year);
	// remember that mktime will automatically correct if invalid dates are entered
	// for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
	// this provides a built in "rounding" feature to generate_calendar()
	
	$day_names = array(); //generate all the day names according to the current locale
	for ($n = 0, $t = (3 + $first_day) * 86400; $n < 7; $n++, $t+=86400) //January 4, 1970 was a Sunday
	    $day_names[$n] = ucfirst(gmstrftime('%A', $t)); //%A means full textual day name
	
	list($month, $year, $month_name, $weekday) = explode(',', gmstrftime('%m, %Y, %B, %w', $first_of_month));
	$weekday = ($weekday + 7 - $first_day) % 7; //adjust for $first_day
	$title   = htmlentities(ucfirst($month_name)) . $year;  //note that some locales don't capitalize month and day names
	
	//Begin calendar .  Uses a real <caption> .  See http://diveintomark . org/archives/2002/07/03
	@list($p, $pl) = each($pn); @list($n, $nl) = each($pn); //previous and next links, if applicable
	if($p) $p = '<span class="calendar-prev">' . ($pl ? '<a href="' . htmlspecialchars($pl) . '">' . $p . '</a>' : $p) . '</span>&nbsp;';
	if($n) $n = '&nbsp;<span class="calendar-next">' . ($nl ? '<a href="' . htmlspecialchars($nl) . '">' . $n . '</a>' : $n) . '</span>';
	$calendar = "<div class=\"mini_calendar\">\n<table>" . "\n" . 
	    '<caption class="calendar-month">' . $p . ($month_href ? '<a href="' . htmlspecialchars($month_href) . '">' . $title . '</a>' : $title) . $n . "</caption>\n<tr>";
	
	if($day_name_length)
	{   //if the day names should be shown ($day_name_length > 0)
	    //if day_name_length is >3, the full name of the day will be printed
	    foreach($day_names as $d)
	        $calendar  .= '<th abbr="' . htmlentities($d) . '">' . htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d) . '</th>';
	    $calendar  .= "</tr>\n<tr>";
	}
	
	if($weekday > 0) 
	{
	    for ($i = 0; $i < $weekday; $i++) 
	    {
	        $calendar  .= '<td>&nbsp;</td>'; //initial 'empty' days
	    }
	}
	for($day = 1, $days_in_month = gmdate('t',$first_of_month); $day <= $days_in_month; $day++, $weekday++)
	{
	    if($weekday == 7)
	    {
	        $weekday   = 0; //start a new week
	        $calendar  .= "</tr>\n<tr>";
	    }
	    if(isset($days[$day]) and is_array($days[$day]))
	    {
	        @list($link, $classes, $content) = $days[$day];
	        if(is_null($content))  $content  = $day;
	        $calendar  .= '<td' . ($classes ? ' class="' . htmlspecialchars($classes) . '">' : '>') . 
	            ($link ? '<a href="' . htmlspecialchars($link) . '">' . $content . '</a>' : $content) . '</td>';
	    }
	    else $calendar  .= "<td>$day</td>";
	}
	if($weekday != 7) $calendar  .= '<td id="emptydays" colspan="' . (7-$weekday) . '">&nbsp;</td>'; //remaining "empty" days
	
	return $calendar . "</tr>\n</table>\n</div>\n";
}

function brk_datasink_gui_viewurl()
{
	$datasink_realm = $_REQUEST["datasink_realm"];
	if ($datasink_realm == "") { echo "datasink_realm not specified"; die(); }

	$datasink_url = $_REQUEST["datasink_url"];
	if ($datasink_url == "") { echo "datasink_url not specified"; die(); }
	
	$datasink_time = $_REQUEST["datasink_time"];

	?>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	<?php

	echo "<form>";
	echo "<input style='width: 30%;' type='text' name='datasink_url' value='{$datasink_url}' />";
	echo "<input type='submit' value='Go' />";
	echo "</form>";

	echo "<br />";

	// datasink url section
	$args = array
	(
		"datasink_url" => $datasink_url,
	);
	
	$timestamps = brk_datasink_url_getavailabletimestamps($args);
	$count = count($timestamps);
	
	if ($count == 0)
	{
		echo "Saved <b>{$count} times</b><br />";
	}
	else
	{
		if ($datasink_time == "")
		{
			$oldest = min($timestamps);
			$oldest_human = date('Y-m-d H:i:s', $oldest);
			$newest = max($timestamps);
			$newest_human = date('Y-m-d H:i:s', $newest);
			echo "Saved <b>{$count} times</b> between {$oldest_human} and {$newest_human}<br />";
			
			
			
			$time = time();
			$today = date('j', $time);
					
			echo "<div>";
			$year = date('Y', $time);
			for ($month = 1; $month <= 12; $month++)
			{
				$timestamps_per_day = array();
				foreach ($timestamps as $timestamp)
				{
					if (date('Y', $timestamp) == $year)
					{
						if (date('n', $timestamp) == $month)
						{
							$timestamp_day_of_month = date('j', $timestamp);
							$timestamps_per_day[$timestamp_day_of_month][] = $timestamp;
						}
					}
				}
				$highlighted_days_of_month = array();
				$days = array_keys($timestamps_per_day);
				foreach ($days as $day)
				{
					$timestamps_on_day = count($timestamps_per_day[$day]);
					
					if ($timestamps_on_day == 1)
					{
						$selecttimestampurl = "https://global.nexusthemes.com/?nxs=marketanalysis-gui&page=debugdatasinkurl&datasink_url={$datasink_url}&datasink_time={$timestamp}";
						$item = array
						(
							null,
							null,
							"<a href='{$selecttimestampurl}' title='one snapshot available'><div style='padding: 2px; background-color: green; color: white;'>{$day}</div></a>"
						);
						$highlighted_days_of_month[$day]=$item;
					}
					else 
					{
						// multiple items
						
						$item = array
						(
							null,
							null,
							"<a href='#' onclick='nxs_js_showpopup_{$year}_{$month}{$day}(); return false;' title='{$timestamps_on_day} snapshots available'><div style='padding: 2px; background-color: lightgreen; color: white;'>{$day}</div></a>"
						);
						$highlighted_days_of_month[$day]=$item;
						
						echo "<script>";
						echo "function nxs_js_showpopup_{$year}_{$month}{$day}() {";
						echo "jQuery('#popup_{$year}_{$month}_{$day}').show();";
						echo "}";
						echo "</script>";
						echo "<div id='popup_{$year}_{$month}_{$day}' class='modal' style='display: none; '>";
						echo "<div class='modal-content'>";
						echo "<div><a href='#' onclick=\"jQuery('.modal').hide(); return false;\">close</a></div>";
						echo "pick a timestamp:<br />";
						foreach ($timestamps_per_day[$day] as $timestamp_on_day)
						{
							$timestamp_human = date("Y-m-d H:i:s", $timestamp_on_day);
							$selecttimestampurl = "https://global.nexusthemes.com/?nxs=marketanalysis-gui&page=debugdatasinkurl&datasink_url={$datasink_url}&datasink_time={$timestamp_on_day}";
							echo "<a href='{$selecttimestampurl}'>";
							echo "<div style='padding: 2px; background-color: green; color: white;'>{$timestamp_human}</div>";
							echo "</a>";
						}
						echo "</div>";
						echo "</div>";
						?>
						<style>
							.modal
							{
								position: fixed; /* Stay in place */
		  					z-index: 1; /* Sit on top */
							  left: 0;
							  top: 0;
							  width: 100%; /* Full width */
							  height: 100%; /* Full height */
							  overflow: auto; /* Enable scroll if needed */
							  background-color: rgb(0,0,0); /* Fallback color */
							  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
							}
							.modal-content 
							{
							  background-color: #fefefe;
							  margin: 15% auto; /* 15% from the top and centered */
							  padding: 20px;
							  border: 1px solid #888;
							  width: 80%; /* Could be more or less, depending on screen size */
							}							
						</style>
						<?php
					}
				}
				
				// 
				$today  = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
	
				for ($day = 1; $day <= 31; $day++)
				{
					if (!array_key_exists($day, $highlighted_days_of_month))
					{
						$current_day = mktime(0, 0, 0, $month, $day, $year);
						if ($current_day > $today)
						{
							// its the future
							$item = array
							(
								null,
								null,
								"<div class='future' style='opacity: 0'>X</div>"
							);
							$highlighted_days_of_month[$day]=$item;
						}
						else
						{
							$item = array
							(
								null,
								null,
								"<div style='padding: 2px; opacity: 0'>X</div>"
							);
							$highlighted_days_of_month[$day]=$item;
						}
					}
				}
				
				if ($month % 2 == 0)
				{
					$backgroundcolor = "#eee";
				}
				else
				{
					$backgroundcolor = "#ddd";
				}
				echo "<div style='float:left; background-color: {$backgroundcolor}'>";
				echo brk_datasink_gui_generate_calendar($year, $month, $highlighted_days_of_month, 1, null, 0);
				echo "</div>";
			}
			echo "<div style='clear:both;'>&nbsp;</div>";
			echo "</div>";
			
			if ($datasink_time == "")
			{
				foreach ($timestamps as $timestamp)
				{
					$timestamp_human = date('Y-m-d H:i:s', $timestamp);
					$picktimestamp_url = "https://global.nexusthemes.com/?nxs=marketanalysis-gui&page=debugdatasinkurl&datasink_url={$datasink_url}&datasink_time={$timestamp}";
					
				}
			}
		}
		else
		{
			if (in_array($datasink_time, $timestamps))
			{
				echo "<form>";
				$page = $_REQUEST["page"];
				echo "<input type='hidden' name='page' value='{$page}' />";
				$backurl = $_REQUEST["backurl"];
				echo "<input type='hidden' name='backurl' value='{$backurl}' />";
				$nxs = $_REQUEST["nxs"];
				echo "<input type='hidden' name='nxs' value='{$nxs}' />";
				echo "<input type='hidden' name='datasink_url' value='{$datasink_url}' />";
				echo "<select name='datasink_time'>";
				foreach ($timestamps as $available_timestamp)
				{
					$human_available_timestamp = date('Y-m-d H:i:s', $available_timestamp);
					if ($available_timestamp == $datasink_time)
					{
						echo "<option selected value='{$available_timestamp}'>{$human_available_timestamp}</option>";	
					}
					else
					{
						echo "<option value='{$available_timestamp}'>{$human_available_timestamp}</option>";	
					}
				}
				echo "</select>";
				echo "<input type='submit' value='Go' />";
				echo "</form>";
	
				
				echo "snapshot date: {$human_datasink_time}<br /><br />";
				
				// exists, now get the actual data so we can present it
				
				echo "<div id='tabs' style='margin-bottom: 20px'>";
				echo "<a style='padding: 5px; background-color: black; color: white;' href='#' onclick=\"jQuery('#rawview').toggle(); return false;\">RAW</a>";
				echo "<a style='padding: 5px; background-color: black; color: white;' href='#' onclick=\"jQuery('#seoview').toggle(); return false;\">SEO</a>";
				echo "</div>";
				
				
				
				// -----
				
				$args = array
				(
					"datasink_url" => $datasink_url,
					"datasink_time" => "TIMESTAMP:{$datasink_time}",
					"datasink_realm" => $datasink_realm,
					"datasink_return" => "TRANSFORM",
					"datasink_transformation" => "seo_aspects"
				);
				$transformed_seo_data = brk_datasink_geturldata($args);
				
				echo "<div id='seoview' style='width: 100%;'>";
				
				$pagetitle = $transformed_seo_data["datasink_meta"]["title"];
				$lastmodifiedtime = $transformed_seo_data["datasink_meta"]["lastmodifiedtime"];
				$headings = $transformed_seo_data["datasink_meta"]["headings"];
				$schemamarkups = $transformed_seo_data["datasink_meta"]["schemamarkups"];
				$tables = $transformed_seo_data["datasink_meta"]["tables"];
				
				echo "page title: {$pagetitle}<br />";
				echo "lastmodifiedtime: {$lastmodifiedtime}<br />";
				echo "headings:<br />";
				var_dump($headings);
				echo "<br />";
				echo "schemamarkups:<br />";
				var_dump($schemamarkups);
				echo "<br />";
				echo "tables:<br />";
				var_dump($tables);
				echo "<br />";
				
				
				
				
				echo "<br />";
				echo "<br />";
				//
				var_dump($transformed_seo_data);
				echo "</div>";
				
				// ------
				
				
				$args = array
				(
					"datasink_realm" => $datasink_realm,
					"datasink_url" => $datasink_url,
					"datasink_time" => "TIMESTAMP:{$datasink_time}",
					"datasink_return" => "RAW"
				);
				$raw_data = brk_datasink_geturldata($args);
				$length = strlen($raw_data);
				$escaped_raw_data = htmlentities($raw_data, ENT_QUOTES, "UTF-8");
				
				echo "<div id='rawview' style='width: 100%; display: none'>";
				echo "<textarea style='width: 80%; height: 50vh;'>{$escaped_raw_data}</textarea>";
				echo "</div>";
			}
			else
			{
				echo "timestamp does not exist?";
				die();
			}
		}
	}
	
	
	echo "so far :)";
	die();
}