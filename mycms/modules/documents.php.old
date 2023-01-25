<?php

class documents extends krn_abstract{
	
	function __construct(){
		parent::__construct();
		$this->mode=$_GET['mode']?$_GET['mode']:'Browse';
	}
	
	function GetResult(){
		$result=$this->{$this->mode}();
		return $result;
	}
	
	function Browse(){
		$table=new BrowserTable(array('Title','Table','Order','Elements count'));
		$query = 'SELECT t1.Id, t1.Title, t1.`Order`, t1.TableId, '
				.'(SELECT COUNT(t2.Id) FROM mycms_elements AS t2 WHERE t1.Id=t2.DocumentId) AS ElementsCount, '
				.'t3.Name AS `Table` '
				.'FROM mycms_documents AS t1 LEFT JOIN mycms_tables AS t3 ON t1.TableId=t3.Id '
				.'ORDER BY IF(t1.`Order`,-1000/t1.`Order`,0) ASC';
		$res=dbDoQuery($query,__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$row=array(
				'<a href="index.php?module=elements&document_id='.$rec['Id'].'">'.htmlspecialchars($rec['Title']).'</a>',
				'<a href="index.php?module=fields&table_id='.$rec['TableId'].'">'.$rec['Table'].'</a>',
				$rec['Order'],
				$rec['ElementsCount']
			);
			$actionEdit='Popup(\'document_edit\',600,{\'id\':'.$rec['Id'].'});';
			$actionDelete='docDeleteConfirm(this,'.$rec['Id'].');';
			$table->AddBodyRow($rec['Id'],$actionEdit,$actionDelete,$row);
		}
		
		$result=LoadTemplate('base_browser');
		$toolAdd=strtr(LoadTemplate('tool_add'),array(
			'<%ACTION%>'=>'docAdd(this);'
		));
		$result=strtr($result,array(
			'<%DOCUMENT_TITLE%>'		=> 'Documents',
			'<%BREAD_CRUMBS%>'			=> '',
			'<%TOOLS_RIGHT_TOP%>'		=> '',
			'<%FILTER%>'				=> '',
			'<%ACTION%>'				=> 'index.php#',
			'<%TABLE%>'					=> $table->GetTable(),
			'<%TOOLS_LEFT_BOTTOM%>'		=> GetButton('Edit',false,'docEdit(this);').' '.GetButton('Delete',false,'docDeleteConfirm(this);'),
			'<%TOOLS_RIGHT_BOTTOM%>'	=> $toolAdd
		));
		return $result;
	}
	
	function Add(){
		$count=(int)$_GET['count']?(int)$_GET['count']:1;
		$element=LoadTemplate('document_add');
		$content='';
		
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
			'<%DOCUMENT_TITLE%>'	=> 'Create new document'.($count>1?'s':''),
			'<%BREAD_CRUMBS%>'		=> '<a href="index.php?module=documents">Documents</a> &rarr; ',
			'<%CONTENT%>'			=> $content,
			'<%ACTION_FORM%>'		=> 'index.php?module=documents&act=AddDocument',
			'<%ACTION_CANCEL%>'		=> 'docAddCancel();',
			'<%ACTION_SUBMIT%>'		=> 'docAddSendForm(this);'
		));
		return $result;
	}
	
	function AddDocument(){
		$title=is_array($_POST['title'])?$_POST['title']:trim($_POST['title']);
		$table=is_array($_POST['table'])?$_POST['table']:(int)$_POST['table'];
		$actionCode=is_array($_POST['action_code'])?$_POST['action_code']:trim($_POST['action_code']);
		$lead_element=is_array($_POST['lead_element'])?$_POST['lead_element']:trim($_POST['lead_element']);
		$order_element=is_array($_POST['order_element'])?$_POST['order_element']:trim($_POST['order_element']);
		$order_direction=is_array($_POST['order_direction'])?$_POST['order_direction']:trim($_POST['order_direction']);
		$parent_id=is_array($_POST['parent_id'])?$_POST['parent_id']:(int)$_POST['parent_id'];
		$parent_field=is_array($_POST['parent_field'])?$_POST['parent_field']:trim($_POST['parent_field']);
		$order=is_array($_POST['order'])?$_POST['order']:(int)$_POST['order'];
		$ids=$_POST['id'];
		$javascript='';
		$error=false;
		
		// много записей
		if(is_array($ids)){
			foreach($ids as $id){
				if($title[$id]){
					if(!dbGetValueFromDb('SELECT COUNT(Id) FROM mycms_documents WHERE Title="'.$title[$id].'"',__FILE__,__LINE__)){
						dbDoQuery('INSERT INTO mycms_documents SET Title="'.$title[$id].'", LeadElement="'.$lead_element[$i].'", OrderElement="'.$order_element[$i].'", OrderDirection="'.$order_direction[$i].'", TableId='.$table[$id].', ParentId='.(int)$parent_id[$id].', ParentField="'.$parent_field[$id].'", `ActionCode`="'.$actionCode[$id].'", `Order`='.(int)$order[$id],__FILE__,__LINE__);
						$javascript.='domD(gei(\'form'.$id.'\'));';
						
					}else{
						$error=true;
						$javascript.='stxAddErrorStatus(gei(\'title'.$id.'\'),\'Уже есть документ с таким названием\');';
					}
					
				}else{
					$error=true;
					$javascript.='msgSetError(form,\'Заполнены не все необходимые поля\');';			
				}
			}
			if(!$error)$javascript='redirect(\'index.php?module=documents\');';
			
		// одна запись
		}else{
			if($title){
				if(!dbGetValueFromDb('SELECT COUNT(Id) FROM mycms_documents WHERE Title="'.$title.'"',__FILE__,__LINE__)){
					dbDoQuery('INSERT INTO mycms_documents SET Title="'.$title.'", LeadElement="'.$lead_element.'", OrderElement="'.$order_element.'", OrderDirection="'.$order_direction.'", TableId='.$table.', ParentId='.$parent_id.', ParentField="'.$parent_field.'", `ActionCode`="'.$actionCode.'", `Order`='.$order,__FILE__,__LINE__);
					$javascript.='reload(true);';
					
				}else{
					$javascript.='stxAddErrorStatus(domP(form.title),\'Уже есть документ с таким названием\');';
				}
				
			}else{
				$javascript.='msgSetError(form,\'Заполнены не все необходимые поля\');';			
			}
		}
		return  $javascript;
	}
	
	function Edit(){
		$ids=$_POST['id'];
		$element=LoadTemplate('document_edit');
		$content='';	
		
		$res=dbDoQuery('SELECT Id, Name FROM mycms_tables ORDER BY IF(`Order`,-1000/`Order`,0) ASC',__FILE__,__LINE__);
		while($tables[]=dbGetRecord($res));
		
		$i=0;
		$sqlCondition='';
		foreach($ids as $id=>$on){
			$sqlCondition.=($sqlCondition?' OR Id='.$id:'Id='.$id);
		}
		$res=dbDoQuery('SELECT * FROM mycms_documents WHERE '.$sqlCondition,__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$i++;
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
			$content.=strtr($element,array(
				'<%EVEN%>'			=> isEven($i)?' form-even':'',
				'<%NUM%>'			=> $rec['Id'],
				'<%TITLE%>'			=> $rec['Title'],
				'<%ACTIONCODE%>'	=> $rec['ActionCode'],
				'<%ORDER%>'			=> $rec['Order'],
				'<%DEFAULT_TITLE%>'	=> $defaultTitle,
				'<%DEFAULT_VALUE%>'	=> $defaultValue,
				'<%OPTIONS_TYPE%>'	=> $optionsType
			));
		}
		
		$result=LoadTemplate('base_editor');
		$result=strtr($result,array(
			'<%DOCUMENT_TITLE%>'	=> 'Edit document'.(count($ids)>1?'s':''),
			'<%BREAD_CRUMBS%>'		=> '<a href="index.php?module=documents">Documents</a> &rarr; ',
			'<%CONTENT%>'			=> $content,
			'<%ACTION_FORM%>'		=> 'index.php?module=documents&act=EditDocument',
			'<%ACTION_CANCEL%>'		=> 'docEditCancel();',
			'<%ACTION_SUBMIT%>'		=> 'docEditSendForm(this);'
		));
		return $result;
	}
	
	function EditDocument(){
		$title=is_array($_POST['title'])?$_POST['title']:trim($_POST['title']);
		$table=is_array($_POST['table'])?$_POST['table']:(int)$_POST['table'];
		$parent_id=is_array($_POST['parent_id'])?$_POST['parent_id']:(int)$_POST['parent_id'];
		$parent_field=is_array($_POST['parent_field'])?$_POST['parent_field']:trim($_POST['parent_field']);
		$lead_element=is_array($_POST['lead_element'])?$_POST['lead_element']:trim($_POST['lead_element']);
		$order_element=is_array($_POST['order_element'])?$_POST['order_element']:trim($_POST['order_element']);
		$order_direction=is_array($_POST['order_direction'])?$_POST['order_direction']:trim($_POST['order_direction']);
		$actionCode=is_array($_POST['action_code'])?$_POST['action_code']:trim($_POST['action_code']);
		$order=is_array($_POST['order'])?$_POST['order']:(int)$_POST['order'];
		$ids=$_POST['id'];
		$javascript='';
		$error=false;
		
		// много записей
		if(is_array($ids)){
			foreach($ids as $id){
				if($title[$id]){
					if(!dbGetValueFromDb('SELECT COUNT(Id) FROM mycms_documents WHERE Id<>'.$id.' AND Title="'.$title[$id].'"',__FILE__,__LINE__)){
						dbDoQuery('UPDATE mycms_documents SET Title="'.$title[$id].'", LeadElement="'.$lead_element[$id].'", OrderElement="'.$order_element[$id].'", OrderDirection="'.$order_direction[$id].'", TableId='.(int)$table[$id].', ParentId='.(int)$parent_id[$id].', ParentField="'.$parent_field[$id].'", `ActionCode`="'.$actionCode[$id].'", `Order`='.(int)$order[$id].' WHERE Id='.$id,__FILE__,__LINE__);
						$javascript.='domD(gei(\'form'.$id.'\'));';
						
					}else{
						$error=true;
						$javascript.='stxAddErrorStatus(gei(\'title'.$id.'\'),\'Уже есть документ с таким названием\');';
					}
					
				}else{
					$error=true;
					$javascript.='msgSetError(form,\'Заполнены не все необходимые поля\');';			
				}
			}
			if(!$error)$javascript='redirect(\'index.php?module=documents\');';
			
		// одна запись
		}else{
			$id=$ids;
			if($title){
				if(!dbGetValueFromDb('SELECT COUNT(Id) FROM mycms_documents WHERE Id<>'.$id.' AND Title="'.$title.'"',__FILE__,__LINE__)){
					dbDoQuery('UPDATE mycms_documents SET Title="'.$title.'", LeadElement="'.$lead_element.'", OrderElement="'.$order_element.'", OrderDirection="'.$order_direction.'", TableId='.$table.', ParentId='.$parent_id.', ParentField="'.$parent_field.'", `ActionCode`="'.$actionCode.'", `Order`='.$order.' WHERE Id='.$id,__FILE__,__LINE__);
					$javascript.='reload(true);';
					
				}else{
					$javascript.='stxAddErrorStatus(domP(form.title),\'Уже есть документ с таким названием\');';
				}
				
			}else{
				$javascript.='msgSetError(form,\'Заполнены не все необходимые поля\');';			
			}
		}
		return  $javascript;
	}
	
	function DeleteDocument(){
		$ids=is_array($_POST['id'])?$_POST['id']:Array((int)$_POST['id']=>'on');
		$javascript='';
		$sqlCondition='';
		foreach($ids as $id=>$on){
			$sqlCondition.=($sqlCondition?' OR Id='.$id:'Id='.$id);
		}
		dbDoQuery('DELETE FROM mycms_documents WHERE '.$sqlCondition,__FILE__,__LINE__);
		$javascript='reload(true);';
		return $javascript;
	}
	
}

?>