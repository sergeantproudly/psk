var browser='';
if(/Gecko/.test(navigator.userAgent))browser='gecko';
else if(/Opera/.test(navigator.userAgent))browser='opera';
else if(/MSIE/.test(navigator.userAgent))browser='ie';
/** Common functions */
function gei(id){
	return document.getElementById(id);
}
function hide(obj){
	obj.style.display='none';
}
function show(obj,displayMode){
	obj.style.display=displayMode?displayMode:'block';
}
function showId(id,displayMode){
	gei(id).style.display=displayMode?displayMode:'block';
}
function hideId(id){
	gei(id).style.display='none';
}
function isBlock(obj){
	if(obj.style.display=='block')return true;
	return false;
}
function toggle(obj){
	if(isBlock(obj)){
		hide(obj);
		return 0;
	}else{
		show(obj);
		return 1;
	}
}
function vis(obj){
	obj.style.visibility='visible';
}
function invis(obj){
	obj.style.visibility='hidden';
}
function scroll(obj){
	obj.scrollIntoView();
}
function unsetDefault(obj,value){
	if(obj.value==value){
		obj.value='';
		return true;
	}else return false;
}
function setDefault(obj,value){
	if(obj.value==''){
		obj.value=value;
		return true;
	}else return false;
}
function loadScript(url){
	var head=document.getElementsByTagName('head')[0];
	var nodes=head.getElementsByTagName('script');
	var exists=false;
	for(var i=0;i<nodes.length;i++)
		if(nodes[i].getAttribute('src')==url)exists=true;
	if(!exists){
		var script=document.createElement('script');
		script.setAttribute('type','text/javascript');
		script.setAttribute('src',url);
		head.appendChild(script);
	}
}
function addHandler(obj,event,handler){
	if(typeof obj.addEventListener!='undefined'){
		obj.addEventListener(event,handler,false);
	}else if(typeof obj.attachEvent!='undefined'){
		obj.attachEvent('on'+event,function(){handler.call(obj);});
	}else{
		throw 'Error in function addHandler()';
	}
}
function removeHandler(obj,event,handler){
	if(typeof obj.removeEventListener!='undefined'){
		obj.removeEventListener(event,handler,false);
	}else if(typeof obj.detachEvent!='undefined'){
		obj.detachEvent('on'+event,handler);
	}else{
		throw 'Error in function removeHandler()';
	}
}
var js_tid=null;
function comWaitJs(func,action){
	if(typeof(this.window[func])=='undefined'){
		js_tid=setTimeout(function(){comWaitJs(func,action);},250);
	}else{
		if(js_tid)clearTimeout(js_tid);
		action();
	}
}
var id_tid=null;
function comWaitId(id,action){
	if(!gei(id)||typeof(gei(id))=='undefined'){
		id_tid=setTimeout(function(){comWaitId(id,action);},250);
	}else{
		if(id_tid)clearTimeout(id_tid);
		action();
	}
} 
function hasClass(elem,className){
	if(elem.className && elem.className.indexOf(className)>=0)return true;
	else false;
}
function addClass(elem,className){
	if(!hasClass(elem,className)){
		elem.className+=(elem.className?' ':'')+className;
	}
	return true;
}
function removeClass(elem,className){
	if(hasClass(elem,className)){
		var re=new RegExp(className+'\s?','g');
		elem.className=elem.className.replace(re,'');
	}
	return true;
}
function redirect(url){
	window.location=url;
}
function reload(forceGet){
	//window.location.reload(forceGet);
	window.location.reload();
}
function getOffsetSum(elem){
	var t=0,l=0;
	while(elem){
		t+=t+parseFloat(elem.offsetTop);
		l+=l+parseFloat(elem.offsetLeft);
		elem=elem.offsetParent;
	}
	return {t: Math.round(t), l: Math.round(l)};
}
function getOffsetRect(elem){
	var box=elem.getBoundingClientRect();
	var body=document.body;
	var docElem=document.documentElement;
	var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
	var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
	var clientTop = docElem.clientTop || body.clientTop || 0;
	var clientLeft = docElem.clientLeft || body.clientLeft || 0;
	var t  = box.top +  scrollTop - clientTop;
	var l = box.left + scrollLeft - clientLeft;
	return {t: Math.round(t), l: Math.round(l)};
}
function getOffset(elem){
	if(elem.getBoundingClientRect){
		return getOffsetRect(elem);
	}else{
		return getOffsetSum(elem);
	}
}
function getSize(elem){
	var w=elem.offsetWidth;
	var h=elem.offsetHeight;
	return {w: Math.round(w), h: Math.round(h)};
}
function setSize(elem,size){
	var w=size.w?size.w:size[0];
	var h=size.h?size.h:size[1];
	if(typeof(w)!='undefined')elem.style.width=(w)+'px';
	if(typeof(h)!='undefined')elem.style.height=(h)+'px';
}
function setWidth(elem,width){
	setSize(elem,{'w':width});
}
function setHeight(elem,height){
	setSize(elem,{'h':height});
}
function setPosition(elem,pos){
	elem.style.top=(pos.t?pos.t:pos[0])+'px';
	elem.style.left=(pos.l?pos.l:pos[1])+'px';
}
function getClientWidth(){
  return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientWidth:document.body.clientWidth;
}
function getClientHeight(){
  return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight;
}
function getZPosition(elem){
	return elem.style.zIndex?elem.style.zIndex:0;
}
function setZPosition(elem,pos){
	elem.style.zIndex=pos;
}
function resetZPosition(elem){
	elem.style.zIndex=0;
}
function getBrowser(){
	var browser='';
	if(/Gecko/.test(window.navigator.userAgent))browser='gecko';
	else if(/Opera/.test(window.navigator.userAgent))browser='opera';
	else if(/MSIE/.test(window.navigator.userAgent))browser='ie';
	return browser;
}
function getBrowserVersion(){
	var browser=getBrowser();
	var version='';
	if(browser=='opera'){
		version=window.navigator.userAgent.substr(window.navigator.userAgent.indexOf('Opera')+6,4);
	}else if(browser=='gecko'){
		version=window.navigator.userAgent.substr(window.navigator.userAgent.indexOf('Gecko')+6,8)+ ' ('+ window.navigator.userAgent.substr(8,3) + ')';
	}else if(browser=='ie'){
		version=parseInt(window.navigator.userAgent.substr(window.navigator.userAgent.indexOf('MSIE')+5,3));
	}else{
		version=window.navigator.appName;
	}
	return version;
}
function isIe6(){
	var browser=getBrowser();
	var version=getBrowserVersion();
	return (browser=='ie'&&version=='6');
}
inArray=Array.prototype.indexOf?
	function(arr,val){
		return arr.indexOf(val)!=-1;
	}:
	function(arr,val){
		var i=arr.length;
		while(i--){
			if(arr[i]==val)return true;
		}
		return false;
	}

function evalScripts(html){
	var tempNode=domC('div');
	tempNode.innerHTML=html;
	for(i in tempNode.childNodes){
		var node=tempNode.childNodes[i];
		if(node.tagName && node.tagName.toLowerCase()=='script'){
			eval(node.innerHTML);
		}
	}
}

// MESSAGES
function msgSetWait(node,text,displayType){
	if(!text)text='Подождите, идет загрузка...';
	if(!displayType)displayType='block';
	var waitNode=domC(displayType=='inline'?'span':'div','wait',text);
	domAP(waitNode,node);
	return waitNode;
}
function msgSetError(node,text,displayType){
	if(!text)return false;
	if(!displayType)displayType='block';
	var errorNode=domC(displayType=='inline'?'span':'div','error',text);
	domAP(errorNode,node);
	return errorNode;
}
function msgSetMessage(node,text,displayType){
	if(!text)return false;
	if(!displayType)displayType='block';
	var messageNode=domC(displayType=='inline'?'span':'div','message',text);
	domAP(messageNode,node);
	return messageNode;
}
function msgSetSuccess(node,text,displayType){
	if(!text)return false;
	if(!displayType)displayType='block';
	var successNode=domC(displayType=='inline'?'span':'div','success',text);
	domAP(successNode,node);
	return successNode;
}
function msgUnset(node,msgTypes){
	if(!msgTypes)msgTypes=new Array('wait','error','message','success');
	var tempNode=node;
	var removingNodes=new Array();
	while(tempNode=domPN(tempNode)){
		if(inArray(msgTypes,tempNode.className)){
			removingNodes.push(tempNode);
		}
	}
	if(removingNodes){
		for(var i=0;i<removingNodes.length;i++){
			domD(removingNodes[i]);
		}
	}
}

function textAutoResize(elem){
	var rowsCount=elem.rows;
	resize();
	function resize(){
		if(elem.scrollHeight>elem.clientHeight && rowsCount!=-1){
			elem.rows=rowsCount+1;
			textAutoResize(elem);
		}
	}
}

//MISCELLANEUS
function getOpacity(elem){
	var opacity=elem.style.opacity!==undefined?parseFloat(elem.style.opacity):parseInt(elem.style.filter)/100;
	return opacity!==undefined?opacity:1;
}
function setOpacity(elem,opacity){
	elem.style.opacity=opacity;
	elem.style.filter='alpha(opacity='+(opacity*100)+')';
}
function resetOpacity(elem){
	setOpacity(elem,1);
}
function setOpaque(elem){
	setOpacity(elem,1);
}
function setTransparent(elem){
	setOpacity(elem,0);
}
function waitForImage(img,callbackFunc){
	img.onload=function(){callbackFunc();};
}
function evalScripts(elem){
	var script=domSTC(elem,'script');
	eval(script.innerHTML);
}

//ARRAYS
inArray=Array.prototype.indexOf?
	function(arr,val){
		return arr.indexOf(val)!=-1;
	}:
	function(arr,val){
		var i=arr.length;
		while(i--){
			if(arr[i]==val)return true;
		}
		return false;
	}
function isArray(arr){
    return (typeof(arr)=='object')&&(arr instanceof Array);
}

// ANIMATION
var animClearElem=null;
var animLocked=false;
function animAppear(elem,speedRate,depth,callback){
	if(!speedRate)speedRate=50;
	else if(speedRate<=50)speedRate=50;
	else if(speedRate>=500)speedRate=500;
	var step=speedRate/1000;
	if(!depth)depth=1;
	var opacityCurr=getOpacity(elem);
	var interval=setInterval(function(){
		if(opacityCurr<depth){
			if(opacityCurr>depth-step){
				opacityCurr=depth;
			}else{
				opacityCurr+=step;
			}		
			setOpacity(elem,opacityCurr);
		}else{
			setOpacity(elem,depth);
			clearInterval(interval);
			if(callback){
				if(typeof(callback)=='function'){
					callback();
				}
			}
		}
	},50);
}
function animDisappear(elem,speedRate,depth,callback){
	if(!speedRate)speedRate=50;
	else if(speedRate<=50)speedRate=50;
	else if(speedRate>=500)speedRate=500;
	var step=speedRate/1000;
	if(!depth)depth=0;
	var opacityCurr=getOpacity(elem);
	var interval=setInterval(function(){
		if(opacityCurr>depth){
			if(opacityCurr<depth+step){
				opacityCurr=depth;
			}else{
				opacityCurr-=step;
			}
			setOpacity(elem,opacityCurr);
		}else{
			setOpacity(elem,depth);
			clearInterval(interval);
			if(callback){
				if(typeof(callback)=='function'){
					callback();
				}
			}
		}
	},50);
}
function animBlink(elem,speedRate,depth,holdTime){
	var step=0.05;
	if(!speedRate)speedRate=50;
	if(!depth)depth=0.3;
	var rise=true;
	var opacityCurr=getOpacity(elem);
	var interval=setInterval(function(){
		if(rise){
			if(opacityCurr<1){
				opacityCurr+=step;
				setOpacity(elem,opacityCurr);
			}else{
				setOpacity(elem,1);
				if(holdTime){
					var timeout=setTimeout(function(){
						rise=false;
						clearTimeout(timeout);
					},holdTime);
				}else{
					rise=false;
				}
			}
		}else{
			if(opacityCurr>depth){
				opacityCurr-=step;
				setOpacity(elem,opacityCurr);
			}else{
				setOpacity(elem,depth);
				rise=true;
			}
		}
		if(animClearElem==elem)clearInterval(interval);
	},speedRate);
}
function animClear(elem){
	animClearElem=elem;
}

function copyText(str){
  let tmp   = document.createElement('textarea'),
      focus = document.activeElement;

  tmp.value = str;

  document.body.appendChild(tmp);
  tmp.select();
  document.execCommand('copy');
  document.body.removeChild(tmp);
  focus.focus();
}

// number for quantity
function num125(n){
	var n100=n%100;
	var n10=n%10;
  	if((n100>10)&&(n100<20)){
    	return 5;
  	}
  	else if(n10==1){
    	return 1;
  	}
  	else if((n10>=2)&&(n10<=4)){
    	return 2;
  	}
  	else{
    	return 5;
  	}
}

// ending for quantity
function word125(n,ending1,ending2,ending5){
	var index=num125(n);
	if(index==1)return ending1;
	else if(index==2)return ending2;
	else return ending5;
}

// object to get parameters
function toGetParams(obj,amp){
	var result='';
	for (var param in obj){
		if(result.length || amp)result+='&';
		result+=param+'='+obj[param];
	}
	return result;
}

// parse url
function parseUrl(url){
	if(typeof(url)=='undefined')url=window.location.toString();
	var a=document.createElement('a');
	a.href=url;
	
	var pathname=a.pathname.match(/^\/?(\w+)/i);
	
	var parser={
		'protocol': a.protocol,
		'hostname': a.hostname,
		'port': a.port,
		'pathname': a.pathname,
		'search': a.search,
		'hash': a.hash,
		'host': a.host,
		'page': pathname?pathname[1]:''
	}
	
	console.log(parser);
	return parser;
}
// get hash
function getHash(url){
	if(typeof(url)=='undefined')url=window.location.toString();
	var a=document.createElement('a');
	a.href=url;
	return a.hash;
}