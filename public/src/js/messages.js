function msgSetWait(node,text,displayType){
	if(!text)text='Подождите, идет загрузка...';
	if(!displayType)displayType='block';
	$(node).before(displayType=='inline'?'<span class="wait">'+text+'</span>':'<div class="wait">'+text+'</div>');
	return $(node).prev().get(0);
}
function msgSetError(node,text,displayType){
	if(!text)return false;
	if(!displayType)displayType='block';
	$(node).before(displayType=='inline'?'<span class="error">'+text+'</span>':'<div class="error">'+text+'</div>');
	return $(node).prev().get(0);
}
function msgSetWarning(node,text,displayType){
	if(!text)return false;
	if(!displayType)displayType='block';
	$(node).before(displayType=='inline'?'<span class="warning">'+text+'</span>':'<div class="warning">'+text+'</div>');
	return $(node).prev().get(0);
}
function msgSetMessage(node,text,displayType){
	if(!text)return false;
	if(!displayType)displayType='block';
	$(node).before(displayType=='inline'?'<span class="message">'+text+'</span>':'<div class="message">'+text+'</div>');
	return $(node).prev().get(0);
}
function msgSetSuccess(node,text,displayType){
	if(!text)return false;
	if(!displayType)displayType='block';
	$(node).before(displayType=='inline'?'<span class="success">'+text+'</span>':'<div class="success">'+text+'</div>');
	return $(node).prev().get(0);
}
function msgUnset(node,animate) {
	if(!animate)$(node).prev('.wait, .error, .message, .success').remove();
	else $(node).prev('.wait, .error, .message, .success').stop().slideUp(500,function(){
		$(this).remove();
	});
}