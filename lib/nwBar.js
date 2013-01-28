var nwBarDefaultOption = {
	height: 12,
	width: 200,
	bg: '/img/bar/white.png',
	barImg: '/img/bar/black.png',
	fps: 50,
	onFinish: function () {},
	onTick: function () {}
};

var nwBarCount=0;

var nwBar = Class.create({
	initialize: function (container, value, max, options) {
		this.container=container;
		this.max=max;
		this.value=value;

		this.start_timer=0;
		this.end_timer=0;
		this.start_value=0;
		this.end_value=0;

		this.options = Object.clone(nwBarDefaultOption);
		Object.extend(this.options, options || {});

		nwBarCount++;

		this.cid="nwBarContainer"+nwBarCount;
		this.bid="nwBar"+nwBarCount;

		this.draw();
	},

	currentPos: function() {
		var pos=this.options.width*this.value/this.max;

		return 0-this.options.width+pos;
	},

	currentPerc: function () {
		return Math.round(this.value/this.max*100);
	},

	draw: function () {
		var text='<div id="'+this.cid+'" style="';

			text+='width:'+this.options.width+'px;';
			text+='height:'+this.options.height+'px;';
			text+='display:block;';
			text+='overflow:hidden;';
			text+='margin:0px;';
			text+='padding:0px;';
			text+='background-image:url('+this.options.bg+');';

			text+='">';

			text+='<div id="'+this.bid+'" style="';

			text+='width:'+this.options.width+'px;';
			text+='height:'+this.options.height+'px;';
			text+='display:block;';
			text+='margin:0px;';
			text+='background-image:url('+this.options.barImg+');';
			text+='position:relative;';
			text+='left:'+this.currentPos()+'px;top:0px';

			text+='">&nbsp;</div></div>';

		this.container.update(text);
	},

	setPos: function () {
		P(this.bid).style.left=this.currentPos()+"px";
	},

	setImage: function (newImg) {
		P(this.bid).style.backgroundImage="url("+newImg+")";
	},

	set: function (value, relative) {
		var old=this.value;

		if (relative) {
			this.value+=value;
		} else {
			this.value=value;
		}

		if (this.value>this.max)
			this.value=this.max;

		if (this.value<0)
			this.value=0;

		this.setPos();

		if (Math.round(old)!=Math.round(this.value))
			this.options.onTick(this.value, this.currentPerc());
	},

	move: function (value, time) {
		this.stop();

		this.start_time=nanotime();
		this.end_time=nanotime()+time*1000;

		this.start_value=this.value;
		this.end_value=value;

		this.process();
	},

	stop: function () {
		clearTimeout(this.timer);
	},

    process: function () {
        if (!P(this.bid)) {
            return 0;
        }

        var time = nanotime();

        if (time < this.end_time) {

            var praejo = time - this.start_time;
            var viso = this.end_time - this.start_time;
            var dalis = praejo / viso;
            viso = this.end_value - this.start_value;
            dalis = dalis * viso + this.start_value;

            this.set(dalis);

            this.timer = setTimeout(function() { this.process(); }.bind(this), this.time);
        } else {
            this.set(this.end_value);
            this.options.onFinish();
        }
    }
});