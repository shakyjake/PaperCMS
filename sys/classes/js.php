<?php

class JS {

	public $enqueued;
	public $inline;
	public $url;
	public $content;
	public $priority;
	public $file_hash;

	function enqueue(?Page $page = null){
		
		global $current_page;
		
		/* we're not inlining an external resource so you can cut that out right now */
		if($this->inline && !is_path($this->url)){
			$this->inline = false;
		}
		
		if(!$this->enqueued){
			if($this->inline && !has_value($this->content)){
				$this->content = file_get_contents(map_path($this->url));
			}
			if($this->inline && has_value($this->content) && !has_value($this->file_hash)){
				$this->file_hash = sprintf('sha384-%1$s', base64_encode(hash('sha384', $this->content || '')));
			}
			if(empty($page)){
				$current_page->js[] = $this;
			} else {
				$page->js[] = $this;
			}
		}
		
	}

	function __construct(string $url = null, int $priority = 100){
		$this->enqueued = false;
		$this->inline = false;
		$this->url = $url;
		$this->content = null;
		$this->priority = $priority;
		$this->file_hash = null;
	}

	function __destruct(){
		$this->enqueued = null;
		$this->inline = null;
		$this->url = null;
		$this->content = null;
		$this->priority = null;
		$this->file_hash = null;
	}

}

function js_sort(JS $a, JS $b){
    if($a->priority === $b->priority){
        return 0;
    }
    return ($a->priority < $b->priority) ? -1 : 1;
}

function js_enqueue(?string $url = null, ?bool $inline = false, ?int $priority = 100){
	
	$js = new JS($url, $priority);
	$js->inline = $inline;
	$js->enqueue();
	
	return $js;
	
}

function js_html(){
	
	global $current_page;
	
	$js_output = new RapidString();
	
	usort($current_page->js, 'js_sort');
	
	foreach($current_page->js as $js_file){
		if($js_file->inline){
			$js_output->add('<script>');
			$js_output->add($js_file->content);
			$js_output->add('</script>');
		} else {
			$js_output->add('<script defer src="');
			$js_output->add(html($js_file->url));
			$js_output->add('"');
			if(has_value($js_file->file_hash)){
				$js_output->add(' integrity="');
				$js_output->add($js_file->file_hash);
				$js_output->add('"');
			}
			if(!is_path($js_file->url)){
				$js_output->add(' crossorigin="anonymous"');
			}
			$js_output->add('></script>');
		}
	}
	
	return $js_output->dump();
	
	$js_output = null;
	
}

?>