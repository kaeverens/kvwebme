<?php
// image-manipulation.php
// used for dealing with images

class Image{
	public $file;
	public $image;
	public $width;
	public $height;
	public $type;
	private $_caching;
	private $_cache_dir;
	public function __construct($file, $caching=false) {
		$this->file=$file;
		$this->_cache_dir=USERBASE.'ww.cache/webme-images-cache/';
		if ($caching) { // if caching is enabled make sure dirs are present
			if (!is_dir($this->_cache_dir)) {
				mkdir($this->_cache_dir);
			}
			if (strpos($file, 'http://')!==false) {
				$name=md5($file);
				if (!file_exists($this->_cache_dir.$name)) {
					$content=Image::curl($file);
					file_put_contents($this->_cache_dir.$name, $content);
				}
				$this->file=$this->_cache_dir.$name;
			}
		}
		$dimensions=getimagesize($this->file);
		$this->type=$dimensions['mime'];
		$this->width=$dimensions[0];
		$this->height=$dimensions[1];
		$this->_caching=$caching;
	}
	public function resize($w, $h, $crop=false) {
		if ($this->_caching) {
			$name=md5($this->file.'|'.$w.'|'.$h.'|'.$crop);
			if (file_exists($this->_cache_dir.$name)) {
				return $this->file=$this->_cache_dir.$name;
			}
		}
		$width=$this->width;
		$height=$this->height;
		$r=$width/$height;
		if ($crop) {
			if ($width>$height) {
				$width=ceil($width-($width*($r-$w/$h)));
			}
			else {
				$height=ceil($height-($height*($r-$w/$h)));
			}
		}
		else {
			if ($w/$h>$r) {
				$newwidth=$h*$r;
				$newheight=$h;
			}
			else {
				$newheight=$w/$r;
				$newwidth=$w;
			}
		}
		$this->image=Image::render($this->type, $this->file);
		$dst=imagecreatetruecolor($newwidth, $newheight);
		imagecopyresampled(
			$dst,
			$this->image,
			0,
			0,
			0,
			0,
			$newwidth,
			$newheight,
			$width,
			$height
		);
		$this->image=$dst;
		if ($this->_caching) {
			$name=md5($this->file.$w.$h.$crop);
			$this->display($this->_cache_dir.$name);
		}
	}
	public static function render($type, $file) {
		switch ($type) {
			case 'image/jpeg':
				return imagecreatefromjpeg($file);
			break;
			case 'image/png':
				return imagecreatefrompng($file);
			break;
			case 'image/gif':
				return imagecreatefromgif($file);
			break;
		}
		return false;
	}
	public function display($file=false) {
		if ($this->image=='') {
			$this->image=Image::render($this->type, $this->file);
		}
		header('Content-Type: '.$this->type);
		switch ($this->type) {
			case 'image/jpeg':
				imagejpeg($this->image, $file);
			break;	
			case 'image/png':
				imagepng($this->image, $file);
			break;
			case 'image/gif':
				imagegif($this->image, $file);
			break;
		}
	}
	public static function curl($url) {
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response=curl_exec($ch);
		curl_close($ch);
		return $response;
	}
}
