var nwLogDefaultOption = {
	itemClass: 'logItemClass'
};

var nwLogCount=0;

var nwLog = new Class.create({
	initialize: function (container, options) {
		this.container = container;
		this.index = nwLogCount++;

		this.lastID = 0;

		this.options = Object.clone(nwLogDefaultOption);
		Object.extend(this.options, options || {});

	},

	addItem: function (data, id, title, force) {
		if ((id <= this.lastID) && !force)
			return false;

		if (!force)
			this.lastID = id;

		var item = document.createElement('div');

		item.setAttribute('class', this.options.itemClass);
		item.setAttribute('id', 'LIST-ITEM-' + this.index + '-' + this.lastID);

		if (title)
			item.title = title;

		item.update(data);

		this.container.appendChild(item);

		this.container.scrollTop = item.offsetTop;
	}
});