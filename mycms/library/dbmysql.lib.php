<?php

	// ошибка при запросе к базе данных
	function dbError($file,$line,$message=''){
		global $Params;		
		if(mysqli_errno($Params['Db']['Link'])){
			$result = '';
			$result .= "Database error in <b>$file</b> at line <b>$line</b><br/>";
			$result .= "MySQL Server message: ".mysqli_error($Params['Db']['Link']).'<br/>';
			$result .= "System message: ".$message.'<br/>';
			die($result);
		}
	}
	
	function dbEscape($string){
		global $Params;	
		return mysqli_real_escape_string($Params['Db']['Link'],$string);
	}

	// запрос к базе данных
    function dbDoQuery($query,$file='',$line=''){
        global $ConfigDB;
        global $Params;        
        global $link_db;		        
        if(!($res = @mysqli_query($Params['Db']['Link'],$query)))
        	dbError($file,$line,$query);
        return $res;
    }
    
    // запрос на определенное количество записей к базе данных
	function dbDoQueryLimit($query,$limitStart,$limitCount=false,$file='',$line=''){
		if(!$limitStart && !$limitCount)
			return dbDoQuery($query,$file,$line);
		return dbDoquery($query." LIMIT $limitStart".($limitCount?",$limitCount":''),$file,$line);
	}
    
    // количество строк в выборке
    function dbGetNumRows($queryResult){
		return @mysqli_num_rows($queryResult);
	}
	
	// последний добавленный id
	function dbGetInsertedId(){
		global $Params;		
		return @mysqli_insert_id($Params['Db']['Link']);
	}
	
	// запись из бд
    function dbGetRecord($queryResult){
		if($queryResult==null) return null;
		return @mysqli_fetch_array($queryResult,MYSQLI_ASSOC);
	}
	
	// запись из бд (ассоц массив)
    function dbGetRow($queryResult){
		if($queryResult==null) return null;
		return @mysqli_fetch_row($queryResult);
	}
	
	// первая запись выборки
    function dbGetRowFromDb($query,$file='',$line=''){
		$res=dbDoQueryLimit($query,0,1,$file,$line);
		if(dbGetNumRows($res)){
			$row=dbGetRow($res);
			dbFreeResult($res);
			return $row;
		}
		dbFreeResult($res);
		return false;
	}
	
	// первая запись выборки (ассоц массив)
	function dbGetRecordFromDb($query,$file='',$line=''){
		$res = dbDoQueryLimit($query,0,1,$file,$line);
		if(dbGetNumRows($res)){
			$rec = dbGetRecord($res);
			dbFreeResult($res);
			return $rec;
		}
		dbFreeResult($res);
		return false;
	}
	
	// первое поле первой записи выборки
	function dbGetValueFromDb($query,$file='',$line=''){
		if($rec = dbGetRowFromDb($query,$file,$line))
			return $rec[0];
		return false;
	}
	
	// освобождение памяти
	function dbFreeResult($result){
		mysqli_free_result($result);
	}
	
	function dbEscapeString($string,$mode=false){
		global $Params;		
		if($mode=='both')return mysqli_escape_string($Params['Db']['Link'],$string);
		else return str_replace('"','\"',$string);
	}

?>