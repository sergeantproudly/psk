<?php

krnLoadLib('define');

class elements extends krn_abstract{
	
	function __construct(){
		parent::__construct();
		$this->mode=$_GET['mode']?$_GET['mode']:'Browse';
		$this->document=dbGetRecordFromDb('SELECT Id, Title FROM mycms_documents WHERE Id='.(int)$_GET['document_id'],__FILE__,__LINE__);
	}
	
	function GetResult(){
		$result=$this->{$this->mode}();
		return $result;
	}
	
	function Browse(){
		$table=new BrowserTable(array('Title','Fieldname','Type','Order','Show'));
		$query = 'SELECT t1.Id, t1.Title, t1.FieldId, t1.Type, t1.Show, t1.`Order`, '
				.'t2.Name AS Field '
				.'FROM mycms_elements AS t1 LEFT JOIN mycms_fields AS t2 ON t2.Id=t1.FieldId '
				.'WHERE t1.DocumentId='.$this->document['Id'].' '
				.'ORDER BY IF(t1.`Order`,-1000/t1.`Order`,0) ASC';
		$res=dbDoQuery($query,__FILE__,__LINE__);
		$elementsCodes=GetElementCodes();
		while($rec=dbGetRecord($res)){
			if(!$rec['Field'])$rec['Field']='===Abstract===';
			$row=array(
				'<a href="#" onclick="Popup(\'element_properties\',600,{\'element_id\':'.$rec['Id'].'});return false;">'.htmlspecialchars($rec['Title']).'</a>',
				$rec['Field'],
				$elementsCodes[$rec['Type']]['Title'],			
				$rec['Order'],
				$rec['Show']?'да':'нет'
			);
			$actionEdit='Popup(\'element_edit\',600,{\'id\':'.$rec['Id'].',\'document_id\':'.$this->document['Id'].'});';
			$actionDelete='elementDeleteConfirm(this,'.$this->document['Id'].','.$rec['Id'].');';
			$table->AddBodyRow($rec['Id'],$actionEdit,$actionDelete,$row);
		}
		
		$result=LoadTemplate('base_browser');
		$toolAdd=strtr(LoadTemplate('tool_add'),array(
			'<%ACTION%>'=>'elementAdd(this,'.$this->document['Id'].');'
		));
		$result=strtr($result,array(
			'<%DOCUMENT_TITLE%>'		=> 'Document elements &#171;'.$this->document['Title'].'&#187;',
			'<%BREAD_CRUMBS%>'			=> '<a href="index.php?module=documents">Documents</a> &rarr; ',
			'<%TOOLS_RIGHT_TOP%>'		=> '',
			'<%FILTER%>'				=> '',
			'<%ACTION%>'				=> 'index.php#',
			'<%TABLE%>'					=> $table->GetTable(),
			'<%TOOLS_LEFT_BOTTOM%>'		=> GetButton('Edit',false,'elementEdit(this,'.$this->document['Id'].');').' '.GetButton('Delete',false,'elementDeleteConfirm(this,'.$this->document['Id'].');'),
			'<%TOOLS_RIGHT_BOTTOM%>'	=> $toolAdd
		));
		return $result;
	}
	
	function Add(){
		$count=(int)$_GET['count']?(int)$_GET['count']:1;
		$element=LoadTemplate('element_add');
		$content='';
		
		$fieldDefaultTitle='';
		$fieldDefaultValue='';
		$fieldOptions='';
		$query = 'SELECT t1.Id, t1.Name '
				.'FROM mycms_fields AS t1 '
				.'WHERE t1.TableId=(SELECT TableId FROM mycms_documents WHERE Id='.$this->document['Id'].')';
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
		
		for($i=1;$i<=$count;$i++){
			$content.=strtr($element,array(
				'<%EVEN%>'			=> isEven($i)?' form-even':'',
				'<%NUM%>'			=> $i,
				'<%FIELD_DEFAULT_TITLE%>'	=> $fieldDefaultTitle,
				'<%FIELD_DEFAULT_VALUE%>'	=> $fieldDefaultValue,
				'<%FIELD_OPTIONS%>'			=> $fieldOptions,
				'<%TYPE_DEFAULT_TITLE%>'	=> $typeDefaultTitle,
				'<%TYPE_DEFAULT_VALUE%>'	=> $typeDefaultValue,
				'<%TYPE_OPTIONS%>'			=> $typeOptions
			));
		}
		
		$result=LoadTemplate('base_editor');
		$result=strtr($result,array(
			'<%DOCUMENT_TITLE%>'	=> 'Add new field'.($count>1?'s':''),
			'<%BREAD_CRUMBS%>'		=> '<a href="index.php?module=documents">Documents</a> &rarr; <a href="index.php?module=elements&document_id='.$this->document['Id'].'">Document elements &#171;'.$this->document['Title'].'&#187;</a> &rarr; ',
			'<%CONTENT%>'			=> $content,
			'<%ACTION_FORM%>'		=> 'index.php?module=elements&act=AddElement&document_id='.$this->document['Id'],
			'<%ACTION_CANCEL%>'		=> 'elementAddCancel('.$this->document['Id'].');',
			'<%ACTION_SUBMIT%>'		=> 'elementAddSendForm(this);'
		));
		return $result;
	}
	
	function AddElement(){
		$title=is_array($_POST['title'])?$_POST['title']:trim($_POST['title']);
		$field=is_array($_POST['field'])?$_POST['field']:(int)$_POST['field'];
		$type=is_array($_POST['type'])?$_POST['type']:(int)$_POST['type'];
		$order=is_array($_POST['order'])?$_POST['order']:(int)$_POST['order'];
		$show=$_POST['show'];	
		$ids=$_POST['id'];
		$javascript='';
		$error=false;
		
		// много записей
		if(is_array($ids)){
			foreach($ids as $id){
				if($title[$id]){		
					dbDoQuery('INSERT INTO mycms_elements SET Title="'.$title[$id].'", DocumentId='.$this->document['Id'].', FieldId='.$field[$id].', Type='.$type[$id].', `Order`='.(int)$order[$id].', `Show`='.($show[$id]?'1':'0'),__FILE__,__LINE__);
					$javascript.='domD(gei(\'form'.$id.'\'));';
					
				}else{
					$error=true;
					$javascript.='msgSetError(form,\'Заполнены не все необходимые поля\');';			
				}
			}
			if(!$error)$javascript='redirect(\'index.php?module=elements&document_id='.$this->document['Id'].'\');';
			
		// одна запись
		}else{
			if($title){
				dbDoQuery('INSERT INTO mycms_elements SET Title="'.$title.'", DocumentId='.$this->document['Id'].', FieldId='.$field.', Type='.$type.', `Order`='.$order.', `Show`='.($show?'1':'0'),__FILE__,__LINE__);
				$javascript.='reload(true);';
				
			}else{
				$javascript.='msgSetError(form,\'Заполнены не все необходимые поля\');';			
			}
		}
		return $javascript;
	}
	
	function Edit(){
		$ids=$_POST['id'];
		$element=LoadTemplate('element_edit');
		$content='';
		$i=0;
		$sqlCondition='';
		foreach($ids as $id=>$on){
			$sqlCondition.=($sqlCondition?' OR Id='.$id:'Id='.$id);
		}
		
		$res=dbDoQuery('SELECT * FROM mycms_elements WHERE '.$sqlCondition,__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$i++;
			
			$fieldDefaultTitle='';
			$fieldDefaultValue='';
			$fieldOptions='';
			$query = 'SELECT t1.Id, t1.Name '
					.'FROM mycms_fields AS t1 '
					.'WHERE t1.TableId=(SELECT TableId FROM mycms_documents WHERE Id='.$this->document['Id'].')';
			$fieldRes=dbDoQuery($query,__FILE__,__LINE__);
			while($field=dbGetRecord($fieldRes)){
				if($rec['FieldId']==$field['Id']){
					$fieldDefaultTitle=$field['Name'];
					$fieldDefaultValue=$field['Id'];
					$fieldOptions.='<span class="item current" value="'.$field['Id'].'">'.$field['Name'].'</span>';
				}else{
					$fieldOptions.='<span class="item" value="'.$field['Id'].'">'.$field['Name'].'</span>';
				}
			}
			
			krnLoadLib('define');
			$elementsCodes=GetElementCodes();
			$typeDefaultTitle='';
			$typeDefaultValue='';
			$typeOptions='';
			foreach($elementsCodes as $code=>$info){
				if($rec['Type']==$code){
					$typeDefaultTitle=$info['Title'];
					$typeDefaultValue=$code;
					$typeOptions.='<span class="item current" value="'.$code.'">'.$info['Title'].'</span>';
				}else{
					$typeOptions.='<span class="item" value="'.$code.'">'.$info['Title'].'</span>';
				}
			}
			
			$content.=strtr($element,array(
				'<%EVEN%>'					=> isEven($i)?' form-even':'',
				'<%NUM%>'					=> $rec['Id'],
				'<%TITLE%>'					=> $rec['Title'],
				'<%FIELD_DEFAULT_TITLE%>'	=> $fieldDefaultTitle,
				'<%FIELD_DEFAULT_VALUE%>'	=> $fieldDefaultValue,
				'<%FIELD_OPTIONS%>'			=> $fieldOptions,
				'<%TYPE_DEFAULT_TITLE%>'	=> $typeDefaultTitle,
				'<%TYPE_DEFAULT_VALUE%>'	=> $typeDefaultValue,
				'<%TYPE_OPTIONS%>'			=> $typeOptions,
				'<%ORDER%>'					=> $rec['Order'],
				'<%SHOW%>'					=> $rec['Show']?' checked="checked"':'',
				'<%CB_SHOW%>'				=> $rec['Show']?' cb-checked':''
			));
		}
		
		$result=LoadTemplate('base_editor');
		$result=strtr($result,array(
			'<%DOCUMENT_TITLE%>'	=> 'Редактировать элемент'.(count($ids)>1?'ы':''),
			'<%BREAD_CRUMBS%>'		=> '<a href="index.php?module=documents">Документы</a> &rarr; <a href="index.php?module=elements&document_id='.$this->document['Id'].'">Элементы документа &#171;'.$this->document['Title'].'&#187;</a> &rarr; ',
			'<%CONTENT%>'			=> $content,
			'<%ACTION_FORM%>'		=> 'index.php?module=elements&act=EditElement&document_id='.$this->document['Id'],
			'<%ACTION_CANCEL%>'		=> 'elementEditCancel('.$this->document['Id'].');',
			'<%ACTION_SUBMIT%>'		=> 'elementEditSendForm(this);'
		));
		return $result;
	}
	
	function EditElement(){
		$title=is_array($_POST['title'])?$_POST['title']:trim($_POST['title']);
		$field=is_array($_POST['field'])?$_POST['field']:(int)$_POST['field'];
		$type=is_array($_POST['type'])?$_POST['type']:(int)$_POST['type'];
		$order=is_array($_POST['order'])?$_POST['order']:(int)$_POST['order'];
		$show=$_POST['show'];	
		$ids=$_POST['id'];
		$javascript='';
		$error=false;
		
		// много записей
		if(is_array($ids)){
			foreach($ids as $id){
				if($title[$id]){
					dbDoQuery('UPDATE mycms_elements SET Title="'.$title[$id].'", FieldId='.(int)$field[$id].', Type='.(int)$type[$id].', `Order`='.(int)$order[$id].', `Show`='.($show[$id]?'1':'0').' WHERE Id='.$id,__FILE__,__LINE__);
					$javascript.='domD(gei(\'form'.$id.'\'));';
					
				}else{
					$error=true;
					$javascript.='msgSetError(form,\'Заполнены не все необходимые поля\');';			
				}
			}
			if(!$error)$javascript='redirect(\'index.php?module=elements&document_id='.$this->document['Id'].'\');';
			
		// одна запись
		}else{
			$id=$ids;
			if($title){
					dbDoQuery('UPDATE mycms_elements SET Title="'.$title.'", FieldId='.$field.', Type='.$type.', `Order`='.$order.', `Show`='.($show?'1':'0').' WHERE Id='.$id,__FILE__,__LINE__);
					$javascript.='reload(true);';
				
			}else{
				$javascript.='msgSetError(form,\'Заполнены не все необходимые поля\');';			
			}
		}
		return  $javascript;
	}
	
	function DeleteElement(){
		$ids=is_array($_POST['id'])?$_POST['id']:Array((int)$_POST['id']=>'on');
		$javascript='';
		$sqlCondition='';
		foreach($ids as $id=>$on){
			$sqlCondition.=($sqlCondition?' OR Id='.$id:'Id='.$id);
		}
		dbDoQuery('DELETE FROM mycms_elements WHERE '.$sqlCondition,__FILE__,__LINE__);
		$javascript='reload(true);';
		return $javascript;
	}
	
	function SetProperties(){
		$elementId=(int)$_GET['element_id'];
		$elementType=dbGetValueFromDb('SELECT `Type` FROM mycms_elements WHERE Id='.$elementId,__FILE__,__LINE__);
		$elementProperties=GetElementProperties($elementType);
		foreach($elementProperties as $code=>$info){
			$value=$_POST['p'][$code];
			switch($info['Input']){
				case 'inp_checkbox':
					$value=$value?'1':'0';
				break;
			}
			if($id=dbGetValueFromDb('SELECT Id FROM mycms_properties WHERE ElementId='.$elementId.' AND `Code`='.$code,__FILE__,__LINE__)){
				dbDoQuery('UPDATE mycms_properties SET `Value`="'.$value.'" WHERE Id='.$id,__FILE__,__LINE__);
			}else{
				dbDoQuery('INSERT INTO mycms_properties SET ElementId='.$elementId.', `Code`='.$code.', `Value`="'.$value.'"',__FILE__,__LINE__);
			}
		}
		$javascript='reload(true);';
		return $javascript;
	}
	
}

?>