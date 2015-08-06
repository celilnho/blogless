<?php
/*
	blogless - a blogless writing system
	Author:  Martin Doering <martin@datenbrei.de>
	Project: http://blogless.datenbrei.de
	License: http://blogless.datenbrei.de/license.html
*/

	// Set internal character encoding to 'UTF-8' - needed for some functions below
	// Not needed since PHP 5.6 with default_charset = UTF-8

	// locale and our own path
	mb_internal_encoding("UTF-8");

	require_once('config.php');
	
	function mystrftime ($format, $timestamp) {
		$format = str_replace('%S', date('S', $timestamp), $format);   
		return strftime($format, $timestamp);
	}

	function update_sitemap() {
		global $config;
		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
		$files = get_article_list();
		foreach ($files as $name => $mtime) {
			$article = get_article($name);
			$xml .= "<url>\n";
			$xml .= '<loc>' . $config['baseurl'] . $name . "/index.html</loc>\n";
			$xml .= '<xhtml:link rel="alternate" hreflang="' . $article['language'] . '" href="' . $config['baseurl'] . $name . '/index.html" />' . "\n";
			$xml .= '<lastmod>' . $article['created'] . "</lastmod>\n";
			$xml .= "</url>\n";
		}
		$article = get_article('index');
		$xml .= "<url>\n";
		$xml .= '<loc>' . $config['baseurl'] . "index.html</loc>\n";
		$xml .= '<lastmod>' . $article['created'] . "</lastmod>\n";
		$xml .= "</url>\n";
		$xml .= "</urlset>\n";
		file_put_contents('../sitemap.xml',$xml);
	}			

	// Generate RSS 2.0 feed
	function update_rss() {
		global $config;
		
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$xml .= "<rss version=\"2.0\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\">\n";
		$xml .= "<channel>\n";
		$xml .= "<title>" . $_SERVER['HTTP_HOST'] . "</title>\n";
		$xml .= "<link>" . $config['baseurl'] . "</link>\n";
		$xml .= "<description>" . $_SERVER['HTTP_HOST'] . "</description>\n";
		$xml .= "<language>" . $config['language'] . "</language>\n";
		$xml .= "<pubDate>" . htmlspecialchars(date(DATE_RSS), time()) . "</pubDate>\n";
		$xml .= "<generator>blogless</generator>\n";
		$files = get_article_list();
		foreach ($files as $filename) {
			$article = get_article($filename);
			$pagename = $article['title'];
			$content = $article['content'];
			$description = $article['description'];
			$xml .= "<item>\n";
			$xml .= "<title>" . htmlspecialchars($pagename) . "</title>\n";
			$xml .= "<link>" . $config['baseurl'] . urlencode($filename) . "/index.html</link>\n";
			$xml .= "<description>" . htmlspecialchars($description, ENT_COMPAT | ENT_XML1) . "</description>\n";
			$xml .= "<content:encoded><![CDATA[" . $article['content'] . "]]></content:encoded>\n";
			//$xml .= "<guid>" . pageid($pagename) . "</guid>\n";
			$xml .= "<pubDate>" . htmlspecialchars($article['created']) . "</pubDate>\n";
			$xml .= "</item>\n";
		}
		$xml .= "</channel>\n";
		$xml .= "</rss>";
		file_put_contents('../feed.xml',$xml);
	}

	function get_article_list() {
		$dir = new DirectoryIterator('../');
		$files = array();
		foreach ($dir as $file) {
			if ($file->isDir() && !$file->isDot() && $file->getFilename() != 'admin') {
				$files[$file->getMTime()] = $file->getFilename();
			}
		}
		krsort($files);
		return $files;
	}

	function get_file_list($path) {
		$dir = new DirectoryIterator($path);
		$files = array();
		foreach ($dir as $file) {
			if (!$file->isDir()) {
				$files[$file->getMTime()] = $file->getFilename();
			}
		}
		krsort($files);
		return $files;
	}

	// get article attributes
	function get_article($name) {
		global $config;
		
		$article = [];
		if ($name == 'index')
			$path = '../index.html';
		else
			$path = '../' . $name . '/index.html';
		
		if (is_readable($path)) {
			$original = file_get_contents($path);
			
			preg_match('#<html lang="(.*)" .*">#u', $original, $matches); 
			$article['language'] = !empty($matches[1]) ? $matches[1] : '';
			preg_match('#<title>(.*)</title>#u', $original, $matches); 
			$article['title'] = !empty($matches[1]) ? htmlspecialchars_decode($matches[1]) : '';
			preg_match('#<link rel="author" href="(.*)">#u', $original, $matches); 
			$article['profile'] = !empty($matches[1]) ? $matches[1] : '';
			preg_match('#<div .* itemprop="articleBody">(.*)</div>#Ums', $original, $matches); 
			$article['content'] = !empty($matches[1]) ? $matches[1] : '';

			$tags = get_meta_tags($path);
			$article['author'] = !empty($tags['author']) ? htmlspecialchars_decode($tags['author']) : '';
			$article['created'] = !empty($tags['created']) ? $tags['created'] : '';
			$article['description'] = !empty($tags['description']) ? htmlspecialchars_decode($tags['description']) : '';
			$article['keywords'] = !empty($tags['keywords']) ? htmlspecialchars_decode($tags['keywords']) : '';
			$article['twitter'] = !empty($tags['twitter']) ? htmlspecialchars_decode($tags['twitter']) : '';
			$article['gravatar'] = !empty($tags['gravatar']) ? $tags['gravatar'] : '';
		}
		else {
			$article['language'] = '';
			$article['title'] = '';
			$article['content'] = '';
			$article['author'] = '';
			$article['profile'] = '';
			$article['created'] = date('Y-m-d', time());
			$article['description'] = '';
			$article['keywords'] = '';
			$article['twitter'] = '';
			$article['gravatar'] = '';
		}
		
		if(empty($article['language'])) $article['language'] = $config['language'];
		if(empty($article['author'])) $article['author'] = $config['author'];
		if(empty($article['profile'])) $article['profile'] = $config['profile'];
		if(empty($article['twitter'])) $article['twitter'] = $config['twitter'];
		
		return $article;
	}
?>