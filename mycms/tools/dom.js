function domCreate(tag,className,text){
	var node=document.createElement(tag);
	if(className)node.className=className;
	if(text)node.innerHTML=text;
	return node;
} 
function domCreateText(text){
	return document.createTextNode(text);
}
function domPrevNode(obj){
	if(typeof(obj)=='string') obj=gei(obj);
	var v=obj.previousSibling;
	if(!v)return false;
	if(v.nodeType!=3)return v;
	while(v=v.previousSibling)
		if(v.nodeType!=3)return v;
}
function domNextNode(obj){
	if(typeof(obj)=='string') obj=gei(obj);
	var v=obj.nextSibling;
	if(!v)return false;
	if(v.nodeType!=3)return v;
	while(v=v.nextSibling)
		if(v.nodeType!=3)return v;
}
function domFirstChild(obj){
	if(typeof(obj)=='string') obj=gei(obj);
	var v=obj.firstChild;
	if(!v)return false;
	if(v.nodeType!=3)return v;
	return domNextNode(v);
}
function domLastChild(obj){
	if(typeof(obj)=='string') obj=gei(obj);
	var v=obj.lastChild;
	if(!v)return false;
	if(v.nodeType!=3)return v;
	return domPrevNode(v);
}
function domDestroy(obj){
	if(typeof(obj)=='string') obj=gei(obj);
	obj.parentNode.removeChild(obj);
}
function domSearchChild(obj,childClass,reverse){
	if(typeof(obj)=='string') obj=gei(obj);
	var v=reverse?obj.lastChild:obj.firstChild;
	if(!v)return false;
	if(hasClass(v,childClass))return v;
	while(v=reverse?v.prevSibling:v.nextSibling)
		if(hasClass(v,childClass))return v;
	return false;
}
function domSearchTagChild(obj,childTag,reverse){
	if(typeof(obj)=='string') obj=gei(obj);
	var v=reverse?obj.lastChild:obj.firstChild;
	if(!v)return false;
	if(v.tagName&&v.tagName.toLowerCase()==childTag.toLowerCase())return v;
	while(v=reverse?domPrevNode(v):domNextNode(v))
		if(v.tagName&&v.tagName.toLowerCase()==childTag.toLowerCase())return v;
	return false;
}

function domAddFirst(obj,par){
	par.insertBefore(obj,par.firstChild);
}
function domAddLast(obj,par){
	par.appendChild(obj);
}
function domAddPrev(obj,node){
	node.parentNode.insertBefore(obj,node);
}
function domAddNext(obj,node){
	var par=node.parentNode; 
	if(node==domLC(par))par.appendChild(obj); 
	else par.insertBefore(obj,domNextNode(node));
} 
function domReplace(obj,node){
	var par=node.parentNode;
	par.replaceChild(obj,node);
}
function domParent(obj){
	return obj.parentNode;
}
function domChildLengh(obj){
	var node=domFC(obj);
	if(!node)return 0;
	var i=0;
	while(node){
		i++;
		node=domNN(node);
	}
	return i;
}
function domIsChild(obj,par){
	while(obj){
		if(obj==par)return true;
		obj=domP(obj);
	}
	return false;
}
function domIsParent(obj,child){
	return domIsChild(child,obj);
}

// synonims
function domC(tag,className,text){return domCreate(tag,className,text);} 
function domCT(text){return domCreateText(text);}
function domD(obj){domDestroy(obj);}
function domPN(obj){return domPrevNode(obj);}
function domNN(obj){return domNextNode(obj);}
function domFC(obj){return domFirstChild(obj);}
function domLC(obj){return domLastChild(obj);}
function domSC(obj,childClass,reverse){return domSearchChild(obj,childClass,reverse);}
function domSTC(obj,childTag,reverse){return domSearchTagChild(obj,childTag,reverse);}
function domAF(obj,par){return domAddFirst(obj,par);}
function domAL(obj,par){return domAddLast(obj,par);}
function domAP(obj,node){return domAddPrev(obj,node);}
function domAN(obj,node){return domAddNext(obj,node);}
function domR(obj,node){return domReplace(obj,node);}
function domP(obj){return domParent(obj);}
function domCL(obj){return domChildLengh(obj);}
function domIC(obj,par){return domIsChild(obj,par);}
function domIP(obj,child){return domIsParent(obj,child);}