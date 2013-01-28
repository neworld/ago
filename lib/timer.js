var Timer = Class.create(PeriodicalExecuter,{
	initialize: function (time2,where,op) {
		this.endtime=time2+time();
		this.where=where;
		this.onstop=op.onstop;
		this.ontimer=op.ontimer;
		this.callback=this.execute;
		this.frequency=1;
		this.currentlyExecuting = false;
		this.registerCallback();
		this.execute();
	},

	execute: function () {
		var left=this.getleft();

		if ((left=="------") || (!document.getElementById(this.where))) {
			if ((typeof this.onstop == 'function') && (document.getElementById(this.where))) {
				this.onstop();
			}
			this.stop();
			return true;
		} else {
			P(this.where).update(left);

			if (typeof this.ontimer == 'function' ) {
				this.ontimer(left);
			}

			return true;
		}
	},

	getleft: function () {
		var text='';
		var timeleft=this.endtime-time();

		if (timeleft<=0) {
			return "------";
		}

		var day=Math.floor(timeleft/(3600*24));
		timeleft-=3600*24*day;
		var hour=Math.floor(timeleft/(3600));
		timeleft-=3600*hour;
		var minute=Math.floor(timeleft/(60));
		timeleft-=60*minute;
		var second=timeleft;

		if (day>0) {
			text=day+'d. ';
		}

		var Shour=(hour<10)? "0"+String(hour) : String(hour);
		var Smin=(minute<10)? "0"+String(minute) : String(minute);
		var Ssec=(second<10)? "0"+String(second) : String(second);

		text=text+Shour+':'+Smin+':'+Ssec;

		return text;
	}
});