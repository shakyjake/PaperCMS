<?php

class CSS {

	public $enqueued;
	public $inline;
	public $url;
	public $media;
	public $content;
	public $priority;
	public $file_hash;
	public $csp;

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
				$current_page->css[] = $this;
			} else {
				$page->css[] = $this;
			}
		}
		
	}

	function __construct(string $url = null, string $media = 'all', int $priority = 100){
		$this->enqueued = false;
		$this->inline = false;
		$this->url = $url;
		$this->media = $media;
		$this->content = null;
		$this->priority = $priority;
		$this->file_hash = null;
		$this->csp = null;
	}

	function __destruct(){
		$this->enqueued = null;
		$this->inline = null;
		$this->url = null;
		$this->media = null;
		$this->content = null;
		$this->priority = null;
		$this->file_hash = null;
		$this->csp = null;
	}

}

function css_sort(CSS $a, CSS $b){
	if($a->inline && !$b->inline){
		return -1;
	}
	if(!$a->inline && $b->inline){
		return 1;
	}
    if($a->priority === $b->priority){
        return 0;
    }
    return ($a->priority < $b->priority) ? -1 : 1;
}

function css_enqueue(?string $url = null, ?string $media = 'all', ?bool $inline = false, ?int $priority = 100){
	
	$css = new CSS($url, $media, $priority);
	$css->inline = $inline;
	$css->enqueue();
	
	return $css;
	
}

function css_html(){
	
	global $current_page;
	
	$css_output_inline = new RapidString();
	$css_output_link = new RapidString();
	$css_output_noscript = new RapidString();
	
	usort($current_page->css, 'css_sort');
	
	foreach($current_page->css as $css_file){
		if($css_file->inline){
			$css_output_inline->add('<style type="text/css">');
			if($css_file->media !== 'all'){
				$css_output_inline->add('@media (');
				$css_output_inline->add($css_file->media);
				$css_output_inline->add('){');
			}
			$css_output_inline->add(css_minify($css_file->content));
			if($css_file->media !== 'all'){
				$css_output_inline->add('}');
			}
			$css_output_inline->add('</style>');
		} else {
			$css_output_link->add('<link rel="stylesheet" type="text/css" media="print" href="');
			$css_output_link->add(html($css_file->url));
			$css_output_link->add('" onload="this.media=\'');
			$css_output_link->add(html($css_file->media));
			$css_output_link->add('\'"');
			if(has_value($css_file->file_hash)){
				$css_output_link->add(' integrity="');
				$css_output_link->add($css_file->file_hash);
				$css_output_link->add('"');
			}
			if(!is_path($css_file->url)){
				$css_output_link->add(' crossorigin="anonymous"');
			}
			$css_output_link->add(' />');
			$css_output_noscript->add('<link rel="stylesheet" type="text/css" href="');
			$css_output_noscript->add(html($css_file->url));
			if($css_file->media !== 'all'){
				$css_output_noscript->add('" media="');
				$css_output_noscript->add(html($css_file->media));
			}
			$css_output_noscript->add('"');
			if(has_value($css_file->file_hash)){
				$css_output_noscript->add(' integrity="');
				$css_output_noscript->add($css_file->file_hash);
				$css_output_noscript->add('"');
			}
			if(!is_path($css_file->url)){
				$css_output_noscript->add(' crossorigin="anonymous"');
			}
			$css_output_noscript->add(' />');
		}
	}
	
	return sprintf(
		'%1$s%2$s<noscript>%3$s</noscript>',
		$css_output_inline->dump(),
		$css_output_link->dump(),
		$css_output_noscript->dump()
	);
	
}

function css_minify($css){
	$css = regex_replace('[\r\n\t]+', ' ', $css);
	$css = regex_replace('\s{2,}', ' ', $css);
	$css = regex_replace('\/\*[^*]*\*+\/', '', $css);
	$css = regex_replace('([a-zA-Z\-]+)\:\s+', '$1:', $css);
	$css = str_replace('; ', ';', $css);
	$css = regex_replace('([\da-z]), ', '$1,', $css);
	$css = regex_replace('^\s+', '', $css);
	$css = regex_replace('\s+$', '', $css);
	$css = regex_replace('\{\s+', '{', $css);
	$css = regex_replace('\s+\{', '{', $css);
	$css = regex_replace('\}\s+', '}', $css);
	$css = regex_replace('\s+\}', '}', $css);
	$css = regex_replace('\;\}', '}', $css);
	$css = regex_replace('\s+\>\s+', '>', $css);
	return $css;
}

?>