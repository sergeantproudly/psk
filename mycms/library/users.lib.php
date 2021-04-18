<?php
    
    krnLoadLib('define');
    
    function GetUserStatus($userMask=false){
    	if(!$userMask)$userMask=$_SESSION['User']['Status'];
		$userStatuses=GetUserStatuses();
		return $userStatuses[$userMask];
	}
	
	function CheckPermission($folder,$userMask=false){
		if(!$userMask)$userMask=$_SESSION['User']['Status'];
		if(!is_array($folder)){
			$moduleName=$folder;
			$folders=GetFolders();
			foreach($folders as $item){
				if($item['Module']==$moduleName){
					$folder=$item;
					break;
				}
			}
		}	
		return $folder['PermissionMask']&$userMask;
	}
	
	function GetStartFolder($userMask=false){
		if(!$userMask)$userMask=$_SESSION['User']['Status'];
		$folders=GetFolders();
		foreach($folders as $item){
			if($userMask&$item['PermissionMask'])return $item; 
		}
		return false;
	}
	
	function GetStartFolderModule($userMask=false){
		$folder=GetStartFolder($userMask);
		return $folder['Module'];
	}
	
	function GetActionsPermission($userMask=false){
		if(!$userMask)$userMask=$_SESSION['User']['Status'];
		$perm=0;
		if($userMask==PERMISSION_MASK_DEVELOPER)$perm=PAMASK_ALLDEV;
		elseif($userMask==PERMISSION_MASK_ADMINISTRATOR)$perm=PAMASK_ALL;
		elseif($userMask==PERMISSION_MASK_MODERATOR)$perm=PAMASK_ALL^PAMASK_DELETE;
		return $perm;
	}
	
	function CheckPA($actionMask,$userActionMask=false){
		if(!$userActionMask)$userActionMask=$_SESSION['User']['ActionStatus'];
		return $userActionMask&$actionMask;
	}

?>