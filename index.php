
<!DOCTYPE html>
<html>
<head>
	<title>Search Engine</title>
	<meta charset="utf8">
</head>
<body>
	<style type="text/css">
		.form-style{	
			overflow: hidden;
		}
		.form{		
			float:left;
			text-align: center;
			display: block;
			margin: 0 35%;
		}
		.text-field{
			width:300px;
		}

	</style>
	<div class="form-style">
		<div class="form">
			<form method="POST" target="form.php">
				<p><input type="form" name="field" class="text-field"></p>
				<p><input type="submit" name="submit" value="Search"></p>
		 <?php  /*
			if (isset($_GET['search'])) {
				

			  for ($i = 0; $i < count($arr); $i++){
		
			<p><a href="search/wiki/<?=$arr[$i] ?>"><?=$arr[$i] ?> </a>  </p>			
		


			  
			 }
		   }
			*/
		   ?>


		</form>
	</div>

</div>


</body>
</html>


<?php

if (isset($_POST['submit'])){

	require_once "db-connect.php";
	require_once "stemming_english.php";
	$fieldValue = $_POST['field'];

	$q = "/[^a-z]+/msi";
	$fieldValue = preg_replace($q, " ", $fieldValue);
	$q = '/\s{1,}/';
	$fieldValue = preg_replace($q, " ", $fieldValue);
		//echo $fieldValue."<br>";

	$words = explode(" ", $fieldValue);
	$numOfWords = count($words);
	$count = 0;
	for ($i = 0; $i < $numOfWords; $i++) {
		$res = mysql_query("SELECT * FROM stopword WHERE word='".$words[$i]."'") ;
		if (mysql_num_rows($res) == 0){
			$words[$count] = PorterStemmer::Stem($words[$i]);	
			$count++;
		}
	}
	$numOfWords = $count;
		//echo "Number of all words: ".$numOfWords."<br>";
		//echo "Words left: ".$count;
	$stringWords = "";
	for ($i=0; $i < $numOfWords; $i++) {
		$stringWords.="word='".$words[$i]."' or ";
	}
	$stringWords = rtrim($stringWords,"or ");
	$query = "SELECT DISTINCT url, page_rank FROM term
	JOIN term_page ON term_page.tid=term.id 
	JOIN page ON page.id=term_page.pid WHERE ".$stringWords." ORDER BY page.page_rank DESC";
		//echo $query."<br>";
	$res = mysql_query($query);
	$arr = array();

	while($val = mysql_fetch_array($res))
	{
		$arr[] = $val['url'];
		$arr2[] = $val['page_rank'];


	}

	for ($i=0; $i < count($arr); $i++) { 
		echo "<p><a href='wiki/".$arr[$i]."'>".$arr[$i]." </a>  </p>";
		echo "<p>page_rank: ".$arr2[$i]." </p>";	
	}
		//header("Location: form.php?search=1");
	

}
?>