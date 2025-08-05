<?php 
class Menu implements \JsonSerializable {
	
	public $name = null;
	public $id = null;
	public $nodes = null;
	public $class_modifier = null;

	function add(string $label = '', string $uri = '#', int $user_group_id = 1, string $icon = null){
		$node = new MenuItem($this->class_modifier);
		$node->label = $label;
		$node->uri = $uri;
		$node->user_group_id = $user_group_id;
		$node->icon = $icon;
		$this->nodes[] = $node;
		return $node;
	}

	function load_from_rs(int $parent_id = null){
		
		if(has_value($this->id)){
			
			$RS = get_records('menuItem_List', $this->id);

			while(!$RS->eof){
				
				$Item = new MenuItem($this->class_modifier);
				$Item->load_from_rs($RS->row);

				$RS->move_next();

			}

			$RS = null;

		}
	
	}

	function load_from_json(mixed $json){
		if(isset($json['name'])){
			$this->name = $json['name'];
		}
		if(isset($json['class_modifier'])){
			$this->class_modifier = $json['class_modifier'];
		}
		foreach($json['children'] as $child){
			$node = new MenuItem($this->class_modifier);
			$node->load_from_json($child);
			$this->nodes[] = $node;
		}
		
	}

	function render(){
		
		$output = '';
		
		if(count($this->nodes)){
		
			$classes = ['menu__list'];

			if(has_value($this->class_modifier)){
				$classes[] = 'menu__list--' . $this->class_modifier;
			}
			
			$html = new RapidString();
			
			$html->add('<ul');
			if(count($classes)){
				$html->add(' class="');
				$html->add(html(implode(' ', $classes)));
				$html->add('"');
			}
			$html->add('>');

			foreach($this->nodes as $node){
				$html->add($node->render());
			}

			$html->add('</ul>');
			$output = $html->dump();
			$html = null;
			
		}
		
		return $output;

	}

	function __construct(int $menu_id = null, string $class_modifier = null){
		
		if(!has_value($class_modifier) && has_value($menu_id) && !is_numeric($menu_id)){
			$class_modifier = $menu_id;
			$menu_id = null;
		}
		
		$this->id = $menu_id;
		$this->class_modifier = $class_modifier;
		$this->nodes = [];

	}

	function __destruct(){

		$this->id = null;
		$this->class_modifier = null;
		$this->nodes = null;

	}
		
	function jsonSerialize():mixed{
		$json = [
			'type' => ''
		];
		$json = [];
		$json['name'] = $this->name;
		$json['id'] = $this->id;
		$json['class_modifier'] = $this->class_modifier;
		$json['items'] = [];
		foreach($this->nodes as $child){
			$json['items'][] = $child;
		}
		return $json;
	}
	
	function __toString(){
		return $this->render();
	}

}

class MenuItem implements \JsonSerializable {

	public $id = null;
	public $menu_id = null;
	public $page_id = null;
	public $label = null;
	public $uri = null;
	public $user_group_id = null;
	public $icon = null;
	public $full_menu = false;
	public $sub_menu = null;
	public $class_modifier = null;

	function load_from_rs(mixed $row){
	
		$this->id = $row['MenuItemId'];
		$this->menu_id = $row['MenuId'];
		$this->page_id = $row['PageId'];
		$this->label = $row['Label'];
		$this->uri = $row['URL'];
		$this->user_group_id = $row['UserGroupId'];
		$this->icon = $row['Icon'];
		$this->full_menu = $row['FullMenu'];
	
	}

	function load_from_json(mixed $json){
		if(isset($json['page_id'])){
			$this->page_id = $json['page_id'];
		}
		if(isset($json['label'])){
			$this->label = $json['label'];
		}
		if(isset($json['uri'])){
			$this->uri = $json['uri'];
		}
		if(isset($json['user_group_id'])){
			$this->user_group_id = $json['user_group_id'];
		}
		if(isset($json['icon'])){
			$this->icon = $json['icon'];
		}
		if(isset($json['children'])){
			$this->sub_menu = new Menu($this->class_modifier);
			$this->sub_menu->load_from_json($json['children']);
		}
	}

	function render(){
		
		$item_classes = ['menu__item'];
		$link_classes = ['menu__link'];
		
		if(has_value($this->icon)){
			$link_classes[] = 'menu__icon';
		}

		if(has_value($this->class_modifier)){
			$item_classes[] = 'menu__item--' . $this->class_modifier;
			$link_classes[] = 'menu__link--' . $this->class_modifier;
		}
		
		$html = new RapidString();
		$html->add('<li class="');
		$html->add(html(implode(' ', $item_classes)));
		$html->add('">');
			$html->add('<a class="');
				$html->add(html(implode(' ', $link_classes)));
			$html->add('"');
			if(has_value($this->icon)){
				$html->add(' style="--icon: \'');
				$html->add(html($this->icon));
				$html->add('\'"');
			}
			$html->add(' href="');
			$html->add(html($this->uri));
			$html->add('">');

			$html->add(html($this->label));

			$html->add('</a>');
		
			if(has_value($this->sub_menu)){
				$html->add($this->sub_menu->output());
			}
		
		$html->add('</li>');
		
		$output = $html->dump();
		$html = null;
		return $output;
	
	}

	function sub_menu_add(){
	
		$this->sub_menu = new Menu($this->menu_id, $this->class_modifier);
		if(has_value($this->id)){
			$this->sub_menu_load();
		}
	
	}

	function sub_menu_load(){
		
	}
		
	function jsonSerialize():mixed{
		$json = [
			'type' => ''
		];
		$json = [];
		$json['id'] = $this->id;
		$json['menu_id'] = $this->menu_id;
		$json['page_id'] = $this->page_id;
		$json['label'] = $this->label;
		$json['uri'] = $this->uri;
		$json['user_group_id'] = $this->user_group_id;
		$json['icon'] = $this->icon;
		$json['full_menu'] = $this->full_menu;
		$json['sub_menu'] = $this->sub_menu;
		$json['class_modifier'] = $this->class_modifier;
		return $json;
	}

	function __construct(string $class_modifier = null){
	
		$this->class_modifier = $class_modifier;
		$this->full_menu = false;
	
	}

	function __destruct(){
	
		$this->id = null;
		$this->menu_id = null;
		$this->page_id = null;
		$this->label = null;
		$this->uri = null;
		$this->user_group_id = null;
		$this->icon = null;
		$this->full_menu = null;
		$this->class_modifier = null;
	
	}
	
	function __toString(){
		return $this->render();
	}

}

function menu_from_json(int $menu_id){
	$menu = new Menu($menu_id);
	if(file_exists(__ROOT__ . '/media/json/menu/menu-' . $menu_id . '.json')){
		$menu->load_from_json(json_decode(file_get_contents(__ROOT__ . '/media/json/menu/menu-' . $menu_id . '.json'), true));
	}
	return $menu;
}
?>