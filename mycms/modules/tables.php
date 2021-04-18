<?php

class tables extends krn_abstract{
	
	function __construct(){
		parent::__construct();
		$this->mode=$_GET['mode']?$_GET['mode']:'Browse';
	}
	
	function GetResult(){
		$result=$this->{$this->mode}();
		return $result;
	}
	
	function Browse(){
		$table= new BrowserTable(array('Table name','Order','Elements count','Fields count'));
		$res=dbDoQuery('SELECT t1.Id, t1.Name, t1.`Order`, (SELECT COUNT(t2.Id) FROM mycms_elements AS t2 WHERE t2.FieldId IN (SELECT t3.Id FROM mycms_fields AS t3 WHERE t3.TableId=t1.Id)) AS ElementsCount FROM mycms_tables AS t1 ORDER BY IF(t1.`Order`,-1000/t1.`Order`,0) ASC',__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$rec['FieldsCount']=dbGetNumRows(dbDoQuery('DESCRIBE `'.$rec['Name'].'`',__FILE__,__LINE__));
			$row=array(
				'<a href="index.php?module=fields&table_id='.$rec['Id'].'">'.htmlspecialchars($rec['Name']).'</a>',
				$rec['Order'],
				$rec['ElementsCount'],
				$rec['FieldsCount']
			);
			$actionEdit='Popup(\'table_edit\',600,{\'id\':'.$rec['Id'].'});';
			$actionDelete='tableDeleteConfirm(this,'.$rec['Id'].');';
			$table->AddBodyRow($rec['Id'],$actionEdit,$actionDelete,$row);
		}
		
		$result=LoadTemplate('base_browser');
		$toolAdd=strtr(LoadTemplate('tool_add'),array(
			'<%ACTION%>'=>'tableAdd(this);'
		));
		$result=strtr($result,array(
			'<%DOCUMENT_TITLE%>'		=> 'Tables',
			'<%BREAD_CRUMBS%>'			=> '',
			'<%TOOLS_RIGHT_TOP%>'		=> '',
			'<%ACTION%>'				=> 'index.php#',
			'<%FILTER%>'				=> '',
			'<%TABLE%>'					=> $table->GetTable(),
			'<%TOOLS_LEFT_BOTTOM%>'		=> GetButton('Edit',false,'tableEdit(this);').' '.GetButton('Delete',false,'tableDeleteConfirm(this);'),
			'<%TOOLS_RIGHT_BOTTOM%>'	=> $toolAdd.' '.GetButton('Create',false,'Popup(\'table_create\',600)')
		));
		return $result;
	}
	
	function Add(){
		$count=(int)$_GET['count']?(int)$_GET['count']:1;
		$element=LoadTemplate('table_add');
		$content='';
		for($i=1;$i<=$count;$i++){
			$content.=strtr($element,array(
				'<%EVEN%>'	=> isEven($i)?' form-even':'',
				'<%NUM%>'	=> $i
			));
		}
		
		$result=LoadTemplate('base_editor');
		$result=strtr($result,array(
			'<%DOCUMENT_TITLE%>'	=> 'Create new table'.($count>1?'s':''),
			'<%BREAD_CRUMBS%>'		=> '<a href="index.php?module=tables">Tables</a> &rarr; ',
			'<%CONTENT%>'			=> $content,
			'<%ACTION_FORM%>'		=> 'index.php?module=table&act=AddTable',
			'<%ACTION_CANCEL%>'		=> 'tableAddCancel();',
			'<%ACTION_SUBMIT%>'		=> 'tableAddSendForm(this);'
		));
		return $result;
	}
	
	function AddTable(){
		$name=is_array($_POST['name'])?$_POST['name']:trim($_POST['name']);
		$order=is_array($_POST['order'])?$_POST['order']:(int)$_POST['order'];
		$ids=$_POST['id'];
		$javascript='';
		$error=false;
		
		global $Config;
		$res=dbDoQuery('SHOW TABLES FROM `'.$Config['Db']['DbName'].'`',__FILE__,__LINE__);
		while($rec=dbGetRow($res)){
			$tables[]=$rec[0];		
		}
		
		// много записей
		if(is_array($ids)){
			foreach($ids as $id){
				if($name[$id]){
					if(!dbGetValueFromDb('SELECT COUNT(Id) FROM mycms_tables WHERE Name="'.$name[$id].'"',__FILE__,__LINE__)){					
						if(in_array($name[$id],$tables)){
							if(mb_substr($name[$id],0,6)!='mycms_'){
								dbDoQuery('INSERT INTO mycms_tables SET Title="'.$name[$id].'", `Order`="'.(int)$order[$id].'"',__FILE__,__LINE__);
								$javascript.='domD(gei(\'form'.$id.'\'));';
								
							}else{
								$error=true;
								$javascript.='stxAddErrorStatus(gei(\'name'.$id.'\'),\'You have not access to add system tables\');';
							}
							
						}else{
							$error=true;
							$javascript.='stxAddErrorStatus(gei(\'name'.$id.'\'),\'There is already has the table with this name\');';
						}
						
					}else{
						$error=true;
						$javascript.='stxAddErrorStatus(gei(\'name'.$id.'\'),\'There is already has the table with this name\');';
					}
					
				}else{
					$error=true;
					$javascript.='msgSetError(form,\'Not all required fields entered\');';			
				}
			}
			if(!$error)$javascript='redirect(\'index.php?module=tables\');';
			
		// одна запись
		}else{
			if($name){
				if(!dbGetValueFromDb('SELECT COUNT(Id) FROM mycms_tables WHERE Name="'.$name.'"',__FILE__,__LINE__)){
					// Существует ли таблица в БД
					if(in_array($name, $tables)) {
						if(mb_substr($name,0,6) != 'mycms_') {
							dbDoQuery('INSERT INTO mycms_tables SET Name="'.$name.'", `Order`="'.$order.'"',__FILE__,__LINE__);
							$javascript.='reload(true);';
							
						} else {
							$javascript.='stxAddErrorStatus(domP(form.name),\'You have not access to add system tables\');';
						}
						
					} else {
						// Таблица в БД не найдена
						$javascript.='stxAddErrorStatus(domP(form.name),\'Указанная таблица не найдена в базе данных\');';
					}
					
				}else{
					$javascript.='stxAddErrorStatus(domP(form.name),\'There is already has the table with this name\');';
				}
				
			}else{
				$javascript.='msgSetError(form,\'Not all required fields entered\');';			
			}
		}
		return  $javascript;
	}
	
	function Edit(){
		$ids=$_POST['id'];
		$element=LoadTemplate('table_edit');
		$content='';
		$i=0;
		$sqlCondition='';
		foreach($ids as $id=>$on){
			$sqlCondition.=($sqlCondition?' OR Id='.$id:'Id='.$id);
		}
		$res=dbDoQuery('SELECT * FROM mycms_tables WHERE '.$sqlCondition,__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$i++;
			$content.=strtr($element,array(
				'<%EVEN%>'	=> isEven($i)?' form-even':'',
				'<%NUM%>'	=> $rec['Id'],
				'<%NAME%>'	=> $rec['Name'],
				'<%ORDER%>'	=> $rec['Order']
			));
		}
		
		$result=LoadTemplate('base_editor');
		$result=strtr($result,array(
			'<%DOCUMENT_TITLE%>'	=> 'Edit table'.(count($ids)>1?'s':''),
			'<%BREAD_CRUMBS%>'		=> '<a href="index.php?module=tables">Tables</a> &rarr; ',
			'<%CONTENT%>'			=> $content,
			'<%ACTION_FORM%>'		=> 'index.php?module=tables&act=EditTable',
			'<%ACTION_CANCEL%>'		=> 'tableEditCancel();',
			'<%ACTION_SUBMIT%>'		=> 'tableEditSendForm(this);'
		));
		return $result;
	}
	
	function EditTable(){
		$name=is_array($_POST['name'])?$_POST['name']:trim($_POST['name']);
		$order=is_array($_POST['order'])?$_POST['order']:(int)$_POST['order'];
		$ids=$_POST['id'];
		$javascript='';
		$error=false;
		
		global $Config;
		$res=dbDoQuery('SHOW TABLES FROM `'.$Config['Db']['DbName'].'`',__FILE__,__LINE__);
		while($rec=dbGetRow($res)){
			$tables[]=$rec[0];		
		}
		
		// много записей
		if(is_array($ids)){
			foreach($ids as $id){
				if($name[$id]){
					if(!dbGetValueFromDb('SELECT COUNT(Id) FROM mycms_tables WHERE Id<>'.$id.' AND Name="'.$name[$id].'"',__FILE__,__LINE__)){
						if(in_array($name[$id],$tables)){
							if(mb_substr($name[$id],0,6)!='mycms_'){
								dbDoQuery('UPDATE mycms_tables SET Name="'.$name[$id].'", `Order`="'.(int)$order[$id].'" WHERE Id='.$id,__FILE__,__LINE__);
								$javascript.='domD(gei(\'form'.$id.'\'));';
								
							}else{
								$error=true;
								$javascript.='stxAddErrorStatus(gei(\'name'.$id.'\'),\'You have not access to add system tables\');';
							}
							
						}else{
							$error=true;
							$javascript.='stxAddErrorStatus(gei(\'name'.$id.'\'),\'There is already has the table with this name\');';
						}
						
					}else{
						$error=true;
						$javascript.='stxAddErrorStatus(gei(\'name'.$id.'\'),\'There is already has the table with this name\');';
					}
					
				}else{
					$error=true;
					$javascript.='msgSetError(form,\'Not all required fields entered\');';			
				}
			}
			if(!$error)$javascript='redirect(\'index.php?module=tables\');';
			
		// одна запись
		}else{
			$id=$ids;
			if($name){
				if(!dbGetValueFromDb('SELECT COUNT(Id) FROM mycms_tables WHERE Id<>'.$id.' AND Name="'.$name.'"',__FILE__,__LINE__)){
					if(in_array($name,$tables)){
						if(mb_substr($name,0,6)!='mycms_'){
							dbDoQuery('UPDATE mycms_tables SET Name="'.$name.'", `Order`="'.$order.'" WHERE Id='.$id,__FILE__,__LINE__);
							$javascript.='reload(true);';
							
						}else{
							$javascript.='stxAddErrorStatus(domP(form.name),\'You have not access to add system tables\');';
						}
						
					}else{
						$javascript.='stxAddErrorStatus(domP(form.name),\'There is already has the table with this name\');';
					}
					
				}else{
					$javascript.='stxAddErrorStatus(domP(form.name),\'There is already has the table with this name\');';
				}
				
			}else{
				$javascript.='msgSetError(form,\'Not all required fields entered\');';
			}
		}
		return  $javascript;
	}
	
	function DeleteTable(){
		$ids=is_array($_POST['id'])?$_POST['id']:Array((int)$_POST['id']=>'on');
		$javascript='';
		$sqlCondition='';
		foreach($ids as $id=>$on){
			$sqlCondition.=($sqlCondition?' OR Id='.$id:'Id='.$id);
		}
		dbDoQuery('DELETE FROM mycms_tables WHERE '.$sqlCondition,__FILE__,__LINE__);
		$javascript='reload(true);';
		return $javascript;
	}
	
	function CreateTable(){
		$name=htmlspecialchars(trim($_POST['name']));
		$order=(int)$_POST['order'];
		$fields=$_POST['field'];
		$type=$_POST['type'];
		$javascript='';
		
		$res=dbDoQuery('SELECT Id, `Value` FROM mycms_field_types',__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$types[$rec['Id']]=$rec['Value'];
		}
		
		if($name){
			$sqlDescription='`Id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY';
			foreach($fields as $k=>$field){
				$sqlDescription.=', `'.$field.'` '.$types[$type[$k]].' NOT NULL';	
			}
			dbDoQuery('CREATE TABLE `'.$name.'` ('.$sqlDescription.') ENGINE = MYISAM ;',__FILE__,__LINE__);
			dbDoQuery('INSERT INTO mycms_tables SET Name="'.$name.'", `Order`='.$order,__FILE__,__LINE__);
			$tableId=dbGetInsertedId();
			foreach($fields as $k=>$field){
				dbDoQuery('INSERT INTO mycms_fields SET Name="'.htmlspecialchars($field).'", TableId='.$tableId.', TypeId='.$type[$k],__FILE__,__LINE__);
			}
			$javascript.='reload(true);';
			
		}else{
			$javascript.='msgSetError(form,\'Not all required fields entered\');';
		}
		return $javascript;
	}
	
}

?>