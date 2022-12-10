// ENVIRONMENT
var envActiveElement=null;
function envInit(){
	if(isIe6()){
		ie6Belated();
	}
	envInitElements();
	envInitBody();
	envPreload();
	
	$('.spoiler-splitter').click(function(){
		$(this).hide().next('span').show();
	});
	
	if($('ul.tabs').length){
		$(window).unbind('scroll'); //?
		
		$('ul.tabs>li').click(function(){
			if($(this).hasClass('active'))return true;
			var id=$(this).attr('id').substring(4);
			$(this).addClass('active').siblings('li').removeClass('active');
			$('#tab-content-'+id).show().siblings('div:visible').hide();
			window.location.hash='tab-'+id;
		});
		
		var hash=getHash();
		if(hash.indexOf('tab-')+1>0){
			var m=hash.match(/tab\-(\d+)/i);
			var id=m[1];
			$('#tab-'+id).addClass('active').siblings('li').removeClass('active');
			$('#tab-content-'+id).show().siblings('div:visible').hide();
		}
	}
}
function envInitBody(){
	var body=document.body;
	addHandler(body,'mouseup',function(e){
		if(!e)e=window.event;
		var target=e.target || e.srcElement;
		var realTarget=target.parentNode;
		
		if(envActiveElement){
			if(realTarget==envActiveElement){
				envButtonState2(envActiveElement);
				envActiveElement=null;
			}else{
				envButtonState1(envActiveElement);
				envActiveElement=null;
			}
		}
		if(envCurrentSel){
			if(!domIP(envCurrentSel,target)){
				envSelectClose(envCurrentSel);
			}
		}
		if(envCurrentMultiSel){
			if(!domIP(envCurrentMultiSel,target)){
				envSelectClose(envCurrentMultiSel);
			}
		}
	});
}
function envInitElements(obj){
	if(!obj)obj=document;
	var inp=obj.getElementsByTagName('input');
	var element=null;
	for(var i=0;i<inp.length;i++){
		if(element=inp[i].parentNode){
			
			// button element
			if(hasClass(element,'btn')){
				envSetButtonHandlers(element);
			}
			
			// select element
			else if(hasClass(element,'inp-select') && inp[i].type!='hidden'){
				envSetSelectHandlers(element);
			}
			
			// multiselect element
			else if(hasClass(element,'inp-multiselect') && inp[i].type!='hidden'){
				envSetMultiSelectHandlers(element);
			}
			
			// checkbox element
			else if(hasClass(element,'inp-checkbox')){
				envSetCheckboxHandlers(element);
			}
			
			// radiobutton element
			else if(hasClass(element,'inp-radiobutton')){
				envSetRadiobuttonHandlers(element);
			}
			
			// date element
			else if(hasClass(element,'inp-date')){
				envInitDatepicker(element);
			}

		}
		
	}
	var inp=obj.getElementsByTagName('textarea');
	var element=null;
	for(var i=0;i<inp.length;i++){
		if(element=inp[i].parentNode){
			
			// wysiwyg editor
			if(hasClass(element,'inp-wysiwyg')){
				envInitWysiwyg(element);
			}
			
		}
	}
	var form=obj.getElementsByTagName('form');
	var formHolder=null;
	for(i=0;i<form.length;i++){
		if(formHolder=domSC(form[i],'form')){
			
			// form holder
			envSetFormHandlers(formHolder);
			
		}
	}
	var table=obj.getElementsByTagName('table');
	for(i=0;i<table.length;i++){
		if(hasClass(table[i],'styled')){
			
			// table
			envSetTableHandlers(table[i]);
			
		}
	}
}
// ENVIRONMENT - BUTTON
function envSetButtonHandlers(btn){
	addHandler(btn,'mouseout',function(){
		if(!envActiveElement){
			envButtonState1(btn);
		}
	});
	addHandler(btn,'mouseover',function(){
		if(!envActiveElement){
			envButtonState2(btn)
		}
	});
	addHandler(btn,'mousedown',function(){
		envButtonState3(btn)
		envActiveElement=btn;
	});
}
function envButtonState1(btn){
	var l=domFC(btn);
	var r=domLC(btn);
	var c=domSTC(btn,'input');
	l.style.backgroundPosition='0 0';
	r.style.backgroundPosition='0 0';
	c.style.backgroundPosition='0 0';
}
function envButtonState2(btn){
	var l=domFC(btn);
	var r=domLC(btn);
	var c=domSTC(btn,'input');
	l.style.backgroundPosition='0 -30px';
	r.style.backgroundPosition='0 -30px';
	c.style.backgroundPosition='0 -30px';
}
function envButtonState3(btn){
	var l=domFC(btn);
	var r=domLC(btn);
	var c=domSTC(btn,'input');
	l.style.backgroundPosition='0 -60px';
	r.style.backgroundPosition='0 -60px';
	c.style.backgroundPosition='0 -60px';
}
function envButtonState4(btn){
	var l=domFC(btn);
	var r=domLC(btn);
	var c=domSTC(btn,'input');
	l.style.backgroundPosition='0 -90px';
	r.style.backgroundPosition='0 -90px';
	c.style.backgroundPosition='0 -90px';
}
function envButtonDisable(btn){
	addClass(btn,'disabled');
	envButtonState4(btn);
	btn.onmouseover=null;
	btn.onmouseout=null;
	btn.onmousedown=null;
	var c=domSTC(btn,'input');
	c.disabled=true;
}
function envButtonActivate(btn){
	removeClass(btn,'disabled');
	envSetButtonHandlers(btn);
	var c=domSTC(btn,'input');
	c.disabled=false;
}

// ENVIRONMENT - CHECKBOX
function envSetCheckboxHandlers(cb){
	addHandler(cb,'click',function(){
		envCheckboxToggle(this);
	});
}
function envCheckboxBlur(cb){
	addClass(cb,'cb-blured');
}
function envCheckboxCheck(cb){
	addClass(cb,'cb-checked');
}
function envCheckboxUncheck(cb){
	removeClass(cb,'cb-checked');
}
function envCheckboxToggle(cb){
	if(hasClass(cb,'cb-checked')){
		envCheckboxUncheck(cb);
	}else{
		envCheckboxCheck(cb);
	}
}

// ENVIRONMENT - RADIOBUTTON
var envCurrentRb=new Array();
function envSetRadiobuttonHandlers(rb){
	addHandler(rb,'click',function(){
		envRadiobuttonSet(this);
	});
}
function envRadiobuttonCheck(rb){
	addClass(rb,'rb-checked');
}
function envRadiobuttonUncheck(rb){
	removeClass(rb,'rb-checked');
}
function envRadiobuttonClearAll(rb){
	var inp=domFC(rb);
	var family=inp.name;
	var form=domP(rb);
	while(form){
		if(form.tagName&&form.tagName.toLowerCase()=='form'){
			for(var i=0;i<form[family].length;i++){
				envRadiobuttonUncheck(domP(form[family][i]));
			}
			return true;
		}
		form=domP(form);
	}
	return false;
}
function envRadiobuttonSet(rb){
	/*
	var inp=domFC(rb);
	var family=inp.name;
	var form=envRadiobuttonGetForm(rb);
	*/
	envRadiobuttonClearAll(rb);
	/*
	if(envCurrentRb[family]){
		envRadiobuttonUncheck(envCurrentRb[family]);
	}
	envCurrentRb[family]=rb;
	*/
	envRadiobuttonCheck(rb);
}
/*
function envRadiobuttonInitCurrent(rb){
	var inp=domFC(rb);
	var family=inp.name;
	envCurrentRb[family]=rb;
}
*/

// ENVIRONMENT - SELECT
var envCurrentSel=null;
var envCurrentSelItem=null;
var envSelItemSetted=false;
var envZPosition1=100;
var envZPosition2=101;
function envSetSelectHandlers(sel){
	var dropdown=domSC(sel,'dropdown');
	var inp=domNN(domFC(sel));
	addHandler(sel,'click',function(){
		if(envCurrentSel&&envCurrentSel!=this){
			envSelectClose(envCurrentSel);
		}
		envSelectToggle(this);
	});
	var item=domFC(dropdown);
	while(item){
		if(hasClass(item,'item')){
			addHandler(item,'click',function(){
				envSelectItemSet(this);
			});
			addHandler(item,'mouseover',function(){
				envSelectItemOver(this);
			});
			addHandler(item,'mouseout',function(){
				envSelectItemOut(this);
			});
		}
		item=domNN(item);
	}
	addHandler(sel,'keydown',function(e){
		if(!e)e=window.event;
		var keyCode=e.keyCode;
		if(isBlock(dropdown)){
			if(keyCode==37||keyCode==27||keyCode==9){
				envSelectClose(this);
			}else if(keyCode==40){
				if(!envCurrentSelItem){
					envCurrentSelItem=domSC(dropdown,'placeholder');
				}
				envSelectItemNext(envCurrentSelItem);
			}else if(keyCode==38){
				envSelectItemPrev(envCurrentSelItem);
			}else if(keyCode==13){
				envSelItemSetted=true;
				envSelectItemSet(envCurrentSelItem);
				envSelectClose(this);
				return false;
			}
		}else{
			if(keyCode==39||keyCode==40){
				envSelectOpen(this);
			}
		}
	});
}
function envSelectOpen(sel){
	var dropdown=domSC(sel,'dropdown');
	var l=domSC(sel,'l');
	var toggler=domSC(sel,'r');
	var inp=domNN(l);
	setZPosition(sel,envZPosition1);
	setZPosition(l,envZPosition2);
	setZPosition(toggler,envZPosition2);
	setZPosition(inp,envZPosition2);
	setZPosition(dropdown,envZPosition1);
	envCurrentSel=sel;
	envSelItemSetted=false;
	envCurrentSelItem=domSC(dropdown,'current');
	show(dropdown);
	if($(dropdown).height()>220){
		//var html=$(dropdown).html();
		//$(dropdown).html('<div class="inner">'+html+'</div>');
	    //$(dropdown).children('.inner').height(220).jScrollPane({
    	$(dropdown).height(220).jScrollPane({
    		scrollbarWidth: 5,
    		mouseWheelSpeed: 50,
			showArrows: false
		});
	}
	addClass(toggler,'r-active');
}
function envSelectClose(sel){
	var dropdown=domSC(sel,'dropdown');
	var l=domSC(sel,'l');
	var toggler=domSC(sel,'r');
	var inp=domNN(l);
	hide(dropdown);
	removeClass(toggler,'r-active');
	resetZPosition(sel);
	resetZPosition(l);
	resetZPosition(toggler);
	resetZPosition(inp);
	resetZPosition(dropdown);
	envCurrentSel=null;
}
function envSelectToggle(sel){
	var dropdown=domSC(sel,'dropdown');
	if(isBlock(dropdown)){
		envSelectClose(sel);
	}else{
		envSelectOpen(sel);
	}
}
function envSelectItemSet(sender){
	var dropdown=$(sender).closest('.dropdown').get(0);
	var sel=dropdown.parentNode;
	var inp=domNN(domFC(sel));
	var hidden=domNN(inp);
	var current=domSC(dropdown,'current');
	removeClass(current,'current');
	inp.value=sender.innerHTML;
	hidden.value=sender.getAttribute('value');
	addClass(sender,'current');
}
function envSelectItemOver(sender){
	addClass(sender,'over');
	envCurrentSelItem=sender;
}
function envSelectItemOut(sender){
	removeClass(sender,'over');
	envCurrentSelItem=null;
}
function envSelectItemPrev(sender){
	var newItem=domPN(sender);
	envSelectItemOut(sender);
	if(!newItem||!hasClass(newItem,'item')){
		var dropdown=domP(sender);
		var newItem=domSC(dropdown,'item',true);
	}
	envSelectItemOver(newItem);
	envSelectItemSet(newItem);
}
function envSelectItemNext(sender){
	var newItem=domNN(sender);
	envSelectItemOut(sender);
	if(!newItem||!hasClass(newItem,'item')){
		var dropdown=domP(sender);
		var newItem=domSC(dropdown,'item');
	}
	envSelectItemOver(newItem);
	envSelectItemSet(newItem);
}


// ENVIRONMENT - MULTISELECT
var envCurrentMultiSel=null;
var envCurrentMultiSelItem=null;
var envMultiSelItemSetted=false;
var envZPosition1=100;
var envZPosition2=101;
function envSetMultiSelectHandlers(sel){
	var dropdown=domSC(sel,'dropdown');
	var inp=domNN(domFC(sel));
	addHandler(sel,'click',function(e){
		if(envCurrentMultiSel&&envCurrentMultiSel!=this){
			envMultiSelectClose(envCurrentMultiSel);
		}
		if(!e)e=window.event;
		var target=e.target || e.srcElement;
		var realTarget=target.parentNode;
		
		if(!domIP(dropdown,target)){
			envMultiSelectToggle(this);
		}
	});
	var item=domFC(dropdown);
	while(item){
		if(hasClass(item,'item')){
			addHandler(item,'click',function(){
				envMultiSelectItemSet(this);
			});
			addHandler(item,'mouseover',function(){
				envMultiSelectItemOver(this);
			});
			addHandler(item,'mouseout',function(){
				envMultiSelectItemOut(this);
			});
		}
		item=domNN(item);
	}
	addHandler(sel,'keydown',function(e){
		if(!e)e=window.event;
		var keyCode=e.keyCode;
		if(isBlock(dropdown)){
			if(keyCode==37||keyCode==27||keyCode==9){
				envMultiSelectClose(this);
			}else if(keyCode==40){
				if(!envCurrentMultiSelItem){
					envCurrentMultiSelItem=domSC(dropdown,'placeholder');
				}
				envMultiSelectItemNext(envCurrentMultiSelItem);
			}else if(keyCode==38){
				envMultiSelectItemPrev(envCurrentMultiSelItem);
			}else if(keyCode==13){
				envMultiSelItemSetted=true;
				envMultiSelectItemSet(envCurrentMultiSelItem);
				envMultiSelectClose(this);
				return false;
			}
		}else{
			if(keyCode==39||keyCode==40){
				envMultiSelectOpen(this);
			}
		}
	});
}
function envMultiSelectOpen(sel){
	var dropdown=domSC(sel,'dropdown');
	var l=domSC(sel,'l');
	var toggler=domSC(sel,'r');
	var inp=domNN(l);
	setZPosition(sel,envZPosition1);
	setZPosition(l,envZPosition2);
	setZPosition(toggler,envZPosition2);
	setZPosition(inp,envZPosition2);
	setZPosition(dropdown,envZPosition1);
	envCurrentMultiSel=sel;
	envMultiSelItemSetted=false;
	envCurrentMultiSelItem=domSC(dropdown,'current');
	show(dropdown);
	if($(dropdown).height()>220){
		//var html=$(dropdown).html();
		//$(dropdown).html('<div class="inner">'+html+'</div>');
	    //$(dropdown).children('.inner').height(220).jScrollPane({
    	$(dropdown).height(220).jScrollPane({
    		scrollbarWidth: 5,
    		mouseWheelSpeed: 50,
    		verticalDragMinHeight: 25,
			showArrows: false
		});
	}
	addClass(toggler,'r-active');
}
function envMultiSelectClose(sel){
	var dropdown=domSC(sel,'dropdown');
	var l=domSC(sel,'l');
	var toggler=domSC(sel,'r');
	var inp=domNN(l);
	hide(dropdown);
	removeClass(toggler,'r-active');
	resetZPosition(sel);
	resetZPosition(l);
	resetZPosition(toggler);
	resetZPosition(inp);
	resetZPosition(dropdown);
	envCurrentMultiSel=null;
}
function envMultiSelectToggle(sel){
	var dropdown=domSC(sel,'dropdown');
	if(isBlock(dropdown)){
		envMultiSelectClose(sel);
	}else{
		envMultiSelectOpen(sel);
	}
}
function envMultiSelectItemSet(sender){
	var dropdown=$(sender).closest('.dropdown').get(0);
	var sel=dropdown.parentNode;
	var inp=domNN(domFC(sel));
	var hidden=domNN(inp);
	var current=domSC(dropdown,'current');
	//removeClass(current,'current');
	///inp.value=sender.innerHTML;
	//hidden.value=sender.getAttribute('value');
	//addClass(sender,'current');
	
	$(sender).toggleClass('current');
	var vals=new Array();
	$(dropdown).find('.item.current').each(function(index,item){
		vals.push($(item).attr('value'));
	});
	$(hidden).val(vals.join(','));
	$(inp).val('Выбрано '+vals.length+' '+word125(vals.length,'значение','значения','значений'));
}
function envMultiSelectItemOver(sender){
	addClass(sender,'over');
	envCurrentMultiSelItem=sender;
}
function envMultiSelectItemOut(sender){
	removeClass(sender,'over');
	envCurrentMultiSelItem=null;
}
function envMultiSelectItemPrev(sender){
	var newItem=domPN(sender);
	envMultiSelectItemOut(sender);
	if(!newItem||!hasClass(newItem,'item')){
		var dropdown=domP(sender);
		var newItem=domSC(dropdown,'item',true);
	}
	envMultiSelectItemOver(newItem);
	envMultiSelectItemSet(newItem);
}
function envMultiSelectItemNext(sender){
	var newItem=domNN(sender);
	envMultiSelectItemOut(sender);
	if(!newItem||!hasClass(newItem,'item')){
		var dropdown=domP(sender);
		var newItem=domSC(dropdown,'item');
	}
	envMultiSelectItemOver(newItem);
	envMultiSelectItemSet(newItem);
}

// ENVIRONMENT - FORM
function envSetLabelHandler(label){
	if(typeof(label)=='string')label=gei(label);
	var fld=domNN(label);
	var elem=domFC(fld);
	if(hasClass(elem,'inp-textarea')){
		var inp=domFC(domFC(domSC(elem,'m')));
	}else if(hasClass(elem,'inp-text')){
		var inp=domSTC(elem,'input');
	}else if(hasClass(elem,'ckeditor')){
		envInitWysiwyg();
	}else{
		return true;
	}
	addHandler(label,'click',function(){
		if(inp)inp.focus();
	});
	addHandler(inp,'focus',function(){
		addClass(domPN(domP(domP(this))),'active');
	});
	addHandler(inp,'blur',function(){
		removeClass(domPN(domP(domP(this))),'active');
	});
}
function envSetCapchaHandler(capcha){
	if(typeof(capcha)=='string')formHolder=gei(capcha);
	var inp=domNN(capcha);
	capcha.onclick=function(){
		inp.focus();
	}
}
function envSetFormHandlers(formHolder){
	if(typeof(formHolder)=='string')formHolder=gei(formHolder);	
	var child=domFC(formHolder);
	while(child){
		if(hasClass(child,'label'))envSetLabelHandler(child);
		child=domNN(child);
	}
}
function envIsSubmit(form){
	if(envSelItemSetted){
		envSelItemSetted=false;
		return false;
	}
	return true;
}

// ENVIRONMENT - PRELOAD
function envPreloadImage(src){
	var img=domC('img');
	img.src=src;
	img.alt='';
	domAL(img,gei('preload'));
}
function envPreload(){
	var preload=domC('div');
	preload.id='preload';
	domAL(preload,document.body);
	envPreloadImage('images/popup_lt.png');
	envPreloadImage('images/popup_t.png');
	envPreloadImage('images/popup_rt.png');
	envPreloadImage('images/popup_l.png');
	envPreloadImage('images/popup_r.png');
	envPreloadImage('images/popup_lb.png');
	envPreloadImage('images/popup_b.png');
	envPreloadImage('images/popup_rb.png');
	envPreloadImage('images/popup_c.png');
	envPreloadImage('images/popup_lb_w.png');
	envPreloadImage('images/popup_b_w.png');
	envPreloadImage('images/popup_rb_w.png');
	envPreloadImage('images/ico_close.png');
	envPreloadImage('images/hint_lt.png');
	envPreloadImage('images/hint_t.png');
	envPreloadImage('images/hint_rt.png');
	envPreloadImage('images/hint_l.png');
	envPreloadImage('images/hint_r.png');
	envPreloadImage('images/hint_lb.png');
	envPreloadImage('images/hint_b.png');
	envPreloadImage('images/hint_rb.png');
	envPreloadImage('images/hint_arr.png');
	envPreloadImage('images/loading16.gif');
}

// ENVIRONMENT - TABLE
function envSetTableHandlers(table){
	var tr=domFC(domFC(table));
	var th=domFC(tr);
	var cb=null;
	var hasCb=false;
	if(th&&hasClass(th,'tools')){
		var cb=domSC(th,'inp-checkbox');
		if(cb){
			hasCb=true;
			addHandler(cb,'click',function(){
				var tr=domP(domP(this));
				while(tr=domNN(tr)){
					var cb=envTableGetLineCheckbox(this);
					if(hasClass(this,'cb-checked')){
						envTableLineCheck(tr);
					}else{
						envTableLineUncheck(tr);
					}
				}			
			});
		}
	}
	while(tr=domNN(tr)){
		if(!hasClass(tr,'empty')){
			addHandler(tr,'mouseover',function(){
			addClass(this,'hover');
			});
			addHandler(tr,'mouseout',function(){
				removeClass(this,'hover');
			});
			if(hasCb){
				addHandler(tr,'click',function(e){
					if(!e)e=window.event;
					var target=e.target?e.target:e.srcElement;
					var selfhit=(target.tagName.toLowerCase()=='input');
					envTableLineToggle(this,selfhit);
				});
			}
		}	
	}
}
function envTableGetLineCheckbox(tr){
	var tools=domSC(tr,'tools');
	var cb=domSC(tools,'inp-checkbox');
	return cb;
}
function envTableLineCheck(tr,selfhit){
	addClass(tr,'selected');
	if(!selfhit){
		var cb=envTableGetLineCheckbox(tr);
		envCheckboxCheck(cb);
		domSTC(cb,'input').checked=true;
	}
}
function envTableLineUncheck(tr,selfhit){
	removeClass(tr,'selected');
	if(!selfhit){
		var cb=envTableGetLineCheckbox(tr);
		envCheckboxUncheck(cb);
		domSTC(cb,'input').checked=false;
	}
}
function envTableLineToggle(tr,selfhit){
	var cb=envTableGetLineCheckbox(tr);
	if(hasClass(cb,'cb-checked')){
		if(selfhit)envTableLineCheck(tr,selfhit);
		else envTableLineUncheck(tr,selfhit);
	}else{
		if(selfhit)envTableLineUncheck(tr,selfhit);
		else envTableLineCheck(tr,selfhit);
	}
}

// IE6
function ie6Belated(){
	var fixes='.btn input,';
	fixes+='.inp-text input,';
	fixes+='.popup .t,';
	fixes+='.popup .lt,';
	fixes+='.popup .rt,';
	fixes+='.popup .m,';
	fixes+='.popup .b,';
	fixes+='.popup .lb,';
	fixes+='.popup .rb';
	DD_belatedPNG.fix(fixes);
}

//  ENVIRONMENT - WYSIWYG
var editors={};
document.addEventListener('DOMContentLoaded', function() {
	CKEDITOR.stylesSet.add( 'my_styles', [
		// Block-level styles
		{ name: 'Секция Xl', element: 'div', attributes: {'class': 'text-xl' } },
		{ name: 'Секция Lg', element: 'div', attributes: {'class': 'text-lg' } },
		{ name: 'Текст Xl', element: 'p', attributes: {'class': 'text-xl' } },
		{ name: 'Текст Lg', element: 'p', attributes: {'class': 'text-lg' } },
	]);
});
function envInitWysiwyg(wysiwyg){
	var inp=domFC(wysiwyg);
	invis(inp);
	hide(inp);
	var id=wysiwyg.id?wysiwyg.id:inp.name;
	var config={
		toolbar : [
					['Source', '-', 'Bold', 'Italic', 'Underline', '-', 'Link', 'Unlink','-', 'Image'],
					['NumberedList','BulletedList','Table'],
					['RemoveFormat'],
					['JustifyLeft','JustifyCenter','JustifyRight', 'Format', 'Styles'],
					['Maximize', 'ShowBlocks']
				  ],
		filebrowserBrowseUrl : 'ckeditor/images',
	    filebrowserUploadUrl : 'ckeditor/images',
	    filebrowserImageBrowseUrl : 'ckeditor/images',
		filebrowserImageUploadUrl : 'index.php?module=ajax&act=UploadImage',
	    filebrowserWindowWidth  : 800,
	    filebrowserWindowHeight : 500,
	    language : 'en',
	    extraAllowedContent : 'div(equipment__item,equipment__caption); p(equipment__item,equipment__caption); ul(equipment__list); li(equipment__list-item)',
	    stylesSet : 'my_styles'
	};
	editors[id]=CKEDITOR.appendTo(wysiwyg, config, inp.value);
}

//  ENVIRONMENT - DATEPICKER
var envDatepickerSetDefaults=false;
function envInitDatepicker(datefld){
	var inp=domSTC(datefld,'input');
	var ico=domNN(datefld);
	var dateFormat=hasClass(datefld,'date-only')?'date':(hasClass(datefld,'time-only')?'time':'datetime');
	if(!envDatepickerSetDefaults){
		$.datepicker.setDefaults({
			dateFormat:		'dd.mm.yy'	
		});
		$.datepicker.setDefaults($.datepicker.regional['en-GB']);
		envDatepickerSetDefaults=true;
	}
	
	if(dateFormat=='datetime'){
		$(inp).datetimepicker({
			onSelect:	function(dateText,datepicker){
				$(this).datepicker('hide');
			}
		});
	}else if(dateFormat=='time'){
		$(inp).timepicker({});
	}else{
		$(inp).datepicker({
			onSelect:	function(dateText,datepicker){
				$(this).datepicker('hide');
			}
		});
	}
	
	addHandler(ico,'click',function(){
		var inp=domSTC(domPN(this),'input');
		inp.focus();
	});
}