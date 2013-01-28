var nwListDefaultOption = {
	adr: '/ajax/list',
	classItem: 'nwlist-item',
	classDelete: 'nwlist-delete',
    formatValue: function (value) {
        return value;
    }
};

var nwListIndex=0;

var nwList = Class.create({
	initialize: function (container, values, options) {
		var self = this;
		this.container = container;
		this.values = [];

		this.options = Object.clone(nwListDefaultOption);
		Object.extend(this.options, options || {});

		this.index = nwListIndex++;

		values.each(function (s) {
			self.itemAdd(s);
		});
	},

	itemName: function (key) {
		return 'list-'+this.index+'-'+key;
	},

	itemAdd: function (item) {
		var self = this;

		var li = document.createElement('li');
		var del = document.createElement('img');

		li.setAttribute('class', this.options.classItem);
		li.setAttribute('id', this.itemName(item.key));
		li.update(this.options.formatValue(item.value));
		del.setAttribute('class', this.options.classDelete);

		del.observe('click', function (ob) {
			self.itemRemove(item.key);
		});

		this.container.appendChild(li);

		li.appendChild(del);

		this.values[item.key] = item.value;
	},

	itemCreate: function (name, params) {
		var self = this;

		if (params) {
			Object.extend(params, { 'name' : name, 'type' : 'create' });
		} else {
			var params = { 'name' : name, 'type' : 'create' };
		}

		new Ajax.Request(this.options.adr, {
			parameters: params,
			onComplete: function (text) {
				if (!text.responseText.isJSON()) {
					Dialogs.alert(text.responseText);
					return 0;
				}

				var DATA=text.responseText.evalJSON();

				self.itemAdd({ 'key' : DATA.key, 'value' : DATA.value});
			}
		});
	},

	itemRemove: function (key, params) {
		if (!this.values[key]) {
			Dialogs.alert('Tokio įrašo nėra');
			return 0;
		}

		var self = this;

		if (params) {
			Object.extend(params, { 'key' : key, 'type' : 'remove' });
		} else {
			var params = { 'key' : key, 'type' : 'remove' };
		}

		new Ajax.Request(this.options.adr, {
			parameters: params,
			onComplete: function (text) {
				if (text.responseText == '1') {
					P(self.itemName(key)).remove();
					self.values[key] = null;
				} else {
					Dialogs.alert(text.responseText);
				}
			}
		});
	}
});