function Ajax(){
		
	this.request=null;
	this.form=null;
	this.data=null;
	this.url=location.href;
	this.method='POST';
	this.async=true;
	this.resultSuccessful=false;
	this.errors=new Array(
		'Ошибка: ',
		'Ошибка: AJAX не поддерживается браузером.',
		'Ошибка: Не удается создать объект XMLHttpRequest. Возможно Ваш браузер не поддерживает или в нем запрещен запуск ActiveX расширений. В последнем случае - измените настройки безопасности.',
		'Ошибка: Не найден запрошеный URL: ',
		'Ошибка: AJAX запрос завершился неудачей. URL: '
	);

	this.onUninitialized=function(ajaxObject){};
	this.onOpen=function(ajaxObject){};
	this.onSent=function(ajaxObject){};
	this.onReceiving=function(ajaxObject){};
	this.onLoaded=function(ajaxObject){};
	this.onError=function(ajaxObject){};
	this.onStatusChange=function(ajaxObject){
	  try{
			switch(ajaxObject.request.readyState){
				case 0:
					ajaxObject.onUninitialized(ajaxObject);
					break;
				case 1:
					ajaxObject.onOpen(ajaxObject);
					break;
				case 2:
					ajaxObject.onSent(ajaxObject);
					break;
				case 3:
					ajaxObject.onReceiving(ajaxObject);
					break;
				case 4:
					if(!ajaxObject.request.status||ajaxObject.request.status>=200&&ajaxObject.request.status<=300||request.status==304){
						ajaxObject.successful=true;
						ajaxObject.onLoaded(ajaxObject);
						break;
	 				}else if(ajaxObject.request.status==404){
						ajaxObject.riseError(3,ajaxObject.url);
						ajaxObject.onError(ajaxObject);
						break;
	 				}else{
						ajaxObject.riseError(3,ajaxObject.url);
						ajaxObject.onError(ajaxObject);
						break;
	 				}
			}
		}catch(e){
		}
	}
	
	this.init=function(){
		if(window.XMLHttpRequest){
			try{ this.request=new XMLHttpRequest(); }
			catch(e){ this.riseError(2); }
		}else if(window.ActiveXObject){
			try{ this.request=new ActiveXObject('Msxml2.XMLHTTP'); }
			catch(e){ try{ this.request=new ActiveXObject('Microsoft.XMLHTTP'); }
			catch(e){ this.riseError(2); } }
		}else{
			this.riseError(1);
		}
		if(this.request){
			var ajaxObject=this;
			this.request.onreadystatechange=function(){ajaxObject.onStatusChange(ajaxObject);};
		}
	}
	
	this.send=function(data,url,method,async){
		url=(url!=null?url:this.url);
		method=(method!=null?method:this.method);
		async=(async!=null?async:this.async);

		if(method=='GET'&&data){
			url=url+'?'+data;
			data=null;
		}
		this.request.open(method,url,async);
		this.request.setRequestHeader('Content-Type','application/x-www-form-urlencoded');	
		this.request.send(data);
	}
	this.sendData=function(fields,url,method,async){
		url=(url!=null?url:this.url);
		method=(method!=null?method:this.method);
		async=(async!=null?async:this.async);

		var data='ajax_query=1';
		for(i in fields){
			data+='&'+i+'='+encodeURIComponent(fields[i]);
		}
		this.send(data,url,method,async);
	}
	this.sendForm=function(form,iframe,url,method,async){
		if(typeof(form)=='string'){
			form=(document.getElementById(form)?document.getElementById(form):document.forms[form]);
		}		
		iframe=(iframe!=null?iframe:this.iframe);
		url=(url!=null?url:(form.action?form.action:this.url));
		method=(method!=null?method:(form.method?form.method:this.method));
		async=(async!=null?async:this.async);
		this.resultIframe=iframe;

		var data='ajax_query=1';
		for(i=0; i<form.elements.length; i++){
			if(form.elements[i].name){
				if(form.elements[i].name&&(form.elements[i].type!='checkbox'&&form.elements[i].type!='radio'||form.elements[i].checked))
					data+='&'+form.elements[i].name+'='+encodeURIComponent(form.elements[i].value);
			}
		}
		this.send(data,url,method,async);
	}
	this.riseError=function(i,param){
		alert(this.errors[i]+param);
	}

	this.init();
}