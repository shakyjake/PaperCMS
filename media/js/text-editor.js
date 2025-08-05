
function wysiwyg_setup(selector){
	
	let nodes = null;
	
	if(typeof(selector) === 'string'){
		nodes = document.querySelectorAll(selector);
	} else if(typeof(selector) === 'object'){
		if('length' in selector){
			nodes = selector;
		} else {
			nodes = [selector];
		}
	} else if(typeof(selector) === 'undefined'){
		nodes = document.querySelectorAll('.wysiwyg');
	}
	
	let i = 0;
	let j = nodes.length;
	while(i < j){
		if(nodes[i]){
			CKEDITOR.inline(nodes[i]);
		}
		i += 1;
	}
	
}

(() => {
	load_queue.add(wysiwyg_setup);
})();
