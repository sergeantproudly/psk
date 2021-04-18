<?php

	class notifications extends krn_abstract{
		
		private $notifications;
		
		function __construct(){
			parent::__construct();
		}
		
		function GetResult(){
			return $this->Browse();
		}
		
		function GetList($new_only=true,$limit_from=false,$limit_count=false){
			$this->notifications=new RecordList($limit_from!==false?
				dbDoQueryLimit('SELECT Id, DateTime, Text, Viewed FROM admin_notifications WHERE UserId='.$_SESSION['User']['Id'].($new_only?' AND Viewed=0':'').' ORDER BY DateTime DESC',$limit_from,$limit_count,__FILE__,__LINE__):
				dbDoQuery('SELECT Id, DateTime, Text, Viewed FROM admin_notifications WHERE UserId='.$_SESSION['User']['Id'].($new_only?' AND Viewed=0':'').' ORDER BY DateTime DESC',__FILE__,__LINE__));
			return $this->notifications;
		}
		
		function GetTotal($new_only=true){
			return dbGetValueFromDb('SELECT COUNT(Id) FROM admin_notifications WHERE UserId='.$_SESSION['User']['Id'].($new_only?' AND Viewed=0':'').' ORDER BY DateTime DESC',__FILE__,__LINE__);
		}
		
		function InitList(){
			$this->GetList();
		}
		
		function GetLastNotification(){
			if(!$this->notifications)$this->GetList();
			return $this->notifications->GetFirst();
		}
		
		function GetNotificationsHtml(){		
			$this->InitList();
			$notifications_arr=$this->notifications->ToArray();
			
			$last_notification=$this->GetLastNotification();
			
			$result=LoadTemplate('notifications');
			$result=strtr($result,array(
				'<%LASTNOTIFICATION%>'	=> $last_notification?$last_notification['Text']:'Нет новых уведомлений',
				//'<%MORE%>'				=> count($notifications_arr)>1?' &nbsp; <span class="dashed" onclick="Popup(\'notifications\',850);">'.(count($notifications_arr)-1).' more</span>':''
				'<%MORE%>'				=> ($_GET['module']=='notifications' || count($notifications_arr)<1)?'':' &nbsp; <a href="index.php?module=notifications">показать все</a>'
			));
			return $result;
		}
		
		function SetViewed($notification_ids=array()){
			if(count($notification_ids)){
				dbDoQuery('UPDATE admin_notifications SET Viewed=1 WHERE Id IN ('.implode(',',$notification_ids).')',__FILE__,__LINE__);
				return true;
			}
			return false;
		}
		
		function Browse(){
			$table=new BrowserTable(array('Уведомление','Дата','Прочитано'));
			
			$page=(int)$_GET['page']?(int)$_GET['page']:1;
			$rowsOnPage=$_SESSION['Cms']['RowsOnPage']?$_SESSION['Cms']['RowsOnPage']:$_SESSION['Cms']['RowsOnPage']=$Settings->GetCmsSetting(3,15);
			$pagesInGroup=$_SESSION['Cms']['PagesInGroup']?$_SESSION['Cms']['PagesInGroup']:$_SESSION['Cms']['PagesInGroup']=$Settings->GetCmsSetting(4,15);
			
			$countTotal=$this->GetTotal(false);
			$navMn=GetNavigationMn($countTotal,$rowsOnPage,$pagesInGroup);
			
			$this->GetList(false,($page-1)*$rowsOnPage,$rowsOnPage);
			$notifications_arr=$this->notifications->ToArray();
			$ids=array();
			foreach($notifications_arr as $notification){
				$row=array(
					$notification['Viewed']?$notification['Text']:'<strong style="font-weight: 600;">'.$notification['Text'].'</strong>',
					$notification['Viewed']?date('m/d/Y H:i',strtotime($notification['DateTime'])):'<strong style="font-weight: 600;">'.date('m/d/Y H:i',strtotime($notification['DateTime'])).'</strong>',
					$notification['Viewed']?'да':'<strong style="font-weight: 600;">нет</strong>'
				);
				$actionDelete='ntfDeleteConfirm(this,'.$notification['Id'].');';
				$table->AddBodyRow($notification['Id'],'',$actionDelete,$row);
				if(!$notification['Viewed'])$ids[]=$notification['Id'];
			}
			
			if(count($ids))$this->SetViewed($ids);
			
			$toolRop=strtr(LoadTemplate('tool_rop'),array(
				'<%ROWS_ON_PAGE%>'	=> $rowsOnPage
			));
			
			$result=LoadTemplate('base_browser');
			$result=strtr($result,array(
				'<%DOCUMENT_TITLE%>'		=> 'Уведомления',
				'<%BREAD_CRUMBS%>'			=> '<a href="index.php?module=notifications">Уведомления</a> &rarr; ',
				'<%TOOLS_RIGHT_TOP%>'		=> $toolRop,
				'<%FILTER%>'				=> '',
				'<%ACTION%>'				=> 'index.php#',
				'<%TABLE%>'					=> $table->GetTable(),
				'<%TOOLS_LEFT_BOTTOM%>'		=> GetButton('Удалить',false,'ntfDeleteConfirm(this);'),
				'<%TOOLS_RIGHT_BOTTOM%>'	=> ''
			));
			return $result.$navMn;
		}
		
		function DeleteNotification(){
			$ids=is_array($_POST['id'])?$_POST['id']:Array((int)$_POST['id']=>'on');
			$javascript='';
			$sqlCondition='';
			foreach($ids as $id=>$on){
				$sqlCondition.=($sqlCondition?' OR Id='.$id:'Id='.$id);
			}
			dbDoQuery('DELETE FROM admin_notifications WHERE '.$sqlCondition,__FILE__,__LINE__);
			$javascript='reload(true);';
			return $javascript;
		}
		
	}

	class user_notifications{
		
		public function __construct($params,$user_id){
			return $this->CreateNotification($params,$user_id);
		}
		
		public static function CreateNotification($params,$user_id){			
			$query = 'INSERT INTO user_notifications SET '
					.'DateTime=NOW(), '
					.'UserId='.$user_id.', '
					.'Text="'.$params['Text'].'", '
					.($params['Link']?'Link="'.$params['Link'].'", ':'')
					.'Viewed=0';
			dbDoQuery($query,__FILE__,__LINE__);
		}
		
	}

?>