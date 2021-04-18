// CHECKS
function stxCheckNotEmpty(fld){
	if(fld.value)return true;
	else{
		var elem=fld.tagName.toLowerCase()=='textarea'&&!hasClass(fld,'ckeditor')?domP(domP(domP(fld))):domP(fld);
		addClass(elem,'inp-error');
		if(mode=1){
			stxAddErrorStatus(elem,'Field can\'t be empty');
		}
		return false;
	}
}
function stxCheckTheSame(fld,mode){
	if(typeof(mode)=='undefined')mode=1;
	var fld2=gei(fld.id+'_repeat');
	if(fld.value==fld2.value)return true;
	else{
		var elem=fld.tagName.toLowerCase()=='textarea'?domP(domP(domP(fld))):domP(fld);
		addClass(elem,'inp-error');
		if(mode=1){
			stxAddErrorStatus(elem,'The password doesn\'t match re');
		}
		return false;
	}
}
function stxCheckUploaded(fld,mode){
	if(typeof(mode)=='undefined')mode=1;
	if(!fld.getAttribute('uploading'))return true;
	else{
		var elem=domP(fld);
		addClass(elem,'inp-error');
		if(mode=1){
			stxAddErrorStatus(elem,'Wait for the download of the file');
		}
		return false;
	}
}
function stxCheck(fld,patternTitle,mode){
	if(typeof(mode)=='undefined')mode=1;
	var value=fld.value;
	var reg=new RegExp(stxPatterns[patternTitle]['pattern']);
	if(!value || reg.test(value))return true;
	else{
		var elem=fld.tagName.toLowerCase()=='textarea'?domP(domP(domP(fld))):domP(fld);
		addClass(elem,'inp-error');
		if(mode=1){
			stxAddErrorStatus(elem,stxPatterns[patternTitle]['error']);
		}
		return false;
	}
}

var stxPatterns={
	'number'	: {
		'pattern'	: '^\-?[0-9]+$',
		'error'		: 'The value must be a number'
	},
	'integer'	: {
		'pattern'	: '^\-?[0-9]+$',
		'error'		: 'The value must be a number'
	},
	'float'	: {
		'pattern'	: '^\-?[0-9\.]+$',
		'error'		: 'The value must be a valid number, the delimiter «.»'
	},
	'date'		: {
		'pattern'	: '^([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}( [0-9]{1,2}:[0-9]{1,2})*|([0-9]{2}:[0-9]{2}))$',
		'error'		: 'Date must satisfy the dd.mm.yyyy'
	},
	'email'		: {
		'pattern'	: '^[a-zA-Z0-9_\.\-]+@([a-zA-Z0-9][a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,6}$',
		'error'		: 'E-mail must satisfy the pattern: something@something.something'
	},
	'phone'		: {
		'pattern'	: '^[0-9\+\-() ,\.]+$',
		'error'		: 'The phone can contain numbers, plus signs, minus signs, parentheses and point'
	},
	'login'	: {
		'pattern'	: '^[a-zA-Z0-9\._]+$',
		'error'		: 'Login can contain only latin characters, digits, dot and underscore'
	},
	'password'	: {
		'pattern'	: '^[a-zA-Z0-9\._]+$',
		'error'		: 'The password can contain latin characters, digits, dot and underscoreе'
	}
};
function stxCheckElements(checks,mode){
	if(typeof(mode)=='undefined')mode=1;
	var correct=true;
	for(var i=0;i<checks.length;i++){
		for(var j=0;j<checks[i].pattern.length;j++){
			var object=checks[i].object;
			var pattern=checks[i].pattern[j];
			if(pattern=='important'){
				if(!stxCheckNotEmpty(object,mode))correct=false;
			}else if(pattern=='uploaded'){
				if(!stxCheckUploaded(object,mode))correct=false;
			}else if(pattern=='same'){
				if(!stxCheckTheSame(object,mode))correct=false;
			}else{
				if(!stxCheck(object,pattern,mode))correct=false;
			}
		}
	}
	return correct;
}
function stxAddErrorStatus(inp,message){
	var status=domC('div','status status-error',message);
	var space=domCT(' ');
	domAN(space,inp);
	domAN(status,inp);
}
function stxResetStatus(elem,mode){
	if(typeof(mode)=='undefined')mode=1;
	if(elem.tagName.toLowerCase()!='form'){
		var inp=elem.tagName.toLowerCase()=='textarea'?domP(domP(domP(elem))):domP(elem);
		removeClass(inp,'inp-error');
		if(mode==1){
			var status=domSC(domP(inp),'status');
			if(status){
				domD(status.previousSibling);
				domD(status);
			}
		}
	}else{
		var fld=null;
		for(var i=0;elem.elements[i];i++){
			fld=elem.elements[i];
			if((fld.tagName.toLowerCase()=='input')||(fld.tagName.toLowerCase()=='textarea')){			
				stxResetStatus(fld);
			}
		}
	}
}