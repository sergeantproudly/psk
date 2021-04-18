<?php

krnLoadLib('users');
krnLoadLib('define');

class users extends krn_abstract{
	
	function __construct(){
		$this->mode=$_GET['mode']?$_GET['mode']:'Browse';
	}
	
	function GetResult(){
		$result=$this->{$this->mode}();
		return $result;
	}
	
	function Browse(){
		$userMask=$_SESSION['User']['Status'];
		$sqlCondition='';
		if($userMask==PERMISSION_MASK_ADMINISTRATOR){
			$sqlCondition.=' WHERE `Status`<>'.PERMISSION_MASK_DEVELOPER;
		}elseif($userMask==PERMISSION_MASK_MODERATOR){
			$sqlCondition.=' WHERE Id='.$_SESSION['User']['Id'];
		}
		
		$table=new BrowserTable(array('Username','Login','Status'));
		$query = 'SELECT * FROM mycms_users'.$sqlCondition.' ORDER BY `Status`';
		$res=dbDoQuery($query,__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$row=array(
				htmlspecialchars($rec['Name']),
				$rec['Login'],
				GetUserStatus($rec['Status'])
			);
			if(CheckPA(PAMASK_EDIT))$actionEdit='Popup(\'user_edit\',600,{\'id\':'.$rec['Id'].'});';
			else $actionEdit=false;
			if(CheckPA(PAMASK_DELETE))$actionDelete='userDeleteConfirm(this,'.$rec['Id'].');';
			else $actionDelete=false;
			$table->AddBodyRow($rec['Id'],$actionEdit,$actionDelete,$row);
		}
		
		$result=LoadTemplate('base_browser');
		$toolAdd=strtr(LoadTemplate('tool_add'),array(
			'<%ACTION%>'=>'userAdd(this);'
		));
		$result=strtr($result,array(
			'<%DOCUMENT_TITLE%>'		=> 'Users',
			'<%BREAD_CRUMBS%>'			=> '',
			'<%TOOLS_RIGHT_TOP%>'		=> '',
			'<%ACTION%>'				=> 'index.php#',
			'<%FILTER%>'				=> '',
			'<%TABLE%>'					=> $table->GetTable(),
			'<%TOOLS_LEFT_BOTTOM%>'		=> (CheckPA(PAMASK_EDIT)?GetButton('Edit',false,'userEdit(this);'):'').' '.(CheckPA(PAMASK_DELETE)?GetButton('Delete',false,'userDeleteConfirm(this);'):''),
			'<%TOOLS_RIGHT_BOTTOM%>'	=> CheckPA(PAMASK_CREATE)&&$userMask!=PERMISSION_MASK_MODERATOR?$toolAdd:''
		));
		return $result;
	}
	
	function Add(){
		$count=(int)$_GET['count']?(int)$_GET['count']:1;
		$element=LoadTemplate('user_add');
		$content='';
		
		$defaultTitle='';
		$defaultValue='';
		$optionsType='';
		$userStatuses=GetUserStatuses();
		foreach($userStatuses as $code=>$status){
			if(!$defaultTitle){
				$defaultTitle=$status;
				$defaultValue=$code;
				$optionsType.='<span class="item current" value="'.$code.'">'.$status.'</span>';
			}else{
				$optionsType.='<span class="item" value="'.$code.'">'.$status.'</span>';
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
			'<%DOCUMENT_TITLE%>'	=> 'Create new account'.($count>1?'s':''),
			'<%BREAD_CRUMBS%>'		=> '<a href="index.php?module=users">Users</a> &rarr; ',
			'<%CONTENT%>'			=> $content,
			'<%ACTION_FORM%>'		=> 'index.php?module=users&act=AddUser',
			'<%ACTION_CANCEL%>'		=> 'userAddCancel();',
			'<%ACTION_SUBMIT%>'		=> 'userAddSendForm(this);'
		));
		return $result;
	}
	
	function AddUser(){
		$name=is_array($_POST['name'])?$_POST['name']:trim($_POST['name']);
		$login=is_array($_POST['login'])?$_POST['login']:trim($_POST['login']);
		$password=is_array($_POST['password'])?$_POST['password']:trim($_POST['password']);
		$status=is_array($_POST['status'])?$_POST['status']:(int)$_POST['status'];
		$ids=$_POST['id'];
		$javascript='';
		$error=false;
		
		$userMask=$_SESSION['User']['Status'];
		
		// много записей
		if(is_array($ids)){
			foreach($ids as $id){
				if($name[$id]&&$login[$id]&&$password[$id]){
					if(!dbGetValueFromDb('SELECT COUNT(Id) FROM mycms_users WHERE Login="'.trim($login[$id]).'"',__FILE__,__LINE__)){
						dbDoQuery('INSERT INTO mycms_users SET `Name`="'.trim($name[$id]).'", `Login`="'.trim($login[$id]).'", `Password`="'.md5(trim($password[$id])).'"'.($userMask==PERMISSION_MASK_ADMINISTRATOR||$userMask==PERMISSION_MASK_DEVELOPER?', `Status`='.(int)$status[$id]:''),__FILE__,__LINE__);
						$javascript.='domD(gei(\'form'.$id.'\'));';
						
					}else{
						$error=true;
						$javascript.='stxAddErrorStatus(gei(\'name'.$id.'\'),\'There is already an account with this login\');';
					}
					
				}else{
					$error=true;
					$javascript.='msgSetError(form,\'Not all required fields\');';			
				}
			}
			if(!$error)$javascript='redirect(\'index.php?module=users\');';
			
		// одна запись
		}else{
			if($name&&$login&&$password){
				if(!dbGetValueFromDb('SELECT COUNT(Id) FROM mycms_users WHERE Name="'.$name.'"',__FILE__,__LINE__)){
					dbDoQuery('INSERT INTO mycms_users SET `Name`="'.$name.'", `Login`="'.$login.'", `Password`="'.md5($password).'"'.($userMask==PERMISSION_MASK_ADMINISTRATOR||$userMask==PERMISSION_MASK_DEVELOPER?', `Status`='.$status:''),__FILE__,__LINE__);
					$javascript.='reload(true);';
					
				}else{
					$javascript.='stxAddErrorStatus(domP(form.name),\'There is already an account with this login\');';
				}
				
			}else{
				$javascript.='msgSetError(form,\'Not all required fields\');';			
			}
		}
		return  $javascript;
	}
	
	function Edit(){
		$ids=$_POST['id']?$_POST['id']:Array($_GET['user_id']=>'on');
		$element=LoadTemplate('user_edit');
		$content='';	
		
		$userStatuses=GetUserStatuses();
		
		$i=0;
		$sqlCondition='';
		foreach($ids as $id=>$on){
			$sqlCondition.=($sqlCondition?' OR Id='.$id:'Id='.$id);
		}
		$res=dbDoQuery('SELECT * FROM mycms_users WHERE '.$sqlCondition,__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$i++;
			$defaultTitle='';
			$defaultValue='';
			$optionsType='';
			foreach($userStatuses as $code=>$status){
				if($rec['Status']==$code){
					$defaultTitle=$status;
					$defaultValue=$code;
					$optionsType.='<span class="item current" value="'.$code.'">'.$status.'</span>';
				}elseif($code){
					$optionsType.='<span class="item" value="'.$code.'">'.$status.'</span>';
				}
			}
			$content.=strtr($element,array(
				'<%EVEN%>'			=> isEven($i)?' form-even':'',
				'<%NUM%>'			=> $rec['Id'],
				'<%NAME%>'			=> $rec['Name'],
				'<%LOGIN%>'			=> $rec['Login'],
				'<%PASSWORD%>'		=> '',
				'<%DEFAULT_TITLE%>'	=> $defaultTitle,
				'<%DEFAULT_VALUE%>'	=> $defaultValue,
				'<%OPTIONS_TYPE%>'	=> $optionsType
			));
		}
		
		$result=LoadTemplate('base_editor');
		$result=strtr($result,array(
			'<%DOCUMENT_TITLE%>'	=> 'Edit account'.(count($ids)>1?'s':''),
			'<%BREAD_CRUMBS%>'		=> '<a href="index.php?module=users">Users</a> &rarr; ',
			'<%CONTENT%>'			=> $content,
			'<%ACTION_FORM%>'		=> 'index.php?module=users&act=EditUser',
			'<%ACTION_CANCEL%>'		=> 'userEditCancel();',
			'<%ACTION_SUBMIT%>'		=> 'userEditSendForm(this);'
		));
		return $result;
	}
	
	function EditUser(){
		$name=is_array($_POST['name'])?$_POST['name']:trim($_POST['name']);
		$login=is_array($_POST['login'])?$_POST['login']:trim($_POST['login']);
		$password=is_array($_POST['password'])?$_POST['password']:trim($_POST['password']);
		$status=is_array($_POST['status'])?$_POST['status']:(int)$_POST['status'];
		$ids=$_POST['id'];
		$javascript='';
		$error=false;
		
		$userMask=$_SESSION['User']['Status'];
		
		// много записей
		if(is_array($ids)){
			foreach($ids as $id){
				if($name[$id]&&$login[$id]){
					if(!dbGetValueFromDb('SELECT COUNT(Id) FROM mycms_users WHERE Id<>'.$id.' AND `Login`="'.trim($login[$id]).'"',__FILE__,__LINE__)){
						dbDoQuery('UPDATE mycms_users SET `Name`="'.trim($name[$id]).'", `Login`="'.trim($login[$id]).'"'.($password[$id]?', `Password`="'.md5(trim($password[$id])).'", ':'').($userMask==PERMISSION_MASK_ADMINISTRATOR||$userMask==PERMISSION_MASK_DEVELOPER?', `Status`='.(int)$status[$id]:'').' WHERE Id='.$id,__FILE__,__LINE__);
						$javascript.='domD(gei(\'form'.$id.'\'));';
						
					}else{
						$error=true;
						$javascript.='stxAddErrorStatus(gei(\'name'.$id.'\'),\'There is already an account with this login\');';
					}
					
				}else{
					$error=true;
					$javascript.='msgSetError(form,\'Not all required fields\');';			
				}
			}
			if(!$error)$javascript='redirect(\'index.php?module=users\');';
			
		// одна запись
		}else{
			$id=$ids;
			if($name&&$login){
				if(!dbGetValueFromDb('SELECT COUNT(Id) FROM mycms_users WHERE Id<>'.$id.' AND `Login`="'.trim($login).'"',__FILE__,__LINE__)){
					dbDoQuery('UPDATE mycms_users SET `Name`="'.$name.'", `Login`="'.$login.'"'.($password?', `Password`="'.md5($password).'"':'').($userMask==PERMISSION_MASK_ADMINISTRATOR||$userMask==PERMISSION_MASK_DEVELOPER?', `Status`='.$status:'').' WHERE Id='.$id,__FILE__,__LINE__);
					$javascript.='reload(true);';
					
				}else{
					$javascript.='stxAddErrorStatus(domP(form.name),\'There is already an account with this login\');';
				}
				
			}else{
				$javascript.='msgSetError(form,\'Not all required fields\');';			
			}
		}
		return  $javascript;
	}
	
	function DeleteUser(){
		$userMask=$_SESSION['User']['Status'];
		if($userMask!=PERMISSION_MASK_ADMINISTRATOR&&$userMask!=PERMISSION_MASK_DEVELOPER)return false;
		
		$ids=is_array($_POST['id'])?$_POST['id']:Array((int)$_POST['id']=>'on');
		$javascript='';
		$sqlCondition='';
		foreach($ids as $id=>$on){
			$sqlCondition.=($sqlCondition?' OR Id='.$id:'Id='.$id);
		}
		dbDoQuery('DELETE FROM mycms_users WHERE '.$sqlCondition,__FILE__,__LINE__);
		$javascript='reload(true);';
		return $javascript;
	}
	
}

?>