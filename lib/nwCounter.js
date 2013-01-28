var nwCounter = Class.create({
	initialize : function (steps, action, finish) {
		this.steps = steps;
		this.done = 0;
		if (action) this.action = action;
		if (finish) this.finish = finish;

		this.step(true);
	},

	step : function (stop) {
		if (!stop)
			this.done++;

		if (this.action)
			this.action(this.done, this.steps);

		if (this.done>=this.steps)
			this.finish(this.done, this.steps);

	},

	clear : function () {
		this.done=0;
		this.step(true);
	}
});