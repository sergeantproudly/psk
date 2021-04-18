var currPopup=null;
function Popup(popupCode,widthType,info){
	var self=this;
	this.popup=null;
	this.popupTop=null;
	this.icoClose=null;
	this.fixed=true;
	this.popupCode='';
	this.widthType=0;
	this.info=info?info:{};
	
	this.fixPopup=function(){
		if(!isIe6()){
			//self.popup.style.position='fixed';
			self.popup.style.position='absolute';
			self.fixed=false;
		}else{
			self.popup.style.position='absolute';
			self.fixed=false;
		}
	}
	
	this.posPopup=function(){
		var t=0,l=0,w=0,h=0;
		w = parseInt(Math.round(getClientWidth()));
		h = parseInt(Math.round(getClientHeight()));
		var scrollTop = parseInt(self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop));
		var size=getSize(self.popup);
		l=Math.round(w/2 - size.w/2);
		if(self.popup.offsetHeight<h){
			if(self.fixed){
				t=Math.round(h/2 - size.h/2);
			}else{
				t=scrollTop + Math.round(h/2 - size.h/2);
			}
		}else{
			self.popup.style.position='absolute';
			t=scrollTop + 40;
		}			
		setPosition(self.popup,{t: t,l: l});
	}
	
	this.stylizePopup=function(){
		var contentHolder=domFC(domFC(domSC(self.popup,'m')));
		var size=getSize(contentHolder);
		if(size.h>=161){
			removeClass(contentHolder,'white');
			removeClass(domSC(self.popup,'b'),'white');
		}
	}
	
	this.showPopup=function(){
		self.popup.style.visibility='hidden';
		self.popup.style.display='block';
		self.posPopup();
		//self.stylizePopup();
		showCover();
		self.popup.style.visibility='visible';
	}
	
	this.hidePopup=function(){
		hide(popup);
		hideCover();
		currPopup=null;
	}
	
	this.destroyPopup=function(){
		domD(self.popup);
		hideCover();
		currPopup=null;
	}
	
	this.init=function(popupCode){
		/*
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var innerText=ajaxObj.request.responseText;
			var tempPar=domC('div');
			if(innerText)tempPar.innerHTML=innerText;
			currPopup=self.popup=domFC(tempPar);
			self.popupTop=domFC(self.popup);
			self.icoClose=domSTC(self.popupTop,'img');
			self.icoClose.onclick=self.destroyPopup;
			document.body.appendChild(self.popup);
			self.popup.style.width=self.widthType+'px';
			envInitElements(self.popup);
			if(self.fixed){
				self.fixPopup();
			}
			self.showPopup();
		}
		self.info['code']=popupCode;
		ajaxObj.sendData(self.info,'index.php?module=ajax&act=GetPopup');
		*/
		self.info['code']=popupCode;
		$.ajax({
			type: 'POST',
			url: 'index.php?module=ajax&act=GetPopup',
			data: self.info,
			dataType: 'html',			
			success: function(response){				
				var innerText=response;
				var tempPar=domC('div');
				if(innerText)tempPar.innerHTML=innerText;
				currPopup=self.popup=domFC(tempPar);
				self.popupTop=domFC(self.popup);
				self.icoClose=domSTC(self.popupTop,'img');
				self.icoClose.onclick=self.destroyPopup;
				document.body.appendChild(self.popup);
				self.popup.style.width=self.widthType+'px';
				envInitElements(self.popup);
				if(self.fixed){
					self.fixPopup();
				}
				self.showPopup();
			}
		});
		
	}
	if(currPopup)domD(currPopup);
	this.popupCode=popupCode;
	this.widthType=widthType>0?widthType:400;
	this.init(self.popupCode);
}

function showCover(){
	var cover=gei('cover');
	setTransparent(cover);
	show(cover);
	animAppear(cover,300,0.65);
}	
function hideCover(){
	var cover=gei('cover');
	animDisappear(cover,300,0,function(){
		hide(cover);
	});
}
var currContextPopup=null;
function ContextPopup(popupCode,context,widthType,direction,info){
	var self=this;
	this.popup=null;
	this.context=null;
	this.direction='down';
	this.icoClose=null;
	this.inner=null;
	this.arrow=null;
	this.popupCode='';
	this.widthType=0;
	this.info=info?info:{};
	
	this.setContext=function(context){
		if(typeof(context)=='string')
			self.context=gei(context);
		else
			self.context=context;
		return self.context;
	}
	
	this.posPopup=function(){
		var t=0,l=0,w=0,h=0;
		w = parseInt(Math.round(getClientWidth()));
		h = parseInt(Math.round(getClientHeight()));
		var pos=getOffset(self.context);
		var size=getSize(self.popup);
		var sizeContext=getSize(self.context);
		var oversize=0;
		switch(self.direction){
			case 'down':
				if(pos.l+Math.round(sizeContext.w/2)-Math.round(size.w/2)<0){
					oversize=pos.l-Math.round(sizeContext.w/2)+Math.round(size.w/2);
				}else if(pos.l+Math.round(sizeContext.w/2)+Math.round(size.w/2)>w){
					oversize=w-pos.l-Math.round(sizeContext.w/2)-Math.round(size.w/2);
				}
				if(pos.t+sizeContext.h+4+size.h>h){
					addClass(self.arrow,'arr-b');
					setPosition(self.popup,{t: pos.t-size.h-4, l: pos.l-Math.round(size.w/2)+Math.round(sizeContext.w/2)+oversize});
				}else{
					addClass(self.arrow,'arr-t');
					setPosition(self.popup,{t: pos.t+sizeContext.h+4, l: pos.l-Math.round(size.w/2)+Math.round(sizeContext.w/2)+oversize});
				}
				if(oversize){
					self.arrow.style.left=(Math.round(size.w/2)-15-oversize)+'px';
					self.arrow.style.marginLeft='0px';
				}
				break;
				
			case 'up':
				if(pos.l+Math.round(sizeContext.w/2)-Math.round(size.w/2)<0){
					oversize=pos.l-Math.round(sizeContext.w/2)+Math.round(size.w/2);
				}else if(pos.l+Math.round(sizeContext.w/2)+Math.round(size.w/2)>w){
					oversize=w-pos.l-Math.round(sizeContext.w/2)-Math.round(size.w/2);
				}
				if(pos.t-size.h-4<0){
					addClass(self.arrow,'arr-t');
					setPosition(self.popup,{t: pos.t+sizeContext.h+4, l: pos.l-Math.round(size.w/2)+Math.round(sizeContext.w/2)+oversize});
				}else{
					addClass(self.arrow,'arr-b');
					setPosition(self.popup,{t: pos.t-size.h-4, l: pos.l-Math.round(size.w/2)+Math.round(sizeContext.w/2)+oversize});
				}
				if(oversize){
					self.arrow.style.left=(Math.round(size.w/2)-15-oversize)+'px';
					self.arrow.style.marginLeft='0px';
				}
				break;
					
			case 'right':
				if(pos.l+Math.round(sizeContext.w/2)+Math.round(size.w/2)>w){
					addClass(self.arrow,'arr-r');
					setPosition(self.popup,{t: pos.t-Math.round(size.h/2)+Math.round(sizeContext.h/2), l: pos.l-size.w+4});
				}else{
					addClass(self.arrow,'arr-l');
					setPosition(self.popup,{t: pos.t-Math.round(size.h/2)+Math.round(sizeContext.h/2), l: pos.l+sizeContext.w-4});
				}
				
				break;
				
			case 'left':
				if(pos.l+Math.round(sizeContext.w/2)-Math.round(size.w/2)<0){
					addClass(self.arrow,'arr-l');
					setPosition(self.popup,{t: pos.t-Math.round(size.h/2)+Math.round(sizeContext.h/2), l: pos.l+sizeContext.w-4});
				}else{
					addClass(self.arrow,'arr-r');
					setPosition(self.popup,{t: pos.t-Math.round(size.h/2)+Math.round(sizeContext.h/2), l: pos.l-size.w+4});
				}
				break;
				
			default:
				alert('Error of positioning');
		}
	}
	
	this.showPopup=function(){
		self.popup.style.visibility='hidden';
		self.popup.style.display='block';
		self.posPopup();
		self.popup.style.visibility='visible';
	}
	
	this.hidePopup=function(){
		hide(popup);
		currPopup=null;
	}
	
	this.destroyPopup=function(){
		domD(self.popup);
		currPopup=null;
	}
	
	this.init=function(popupCode){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var innerText=ajaxObj.request.responseText;
			var tempPar=domC('div');
			if(innerText)tempPar.innerHTML=innerText;
			currPopup=self.popup=domFC(tempPar);
			self.popupTop=domFC(self.popup);
			self.icoClose=domFC(self.popupTop);
			self.icoClose.onclick=self.destroyPopup;
			self.arrow=domC('div');
			domAL(self.arrow,self.popup);
			document.body.appendChild(self.popup);
			envInitElements(self.popup);
			self.showPopup();
		}
		self.info['code']=popupCode;
		self.info['width']=self.widthType;
		ajaxObj.sendData(self.info,'ajax-act-GetPopup.html');
		
	}
	if(currPopup)domD(currPopup);
	this.popupCode=popupCode;
	if(widthType)this.widthType=widthType;
	this.setContext(context);
	if(direction)this.direction=direction;
	this.init(self.popupCode);
	
}

var currHint=null;
function Hint(context,content,widthType){
	var self=this;
	this.hint=null;
	this.context=null;
	this.contentHolder=null;
	this.widthType=0;
	
	this.setContext=function(context){
		if(typeof(context)=='string')
			self.context=gei(context);
		else
			self.context=context;
		return self.context;
	}
	
	this.posHint=function(){
		var pos=getOffset(self.context);
		var size=getSize(self.hint);
		var sizeContext=getSize(self.context);
		setPosition(self.hint,{t: pos.t-14, l: pos.l+sizeContext.w+4});
	}
	
	this.showHint=function(){
		self.hint.style.visibility='hidden';
		self.hint.style.display='block';
		self.posHint();
		self.hint.style.visibility='visible';
	}
	
	this.hideHint=function(){
		hide(self.hint);
		currHint=null;
	}
	
	this.destroyHint=function(){
		domD(self.hint);
		currHint=null;
	}
	
	this.buildHint=function(){
		self.hint=domC('div','hint')
		
		var t=domC('div','t');
		var lt=domC('div','lt');
		var rt=domC('div','rt');
		domAL(lt,t);
		domAL(rt,t);
		
		var m=domC('div','m');
		var mm=domC('div','m');
		self.contentHolder=domC('div','content');
		domAL(self.contentHolder,mm);
		domAL(mm,m);
		
		var b=domC('div','b');
		var lb=domC('div','lb');
		var rb=domC('div','rb');
		domAL(lb,b);
		domAL(rb,b);
		
		var arr=domC('div','arr');
		
		domAL(t,self.hint);
		domAL(m,self.hint);
		domAL(b,self.hint);
		domAL(arr,self.hint);
		
		document.body.appendChild(self.hint);
	}
	
	this.getContentHolder=function(){
		return self.contentHolder;
	}
	
	this.init=function(){	
		self.buildHint();
		self.contentHolder.innerHTML=content;
		self.hint.style.width=widthType+'px';
		if(currHint)domD(currHint);
		currHint=self.hint;
		self.showHint();	
	}
	this.context=context;
	if(widthType)this.widthType=widthType;
	this.init();
	
	return self;
}