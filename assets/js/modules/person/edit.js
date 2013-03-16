$(document).ready(function () {
	$('input[name=person_name]').typeahead({
		source: function (query, process) {

			this.render = this.options.render || this.render; //added
			this.select = this.options.select || this.select; //added

			return $.getJSON('/call/person/json_list_persons/', { query: query }, function (data) {
				return process(data);
			});
		},
		updater: function (person) {
			$('input[name=person_id]').val(person.id);
			return person.name;
		},
		matcher: function (item) {
			return ~item.name.toLowerCase().indexOf(this.query.toLowerCase())
		},
		sorter: function (items) {
			var beginswith = []
					, caseSensitive = []
					, caseInsensitive = []
					, item;

			while (item = items.shift()) {
				if (!item.name.toLowerCase().indexOf(this.query.toLowerCase())) beginswith.push(item);
				else if (~item.name.indexOf(this.query)) caseSensitive.push(item);
				else caseInsensitive.push(item)
			}

			return beginswith.concat(caseSensitive, caseInsensitive)
		},
		render: function (items) {
			var that = this;

			items = $(items).map(function (i, item) {
				i = $(that.options.item).attr('data-id', item.id).attr('data-value', item.name);
				i.find('a').html(that.highlighter(item.name));
				return i[0]
			});

			items.first().addClass('active');
			this.$menu.html(items);
			return this
		},
		select: function () {
			var id = this.$menu.find('.active').attr('data-id');
			var value = this.$menu.find('.active').attr('data-value');

			this.$element
					.val(this.updater({id:id, name:value}))
					.change();
			return this.hide()
		}
	});
})
;