<?php

define('DOCUMENT_REQUESTS_ID',9);

function PopupMessage($template){
	$header=htmlspecialchars($_POST['header']);
	$message=htmlspecialchars($_POST['message']);
	return strtr($template,array(
		'<%HEADER%>'	=> $header,
		'<%MESSAGE%>'	=> $message
	));
}

function PopupDeleteConfirm($template){
	$thing=htmlspecialchars($_POST['thing']);
	$action=htmlspecialchars($_POST['action']);
	return strtr($template,array(
		'<%THING%>'		=> $thing,
		'<%ACTION%>'	=> $action
	));
}

function PopupDocumentAdd($template){
	$defaultTitle='';
	$defaultValue='';
	$optionsType='';
	$res=dbDoQuery('SELECT Id, Name FROM mycms_tables ORDER BY IF(`Order`,-1000/`Order`,0) ASC',__FILE__,__LINE__);
	while($rec=dbGetRecord($res)){
		if(!$defaultTitle){
			$defaultTitle=$rec['Name'];
			$defaultValue=$rec['Id'];
			$optionsType.='<span class="item current" value="'.$rec['Id'].'">'.$rec['Name'].'</span>';
		}else{
			$optionsType.='<span class="item" value="'.$rec['Id'].'">'.$rec['Name'].'</span>';
		}
	}
	
	$optionsParent='<span class="item" value="0">&nbsp;</span>';
	$res=dbDoQuery('SELECT Id, Title FROM mycms_documents ORDER BY IF(`Order`,-1000/`Order`,0) ASC',__FILE__,__LINE__);
	while($rec=dbGetRecord($res)){
		$optionsParent.='<span class="item" value="'.$rec['Id'].'">'.$rec['Title'].'</span>';
	}
	return strtr($template,array(
		'<%DEFAULT_TITLE%>'	=> $defaultTitle,
		'<%DEFAULT_VALUE%>'	=> $defaultValue,
		'<%OPTIONS_TYPE%>'	=> $optionsType,
		'<%OPTIONS_PARENT%>'	=> $optionsParent
	));
}

function PopupDocumentEdit($template){
	$id=(int)$_POST['id'];
	
	$rec=dbGetRecordFromDb('SELECT Title, LeadElement, OrderElement, OrderDirection, TableId, ParentId, ParentField, `ActionCode`, `Order` FROM mycms_documents WHERE Id='.$id,__FILE__,__LINE__);
	
	$res=dbDoQuery('SELECT Id, Name FROM mycms_tables ORDER BY IF(`Order`,-1000/`Order`,0) ASC',__FILE__,__LINE__);
	while($tables[]=dbGetRecord($res));	
	$defaultTitle='';
	$defaultValue='';
	$optionsType='';	
	foreach($tables as $table){
		if($rec['TableId']==$table['Id']){
			$defaultTitle=$table['Name'];
			$defaultValue=$table['Id'];
			$optionsType.='<span class="item current" value="'.$table['Id'].'">'.$table['Name'].'</span>';
		}elseif($table['Id']){
			$optionsType.='<span class="item" value="'.$table['Id'].'">'.$table['Name'].'</span>';
		}
	}
	
	$res=dbDoQuery('SELECT Id, Title FROM mycms_documents WHERE Id<>'.$id.' ORDER BY IF(`Order`,-1000/`Order`,0) ASC',__FILE__,__LINE__);
	while($parents[]=dbGetRecord($res));
	$defaultTitleParent='';
	$defaultValueParent='';
	$optionsParent='<span class="item" value="0">&nbsp;</span>';	
	foreach($parents as $parent){
		if($rec['ParentId']==$parent['Id']){
			$defaultTitleParent=$parent['Title'];
			$defaultValueParent=$parent['Id'];
			$optionsParent.='<span class="item current" value="'.$parent['Id'].'">'.$parent['Title'].'</span>';
		}elseif($parent['Id']){
			$optionsParent.='<span class="item" value="'.$parent['Id'].'">'.$parent['Title'].'</span>';
		}
	}
	
	return strtr($template,array(
		'<%ID%>'			=> $id,
		'<%TITLE%>'			=> $rec['Title'],
		'<%LEADELEMENT%>'	=> $rec['LeadElement'],
		'<%ORDERELEMENT%>'	=> $rec['OrderElement'],
		'<%ORDERDIRECTION%>'=> $rec['OrderDirection'],
		'<%ACTIONCODE%>'	=> $rec['ActionCode'],
		'<%ORDER%>'			=> $rec['Order'],
		'<%DEFAULT_TITLE%>'	=> $defaultTitle,
		'<%DEFAULT_VALUE%>'	=> $defaultValue,
		'<%OPTIONS_TYPE%>'	=> $optionsType,
		'<%DEFAULT_TITLE_PARENT%>'	=> $defaultTitleParent,
		'<%DEFAULT_VALUE_PARENT%>'	=> $defaultValueParent,
		'<%OPTIONS_PARENT%>'	=> $optionsParent,
		'<%PARENTFIELD%>'	=> $rec['ParentField']
	));
}

function PopupTableEdit($template){
	$id=(int)$_POST['id'];
	$rec=dbGetRecordFromDb('SELECT Name, `Order` FROM mycms_tables WHERE Id='.$id,__FILE__,__LINE__);
	return strtr($template,array(
		'<%ID%>'	=> $id,
		'<%NAME%>'	=> $rec['Name'],
		'<%ORDER%>'	=> $rec['Order']
	));
}

function PopupTableCreate($template){
	$defaultTitle='';
	$defaultValue='';
	$optionsType='';
	$res=dbDoQuery('SELECT Id, Title FROM mycms_field_types',__FILE__,__LINE__);
	while($rec=dbGetRecord($res)){
		if(!$defaultTitle){
			$defaultTitle=$rec['Title'];
			$defaultValue=$rec['Id'];
			$optionsType.='<span class="item current" value="'.$rec['Id'].'">'.$rec['Title'].'</span>';
		}else{
			$optionsType.='<span class="item" value="'.$rec['Id'].'">'.$rec['Title'].'</span>';
		}
	}
	return strtr($template,array(
		'<%DEFAULT_TITLE%>'	=> $defaultTitle,
		'<%DEFAULT_VALUE%>'	=> $defaultValue,
		'<%OPTIONS_TYPE%>'	=> $optionsType
	));
}

function PopupFieldAdd($template){
	$tableId=(int)$_POST['table_id'];
	$defaultTitle='';
	$defaultValue='';
	$optionsType='';
	$res=dbDoQuery('SELECT Id, Title FROM mycms_field_types',__FILE__,__LINE__);
	while($rec=dbGetRecord($res)){
		if(!$defaultTitle){
			$defaultTitle=$rec['Title'];
			$defaultValue=$rec['Id'];
			$optionsType.='<span class="item current" value="'.$rec['Id'].'">'.$rec['Title'].'</span>';
		}else{
			$optionsType.='<span class="item" value="'.$rec['Id'].'">'.$rec['Title'].'</span>';
		}
	}
	return strtr($template,array(
		'<%TABLE_ID%>'		=> $tableId,
		'<%DEFAULT_TITLE%>'	=> $defaultTitle,
		'<%DEFAULT_VALUE%>'	=> $defaultValue,
		'<%OPTIONS_TYPE%>'	=> $optionsType
	));
}

function PopupFieldEdit($template){
	$id=(int)$_POST['id'];
	$tableId=(int)$_POST['table_id'];
	$field=dbGetRecordFromDb('SELECT Name, TypeId FROM mycms_fields WHERE Id='.$id,__FILE__,__LINE__);
	
	$defaultTitle='';
	$defaultValue='';
	$optionsType='';
	$res=dbDoQuery('SELECT Id, Title FROM mycms_field_types',__FILE__,__LINE__);
	while($rec=dbGetRecord($res)){
		if($field['TypeId']==$rec['Id']){
			$defaultTitle=$rec['Title'];
			$defaultValue=$rec['Id'];
			$optionsType.='<span class="item current" value="'.$rec['Id'].'">'.$rec['Title'].'</span>';
		}else{
			$optionsType.='<span class="item" value="'.$rec['Id'].'">'.$rec['Title'].'</span>';
		}
	}
	return strtr($template,array(
		'<%ID%>'			=> $id,
		'<%NAME%>'			=> $field['Name'],
		'<%TABLE_ID%>'		=> $tableId,
		'<%DEFAULT_TITLE%>'	=> $defaultTitle,
		'<%DEFAULT_VALUE%>'	=> $defaultValue,
		'<%OPTIONS_TYPE%>'	=> $optionsType
	));
}

function PopupElementAdd($template){
	$documentId=(int)$_POST['document_id'];
	
	$fieldDefaultTitle='';
	$fieldDefaultValue='';
	$fieldOptions='';
	$query = 'SELECT t1.Id, t1.Name '
			.'FROM mycms_fields AS t1 '
			.'WHERE t1.TableId=(SELECT TableId FROM mycms_documents WHERE Id='.$documentId.')';
	$res=dbDoQuery($query,__FILE__,__LINE__);
	while($rec=dbGetRecord($res)){
		if(!$fieldDefaultTitle){
			$fieldDefaultTitle=$rec['Name'];
			$fieldDefaultValue=$rec['Id'];
			$fieldOptions.='<span class="item current" value="'.$rec['Id'].'">'.$rec['Name'].'</span>';
		}else{
			$fieldOptions.='<span class="item" value="'.$rec['Id'].'">'.$rec['Name'].'</span>';
		}
	}
	$fieldOptions.='<span class="item" value="0">===Abstract===</span>';
	
	krnLoadLib('define');
	$elementsCodes=GetElementCodes();
	$typeDefaultTitle='';
	$typeDefaultValue='';
	$typeOptions='';
	foreach($elementsCodes as $code=>$info){
		if(!$typeDefaultTitle){
			$typeDefaultTitle=$info['Title'];
			$typeDefaultValue=$code;
			$typeOptions.='<span class="item current" value="'.$code.'">'.$info['Title'].'</span>';
		}else{
			$typeOptions.='<span class="item" value="'.$code.'">'.$info['Title'].'</span>';
		}
	}
	return strtr($template,array(
		'<%DOCUMENT_ID%>'			=> $documentId,
		'<%FIELD_DEFAULT_TITLE%>'	=> $fieldDefaultTitle,
		'<%FIELD_DEFAULT_VALUE%>'	=> $fieldDefaultValue,
		'<%FIELD_OPTIONS%>'			=> $fieldOptions,
		'<%TYPE_DEFAULT_TITLE%>'	=> $typeDefaultTitle,
		'<%TYPE_DEFAULT_VALUE%>'	=> $typeDefaultValue,
		'<%TYPE_OPTIONS%>'			=> $typeOptions
	));
}

function PopupElementEdit($template){
	$id=(int)$_POST['id'];
	$documentId=(int)$_POST['document_id'];
	$element=dbGetRecordFromDb('SELECT Title, FieldId, Type, `Order`, `Show` FROM mycms_elements WHERE Id='.$id,__FILE__,__LINE__);
	
	$fieldDefaultTitle='';
	$fieldDefaultValue='';
	$fieldOptions='';
	$query = 'SELECT t1.Id, t1.Name '
			.'FROM mycms_fields AS t1 '
			.'WHERE t1.TableId=(SELECT TableId FROM mycms_documents WHERE Id='.$documentId.')';
	$res=dbDoQuery($query,__FILE__,__LINE__);
	while($rec=dbGetRecord($res)){
		if($element['FieldId']==$rec['Id']){
			$fieldDefaultTitle=$rec['Name'];
			$fieldDefaultValue=$rec['Id'];
			$fieldOptions.='<span class="item current" value="'.$rec['Id'].'">'.$rec['Name'].'</span>';
		}else{
			$fieldOptions.='<span class="item" value="'.$rec['Id'].'">'.$rec['Name'].'</span>';
		}
	}
	$fieldOptions.='<span class="item'.($element['FieldId']==0?' current':'').'" value="0">===Abstract===</span>';
	
	krnLoadLib('define');
	$elementsCodes=GetElementCodes();
	$typeDefaultTitle='';
	$typeDefaultValue='';
	$typeOptions='';
	foreach($elementsCodes as $code=>$info){
		if($element['Type']==$code){
			$typeDefaultTitle=$info['Title'];
			$typeDefaultValue=$code;
			$typeOptions.='<span class="item current" value="'.$code.'">'.$info['Title'].'</span>';
		}else{
			$typeOptions.='<span class="item" value="'.$code.'">'.$info['Title'].'</span>';
		}
	}
	return strtr($template,array(
		'<%ID%>'					=> $id,
		'<%DOCUMENT_ID%>'			=> $documentId,
		'<%TITLE%>'					=> $element['Title'],
		'<%FIELD_DEFAULT_TITLE%>'	=> $fieldDefaultTitle,
		'<%FIELD_DEFAULT_VALUE%>'	=> $fieldDefaultValue,
		'<%FIELD_OPTIONS%>'			=> $fieldOptions,
		'<%TYPE_DEFAULT_TITLE%>'	=> $typeDefaultTitle,
		'<%TYPE_DEFAULT_VALUE%>'	=> $typeDefaultValue,
		'<%TYPE_OPTIONS%>'			=> $typeOptions,
		'<%ORDER%>'					=> $element['Order'],
		'<%SHOW%>'					=> $element['Show']?' checked="checked"':'',
		'<%CB_SHOW%>'				=> $element['Show']?' cb-checked':''
	));
}

function PopupUserAdd($template){
	krnLoadLib('define');
	$userStatus=GetUserStatuses();
	
	$defaultTitle='';
	$defaultValue='';
	$optionsType='';
	foreach($userStatus as $code=>$status){
		if(!$defaultTitle){
			$defaultTitle=$status;
			$defaultValue=$code;
			$optionsType.='<span class="item current" value="'.$code.'">'.$status.'</span>';
		}else{
			$optionsType.='<span class="item" value="'.$code.'">'.$status.'</span>';
		}
	}
	return strtr($template,array(
		'<%DEFAULT_TITLE%>'	=> $defaultTitle,
		'<%DEFAULT_VALUE%>'	=> $defaultValue,
		'<%OPTIONS_TYPE%>'	=> $optionsType
	));
}

function PopupUserEdit($template){
	$id=(int)$_POST['id'];
	
	krnLoadLib('define');
	$userStatus=GetUserStatuses();
	
	$defaultTitle='';
	$defaultValue='';
	$optionsType='';
	$rec=dbGetRecordFromDb('SELECT `Name`, `Login`, `Password`, `Status` FROM mycms_users WHERE Id='.$id,__FILE__,__LINE__);
	foreach($userStatus as $code=>$status){
		if($rec['Status']==$code){
			$defaultTitle=$status;
			$defaultValue=$code;
			$optionsType.='<span class="item current" value="'.$code.'">'.$status.'</span>';
		}elseif($code){
			$optionsType.='<span class="item" value="'.$code.'">'.$status.'</span>';
		}
	}
	return strtr($template,array(
		'<%ID%>'			=> $id,
		'<%NAME%>'			=> $rec['Name'],
		'<%LOGIN%>'			=> $rec['Login'],
		'<%PASSWORD%>'		=> '',
		'<%DEFAULT_TITLE%>'	=> $defaultTitle,
		'<%DEFAULT_VALUE%>'	=> $defaultValue,
		'<%OPTIONS_TYPE%>'	=> $optionsType
	));
}

function PopupRecordAdd($template){
	$documentId=(int)$_POST['document_id'];
	$params=$_POST['params']&&is_array($_POST['params'])?$_POST['params']:array();
	$content='';
	$element=LoadTemplate('form_row');
	
	krnLoadLib('define');
	krnLoadLib('settings');
	global $Settings;
	$elementsCodes=GetElementCodes();
	$fields=GetFieldsList($documentId);
	$fieldsIds=GetSubArray($fields,'Id');
	$properties=$Settings->GetElementsSettings($fieldsIds);
	$total=count($fields);
	$i=0;
	foreach($fields as $name=>$info){
		$i++;
		$skip=false;
		$inp=LoadTemplate($elementsCodes[$info['Type']]['Input']);
		$elementProperties=$properties[$info['Id']];
		$attributes=$elementProperties[1]?' important="true"':'';
		switch($info['Type']){
			case 1:
				if($elementProperties[5])$attributes.=' syntax="'.$elementProperties[5].'"';
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%VALUE%>'			=> $elementProperties[3],
					'<%MAXLENGTH%>'		=> $elementProperties[10],
					'<%ATTRIBUTES%>'	=> $attributes
				));
			break;
			case 2:
				if($elementProperties[5])$attributes.=' syntax="'.$elementProperties[5].'"';
				$size=($elementProperties[22]||$elementProperties[23])?'style="'.($elementProperties[22]?'width:'.$elementProperties[22].'px;':'').($elementProperties[23]?'height:'.$elementProperties[23].'px;':'').'"':'';
				if($elementProperties[20]=='wysiwyg'){
					$inp=strtr(LoadTemplate('inp_wysiwyg'),array(
						'<%IDNUM%>'			=> '',
						'<%NAME%>'			=> $name,
						'<%VALUE%>'			=> $elementProperties[3],
						'<%SIZE%>'			=> $size,
						'<%ATTRIBUTES%>'	=> $attributes
					));						
				}else{				
					$inp=strtr($inp,array(
						'<%IDNUM%>'			=> '',
						'<%NAME%>'			=> $name,
						'<%VALUE%>'			=> $elementProperties[3],
						'<%SIZE%>'			=> $size,
						'<%ATTRIBUTES%>'	=> $attributes
					));
				}			
			break;
			case 3:
				$dateType=$elementProperties[30];
				$now=date($dateType=='datetime'?'d/m/Y H:i':($dateType=='time'?'H:i':'d/m/Y'));
				$class=$dateType=='datetime'?'':($dateType=='time'?' time-only':' date-only');
				$attributes.=' syntax="date'.($elementProperties[5]?','.$elementProperties[5]:'').'"';
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%CLASS%>'			=> $class,
					'<%NAME%>'			=> $name,
					'<%VALUE%>'			=> $elementProperties[3]?$elementProperties[3]:$now,
					'<%ATTRIBUTES%>'	=> $attributes
				));
			break;
			case 4:			
				if($resize=$elementProperties[42])continue 2;
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%CAPTION%>'		=> '',
					'<%VALUE%>'			=> '',
					'<%RECORDID%>'		=> '1',
					'<%ELEMENTID%>'		=> $info['Id'],
					'<%UPLOADERCODE%>'	=> 'Image',
					'<%UPLOADERINFO%>'	=> 'id:1,element_id:'.$info['Id']
				));
			break;
			case 5:
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%CAPTION%>'		=> '',
					'<%VALUE%>'			=> '',
					'<%RECORDID%>'		=> '1',
					'<%ELEMENTID%>'		=> $info['Id'],
					'<%UPLOADERCODE%>'	=> 'File',
					'<%UPLOADERINFO%>'	=> 'id:1,element_id:'.$info['Id']
				));
			break;
			case 6:
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%CB_CHECKED%>'	=> $elementProperties[3]?' cb-checked':'',
					'<%CHECKED%>'		=> $elementProperties[3]?' checked="checked"':'',
					'<%ATTRIBUTES%>'	=> $attributes
				));
			break;
			case 7:
				$variants=explode('; ',$elementProperties[70]);
				$inpRow=$inp;
				$inp='';
				foreach($variants as $variant){
					$inp.=($inp?'<br/>':'').strtr($inpRow,array(
						'<%NAME%>'		=> $name,
						'<%VALUE%>'		=> $variant,
						'<%TITLE%>'		=> $variant,
						'<%RB_CHECKED%>'=> '',
						'<%CHECKED%>'	=> ''
					));
				}				
			break;
			case 8:
				$defaultTitle='';
				$defaultValue='';
				$options=$elementProperties[1]?'':'<span class="item current" value="0">&nbsp;</span>';
				preg_match_all('/([A-z0-9]+)/',$elementProperties[81],$matchFields);
				$res=dbDoQuery('SELECT `'.$elementProperties[82].'`, `'.implode('`, `',$matchFields[0]).'` FROM `'.$elementProperties[80].'`',__FILE__,__LINE__);		
				while($rec=dbGetRecord($res)){
					if(!$defaultTitle&&$elementProperties[1]){
						$defaultTitle=strtr($elementProperties[81],$rec);
						$defaultValue=$rec[$elementProperties[82]];
						$options.='<span class="item current" value="'.$rec[$elementProperties[82]].'">'.strtr($elementProperties[81],$rec).'</span>';
					}else{
						$options.='<span class="item" value="'.$rec[$elementProperties[82]].'">'.strtr($elementProperties[81],$rec).'</span>';
					}
				}
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%DEFAULT_TITLE%>'	=> $defaultTitle,
					'<%DEFAULT_VALUE%>'	=> $defaultValue,
					'<%OPTIONS%>'		=> $options,
					'<%ATTRIBUTES%>'	=> $attributes
				));
			break;
			case 9:
				$defaultTitle='';
				$defaultValue='';
				$options='';
				preg_match_all('/([A-z0-9]+)/',$elementProperties[91],$matchFields);
				$res=dbDoQuery('SELECT `'.$elementProperties[92].'`, `'.implode('`, `',$matchFields[0]).'` FROM `'.$elementProperties[90].'`',__FILE__,__LINE__);		
				while($rec=dbGetRecord($res)){
					if(!$defaultTitle&&$elementProperties[1]){
						$defaultTitle=strtr($elementProperties[91],$rec);
						$defaultValue=$rec[$elementProperties[92]];
						$options.='<span class="item current" value="'.$rec[$elementProperties[92]].'">'.strtr($elementProperties[91],$rec).'</span>';
					}else{
						$options.='<span class="item" value="'.$rec[$elementProperties[92]].'">'.strtr($elementProperties[91],$rec).'</span>';
					}
				}
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $elementProperties[96],
					'<%DEFAULT_TITLE%>'	=> $defaultTitle,
					'<%DEFAULT_VALUE%>'	=> $defaultValue,
					'<%OPTIONS%>'		=> $options,
					'<%ATTRIBUTES%>'	=> $attributes
				));
			break;
			default:
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%VALUE%>'			=> '',
					'<%ATTRIBUTES%>'	=> $attributes
				));
			break;
		}
		if(isset($params['parent_record_field']) && $name==$params['parent_record_field'] && isset($params['parent_record_id'])){
			$content.='<input type="hidden" name="'.$name.'" value="'.$params['parent_record_id'].'"/>';
		}else{
			$content.=strtr($element,array(
				'<%LABEL%>'			=> $info['Title'],
				'<%ELEMENT%>'		=> $inp,
				'<%HIDDEN%>'		=> $i==$total?'<input type="hidden" name="id" value="1"/>':''			
			));
		}
		if($elementProperties[4]){
			$content.='<div class="comment">'.$elementProperties[4].'</div>';
		}
	}
	return strtr($template,array(
		'<%DOCUMENT_ID%>'	=> $documentId,
		'<%CONTENT%>'		=> $content
	));
}

function PopupRecordEdit($template){
	$id=(int)$_POST['id'];
	$documentId=(int)$_POST['document_id'];
	$params=$_POST['params']&&is_array($_POST['params'])?$_POST['params']:array();
	$element=LoadTemplate('form_row');
	$content='';
	
	krnLoadLib('define');
	krnLoadLib('settings');
	global $Settings;
	$elementsCodes=GetElementCodes();
	$fields=GetFieldsList($documentId);
	$fieldsIds=GetSubArray($fields,'Id');
	$properties=$Settings->GetElementsSettings($fieldsIds);
	$table=dbGetValueFromDb('SELECT t1.Name FROM mycms_tables AS t1 WHERE t1.Id=(SELECT t2.TableId FROM mycms_documents AS t2 WHERE t2.Id='.$documentId.')',__FILE__,__LINE__);
	$query = 'SELECT `'.implode('`, `',array_diff(array_keys($fields),array(''))).'` '
			.'FROM `'.$table.'` '
			.'WHERE Id='.$id;	$record=dbGetRecordFromDb($query,__FILE__,__LINE__);
	$total=count($fields);
	$i=0;
	foreach($fields as $name=>$info){
		$i++;
		$inp=LoadTemplate($elementsCodes[$info['Type']]['Input']);
		$elementProperties=$properties[$info['Id']];
		$attributes=$elementProperties[1]?' important="true"':'';
		switch($info['Type']){
			case 1:
				if($elementProperties[5])$attributes.=' syntax="'.$elementProperties[5].'"';
				if($elementProperties[5]=='password')$attributes.=' placeholder="Complete to change password"';
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%VALUE%>'			=> $elementProperties[5]!='password' ? $record[$name] : '',
					'<%MAXLENGTH%>'		=> $elementProperties[10],
					'<%ATTRIBUTES%>'	=> $attributes
				));
			break;
			case 2:
				if($elementProperties[5])$attributes.=' syntax="'.$elementProperties[5].'"';
				$size=($elementProperties[22]||$elementProperties[23])?'style="'.($elementProperties[22]?'width:'.$elementProperties[22].'px;':'').($elementProperties[23]?'height:'.$elementProperties[23].'px;':'').'"':'';
				if($elementProperties[20]=='wysiwyg'){
					$inp=strtr(LoadTemplate('inp_wysiwyg'),array(
						'<%IDNUM%>'			=> '',
						'<%NAME%>'			=> $name,
						'<%VALUE%>'			=> $record[$name],
						'<%SIZE%>'			=> $size,
						'<%ATTRIBUTES%>'	=> $attributes
					));						
				}else{
					$inp=strtr($inp,array(
						'<%IDNUM%>'			=> '',
						'<%NAME%>'			=> $name,
						'<%VALUE%>'			=> $record[$name],
						'<%SIZE%>'			=> $size,
						'<%ATTRIBUTES%>'	=> $attributes
					));
				}			
			break;
			case 3:
				$dateType=$elementProperties[30];
				$class=$dateType=='datetime'?'':($dateType=='time'?' time-only':' date-only');
				$attributes.=' syntax="date'.($elementProperties[5]?','.$elementProperties[5]:'').'"';
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%CLASS%>'			=> $class,
					'<%NAME%>'			=> $name,
					'<%VALUE%>'			=> ModifiedDateTime($record[$name],$dateType),
					'<%ATTRIBUTES%>'	=> $attributes
				));
			break;
			case 4:		
				if($resize=$elementProperties[42])continue 2;
				krnLoadLib('files');
				if($record[$name])$fileInfo=flGetInfo($record[$name]);
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%CAPTION%>'		=> $record[$name]?$fileInfo['basename']:'',
					'<%VALUE%>'			=> '',
					'<%RECORDID%>'		=> $id,
					'<%ELEMENTID%>'		=> $info['Id'],
					'<%UPLOADERCODE%>'	=> 'Image',
					'<%UPLOADERINFO%>'	=> 'id:'.$id.',element_id:'.$info['Id']
				));
			break;
			case 5:
				krnLoadLib('files');
				if($record[$name])$fileInfo=flGetInfo($record[$name]);
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%CAPTION%>'		=> $record[$name]?$fileInfo['basename']:'',
					'<%VALUE%>'			=> '',
					'<%RECORDID%>'		=> $id,
					'<%ELEMENTID%>'		=> $info['Id'],
					'<%UPLOADERCODE%>'	=> 'File',
					'<%UPLOADERINFO%>'	=> 'id:'.$id.',element_id:'.$info['Id']
				));
			break;
			case 6:
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%CB_CHECKED%>'	=> $record[$name]?' cb-checked':'',
					'<%CHECKED%>'		=> $record[$name]?' checked="checked"':'',
					'<%ATTRIBUTES%>'	=> $attributes
				));
			break;
			case 7:
				$variants=explode('; ',$elementProperties[70]);
				$inpRow=$inp;
				$inp='';
				foreach($variants as $variant){
					$inp.=($inp?'<br/>':'').strtr($inpRow,array(
						'<%NAME%>'		=> $name,
						'<%VALUE%>'		=> $variant,
						'<%TITLE%>'		=> $variant,
						'<%RB_CHECKED%>'=> $record[$name]==$variant?' rb-checked':'',
						'<%CHECKED%>'	=> $record[$name]==$variant?' checked="checked"':''
					));
				}
			break;
			case 8:
				$defaultTitle='';
				$defaultValue='';
				$options=$elementProperties[1]?'':'<span class="item'.(!$record[$name]?' current':'').'" value="0">&nbsp;</span>';
				$sqlCondition='';
				if($elementProperties[83]){
					$table=dbGetValueFromDb('SELECT t1.Name FROM mycms_tables AS t1 WHERE t1.Id=(SELECT t2.TableId FROM mycms_documents AS t2 WHERE t2.Id='.$documentId.')',__FILE__,__LINE__);
					if($table==$elementProperties[80]){
						$sqlCondition=' WHERE Id<>'.$id;
					}
				}
				preg_match_all('/([A-z0-9]+)/',$elementProperties[81],$matchFields);
				$resOpt=dbDoQuery('SELECT `'.$elementProperties[82].'`, `'.implode('`, `',$matchFields[0]).'` FROM `'.$elementProperties[80].'`'.$sqlCondition,__FILE__,__LINE__);
				while($opt=dbGetRecord($resOpt)){
					if($opt[$elementProperties[82]]==$record[$name]){
						$defaultTitle=strtr($elementProperties[81],$opt);
						$defaultValue=$opt[$elementProperties[82]];
						$options.='<span class="item current" value="'.$opt[$elementProperties[82]].'">'.strtr($elementProperties[81],$opt).'</span>';
					}else{
						$options.='<span class="item" value="'.$opt[$elementProperties[82]].'">'.strtr($elementProperties[81],$opt).'</span>';
					}
				}
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%DEFAULT_TITLE%>'	=> $defaultTitle,
					'<%DEFAULT_VALUE%>'	=> $defaultValue,
					'<%OPTIONS%>'		=> $options,
					'<%ATTRIBUTES%>'	=> $attributes
				));
			break;
			case 9:
				$options='';
				$sqlCondition='';
				if($elementProperties[93]){
					$table=dbGetValueFromDb('SELECT t1.Name FROM mycms_tables AS t1 WHERE t1.Id=(SELECT t2.TableId FROM mycms_documents AS t2 WHERE t2.Id='.$documentId.')',__FILE__,__LINE__);
					if($table==$elementProperties[90]){
						$sqlCondition=' WHERE Id<>'.$id;
					}
				}
				$values=array();
				$resValues=dbDoQuery('SELECT `'.$elementProperties[98].'` FROM `'.$elementProperties[96].'` WHERE `'.$elementProperties[97].'`='.$id,__FILE__,__LINE__);
				while($recValue=dbGetRow($resValues)){
					$values[]=$recValue[0];
				}				
				
				preg_match_all('/([A-z0-9]+)/',$elementProperties[91],$matchFields);
				if($elementProperties[94]){
					$resOpt=dbDoQuery('SELECT `'.$elementProperties[92].'`, `'.implode('`, `',$matchFields[0]).'` FROM `'.$elementProperties[90].'`'.$sqlCondition.' '.($elementProperties[94]?'ORDER BY '.$elementProperties[94].' '.$elementProperties[95]:''),__FILE__,__LINE__);
				}else{
					$resOpt=dbDoQuery('SELECT `'.$elementProperties[92].'`, `'.implode('`, `',$matchFields[0]).'` FROM `'.$elementProperties[90].'`'.$sqlCondition,__FILE__,__LINE__);
				}
				$counter=0;			
				while($opt=dbGetRecord($resOpt)){
					if(in_array($opt[$elementProperties[92]],$values)){
						$options.='<span class="item current" value="'.$opt[$elementProperties[92]].'">'.strtr($elementProperties[91],$opt).'</span>';
						$counter++;
					}else{
						$options.='<span class="item" value="'.$opt[$elementProperties[92]].'">'.strtr($elementProperties[91],$opt).'</span>';
					}
				}
				$defaultTitle='Выбрано '.$counter.' '.Word125($counter,'значение','значения','значений');
				$defaultValue=implode(',',$values);
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $elementProperties[96],
					'<%DEFAULT_TITLE%>'	=> $defaultTitle,
					'<%DEFAULT_VALUE%>'	=> $defaultValue,
					'<%OPTIONS%>'		=> $options,
					'<%ATTRIBUTES%>'	=> $attributes
				));
			break;
			default:
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%VALUE%>'			=> $record[$name],
					'<%ATTRIBUTES%>'	=> $attributes
				));
			break;
		}
		if(isset($params['parent_record_field']) && $name==$params['parent_record_field'] && isset($params['parent_record_id'])){
			$content.='<input type="hidden" name="'.$name.'" value="'.$params['parent_record_id'].'"/>';
		}

		if ($i == 1) {
			$content .= '<input type="hidden" name="id" value="'.$id.'"/>';
		}

		$content.=strtr($element,array(
			'<%LABEL%>'			=> $info['Title'],
			'<%ELEMENT%>'		=> $inp,
			'<%HIDDEN%>'		=> ''			
		));

		
		if($elementProperties[4]){
			$content.='<div class="comment">'.$elementProperties[4].'</div>';
		}
	}
	return strtr($template,array(
		'<%DOCUMENT_ID%>'	=> $documentId,
		'<%CONTENT%>'		=> $content
	));
}

function PopupElementProperties($template){
	krnLoadLib('define');
	$elementId=(int)$_POST['element_id'];
	$element=dbGetRecordFromDb('SELECT Title, `Type` FROM mycms_elements WHERE Id='.$elementId,__FILE__,__LINE__);
	$elementProperties=GetElementProperties($element['Type']);
	$formRow=LoadTemplate('form_row');
	$content='';
	
	$res=dbDoQuery('SELECT `Code`, `Value` FROM mycms_properties WHERE ElementId='.$elementId.' AND (`Code`='.implode(' OR `Code`=',array_keys($elementProperties)).')',__FILE__,__LINE__);
	while($rec=dbGetRecord($res)){
		$propValues[$rec['Code']]=$rec['Value'];
	}
	
	foreach($elementProperties as $code=>$info){
		$inp=LoadTemplate($info['Input']);
		$name='p['.$code.']';
		
		if(!isset($propValues[$code])){
			if($code==81 || $code==91)$propValues[$code]='Title';
			elseif($code==82 || $code==92)$propValues[$code]='Id';
		}
		
		switch($info['Input']){
			case 'inp_text':
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%VALUE%>'			=> $propValues[$code],
					'<%MAXLENGTH%>'		=> '255',
					'<%ATTRIBUTES%>'	=> ''
				));
			break;
			case 'inp_textarea':
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%VALUE%>'			=> $propValues[$code],
					'<%SIZE%>'			=> $code==4?' style="height: 65px;"':'',
					'<%ATTRIBUTES%>'	=> ''
				));
			break;
			case 'inp_checkbox':
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%CB_CHECKED%>'	=> $propValues[$code]?' cb-checked':'',
					'<%CHECKED%>'		=> $propValues[$code]?' checked="checked"':'',
					'<%ATTRIBUTES%>'	=> ''
				));
			break;
			case 'inp_select':
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%DEFAULT_TITLE%>'	=> $defaultTitle,
					'<%DEFAULT_VALUE%>'	=> $defaultValue,
					'<%OPTIONS%>'		=> $options,
					'<%ATTRIBUTES%>'	=> ''
				));
			break;
			default:
				$inp=strtr($inp,array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%VALUE%>'			=> $propValues[$code],
					'<%ATTRIBUTES%>'	=> ''
				));
			break;
		}
		
		$content.=strtr($formRow,array(
			'<%LABEL%>'			=> $info['Title'],
			'<%ELEMENT%>'		=> $inp,
			'<%HIDDEN%>'		=> ''			
		));
	}
	
	return strtr($template,array(
		'<%ELEMENT_ID%>'	=> $elementId,
		'<%ELEMENT_TITLE%>'	=> $element['Title'],
		'<%CONTENT%>'		=> $content
	));
}

function PopupNotifications($template){
	$element=LoadTemplate('notifications_el');
	$content='';
	
	$notifications=krnLoadModuleByName('notifications');
	$notifications_arr=$notifications->GetList()->ToArray();
	$ids=array();
	foreach($notifications_arr as $notification){
		$notification['DateTime']=date('m/d/Y \a\t H:i',strtotime($notification['DateTime']));
		$content.=SetAtribs($element,$notification);
		array_push($ids,$notification['Id']);
	}
	
	$notifications->SetViewed($ids);
	
	return strtr($template,array(
		'<%CONTENT%>'	=> $content
	)); 
}

?>
