
function ajax_form_submit(event){

	event.stopImmediatePropagation();

	let form = event.target;
	while(form.nodeName.toLowerCase() !== 'form'){
		form = form.parentNode;
		if(form.nodeName.toLowerCase() === 'body'){
			return;
		}
	}
	event.preventDefault();

	const disable_buttons = form.querySelectorAll('button');
	disable_buttons.forEach((button) => {
		button.dataset.prevState = button.disabled ? '0' : '1';
		button.disabled = true;
		button.classList.add('form__button--loading');
	});

	const form_data = new FormData(form);
	const url = form.getAttribute('action');
	const method = form.getAttribute('method');

	fetch(url, {
		method: method,
		body: form_data
	})
	.then(response => response.json())
	.then(response => {
		const error_keys = Object.keys(response.messages);
		if(error_keys.length){
			error_keys.forEach((key) => {
				const error = response.messages[key];
				const input = form.querySelector(`[name="${key}"]`);
				let error_message = form.querySelector(`#error-${key}`);
				if(error_message){
					while(error_message.childNodes.length){
						error_message.removeChild(error_message.firstChild);
					}
				} else {
					error_message = document.createElement('div');
					error_message.id = `error-${key}`;
					error_message.className = 'form__message';
					if('type' in error){
						error_message.classList.add('form__message--' + error.type);
					}
					if(input){
						input.parentNode.insertBefore(error_message, input.nextSibling);
					} else {
						form.insertBefore(error_message, form.firstChild);
					}
				}
				error.messages.forEach((message, index) => {
					if(index){
						error_message.append(document.createElement('br'));
					}
					error_message.append(message);
				});
				if(input){
					input.classList.add('error');
					input.addEventListener('input', (event) => {
						let input = event.target;
						const error = document.querySelector(`#error-${input.name}`);
						if(error){
							error.remove();
						}
					});
				}
			});
		}
		if(response.redirect){
			if(response.redirect.length){
				window.location.href = response.redirect;
			}
		}
	})
	.finally(() => {
		const enable_buttons = form.querySelectorAll('button');
		enable_buttons.forEach((button) => {
			button.disabled = button.dataset.prevState === '1' ? false : true;
			button.classList.remove('form__button--loading');
		});
	});

}

function bind_ajax_forms__single(root){
	
	const forms = root.querySelectorAll('form[action^="/ajax/"]');

	forms.forEach((form) => {
		form.addEventListener('submit', ajax_form_submit);
	});

}

function bind_ajax_forms(root){

	if(typeof(root) === 'undefined'){

		root = document;

	} else if(typeof(root) === 'string'){

		root = document.querySelectorAll(selector);

	}

	if(root){

		if('length' in root){

			root.forEach((element) => {

				bind_ajax_forms__single(element);
	
			});

		} else {
			
			bind_ajax_forms__single(root);
			
		}

	}

}

(() => {

	bind_ajax_forms();

})();

