
function default_block_content(type, uuid){
	
	type = typeof(type) === 'string' ? type : '';
	type = type.toLowerCase();
	type = type.replace(/[^a-z\d\-\_]+/g, '');
	
	let editor = '<button class="block__button block__button--remove"><i class="fa-solid fa-trash-can"></i></button><button class="block__button block__button--move ui-handle ui-sortable-handle"><i class="fa-solid fa-arrows-up-down"></i></button>';
	let innards = '';
	
	switch(type){
		case 'text':
			innards = '<div contenteditable="true">Lorem ipsum dolor sit amet, consectetuer adispiscing elit. Etiam a tristique nullam orci et neque</div>';
			break;
		case 'rich-text':
			innards = '<div class="wysiwyg" contenteditable="true"><p>Lorem ipsum dolor sit amet, consectetuer adispiscing elit. Etiam a tristique nullam orci et neque</p></div>';
			break;
		case 'image':
			innards = '<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MCA1MCI+PHBhdGggZmlsbD0iIzgwODA4MCIgZD0iTTQ5LDExLjV2MjdBNC41MSw0LjUxLDAsMCwxLDQ0LjUsNDNINS41QTQuNTEsNC41MSwwLDAsMSwxLDM4LjV2LTI3QTQuNTEsNC41MSwwLDAsMSw1LjUsN2gzOUE0LjUxLDQuNTEsMCwwLDEsNDksMTEuNVpNNDQuNSwzNy45NFYxMi4wNmEuNTYuNTYsMCwwLDAtLjU2LS41Nkg2LjA2YS41Ni41NiwwLDAsMC0uNTYuNTZWMzcuOTRhLjU2LjU2LDAsMCwwLC41Ni41Nkg0My45NEEuNTYuNTYsMCwwLDAsNDQuNSwzNy45NFpNMTYuNzUsMTlBMy43NSwzLjc1LDAsMSwxLDEzLDE1LjI1LDMuNzUsMy43NSwwLDAsMSwxNi43NSwxOVpNMTAsMjkuNWwzLjcxLTMuNzFhMS4xMywxLjEzLDAsMCwxLDEuNTgsMEwxOSwyOS41LDMwLjIxLDE4LjI5YTEuMTMsMS4xMywwLDAsMSwxLjU4LDBMNDAsMjYuNVYzNEgxMFoiLz48L3N2Zz4=" width="640" height="480" alt="Click to edit image" loading="lazy" class="picsum" />';
			break;
		case 'grid':
			editor = '<div class="form__field"><label class="form__label">Min. width:</label><span class="form__unit form__unit--after" data-unit="em"><input class="unit__input" type="number" name="MinWidth" id="MinWidth_' + uuid + '" step="0.0001" value="15" /></span></div><div class="form__field"><label class="form__label">Max. width:</label><span class="form__unit form__unit--after" data-unit="em"><input class="unit__input" type="number" name="MaxWidth" id="MaxWidth_' + uuid + '" step="0.0001" /></span></div>' + editor;
			innards = '<div class="grid"><div class="grid__item ui-droppable ui-sortable"></div><div class="grid__item ui-droppable ui-sortable"></div><div class="grid__item ui-droppable ui-sortable"></div></div>';
			break;
		default:
			innards = 'Unknown content type "' + type + '"';
			break;
	}
	const toolbar = '<div class="block__toolbar">' + editor + '</div>';
	const content = '<div class="block__content">' + innards + '</div>';
	return toolbar + content;
}

function page_builder_setup(){
	(($) => {
		let uuid = self.crypto.randomUUID();
		$('.ui-droppable').droppable({
			accept: '.ui-draggable',
			drop: function(event, ui){
				event.stopImmediatePropagation();
				const type = ui.draggable.attr('data-block');
				const width = ui.draggable.siblings()[0].offsetWidth;
				ui.draggable
					.attr('class', 'block block__row block__row--' + type)
					.attr('id', 'block-' + uuid)
					.removeAttr('data-block')
					.html(default_block_content(type, uuid))
					.animate({width : width + 'px'}, 100, 'linear', () => {
						setTimeout(() => {
							(($) => {
								$('.block[style]').removeAttr('style');
							})(jQuery);
						}, 500);
					});
			}
		});
		$('.ui-draggable').draggable({
			connectToSortable: '.ui-sortable',
			revert: 'invalid',
			containment: 'document',
			helper: 'clone',
			cursor: 'move'
		});
		$('.ui-sortable').sortable({
			connectWith: '.ui-sortable',
			handle: '.ui-handle',
			revert: true
		});
		$('#main').on('click tap', '.block__button--remove', function(event){
			event.stopImmediatePropagation();
			$(this).parent().parent().slideUp(200, function(){
				$(this).remove();
			});
		});
	})(jQuery);
}

(() => {
	load_queue.add(page_builder_setup);
})();