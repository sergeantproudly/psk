<?php

class fields extends krn_abstract{
	
	function __construct(){
		parent::__construct();
		$this->mode=$_GET['mode']?$_GET['mode']:'Browse';
		$this->table=dbGetRecordFromDb('SELECT Id, Name FROM mycms_tables WHERE Id='.(int)$_GET['table_id'],__FILE__,__LINE__);
	}
	
	function GetResult(){
		$result=$this->{$this->mode}();
		return $result;
	}
	
	function Browse(){
		$table=new BrowserTable(array('Fieldname','Type','SQL-Type','Is element'));
		$res=dbDoQuery('DESCRIBE `'.$this->table['Name'].'`',__FILE__,__LINE__);
		$query = 'SELECT t1.Id, t1.Name, '
				.'t2.Title AS TypeTitle, t2.Value AS TypeSql, '
				.'(SELECT COUNT(t3.Id) FROM mycms_elements AS t3 WHERE t3.FieldId=t1.Id) AS IsElement '
				.'FROM mycms_fields AS t1 LEFT JOIN mycms_field_types AS t2 ON t1.TypeId=t2.Id '
				.'WHERE t1.TableId='.$this->table['Id'];
		$res=dbDoQuery($query,__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			if($rec['Name']=='Id')continue;
			$row=array(
				$rec['Name'],
				$rec['TypeTitle'],
				$rec['TypeSql'],
				$rec['IsElement']?'yes':'no'
			);
			$actionEdit='Popup(\'field_edit\',600,{\'id\':'.$rec['Id'].',\'table_id\':'.$this->table['Id'].'});';
			$actionDelete='fieldDeleteConfirm(this,'.$this->table['Id'].','.$rec['Id'].');';
			$table->AddBodyRow($rec['Id'],$actionEdit,$actionDelete,$row);
		}
		
		$result=LoadTemplate('base_browser');
		$toolAdd=strtr(LoadTemplate('tool_add'),array(
			'<%ACTION%>'=>'fieldAdd(this,'.$this->table['Id'].');'
		));
		$result=strtr($result,array(
			'<%DOCUMENT_TITLE%>'		=> 'Table fields &#171;'.$this->table['Name'].'&#187;',
			'<%BREAD_CRUMBS%>'			=> '<a href="index.php?module=tables">Used tables</a> &rarr; ',
			'<%TOOLS_RIGHT_TOP%>'		=> '',
			'<%ACTION%>'				=> 'index.php#',
			'<%FILTER%>'				=> '',
			'<%TABLE%>'					=> $table->GetTable(),
			'<%TOOLS_LEFT_BOTTOM%>'		=> GetButton('Edit',false,'fieldEdit(this,'.$this->table['Id'].');').' '.GetButton('Delete',false,'fieldDeleteConfirm(this,'.$this->table['Id'].');'),
			'<%TOOLS_RIGHT_BOTTOM%>'	=> $toolAdd.' '.GetButton('Scan table',false,'fieldScanTable('.$this->table['Id'].');')
		));
		return $result;
	}
	
	function Add(){
		$count=(int)$_GET['count']?(int)$_GET['count']:1;
		$element=LoadTemplate('field_add');
		$content='';
		
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
		
		for($i=1;$i<=$count;$i++){
			$content.=strtr($element,array(
				'<%EVEN%>'			=> isEven($i)?' form-even':'',
				'<%NUM%>'			=> $i,
				'<%DEFAULT_TITLE%>'	=> $defaultTitle,
				'<%DEFAULT_VALUE%>'	=> $defaultValue,
				'<%OPTIONS_TYPE%>'	=> $optionsType
			));
		}
		
		$result=LoadTemplate('base_editor');
		$result=strtr($result,array(
			'<%DOCUMENT_TITLE%>'	=> 'Add new field'.($count>1?'s':''),
			'<%BREAD_CRUMBS%>'		=> '<a href="index.php?module=tables">Used tables</a> &rarr; <a href="index.php?module=fields&table_id='.$this->table['Id'].'">Table fields &#171;'.$this->table['Name'].'&#187;</a> &rarr; ',
			'<%CONTENT%>'			=> $content,
			'<%ACTION_FORM%>'		=> 'index.php?module=fields&act=AddField&table_id='.$this->table['Id'],
			'<%ACTION_CANCEL%>'		=> 'fieldAddCancel('.$this->table['Id'].');',
			'<%ACTION_SUBMIT%>'		=> 'fieldAddSendForm(this);'
		));
		return $result;
	}
	
	function AddField(){
		$name=is_array($_POST['name'])?$_POST['name']:trim($_POST['name']);
		$type=is_array($_POST['type'])?$_POST['type']:(int)$_POST['type'];
		$ids=$_POST['id'];
		$javascript='';
		$error=false;
		
		$res=dbDoQuery('DESCRIBE `'.$this->table['Name'].'`',__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$fields[]=$rec['Field'];
		}
		
		// много записей
		if(is_array($ids)){
			foreach($ids as $id){
				if($name[$id]){
					if(!in_array($name[$id],$fields)){
						$typeSql=dbGetValueFromDb('SELECT `Value` FROM mycms_field_types WHERE Id='.$type[$id],__FILE__,__LINE__);
						
						dbDoQuery('ALTER TABLE `'.$this->table['Name'].'` ADD `'.$name[$id].'` '.$typeSql.' NOT NULL ',__FILE__,__LINE__);
						dbDoQuery('INSERT INTO mycms_fields SET Name="'.dbEscape($name[$id]).'", TableId='.$this->table['Id'].', TypeId='.$type[$id],__FILE__,__LINE__);
						$javascript.='domD(gei(\'form'.$id.'\'));';
						
					}else{
						$error=true;
						$javascript.='stxAddErrorStatus(gei(\'name'.$id.'\'),\'В таблице &#171;'.$this->table['Name'].'&#187; уже есть поле с таким именем\');';
					}
					
				}else{
					$error=true;
					$javascript.='msgSetError(form,\'Заполнены не все необходимые поля\');';			
				}
			}
			if(!$error)$javascript='redirect(\'index.php?module=fields&table_id='.$this->table['Id'].'\');';
			
		// одна запись
		}else{
			if($name){
				if(!in_array($name,$fields)){
					$typeSql=dbGetValueFromDb('SELECT `Value` FROM mycms_field_types WHERE Id='.$type,__FILE__,__LINE__);
					
					dbDoQuery('ALTER TABLE `'.$this->table['Name'].'` ADD `'.$name.'` '.$typeSql.' NOT NULL ',__FILE__,__LINE__);
					dbDoQuery('INSERT INTO mycms_fields SET Name="'.dbEscape($name).'", TableId='.$this->table['Id'].', TypeId='.$type,__FILE__,__LINE__);
					$javascript.='reload(true);';
					
				}else{
					$javascript.='stxAddErrorStatus(domP(form.name),\'В таблице &#171;'.$this->table['Name'].'&#187; уже есть поле с таким именем\');';
				}
				
			}else{
				$javascript.='msgSetError(form,\'Заполнены не все необходимые поля\');';			
			}
		}
		return  $javascript;
	}
	
	function Edit(){
		$ids=$_POST['id'];
		$element=LoadTemplate('field_edit');
		$content='';
		$i=0;
		$sqlCondition='';
		foreach($ids as $id=>$on){
			$sqlCondition.=($sqlCondition?' OR Id='.$id:'Id='.$id);
		}
		
		$res=dbDoQuery('SELECT Id, Title FROM mycms_field_types',__FILE__,__LINE__);
		while($types[]=dbGetRecord($res));
		
		$res=dbDoQuery('SELECT * FROM mycms_fields WHERE '.$sqlCondition,__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$i++;
			
			$defaultTitle='';
			$defaultValue='';
			$optionsType='';
			foreach($types as $type){
				if($rec['TypeId']==$type['Id']){
					$defaultTitle=$type['Title'];
					$defaultValue=$type['Id'];
					$optionsType.='<span class="item current" value="'.$type['Id'].'">'.$type['Title'].'</span>';
				}else{
					$optionsType.='<span class="item" value="'.$type['Id'].'">'.$type['Title'].'</span>';
				}
			}
			
			$content.=strtr($element,array(
				'<%EVEN%>'			=> isEven($i)?' form-even':'',
				'<%NUM%>'			=> $rec['Id'],
				'<%NAME%>'			=> $rec['Name'],
				'<%DEFAULT_TITLE%>'	=> $defaultTitle,
				'<%DEFAULT_VALUE%>'	=> $defaultValue,
				'<%OPTIONS_TYPE%>'	=> $optionsType
			));
		}
		
		$result=LoadTemplate('base_editor');
		$result=strtr($result,array(
			'<%DOCUMENT_TITLE%>'	=> 'Редактировать пол'.(count($ids)>1?'я':'е'),
			'<%BREAD_CRUMBS%>'		=> '<a href="index.php?module=tables">Используемые таблицы</a> &rarr; <a href="index.php?module=fields&table_id='.$this->table['Id'].'">Поля таблицы &#171;'.$this->table['Name'].'&#187;</a> &rarr; ',
			'<%CONTENT%>'			=> $content,
			'<%ACTION_FORM%>'		=> 'index.php?module=fields&act=EditField&table_id='.$this->table['Id'],
			'<%ACTION_CANCEL%>'		=> 'fieldEditCancel('.$this->table['Id'].');',
			'<%ACTION_SUBMIT%>'		=> 'fieldEditSendForm(this);'
		));
		return $result;
	}
	
	function EditField(){
		$name=is_array($_POST['name'])?$_POST['name']:trim($_POST['name']);
		$type=is_array($_POST['type'])?$_POST['type']:(int)$_POST['type'];
		$ids=$_POST['id'];
		$javascript='';
		$error=false;
		
		$res=dbDoQuery('DESCRIBE `'.$this->table['Name'].'`',__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$fields[]=$rec['Field'];
		}
		
		// много записей
		if(is_array($ids)){
			foreach($ids as $id){
				if($name[$id]){
					$nameOld=dbGetValueFromDb('SELECT Name FROM mycms_fields WHERE Id='.$id,__FILE__,__LINE__);
					if($k=array_search($nameOld,$fields))unset($fields[$k]);
					if(!in_array($name[$id],$fields)){
						$typeSql=dbGetValueFromDb('SELECT `Value` FROM mycms_field_types WHERE Id='.$type[$id],__FILE__,__LINE__);
						
						dbDoQuery('ALTER TABLE `'.$this->table['Name'].'` CHANGE `'.$nameOld.'` `'.$name[$id].'` '.$typeSql.' NOT NULL ',__FILE__,__LINE__);
						dbDoQuery('UPDATE mycms_fields SET Name="'.dbEscape($name[$id]).'", TypeId='.$type[$id].' WHERE Id='.$id,__FILE__,__LINE__);
						$javascript.='domD(gei(\'form'.$id.'\'));';
						
					}else{
						$error=true;
						$javascript.='stxAddErrorStatus(gei(\'name'.$id.'\'),\'В таблице &#171;'.$this->table['Name'].'&#187; уже есть поле с таким именем\');';
					}
					
				}else{
					$error=true;
					$javascript.='msgSetError(form,\'Заполнены не все необходимые поля\');';			
				}
			}
			if(!$error)$javascript='redirect(\'index.php?module=fields&table_id='.$this->table['Id'].'\');';
			
		// одна запись
		}else{
			$id=$ids;
			if($name){
				$nameOld=dbGetValueFromDb('SELECT Name FROM mycms_fields WHERE Id='.$id,__FILE__,__LINE__);
				if($k=array_search($nameOld,$fields))unset($fields[$k]);
				if(!in_array($name,$fields)){
					$typeSql=dbGetValueFromDb('SELECT `Value` FROM mycms_field_types WHERE Id='.$type,__FILE__,__LINE__);
					
					dbDoQuery('ALTER TABLE `'.$this->table['Name'].'` CHANGE `'.$nameOld.'` `'.$name.'` '.$typeSql.' NOT NULL ',__FILE__,__LINE__);
					dbDoQuery('UPDATE mycms_fields SET Name="'.dbEscape($name).'", TypeId='.$type.' WHERE Id='.$id,__FILE__,__LINE__);
					$javascript.='reload(true);';
					
				}else{
					$javascript.='stxAddErrorStatus(domP(form.name),\'В таблице &#171;'.$this->table['Name'].'&#187; уже есть поле с таким именем\');';
				}
				
			}else{
				$javascript.='msgSetError(form,\'Заполнены не все необходимые поля\');';			
			}
		}
		return  $javascript;
	}
	
	function DeleteField(){
		$ids=is_array($_POST['id'])?$_POST['id']:Array((int)$_POST['id']=>'on');
		$javascript='';
		$sqlCondition='';
		foreach($ids as $id=>$on){
			$sqlCondition.=($sqlCondition?' OR Id='.$id:'Id='.$id);
		}
		$res=dbDoQuery('SELECT Name FROM mycms_fields WHERE '.$sqlCondition,__FILE__,__LINE__);
		$sqlCondition2='';
		while($rec=dbGetRecord($res)){
			$sqlCondition2.=($sqlCondition2?', DROP `'.$rec['Name'].'`':'DROP `'.$rec['Name'].'`');
		}
		dbDoQuery('ALTER TABLE `'.$this->table['Name'].'` '.$sqlCondition2,__FILE__,__LINE__);
		dbDoQuery('DELETE FROM mycms_fields WHERE '.$sqlCondition,__FILE__,__LINE__);
		$javascript='reload(true);';
		return $javascript;
	}
	
	function ScanTable(){
		$fieldTypeId=0;
		$res=dbDoQuery('SELECT Id, `Value` FROM mycms_field_types',__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			if(!$fieldTypeId)$fieldTypeId=$rec['Id'];
			if(preg_match('/^[A-z]+/i',$rec['Value'],$match)){
				$fieldTypes[$rec['Id']]=strtolower($match[0]);
			}		
		}
		$res=dbDoQuery('SELECT Name FROM mycms_fields WHERE TableId='.$this->table['Id'],__FILE__,__LINE__);
		while($fields[]=dbGetRecord($res));
		$res=dbDoQuery('DESCRIBE `'.$this->table['Name'].'`',__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			if($rec['Field']!='Id'){
				if(!in_array($rec['Field'],$fields)){
					if(preg_match('/^[A-z]+/i',$rec['Type'],$match)){
						$typeId=array_search($match[0],$fieldTypes);				
					}
					if(!$typeId)$typeId=$fieldTypeId;
					dbDoQuery('INSERT INTO mycms_fields SET Name="'.$rec['Field'].'", TableId='.$this->table['Id'].', TypeId='.$typeId,__FILE__,__LINE__);
				}
			}
		}
		return 'reload(true);';
	}
	
}

?>