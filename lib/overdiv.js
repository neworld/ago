var overDIV_on=false;
var tmpX=0;
var tmpY=0;
function overlib(text) {
	var b=document.viewport.getDimensions();
	var max=b.height-P('overDiv').getDimensions().height-10;
	var ob=P('overDiv');
	ob.innerHTML=text;
	ob.style.visibility='visible';
	if (tmpX+16+ob.getDimensions().width<=b.width) {
		ob.style.left=(tmpX+12)+"px";
	} else {
		ob.style.left=(tmpX-12-ob.getDimensions().width)+"px";
	}
	ob.style.top=Math.min((tmpY+8),max)+"px";
	overDIV_on=true;
	return true;
}
function nd() {
	var ob=P('overDiv');
	ob.style.visibility='hidden';
	overDIV_on=false;
}
function overDIV_move(e) {
	var b=document.viewport.getDimensions();
	var max=b.height-P('overDiv').getDimensions().height-10;
	tmpX=Event.pointerX(e);
	tmpY=Event.pointerY(e);
	if (overDIV_on) {
		var ob=P('overDiv');

		if (tmpX+16+ob.getDimensions().width<=b.width) {
			ob.style.left=(tmpX+12)+"px";
		} else {
			ob.style.left=(tmpX-12-ob.getDimensions().width)+"px";
		}

		ob.style.top=Math.min((tmpY+8),max)+"px";
	}
	return true;
}

Event.observe(window, 'load', function() {
	Event.observe(document, 'mousemove', overDIV_move);
	var newdiv = document.createElement('div');
	newdiv.setAttribute('id','overDiv');
	document.body.appendChild(newdiv);
	P('overDiv').setOpacity(0.9);
});

var Overlib=Class.create({
	initialize: function (text,ob) {
		ob.observe('mouseover',function(event) {
			overlib(text);
		});
		ob.observe('mouseout',function(event) {
			nd();
		});
	}
});