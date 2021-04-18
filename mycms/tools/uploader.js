
function Uploader(){
	var self=this;
	this.uploader=null;
	this.isIe=(getBrowser()=='ie');
	
	this.init=function(){
		var uploader=domC('div');
		uploader.id='uploader';	
		domAL(uploader,document.body);
		self.uploader=uploader;
	}
	
	this.getParamInput=function(param,value){
		if(!self.isIe){
			var inp=domC('input');
			inp.type='hidden';
			inp.name=param;
			inp.value=value;
		}else{
			var inp=domC('<input type="hidden" name="'+param+'" value="'+value+'">');
		}
		return inp;
	}
	
	this.upload=function(input,info){
		var container=domP(input);
		var fauxInput=domSC(container,'faux-input');
		var valueHolder=domNN(input);
		if(valueHolder.value){
			self.removeFile(valueHolder.value);
			fauxInput.innerHTML='';
			var uploadedLine=domSC(domP(container),'uploaded-file');
			if(uploadedLine)domD(uploadedLine);
		}
		
		var fileBtn=domSC(container,'browse-btn');
		if(fileBtn)self.setButtonState(fileBtn,1);
		
		var iframe=domC(self.isIe?'<iframe name="uploader'+info['id']+'" src="">':'iframe');
		with(iframe){
			name='uploader'+info['id'];
			setAttribute('name','uploader'+info['id']);
			id='uploader'+info['id'];
		}	
		domAL(iframe,self.uploader);
		
		var form=domC(self.IsIe?'<form action="index.php?module=ajax&act=GetUploader" method="post" enctype="multipart/form-data" target="uploader'+info['id']+'" id="uploader-form'+info['id']+'">':'form');
		with(form){
			id='uploader-form'+info['id'];
			action='index.php?module=ajax&act=GetUploader';
			target='uploader'+info['id'];
			enctype='multipart/form-data';
			setAttribute('method','post');
			setAttribute('target','uploader'+info['id']);
			setAttribute('enctype','multipart/form-data');
		}
		domAL(form,self.uploader);
		
		input.id='file-input'+info['id']+'_'+info['element_id'];
		input.name='File';
		input.setAttribute('id','file-input'+info['id']+'_'+info['element_id']);
		input.setAttribute('name','File');
		valueHolder.id='file-value-holder'+info['id']+'_'+info['element_id'];
		valueHolder.setAttribute('id','file-value-holder'+info['id']+'_'+info['element_id']);
		valueHolder.setAttribute('uploading',1);
		domAF(input,form);
		
		for(var k in info){
			domAL(self.getParamInput(k,info[k]),form);
		}
		
		form.submit();
	}
	
	this.setButtonState=function(button,state){
		var img=domFC(button);
		if(state==1){
			img.src='images/loading16.gif';
		}else{
			img.src='images/inp_file_btn_label.gif';
		}
	}
	
	this.setUploaded=function(id,elementId,value,caption,html){
		valueHolder=gei('file-value-holder'+id+'_'+elementId);
		valueHolder.value=value;
		var container=domP(valueHolder);
		var fileBtn=domSC(container,'browse-btn');
		var fauxInput=domSC(container,'faux-inp');
		fauxInput.innerHTML=caption;
		var uploadedFile=domC('span','uploaded-file',html);
		domAN(uploadedFile,container);
		var input=gei('file-input'+id+'_'+elementId);
		input.name='';
		input.removeAttribute('name');
		domAP(input,valueHolder);
		valueHolder.removeAttribute('uploading');
		if(fileBtn)self.setButtonState(fileBtn,0);
	}
	
	this.destroy=function(id){
		var iframe=gei('uploader'+id);
		var form=gei('uploader-form'+id);
		domD(iframe);
		domD(form);
	}
	
	this.removeFile=function(filepath){
		var ajaxObj=new Ajax();
		ajaxObj.sendData({'filepath':filepath},'index.php?module=ajax&act=RemoveFile');
	}
	
	this.uploader=gei('uploader')
	if(!this.uploader)this.init();
	
}

function uplUpload(input,uploaderCode,info){
	info['code']=uploaderCode;
	var uploader=new Uploader();
	uploader.upload(input,info);
}
function uplSetUploaded(recordId,elementId,value,filename,html){
	if(!html)html='File uploaded';
	var uploader=new Uploader();
	uploader.setUploaded(recordId,elementId,value,filename,html);
	uploader.destroy(recordId);
}