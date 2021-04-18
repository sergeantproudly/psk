<?php

/**
	* Загружает файл шаблона.
	* Использует встроенные функции чтения файлов. В случае, если файл с переданным именем не найден, то прекратится выполнение программы. 
	* @param string $filename
	* @return void
	*/
function LoadFile($filename) {
	// [TODO]: Заменить функцию чтения файла на file_get_contents. Данная функция гораздо производительней, чем потоковые функции работы с файлами
	if (file_exists($filename)) {
		$fp = fopen($filename, 'r');
		$content = fread($fp, filesize($filename));
		fclose($fp);
		return $content;
	} else 
	return die('Can\'t load file - '.$filename);
}

/**
 * Проверяет, существует ли шаблон с указанным именем в указанной директории, и загружает его.
 * Проверка осуществляется в общем каталоге шаблонов (по умолчанию, templates/). Функция возвращает содержимое файла шаблона
 * @param string $name
 * @param string $dir
 * @return string
 */
function LoadTemplate($name, $dir = ''){
	// [TODO]: Переписать функцию загрузки шаблона с использованием glob()
	$filepath = '';

	if ($dir) 
		$dir .= '/';
	
	if (file_exists(TEMPLATES_DIR.$dir.$name)) {
		$filepath = TEMPLATES_DIR.$dir.$name;
	} elseif (file_exists(TEMPLATES_DIR.$dir.$name.'.html')) {
		$filepath = TEMPLATES_DIR.$dir.$name.'.html';
	} elseif (file_exists(TEMPLATES_DIR.$dir.$name.'.htm')) {
		$filepath = TEMPLATES_DIR.$dir.$name.'.htm';
	} else {
		return false;
	}
			
	return LoadFile($filepath);
}
    
/**
 * Заменяет плейсхолдеры реальными данными.
 * Функция парсит шаблон в поисках плейсхолдеров с именем, соответствующим имени элемента в массиве и, при обнаружении, выполняет замену на данные 
 *
 * @param string $template
 * @param array $array
 * @return void
 */
function SetAtribs($template, $array){
	if(count($array)){
		foreach($array as $k=>$v)
			$template=str_replace('<%'.strtoupper($k).'%>',$v,$template);
	}
	return $template;
}
	
// content attribute substitution
function SetContent($template,$content){
	return strtr($template,array(
		'<%CONTENT%>'	=> $content
	));
}
    
    // pagination
function GetNavigationMn($count, $pSize, $pgSize, $curPage=false,$url=false,$getVar='page') {
		krnLoadLib('url');
    	if(!$url)$url=$_SERVER['REQUEST_URI'];
    	if(!$curPage)$curPage=(int)$_GET[$getVar]?(int)$_GET[$getVar]:1;
		$result='';
		$pageCount=ceil($count/$pSize);
		$lotOf=$pageCount>$pgSize*3;
		if($pageCount>1){
			$pageGroup=ceil($curPage/$pgSize);
			$url=UrlDelVars($url,array($getVar=>''));
			
			if($curPage>1 && !$lotOf){
				$result.='<a href="'.urlAddVars($url,array($getVar=>$curPage-1)).'" class="extend">назад</a> ';
			}
			
			if($pageGroup>1 && $lotOf){
				$rangeMax=$curPage-floor(($pgSize-1)/2)-1;
				$rangeMin=$rangeMax-$pgSize+1<1?1:$rangeMax-$pgSize+1;
				$result.='<a href="'.urlAddVars($url,array($getVar=>$rangeMin)).'" class="group-l">'.($rangeMin).($rangeMin!=$rangeMax?('&#150;'.$rangeMax):'').'</a>';
			}
			
			for($i=$curPage-floor(($pgSize-1)/2);$i<=$curPage+ceil(($pgSize-1)/2);$i++){
				if($i>0 && $i<=$pageCount){
					$result.=($i==$curPage?'<span class="curr">'.$i.'</span> ':'<a href="'.urlAddVars($url,array($getVar=>$i)).'">'.$i.'</a> ');
				}			
			}
			
			if($pageGroup<floor($pageCount/$pgSize) && $lotOf){
				$rangeMin=$curPage+ceil(($pgSize-1)/2)+1;
				$rangeMax=$rangeMin+$pgSize>$pageCount?$pageCount:$rangeMin+$pgSize;
				$result.='<a href="'.urlAddVars($url,array($getVar=>$rangeMin)).'" class="group-r">'.($rangeMin).($rangeMax!=$rangeMin?('&#150;'.$rangeMax):'').'</a>';
			}
			
			if($curPage<$pageCount && !$lotOf){
				$result.='<a href="'.urlAddVars($url,array($getVar=>$curPage+1)).'" class="extend">вперед</a>';
			}
		}

		return '<div class="mn-nav">'.$result.'</div>';
	}
	
	// date time mod
	function ModifiedDateTime($date,$type='date'){
		if(preg_match('/(\d{4}).(\d{1,2}).(\d{1,2}) (\d{1,2}).(\d{1,2}).(\d{1,2})/i',$date,$regs)){
			if($type=='date'||!$type){
				$result=$regs[3].'.'.$regs[2].'.'.$regs[1];			
			}elseif($type=='datetime'){
				$result=$regs[3].'.'.$regs[2].'.'.$regs[1].' в '.$regs[4].':'.$regs[5];
			}elseif($type=='time'){
				$result=$regs[4].':'.$regs[5];
			}
		}
		return $result;
	}
	
	// time parsing (9:00)
	function ParseTime($time){
		if(preg_match('/(\d{1,2}):(\d{1,2})/i',$time,$regs)){
			$result=array(
				'h'	=> (int)$regs[1],
				'm'	=> (int)$regs[2]
			);
			return $result;
		}
		return false;
	}
	
	// text cut
	function TrimText($text,$len){
		if(mb_strlen($text)>$len) {
			$text=mb_substr($text,0,$len);
			$isSpace=false;
			$i=mb_strlen($text)-1;
			$bot=round($len-$len/4);
			while($i>$bot&&!$isSpace){
				if($text{$i}==' '||$text{$i}=="\n") {
					$isSpace=true;
					$text=mb_substr($text,0,$i);
				}
				$i--;
			}
			$text=trim(preg_replace('/[:;.\'"]+$/is','',$text));
			$text=$text.'...';
		}
		return $text;
	}
	
	// text spoiler
	function SpoilerText($text,$len,$params=array()){
		if(mb_strlen($text)<=$len)return $text;
		$encoding = isset($params['encoding']) ? $params['encoding'] : mb_internal_encoding();
		$text1 = mb_substr($text, 0, $len, $encoding);
		$position = mb_strrpos($text1, ' ', $encoding);
		$text1 = mb_substr($text1, 0, $position, $encoding);
		$text2 = mb_substr($text, $position, mb_strlen($text)-$position, $encoding);
		
		$splitter = isset($params['splitter']) ? $params['splitter'] : '...';
		$result = $text1 . '<span class="spoiler-splitter">'.$splitter.'</span><span style="display: none;">' . $text2 .'</span>'; 
		return $result;
	} 
	
	// 2007-12-30 to 30.12.2007
	function DateEng2Rus($date){
		if(preg_match("/(\d+){1,4}[\.-](\d+){1,2}[\.-](\d+){1,2} ?(\d+){0,2}.?(\d+){0,2}/i",$date,$regs))
			$result=$regs[3].'.'.$regs[2].'.'.$regs[1].($regs[4]?' '.$regs[4].':'.$regs[5]:'');
		return $result;
	}
	// 30.12.2007 to 2007-12-30
	function DateRus2Eng($date){
		if(preg_match("/(\d+){1,2}[\.-](\d+){1,2}[\.-](\d+){1,4} ?(\d+){0,2}.?(\d+){0,2}/i",$date,$regs))
			$result=$regs[3].'-'.$regs[2].'-'.$regs[1].($regs[4]?' '.$regs[4].':'.$regs[5]:'');
		return $result;
	}
	
	function DatePrepareToDb($date){
		if(preg_match("/(\d+){1,2}[\.\/-](\d+){1,2}[\.\/-](\d+){1,4} ?(\d+){0,2}.?(\d+){0,2}/i",$date,$regs))
			$result=$regs[3].'-'.$regs[2].'-'.$regs[1].($regs[4]?' '.$regs[4].':'.$regs[5]:'');
		return $result;
	}
	
	// is even
	function isEven($number){
		if($number%2==0)return true;
		return false;
	}
	
	// number for quantity
	function Num125($n){
		$n100=$n%100;
		$n10=$n%10;
	  	if(($n100>10)&&($n100<20)){
	    	return 5;
	  	}
	  	elseif($n10==1){
	    	return 1;
	  	}
	  	elseif(($n10>=2)&&($n10<=4)){
	    	return 2;
	  	}
	  	else{
	    	return 5;
	  	}
	}
	
	// ending for quantity
	function Word125($n,$ending1,$ending2,$ending5){
		return ${'ending'.Num125($n)};
	} 

	// price parse
	function ParsePrice($price,$mode='triplet',$params=array()){
		$result='';
		if($mode=='triplet'){
			$offset=$params['Offset']?$params['Offset']:3;
			$l=mb_strlen($price);
			$i=0;
			while($i>$l*(-1)){
				$i=$i-$offset;
				$result=mb_substr($price,$i>$l*(-1)?$i:$l*(-1),$i>$l*(-1)?$offset:$offset+$i+$l).($result?' ':'').$result;
			}
		}
		return $result;
	}
	
	// button
	function GetButton($title,$submit,$onclick=''){
		return '<span class="btn"'.($onclick?' onclick="'.$onclick.'"':'').'><span class="l"></span><input type="'.($submit?'submit':'button').'" value="'.$title.'"/><span class="r"></span></span>';
	}
	
	// browser table
	class BrowserTable{
		private $result='';
		private $rowsCount=0;
		private $itemsCount=0;
		private $tplTable='';
		private $tplHRow='';
		private $tplSimpleHRow='';
		private $tplRow='';
		private $tplSimpleRow='';		

		public function __construct($items=array()){
			$this->tplTable=LoadTemplate('browser_table');
			$this->tplHRow=LoadTemplate('browser_table_hrow');
			$this->tplSimpleHRow=LoadTemplate('browser_table_hrow_simple');
			$this->tplRow=LoadTemplate('browser_table_row');
			$this->tplSimpleRow=LoadTemplate('browser_table_row_simple');		

			if(count($items))$this->AddHeaderRow($items);
		}		

		public function AddHeaderRow($items=array()){
			if(!$this->itemsCount)$this->itemsCount=count($items);
			$content='';
			foreach($items as $item){
				$content.='<th>'.$item.'</th>';
			}
			$this->result.=SetContent($this->tplHRow,$content);
		}		

		public function AddSimpleHeaderRow($items=array()){
			if(!$this->itemsCount)$this->itemsCount=count($items);
			$content='';
			foreach($items as $item){
				$content.='<th>'.$item.'</th>';
			}
			$this->result.=SetContent($this->tplSimpleHRow,$content);
		}		

		public function AddBodyRow($id,$actionEdit,$actionDelete,$items=array(),$params=array()){
			$this->rowsCount++;
			if(!$this->itemsCount)$this->itemsCount=count($items);
			$content='';
			foreach($items as $item){
				$content.='<td>'.$item.'</td>';
			}
			$class=isEven($this->rowsCount)?'even':'';
			if($params['Class'])$class.=' '.$params['Class'];
			$this->result.=strtr($this->tplRow,array(
				'<%CLASS%>'			=> $class?' class="'.$class.'"':'',
				'<%ID%>'			=> $id,
				'<%TOOL_EDIT%>'		=> $actionEdit?'<img src="images/ico_edit.png" alt="Редактировать запись" title="Редактировать запись" class="ico" onclick="'.$actionEdit.'"/>':'',
				'<%TOOL_DELETE%>'	=> $actionDelete?'<img src="images/ico_delete.png" alt="Удалить запись" title="Удалить запись" class="ico" onclick="'.$actionDelete.'"/>':'',
				'<%CONTENT%>'		=> $content
			));
		}		

		public function AddSimpleBodyRow($items=array(),$params=array()){
			if(!$this->itemsCount)$this->itemsCount=count($items);
			$this->rowsCount++;
			$content='';
			foreach($items as $item){
				$content.='<td>'.$item.'</td>';
			}
			$class=isEven($this->rowsCount)?'even':'';
			if($params['Class'])$class.=' '.$params['Class'];
			$this->result.=strtr($this->tplSimpleRow,array(
				'<%CLASS%>'			=> $class?' class="'.$class.'"':'',
				'<%CONTENT%>'		=> $content
			));
		}		

		public function GetTable(){
			if(!$this->rowsCount)$this->result.='<td colspan="'.($this->itemsCount+1).'" class="empty">Здесь пока пусто...</td>';
			return SetContent($this->tplTable,$this->result);
		}
	}
	
	// list
	class RecordList{
		private $list=array();
		private $inited=false;
		
		public function __construct($src=array()){
			switch(gettype($src)){
				case 'array':
					$this->list=$src;
					break;
				case 'resource':
					while($this->list[]=dbGetRecord($src));
					array_pop($this->list);
					break;
				case 'string':
					$res=dbDoQuery($src,__FILE__,__LINE__);
					while($this->list[]=dbGetRecord($res));
					array_pop($this->list);
					break;
				default:
					$this->list=array();
					break;	
			}
		}
		
		public function GetCurr(){
			return current($this->list);
		}
		
		public function GetPrev($recordId=false){
			if($recordId)$this->SetPosById($recordId);
			return prev($this->list);
		}
		
		public function GetNext($recordId=false){
			if($recordId)$this->SetPosById($recordId);
			if(!$recordId && ($this->GetKey()==0 && !$this->inited)){
				$this->inited=true;
				return current($this->list);
			}
			return next($this->list);
		}
		
		public function GetFirst(){
			return $this->Reset();
		}
		
		public function GetLast(){
			return end($this->list);
		}
		
		public function GetKey($recordId=false){
			if($recordId)$this->SetPosByKey($recordId);
			return key($this->list);
		}
		
		public function GetPos($recordId=false){
			return $this->GetKey($recordId);
		}
		
		public function Reset(){
			$this->inited=false;
			return reset($this->list);
		}
		
		public function GetCount(){
			return count($this->list);
		}
		
		public function SetPosByKey($key){
			$this->Reset();
			while($this->GetNext()!==false){
				if($this->GetKey()==$key)return true;
			}
			return false;
		}
		
		public function SetPosById($recordId){
			$this->Reset();
			while($this->GetNext()!==false){
				$curr=$this->GetCurr();
				if($curr['Id']==$recordId){
					return $this->GetKey();
				}
			}
			return false;
		}
		
		public function ToArray(){
			return $this->list;
		}
		
	}
	
	// fileds list
	function GetFieldsList($document,$onlyShowed=false){
		if(is_array($document)&&$document['TableId']){
			$query = 'SELECT t1.Name, '
					.'t2.Id, t2.Title, t2.Type '
					.'FROM mycms_elements AS t2 LEFT JOIN mycms_fields AS t1 ON t2.FieldId=t1.Id '
					.'WHERE t2.DocumentId='.$document['Id'].($onlyShowed?' AND `Show`=1':'').' '
					.'ORDER BY IF(t2.`Order`,-1000/t2.`Order`,0) ASC';
		}else{
			$query = 'SELECT t1.Name, '
					.'t2.Id, t2.Title, t2.Type '
					.'FROM mycms_elements AS t2 LEFT JOIN mycms_fields AS t1 ON t2.FieldId=t1.Id '
					.'WHERE t2.DocumentId='.(is_array($document)?$document['Id']:$document).($onlyShowed?' AND `Show`=1':'').' '
					.'ORDER BY IF(t2.`Order`,-1000/t2.`Order`,0) ASC';
		}
		$res=dbDoQuery($query,__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$fields[$rec['Name']]=Array('Id'=>$rec['Id'],'Title'=>$rec['Title'],'Type'=>$rec['Type']);
		}
		return $fields;
	}
	
	function GetSubArray($array,$keyTitle){
		$subArray=Array();
		foreach($array as $key=>$val){
			$subArray[$key]=$val[$keyTitle];
		}
		return $subArray;
	}
	
	function BinToHex($bin){
		return bin2hex($bin);
	}
	
	function HexToBin($hex){
		return pack('H*',$hex);
	}

?>