<?php

	class Component implements \JsonSerializable {
		
		public $html;
		public $can_have_children;
		public $children;
		public $args;
		
		function append(Component $component){
			if($this->can_have_children){
				$this->children[] = $component;
			}
			return $this;
		}
		
		function render(){
			return sprintf($this->html, $this->render_children());
		}
		
		function render_children(){
			if(!$this->can_have_children){
				return '';
			}
			$html = [];
			foreach($this->children as $child){
				$html[] = $child->render();
			}
			return implode('', $html);
		}
		
		function __construct(){
			$this->html = '%1$s';
			$this->can_have_children = true;
			$this->children = [];
			$this->args = [];
		}
		
		function __destruct(){
			$this->html = null;
			$this->can_have_children = null;
			$this->children = null;
			$this->args = null;
		}
		
		function jsonSerialize():mixed{
			$json = [
				'type' => ''
			];
			foreach($this->args as $key => $value){
				if(has_value($value)){
					$json[$key] = $value;
				}
			}
			if($this->can_have_children){
				$json['children'] = [];
				foreach($this->children as $child){
					$json['children'][] = $child;
				}
			}
			return $json;
		}
		
		function __toString(){
			return sprintf($this->html, $this->render_children());
		}
		
	}

	class Container extends Component {
		
		function __construct(string $node_name = 'div', string $class = '', array $attributes = []){
			parent::__construct();
			$this->args['node_name'] = $node_name;
			$this->args['class'] = $class;
			$this->args['attributes'] = $attributes;

			$parts = [];

			if(strlen($node_name)){
				$parts[] = '<';
				$parts[] = $node_name;
				if(has_value($class)){
					$parts[] = ' class="';
					$parts[] = html($class);
					$parts[] = '"';
				}
				if(count($attributes)){
					foreach($attributes as $name => $value){
						$parts[] = ' ';
						$parts[] = $name;
						$parts[] = '="';
						$parts[] = html($value);
						$parts[] = '"';
					}
				}
				$parts[] = '>';
			}
			$parts[] = '%1$s';
			if(strlen($node_name)){
				$parts[] = '</';
				$parts[] = $node_name;
				$parts[] = '>';
			}
			
			$this->html = implode('', $parts);
		}
		
		function jsonSerialize():mixed{
			$json = parent::jsonSerialize();
			$json['type'] = 'container';
			return $json;
		}
		
	}

	class Navigation extends Component {
		
		public $menu_id;
		
		function __construct(int $menu_id){
			parent::__construct();
			$this->can_have_children = false;
			$this->menu_id = $menu_id;
			$menu = menu_from_json($menu_id);
			$this->html = $menu->render();
		}
		
		function jsonSerialize():mixed{
			$json = parent::jsonSerialize();
			$json['id'] = $this->menu_id;
			$json['type'] = 'menu';
			return $json;
		}
		
	}

	class Flex extends Component {
		
		function __construct(string $class = '', array $attributes = []){
			parent::__construct();
			$this->args['class'] = $class;
			$this->args['attributes'] = $attributes;
			$parts = [];
			$parts[] = '<div class="flex';
			if(has_value($class)){
				$parts[] = ' ';
				$parts[] = html($class);
				$parts[] = '__flex';
			}
			$parts[] = '"';
			if(count($attributes)){
				foreach($attributes as $name => $value){
					$parts[] = ' ';
					$parts[] = $name;
					$parts[] = '="';
					$parts[] = html($value);
					$parts[] = '"';
				}
			}
			$parts[] = '>%1$s</div>';
			$this->html = implode('', $parts);
		}
		
		function jsonSerialize():mixed{
			$json = parent::jsonSerialize();
			$json['type'] = 'flex';
			return $json;
		}
		
	}

	class FlexItem extends Component {
		
		function __construct(string $class = '', array $attributes = []){
			parent::__construct();
			$this->args['class'] = $class;
			$this->args['attributes'] = $attributes;
			$parts = [];
			$parts[] = '<div class="flex__item';
			if(has_value($class)){
				$parts[] = ' flex__item--';
				$parts[] = html($class);
			}
			$parts[] = '"';
			if(count($attributes)){
				foreach($attributes as $name => $value){
					$parts[] = ' ';
					$parts[] = $name;
					$parts[] = '="';
					$parts[] = html($value);
					$parts[] = '"';
				}
			}
			$parts[] = '>%1$s</div>';
			$this->html = implode('', $parts);
		}
		
		function jsonSerialize():mixed{
			$json = parent::jsonSerialize();
			$json['type'] = 'flex-item';
			return $json;
		}
		
	}

	class Grid extends Component {
		
		function __construct(string $class = '', array $attributes = []){
			parent::__construct();
			$this->args['class'] = $class;
			$this->args['attributes'] = $attributes;
			$parts = [];
			$parts[] = '<div class="grid';
			if(has_value($class)){
				$parts[] = ' ';
				$parts[] = html($class);
				$parts[] = '__grid';
			}
			$parts[] = '"';
			if(count($attributes)){
				foreach($attributes as $name => $value){
					$parts[] = ' ';
					$parts[] = $name;
					$parts[] = '="';
					$parts[] = html($value);
					$parts[] = '"';
				}
			}
			$parts[] = '>%1$s</div>';
			$this->html = implode('', $parts);
		}
		
		function jsonSerialize():mixed{
			$json = parent::jsonSerialize();
			$json['type'] = 'grid';
			return $json;
		}
		
	}

	class GridItem extends Component {
		
		function __construct(string $class = '', array $attributes = []){
			parent::__construct();
			$this->args['class'] = $class;
			$this->args['attributes'] = $attributes;
			$parts = [];
			$parts[] = '<div class="grid__item';
			if(has_value($class)){
				$parts[] = ' grid__item--';
				$parts[] = html($class);
			}
			$parts[] = '"';
			if(count($attributes)){
				foreach($attributes as $name => $value){
					$parts[] = ' ';
					$parts[] = $name;
					$parts[] = '="';
					$parts[] = html($value);
					$parts[] = '"';
				}
			}
			$parts[] = '>%1$s</div>';
			$this->html = implode('', $parts);
		}
		
		function jsonSerialize():mixed{
			$json = parent::jsonSerialize();
			$json['type'] = 'grid-item';
			return $json;
		}
		
	}

	class RichText extends Component {
		
		function __construct(string $content = ''){
			parent::__construct();
			$this->args['content'] = $content;
			$this->can_have_children = false;
			$this->html = $content;
		}
		
		function jsonSerialize():mixed{
			$json = parent::jsonSerialize();
			$json['type'] = 'rich-text';
			return $json;
		}
		
	}

	class PlainText extends Component {
		
		function __construct(string $content = ''){
			parent::__construct();
			$this->args['content'] = $content;
			$this->can_have_children = false;
			$this->html = $content;
		}
		
		function jsonSerialize():mixed{
			$json = parent::jsonSerialize();
			$json['type'] = 'text';
			return $json;
		}
		
	}

	class Heading extends Component {
		
		function __construct(int $level, string $content = '', ?string $class = '', ?array $attributes = []){
			parent::__construct();
			$this->args['level'] = $level;
			$this->args['content'] = $content;
			$this->args['class'] = $content;
			$this->args['attributes'] = $attributes;
			$this->can_have_children = false;

			$parts = [];
			$parts[] = '<h';
			$parts[] = (string)$level;
			$parts[] = ' class="title';
			if(has_value($class)){
				$parts[] = ' ';
				$parts[] = html($class);
				$parts[] = '__title';
			}
			$parts[] = '"';
			if(count($attributes)){
				foreach($attributes as $name => $value){
					$parts[] = ' ';
					$parts[] = $name;
					$parts[] = '="';
					$parts[] = html($value);
					$parts[] = '"';
				}
			}
			$parts[] = '>';
			$parts[] = html($content);
			$parts[] = '</h';
			$parts[] = (string)$level;
			$parts[] = '>';
			$this->html = implode('', $parts);

		}
		
		function jsonSerialize():mixed{
			$json = parent::jsonSerialize();
			$json['type'] = 'heading';
			return $json;
		}
		
	}

	class Link extends Component {
		
		function __construct(string $href = '', string $class = '', array $attributes = []){
			parent::__construct();
			$this->args['node_name'] = 'a';
			$this->args['href'] = $href;
			$this->args['class'] = $class;
			$this->args['attributes'] = $attributes;

			$parts = [];
			$parts[] = '<a';
			$parts[] = ' class="link';
			if(has_value($class)){
				$parts[] = ' ';
				$parts[] = html($class);
				$parts[] = '__link';
			}
			$parts[] = '" href="';
			$parts[] = html($href);
			$parts[] = '"';
			if(count($this->args['attributes'])){
				foreach($this->args['attributes'] as $attribute_name => $attribute_value){
					$parts[] = ' ';
					$parts[] = $attribute_name;
					$parts[] = '="';
					$parts[] = html($attribute_value);
					$parts[] = '"';
				}
			}
			$parts[] = '>%1$s</a>';
		
			$this->html = implode('', $parts);
		}
		
		function jsonSerialize():mixed{
			$json = parent::jsonSerialize();
			$json['type'] = 'link';
			return $json;
		}
		
	}

	class Image extends Component {
		
		function __construct(string $src, int $width, int $height, string $alt = '', string $loading = '', string $class = '', array $attributes = []){
			parent::__construct();
			if($loading !== 'eager'){
				$loading = 'lazy';
			}
			$this->args['src'] = $src;
			$this->args['width'] = $width;
			$this->args['height'] = $height;
			$this->args['alt'] = $alt;
			$this->args['loading'] = $loading;
			$this->args['class'] = $class;
			$this->args['attributes'] = $attributes;
			$this->can_have_children = false;
			$parts = [];
			if(has_value($src) && has_value($width) && has_value($height)){
				$parts[] = '<img';
				$parts[] = ' src="';
				$parts[] = html($src);
				$parts[] = '"';
				$parts[] = ' width="';
				$parts[] = html($width);
				$parts[] = '"';
				$parts[] = ' height="';
				$parts[] = html($height);
				$parts[] = '"';
				$parts[] = ' alt="';
				$parts[] = html($alt);
				$parts[] = '"';
				$parts[] = ' loading="';
				$parts[] = html($loading);
				$parts[] = '"';
				$parts[] = ' class="img';
				if(has_value($class)){
					$parts[] = ' ';
					$parts[] = html($class);
					$parts[] = '__img';
				}
				$parts[] = '"';
				if(count($attributes)){
					foreach($attributes as $name => $value){
						$parts[] = ' ';
						$parts[] = $name;
						$parts[] = '="';
						$parts[] = html($value);
						$parts[] = '"';
					}
				}
				$parts[] = ' />';
			}
			$this->html = implode('', $parts);
		}
		
		function jsonSerialize(): mixed {
			$json = parent::jsonSerialize();
			$json['type'] = 'image';
			return $json;
		}
		
	}

	class Embed extends Component {
		
		function __construct(string $src, int $width, int $height, string $title = '', string $loading = '', string $class = '', array $attributes = ['rel' => 'noopener']){
			parent::__construct();
			if($loading !== 'eager'){
				$loading = 'lazy';
			}
			$this->args['src'] = $src;
			$this->args['width'] = $width;
			$this->args['height'] = $height;
			$this->args['title'] = $title;
			$this->args['loading'] = $loading;
			$this->args['class'] = $class;
			$this->args['attributes'] = $attributes;
			$this->can_have_children = false;
			$parts = [];
			if(has_value($src) && has_value($width) && has_value($height)){
				$parts[] = '<iframe';
				$parts[] = ' src="';
				$parts[] = html($src);
				$parts[] = '"';
				$parts[] = ' width="';
				$parts[] = html($width);
				$parts[] = '"';
				$parts[] = ' height="';
				$parts[] = html($height);
				$parts[] = '"';
				$parts[] = ' title="';
				$parts[] = html($title);
				$parts[] = '"';
				$parts[] = ' loading="';
				$parts[] = html($loading);
				$parts[] = '"';
				$parts[] = ' class="iframe';
				if(has_value($class)){
					$parts[] = ' ';
					$parts[] = html($class);
					$parts[] = '__iframe';
				}
				$parts[] = '"';
				if(count($attributes)){
					foreach($attributes as $name => $value){
						$parts[] = ' ';
						$parts[] = $name;
						$parts[] = '="';
						$parts[] = html($value);
						$parts[] = '"';
					}
				}
				$parts[] = '></iframe>';
			}
			$this->html = implode('', $parts);
		}
		
		function jsonSerialize():mixed{
			$json = parent::jsonSerialize();
			$json['type'] = 'embed';
			return $json;
		}
		
	}

	function component_parse(array $data){// TODO: completely rewrite this - it's not extensible
		if(!isset($data['class'])){
			$data['class'] = '';
		}
		if(!isset($data['attributes'])){
			$data['attributes'] = [];
		}
		switch($data['type']){
			case 'container':
				$component = new Container($data['node_name'], $data['class'], $data['attributes']);
				foreach($data['children'] as $child){
					$component->append(component_parse($child));
				}
				break;
			case 'admin-list':
				$component = new AdminList($data['src_url'], $data['filter_urls'], $data['class'], $data['attributes']);
				break;
			case 'menu':
				$component = new Navigation($data['menu_id']);
				break;
			case 'image':
				if(!isset($data['alt'])){
					$data['alt'] = '';
				}
				if(!isset($data['loading'])){
					$data['loading'] = '';
				}
				$component = new Image($data['src'], $data['width'], $data['height'], $data['alt'], $data['loading'], $data['class'], $data['attributes']);
				break;
			case 'embed':
				if(!isset($data['title'])){
					$data['title'] = '';
				}
				if(!isset($data['loading'])){
					$data['loading'] = '';
				}
				$component = new Embed($data['src'], $data['width'], $data['height'], $data['title'], $data['loading'], $data['class'], $data['attributes']);
				break;
			case 'rich-text':
				if(!isset($data['content'])){
					$data['content'] = '';
				}
				$component = new RichText($data['content']);
				break;
			case 'heading':
				if(empty($data['level'])){
					$data['level'] = '1';
				}
				$data['level'] = filter_var($data['level'], FILTER_SANITIZE_NUMBER_INT);
				if(empty($data['level'])){
					$data['level'] = '1';
				}
				if(!isset($data['content'])){
					$data['content'] = '';
				}
				$component = new Heading($data['level'], $data['content']);
				break;
			case 'text':
				if(!isset($data['content'])){
					$data['content'] = '';
				}
				$component = new PlainText($data['content']);
				break;
			case 'link':
				$component = new Link($data['href'], $data['class'], $data['attributes']);
				foreach($data['children'] as $child){
					$component->append(component_parse($child));
				}
				break;
			case 'flex':
				$component = new Flex($data['class'], $data['attributes']);
				foreach($data['children'] as $child){
					$component->append(component_parse($child));
				}
				break;
			case 'flex-item':
				$component = new FlexItem($data['class'], $data['attributes']);
				foreach($data['children'] as $child){
					$component->append(component_parse($child));
				}
				break;
			case 'grid':
				$component = new Grid($data['class'], $data['attributes']);
				foreach($data['children'] as $child){
					$component->append(component_parse($child));
				}
				break;
			case 'grid-item':
				$component = new GridItem($data['class'], $data['attributes']);
				foreach($data['children'] as $child){
					$component->append(component_parse($child));
				}
				break;
			case 'form':
				$component = new Form($data['method'], $data['action'], $data['class'], $data['attributes']);
				foreach($data['children'] as $child){
					$component->append(component_parse($child));
				}
				js_enqueue('/media/js/form.js', false, 10);
				break;
			case 'input':
				$component = new FormInput($data['name'], $data['label'], $data['id'], $data['class'], $data['attributes']);
				break;
			case 'input-email':
				$component = new FormInputEmail($data['name'], $data['label'], $data['id'], $data['class'], $data['attributes']);
				break;
			case 'input-phone':
				$component = new FormInputPhone($data['name'], $data['label'], $data['id'], $data['class'], $data['attributes']);
				break;
			case 'input-password':
				$component = new FormInputPassword($data['name'], $data['label'], $data['id'], $data['class'], $data['attributes']);
				break;
			case 'input-hidden':
				$component = new FormInputHidden($data['name'], $data['attributes']);
				break;
			case 'textarea':
				$component = new FormInputTextarea($data['name'], $data['label'], $data['id'], $data['class'], $data['attributes']);
				break;
			case 'checkbox':
				$component = new FormInputCheckbox($data['name'], $data['label'], $data['value'], $data['id'], $data['class'], $data['attributes']);
				break;
			case 'checkbox-list':
				$component = new FormInputCheckboxList($data['name'], $data['label'], $data['options'], $data['id'], $data['class'], $data['attributes']);
				break;
			case 'radio-list':
				$component = new FormInputRadioList($data['name'], $data['label'], $data['options'], $data['id'], $data['class'], $data['attributes']);
				break;
			case 'select':
				$component = new FormInputSelect($data['name'], $data['label'], $data['options'], $data['id'], $data['class'], $data['attributes']);
				break;
			case 'button':
				$component = new FormButton($data['label'], $data['class'], $data['attributes']);
				break;
			default:
				$component = new Component();
				foreach($data['children'] as $child){
					$component->append(component_parse($child));
				}
				break;
		}
		return $component;
	}

	function components_parse(array $data){
		$root = new Component();
		foreach($data as $child){
			$root->append(component_parse($child));
		}
		return $root;
	}

	function components_render(array $data){
		$root = components_parse($data);
		return $root->render();
	}

?>