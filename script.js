Object.extend(Event, {
	wheel:function (event){
		var delta = 0;
		if (!event) event = window.event;
		if (event.wheelDelta) {
			delta = event.wheelDelta/120;
//			if (window.opera) delta = -delta;
		} else if (event.detail) { delta = -event.detail/3;	}
//		if (event.axis && event.axis == event.HORIZONTAL_AXIS) delta = -delta;
		return Math.round(delta); //Safari Round
	}
});
function smile(text) {
	for (x=0;x<=smilein.length;x++) {
		text=str_replace(smilein[x],smileout[x],text);
	}
	return text;
}
function str_replace(ssearch, rreplace, subject) {
	return subject.split(ssearch).join(rreplace);
}
function getrandom(mmin,mmax) {
	var number=(Math.floor(Math.random()*(mmax-mmin+1))+mmin);
	return number;
}
function resizorius() {
	var b=document.viewport.getDimensions();
	var height=b.height-300-1;
	P('Content').style.width=(b.width-300-24)+"px";
	var a=P('chatfrm').select('div');
	P('chatfrm').height=height+"px";
	P('chat_main_box').height=height+"px";
	a.each(function (s) {
		s.style.height=(height)+"px";
	});
}
function time() {
	var d = new Date();

	return Math.floor(d.getTime()/1000);
}
function nanotime() {
	var d = new Date();

	return d.getTime();
}
function checkeris(adr,ob) {
	new Ajax.Request(adr,{
		onSuccess: function (response) {
			ob.src=(response.responseText)? '/img/good.png' : '/img/bad.png';
		}
	});
	return true;
}
function check_email(e) {
	re = /(@.*@)|(\.\.)|(^\.)|(^@)|(@$)|(\.$)|(@\.)/i;
	re_two = /^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/i;
	if (!e.match(re) && e.match(re_two)) {
		return true;
	}
	return false;
}
var IMAGEBOX_GROUPS=Array();
function check_imagebox(group,ob) {
	if (IMAGEBOX_GROUPS[group]) {
		IMAGEBOX_GROUPS[group].removeClassName('check_imagebox');
	}
	ob.addClassName('check_imagebox');
	IMAGEBOX_GROUPS[group]=ob;
}
var SHOW_HIDE_GROUPS=Array();
function show_hide(group,ob) {
	if (SHOW_HIDE_GROUPS[group]) {
		SHOW_HIDE_GROUPS[group].hide();
	}
	ob.show();
	SHOW_HIDE_GROUPS[group]=ob;
}
function half_sinoidal (pos) {
	return (-Math.cos(pos*Math.PI/2+Math.PI/2));
}
var MENU_EFFECT;
var kryptis=null;
function move_menu_content(s) {
	var max = P('menu-main-links').getWidth()-P('menu-main').getWidth();
	var a = P('menu-main-links').positionedOffset();
	var left=-1*a[0];

	s=(left+s>max)? max-left : s;
	s=(left+s<=0)? -1*left : s;
	kryptis=(s>0)? 1 : -1;
	var duration=0.5;
	var transition=Effect.Transitions.sinoidal;
	if (MENU_EFFECT) {
		if (MENU_EFFECT.state!="finished") {
			MENU_EFFECT.cancel();
			duration=0.25;
			transition=half_sinoidal;
		}
	}
	
	MENU_EFFECT=new Effect.Move(
		P('menu-main-links'),
		{
			x: -1*s,
			y:0,
			duration: duration,
			transition: transition,
			beforeUpdate: function () {
				var b = P('menu-main-links').positionedOffset();
				var bb=-1*b[0];
				if (((bb>=max) && (kryptis==1)) || ((bb<=0) && (kryptis==-1))) {
					MENU_EFFECT.cancel();
					MENU_EFFECT=null;
				}
			}
		}
	);

}
//mirksiukas
/*var FLASH_OB=Array();
function flash_add(ob,clase) {
	ob.addClassName(clase);
	FLASH_OB.push({'ob' : ob, 'clase' : clase});
}
function flash_remove(ob) {
	var OB;
	var CLASE;
	FLASH_OB.filter(function (element, index, array) {
		if (element.ob.id!=ob.id) {
			return true;
		} else {
			OB=element.ob;
			CLASE=element.clase;
			return false;
		}
	});
	OB.removeClassName*/

//chatas
var CHAT_CHANNEL='json=1';
var CHAT_CHANNEL_ID=1;
var CHAT_TRACK=1;
var CHANNELS=Array();
function chat_add_channel(channel,ob, on) {
	CHANNELS.push({'channel' : channel, 'ob': ob, 'on' : on});
}
function sndchat(text) {
	CHAT_TRACK++;
	var aa=Array();
	CHANNELS.each(function (s) {
		aa.push(s.channel);
	});
	new Ajax.Request('/chat?'+CHAT_CHANNEL,{
		method: 'post',
		parameters: {'text':text, TRACK: CHAT_TRACK, 'CHAT_CHECK': Object.toJSON(aa) },
		onComplete: function(transport) {
			closeitem();
			var ob=P('chat_slide_'+CHAT_CHANNEL_ID);
			if (transport.responseText.isJSON()) {
				var a=transport.responseText.evalJSON(true);
				if (a.TRACK!=CHAT_TRACK) {
					return false;
				}
				ob.innerHTML='';
				a.data.each(function (s) {
					if (s.text && s.user) {
						if (s.user=='system') {
							ob.innerHTML+='<div class="chat_system">'+s.text+"</div>";
						} else if (s.user=='post') {
							ob.innerHTML+='<div class="chat_post">'+s.text+'</div>';
						} else {
							var priv = (s.private != null)? 'private' : ''; 
							var atag=(s.atag)? " <a class='" + priv + "' onclick=\"content('/page/claninfo/tag/"+s.atag+"');\">["+s.atag+"]</a>" : "";
							ob.innerHTML+='<div class="' + priv + '"><span style="font-weight:bold"><a class="pointer '+priv+'" onclick="content(\'/page/userinfo/name/'+s.user+'\');">'+s.user+"</a></span>"+atag+": "+smile(activate_itemLinks(s.text))+"</div>";
						}
					}
				});
				ob.scrollTop = 1200;

				CHANNELS.each(function (s) {
					var NEED=false;
					a.unread.each(function (ss) {
						if (ss==s.channel)
							NEED=true;
					});
					if (NEED) {
						s.ob.addClassName('unread_chat');
					} else {
						s.ob.removeClassName('unread_chat');
					}
				});
				a.online.each(function (s) {
					CHANNELS.each( function (ss) {
						if (s.channel==ss.channel) {
							ss.on.update(s.online);
						}
					});
				});

			} else {
				ob.innerHTML=transport.responseText;
			}
		}
	});
}
var INFO_NEW=true;
function change_info(info) {
	if (INFO_NEW) {
		show_info(info);
		INFO_NEW=false;
	} else {
		hide_info(info);
		show_info('');
	}
}
function hide_info(info) {
	new Effect.Opacity(P('info_box'),{
		afterFinish: function() {
			if (info) {
				P('info_box').innerHTML=info;
			}
		},
		from: 1,
		to: 0,
		duration: 2,
		queue: 'end'
	});
}
var INFO_TIME;
function show_info(info) {
	new Effect.Opacity(P('info_box'),{
		beforeStart: function() {
			if (info!='') {
				P('info_box').innerHTML=info;
			}
		},
		afterFinish: function() {
			INFO_TIME=time();
		},
		from: 0,
		to: 1,
		duration: 2,
		queue: 'end'
	});
}


//ajaxinis controleris
var no = "no";
var returnas = 0;
var SAVED_ADR;
var SERVER_TIME;
var reg_time = 0;
var UNRUN;
var VERSION;
var VERSION2;
var TRACK_NUM = 1;
function content(adr,post,save,title) {
	P('Content').startWaiting('bigBlackWaiting',0);
	//pasiruosimas
	var dimensions=P('Content').getDimensions();
	returnas++;
	if (!save) {
		SAVED_ADR=adr;
	} else if (save!="no") {
		SAVED_ADR=save;
	}

	reg_time=time();

	TRACK_NUM++;

	new Ajax.Request(adr,{
		method: 'post',
		parameters: {
			time: reg_time,
			width: dimensions.width,
			height:dimensions.height,
			returnas:returnas,
			key:SES_KEY,
			TRACK_NUM:TRACK_NUM,
			post:Object.toJSON(post)
		},
		onComplete: function (transport) {
			if (transport.responseText.isJSON()) {
				var data=transport.responseText.evalJSON();
				if (data.TRACK_NUM!=TRACK_NUM) {
					return false;
				}

				if (VERSION2 < data.version) {
					refresh();
				}
				if (UNRUN) {
					eval(UNRUN);
                    UNRUN = '';
				}
				if (data.unrun) {
					UNRUN=data.unrun;
				}
				SERVER_TIME=data.time;
				if (data.info) {
					change_info(data.info);
				}
				if (data.user) {
					set_sensor(data.user);
				}
				P('Content').innerHTML=data.content;
				if (data.run) {
					//alert(data.run);
					eval(data.run);
				}

				P('event-icon').src=(data.event>0)? "/img/event2.png" : "/img/event.png";

				P('pm-icon').src=(data.pm>0)? "/img/PM2.png" : "/img/PM.png";

				if (title) {
					changeTitle(title);
				} else {
					changeTitle('Agonija.eu');
				}
			} else {
				P('Content').innerHTML='<div class="bad">'+transport.responseText+'</div>';
			}
			P('Content').stopWaiting();
		}
	});
}
function refresh() {
	window.location.href = "/main/index/nocache/"+getrandom(10000,99999);
}
function Crefresh() {
	content(SAVED_ADR);
}
function upgrade_skill(id,down) {
	var adr=(down)? '/ajax/skill/down/1/type/'+id : '/ajax/skill/type/'+id;
	if (id) {
		P('skill_box_'+id).startWaiting('bigBlackWaiting',0);
		new Ajax.Request(adr,{
			onComplete: function (transport) {
				if (transport.responseText.isJSON()) {
					var data=transport.responseText.evalJSON();
					//sudedam skilu kainas
					for (var index in data.skill) {
						P('skill_'+index+'_cost').update(data.skill[index]);
					};
					//suupdatinam patobulinta skila
					P('skill_'+data.skill_type+'_lvl').update(data.lvl);
					set_sensor({money:data.money});
				} else {
					Dialogs.alert(transport.responseText);
				}
				P('skill_box_'+id).stopWaiting();
			}
		});
	}
}

function set_sensor(ob) {
	if (typeof ob.lvl != 'undefined') {
		P('sensor_lvl').update(ob.lvl);
	}
	if (typeof ob.money != 'undefined') {
		P('sensor_money').update(ob.money);
	}
	if (typeof ob.nano != 'undefined') {
		P('sensor_nano').update(ob.nano);
	}
	if ((typeof ob.hp != 'undefined') && (typeof ob.maxhp != 'undefined')) {
		var hp=ob.hp;
		var maxhp=ob.maxhp;
		
		bar_hp.max = maxhp;
		bar_hp.move(hp, 0.2);
	}
	if ((typeof ob.have_exp != 'undefined') && (typeof ob.need_exp != 'undefined')) {
		var have_exp=ob.have_exp;
		var need_exp=ob.need_exp;
		
		bar_exp.max = need_exp;
		bar_exp.move(have_exp, 0.2);
	}
	if (typeof ob.work != 'undefined') {
		bar_work.move(ob.work, 0.2);
	}
}
var ITEM_PLACE;
var ITEM_MENU_VIEW=false;
function show_item_menu(place) {
	nd();
	ITEM_PLACE=place;

	var bb=P('Content').cumulativeScrollOffset();
	var pos=P('Content').cumulativeOffset();
	var a=P('item-menu').getDimensions();
	var aa=P('Content').getDimensions();

	var left=(aa.width<a.width+tmpX)? aa.width-a.width-30 : tmpX;
	var top=(aa.height<a.height+tmpY-pos[1])? aa.height-a.height-5+pos[1] : tmpY;

	P('item-menu').style.top=String(top-5-pos[1]+bb[1])+"px";
	P('item-menu').style.left=String(left-5-pos[0]+bb[0])+"px";

	if (ITEM_RAW_TYPE=='CONSUMABLE') {
		P('item_use_link').style.color="#FFFFFF";
	} else {
		P('item_use_link').style.color="#FF0000";
	}

	Effect.BlindDown('item-menu',{
		duration: 0.25 ,
		afterFinish: function () {
			ITEM_MENU_VIEW=true;
		}
	});
}
function hide_item_menu() {
	if (ITEM_MENU_VIEW) {
		ITEM_MENU_VIEW=false;
		Effect.BlindUp('item-menu', { duration: 0.25, queue: 'end'});
	}
}

function show_item_dialog() {
	var item=ITEM_PLACE;
	hide_item_menu();
	new Ajax.Request('/ajax/getitemplace', {
		parameters: { item : item },
		onComplete: function (DATA) {
			if (DATA.responseText.isJSON()) {
				var data=DATA.responseText.evalJSON();
				var text='<div style="font-weight:bold">Perkelti daiktą į kitą vietą: </div>';
				text+='<div><select id="new_place">';
				for (var index in data.places) {
					text+='<option value="'+index+'">'+data.places[index]+'</option>';
				}
				text+='</select></div>';
				text+='<div><input id="do_place" type="button" value="Perkelti" /></div>';

				var idialog = new Dialog({
					title: data.title,
					content: text,
					afterOpen: function () {
						P('do_place').observe('click',function () {
							content('/page/item/actionas/place',{ 'type' : item, 'place' : $F('new_place')}, no);
							Dialogs.close();
						});
					}
				});
				idialog.open();
			} else {
				Dialogs.alert(DATA.responseText);
			}
		}
	});
}
function show_item_remove() {
	var item=ITEM_PLACE;
	hide_item_menu();
	Dialogs.confirm(
		'Ar tikrai norit parduoti '+ITEM_TITLE,
		function(){
			content('/page/item/actionas/remove',{ 'type' : item }, no);
			Dialogs.close();
		}
	);
}
function show_private_market_send(to) {
	var item=ITEM_PLACE;
	hide_item_menu();
	var text='<div>Kam:</div>';
	text+='<div><input type="text" id="to" /></div>';
	text+='<div>Suma:</div>';
	text+='<div><input type="text" id="cost" /></div>';
	text+='<div style="text-align:center">';
	text+='<input type="button" value="siusti" id="ok" />';
	text+='<input type="button" value="atšaukti" id="cancel" />';
	text+='</div>';

	var idialog = new Dialog({
		title: ITEM_TITLE,
		content: text,
		afterOpen: function () {
			if (to)
				P('to').value=to;

			P('ok').observe('click',function () {
				if ($F('to') && $F('cost')) {
					content('/page/item/actionas/pmarket', {'type' : item, 'to' : $F('to'), 'cost' : $F('cost')}, no);
					Dialogs.close();
				}
			});

			P('cancel').observe('click', function () {
				Dialogs.close();
			});
		}
	});
	idialog.open();
}
function show_pmarket(id) {
	new Ajax.Request('/ajax/pmarket', {
		parameters: {'pm_id':id},
		onComplete: function (DATA) {
			if (DATA.responseText.isJSON()) {
				var data=DATA.responseText.evalJSON();
				var text='<div>Ar tikrai norite nupirkti '+data.link+' už: '+data.cost+' iš: '+data.from+'</div>';
				text+='<div style="text-align:center">';
				text+='<input type="button" value="siusti" id="pm-ok" />';
				text+='<input type="button" value="atšaukti" id="pm-cancel" />';
				text+='</div>';
				var idialog = new Dialog({
					title: data.title+' pirkimas',
					content: text,
					afterOpen: function () {
						P('pm-ok').observe('click',function () {
							content(SAVED_ADR, { 'pmarket_buy' : id }, no);
							Dialogs.close();
						});

						P('pm-cancel').observe('click', function () {
							Dialogs.close();
							show_item_reason(data, id);
						});
					}
				});
				idialog.open();
			} else {
				Dialogs.alert(DATA.responseText);
			}
		}
	});
}

function remove_pmarket(id) {
	new Ajax.Request('/ajax/pmarket', {
		parameters: {'pm_id':id},
		onComplete: function (DATA) {
			if (DATA.responseText.isJSON()) {
				var data=DATA.responseText.evalJSON();
				var text='<div>Ar tikrai norite nupirkti '+data.link+' už: '+data.cost+' iš: '+data.from+'</div>';
				text+='<div style="text-align:center">';
				text+='<input type="button" value="siusti" id="pm-ok" />';
				text+='<input type="button" value="atšaukti" id="pm-cancel" />';
				text+='</div>';
				Dialogs.confirm(
					'Ar tikrai norite atšaukti '+data.link+' pardavimą?',
					function () {
						content(SAVED_ADR, {'pmarket_remove' : id}, no);
						Dialogs.close();
					}
				);
			} else {
				Dialogs.alert(DATA.responseText);
			}
		}
	});
}

function show_item_reason(data, id) {
	var text='<div>Jūs galite nusiųsti paaiškinimą, kodėl neperkate daikto. Tarkim per brangu, ar ne tą daiktą siunčia</div>';
	text+='<div><input type="text" id="reason" size="70"></div>';
	text+='<div style="text-align:center">';
	text+='<input type="button" value="Siųsti" id="ok" />';
	text+='</div>';
	var iidialog=new Dialog({
		title: data.title+' pirkimo atšaukimas',
		content: text,
		afterOpen: function () {
			P('ok').observe('click', function () {
				content(
					'/page/item',
					{
						'pmarket_cancel': id,
						'reason': $F('reason')
					},
					no
				);
			});
		}
	});
	iidialog.open();
}

function item_use() {
	if (ITEM_RAW_TYPE=='CONSUMABLE') {
		hide_item_menu();
		content('/page/item/actionas/use',{ 'type' : ITEM_PLACE }, no);
	}
}

function show_item_market() {
	var item=ITEM_PLACE;
	hide_item_menu();
	new Ajax.Request('/ajax/getitemplace', {
		parameters: { item : item },
		onComplete: function (DATA) {
			if (DATA.responseText.isJSON()) {
				var data=DATA.responseText.evalJSON();
				var text='';
				if (data.cansell==1) {
					text+='<div style="margin-top:10px"><input id="do_market" type="button" value="Įdėti į turgų" /> Kaina: <input size="5" id="market_cost" type="text" value="'+data.cost+'" />';
					text+=' <input style="width:18px;height:18px" type="checkbox" id="check_market" value="remove"> Tvirtinti </div>';
				} else {
					text+='<div><i>Nebeturite laisvų vietų turguje. Turite išsiimti senus daiktus arba padidinti prekybos įgūdžius</i></div>';
				}

				var idialog = new Dialog({
					title: data.title,
					content: text,
					afterOpen: function () {
						if (data.cansell==1) {
							P('do_market').observe('click', function () {
								if (P('check_market').checked) {
									content('/page/item/actionas/market',{ 'type' : item, 'cost' : $F('market_cost') }, no);
									Dialogs.close();
								}
							});
						}
					}
				});
				idialog.open();
			} else {
				Dialogs.alert(DATA.responseText);
			}
		}
	});
}
function show_item_link() {
	hide_item_menu();
	var text='Daikto nuoroda: <div><input size="50" type="text"  readonly value="'+ITEM_LINK+'" /></div>';
	var idialog = new Dialog({
		title: ITEM_TITLE,
		content: text
	});
	idialog.open();
}


function show_new_pm(to,title,fromid) {
	var text="<div>Kam:</div>";
	text+='<input id="pm-to" type="text" size="32" maxlength="64" />';
	text+='<div>Tema:</div>';
	text+='<input id="pm-title" type="text" size="64" maxlength="128" />';
	text+='<div>Žinutė</div>';
	text+='<textarea id="pm-text" rows="10" cols="64"></textarea>';
	if (fromid && to)
		text+='<div><input type="checkbox" id="pm-fromid" value='+fromid+' checked /> noriu jog būtų cituojama atsakomoji žinutė</div>';

	text+='<div style="text-align:center">';
	text+='<input id="pm-ok" type="button" value="Siųsti" />';
	text+='<input id="pm-no" type="button" value="Atšaukti" />';
	text+='</div>';

	var pmdialog = new Dialog({
		title: "Rašyti pm",
		content: text,
		afterOpen: function () {
			if (to)
				P('pm-to').value=to;
			if (title) {
				var newtitle=(title.indexOf("RE: ")===0)? title : "RE: "+title;
				P('pm-title').value=newtitle;
			}
			P('pm-no').observe('click',function () {
				Dialogs.close();
			});
			P('pm-ok').observe('click', function () {
				content(
					'/page/PM',
					{
						to : $F('pm-to'),
						title : $F('pm-title'),
						text : $F('pm-text'),
						fromid : (P('pm-fromid')) ? $F('pm-fromid') : '',
						'send' : 1
					},
					no
				);
				Dialogs.close();
			});
		}
	});
	pmdialog.open();
}
function show_pm(key) {
	new Ajax.Request('/ajax/getpm',{
		parameters: {pid : key},
		onComplete: function (DATA) {
			if (DATA.responseText.isJSON()) {
				var data=DATA.responseText.evalJSON();
				var text="<div style=\"width:500px\">"+data.text+"</div>";
				text+='<div style="text-align:center">';
				text+='<input id="pm-reply" type="button" value="Atsakyti" />';
				text+='<input id="pm-delete" type="button" value="Ištrinti" />';
				text+='<input id="pm-close" type="button" value="Išjungti" />';
				text+='</div>';

				var idialog = new Dialog({
					title: "Žinutė nuo " + data.fromname,
					content: text,
					afterOpen: function () {
						P('pm-close').observe('click',function () {
							Dialogs.close();
						});
						P('pm-delete').observe('click', function () {
							content('/page/pm/delete/'+data.id,null,no);
							Dialogs.close();
						});
						P('pm-reply').observe('click', function () {
							Dialogs.close();
							show_new_pm(data.fromname,data.title,data.id);
						});
					}
				});
				idialog.open();
			} else {
				Dialogs.alert(DATA.responseText);
			}
		}
	});
}
function show_buy_dialog(id,title,cost,url) {
	Dialogs.confirm(
		'Ar tikrai norite nusipirkti '+title+' už '+cost+'?',
		function () {
			content(url,{ buy : id },no);
			Dialogs.close();
		},
		function () {
			Dialogs.close();
		}
	);
}
function show_remove_dialog(id,title,cost,url) {
	Dialogs.confirm(
		'Ar tikrai norite atsiimti '+title+'?',
		function () {
			content(url,{ remove : id },no);
			Dialogs.close();
		},
		function () {
			Dialogs.close();
		}
	);
}
function show_forum_dialog(subid,root,title,cid) {
	var text="";
	if (!root) {
		text+="<div>Tema</div>";
		text+='<input id="forum-title" type="text" size="40" maxlength="128" />';
	}
	text+='<div>Žinutė</div>';
	text+='<textarea id="forum-text" rows="10" cols="64"></textarea>';

	text+='<div style="text-align:center">';
	text+='<input id="forum-ok" type="button" value="Siųsti" />';
	text+='<input id="forum-no" type="button" value="Atšaukti" />';
	text+='</div>';

	var forumdialog = new Dialog({
		title: (title)? 'Atsakyti į temą: '+title : 'Kurti naują temą',
		content: text,
		afterOpen: function () {
			P('forum-no').observe('click', function () {
				Dialogs.close();
			});
			P('forum-ok').observe('click', function () {
				content(
					'/page/forum/subid/'+subid+((root)? '/root/'+root : '')+((cid)? '/cid/'+cid : ''),
					{
						text: $F('forum-text'),
						title: (P('forum-title'))? $F('forum-title') : '',
						write: 1
					},
					no
				);
				Dialogs.close();
			});
		}
	});
	forumdialog.open();
}
function show_clan_invite() {
	var text="";

	text+="<div>Adresatas</div>";
	text+='<input id="clan_name" type="text" size="40" maxlength="128" />';

	text+="<div>Priežastis</div>";
	text+='<input id="clan_reason" type="text" size="40" maxlength="128" />';

	text+='<div style="text-align:center">';
	text+='<input id="clan-okk" type="button" value="Siųsti" />';
	text+='<input id="clan-no" type="button" value="Atšaukti" />';
	text+='</div>';

	var forumdialog = new Dialog({
		title: 'Pakviesti narį į aljansą',
		content: text,
		afterOpen: function () {
			P('clan-no').observe('click', function () {
				Dialogs.close();
			});
			P('clan-okk').observe('click', function () {
				content(
					'/page/clan',
					{
						name: $F('clan_name'),
						reason: $F('clan_reason'),
						invite: 1
					},
					no
				);
				Dialogs.close();
			});
		}
	});
	forumdialog.open();
}
function shop_buy(id,nano,money) {
	var text='<input id="buy_nano" type="button" value="Už '+nano+' nano kreditų" />';
	text+='<input id="buy_money" type="button" value="Už '+money+' agonų" />';
	text+='<input id="buy_close" type="button" value="Išjungti" />';

	var dialogas=new Dialog({
		title: 'Pirkti...',
		content:text,
		afterOpen: function () {
			P('buy_nano').observe('click', function () {
				Dialogs.close();
				shop_buy_confirm(id,nano,1);
			});
			P('buy_money').observe('click', function () {
				Dialogs.close();
				shop_buy_confirm(id,money,2);
			});
			P('buy_close').observe('click', function () {
				Dialogs.close();
			});
		}
	});
	dialogas.open();
}
function shop_buy_confirm(id,num,type) {
	Dialogs.confirm(
		'Ar tikrai norite pirkti už '+num+' '+((type==1)? 'nano kreditų' : 'agonų'),
		function () {
			Dialogs.close();
			content('/page/shop', {'buy' : id, 'type' : type}, no);
		}
	)
}
function get_radio(klase) {
	var id=0;
	$$(klase).each(function (s) {
		if (s.checked) {
			id=s.id;
		}
	});
	return id;
}
function vote() {
	var id=get_radio('.poll-item');
	var a=id.split('poll-item-');
	var id2=a[1];
	new Ajax.Request('/ajax/vote',{
		parameters: {pid : id2},
		onComplete: function (DATA) {
			if (DATA.responseText=='0') {
				change_info('Jūsų balsas įrašytas');
			} else {
				change_info(DATA.responseText);
			}
		}
	});
}
var showItems=Array();
var SHOWITEM=true;
function showitem(itemid) {
	SHOWITEM=true;
	if (showItems[itemid]) {
		overlib(showItems[itemid]);
	} else {
		new Ajax.Request('/ajax/item/itemid/'+itemid,{
			onComplete: function (DATA) {
				if (DATA.responseText) {
					showItems[itemid]=DATA.responseText;
					if (SHOWITEM) {
						overlib(DATA.responseText);
					}
				}
			}
		});
	}
}
function closeitem() {
	if (SHOWITEM) {
		SHOWITEM=false;
		nd();
	}
}

function activate_itemLinks(text) {
	return String(text).replace(/\[ITEM:([0-9]+)(:([^\[\]:]*):)?\]/,'<a style="font-weight:bold;color:#008E03" class="pointer" onmouseover="showitem($1);" onmouseout="closeitem()">[$3]</a>');
}
function changeTitle(title) {
	document.title = title;
}
function startfotoUpload() {
	P('upload_form').hide();
	P('upload_status').update('<img src="/img/ajax-loader.gif" />');
	P('upload_status').show();
}

function stopfotoUpload(status) {
	if (status=='0') {
		Crefresh();
	} else {
		P('upload_status').update(status);
		P('upload_form').show();
	}
}

var startX;
var startY;
var ITEM_DRAG=false;
var itemX;
var itemY;
var ITEM_TYPE;
var ITEM_REVERSE;
var ITEM_OB;
var ITEM_POS;
var ITEM_BOX_POS;
var ITEM_BOX_POS2;
var ITEM_DROP;
var ITEM_LINK;
var ITEM_TITLE;
var ITEM_COST;
var ITEM_RAW_TYPE;
Event.observe(document, 'mousemove', item_mousemove);
Event.observe(document, 'mouseup', item_mouseup);
var ITEM_DROPS;
var ITEM_START_SCROLL;
function item_click(ob,TYPE,reverse,linkas,title,cost,raw_type) {
	if (SELL_ACTIVE) {
		quick_sell(TYPE);
		return false;
	}
	ITEM_LINK=linkas;
	ITEM_RAW_TYPE=raw_type;
	ITEM_DROPS=Array();
	var bb=P('Content').cumulativeScrollOffset();
	ITEM_START_SCROLL=bb;
	$$('.item_drop').each(function (s) {
		var pos=s.cumulativeOffset();
		ITEM_DROPS.push({
			x: pos[0]-bb[0],
			y: pos[1]-bb[1],
			id: s.id
		});
	});
	ITEM_OB=ob;
	ITEM_TYPE=TYPE;
	ITEM_TITLE=title;
	ITEM_COST=cost;
	ITEM_REVERSE=reverse;
	startX=tmpX;
	startY=tmpY;
	ITEM_DRAG=true;
	Position.relativize(ITEM_OB);
	ITEM_POS=ITEM_OB.cumulativeOffset();
	ITEM_BOX_POS=P('Content').cumulativeOffset();
}
function item_mousemove(e) {
	var X=Event.pointerX(e);
	var Y=Event.pointerY(e);
	if (ITEM_DRAG) {
		var bb=P('Content').cumulativeScrollOffset();

		ITEM_OB.style.left=String(X-ITEM_POS[0]-(startX-ITEM_POS[0])+bb[0]-ITEM_START_SCROLL[0])+"px";
		ITEM_OB.style.top=String(Y-ITEM_POS[1]-(startY-ITEM_POS[1])+bb[1]-ITEM_START_SCROLL[1])+"px";
	}
}
function item_mouseup() {
	if ((startX!=tmpX) || (startY!=tmpY) && ITEM_DRAG) {
		var FROM;
		var TO;
		var SEND=false;
		if (ITEM_OB) {
			var bb=P('Content').cumulativeScrollOffset();
			ITEM_DRAG=false;
			ITEM_OB.style.left="0px";
			ITEM_OB.style.top="0px";
			ITEM_DROPS.each(function (s) {
				if (
					(s.x-bb[0]+ITEM_START_SCROLL[0]<tmpX) &&
					(s.x+42-bb[0]+ITEM_START_SCROLL[0]>tmpX) &&
					(s.y-bb[1]+ITEM_START_SCROLL[1]<tmpY) &&
					(s.y+42-bb[1]+ITEM_START_SCROLL[1]>tmpY)
					)
				{
					var a=s.id.split("DROP-");
					item_set_drop(a[1]);
					SEND=true;
				}
				P(s.id).removeClassName('item_drop_a');
				if (SEND) {
					FROM=(ITEM_REVERSE)? ITEM_DROP : ITEM_TYPE;
					TO=(!ITEM_REVERSE)? ITEM_DROP : ITEM_TYPE;
				}
			});
			if (SEND && (FROM!=TO)) {
				content('/page/item/actionas/place',{ 'type' : FROM, 'place' : TO}, no);
			}
		}
	} else if (ITEM_TYPE) {
		show_item_menu(ITEM_TYPE);
	}
	ITEM_DRAG=false;
	ITEM_OB=null;
	ITEM_TYPE=null;
}
function item_set_drop(TYPE) {
	ITEM_DROP=TYPE;
}
function do_clock(ob) {
 var thistime= new Date()
 var hours=thistime.getHours()
 var minutes=thistime.getMinutes()
 var seconds=thistime.getSeconds()
 if (eval(hours) <10) {hours="0"+hours}
 if (eval(minutes) < 10) {minutes="0"+minutes}
 if (seconds < 10) {seconds="0"+seconds}
 ob.update(hours+":"+minutes+":"+seconds);
}
var SEX;
function chose_sex(sex) {
	SEX=sex;
	check_imagebox(10,P('sex_'+sex));
}
var MAP;
function chose_map(map) {
	MAP=map;
	check_imagebox(15,P('map_'+map));
}
function mob_info(ob) {
	var text='<div style="font-size:20px;font-weight:bold">'+ob.title+'</div>';
	text+='<div>Lygis: '+ob.lvl+'</div>';
	text+='<div>Gyvybių: '+ob.hp+'</div>';
	text+='<div>Žala: '+ob.dmg_min+' - '+ob.dmg_max+'</div>';
	text+='<div>Šarvai: '+ob.armor+'</div>';
	text+='<input type="button" value="Medžioti"   onclick="content(SAVED_ADR,{ hunt : '+ob.id+'},no);">';
	P('mobinfo').update(text);
}
function get_new(id,title) {
	new Ajax.Request('/ajax/getnew',{
		parameters: {nid : id},
		onComplete: function (DATA) {
			var text=DATA.responseText;

			var idialog = new Dialog({
				title: title,
				content: "<div style='width:500px'>" + text + "</div>"
			});
			idialog.open();
		}
	});
}
function setdrink(num) {
	DRINK=num;
}
function sendoney() {
	var text="<div>Suma:</div>";
	text+='<div><input id="money-num" type="text" /></div>';
	text+='<div style="text-align:center"><input id="send_money" type="button" value="Papildyti" />';
	text+='<input id="send_close" type="button" value="Išjungti" /></div>';

	var dialogas=new Dialog({
		title: 'Papilditi klano iždą',
		content: text,
		afterOpen: function () {
			P('send_money').observe('click', function () {
				content('/page/clan',{ 'send_money_num' : $F('money-num') }, no);
				Dialogs.close();
			});
			P('send_close').observe('click', function () {
				Dialogs.close();
			});
		}
	});
	dialogas.open();
}
function quick_sell(type) {
	new Ajax.Request('/ajax/quicksell',{
		parameters: {'type' : type},
		onComplete: function (DATA) {
			var text=DATA.responseText;
			if (is_number(text)) {
				set_sensor( { 'money' : text } );
				P('DRAG-'+type).remove();
			} else {
				Dialogs.alert(text);
			}
		}
	});
}
var SELL_ACTIVE=false;
var WAS_ACTIVATED=false;
function quick_sell_activate() {
	if (SELL_ACTIVE) {
		P('SELL_IMG').hide();
		SELL_ACTIVE=false;
		P('QUICK_SELL_BUTTON').value="Aktyvuoti greitą pardavimą";
	} else {
		P('SELL_IMG').show();
		SELL_ACTIVE=true;
		P('QUICK_SELL_BUTTON').value="Deaktyvuoti greitą pardavimą";
		if (!WAS_ACTIVATED) {
			Event.observe(document, 'mousemove', quick_sell_move);
			WAS_ACTIVATED=true;
			P('SELL_IMG').style.top=(tmpY+12)+"px";
			P('SELL_IMG').style.left=(tmpX+12)+"px";
		}
	}
}
function quick_sell_move(e) {
	X=Event.pointerX(e);
	Y=Event.pointerY(e);

	P('SELL_IMG').style.top=(Y+12)+"px";
	P('SELL_IMG').style.left=(X+12)+"px";
}
function is_number(a_string) {
tc = a_string.charAt(0);
if (tc == "0" || tc == "1" || tc == "2" || tc == "3" ||	tc == "4" || tc == "5" || tc == "6" || tc == "7" || tc == "8" || tc == "9") {
return true;
}
else {
return false;
   }
}
function insertAtCursor(myField, myValue) {
  //IE support
  if (document.selection) {
	myField.focus();
	sel = document.selection.createRange();
	sel.text = myValue;
  }
  //MOZILLA/NETSCAPE support
  else if (myField.selectionStart || myField.selectionStart == 0) {
	var startPos = myField.selectionStart;
	var endPos = myField.selectionEnd;
	myField.value = myField.value.substring(0, startPos)
				  + myValue
				  + myField.value.substring(endPos, myField.value.length);
  } else {
	myField.value += myValue;
  }
}
function smileadd(code) {
	insertAtCursor(P('enterchat'),code);
}
function transfer_safe(getas) {
	var num = $F('safe_num');

	if (getas)
		num = -1 * num;

	new Ajax.Request('/ajax/transferfromsafe', {
		parameters: {'num' : num },
		onComplete: function (DATA) {
			var data = DATA.responseText;
			if (data.isJSON()) {
				var a = data.evalJSON();
				set_sensor({ 'money' : a.money});
				P('safe_num_indicator').update(a.safe);
			} else {
				Dialogs.alert(DATA.responseText);
			}
		}
	});
}
function pagergo(adr, page) {
	content(go=str_replace("PP",String(page), adr));
}
function view_money_back(uid) {
	var text="<div>Suma:</div>";
	text+='<div><input id="num" type="text" /></div>';
	text+='<div style="text-align:center"><input id="send_money" type="button" value="Pervesti" />';
	text+='<input id="send_close" type="button" value="Išjungti" /></div>';

	var dialogas=new Dialog({
		title: 'Pervesti žaidėjui pinigų iš klano iždo (bus tiakomas 5% komisinis)',
		content: text,
		afterOpen: function () {
			P('send_money').observe('click', function () {
				content('/page/clan',{
					'send_money_out' : $F('num'),
					'player' : uid,
					'money_out' : 1
				}, no);
				Dialogs.close();
			});
			P('send_close').observe('click', function () {
				Dialogs.close();
			});
		}
	});
	dialogas.open();
}

var agui = null;

function destroyAtack() {
	agui=null;
}

var FRIEND_LIST;
var IGNORE_LIST;

function select_emblem(ob) {
	if (ob.hasClassName('select-emblem-1')) {
		ob.removeClassName('select-emblem-1');
		return;
	}
		
	if (ob.hasClassName('select-emblem-2')) {
		ob.removeClassName('select-emblem-2');
		return;
	}
	
	if ($$('.select-emblem-1').size() == 0)
		ob.addClassName('select-emblem-1');
	else if ($$('.select-emblem-2').size() == 0)
		ob.addClassName('select-emblem-2');
		
	if ($$('.select-emblem-1').size() && $$('.select-emblem-2').size()) { 
		new Ajax.Request('/ajax/workshop2', {
			parameters : { ob1 : $$('.select-emblem-1')[0].id.split('-')[1], ob2 : $$('.select-emblem-2')[0].id.split('-')[1] },
			method : "POST",
			onComplete: function (DATA) {
				var text = DATA.responseText;
				if (is_number(text)) {
					P('pay-money').value = text + ' agonų';
					P('pay-money').show();
					P('pay-nano').show();
				} else {
					Dialogs.alert(text);
				}
			}			
		})
	}
}
function join_emblem(tipas) {
	if (!($$('.select-emblem-1').size() && $$('.select-emblem-2').size())) return;
	var i1 = $$('.select-emblem-1')[0].id.split('-')[1];
	var i2 = $$('.select-emblem-2')[0].id.split('-')[1]; 
	
	content('/page/workshop/place/2', { i1 : i1, i2 : i2, tipas: tipas}, no);
}
function workshop3(ob) {
	if (ob.hasClassName('select-emblem-1')) return;
	
	if ($$('.select-emblem-1').size())
		$$('.select-emblem-1')[0].removeClassName('select-emblem-1');
		
	ob.addClassName('select-emblem-1');
	
	new Ajax.Request('/ajax/workshop3', {
		parameters : {ob : ob.id.split('-')[1]},
		method : "POST",
		onComplete: function (DATA) {
			var text = DATA.responseText;
			if (is_number(text)) {
				P('change-emblem').value = text + " agonų";
				P('change-emblem').show();
				P('change-emblem2').show();
			} else {
				Dialogs.alert(text);
			}
		}
	});
}
function changeEmblems(tipas) {
	if (!$$('.select-emblem-1').size()) return;
	
	var i = $$('.select-emblem-1')[0].id.split('-')[1];
	content('/page/workshop/place/3', {i : i, tipas : tipas}, no);
}

function refreshSwatch() {
	var value = $("#work-laikas").slider("value");
	$("#work-laikas-display").html(value);
}