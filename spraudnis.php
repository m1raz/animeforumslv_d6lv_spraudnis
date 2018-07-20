<?php
	function title($title)
	{
		if(strlen($title) > 40)
		{
			$nt = substr_replace($title, "[...]", 40);
			return $nt;
		}
		else
			return $title;
	}
	header('Content-Type: text/html; charset=utf-8');
	$api_url = "https://animeforums.lv/latest.json?order=\"activity\"";
	$req = file_get_contents($api_url);
	//$req8 = utf8_decode($req);
	$json = json_decode($req);
	$answ[1] = "";
	$answ[2] = "";
	$i = 2;
	foreach($json->{'topic_list'}->{'topics'} as $topic)
	{
		$i === 1 ? $i = 2 : $i = 1;
		$answ[$i] .= "<a href='https://animeforums.lv/t/" . $topic->{'id'} . "'>" . title($topic->{'title'}) . " by " . $topic->{'last_poster_username'} . "</a><br>";
	}
	$html = "<hr><h3>Foruma aktualitātes</h3><table><tr><td>" . $answ[1] . "</td><td>" . $answ[2] . "</td><tr></table><hr>";
	exit(json_encode($html, JSON_UNESCAPED_SLASHES));
?>
