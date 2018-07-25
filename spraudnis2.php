<?php
	$posts = 5;
	function linkify($value, $protocols = array('http', 'https', 'mail'), array $attributes = array())
    {
        $attr = '';
        foreach ($attributes as $key => $val)
		{
            $attr = ' ' . $key . '="' . htmlentities($val) . '"';
        }
        $links = array();
        $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) { return '<' . array_push($links, $match[1]) . '>'; }, $value);
        foreach ((array)$protocols as $protocol) 
		{
            switch ($protocol) 
			{
                case 'http':
                case 'https':   $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { if ($match[1]) $protocol = $match[1]; $link = $match[2] ?: $match[3]; return '<' . array_push($links, "<a $attr href=\"$protocol://$link\" target=\"_blank\">$link</a>") . '>'; }, $value); break;
                case 'mail':    $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
                case 'twitter': $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . "\">{$match[0]}</a>") . '>'; }, $value); break;
                default:        $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
            }
        }
        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) { return $links[$match[1] - 1]; }, $value);
    }
	function images($value)
	{
		if($r = preg_replace('/(https?:\/\/[^ ]+?(?:\.jpg|\.png|\.gif))/', '<br><img src="$1" alt="$1" />', $value))
		{
			return $r;
		}
		else
		{
			return false;
		}
	}
	
	header('Content-Type: text/html; charset=utf-8');
	$api_url = "https://animeforums.lv/posts.json";
	$req = file_get_contents($api_url);
	$json = json_decode($req);
	$answ = "";
	$i = 2;
	for($i = 0; $i < $posts; $i++)
	{
		if($json->{'latest_posts'}[$i]->{'raw'} !== "")
		{
			$temp_api = "https://animeforums.lv/users/" . $json->{'latest_posts'}[$i]->{'username'} . ".json";
			$temp_req = file_get_contents($temp_api);
			$temp_json = json_decode($temp_req);
			preg_match('~(?:https?://)?(?:www.)?(?:youtube.com|youtu.be)/(?:watch\?v=)?([^\s]+)~', $json->{'latest_posts'}[$i]->{'raw'}, $ytmatch);
			$answ .= 
				"
				<div class=\"media\">
					<div class=\"media-left\">
						<img src=\"https://animeforums.lv/user_avatar/animeforums.lv/".$json->{'latest_posts'}[$i]->{'username'}."/120/".$temp_json->{'user'}->{'uploaded_avatar_id'}."_1.png\"  class=\"rounded-circle\" style=\"width:60px\">
					</div>
					<div class=\"media-body\">
						<h4 class\"media-heading\">
						<a href='https://animeforums.lv/u/".$json->{'latest_posts'}[$i]->{'username'}."'>
						".
						
						$json->{'latest_posts'}[$i]->{'username'}
						
						."</a> @ 
							<a href='https://animeforums.lv/t/".$json->{'latest_posts'}[$i]->{'topic_id'}."/".$json->{'latest_posts'}[$i]->{'post_number'}."'>
						". 
						
						$json->{'latest_posts'}[$i]->{'topic_title'}
						
						."</a></h4>
						<p>".linkify(images($json->{'latest_posts'}[$i]->{'raw'}));
						if(sizeof($ytmatch) != 0)
						{
							$answ .= "<br><iframe id=\"ytiframe\" width=\"267\" height=\"150\" src=\"https://www.youtube.com/embed/".$ytmatch[1]."\" frameborder=\"0\" allow=\"autoplay; encrypted-media\" allowfullscreen onclick=\"pauseRequestsForTenMinutes();\"></iframe>";
						}
			$answ .= "
						</p>
					</div>
				</div>
				";
		}else{
			$posts++;
		}
	}
	$html = $answ;
	exit(json_encode($html, JSON_UNESCAPED_SLASHES));
?>
