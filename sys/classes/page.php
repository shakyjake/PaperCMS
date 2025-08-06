<?php
class Page {
	
	public $id = null;
	public $parent_id = null;
	public $type = null;
	public $name = null;
	public $slug = null;
	public $url = null;
	public $template_id = null;
	public $css = [];
	public $js = [];
	public $body = [];
	public $data = [];
	public $data_loaded = false;
	public $meta = [];
	public $meta_loaded = false;
	
	function data_load(){

		$params = [
			$this->id
		];
		
		$RS = get_records('pageData_List', $params);
		if(!$RS->eof){
			$this->data_loaded = true;
			while(!$RS->eof){
				$this->data[$RS->row['DataKey']] = new PageData($this->id, $RS->row['PageDataId'], $RS->row['DataKey'], $RS->row['DataValue']);
				$RS->move_next();
			}
		}
		$RS = null;
	}

	function data_get(string $key, bool $value_only = true){
		if(empty($this->data[$key])){
			if(!$this->data_loaded){
				$this->data_load();
			}
			if(empty($this->data[$key])){
				return null;
			}
		}
		if($value_only){
			return $this->data[$key]->value;
		}
		return $this->data[$key];
	}

	function data_set(string $key, mixed $value){
		if(empty($this->data[$key])){
			$this->data[$key] = new PageData($this->id, null, $key, $value);
		} else {
			$this->data[$key]->value = $value;
		}
		$this->data[$key]->save();
		$this->json_save();
	}

	function data_parse(string $html = ''){

		if(str_contains($html, TAGSTART . 'DATA(')){

			$matches = regex_match(regex_escape(TAGSTART) . 'DATA\([\'"]?([A-Za-z0-9\.\/]*)[\'"]?\)' . regex_escape(TAGEND), $html);

			foreach($matches as $match){
				$html = str_replace($match['value'], $this->data_get($match[1], true), $html);
			}

		}

		return $html;
		
	}

	function json_load(){

		$file_path = map_path(sprintf(
			'/media/json/page/page-%1$s.json',
			$this->id
		));

		$json = json_decode(file_get_contents($file_path), true);

		$this->parent_id = $json['parent_id'];
		$this->type = $json['type'];
		$this->name = $json['name'];
		$this->slug = $json['slug'];
		$this->url = $json['url'];
		$this->template_id = $json['template_id'];

		foreach($json['css'] as $css){
			$url = $css['url'];
			$media = empty($css['media']) ? 'all' : $css['media'];
			$inline = isset($css['inline']) ? $css['inline'] : false;
			$priority = empty($css['priority']) ? 100 : $css['priority'];
			$hash = empty($css['hash']) ? '' : $css['hash'];
			$css_single = new CSS($url, $media, $inline, $priority, $hash);
			$css_single->inline = $inline;
			if(!empty($hash)){
				$css_single->file_hash = $hash;
			}
			$css_single->enqueue($this);
		}
		foreach($json['js'] as $js){
			$url = $js['url'];
			$inline = isset($js['inline']) ? $js['inline'] : false;
			$priority = empty($js['priority']) ? 100 : $js['priority'];
			$hash = empty($js['hash']) ? '' : $js['hash'];
			$js_single = new JS($url, $inline, $priority);
			$js_single->inline = $inline;
			if(!empty($hash)){
				$js_single->file_hash = $hash;
			}
			$js_single->enqueue($this);
		}

		if(!empty($json['body'])){
			$this->body = $json['body'];
		}

		foreach($json['data'] as $key => $value){
			$this->data[$key] = $value;
		}

		foreach($json['meta'] as $meta){
			$this->meta[$meta['id']] = [
				'tag_name' =>empty($meta['tag_name']) ? '' : $meta['tag_name'],
				'attributes' => [],
				'value' => empty($meta['content']) ? '' : $meta['content'],
				'default_value' => empty($meta['default_value']) ? '' : $meta['default_value']
			];
		}

	}

	function json_save(){

		$json = [
			'id' => $this->id,
			'slug' => $this->slug,
			'url' => $this->url,
			'name' => $this->name,
			'template_id' => $this->template_id,
			'parent_id' => $this->parent_id,
			'type' => $this->type,
			'meta' => [],
			'css' => [],
			'js' => [],
			'data' => [],
			'body' => []
		];

		if(!$this->meta_loaded){
			$this->meta_load();
		}

		foreach($this->meta as $id => $meta_element){
			$json['meta'][] = [
				'id' => $id,
				'tag_name' => $meta_element['tag_name'],
				'attributes' => $meta_element['attributes'],
				'value' => $meta_element['value'],
				'default_value' => $meta_element['default_value']
			];
		}

		if(!$this->data_loaded){
			$this->data_load();
		}

		foreach($this->data as $data_element){
			$json['data'][$data_element->name] = $data_element->value;
		}

		$file_path = map_path(sprintf(
			'/media/json/page/page-%1$s.json',
			$this->id
		));

		file_put_contents($file_path, json_encode($json, JSON_PRETTY_PRINT));

	}

	function save(){
		$params = [
			[&$this->id, SQLSRV_PARAM_OUT],
			$this->parent_id,
			$this->type,
			$this->name,
			$this->slug,
			$this->template_id
		];
		execute_sql('page_Save', $params);
		$this->json_save();
	}

	function delete(?int $redirect_to = null){

		global $current_user;

		$params = [
			[&$this->id, SQLSRV_PARAM_OUT],
			$current_user->id,
			$redirect_to
		];

		execute_sql('page_Delete', $params);

		if($this->id === 0){
			throw new Exception('User with insufficient permissions attempted to delete page.');
		}

	}
	
	function meta_html(){
		if(!$this->meta_loaded){
			$this->meta_load();
		}
		$out = [];
		foreach($this->meta as $meta_element){
			$out[] = '<';
			$out[] = html($meta_element['tag_name']);
			if(count($meta_element['attributes'])){
				foreach($meta_element['attributes'] as $attribute){
					$out[] = ' ';
					$out[] = html($attribute->attribute_name);
					$out[] = '="';
					if(empty($attribute->value)){
						$out[] = html($attribute->default_value);
					} else {
						$out[] = html($attribute->value);
					}
					$out[] = '"';
				}
				$out[] = ' />';
			} else {
				$out[] = '>';
				if(empty($meta_element['value'])){
					$out[] = html($meta_element['default_value']);
				} else {
					$out[] = html($meta_element['value']);
				}
				$out[] = '</';
				$out[] = $meta_element['tag_name'];
				$out[] = '>';
			}
		}
		return implode('', $out);
	}
	
	function meta_load(){

		$params = [
			$this->id
		];
		$RS = get_records('pageMeta_List', $params);

		if(!$RS->eof){
			$this->meta_loaded = true;
			while(!$RS->eof){
				if(!isset($this->meta[$RS->row['MetaTagId']])){
					$this->meta[$RS->row['MetaTagId']] = [
						'tag_name' => $RS->row['MetaElement'],
						'attributes' => [],
						'value' => '',
						'default_value' => ''
					];
				}
				if(empty($RS->row['MetaAttributeId'])){
					$this->meta[$RS->row['MetaTagId']]['value'] = $RS->row['MetaContent'];
					$this->meta[$RS->row['MetaTagId']]['default_value'] = $RS->row['DefaultContent'];
				} else {
					$this->meta[$RS->row['MetaTagId']]['attributes'][$RS->row['MetaAttributeName']] = new PageMeta($RS->row['PageMetaId'], $this->id, $RS->row['MetaTagId'], $RS->row['MetaAttributeId'], $RS->row['MetaAttributeName'], $RS->row['MetaContent'], $RS->row['DefaultContent']);
				}
				$RS->move_next();
			}
		}
		$RS = null;
	}
	
	function load_from_url(bool $load_data = false){

		global $current_user;

		$url = '';
		if(!empty($_REQUEST['u'])){
			$url = sanitise_url($_REQUEST['u']);
		}

		if(empty($url)){

			$this->id = 1;
			$this->load($load_data);

		} else {
			$params = [
				$url,
				$current_user->id
			];
			$RS = get_records('page_Detail_Public', $params);
			if($RS->eof){
				http_response_code(404);
				if(accept_html() && option('404_page_id')){
					$this->id = option('404_page_id');
					$this->load();
					$this->output();
				}
				die();
			} else {
				$this->id = $RS->row['PageId'];
				$this->json_load();
			}
			$RS = null;
		}
	}
	
	function load(bool $load_data = false){

		global $current_user;

		$params = [
			$this->id,
			$current_user->id
		];
		$RS = get_records('page_Detail', $params);

		if(!$RS->eof){
			$this->load_from_rs($RS);
			if($load_data){
				$this->data_load();
			}
		}
		$RS = null;
	}
	
	function load_from_rs(RecordSet $RS){
		if(!$RS->eof){
			$this->id = $RS->row['PageId'];
			$this->parent_id = $RS->row['ParentId'];
			$this->type = $RS->row['TypeName'];
			$this->name = $RS->row['Name'];
			$this->template_id = $RS->row['TemplateId'];
			$this->url = $RS->row['Url'];
		}

	}

	function template_get(){
		if(empty($this->template_id)){
			$this->template_id = 1;
		}
		$file_path = map_path(sprintf(
			'/media/json/template/template-%1$s.json',
			$this->template_id
		));
		if(file_exists($file_path)){
			return json_decode(file_get_contents($file_path), true);
		}
		return [];
	}

	function header_get(){
		$file_path = map_path(sprintf('/media/json/header-%1$s.json', $this->template_id));
		if(file_exists($file_path)){
			return json_decode(file_get_contents($file_path), true);
		}
		$file_path = map_path('/media/json/header.json');
		if(file_exists($file_path)){
			return json_decode(file_get_contents($file_path), true);
		}
		return [];
	}

	function footer_get(){
		$file_path = map_path(sprintf('/media/json/footer-%1$s.json', $this->template_id));
		if(file_exists($file_path)){
			return json_decode(file_get_contents($file_path), true);
		}
		$file_path = map_path('/media/json/footer.json');
		if(file_exists($file_path)){
			return json_decode(file_get_contents($file_path), true);
		}
		return [];
	}

	function parse(?string $html = ''){

		if(empty($html)){
			return '';
		}

		$html = tag_parse($html, 'PAGE.META', $this->meta_html());
		$html = tag_parse($html, 'PAGE.NAME', $this->name);
		$html = tag_parse($html, 'PAGE.URL', sprintf(
			'https://%1$s%2$s',
			$_SERVER['HTTP_HOST'],
			$this->url
		));
		$html = tag_parse($html, 'PAGE.CSS', css_html());
		$html = tag_parse($html, 'PAGE.JS', js_html());
		$html = template_parse_user($html);
		$html = template_parse_datetime($html);
		$html = template_parse_config($html);

		return $html;

	}

	function output(){

		$html = template_get('root');

		$html = tag_parse($html, 'HEAD', partial_get('head'));
		$html = tag_parse($html, 'HEADER', components_render($this->header_get()));
		$html = tag_parse($html, 'FOOTER', components_render($this->footer_get()));
		
		if(empty($this->template_id)){
			$html = tag_parse($html, 'MAIN', components_render($this->body));
		} else {
			$template = $this->template_get();
			foreach($template['css'] as $css){
				$url = $css['url'];
				$media = empty($css['media']) ? 'all' : $css['media'];
				$inline = isset($css['inline']) ? $css['inline'] : false;
				$priority = empty($css['priority']) ? 100 : $css['priority'];
				$hash = empty($css['hash']) ? '' : $css['hash'];
				$css_single = new CSS($url, $media, $inline, $priority, $hash);
				$css_single->inline = $inline;
				if(!empty($hash)){
					$css_single->file_hash = $hash;
				}
				$css_single->enqueue();
			}
			foreach($template['js'] as $js){
				$url = $js['url'];
				$inline = isset($js['inline']) ? $js['inline'] : false;
				$priority = empty($js['priority']) ? 100 : $js['priority'];
				$hash = empty($js['hash']) ? '' : $js['hash'];
				$js_single = new JS($url, $inline, $priority);
				$js_single->inline = $inline;
				if(!empty($hash)){
					$js_single->file_hash = $hash;
				}
				$js_single->enqueue();
			}
			$html = tag_parse($html, 'MAIN', components_render($template['content']));
			if(tag_exists($html, 'PAGE.CONTENT')){
				$html = tag_parse($html, 'PAGE.CONTENT', components_render($this->body));
			}
		}

		$html = $this->parse($html);

		$html = template_parse_assoc($html, 'DATA', $this->data);

		//$this->json_save();

		echo $html;

	}
	
	function __construct(?int $id = null, ?int $parent_id = null, ?string $type = null, ?string $name = null, ?string $slug = null, ?int $template_id = null){
		$this->id = $id;
		$this->parent_id = $parent_id;
		$this->type = $type;
		$this->name = $name;
		$this->slug = $slug;
		$this->template_id = $template_id;
		$this->data = [];
		$this->data_loaded = false;
		$this->css = [];
		$this->js = [];
	}
	
	function __destruct(){
		$this->id = null;
		$this->parent_id = null;
		$this->type = null;
		$this->name = null;
		$this->slug = null;
		$this->template_id = null;
		$this->data = null;
		$this->data_loaded = false;
		$this->css = null;
		$this->js = null;
	}
	
}

class PageData {
	
	public $id = null;
	public $page_id = null;
	public $name = null;
	public $value = null;
	
	function save(){
		$params = [
			[$this->id, SQLSRV_PARAM_OUT],
			$this->page_id,
			$this->name,
			$this->value
		];
		execute_sql('pageData_Save', $params);
	}
	
	function load(){
		$RS = get_records('pageData_Detail', [
			$this->id,
			$this->page_id,
			$this->name
		]);
		if(!$RS->eof){
			$this->page_id = $RS->row['PageId'];
			$this->name = $RS->row['DataKey'];
			$this->value = $RS->row['DataValue'];
		}
	}

	function delete(){
		$params = [
			$this->id
		];
		execute_sql('pageData_Delete', $params);
	}
	
	function __construct(int $page_id, ?int $page_data_id = null, ?string $name = null, mixed $value = null){
		if(empty($page_id)){
			throw new \Exception('Page Data must have an associated Page.');
		}
		$this->id = $page_data_id;
		$this->page_id = $page_id;
		$this->name = $name;
		$this->value = $value;
	}
	
	function __destruct(){
		$this->id = null;
		$this->page_id = null;
		$this->name = null;
		$this->value = null;
	}

	function __toString(){
		return $this->value;
	}

}

class PageMeta {
	
	public $id = null;
	public $page_id = null;
	public $meta_id = null;
	public $attribute_id = null;
	public $attribute_name = null;
	public $value = null;
	public $default_value = null;
	
	function value(){
		if(!is_null($this->value)){
			return $this->value;
		}
		if(!is_null($this->default_value)){
			return $this->default_value;
		}
		return '';
	}
	
	function save(){

		global $current_user;

		$params = [
			[$this->id, SQLSRV_PARAM_OUT],
			$this->page_id,
			$this->meta_id,
			$this->attribute_id,
			$this->value,
			$current_user->id
		];

		execute_sql('pageMeta_Save', $params);

		if($this->id === 0){
			throw new \Exception('User with insufficient permissions attempted to save page meta.');
		}

	}
	
	function __construct(?int $id = null, ?int $page_id = null, ?int $meta_id = null, ?int $attribute_id = null, ?string $attribute_name = null, ?string $value = '', ?string $default_value = ''){
		$this->id = $id;
		$this->page_id = $page_id;
		$this->meta_id = $meta_id;
		$this->attribute_id = $attribute_id;
		$this->attribute_name = $attribute_name;
		$this->value = $value;
		$this->default_value = $default_value;
	}
	
	function __destruct(){
		$this->id = null;
		$this->page_id = null;
		$this->meta_id = null;
		$this->attribute_id = null;
		$this->attribute_name = null;
		$this->value = null;
		$this->default_value = null;
	}

}

function page_parse(?string $template = '', ?RecordSet $RS = null){

	if(empty($template)){
		return '';
	}

	$template = tag_parse($template, 'PAGE.ID', $RS->row['PageId']);
	$template = tag_parse($template, 'PAGE.PARENTID', $RS->row['ParentId']);
	$template = tag_parse($template, 'PAGE.NAME', $RS->row['Name']);
	$template = tag_parse($template, 'PAGE.SLUG', $RS->row['Slug']);
	$template = tag_parse($template, 'PAGE.URL', $RS->row['Url']);

	$template = template_parse_config($template);

	return $template;

}

function page_save(){

	global $current_user;

	$response = new AJAXResponse();

	$id = do_form('id');
	$parent_id = do_form('parent_id');
	$type = do_form('type');
	$name = do_form('name');
	$slug = do_form('slug');
	$template_id = do_form('template_id');

	$params = [
		$id,
		$parent_id,
		$type,
		$name,
		$slug,
		$template_id,
		$current_user->id
	];

	$RS = get_records('page_Save', $params);
	
	$response->rs_add($RS, 403);

	$RS = null;

	$response->output();

	die();

}
ajax_add('page', 'save');

function page_remove(){

	global $current_user;

	$response = new AJAXResponse();

	$id = do_form('id');

	$params = [
		$id,
		$current_user->id
	];

	$RS = get_records('page_Remove', $params);
	
	$response->rs_add($RS, 403);

	$RS = null;

	$response->output();

	die();

}
ajax_add('page', 'remove');

function page_list(){

	global $current_user;

	$response = new AJAXResponse();

	$id = do_form('id', '0');

	$params = [
		$id,
		$current_user->id
	];

	$RS = get_records('page_List', $params);
	
	$response->rs_add($RS, 403);

	$RS = null;

	$response->output();

	die();

}
ajax_add('page', 'list');

function page_type_list(){

	$response = new AJAXResponse();

	$RS = get_records('pageType_List');
	
	if($RS->eof){

		$response->status_code = 404;

	} else {

		foreach($RS->rows as $row){
			$response_row = [
				'type' => namify($row['TypeName']),
				'usage' => $row['Usage']
			];
			$response->data[] = $response_row;
		}
	
	}

	$RS = null;

	$response->output();

	die();

}
ajax_add('page_type', 'list');

function page_type_list_admin(){

	$response = new AJAXResponse();

	$RS = get_records('pageType_List');
	
	if($RS->eof){

		$response->status_code = 404;

	} else {

		foreach($RS->rows as $row){
			$response_row = [
				'id' => $row['TypeName'],
				'name' => namify($row['TypeName']) . ' (' . $row['Usage'] . ')'
			];
			$response->data[] = $response_row;
		}
	
	}

	$RS = null;

	$response->output();

	die();

}
ajax_add('page_type', 'list_admin');

function page_search_admin(){

	global $current_user;

	$response = new AJAXResponse();

	$parent_id = do_form('id', '0');
	$search = do_form('search', '');
	$type = do_form('type', 'page');
	$page = do_form('pagination_page', 1);
	$per_page = do_form('pagination_per_page', 25);

	$params = [
		$parent_id,
		$search,
		$type,
		$current_user->id,
		$page,
		$per_page
	];

	$RS = get_records('page_Search_Admin', $params);

	$response->pagination = new Pagination('/ajax/page/search-admin', $RS, $page, $per_page);

	if(!$RS->eof){
	
		$response->pagination->total_records = $RS->row['ResultCount'];
		$response->pagination->total_pages = ceil($response->pagination->total_records / $response->pagination->per_page);

		foreach($RS->rows as $row){

			$response_row = [
				'id' => $row['PageId'],
				'parent_id' => $row['ParentId'],
				'type' => namify($row['TypeName']),
				'name' => $row['Name'],
				'img' => '',
				'date' => human_date($row['DateCreated']),
				'author' => $row['AuthorName'],
				"detail_url" => '/ajax/page/detail-admin/' . $row['PageId'],
			];

			$response->data[] = $response_row;

		}

	}

	$RS = null;

	$response->output();

	die();

}
ajax_add('page', 'search_admin');

function page_search(){

	global $current_user;

	$response = new AJAXResponse();

	$parent_id = do_form('id', '0');
	$search = do_form('search', '');
	$type = do_form('type', 'page');
	$page = do_form('pagination_page', 1);
	$per_page = do_form('pagination_per_page', 25);

	$params = [
		$parent_id,
		$search,
		$type,
		$current_user->id,
		$page,
		$per_page
	];

	$RS = get_records('page_Search', $params);

	if(!$RS->eof){

		foreach($RS->rows as $row){

			$response_row = [
				'id' => $row['PageId'],
				'parent_id' => $row['ParentId'],
				'type' => namify($row['TypeName']),
				'name' => $row['Name'],
				'img' => '',
				'date' => human_date($row['DateCreated']),
				'author' => $row['AuthorName'],
				"detail_url" => '/ajax/page/detail/' . $row['PageId'],
			];

			$response->data[] = $response_row;

		}

	}

	$RS = null;

	$response->output();

	die();

}
ajax_add('page', 'search');

function page_detail(){

	global $current_user;

	$response = new AJAXResponse();

	$id = do_form('id');

	$params = [
		$id,
		$current_user->id
	];

	$RS = get_records('page_Detail', $params);
	
	$response->rs_add($RS, 403);

	$RS = null;

	$response->output();

	die();

}
ajax_add('page', 'detail');

function page_detail_admin(){

	global $current_user;

	$response = new AJAXResponse();

	$id = do_form('id');

	$params = [
		$id,
		$current_user->id
	];

	$RS = get_records('page_Detail_Admin', $params);

	if($RS->eof){

		$response->status_code = 403;

	} else {

		$response->data['id'] = $RS->row['PageId'];
		$response->data['parent_id'] = $RS->row['ParentId'];
		$response->data['name'] = $RS->row['Name'];
		$response->data['url'] = $RS->row['Url'];

		$form = new Form('POST', '/ajax/page/save/' . $RS->row['PageId']);

		$grid = new Grid('form');
		$form->append($grid);

		$grid_item = new GridItem('form');
		$grid_item->append(new FormInput('name', 'Name', 'name', '', [
			'value' => $RS->row['Name']
		]));
		$grid->append($grid_item);

		if($RS->row['PageId'] === 1){
			
			$form->append(new FormInputHidden('slug', [
				'value' => $RS->row['Slug']
			]));
			
			$form->append(new FormInputHidden('type', [
				'value' => $RS->row['TypeName']
			]));

		} else {

			$grid_item = new GridItem('form');
			$grid_item->append(new FormInput('slug', 'URL', 'slug', '', [
				'value' => $RS->row['Slug']
			]));
			$grid->append($grid_item);

			$grid_item = new GridItem('form');
			$grid_item->append(new FormInput('type', 'Page Type', 'type', '', [
				'value' => $RS->row['TypeName']
			]));
			$grid->append($grid_item);

		}

		$templates = [
			'0' => 'None'
		];

		$params = [
			null,
			null,
			'template',
			1,
			999
		];

		$templates_rs = get_records('thing_Search', $params);
	
		while(!$templates_rs->eof){
	
			$templates[$templates_rs->row['ThingId']] = $templates_rs->row['Name'];
	
			$templates_rs->move_next();
	
		}
	
		$templates_rs = null;

		$form->append(new FormInputSelect('template_id', 'Template', $templates, 'template_id', '', [
			'value' => $RS->row['TemplateId']
		]));

		$flex = new Flex('detail');

		$params = [
			$current_user->id
		];

		$users_rs = get_records('userGroup_List', $params);

		$user_options = [];
		
		while(!$users_rs->eof){
			$user_options[$users_rs->row['UserGroupId']] = $users_rs->row['UserGroupName'];
			$users_rs->move_next();
		}

		$params = [
			$RS->row['PageId'],
			$current_user->id
		];

		$permissions_rs = get_records('pagePermissions_List', $params);

		if(!$permissions_rs->eof){

			$flex_item = new FlexItem('detail');

			$flex_item->append(new Heading(3, 'Permissions', 'section'));

			while(!$permissions_rs->eof){

				$flex_item->append(new FormInputSelect('permissions[' . $permissions_rs->row['ActionId'] . ']', $permissions_rs->row['ActionName'], $user_options, 'permissions_' . $permissions_rs->row['ActionId'], '', [
					'value' => $permissions_rs->row['UserGroupId']
				]));

				$permissions_rs->move_next();

			}

			$flex->append($flex_item);
		}

		$params = [
			$RS->row['PageId'],
			$current_user->id
		];

		$meta_rs = get_records('pageMeta_List_Admin', $params);

		if(!$meta_rs->eof){

			$flex_item = new FlexItem('detail');

			$flex_item->append(new Heading(3, 'Meta', 'section'));

			while(!$meta_rs->eof){

				$flex_item->append(new FormInput('meta[' . $meta_rs->row['MetaTagId'] . '][' . $meta_rs->row['MetaAttributeId'] . ']', $meta_rs->row['FriendlyName'], 'meta_' . $meta_rs->row['MetaTagId'] . '_' . $meta_rs->row['MetaAttributeId'], '', [
					'value' => $meta_rs->row['MetaContent'],
					'placeholder' => page_parse($meta_rs->row['DefaultContent'], $RS)
				]));

				$meta_rs->move_next();

			}

			$flex->append($flex_item);

		}

		$form->append($flex);

		$response->data['form'] = $form->render();

	}

	$RS = null;

	$response->output();

	die();

}
ajax_add('page', 'detail_admin');

function page_data_save(){

	global $current_user;

	$response = new AJAXResponse();

	$id = do_form('id');
	$page_id = do_form('page_id');
	$data_key = do_form('data_key');
	$data_value = do_form('data_value');

	$params = [
		$id,
		$page_id,
		$data_key,
		$data_value,
		$current_user->id
	];

	$RS = get_records('pageData_Save', $params);

	$response->rs_add($RS, 403);

	$RS = null;

	$response->output();

	die();

}
ajax_add('page_data', 'save');

function page_data_remove(){

	global $current_user;

	$response = new AJAXResponse();

	$id = do_form('id');
	$page_id = do_form('page_id');
	$data_key = do_form('data_key');

	$params = [
		$id,
		$page_id,
		$data_key,
		$current_user->id
	];

	$RS = get_records('pageData_Remove', $params);

	$response->rs_add($RS, 403);

	$RS = null;

	$response->output();

	die();

}
ajax_add('page_data', 'remove');

function page_data_list(){

	global $current_user;

	$response = new AJAXResponse();

	$page_id = do_form('id', '0');

	$params = [
		$page_id,
		$current_user->id
	];

	$RS = get_records('pageData_List', $params);

	$response->rs_add($RS, 403);

	$RS = null;

	$response->output();

	die();

}
ajax_add('page_data', 'list');

function page_data_detail(){

	global $current_user;

	$response = new AJAXResponse();

	$id = do_form('id');
	$page_id = do_form('page_id');
	$data_key = do_form('data_key');

	$params = [
		$id,
		$page_id,
		$data_key,
		$current_user->id
	];

	$RS = get_records('pageData_Detail', $params);

	$response->rs_add($RS, 403);

	$RS = null;

	$response->output();

	die();

}
ajax_add('page_data', 'detail');

?>