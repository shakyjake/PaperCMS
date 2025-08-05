<?php

class Form extends Component {
	
	function __construct(string $method = 'post', string $action = '', string $class = '', array $attributes = []){
		parent::__construct();
		$this->args['node_name'] = 'form';
		$this->args['class'] = $class;
		$this->args['method'] = $method;
		$this->args['action'] = $action;
		$this->args['attributes'] = $attributes;
		$parts = [];
		$parts[] = '<form';
		$parts[] = ' class="form';
		if(has_value($class)){
			$parts[] = ' ';
			$parts[] = html($class);
		}
		$parts[] = '" method="';
		$parts[] = html($method);
		$parts[] = '" action="';
		$parts[] = html($action);
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
		$parts[] = '>%1$s</form>';
		
		$this->html = implode('', $parts);
	
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'form';
		return $json;
	}

}

class FormInput extends Component {
	
	function __construct(string $name, string $label, string $id = '', string $class = '', array $attributes = []){

		parent::__construct();
		$this->args['node_name'] = 'input';
		$this->args['class'] = $class;
		$this->args['name'] = $name;
		$this->args['id'] = $id;
		$this->args['attributes'] = $attributes;
		$this->args['attributes']['type'] = 'text';
		$value = do_form($name, '');
		if(!empty($value)){
			$this->args['attributes']['value'] = $value;
		}
		$this->can_have_children = false;

		$parts = [];
		$parts[] = '<div class="form__field';
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
			$parts[] = '">';
				$parts[] = html($label);
			$parts[] = '</label>';
			$parts[] = '<input name="';
			$parts[] = html($name);
			$parts[] = '" id="';
			$parts[] = html(strlen($id) ? $id : $name);
			$parts[] = '" class="form__input';
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
		$parts[] = '</div>';
		
		$this->html = implode('', $parts);
	
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'input';
		return $json;
	}

}

class FormInputEmail extends Component {
	
	function __construct(string $name, string $label, string $id = '', string $class = '', array $attributes = []){

		parent::__construct();
		$this->args['node_name'] = 'input';
		$this->args['class'] = $class;
		$this->args['name'] = $name;
		$this->args['id'] = $id;
		$this->args['attributes'] = $attributes;
		$this->args['attributes']['type'] = 'email';
		$value = do_form($name, '');
		if(!empty($value)){
			$this->args['attributes']['value'] = $value;
		}
		$this->can_have_children = false;

		$parts = [];
		$parts[] = '<div class="form__field';
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
			$parts[] = '">';
				$parts[] = html($label);
			$parts[] = '</label>';
			$parts[] = '<input name="';
			$parts[] = html($name);
			$parts[] = '" id="';
			$parts[] = html(strlen($id) ? $id : $name);
			$parts[] = '" class="form__input';
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
		$parts[] = '</div>';
		
		$this->html = implode('', $parts);
	
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'input-email';
		return $json;
	}

}

class FormInputPassword extends Component {
	
	function __construct(string $name, string $label, string $id = '', string $class = '', array $attributes = []){

		parent::__construct();
		$this->args['node_name'] = 'input';
		$this->args['class'] = $class;
		$this->args['name'] = $name;
		$this->args['id'] = $id;
		$this->args['attributes'] = $attributes;
		$this->args['attributes']['type'] = 'password';
		$value = do_form($name, '');
		if(!empty($value)){
			$this->args['attributes']['value'] = $value;
		}
		$this->can_have_children = false;

		$parts = [];
		$parts[] = '<div class="form__field';
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
			$parts[] = '">';
				$parts[] = html($label);
			$parts[] = '</label>';
			$parts[] = '<input name="';
			$parts[] = html($name);
			$parts[] = '" id="';
			$parts[] = html(strlen($id) ? $id : $name);
			$parts[] = '" class="form__input';
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
		$parts[] = '</div>';
		
		$this->html = implode('', $parts);
	
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'input-password';
		return $json;
	}

}

class FormInputPhone extends Component {
	
	function __construct(string $name, string $label, string $id = '', string $class = '', array $attributes = []){

		parent::__construct();
		$this->args['node_name'] = 'input';
		$this->args['class'] = $class;
		$this->args['name'] = $name;
		$this->args['id'] = $id;
		$this->args['attributes'] = $attributes;
		$this->args['attributes']['type'] = 'tel';
		$value = do_form($name, '');
		if(!empty($value)){
			$this->args['attributes']['value'] = $value;
		}
		$this->can_have_children = false;

		$parts = [];
		$parts[] = '<div class="form__field';
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
			$parts[] = '">';
				$parts[] = html($label);
			$parts[] = '</label>';
			$parts[] = '<input name="';
			$parts[] = html($name);
			$parts[] = '" id="';
			$parts[] = html(strlen($id) ? $id : $name);
			$parts[] = '" class="form__input';
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
		$parts[] = '</div>';
		
		$this->html = implode('', $parts);
	
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'input-phone';
		return $json;
	}

}

class FormInputHidden extends Component {
	
	function __construct(string $name, array $attributes = []){

		parent::__construct();
		$this->args['node_name'] = 'input';
		$this->args['name'] = $name;
		$this->args['attributes'] = $attributes;
		$this->args['attributes']['type'] = 'hidden';
		$value = do_form($name, '');
		if(!empty($value)){
			$this->args['attributes']['value'] = $value;
		}
		$this->can_have_children = false;

		$parts = [];
		$parts[] = '<input name="';
		$parts[] = html($name);
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
		
		$this->html = implode('', $parts);
	
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'input-hidden';
		return $json;
	}

}

class FormInputTextarea extends Component {
	
	function __construct(string $name, string $label, string $id = '', string $class = '', array $attributes = []){

		parent::__construct();
		$this->args['node_name'] = 'input';
		$this->args['class'] = $class;
		$this->args['name'] = $name;
		$this->args['id'] = $id;
		$this->args['attributes'] = $attributes;
		$value = do_form($name, '');
		if(empty($value)){
			$value = !empty($this->args['attributes']['value']) ? $this->args['attributes']['value'] : '';
			unset($this->args['attributes']['value']);
		}
		$this->can_have_children = false;

		$parts = [];
		$parts[] = '<div class="form__field';
		if(has_value($class)){
			$parts[] = ' form__field--';
			$parts[] = html($class);
		} else {
			$parts[] = ' form__field--textarea';
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
			$parts[] = '">';
				$parts[] = html($label);
			$parts[] = '</label>';
			$parts[] = '<textarea name="';
			$parts[] = html($name);
			$parts[] = '" id="';
			$parts[] = html(strlen($id) ? $id : $name);
			$parts[] = '" class="form__input';
			if(has_value($class)){
				$parts[] = ' form__input--';
				$parts[] = html($class);
			} else {
				$parts[] = ' form__input--textarea';
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
			$parts[] = '>';
			$parts[] = html($value);
			$parts[] = '</textarea>';
		$parts[] = '</div>';
		
		$this->html = implode('', $parts);
	
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'textarea';
		return $json;
	}

}

class FormInputCheckbox extends Component {
	
	function __construct(string $name, string $label, string $value, string $id = '', string $class = '', array $attributes = []){

		parent::__construct();
		$this->args['node_name'] = 'input';
		$this->args['class'] = $class;
		$this->args['name'] = $name;
		$this->args['id'] = $id;
		$this->args['value'] = $value;
		$this->args['attributes'] = $attributes;
		$this->args['attributes']['type'] = 'checkbox';

		if(isset($_REQUEST[$name])){
			$checked = $_REQUEST[$name] === $value;
		} else if(isset($attributes['checked'])){
			$checked = $attributes['checked'];
			unset($attributes['checked']);
		} else {
			$checked = false;
		}
		
		$this->can_have_children = false;

		$parts = [];
		$parts[] = '<div class="form__field checkbox';
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
			$parts[] = '<div class="checkbox__item">';
				$parts[] = '<input name="';
				$parts[] = html($name);
				$parts[] = '" id="';
				$parts[] = html(strlen($id) ? $id : $name);
				$parts[] = '" class="form__input checkbox__input';
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
				$parts[] = ' value="';
				$parts[] = html($value);
				$parts[] = '"';
				if($checked){
					$parts[] = ' checked';
				}
				$parts[] = ' />';
				$parts[] = '<label class="form__label checkbox__label';
				if(has_value($class)){
					$parts[] = ' form__label--';
					$parts[] = html($class);
				}
				$parts[] = '" for="';
				$parts[] = html(strlen($id) ? $id : $name);
				$parts[] = '">';
					$parts[] = html($label);
				$parts[] = '</label>';
			$parts[] = '</div>';
		$parts[] = '</div>';
		
		$this->html = implode('', $parts);
	
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'checkbox';
		return $json;
	}

}

class FormInputCheckboxList extends Component {
	
	function __construct(string $name, string $label, mixed $options, string $id = '', string $class = '', array $attributes = []){

		parent::__construct();
		$this->args['node_name'] = 'input';
		$this->args['class'] = $class;
		$this->args['name'] = $name;
		$this->args['id'] = $id;
		$this->args['options'] = $options;
		$this->args['attributes'] = $attributes;
		$this->args['attributes']['type'] = 'checkbox';
		$value = do_form($name, '');
		if(empty($value)){
			$value = !empty($this->args['attributes']['value']) ? $this->args['attributes']['value'] : '';
			unset($this->args['attributes']['value']);
		}
		$this->can_have_children = false;

		$parts = [];
		$parts[] = '<div class="form__field checkbox';
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
			$parts[] = '<div class="form__label';
			if(has_value($class)){
				$parts[] = ' form__label--';
				$parts[] = html($class);
			}
			$parts[] = '">';
				$parts[] = html($label);
			$parts[] = '</div>';
			$parts[] = '<div class="form__input checkbox__list"';
			if(is_string($options)){
				$parts[] = ' data-ajax-source="';
				$parts[] = html($options);
				$parts[] = '"';
				$parts[] = ' data-name="';
				$parts[] = html($name);
				$parts[] = '"';
				$parts[] = ' data-class="';
				$parts[] = html($class);
				$parts[] = '"';
				$parts[] = ' data-value="';
				$parts[] = html($value);
				$parts[] = '"';
			}
			$parts[] = '>';
			if(is_iterable($options)){
				foreach($options as $option_value => $option_label){
					$parts[] = '<div class="checkbox__item">';
						$parts[] = '<input name="';
						$parts[] = html($name);
						$parts[] = '[]" id="';
						$parts[] = html(strlen($id) ? $id : $name);
						$parts[] = '_';
						$parts[] = html($option_value);
						$parts[] = '" class="form__input checkbox__input';
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
						$parts[] = ' value="';
						$parts[] = html($option_value);
						$parts[] = '"';
						if($option_value === $value){
							$parts[] = ' checked';
						}
						$parts[] = ' />';
						$parts[] = '<label class="form__label checkbox__label';
						if(has_value($class)){
							$parts[] = ' form__label--';
							$parts[] = html($class);
						}
						$parts[] = '" for="';
						$parts[] = html(strlen($id) ? $id : $name);
						$parts[] = '_';
						$parts[] = html($option_value);
						$parts[] = '">';
							$parts[] = html($option_label);
						$parts[] = '</label>';
					$parts[] = '</div>';
				}
			}
			$parts[] = '</div>';
		$parts[] = '</div>';
		
		$this->html = implode('', $parts);
	
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'checkbox-list';
		return $json;
	}

}

class FormInputRadioList extends Component {
	
	function __construct(string $name, string $label, mixed $options, string $id = '', string $class = '', array $attributes = []){

		parent::__construct();
		$this->args['node_name'] = 'input';
		$this->args['class'] = $class;
		$this->args['name'] = $name;
		$this->args['id'] = $id;
		$this->args['options'] = $options;
		$this->args['attributes'] = $attributes;
		$this->args['attributes']['type'] = 'radio';
		$value = do_form($name, '');
		if(empty($value)){
			$value = !empty($this->args['attributes']['value']) ? $this->args['attributes']['value'] : '';
			unset($this->args['attributes']['value']);
		}
		$this->can_have_children = false;

		$parts = [];
		$parts[] = '<div class="form__field checkbox';
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
			$parts[] = '<div class="form__label';
			if(has_value($class)){
				$parts[] = ' form__label--';
				$parts[] = html($class);
			}
			$parts[] = '">';
				$parts[] = html($label);
			$parts[] = '</div>';
			$parts[] = '<div class="form__input checkbox__list checkbox__list--radio"';
			if(is_string($options)){
				$parts[] = ' data-ajax-source="';
				$parts[] = html($options);
				$parts[] = '"';
				$parts[] = ' data-name="';
				$parts[] = html($name);
				$parts[] = '"';
				$parts[] = ' data-class="';
				$parts[] = html($class);
				$parts[] = '"';
				$parts[] = ' data-value="';
				$parts[] = html($value);
				$parts[] = '"';
			}
			$parts[] = '>';
			if(is_iterable($options)){
				foreach($options as $option_value => $option_label){
					$parts[] = '<div class="checkbox__item">';
						$parts[] = '<input name="';
						$parts[] = html($name);
						$parts[] = '" id="';
						$parts[] = html(strlen($id) ? $id : $name);
						$parts[] = '_';
						$parts[] = html($option_value);
						$parts[] = '" class="form__input checkbox__input checkbox__input--radio';
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
						$parts[] = ' value="';
						$parts[] = html($option_value);
						$parts[] = '"';
						if($option_value === $value){
							$parts[] = ' checked';
						}
						$parts[] = ' />';
						$parts[] = '<label class="form__label checkbox__label';
						if(has_value($class)){
							$parts[] = ' form__label--';
							$parts[] = html($class);
						}
						$parts[] = '" for="';
						$parts[] = html(strlen($id) ? $id : $name);
						$parts[] = '_';
						$parts[] = html($option_value);
						$parts[] = '">';
							$parts[] = html($option_label);
						$parts[] = '</label>';
					$parts[] = '</div>';
				}
			}
			$parts[] = '</div>';
		$parts[] = '</div>';
		
		$this->html = implode('', $parts);
	
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'radio-list';
		return $json;
	}

}

class FormInputSelect extends Component {
	
	function __construct(string $name, string $label, mixed $options, string $id = '', string $class = '', array $attributes = []){

		parent::__construct();
		$this->args['node_name'] = 'select';
		$this->args['class'] = $class;
		$this->args['name'] = $name;
		$this->args['id'] = $id;
		$this->args['options'] = $options;
		$this->args['attributes'] = $attributes;
		$value = do_form($name, '');
		if(empty($value)){
			$value = isset($this->args['attributes']['value']) ? (string)$this->args['attributes']['value'] : '';
			unset($this->args['attributes']['value']);
		}
		$this->can_have_children = false;

		$parts = [];
		$parts[] = '<div class="form__field form__field--select';
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
			$parts[] = '<label class="form__label form__label--select';
			if(has_value($class)){
				$parts[] = ' form__label--';
				$parts[] = html($class);
			}
			$parts[] = '" for="';
			$parts[] = html(strlen($id) ? $id : $name);
			$parts[] = '">';
				$parts[] = html($label);
			$parts[] = '</label>';
			$parts[] = '<select name="';
			$parts[] = html($name);
			$parts[] = '" id="';
			$parts[] = html(strlen($id) ? $id : $name);
			$parts[] = '" class="form__input form__input--select';
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
			$parts[] = '>';
			if(is_string($options)){
				$parts[] = ' data-ajax-source="';
				$parts[] = html($options);
				$parts[] = '"';
				$parts[] = ' data-value="';
				$parts[] = html($value);
				$parts[] = '"';
			}
			if(is_iterable($options)){
				foreach($options as $option_value => $option_label){
					$parts[] = '<option value="';
					$parts[] = html($option_value);
					$parts[] = '"';
					if($value === (string)$option_value){
						$parts[] = ' selected';
					}
					$parts[] = '>';
					$parts[] = html($option_label);
					$parts[] = '</option>';
				}
			}
			$parts[] = '</select>';
		$parts[] = '</div>';
		
		$this->html = implode('', $parts);
	
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'select';
		return $json;
	}

}

class FormButton extends Component {
	
	function __construct(string $label, string $class = '', array $attributes = []){

		parent::__construct();
		$this->args['node_name'] = 'button';
		$this->args['class'] = $class;
		$this->args['attributes'] = $attributes;
		$this->can_have_children = false;

		$parts = [];
		$parts[] = '<div class="form__field';
		if(has_value($class)){
			$parts[] = ' form__field--';
			$parts[] = html($class);
		}
		$parts[] = '">';
			$parts[] = '<button class="form__button';
			if(has_value($class)){
				$parts[] = ' form__button--';
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
			$parts[] = '>';
			$parts[] = html($label);
			$parts[] = '</button>';
		$parts[] = '</div>';
		
		$this->html = implode('', $parts);
	}
	
	function jsonSerialize():mixed{
		$json = parent::jsonSerialize();
		$json['type'] = 'button';
		return $json;
	}

}

?>