<?php

class Table {

	public $columns;
	public $rows;
	public $order_by;
	
	function output(){
		
		$out = new RapidString();
		
		usort($this->columns, 'priority_sort');
		
		$out->add('<table class="table">');
			$out->add('<thead class="table__head">');
				foreach($this->columns as $column){
					$out->add('<th scope="col" class="table__cell table__cell--header table__header table__header--head">');
					$out->add(html($column->label));
					$out->add('</th>');
				}
			$out->add('</thead>');
			$out->add('<tbody class="table__body">');
				foreach($this->rows as $row){
					$out->add($row->output());
				}
			$out->add('</tbody>');
		$out->add('</table>');
		
		return $out->dump();
	
	}
	
	function column_get(string $column_id){
		return $this->columns[$column_id];
	}
	
	function column_add(string $column_label, string $column_id = '', int $priority = null){
		if(empty($column_id)){
			$column_id = idify($column_label);
		}
		if(empty($priority)){
			$priority = count($this->columns);
		}
		$this->columns[$column_id] = new \TableColumn($column_label, $column_id, $priority);
	}
	
	function add(array $attributes = null, int $priority = null){
		if(empty($priority)){
			$priority = count($this->rows);
		}
		$row = new \TableRow($attributes, $priority);
		$this->rows[] = $row;
		return $row;
	}
	
	function __construct(){
		$this->columns = [];
		$this->rows = [];
	}

	function __destruct(){
		$this->columns = null;
		$this->rows = null;
	}
	
}

class TableRow {
	
	public $cells;
	public $priority;
	public $attributes;
	
	function output(){
		$out = new RapidString();
		$out->add('<tr');
		foreach($this->attributes as $key => $value){
			$out->add(' ');
			$out->add(html($key));
			$out->add('="');
			$out->add(html($value));
			$out->add('"');
		}
		$out->add('>');
		foreach($this->cells as $cell){
			$out->add($cell->output());
		}
		$out->add('</td>');
	}
	
	function add(string $content = '', array $attributes = [], string $type = 'cell'){
		$type = $type === 'header' ? $type : 'cell';
		$this->cells[] = new \TableCell($content, $attributes, $type);
	}
	
	function __construct(array $attributes = [], int $priority = 100){
		$this->cells = [];
		$this->priority = $priority;
		$this->attributes = $attributes;
	}

	function __destruct(){
		$this->cells = null;
		$this->attributes = null;
	}

}

class TableColumn {
	
	public $label;
	public $id;
	public $priority;
	
	function __construct(string $label, string $id = '', int $priority = 100){
		$this->label = $label;
		$this->id = $id;
		$this->priority = $priority;
	}

	function __destruct(){
		$this->label = null;
		$this->id = null;
		$this->priority = null;
	}
	
}

class TableCell {
	
	public $type;
	public $content;
	public $priority;
	public $attributes;
	
	function output(){
		$out = new RapidString();
		if($this->type === 'header'){
			$out->add('<th scope="row" class="table__cell table__cell--header table__header table__header--head"');
		} else {
			$out->add('<td');
		}
		foreach($this->attributes as $key => $value){
			$out->add(' ');
			$out->add(html($key));
			$out->add('="');
			$out->add(html($value));
			$out->add('"');
		}
		$out->add('>');
		$out->add($this->content);
		if($this->type === 'header'){
			$out->add('</th>');
		} else {
			$out->add('</td>');
		}
	}
	
	function __construct(string $content = '', array $attributes = [], string $type = 'cell'){
		$this->type = $type === 'header' ? $type : 'cell';
		$this->content = $content;
		$this->attributes = $attributes;
	}

	function __destruct(){
		$this->content = null;
		$this->attributes = null;
		$this->type = null;
		$this->priority = null;
	}

}

function idify(string $label){
	$id = $label;
	$id = regex_replace('/[^\da-zA-Z]+/', '_', $id);
	$id = regex_replace('/([A-Z])/', '_$1', $id);
	$id = regex_replace('/(\d+)/', '_$1', $id);
	$id = regex_replace('/\_{2,}/', '_', $id);
	$id = strtolower($id);
	return $id;
}

function priority_sort($a, $b){
	if($a->priority === $b->priority){
		return 0;
	}
	return ($a->priority < $b->priority) ? -1 : 1;
}

?>