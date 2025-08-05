
function admin_detail_get(url){

	const detail = document.querySelector('.admin__detail');
	if(detail){
		while(detail.childNodes.length){
			detail.firstChild.remove();
		}
	}

	detail.classList.add('admin__detail--loading');
	
	fetch(url)
		.then(response => response.json())
		.then(response => {
			if(response){

				let data = response.data;
				if('length' in response.data){
					data = response.data[0];
				}

				const frag = document.createDocumentFragment();

				const title = document.createElement('div');
				title.className = 'detail__title';
				title.append(data.name);
				frag.append(title);

				const form = document.createElement('div');
				form.className = 'detail__form';
				frag.append(form);
				form.innerHTML = data.form;

				const buttons = document.createElement('div');
				buttons.className = 'detail__buttons';
				frag.append(buttons);

				const button_save = document.createElement('button');
				button_save.className = 'form__button form__button--save';
				button_save.type = 'submit';
				button_save.append('Save');
				buttons.append(button_save);

				const button_delete = document.createElement('button');
				button_delete.className = 'form__button form__button--delete';
				button_delete.type = 'button';
				button_delete.append('Delete');
				buttons.append(button_delete);

				detail.append(frag);

			}

		})
		.finally(() => {
			detail.classList.remove('admin__detail--loading');
		});
}

function admin_list_click(event){
	event.stopImmediatePropagation();
	event.preventDefault();
	const active = document.querySelector('.list__item--active');
	if(active){
		active.classList.remove('list__item--active');
	}
	let target = event.target;
	while(!target.classList.contains('list__item')){
		target = target.parentNode;
		if(target.nodeName.toLowerCase() === 'body'){
			return;
		}
	}
	target.classList.add('list__item--active');
	if(target.dataset.url){
		admin_detail_get(target.dataset.url);
	}
}

function admin_list_pagination(event){
	event.stopImmediatePropagation();
	event.preventDefault();
	let target = event.target;
	while(!target.classList.contains('form__button--pagination')){
		target = target.parentNode;
		if(target.nodeName.toLowerCase() === 'body'){
			return;
		}
	}
	const pagination_page = parseInt(target.dataset.paginationPage);
	admin_list_populate(pagination_page);
}

function filter_settings_set(fd){

	const data = {};

	fd.forEach((value, key) => {
		data[key] = value;
	});

	window.localStorage.setItem('filter_settings', JSON.stringify(data));

}

function filter_settings_get(){

	const data = window.localStorage.getItem('filter_settings');
	if(data){
		return JSON.parse(data);
	}

	return {};

}

function admin_list_populate(pagination_page){

	pagination_page = typeof(pagination_page) === 'number' ? pagination_page : 1;

	const list = document.querySelector('.admin__list');

	if(list){

		while(list.childNodes.length){
			list.firstChild.remove();
		}

		list.classList.add('admin__list--loading');

		if(list.dataset.src){
			if(list.dataset.src.length){

				const fd = new FormData();

				fd.append('pagination_page', pagination_page);
				
				const refine = list.parentNode.querySelector('.admin__refine');
				if(refine){
					const search = refine.querySelector('[name="search"]');
					if(search){
						fd.append('search', search.value);
					}
					const filters = refine.querySelectorAll('.filter__options .checkbox__list');
					filters.forEach(filter => {
						const filter_options = filter.querySelectorAll('.checkbox__input');
						console.log(filter, filter_options);
						if(filter_options.length){
							console.log('WELL?!');
							let filter_name = filter.dataset.name;
							let filter_values = [];
							let none_checked = true;
							filter_options.forEach(option => {
								if(option.checked){
									filter_values.push(option.value);
									none_checked = false;
								}
							});
							if(none_checked){
								const stored_data = filter_settings_get();
								console.log(stored_data);
								if(filter_name in stored_data){
									if(stored_data[filter_name].length){
										filter_values = stored_data[filter_name].split(',');
										console.log(filter_values);
										none_checked = false;
									}
								}
							}
							if(none_checked){
								filter_options.forEach(option => {
									filter_values.push(option.value);
								});
							}
							filter_options.forEach(option => {
								if(filter_values.includes(option.value)){
									option.checked = true;
								}
							});
							fd.append(filter_name, filter_values.join(','));
						}
					});
				}

				filter_settings_set(fd);

				const data = new URLSearchParams(fd);

				fetch(list.dataset.src, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: data
				})
				.then(response => response.json())
				.then(response => {
					if(response){
						if(response.data.length){
							const data = response.data;
							const frag = document.createDocumentFragment();
							data.forEach((item, index) => {
								const list_item = document.createElement('div');
								list_item.className = 'list__item';
								if(!index){
									list_item.classList.add('list__item--active');
									admin_detail_get(item.detail_url);
								}
								list_item.dataset.url = item.detail_url;
								list_item.dataset.id = item.id;

								const title = document.createElement('h3');
								title.className = 'list__title';
								title.append(item.name);
								list_item.append(title);

								const type = document.createElement('div');
								type.className = 'list__type';
								if(item.type){
									if(item.type.length){
										type.append(item.type);
									}
								}
								list_item.append(type);

								const image = document.createElement('div');
								image.className = 'list__image';
								if(item.img){
									if(item.img.length){
										const img = document.createElement('img');
										img.className = 'list__img';
										img.alt = item.name;
										img.width = 80;
										img.height = 80;
										img.loading = 'lazy';
										img.src = item.img;
										image.append(img);
									}
								}
								list_item.append(image);

								const date = document.createElement('div');
								date.className = 'list__date';
								if(item.date){
									if(item.date.length){
										date.append(item.date);
									}
								}
								list_item.append(date);

								const author = document.createElement('div');
								author.className = 'list__author';
								if(item.author){
									if(item.author.length){
										author.append(item.author);
									}
								}
								list_item.append(author);

								frag.append(list_item);
							});
							list.append(frag);

							const pagination_holder = list.parentNode.querySelector('.admin__pagination');
							if(pagination_holder){

								while(pagination_holder.childNodes.length){
									pagination_holder.firstChild.remove();
								}

								let field = null;
								let button = null;
								
								button = document.createElement('button');
								button.className = 'form__button form__button--pagination pagination__first';
								button.dataset.paginationPage = 1;
								button.append('First');
								if(response.pagination.page === 1){
									button.disabled = true;
								}
								pagination_holder.append(button);
								
								button = document.createElement('button');
								button.className = 'form__button form__button--pagination pagination__left';
								button.dataset.paginationPage = response.pagination.page - 1;
								button.append('Previous');
								if(response.pagination.page === 1){
									button.disabled = true;
								}
								pagination_holder.append(button);
								
								button = document.createElement('button');
								button.className = 'form__button form__button--pagination pagination__right';
								button.dataset.paginationPage = response.pagination.page + 1;
								button.append('Next');
								if(response.pagination.page >= response.pagination.total_pages){
									button.disabled = true;
								}
								pagination_holder.append(button);
								
								button = document.createElement('button');
								button.className = 'form__button form__button--pagination pagination__last';
								button.dataset.paginationPage = response.pagination.total_pages;
								button.append('Last');
								if(response.pagination.page >= response.pagination.total_pages){
									button.disabled = true;
								}
								pagination_holder.append(button);

								let summary = document.createElement('div');
								summary.className = 'pagination__summary';
								summary.append('Showing ' + response.pagination.first_record + '-' + Math.min(response.pagination.first_record + response.pagination.per_page - 1, response.pagination.total_records) + ' of ' + response.pagination.total_records);

								pagination_holder.append(summary);

							}

						}
					}
				})
				.finally(() => {
					list.classList.remove('admin__list--loading');
				});
			}
		}
	}
}

function admin_list_search(event){
	if(event.target.nodeName.toLowerCase() === 'input'){
		if(event.target.type === 'checkbox'){
			return admin_list_populate();
		}
		if(event.target.type === 'radio'){
			return admin_list_populate();
		}
	}
	window.search_timeout = typeof(window.search_timeout) === 'undefined' ? null : window.search_timeout;
	if(window.search_timeout){
		clearTimeout(window.search_timeout);
	}
	window.search_timeout = setTimeout(admin_list_populate, 500);
}

function sidebar_toggle(event){
	event.stopImmediatePropagation();
	event.preventDefault();
	const sidebar = document.querySelector('.sidebar');
	if(sidebar){
		if(sidebar.getAttribute('aria-expanded') === 'true'){
			sidebar.setAttribute('aria-expanded', 'false');
		} else {
			sidebar.setAttribute('aria-expanded', 'true');
		}
	}
}

function checkbox_list_populate_ajax(cbl){

	const stored_data = filter_settings_get();

	if(cbl.dataset.ajaxSource){
		if(cbl.dataset.ajaxSource.length){
			if(cbl.dataset.ajaxSource.substr(0, '/ajax/'.length) === '/ajax/'){
				const class_modifier = cbl.dataset.class;
				let selected_value = cbl.dataset.value.split(',');
				const field_name = cbl.dataset.name;
				if(field_name in stored_data){
					if(stored_data[field_name].length){
						selected_value = stored_data[field_name].split(',');
					}
				}
				fetch(cbl.dataset.ajaxSource)
					.then(response => response.json())
					.then(response => {
						if(response){
							if(response.data.length){
								const data = response.data;
								const frag = document.createDocumentFragment();
								data.forEach(item => {

									const holder = document.createElement('div');
									holder.className = 'checkbox__item';

									const input = document.createElement('input');
									input.type = 'checkbox';
									input.className = 'form__input checkbox__input';
									if(class_modifier.length){
										input.classList.add('form__input--' + class_modifier);
									}
									input.name = field_name;
									input.id = field_name + '_' + item.id;
									input.value = item.id;
									input.checked = selected_value.includes(item.id.toString());
									holder.append(input);

									const label = document.createElement('label');
									label.className = 'form__label checkbox__label';
									if(class_modifier.length){
										label.classList.add('form__label--' + class_modifier);
									}
									label.htmlFor = field_name + '_' + item.id;
									label.append(item.name);
									holder.append(label);

									frag.append(holder);
								});
								cbl.append(frag);
							}
						}
					});
			}
		}
	}
}

(() => {

	const stored_data = filter_settings_get();

	const button = document.querySelector('.form__button--sidebar');
	if(button){
		button.addEventListener('click', sidebar_toggle);
		button.addEventListener('tap', sidebar_toggle);
	}

	const cbls = document.querySelectorAll('.checkbox__list[data-ajax-source]:not(.checkbox__list--radio)');
	cbls.forEach(checkbox_list_populate_ajax);

	const list = document.querySelector('.admin__list');
	if(list){

		list.addEventListener('click', admin_list_click);
		list.addEventListener('tap', admin_list_click);

		const refine = list.parentNode.querySelector('.admin__refine');
		if(refine){
			refine.addEventListener('input', admin_list_search);
			const search = refine.querySelector('[name="search"]');
			if(search){
				if('search' in stored_data){
					search.value = stored_data.search;
				}
			}
		}

		const pagination = list.parentNode.querySelector('.admin__pagination');
		if(pagination){
			pagination.addEventListener('click', admin_list_pagination);
			pagination.addEventListener('tap', admin_list_pagination);
		}

		admin_list_populate();

	}

})();