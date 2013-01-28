var atackGUI = Class.create({
	initialize: function (data) {
		var self = this;

		this.aid = data.aid;
		this.uid = data.uid;
		this.width = data.width;
		this.height = data.height;
		this.x = data.x;
		this.y = data.y;
		this.o = [];
		this.key = 0;

		this.pushed = null;
		this.rolled = null;

		this.fighters = [];
		this.objects = [];

		this.myindex;

		this.canvasBG = document.createElement('canvas');
		this.canvasMiddle = document.createElement('canvas');

		this.canvasBG.setAttribute('width', this.x);
		this.canvasBG.setAttribute('height', this.y);
		this.canvasBG.setAttribute('id', 'BG');
		this.canvasMiddle.setAttribute('width', this.x);
		this.canvasMiddle.setAttribute('height', this.y);
		this.canvasMiddle.setAttribute('id', 'MIDDLE');

		this.canvas = P('A_MAP');

		this.A_BAR=new nwBar(P('a-timer-bar'), 0, 30,  {
			onFinish : function () {
				setTimeout(function () {self.processAtack(0);}, 1500);
			},
			onTick : function (value) {
				P('a-timer-left').update(Math.round(value));
			}
		});

		this.log = new nwLog(P('a-log'));

		this.start();

		this.img = {
			'bg' : new Image(),
			'rjalL' : new Image(),
			'manL' : new Image(),
			'rjalR' : new Image(),
			'manR' : new Image()
		}

		this.img.bg.src = '/img/battle_ground.png';
		this.img.rjalL.src = '/img/battle_rjal_1_L.png';
		this.img.manL.src = '/img/battle_man_1_L.png';
		this.img.rjalR.src = '/img/battle_rjal_1_R.png';
		this.img.manR.src = '/img/battle_man_1_R.png';

		var left=5;

		if (this.img.bg.width>0) left--;
		if (this.img.rjalL.width>0) left--;
		if (this.img.manL.width>0) left--;
		if (this.img.rjalR.width>0) left--;
		if (this.img.manR.width>0) left--;

		if (left==0)
			this.finish(this);

		this.counter = new nwCounter(
			left,
			function () {},
			function () {
				if (left>0)
					self.finish(self);
			}
		);

		this.img.bg.onload = function () {
			self.counter.step();
		}
		this.img.rjalL.onload = function () {
			self.counter.step();
		}
		this.img.manL.onload = function () {
			self.counter.step();
		}
		this.img.rjalR.onload = function () {
			self.counter.step();
		}
		this.img.manR.onload = function () {
			self.counter.step();
		}

		Event.observe(P('a-map-box'), 'mousemove', function (e) { self.mouseMove(e); });
		Event.observe(P('a-map-box'), 'mouseout',  function () { self.mouseOut(); });
		Event.observe(P('a-map-box'), 'mouseup',   function () { self.mouseUp(); });
		Event.observe(P('a-map-box'), 'mousedown', function () { self.click();} );

		this.paspausta = false;
		this.draged = false;
		this.start = null;
		this.moved = null;
		this.pushed = null;

		this.mx = null;
		this.my = null;
	},

	mouseMove: function (e) {
		this.mx=e.pointerX();
		this.my=e.pointerY();

		if (this.paspausta && !this.draged && (Math.abs(this.start.x-this.mx)>4 || Math.abs(this.start.y-this.my)>4)) {
			this.draged=true;
		}

		if (this.draged) {
			P('a-map-box').scrollLeft = this.start.sx - this.mx + this.start.x;
			P('a-map-box').scrollTop = this.start.sy -  this.my + this.start.y;
			return true;
		}

		var a=P('a-map-box').cumulativeScrollOffset();
		var b=P('a-map-box').cumulativeOffset();

		var mx2=this.mx-b[0]+a[0];
		var my2=this.my-b[1]+a[1];

		var data=this.atackCountCoo(mx2, my2);

		if (this.moved==null || !(this.moved.x == data.x && this.moved.y==data.y)) {
			this.moved=data;

			this.atackInfo(data.x,data.y)

			this.draw();
		}
	},

	mouseOut: function () {
		this.draged=false;
		this.paspausta=false;

		this.moved=null;

		if (this.pushed) {
			this.atackInfo(this.pushed.x,this.pushed.y);
		} else {
			this.atackInfo();
		}

		this.draw();
	},

	mouseUp: function () {
		if (!this.draged) {
			this.pushed=this.moved;
			this.draw();
		}
		this.draged=false;
		this.paspausta=false;
		this.start=null;
	},

	click : function () {
		this.paspausta=true;
		this.start={
			'x' : this.mx,
			'y' : this.my,
			'sy' : P('a-map-box').scrollTop,
			'sx' : P('a-map-box').scrollLeft
		}
	},

	atstumas: function (x1, y1, x2, y2) {
		return Math.sqrt((x1-x2)*(x1-x2)+(y1-y2)*(y1-y2));
	},

	atackCountCoo: function (x, y) {
		var index;
		var way = 9999999;

		var w = 60;
		var h = 50;

		var a = Math.round(x / w) + 1;
		var b = Math.round(y / h) + 1;

		for (var a1 = a-1; a1 <= a+1; a1++)
			for (var b1 = b-1; b1 <= b+1; b1++) {
				var ii = b1 + (a1 - 1) * this.height;

				if (this.o[ii]) {
					var nway=this.atstumas(x,y, this.o[ii].ox, this.o[ii].oy);

					if (nway<way) {
						way=nway;
						index=ii;
					}
				}
			}

		return this.o[index];
	},

	atackInfo : function (x,y) {
		if (!(x && y)) {
			P('a-info').update();
			return 0;
		}

		var text="(x: "+x+"; y: "+y+"): ";

		var color='#CCCCCC';
		var self = this;

		if (this.fighters!=null) {
			this.fighters.each(function (s) {
				if (s.x==x && s.y==y) {
					color=(self.fighters[self.myindex].side==s.side)? 'green' : 'red';

					text+='Žaidėjas '+(s.name)+' kuris turi '+(Math.round(s.HP/s.maxHP*100))+'% gyybių';
				}
			});
		}

		P('a-info').update(text);
		P('a-info').style.color=color;
	},

	start : function () {
		this.loader();
	},

	loader : function () {
		P('a-timer-left').update(
			'<img src="/img/waiting2.gif" />'
		);
	},

	finish : function (self) {
		self.makebg();
		self.processAtack(0);
	},

	makebg : function () {
		var ctx = this.canvasBG.getContext('2d');

		var x;
		var y;

		var imgWidth = this.img.bg.width;
		var imgHeight = this.img.bg.height;

		for (x = 0; x <= this.x; x += imgWidth) {
			for (y = 0; y <= this.y; y += imgHeight) {
				ctx.drawImage(this.img.bg, x, y);
			}
		}

		var index = 0;
		for (x = 1; x <= this.width; x++) {
			for (y = 1; y <= this.height; y++) {
				var ox=(x-1)*60+40;
				var oy=(y-1)*50+25;

				if (x%2==0) {
					oy+=25;
				}

				index++;

				this.o[index]= {
					'ox' : ox,
					'oy' : oy,
					'x' : x,
					'y' : y
				};

				this.drawSesiakampi(ctx, ox, oy, {
					strokeColor: 'white',
					strokeWidth: 1,
					alpha: 1
				});
			}
		}
	},

	processAtack : function (type) {
		if (!(P('a-timer-left')))
			this.destroy();

		this.key++;
		var self = this;

		self.A_BAR.stop();
		this.loader();

		if (this.pushed!=null) {
			var xx=this.pushed.x;
			var yy=this.pushed.y;
		} else {
			var xx=0;
			var yy=0;
		}

		new Ajax.Request('/atack', {
			parameters: {
				'x' : xx,
				'y' : yy,
				'type' : type,
				'key' : self.key,
				'aid' : self.aid,
				'since' : self.log.lastID
			},
			onComplete: function (text) {
				if (!text.responseText.isJSON()) {
					Dialogs.alert(text.responseText);
					return 0;
				}

				var DATA=text.responseText.evalJSON();

				if (DATA.error) {
					Dialogs.alert(DATA.error);
				}

				var ilgis = DATA.logs.length;

				for (var i = 0; i <= ilgis - 1; i++) {
					var itemas = DATA.logs[i];
					var force = (itemas.id == 0)? true : false;
					self.log.addItem(itemas.text, Number(itemas.id), itemas.date, force);
				}

				self.A_BAR.set(DATA.time_left);
				self.A_BAR.move(0,DATA.time_left);

				self.fighters=DATA.fighters;
				self.objects=DATA.objects;

				P('a-turn-left').update(DATA.left);
				P('a-bullet-left1').update(DATA.shot_left1);
				P('a-bullet-left2').update(DATA.shot_left2);

				self.fighters.each(function (s, index) {
					if (s.id==self.uid) {
						self.myindex=index;
					}
				});

				if (self.fighters[self.myindex].side==DATA.side) {
					var color="green";
				} else {
					var color="red";
				}

				P('a-timer-left').style.color=color;
				self.A_BAR.setImage('/img/bar/'+color+'.png');

				if (type>0)
					self.pushed = null;

				self.drawFighters();
				self.draw();
			}
		});
	},

	drawSesiakampi : function (ctx, x, y, info) {
		ctx.save();
		ctx.strokeStyle = info.strokeColor;
		ctx.lineWidth = info.strokeWidth;
		ctx.translate(x-40,y-25);

		ctx.globalAlpha=info.alpha;

		var w=80;
		var h=50;

		var points=[
			{ "x" : w/4-1	, "y" :0	},
			{ "x" : w/4*3-1	, "y" :0	},
			{ "x" : w-1		, "y" :h/2},
			{ "x" : w/4*3-1	, "y" :h	},
			{ "x" : w/4-1	, "y" :h	},
			{ "x" : 0-1		, "y" :h/2}
		];

		ctx.beginPath();
		ctx.moveTo(w/4-1, 0);

		points.each(function (s,index) {
			ctx.lineTo(s.x, s.y);
		});
		ctx.closePath();

		if (info.fillColor) {
			ctx.fillStyle = info.fillColor;
			ctx.fill();
		}

		ctx.stroke();

		ctx.restore();
	},

	drawFighters: function () {
		var ctx1 = this.canvasMiddle.getContext('2d');
		var self = this;

		ctx1.clearRect(0,0, this.x, this.y);

		self.fighters.each( function (s, index) {
			var ox=(s.x-1)*60+40;
			var oy=(s.y-1)*50+25;

			if (s.x%2==0) {
				oy+=25;
			}

			var race = s.race;
			var side = s.side;

			if (s.side!=self.fighters[self.myindex].side) {
				var color = 'red';
			} else if (s.id==self.fighters[self.myindex].id) {
				var color = 'blue';
			} else {
				var color = 'green';
			}

			self.drawSesiakampi(ctx1, ox, oy, {
				'strokeColor' : 'white',
				'strokeWidth' : 1,
				'alpha' : 0.45,
				'fillColor' : color
			});

			if (s.race==0) {//zmogus
				if (s.side=='L') {
					var img = self.img.manL;
				} else {
					var img = self.img.manR;
				}
			} else {
				if (s.side=='L') {
					var img = self.img.rjalL;
				} else {
					var img = self.img.rjalR;
				}
			}

			var x = ox - img.width/2;
			var y = oy - img.height/2 - 12;

			ctx1.drawImage(img, x, y);

			var hp = (s.dead=='Y')? 0 : s.HP;
			var maxhp = s.maxHP;

			var rate = hp / maxhp;

			var bar_x = ox - 80/4;
			var bar_y = oy + 25 - 8;

			var bar_h = 6;
			var bar_w = 80 / 2;

			self.bar(ctx1, bar_x, bar_y, bar_w, bar_h, rate, 'black', 'gray', 'green');
		});
	},

	draw: function () {
		var ctx2 = this.canvas.getContext('2d');

		ctx2.drawImage(this.canvasBG, 0, 0);
		ctx2.drawImage(this.canvasMiddle, 0, 0);

		if (this.moved!=null)
			this.drawSesiakampi(ctx2, this.moved.ox, this.moved.oy, {
				strokeColor : 'red',
				strokeWidth : 3,
				alpha: 0.3
			});

		if (this.pushed!=null)
			this.drawSesiakampi(ctx2, this.pushed.ox, this.pushed.oy, {
				strokeColor : 'red',
				strokeWidth : 3,
				alpha: 0.7
			});
	},

	destroy: function () {
		destroyAtack();
	},

	bar: function (ctx,x, y, width, height, dalis, border, bg, bar) {
		ctx.save();

		ctx.globalAlpha = 1;

		ctx.translate(x,y);

		ctx.fillStyle = bg;
		ctx.fillRect(0,0,width, height);

		ctx.strokeStyle = border;
		ctx.strokeRect(0,0, width, height);

		if (dalis > 0) {
			var ilgis = dalis * (width - 2);
			var aukstis = height - 2;

			ctx.fillStyle = bar;
			ctx.fillRect(1,1,ilgis, aukstis)
		}

		ctx.restore();
	}
});