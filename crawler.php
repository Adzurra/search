<?php
	mysql_query("TRUNCATE link");
	mysql_query("TRUNCATE page");
	mysql_query("TRUNCATE term");
	mysql_query("TRUNCATE term_page");
	mysql_query("INSERT INTO page (url) VALUES ('index.html')");
	set_time_limit(0);
	include ('simple_html_dom.php');
	include ('stemming_english.php');
	require "db-connect.php";
		$i = 1;
	error_reporting(0);
	while (1) {
	$val = mysql_query("SELECT url FROM page WHERE id=".$i);
	 
	if(mysql_num_rows($val) == 0)
		{ break;}
	$cur_url = mysql_result($val, 0);
	$html = file_get_html ('hyperlink/'.$cur_url);
	//echo $html;
	if ($html === false) {
		$i++;
		continue;
	}
		$tmp = $html->find('a');
		if(count($tmp) > 0)
		{
		
	$url = array();
	for ($j = 0; $j < count($tmp); $j++) { 
		$url[$j] = $tmp[$j]->href;
		//echo $url[$j]."<br>";
	}
	$arr = "";
	$arr_link = "";
	//$url = array_unique($url);
	for ($j=0; $j < count($url); $j++) {
		$res = mysql_query("SELECT * FROM page WHERE url='".$url[$j]."'");
		
		if (mysql_num_rows($res) == 0 && stristr($arr, $url[$j]) === FALSE){
			$arr.= "('".$url[$j]."',0),";
		}
		if (stristr($arr_link, $url[$j]) === FALSE) {
			
			$arr_link.=" url='".$url[$j]."' OR";	
		}
		
	}
	
	$arr_link = rtrim($arr_link,'OR');
	//echo $arr_link."arr_link <br>";
	
	$arr = rtrim($arr,',');
	mysql_query("INSERT INTO page (url, complete) VALUES ".$arr);
	$res2 = mysql_query("SELECT id FROM page WHERE ".$arr_link);
	
	while ($val2 = mysql_fetch_array($res2)) {

			$arr2[] = $val2['id'];
	 	
	 }
	
	 $arr_link2 = "";
	 for ($j=0; $j < count($arr2); $j++) { 
	 	$arr_link2.="(".$i.", ".$arr2[$j]."), ";
	 }
	 unset($arr2);
	 unset($val2);
	 
	
	//echo "<pre>";
	//var_dump($arr2);
	//echo "</pre>";
	//$arr = substr_replace($arr, "", strlen($arr)-1);
	$arr_link2 = rtrim($arr_link2,", ");
	
	
	mysql_query("INSERT INTO link (pid1, pid2) VALUES ".$arr_link2);
	
}

	 	

$html = file_get_html ('hyperlink/'.$cur_url)->plaintext;
$q = "/[^a-z]+/msi";			
$html = preg_replace($q, " ", $html);

$q = '/\s{1,}/';
$html = preg_replace($q, " ", $html);
$html = ltrim($html, " ");
$html = rtrim($html, " ");
$words = explode(" ", $html);
$terms = "";
$term_page = "";

	for ($j = 0; $j < count($words); $j++) { 
		
		$res = mysql_query("SELECT * FROM stopword WHERE word='".$words[$j]."'");
		if(mysql_num_rows($res) > 0)
		{
			continue;

		}

		$words[$j] = PorterStemmer::Stem($words[$j]);
		$res2 = mysql_query("SELECT * FROM term WHERE word='".$words[$j]."'");
		 
		if ((mysql_num_rows($res2) == 0) && (stristr($terms, "('".$words[$j]."'), ") === FALSE)) {
				$terms.= "('".$words[$j]."'), " ;
			}
		
	}	

	$terms = rtrim($terms,", ");
	
	mysql_query("INSERT INTO term (word) VALUES ".$terms);

	for ($j=0; $j < count($words); $j++) { 

		$res = mysql_query("SELECT * FROM stopword WHERE word='".$words[$j]."'");
		if(mysql_num_rows($res) > 0)
		{
			continue;

		}

		$res1 = mysql_query("SELECT id FROM term WHERE word='".$words[$j]."'");
		$val1 = mysql_result($res1, 0);
		$term_page.= "(".$i.",".$val1."), ";
		
	}

	$term_page = rtrim($term_page,", ");
	
	mysql_query("INSERT INTO term_page (pid, tid) VALUES ".$term_page);
	
	
	mysql_query("UPDATE page SET complete=1 WHERE id=".$i);
	 	$i++;


} 




?>
