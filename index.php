<?php
/*
Plugin Name: WP Gif Resizer
Plugin URI: http://wordpress.org/plugins/wp-gif-resizer/
Description: Help WordPress to generate animated gif thumbnails
Version: 0.0.1
Author: bmosnier
License: GPLv3
*/

class wpGifResizer{

	private $dir;
	private $sizes;
	private $namespace = 'wp-gif-resizer';

	function __construct(){

		register_activation_hook( __FILE__, [$this, 'activated']);
		register_deactivation_hook(__FILE__, [$this, 'desactivated']);
		register_uninstall_hook(__FILE__, [$this, 'desactivated']);

		$enable = get_option($this->namespace.'-enabled', false);
		if($enable){
			add_filter('wp_generate_attachment_metadata', [$this, 'media'], 10, 2);
		}
	}

	function activated(){
		$allowed = $this->requirement();
		update_option($this->namespace.'-enabled', $allowed, false);
	}

	function desactivated(){
		delete_option($this->namespace.'-enabled');
	}

	private function requirement(){
		$res = true;

		try{
			$run = $this->exec('convert -v');
			if(!empty($run['stderr'])) $res = false;
		} catch (Exception $e){
			$res = false;
		}

		return $res;
	}

	private function allSizes(){

		global $_wp_additional_image_sizes;
		$sizes = [];

		foreach(get_intermediate_image_sizes() as $_size){

			if(in_array($_size, ['thumbnail', 'medium', 'medium_large', 'large'])){
				$sizes[$_size]['width']  = get_option("{$_size}_size_w");
				$sizes[$_size]['height'] = get_option("{$_size}_size_h");
				$sizes[$_size]['crop']   = (bool)get_option("{$_size}_crop");
			}else
			if(isset($_wp_additional_image_sizes[$_size])){
				$sizes[$_size] = [
					'width'  => $_wp_additional_image_sizes[$_size]['width'],
					'height' => $_wp_additional_image_sizes[$_size]['height'],
					'crop'   => $_wp_additional_image_sizes[$_size]['crop'],
				];
			}
		}

		return $sizes;
	}

	/**
	 * From an Attachment ID, generate all "new" thumbnails
	 *
	 * @param $meta
	 * @param $id
	 *
	 * @return $meta
	 */
	public function media($meta, $id){

		$this->dir = wp_upload_dir();
		$this->sizes = $this->allSizes();

		$src = $this->dir['basedir'].'/'.$meta['file'];
		if(empty($src)) return $meta;

		$ext = pathinfo($src, PATHINFO_EXTENSION);
		if(empty($ext) OR strtolower($ext) !== 'gif') return $meta;

		foreach($meta['sizes'] as $size => $params){
			$url = dirname($src).'/'.$params['file'];
		#	$img = wp_get_attachment_image_src($id, $size, false);
		#	$url = $this->getFullPath($img[0]);

		#	echo $src."\n".$url."\n\n";
		#	if($url != $src)
			$this->convert($src, $url, $size);

		}

		return $meta;
	}

	// width x height
	private function convert($src, $dst, $size){

		$opt = $this->sizes[$size];
		$ext = pathinfo(basename($dst), PATHINFO_EXTENSION);
		$name = substr(basename($dst), 0, (strlen($ext)+1)*-1);
		$dst = dirname($dst).'/'.$name.'.'.$ext;

		$resize = 'x'.$opt['height'];
		if(!$opt['height']) $resize = $opt['width'].'x';

		if($opt['crop']){
			$crop = $opt['width'].'x'.$opt['height'];
			$resize = $crop.'^ -gravity center -extent '.$crop;
		}

		$cmd = 'convert '.escapeshellarg($src).' -resize '.$resize.' '.escapeshellarg($dst);

		$this->exec($cmd);
	}

	private function exec($cmd, $input=''){

		$proc = proc_open($cmd, array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w')),
			$pipes
		);

		fwrite($pipes[0], $input);

		fclose($pipes[0]);
		$stdout = stream_get_contents($pipes[1]);
		fclose($pipes[1]);

		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[2]);

		$rtn = proc_close($proc);

		return [
			'stdout' => $stdout,
			'stderr' => $stderr,
			'return' => $rtn
		];
	}

}

new wpGifResizer();
