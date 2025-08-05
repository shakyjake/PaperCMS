<?php

class AdminList extends Component {
	
	function __construct(string $src_url, ?array $filter_urls = [], ?string $class = '', ?array $attributes = []){
		parent::__construct();
		$this->args['src_url'] = $src_url;
		$this->args['filter_urls'] = $filter_urls;
		$this->args['class'] = $class;
		$this->args['attributes'] = $attributes;
		$parts = [];
		$parts[] = '<div class="admin__panel admin__panel--list';
		if(has_value($class)){
			$parts[] = ' admin__panel--';
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
		$parts[] = '>';
			$parts[] = '<div class="admin__refine';
			if(has_value($class)){
				$parts[] = ' admin__refine--';
				$parts[] = html($class);
			}
			$parts[] = '">';
				$parts[] = '<div class="form__field form__field--refine">';
					$parts[] = '<label class="form__label form__label--refine screen-reader-text" for="refine-search">Search</label>';
					$parts[] = '<input type="text" class="form__input form__input--refine" id="refine-search" placeholder="Search" />';
				$parts[] = '</div>';
				if(is_iterable($filter_urls)){
					foreach($filter_urls as $filter_url){
						$parts[] = '<div class="admin__filter">';
							$parts[] = '<div class="form__field form__field--filter">';
								$parts[] = '<button type="button" class="form__button form__button--filter">Filter</button>';
							$parts[] = '</div>';
							$parts[] = '<div class="filter__options">';
								$cb = new FormInputCheckboxList($filter_url['name'], $filter_url['label'], $filter_url['url'], $filter_url['name'], '', ['value' => $filter_url['value']]);
								$parts[] = $cb->html;
							$parts[] = '</div>';
						$parts[] = '</div>';
					}
				}
			$parts[] = '</div>';
			$parts[] = '<div class="admin__list';
			if(has_value($class)){
				$parts[] = ' admin__list--';
				$parts[] = html($class);
			}
			$parts[] = '" data-src="';
			$parts[] = html($src_url);
			$parts[] = '"></div>';
			$parts[] = '<div class="admin__pagination';
			if(has_value($class)){
				$parts[] = ' admin__pagination--';
				$parts[] = html($class);
			}
			$parts[] = '"></div>';
		$parts[] = '</div>';
		$this->html = implode('', $parts);
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'admin-list';
		return $json;
	}

}

class PageChooser extends Component {
	
	function __construct(string $name, string $label, string $id = '', string $class = '', array $attributes = []){

		global $current_user;

		parent::__construct();
		$this->args['node_name'] = 'input';
		$this->args['class'] = $class;
		$this->args['name'] = $name;
		$this->args['id'] = $id;
		$this->args['attributes'] = $attributes;
		$this->args['attributes']['type'] = 'text';

		$value = do_form($name, '');
		if(empty($value)){
			if(!empty($this->args['attributes']['value'])){
				$value = $this->args['attributes']['value'];
				unset($this->args['attributes']['value']);
			}
		}

		$name_value = do_form($name . '_name', '');
		if(empty($name_value)){
			$this->args['attributes']['value'] = get_single_value('page_Name', [
				$value,
				$current_user->id
			]);
		} else {
			$this->args['attributes']['value'] = $name_value;
		}

		$this->can_have_children = false;

		$parts = [];
		$parts[] = '<div data-src="/ajax/page/search" class="chooser form__field';
		if(has_value($class)){
			$parts[] = ' form__field--';
			$parts[] = html($class);
		}
		$parts[] = '"';
		if(strlen($id)){
			$parts[] = ' id="field-';
			$parts[] = html($id);
			$parts[] = '"';
		}
		$parts[] = '>';
			$parts[] = '<label class="form__label';
			if(has_value($class)){
				$parts[] = ' form__label--';
				$parts[] = html($class);
			}
			$parts[] = '" for="';
			$parts[] = html(strlen($id) ? $id : $name);
			$parts[] = '_name">';
				$parts[] = html($label);
			$parts[] = '</label>';
			$parts[] = '<input name="';
			$parts[] = html($name);
			$parts[] = '_name" id="';
			$parts[] = html(strlen($id) ? $id : $name);
			$parts[] = '_name" class="chooser__input form__input';
			if(has_value($class)){
				$parts[] = ' form__input--';
				$parts[] = html($class);
			}
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
			$parts[] = ' />';
			$parts[] = '<input type="hidden" class="chooser__value" name="';
			$parts[] = html($name);
			$parts[] = '" id="';
			$parts[] = html(strlen($id) ? $id : $name);
			$parts[] = '" />';
		$parts[] = '</div>';
		
		$this->html = implode('', $parts);
	
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'page-chooser';
		return $json;
	}

}

?>