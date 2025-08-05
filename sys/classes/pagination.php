<?php

class Pagination implements \JsonSerializable {

	public $url = null;
	public $page = null;
	public $per_page = null;
	public $first_record = null;
	public $total_records = null;
	public $total_pages = null;

	function render(?int $max_links = 5) : string {

		$out = [];

		$out[] = '<div class="pagination">';

		if($this->page > 1){
			$out[] = '<a class="pagination__link pagination__link--first" href="';
			$out[] = html($this->url);
			$out[] = '">First</a>';
			$out[] = '<a class="pagination__link pagination__link--prev" href="';
			if($this->page === 2){
				$out[] = html($this->url);
			} else {
				$out[] = html(query_param_add($this->url, 'page', $this->page - 1));
			}
			$out[] = '">Previous</a>';
		}

		$from_page = max(1, $this->page - floor($max_links / 2));
		$to_page = min($this->total_pages, $from_page + $max_links - 1);
		$i = $from_page;

		while($i <= $to_page){

			$out[] = '<a class="pagination__link pagination__link--numeric';
			if($i === $this->page){
				$out[] = ' pagination__link--current';
			}
			$out[] = '" href="';
			if($i === 1){
				$out[] = html($this->url);
			} else {
				$out[] = html(query_param_add($this->url, 'page', $this->page - 1));
			}
			$out[] = '">';
			$out[] = html($i);
			$out[] = '</a>';

			$i += 1;

		}

		$out[] = '<div class="pagination__details">Showing ';
			$out[] = html($this->first_record);
			$out[] = '&ndash;';
			$out[] = min($this->first_record + $this->per_page - 1, $this->total_records);
			$out[] = ' of ';
			$out[] = html($this->total_records);
		$out[] = '</div>';

		return implode('', $out);

	}

	function __construct(string $url, RecordSet $rs, int $page, int $per_page){
		$this->url = $url;
		$this->page = $page;
		$this->per_page = $per_page;
		$this->total_records = count($rs->rows);
		$this->total_pages = ceil($this->total_records / $this->per_page);
		$this->first_record = (($this->page - 1) * $this->per_page) + 1;
	}

	function __destruct(){
		$this->page = null;
		$this->per_page = null;
		$this->total_records = null;
		$this->total_pages = null;
		$this->first_record = null;
	}

	function jsonSerialize() : mixed {
		$json = [
			'page' => $this->page,
			'per_page' => $this->per_page,
			'total_records' => $this->total_records,
			'total_pages' => $this->total_pages,
			'first_record' => $this->first_record
		];
		return $json;
	}

}

?>