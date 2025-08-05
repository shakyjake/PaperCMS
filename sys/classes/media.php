<?php

define('__UPLOADS__', config_path('Uploads'));
define('__UPLOADS_PATH__', map_path(__UPLOADS__));

class MediaFile {
	
	private $loaded = false;
	public $id = null;
	public $type = null;
	public $url = null;
	public $file_name = null;
	public $width = null;
	public $height = null;
	public $class_name = null;
	public $attributes = null;
	public $category = null;
	public $versions = null;
	
	function load(){

		$RS = get_records('media_Detail_Public', [
			$this->category,
			$this->file_name
		]);

		if(!$RS->eof){

			$this->loaded = true;

			$this->id = $RS->row['MediaId'];
			$this->type = $RS->row['MediaTypeId'];
			$this->width = $RS->row['Width'];
			$this->height = $RS->row['Height'];
			$this->file_name = $RS->row['FileName'];
			$this->category = new MediaCategory($RS->row['CatId']);
			$this->url = $RS->row['Url'] || ($this->category->folder_url . '/' . $this->file_name);

		}

		$RS = null;

	}
	
	function save(){
		
		$this->regen();

		$RS = get_records('mediaFile_Save', [
			[$this->id, SQLSRV_PARAM_OUT],
		]);
		
		if(!$RS->eof){
			$pages_affected = $RS->collapse('AffectedPages');
		}
		
		$RS = null;
		
	}
	
	function delete(){
		
		$this->regen();

		$RS = get_records('mediaFile_Delete', $this->id);
		
		if(!$RS->eof){
			$pages_affected = $RS->collapse('AffectedPages');
		}
		
		$RS = null;
		
	}
	
	function alt_generate(){
		$alt = pathinfo($this->file_name, PATHINFO_FILENAME);// strips ext
		$alt = regex_replace('/[^\da-z]+/', ' ', $alt);
		$alt = regex_replace('/(\s+)and(\s+)/', ' & ', $alt);
		$alt = regex_replace('/\s{2,}/', ' ', $alt);
		if(strlen($alt) > 2){
			$alt = strtoupper(substr($alt, 0, 1)) . substr($alt, 1, strlen($alt) - 1);
		}
		return $alt;
	}

	function regen(){
		media_regenerate($this->category->id, $this->file_name);
	}
	
	function output(){

		if(!$this->loaded){
			$this->load();
		}

		$out = new RapidString();
		if($this->type === '1'){
			$out->add('<img src="');
			$out->add(html(__UPLOADS__));
			$out->add('/cat-');
			$out->add(html($this->category));
			$out->add('/');
			$out->add(html($this->file_name));
			$out->add('" width="');
			$out->add(html($this->width));
			$out->add('" height="');
			$out->add(html($this->height));
			$out->add('" class="');
			$out->add(html(implode(' ', $this->class_name)));
			$out->add('"');
			foreach($this->attributes as $key => $value){
				$out->add(' ');
				$out->add(html($key));
				if($key !== $value){
					$out->add('="');
					$out->add(html($value));
					$out->add('"');
				}
			}
			$out->add(' />');
		}
	}
	
	function __construct(int $id = 0){
		$this->id = $id;
		$this->class_name = ['img'];
		$this->attributes = [
			'loading' => 'lazy'
		];
		$this->loaded = false;
	}
	
	function __destruct(){
		$this->id = null;
		$this->type = null;
		$this->url = null;
		$this->file_name = null;
		$this->category = null;
		$this->attributes = null;
		$this->width = null;
		$this->height = null;
		$this->class_name = null;
		$this->loaded = null;
	}

}

class MediaCategory {
	
	public $id;
	public $name;
	public $parent_id;
	public $folder_path;
	public $folder_url;
	
	function create($parent_id){
		execute_sql('mediaCat_add', [
			[$this->id, SQLSRV_PARAM_OUT],
			$parent_id,
			$this->name
		]);

		if($this->id > 0){
			return $this->directory_create();
		} else {
			return false;
		}
	}
	
	function save($parent_id){
		return execute_sql('mediaCat_Save', [
			[$this->id, SQLSRV_PARAM_OUT],
			$parent_id,
			$this->name
		]);
	}
	
	function directory_exists(){
		return is_dir($this->folder_path);
	}
	
	function directory_create(){
		if(!$this->directory_exists()){
			return mkdir($this->folder_path);
		}
		return true;
	}
	
	function directory_remove(){
		if($this->directory_exists($this->folder_path)){
			return rmdir($this->folder_path);
		}
		return true;
	}
	
	function __construct(int $id = 0, string $name = null){
		$this->id = $id;
		$this->name = $name;
		$this->folder_url = __UPLOADS__ . '/cat-' . $this->id;
		$this->folder_path = map_path($this->folder_url);
	}
	
	function __destruct(){
		$this->id = null;
		$this->name = null;
	}
	
}

?>