
const load_queue = null;

const load_queuer = function(){
	const _ = this;
	_.loaded = false;
	_.queue = [];
	_.load = function(){
		_.queue.forEach((callback) => {
			callback();
		});
		_.loaded = true;
		_.queue = [];
	};
	_.add = function(callback){
		if(typeof(callback) === 'function'){
			if(_.loaded){
				callback();
			} else {
				_.queue.push(callback);
			}
		}
	};
};

(() => {
	load_queue = new load_queuer();
	window.addEventListener('load', load_queue.load);
})();
