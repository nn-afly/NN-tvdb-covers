<?php
define('FS_ROOT', realpath(dirname(__FILE__)));
require_once (FS_ROOT . "/../../www/config.php");
require_once (FS_ROOT . "/../../www/lib/framework/db.php");
require_once (FS_ROOT . "/../../www/lib/thetvdb.php");
require_once (FS_ROOT . "/../../www/lib/util.php");
include("SimpleImage.php");
    
    $key = '5F84ECB91B42D719';
	$nnpath = '/var/www/newznab/www';
	
	function getTVDb()
	{			
		$db = new DB();
		return $db->query("select * from thetvdb where artworkprocessed = 0  LIMIT 100");		
	}
	
	function setTVDb($id, $poster)
	{			
		$db = new DB();
		return $db->query(sprintf("UPDATE thetvdb SET artworkprocessed = 1, poster = %d WHERE tvdbid = %d",$poster, $id));		
	}
	
	function TheTVDBAPI($seriesid, $key)
	{
		$apiresponse = getUrl('http://www.thetvdb.com/api/'.$key.'/series/'.$seriesid.'/all/en.xml'); //.zip?
		$TheTVDBAPIXML = @simplexml_load_string($apiresponse);

		
		if (!$TheTVDBAPIXML)
		{	
			return false;
		}
		
		if (!$TheTVDBAPIXML->Series)
		{	
			return false;
		}
		
		if (!$TheTVDBAPIXML->Series->poster)
		{	
			return false;
		}		
		
		if ($TheTVDBAPIXML->Series->poster == "")
		{
			return false;
		}
		else
		{
			return $TheTVDBAPIXML->Series->poster;
		}

	}
	
	
	if(!is_dir('/var/www/newznab/www/covers/tv/')){			
		mkdir('/var/www/newznab/www/covers/tv/', 0777, true);
	}
	
	if(!is_dir('/var/www/newznab/www/covers/tv/posters')){
		mkdir('/var/www/newznab/www/covers/tv//posters', 0777, true);
	}
	
	
	$results = getTVDb();
	
	foreach($results as $result) 
	{
			
			
		$sid= $result['tvdbID'];
		
		$res = TheTVDBAPI($sid, $key);
		echo ".";
		if ($res === false)
		{
			setTVDb($sid, 0);
		}else
		{
		
			file_put_contents($nnpath."/covers/tv//posters/".$sid.".jpg",
				file_get_contents("http://www.thetvdb.com/banners/".$res));
											
				$image = new SimpleImage();
				$image->load($nnpath."/covers/tv/posters/".$sid.".jpg");
				$image->resize(276,406);
				$image->save($nnpath."/covers/tv/posters/".$sid.".jpg");
				usleep(400000);
		
				setTVDb($result['tvdbID'], 1);
		}
	
		usleep(400000);
	
	}
?>