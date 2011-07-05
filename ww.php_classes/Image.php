<?php
// image-manipulation.php
// used for dealing with images

class Image{
	public $file;
	public $image;
	public $width;
	public $height;
	public $type;
	private $caching;
	private $cache_dir;
	public function __construct($file,$caching=false){
		if(!file_exists($file))
			return false;
		$dimensions=getimagesize($file);
		$this->cache_dir=USERBASE.'ww.cache/webme-images-cache/';
		$this->type=$dimensions['mime'];
		$this->file=$file;
		$this->width=$dimensions[0];
		$this->height=$dimensions[1];
		$this->caching=$caching;
		if($caching){ // if caching is enabled make sure dirs are present
			if(!is_dir($this->cache_dir)){
				mkdir($this->cache_dir);
			}
		}
	}
	public function resize($w,$h,$crop=false){
		if($this->caching){
			$name=md5($this->file.$w.$h.$crop);
			if(file_exists($this->cache_dir.$name))
				return $this->file=$this->cache_dir.$name;
		}
		$width=$this->width;
		$height=$this->height;
		$r=$width/$height;
		if($crop){
			if($width>$height){
				$width=ceil($width-($width*($r-$w/$h)));
			}
			else{
				$height=ceil($height-($height*($r-$w/$h)));
			}
		}
		else{
			if($w/$h>$r){
				$newwidth=$h*$r;
				$newheight=$h;
			}
			else{
				$newheight=$w/$r;
				$newwidth=$w;
			}
		}
		$this->render();
		$dst=imagecreatetruecolor($newwidth,$newheight);
		imagecopyresampled($dst,$this->image,0,0,0,0,$newwidth,$newheight,$width,$height);
		$this->image=$dst;
		if($this->caching){
			$name=md5($this->file.$w.$h.$crop);
			$this->display($this->cache_dir.$name);
		}
	}
	private function render(){
		switch($this->type){
			case 'image/jpeg':
				$this->image=imagecreatefromjpeg($this->file);
			break;
			case 'image/png':
				$this->image=imagecreatefrompng($this->file);
			break;
			case 'image/gif':
				$this->image=imagecreatefromgif($this->file);
			break;
		}
	}
	public function display($file=false){
		if($this->image=='')
			$this->render();
		header('Content-Type: '.$this->type);
		switch($this->type){
			case 'image/jpeg':
				imagejpeg($this->image,$file);
			break;	
			case 'image/png':
				imagepng($this->image,$file);
			break;
			case 'image/gif':
				imagegif($this->image,$file);
			break;
		}
	}
}
?>
