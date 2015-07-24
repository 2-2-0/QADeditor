<?php
/*
 * qadeditor.php
 * Quick And Dirty MySQL tables editor
 * by 220 @ WKH
 * 
 * Copyright 2014 deadman <deadman@espartaco>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */

?>
<?php
	$file_loc = "qadeditor.php";

	// parameters
	$hostname = "hostname";
	$username = "username";
	$password = "password";
	
	$database = "database";
	$table = "table";

	// internal stuff	
	$database = $_REQUEST ["db"];
	$table = $_REQUEST ["t"];
	
	$col_names = array ();
	$col_types = array ();
	
	$a = $_REQUEST ["a"];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>QAD editor</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta name="generator" content="Geany 1.23.1" />
	<style>
		body {
			background: white;
			color:black;
			
			font-family: sans-serif;
			font-size: x-small;
		}
		
		td {
			border: thin black solid;
		}
		
		input {
			font-size: x-small;
		}
		
	</style>
</head>

<body>
	<?php
	$mysql = new mysqli ($hostname, $username, convert_uudecode ($password));
	
	if (mysqli_connect_errno()) {
		print ("Connection not available. Check your login data and your server status.<BR />Error: ");
		print (mysqli_connect_errno ());
		print ("<BR />");
		print (mysqli_connect_error ());
		die ();
	}
	if (!$database) {
		print ("Pick a database:<BR />");
		
		$query = "SHOW DATABASES";
		$result = $mysql->query ($query);
		
		// list
		for ($i=0; $i<$result->num_rows; $i++) {
			$rows = $result->fetch_row ();
			$database = $rows [0];
			print ("<A HREF=\"$file_loc?db=$database\">$database</A><BR />\n");
		}
		
	} else {
		
		if (!$mysql->select_db ($database)) {
			print ("Database name invalid!");
			die ();
		} else
		if (!$table) {
			print ("<A HREF=\"$file_loc\">[back]</A><BR />");
			print ("Pick a table:<BR />\n");
			
			$query = "SHOW TABLES";
			$result = $mysql->query ($query);
			
			// list
			for ($i=0; $i<$result->num_rows; $i++) {
				$rows = $result->fetch_row ();
				$table = $rows [0];
				print ("<A HREF=\"$file_loc?db=$database&t=$table\">$table</A><BR />\n");
			}
		} else {
			$query = "SHOW COLUMNS FROM $table";
			$rcol = $mysql->query ($query);
			
			$columns = $rcol->num_rows;
			
			//////////
			
			for ($i=0; $i<$columns; $i++) {
				$col = $rcol->fetch_row ();
				$col_names [$i] = $col [0];
				$col_types [$i] = $col [1];
			}

			switch ($a) {
				case 10:
					// edit
					$id = $_REQUEST ["id"];
					
					//$col = $rcol->fetch_row ();
					//$id_row = $col [0];
					
					print ("<A HREF=\"$file_loc?db=$database&t=$table\">[back]</A><BR />\n");
					print ("EDIT! $id");
					//////////// EDIT
					
					$query = "SELECT * FROM $table WHERE $id_row=$id";
					print ($query);
					
					print ("<TABLE>\n");
					for ($i=0; $i<$columns; $i++) {
						print ("<TR>\n");
						print ("<TD>\n");
						print ($col_names [$i]);
						print ("</TD>\n");
						print ("<TD>\n");
						print ("<INPUT type=\"text\" name=\"\" value=\"\" />\n");
						print ("</TD>\n");
						print ("</TR>\n");
					}
					print ("<TR>\n");
					print ("<TD colspan=\"2\">\n");
					print ("<INPUT type=\"submit\" value=\"save info\" />");
					print ("</TD>\n");
					print ("</TR>\n");
					print ("</TABLE>\n");
					
					exit ();
					break;
				case 20:
					// delete
					$id = $_REQUEST ["id"];
					$column = $_REQUEST ["c"];
					
					print ("Are you sure you want to delete this record? <BR />");
					print ("<A HREF=\"$file_loc?a=22&db=$database&t=$table&id=$id&c=$column\">[YES]</A>");
					print ("&nbsp;&nbsp;&nbsp;");
					print ("<A HREF=\"$file_loc?db=$database&t=$table\">[NO]</A><BR />");
					
					break;
				case 22:
					// delete
					$id = $_REQUEST ["id"];
					$column = $_REQUEST ["c"];
					
					$query = "DELETE FROM $table WHERE $column=$id";
					print ($query);
					$mysql->query ($query);
					
					$s = "location: $file_loc?db=$database&t=$table";
					//print ($s);
					header ($s);
					
					break;
				case 30:
					// add					
					$query = "INSERT INTO $table (";
					for ($i=0; $i<$columns; $i++) {
						$query.= $col_names [$i];
						if ($i<$columns-1) $query.= ", ";
					}
					$query.= ") VALUES (";
					for ($i=0; $i<$columns; $i++) {
						//$query.= "_";
						
						$token = $_REQUEST [$col_names [$i]];
						
						$p = false;
						$t = $col_types [$i];
						$t = strtolower ($t);

						if (stristr ($t, "text") || stristr ($t, "char") || stristr ($t, "time") || stristr ($t, "date")) {

							$p = true;
						}

						if ($p) $query.= "'";
						else 
						if (!$token) $token = 0;
						$query.= $token;
						if ($p) $query.= "'";

						if ($i<$columns-1) $query.= ", ";
					}
					$query.= ")";
					
					$mysql->query ($query);
					$s = "location: $file_loc?db=$database&t=$table";
					header ($s);
					break;
				default:
					///////////// LIST
					print ("<A HREF=\"$file_loc?db=$database\">[back]</A><BR />");
					
					print ("<FORM method=\"POST\" action=\"$file_loc?a=30&db=$database&t=$table\">\n");
					print ("<TABLE>\n");
					print ("<TR>\n");
					for ($i=0; $i<$columns; $i++) {
						print ("<TH>".$col_names [$i]."</TH>\n");
					}
					print ("</TR>\n");
					
					// list
					$query = "SELECT * FROM $table";
					$result = $mysql->query ($query);
					
					$column = $col_names [0];
					
					for ($i=0; $i<$result->num_rows; $i++) {
						$rows = $result->fetch_row ();
						$id = $rows [0];

						print ("<TR>\n");
						for ($j=0; $j<$columns; $j++) {
							print ("<TD>".$rows [$j]."</TD>\n");	
						}
						print ("<TD>");
						print ("<A HREF=\"$file_loc?a=10&db=$database&t=$table&id=$id&c=$column\">[EDIT]</A> ");
						print ("</TD>\n");
						
						print ("<TD>");
						print ("<A HREF=\"$file_loc?a=20&db=$database&t=$table&id=$id&c=$column\">[X]</A> ");
						print ("</TD>\n");
						
						print ("</TR>\n");
					}
					
					print ("<TR>\n");
					for ($i=0; $i<$columns; $i++) {
						print ("<TD>");
						print ("<INPUT type=\"text\" name=\"".$col_names [$i]."\" size=\"7\" />");
						print ("</TD>\n");	
					}
					print ("<TD colspan=\"2\">");
					print ("<INPUT type=\"submit\" value=\"add new\"/>");
					print ("</TD>\n");
					print ("</TR>\n");

					
					print ("</TABLE>\n");
					print ("</FORM>\n");
					////////////// end: LIST
					break;
			} // end switch

		} // end if
	}
	mysql_close ();
	?>
</body>
</html>
