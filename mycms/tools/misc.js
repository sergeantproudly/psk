// AUTHORISATION
function authSendForm(form){
	var checks=new Array(
		{'object':form.login,'pattern':Array('important')},
		{'object':form.password,'pattern':Array('important')}
	);
	stxResetStatus(form);
	if(stxCheckElements(checks)){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			var responseStatus=response.substr(0,1);
			var responseText=response.substr(2);
			if(responseStatus=='1'){
				reload(true);
			}else{
				msgSetError(form,responseText);
			}
		}
		ajaxObj.sendForm(form);
	}
}
function authLogout(){
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		reload(true);	
	}
	ajaxObj.sendData({},'index.php?module=enter&act=Logout');
}

// DOCUMENTS
function docAdd(sender){
	var count=domSTC(domPN(sender),'input').value;
	if(!count||count<=1){
		Popup('document_add',600);
	}else{
		redirect('index.php?module=documents&mode=Add&count='+count);
	}
}
function docAddSendForm(form){
	var dimension=6;
	var length=form.elements.length-2;
	var checks=new Array();
	for(var i=0;i<length;i+=dimension){
		checks.push({'object':form.title,'pattern':Array('important')});
		checks.push({'object':form.order,'pattern':Array('number')});
	}
	stxResetStatus(form);
	if(stxCheckElements(checks)){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			eval(response);
		}
		ajaxObj.sendForm(form);
	}
}
function docAddCancel(){
	redirect('index.php?module=documents');
}
function docEdit(sender){
	var form=gei('browser-form');
	var cbs=new Array();
	var fstCb=null;
	for(var i=0;i<form.elements.length;i++){
		var inp=form.elements[i];
		if(inp.type=='checkbox' && inp.name!='check_all'){
			if(inp.checked){
				if(!fstCb){
					fstCb=inp;
				}else{
					form.action='index.php?module=documents&mode=Edit';
					form.submit();
					return true;
				}
			}
		}
	}
	if(fstCb){
		id=fstCb.name.substr(3,fstCb.name.length-4);
		Popup('document_edit',600,{'id':id});
	}else{
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});
	}
}
function docEditSendForm(form){
	var dimension=6;
	var length=form.elements.length-2;
	var checks=new Array();
	for(var i=0;i<length;i+=dimension){
		checks.push({'object':form.title,'pattern':Array('important')});
		checks.push({'object':form.order,'pattern':Array('number')});
	}
	stxResetStatus(form);
	if(stxCheckElements(checks)){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			eval(response);
		}
		ajaxObj.sendForm(form);
	}
}
function docEditCancel(){
	redirect('index.php?module=documents');
}
function docDeleteConfirm(sender,id){
	if(hasClass(sender,'ico')){
		var hint=Hint(sender,'<div class="confirm">Уверены?<br/><span class="dashed">да</span> <span class="dashed">нет</span></div>',150);
		var contentHolder=hint.getContentHolder();
		var confirm=domSC(domFC(contentHolder),'dashed');
		var cancel=domNN(confirm);
		addHandler(confirm,'click',function(){
			docDelete(id);
		});
		addHandler(cancel,'click',function(){
			hint.destroyHint();
		});
	}else{
		var form=gei('browser-form');
		var cbs=new Array();
		for(var i=0;i<form.elements.length;i++){
			var inp=form.elements[i];
			if(inp.type=='checkbox' && inp.name!='check_all'){
				if(inp.checked){
					Popup('delete_confirm',350,{'thing':'documents','action':'docDeleteSendForm();'});
					return true;
				}
			}
		}
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});	
	}
}
function docDelete(id){
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendData({'id':id},'index.php?module=documents&act=DeleteDocument');
}
function docDeleteSendForm(sender){
	form=gei('browser-form');
	form.action='index.php?module=documents&act=DeleteDocument';
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendForm(form);
}

// TABLES
function tableAdd(sender){
	var count=domSTC(domPN(sender),'input').value;
	if(!count||count<=1){
		Popup('table_add',600);
	}else{
		redirect('index.php?module=tables&mode=Add&count='+count);
	}
}
function tableAddSendForm(form){
	var dimension=3;
	var length=form.elements.length-2;
	var checks=new Array();
	for(var i=0;i<length;i+=dimension){
		checks.push({'object':form.elements[i],'pattern':Array('important')});
		checks.push({'object':form.elements[i+1],'pattern':Array('number')});
	}
	stxResetStatus(form);
	if(stxCheckElements(checks)){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			eval(response);
		}
		ajaxObj.sendForm(form);
	}
}
function tableAddCancel(){
	redirect('index.php?module=tables');
}
function tableEdit(sender){
	var form=gei('browser-form');
	var cbs=new Array();
	var fstCb=null;
	for(var i=0;i<form.elements.length;i++){
		var inp=form.elements[i];
		if(inp.type=='checkbox' && inp.name!='check_all'){
			if(inp.checked){
				if(!fstCb){
					fstCb=inp;
				}else{
					form.action='index.php?module=tables&mode=Edit';
					form.submit();
					return true;
				}
			}
		}
	}
	if(fstCb){
		id=fstCb.name.substr(3,fstCb.name.length-4);
		Popup('table_edit',600,{'id':id});
	}else{
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});
	}
}
function tableEditSendForm(form){
	var dimension=3;
	var length=form.elements.length-2;
	var checks=new Array();
	for(var i=0;i<length;i+=dimension){
		checks.push({'object':form.elements[i],'pattern':Array('important')});
		checks.push({'object':form.elements[i+1],'pattern':Array('number')});
	}
	stxResetStatus(form);
	if(stxCheckElements(checks)){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			eval(response);
		}
		ajaxObj.sendForm(form);
	}
}
function tableEditCancel(){
	redirect('index.php?module=tables');
}
function tableDeleteConfirm(sender,id){
	if(hasClass(sender,'ico')){
		var hint=Hint(sender,'<div class="confirm">Уверены?<br/><span class="dashed">да</span> <span class="dashed">нет</span></div>',150);
		var contentHolder=hint.getContentHolder();
		var confirm=domSC(domFC(contentHolder),'dashed');
		var cancel=domNN(confirm);
		addHandler(confirm,'click',function(){
			tableDelete(id);
		});
		addHandler(cancel,'click',function(){
			hint.destroyHint();
		});
	}else{
		var form=gei('browser-form');
		var cbs=new Array();
		for(var i=0;i<form.elements.length;i++){
			var inp=form.elements[i];
			if(inp.type=='checkbox' && inp.name!='check_all'){
				if(inp.checked){
					Popup('delete_confirm',350,{'thing':'tables','action':'tableDeleteSendForm();'});
					return true;
				}
			}
		}
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});	
	}
}
function tableDelete(id){
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendData({'id':id},'index.php?module=tables&act=DeleteTable');
}
function tableDeleteSendForm(sender){
	form=gei('browser-form');
	form.action='index.php?module=tables&act=DeleteTable';
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendForm(form);
}
function tableCreateSendForm(form){
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendForm(form);
}
function tableCreateAddField(){
	var srcLabel=gei('clone-point');
	var srcFld=domNN(srcLabel);
	var srcReset=domNN(srcFld);
	var newLabel=srcLabel.cloneNode(true);
	var newFld=srcFld.cloneNode(true);
	var newReset=srcReset.cloneNode(true);
	
	var btnLine=domSC(domP(srcLabel),'btn-common');
	domAP(newLabel,btnLine);
	domAP(newFld,btnLine);
	domAP(newReset,btnLine);
	envInitElements(newFld);
}

// FIELDS
function fieldAdd(sender,table_id){
	var count=domSTC(domPN(sender),'input').value;
	if(!count||count<=1){
		Popup('field_add',600,{'table_id':table_id});
	}else{
		redirect('index.php?module=fields&mode=Add&table_id='+table_id+'&count='+count);
	}
}
function fieldAddSendForm(form){
	var dimension=3;
	var length=form.elements.length-2;
	var checks=new Array();
	for(var i=0;i<length;i+=dimension){
		checks.push({'object':form.elements[i],'pattern':Array('important')});
	}
	stxResetStatus(form);
	if(stxCheckElements(checks)){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			eval(response);
		}
		ajaxObj.sendForm(form);
	}
}
function fieldAddCancel(table_id){
	redirect('index.php?module=fields&table_id='+table_id);
}
function fieldEdit(sender,table_id){
	var form=gei('browser-form');
	var cbs=new Array();
	var fstCb=null;
	for(var i=0;i<form.elements.length;i++){
		var inp=form.elements[i];
		if(inp.type=='checkbox' && inp.name!='check_all'){
			if(inp.checked){
				if(!fstCb){
					fstCb=inp;
				}else{
					form.action='index.php?module=fields&mode=Edit&table_id='+table_id;
					form.submit();
					return true;
				}
			}
		}
	}
	if(fstCb){
		id=fstCb.name.substr(3,fstCb.name.length-4);
		Popup('field_edit',600,{'id':id,'table_id':table_id});
	}else{
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});
	}
}
function fieldEditSendForm(form){
	var dimension=3;
	var length=form.elements.length-2;
	var checks=new Array();
	for(var i=0;i<length;i+=dimension){
		checks.push({'object':form.elements[i],'pattern':Array('important')});
	}
	stxResetStatus(form);
	if(stxCheckElements(checks)){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			eval(response);
		}
		ajaxObj.sendForm(form);
	}
}
function fieldEditCancel(table_id){
	redirect('index.php?module=fields&table_id='+table_id);
}
function fieldDeleteConfirm(sender,table_id,id){
	if(hasClass(sender,'ico')){
		var hint=Hint(sender,'<div class="confirm">Уверены?<br/><span class="dashed">да</span> <span class="dashed">нет</span></div>',150);
		var contentHolder=hint.getContentHolder();
		var confirm=domSC(domFC(contentHolder),'dashed');
		var cancel=domNN(confirm);
		addHandler(confirm,'click',function(){
			fieldDelete(id,table_id);
		});
		addHandler(cancel,'click',function(){
			hint.destroyHint();
		});
	}else{
		var form=gei('browser-form');
		var cbs=new Array();
		for(var i=0;i<form.elements.length;i++){
			var inp=form.elements[i];
			if(inp.type=='checkbox' && inp.name!='check_all'){
				if(inp.checked){
					Popup('delete_confirm',350,{'thing':'fields','action':'fieldDeleteSendForm('+table_id+');'});
					return true;
				}
			}
		}
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});	
	}
}
function fieldDelete(id,table_id){
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendData({'id':id},'index.php?module=fields&act=DeleteField&table_id='+table_id);
}
function fieldDeleteSendForm(table_id){
	form=gei('browser-form');
	form.action='index.php?module=fields&act=DeleteField&table_id='+table_id;
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendForm(form);
}
function fieldScanTable(table_id){
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendData({},'index.php?module=fields&act=ScanTable&table_id='+table_id);
}

// ELEMENTS
function elementAdd(sender,document_id){
	var count=domSTC(domPN(sender),'input').value;
	if(!count||count<=1){
		Popup('element_add',600,{'document_id':document_id});
	}else{
		redirect('index.php?module=elements&mode=Add&document_id='+document_id+'&count='+count);
	}
}
function elementAddSendForm(form){
	var dimension=8;
	var length=form.elements.length-2;
	var checks=new Array();
	for(var i=0;i<length;i+=dimension){
		checks.push({'object':form.elements[i],'pattern':Array('important')});
		checks.push({'object':form.elements[i+5],'pattern':Array('number')});
	}
	stxResetStatus(form);
	if(stxCheckElements(checks)){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			eval(response);
		}
		ajaxObj.sendForm(form);
	}
}
function elementAddCancel(document_id){
	redirect('index.php?module=elements&document_id='+document_id);
}
function elementEdit(sender,document_id){
	var form=gei('browser-form');
	var cbs=new Array();
	var fstCb=null;
	for(var i=0;i<form.elements.length;i++){
		var inp=form.elements[i];
		if(inp.type=='checkbox' && inp.name!='check_all'){
			if(inp.checked){
				if(!fstCb){
					fstCb=inp;
				}else{
					form.action='index.php?module=elements&mode=Edit&document_id='+document_id;
					form.submit();
					return true;
				}
			}
		}
	}
	if(fstCb){
		id=fstCb.name.substr(3,fstCb.name.length-4);
		Popup('element_edit',600,{'id':id,'document_id':document_id});
	}else{
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});
	}
}
function elementEditSendForm(form){
	var dimension=8;
	var length=form.elements.length-2;
	var checks=new Array();
	for(var i=0;i<length;i+=dimension){
		checks.push({'object':form.elements[i],'pattern':Array('important')});
		checks.push({'object':form.elements[i+5],'pattern':Array('number')});
	}
	stxResetStatus(form);
	if(stxCheckElements(checks)){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			eval(response);
		}
		ajaxObj.sendForm(form);
	}
}
function elementEditCancel(document_id){
	redirect('index.php?module=elements&document_id='+document_id);
}
function elementDeleteConfirm(sender,document_id,id){
	if(hasClass(sender,'ico')){
		var hint=Hint(sender,'<div class="confirm">Уверены?<br/><span class="dashed">да</span> <span class="dashed">нет</span></div>',150);
		var contentHolder=hint.getContentHolder();
		var confirm=domSC(domFC(contentHolder),'dashed');
		var cancel=domNN(confirm);
		addHandler(confirm,'click',function(){
			elementDelete(id,document_id);
		});
		addHandler(cancel,'click',function(){
			hint.destroyHint();
		});
	}else{
		var form=gei('browser-form');
		var cbs=new Array();
		for(var i=0;i<form.elements.length;i++){
			var inp=form.elements[i];
			if(inp.type=='checkbox' && inp.name!='check_all'){
				if(inp.checked){
					Popup('delete_confirm',350,{'thing':'field','action':'elementDeleteSendForm('+document_id+');'});
					return true;
				}
			}
		}
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});	
	}
}
function elementDelete(id,document_id){
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendData({'id':id},'index.php?module=elements&act=DeleteElement&document_id='+document_id);
}
function elementDeleteSendForm(document_id){
	form=gei('browser-form');
	form.action='index.php?module=elements&act=DeleteElement&document_id='+document_id;
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendForm(form);
}

// USERS
function userAdd(sender){
	var count=domSTC(domPN(sender),'input').value;
	if(!count||count<=1){
		Popup('user_add',600);
	}else{
		redirect('index.php?module=users&mode=Add&count='+count);
	}
}
function userAddSendForm(form){
	var dimension=6;
	var length=form.elements.length-2;
	var checks=new Array();
	for(var i=0;i<length;i+=dimension){
		checks.push({'object':form.elements[i],'pattern':Array('important')});
		checks.push({'object':form.elements[i+1],'pattern':Array('important','login')});
		checks.push({'object':form.elements[i+2],'pattern':Array('important','password')});
	}
	stxResetStatus(form);
	if(stxCheckElements(checks)){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			eval(response);
		}
		ajaxObj.sendForm(form);
	}
}
function userAddCancel(){
	redirect('index.php?module=users');
}
function userEdit(sender){
	var form=gei('browser-form');
	var cbs=new Array();
	var fstCb=null;
	for(var i=0;i<form.elements.length;i++){
		var inp=form.elements[i];
		if(inp.type=='checkbox' && inp.name!='check_all'){
			if(inp.checked){
				if(!fstCb){
					fstCb=inp;
				}else{
					form.action='index.php?module=users&mode=Edit';
					form.submit();
					return true;
				}
			}
		}
	}
	if(fstCb){
		id=fstCb.name.substr(3,fstCb.name.length-4);
		Popup('user_edit',600,{'id':id});
	}else{
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});
	}
}
function userEditSendForm(form){
	var dimension=6;
	var length=form.elements.length-2;
	var checks=new Array();
	for(var i=0;i<length;i+=dimension){
		checks.push({'object':form.elements[i],'pattern':Array('important')});
		checks.push({'object':form.elements[i+1],'pattern':Array('important','login')});
		checks.push({'object':form.elements[i+2],'pattern':Array('password')});
	}
	stxResetStatus(form);
	if(stxCheckElements(checks)){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			eval(response);
		}
		ajaxObj.sendForm(form);
	}
}
function userEditCancel(){
	redirect('index.php?module=users');
}
function userDeleteConfirm(sender,id){
	if(hasClass(sender,'ico')){
		var hint=Hint(sender,'<div class="confirm">Уверены?<br/><span class="dashed">да</span> <span class="dashed">нет</span></div>',150);
		var contentHolder=hint.getContentHolder();
		var confirm=domSC(domFC(contentHolder),'dashed');
		var cancel=domNN(confirm);
		addHandler(confirm,'click',function(){
			userDelete(id);
		});
		addHandler(cancel,'click',function(){
			hint.destroyHint();
		});
	}else{
		var form=gei('browser-form');
		var cbs=new Array();
		for(var i=0;i<form.elements.length;i++){
			var inp=form.elements[i];
			if(inp.type=='checkbox' && inp.name!='check_all'){
				if(inp.checked){
					Popup('delete_confirm',350,{'thing':'accounts','action':'userDeleteSendForm();'});
					return true;
				}
			}
		}
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});	
	}
}
function userDelete(id){
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendData({'id':id},'index.php?module=users&act=DeleteUser');
}
function userDeleteSendForm(sender){
	form=gei('browser-form');
	form.action='index.php?module=users&act=DeleteUser';
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendForm(form);
}

// RECORDS
function recAdd(sender,document_id,params){
	var count=domSTC(domPN(sender),'input').value;
	if(!count||count<=1){
		Popup('record_add',600,{'document_id':document_id,'params':params});
	}else{
		redirect('index.php?module=records&mode=Add&document_id='+document_id+toGetParams(params,true)+'&count='+count);
	}
}
function recAddSendForm(form){
	var length=form.elements.length-2;
	var checks=new Array();
	var lstElement=null;
	
	for(var i=0;i<length;i++){
		var inp=form.elements[i];
		if(inp.tagName.toLowerCase()=='input'){
			var element=domP(inp);
		}else if(inp.tagName.toLowerCase()=='textarea'){
			if(hasClass(domP(inp),'inp-wysiwyg')){
				var element=domP(inp);
				var id=element.id?element.id:inp.name;
				inp.value=editors[id].getData();
			}else{
				var element=domP(domP(domP(inp)));
			}		
		}
		if(element!=lstElement){
			if(inp.getAttribute('important')){
				checks.push({'object':inp,'pattern':Array('important')});
			}
			if(inp.type=='hidden'&&hasClass(element,'inp-file')){
				checks.push({'object':inp,'pattern':Array('uploaded')});
			}
			if(inp.getAttribute('syntax')){
				syntaxPatterns=inp.getAttribute('syntax').split(',');
				checks.push({'object':inp,'pattern':syntaxPatterns});
			}
			lstElement=element;
		}
	}
	stxResetStatus(form);
	if(stxCheckElements(checks)){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			eval(response);
		}
		ajaxObj.sendForm(form);
	}
}
function recAddCancel(document_id,params){
	//redirect('index.php?module=records&document_id='+document_id+(filter_id?'&filter_id='+filter_id:''));
	if(params && params['parent_record_id']){
		redirect('index.php?module=records&mode=view&document_id='+params['parent_document_id']+'&record_id='+params['parent_record_id']+'#tab-'+document_id);
	}else{
		redirect('index.php?module=records&document_id='+document_id);
	}
}
function recEdit(sender,document_id,params){
	var form=$(sender).closest('form').get(0);
	var cbs=new Array();
	var fstCb=null;
	for(var i=0;i<form.elements.length;i++){
		var inp=form.elements[i];
		if(inp.type=='checkbox' && inp.name!='check_all'){
			if(inp.checked){
				if(!fstCb){
					fstCb=inp;
				}else{
					form.action='index.php?module=records&mode=Edit&document_id='+document_id+toGetParams(params,true);
					form.submit();
					return true;
				}
			}
		}
	}
	if(fstCb){
		id=fstCb.name.substr(3,fstCb.name.length-4);
		Popup('record_edit',600,{'id':id,'document_id':document_id,'params':params});
	}else{
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});
	}
}
function recEditSendForm(form){
	var length=form.elements.length-2;
	var checks=new Array();
	var lstElement=null;
	
	for(var i=0;i<length;i++){
		var inp=form.elements[i];
		if(inp.tagName.toLowerCase()=='input'){
			var element=domP(inp);
		}else if(inp.tagName.toLowerCase()=='textarea'){
			if(hasClass(domP(inp),'inp-wysiwyg')){
				var element=domP(inp);
				var id=element.id?element.id:inp.name;
				inp.value=editors[id].getData();
			}else{
				var element=domP(domP(domP(inp)));
			}
		}
		if(element!=lstElement){
			if(inp.getAttribute('important')){
				checks.push({'object':inp,'pattern':Array('important')});
			}
			if(inp.type=='hidden'&&hasClass(element,'inp-file')){
				checks.push({'object':inp,'pattern':Array('uploaded')});
			}
			if(inp.getAttribute('syntax')){
				syntaxPatterns=inp.getAttribute('syntax').split(',');
				checks.push({'object':inp,'pattern':syntaxPatterns});
			}
			lstElement=element;
		}
	}
	stxResetStatus(form);
	if(stxCheckElements(checks)){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			eval(response);
		}
		ajaxObj.sendForm(form);
	}
}
function recEditCancel(document_id,params){
	//redirect('index.php?module=records&document_id='+document_id+(filter_id?'&filter_id='+filter_id:''));
	if(params && params['parent_record_id']){
		redirect('index.php?module=records&mode=view&document_id='+params['parent_document_id']+'&record_id='+params['parent_record_id']+'#tab-'+document_id);
	}else{
		redirect('index.php?module=records&document_id='+document_id);
	}
}
function recDeleteConfirm(sender,document_id,id){
	if(hasClass(sender,'ico')){
		var hint=Hint(sender,'<div class="confirm">Уверены?<br/><span class="dashed">да</span> <span class="dashed">нет</span></div>',150);
		var contentHolder=hint.getContentHolder();
		var confirm=domSC(domFC(contentHolder),'dashed');
		var cancel=domNN(confirm);
		addHandler(confirm,'click',function(){
			recDelete(id,document_id);
		});
		addHandler(cancel,'click',function(){
			hint.destroyHint();
		});
	}else{
		//var form=gei('browser-form');
		var form=$(sender).closest('form').get(0);
		var cbs=new Array();
		for(var i=0;i<form.elements.length;i++){
			var inp=form.elements[i];
			if(inp.type=='checkbox' && inp.name!='check_all'){
				if(inp.checked){
					Popup('delete_confirm',350,{'thing':'записи','action':'recDeleteSendForm('+document_id+');'});
					return true;
				}
			}
		}
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});	
	}
}
function recDelete(id,document_id){
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendData({'id':id},'index.php?module=records&act=DeleteRecord&document_id='+document_id);
}
function recDeleteSendForm(document_id){
	//form=gei('browser-form');
	if($('#tab-content-'+document_id).length){
		var form=$('#tab-content-'+document_id+' form').get(0);
	}else{
		var form=$('browser-form').get(0);
	}	
	form.action='index.php?module=records&act=DeleteRecord&document_id='+document_id;
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendForm(form);
}
function recSortBy(field_name,order_dir,document_id){
	redirect('index.php?module=records&document_id='+document_id+'&orderby='+field_name+(order_dir?'&orderdir='+order_dir:''));
}

// PROPERTIES
function propSendForm(form){
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendForm(form);
}

// ROWS ON PAGE
function ropRefresh(sender){
	var rop=sender.value;
	if(rop>0){
		var ajaxObj=new Ajax();
		ajaxObj.onLoaded=function(ajaxObj){
			var response=ajaxObj.request.responseText;
			eval(response);
		}
		ajaxObj.sendData({'rop':rop},'index.php?module=ajax&act=SetRowsOnPage');
	}
}

// COPY
function recCopy(sender){
	var links='';
	$('#browser-form>.table-holder>.styled>tbody>tr').each(function(index,item){
		if($(item).find('.tools>.inp-checkbox>input:checkbox[checked]').length){
			links+=$(item).children('td:nth-child(3)').html()+"\r\n";
		}
	});
	if(!links){
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});
		return false;
	}else{
		copyText(links);
		alert('Links copied');
		return true;
	}
}

// NOTIFICATIONS
function ntfDeleteConfirm(sender,id){
	if(hasClass(sender,'ico')){
		var hint=Hint(sender,'<div class="confirm">Уверены?<br/><span class="dashed">да</span> <span class="dashed">нет</span></div>',150);
		var contentHolder=hint.getContentHolder();
		var confirm=domSC(domFC(contentHolder),'dashed');
		var cancel=domNN(confirm);
		addHandler(confirm,'click',function(){
			ntfDelete(id);
		});
		addHandler(cancel,'click',function(){
			hint.destroyHint();
		});
	}else{
		var form=gei('browser-form');
		var cbs=new Array();
		for(var i=0;i<form.elements.length;i++){
			var inp=form.elements[i];
			if(inp.type=='checkbox' && inp.name!='check_all'){
				if(inp.checked){
					Popup('delete_confirm',350,{'thing':'уведомления','action':'ntfDeleteSendForm();'});
					return true;
				}
			}
		}
		Popup('message',300,{'header':'Хм,','message':'Похоже, ничего не выделено'});	
	}
}
function ntfDelete(id){
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendData({'id':id},'index.php?module=notifications&act=DeleteNotification');
}
function ntfDeleteSendForm(sender){
	form=gei('browser-form');
	form.action='index.php?module=notifications&act=DeleteNotification';
	var ajaxObj=new Ajax();
	ajaxObj.onLoaded=function(ajaxObj){
		var response=ajaxObj.request.responseText;
		eval(response);
	}
	ajaxObj.sendForm(form);
}

(function ($) {
	$(function () {
		
		if($('body>.main>.col-left').length && $('body>.main>.col-main').length){
			var $cl=$('body>.main>.col-left');
			var $cc=$('body>.main>.col-main');
			$cl.height('auto');			
			var h1=$cl.outerHeight();
			var h2=$cc.outerHeight();
			var h2off=h2-h1;
			if(h2<h1)$cl.height(h1-h2off);
		}
		
		$("[data-counter='counterup']").counterUp({delay:10,time:1e3});

	})
})(jQuery)