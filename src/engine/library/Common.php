<?php

	namespace Engine\Library;
	
	class Common {
    public static function LoadFile ($filename){
				// [TODO]: Заменить потоковые функции работы с файлами на file_get_contents
        if (file_exists ($filename)){
            $fp = fopen($filename, "r");
            $content = fread ($fp, filesize ($filename));
            fclose ($fp);
            return $content;
        } else return self::ShowFatalError('Не удалось загрузить файл - ' . $filename);
    }

    // считывает html-темплейт
    public static function LoadTemplate ($name,$dir=''){
			if($dir)
				$dir .= '/';
			// [TODO]: Упростить тернарный оператор и синхронизировать реализацию метода с таким же в библиотеке админки
			$file = file_exists(TEMPLATE_DIR.$dir.$name) ?TEMPLATE_DIR.$dir.$name : 
			( 
				file_exists(TEMPLATE_DIR.$dir.$name.'.html') ?TEMPLATE_DIR.$dir.$name.'.html' : TEMPLATE_DIR.$dir.$name.'.htm'
			);
			// var_dump($file);
			return self::LoadFile ($file);
		}
		
	public static function setLinks($array, $page, $action = '', $field = 'Code') {
		foreach ($array as $key => &$item) {
	    	$item['Link'] = "/{$page}";
		    if ($action)
		        $item['Link'] .= "/{$action}/{$item[$field]}/";
		    else
		        $item['Link'] .= "/{$item[$field]}/";
		}
		return $array;
	}

	public static function setNl2Br($array, $keylabel) {
		foreach ($array as $key => &$item) {
	    	$item[$keylabel] = nl2br($item[$keylabel]);
		}
		return $array;
	}
	
    
   // заменяет атрибуты в темплейте
  function SetAtribs($template,$array){
		if(count($array)){
			foreach($array as $k=>$v)
				$template=str_replace('{{'.strtoupper($k).'}}',$v,$template);
		}
		return $template;
	}
	
	// заменяет атрибут контент в темплейте
	function SetContent($template,$content){
		return strtr($template,array(
			'{{CONTENT}}'	=> $content
		));
	}
    
    // показывает сообщение об ошибке
  public static function ShowError($message){
		echo $message.'<br/>';
	}
    
  // показывает сообщение о фатальной ошибке и умирает
  public static function ShowFatalError($message){
		die($message.'<br/>');
	}

    // пересылка на указанный URL
  function Redirect($url){
    header('Location: '.$url);
    exit;
  }
    
    // перезагрузка страницы
  function Reload(){
		header('Location: '.$_SERVER['HTTP_REFERER']);
	}

	// очистка от лишних пробелов
	function SpaceClear(&$var, $mode=false){
			if($mode!='chop')$var = trim($var);
			if($mode!='trim')$var = chop($var);
			return $var;
	}

	// замена опасных символов html сущностями
	function ReplaceEssentials(&$var){
			$var = strtr($var, array('<'	=> '&lt;',
								'>'	=> '&gt;',
								'"'	=> '&quot;',
								"'"	=> '&#039;'));
			return $var;
	}

	// параметры GET текущей страницы (строка)
	function GetPageQuery(){
			return getenv ('QUERY_STRING');
	}

	// локация на сервере текущей страницы (строка)
	function GetPageLocation(){
			return getenv ('SCRIPT_NAME');
	}
    
    // добавление переменных в url
	function UrlAddVars($url, $vars){
		foreach($vars as $k=>$v){
			$url=preg_replace('/\.html/is','-'.$k.'-'.$v.'.html',$url);
		}
		return $url;
	}
    
    // удаление переменных из url
	function UrlDelVars($url,$vars){
		foreach($vars as $k=>$v){
			$url=preg_replace('/-'.$k.'-[^\-]*(-|\.html$)/is','\\1',$url);
		}
		return $url;
	}
	
	// замена переменных в url
	function UrlSetVars($url, $vars){
		$url = UrlDelVars($url, $vars);
		$url = UrlAddVars($url, $vars);
	}
    
	// постраничная навигация
	function GetNavigationMn($count,$p_size,$pg_size,$cur_page,$url=false,$get_var='page'){
    	if(!$url)$url=$_SERVER['REQUEST_URI'];
    	if(!$p_size)$p_size=1;
    	if(!$pg_size)$pg_size=1;
		$result='';
		$page_count=ceil($count/$p_size);
		if($page_count>1){
			$page_group = ceil($cur_page/$pg_size);
			$url=UrlDelVars($url,array($get_var=>''));
			//первая страница
			if(($cur_page > 1) && ($cur_page - $pg_size >= 1)){
				$result.='<a href="'.UrlAddVars($url,array($get_var=>1)).'" class="ext">1</a><span class="dotts">...</span>';
			}
			//список страниц
			for($i = $page_group*$pg_size-$pg_size+1; $i <= ($page_group*$pg_size < $page_count ? $page_group*$pg_size : $page_count); $i++){
				$result.=($i == $cur_page ? '<span class="curr">'.$i.'</span> ' : '<a href="'.UrlAddVars($url,array($get_var=>$i)).'">'.$i.'</a> ');
			}
			//последняя страница
			if(($cur_page < $page_count) && ($page_count >= $cur_page + $pg_size)){
				$result.='<span class="dotts">...</span><a href="'.UrlAddVars($url,array($get_var=>$page_count)).'" class="ext">'.$page_count.'</a>';
			}
			if($page_count>1)$result.='<a href="'.UrlAddVars($url,array('all'=>'1')).'" class="all">Показать все</a>';
		}

		return '<div class="mn-nav">'.$result.'</div>';
	}
	
		// показать еще
	function GetMore($function){
		return '<div class="more" onclick="'.$function.'"><img title="Загрузить еще" alt="Загрузить еще" src="/images/ico_more.png"><span class="dotted">Загрузить еще</span><span class="loading">Одну минутку</span></div>';
	}
	
	// [TODO]: Удалить старый компонент
	// хлебные крошки
	function GetBreadCrumbs($refs,$curr_page){
		$content='';
		foreach($refs as $k=>$v)
			$content.='<a href="'.$v.'">'.$k.'</a> / ';
		$content.='<span class="curr">'.$curr_page.'</span>';
		return '<div id="bread-crumbs">'.$content.'</div>';
	}
	
	// модификация даты
	public static function ModifiedDate($date,$type=4){
		$months=array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
		if(preg_match('/(\d{4}).(\d{1,2}).(\d{1,2})/i',$date,$regs)){
			if($type==1){ // 01.01.2010
				$result=$regs[3].'.'.$regs[2].'.'.$regs[1];
			}elseif($type==2){ // 1 января
				$result=(int)$regs[3].' '.$months[$regs[2]-1].' '.$regs[1];
			}elseif($type==3){ // 01.01
				$result=$regs[3].'.'.$regs[2];
			}elseif($type==4){
				$today=mktime(0,0,0,date('m'),date('d'),date('Y'));
				$yesterday=$today-86400;
				$result=(strtotime($date)>=$today?'сегодня':(strtotime($date)>=$yesterday?'вчера':(int)$regs[3].' '.$months[(int)$regs[2]-1].(date('Y')==$regs[1]?'':' '.$regs[1])));
			}elseif($type==5){
				$result='<span class="day">'.$regs[3].'</span><br />'.$months[$regs[2]-1].'<br /><span class="year">'.$regs[1].'</span>';
			}elseif($type==6){
				$result.='<div class="day">'.$regs[3].'</div><div class="month">'.$months[$regs[2]-1].'<br /><span class="year">'.$regs[1].'</span></div>';
			}
		}
		return $result;
	}
	
	// модификация даты - времени
	function ModifiedDateTime($date,$type=1){
		$months=array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
		if(preg_match('/(\d{4}).(\d{1,2}).(\d{1,2}) (\d{1,2}).(\d{1,2}).(\d{1,2})/i',$date,$regs)){
			if($type==1){
				$result=$regs[4].':'.$regs[5].' '.$regs[3].'.'.$regs[2].'.'.$regs[1];
			}elseif($type==4){
				$today=mktime(0,0,0,date('m'),date('d'),date('Y'));
				$yesterday=$today-86400;
				$result=(strtotime($date)>=$today?'сегодня':(strtotime($date)>=$yesterday?'вчера':(int)$regs[3].' '.$months[(int)$regs[2]-1].(date('Y')==$regs[1]?'':' '.$regs[1].'г.'))).' в '.$regs[4].':'.$regs[5];
			}
		}
		return $result;
	}
	
	// парсинг времени (9:00)
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
	
	// парсинг временного диапазона (9:00-18:00)
	function ParseTimeInterval($interval){
		if(preg_match('/(\d{1,2}):(\d{1,2})-(\d{1,2}):(\d{1,2})/i',$interval,$regs)){
			$result=array(
				'sh'	=> (int)$regs[1],
				'sm'	=> (int)$regs[2],
				'eh'	=> (int)$regs[3],
				'em'	=> (int)$regs[4]
			);
			return $result;
		}
		return false;
	}
	
	// реверс временного диапазона (9:00-18:00 => 18:00-9:00)
	function ReverseInterval($interval){
		if(preg_match('/(\d{1,2}):(\d{1,2})-(\d{1,2}):(\d{1,2})/i',$interval,$regs)){
			return $regs[3].':'.$regs[4].'-'.$regs[1].':'.$regs[2];
		}else{
			return false;
		}
	}
	
	// проверка на вхождение во временной диапазон (9:00-18:00, 14:30)
	function InTimeInterval($interval,$time){
		$intervalInfo=ParseTimeInterval($interval);
		$timeInfo=ParseTime($time);
		if($intervalInfo['sh']*3600+$intervalInfo['sm']*60<$intervalInfo['eh']*3600+$intervalInfo['em']*60){ // нет перехода на след. день
			if(mktime($intervalInfo['sh'],$intervalInfo['sm'])<=mktime($timeInfo['h'],$timeInfo['m']) && mktime($timeInfo['h'],$timeInfo['m'])<mktime($intervalInfo['eh'],$intervalInfo['em'])){
				return true;
			}else{
				return false;
			}
			
		}else{ // есть переход на след. день
			if(mktime($intervalInfo['sh'],$intervalInfo['sm'],0,1,1)<mktime($timeInfo['h'],$timeInfo['m'],0,1,1) && mktime($timeInfo['h'],$timeInfo['m'],0,1,1)<mktime($intervalInfo['eh'],$intervalInfo['em'],0,1,2)){
				return true;
			}else{
				return false;
			}
		}
	}
	
	// остаток времени до конца временного диапазона
	function TillIntervalEnd($interval,$time){
		$intervalInfo=ParseTimeInterval($interval);
		$timeInfo=ParseTime($time);
		if($intervalInfo['sh']*3600+$intervalInfo['sm']*60<$intervalInfo['eh']*3600+$intervalInfo['em']*60){ // нет перехода на след. день
			if(mktime($intervalInfo['sh'],$intervalInfo['sm'])<=mktime($timeInfo['h'],$timeInfo['m']) && mktime($timeInfo['h'],$timeInfo['m'])<mktime($intervalInfo['eh'],$intervalInfo['em'])){
				$timeLeft=mktime($intervalInfo['eh'],$intervalInfo['em'])-mktime($timeInfo['h'],$timeInfo['m']);
				$result=array(
					'h'	=> floor($timeLeft/3600),
					'm'	=> floor(($timeLeft-floor($timeLeft/3600)*3600)/60)
				);
				return $result;
			}else{
				return false;
			}
			
		}else{ // есть переход на след. день
			$b=$timeInfo['h']*3600+$timeInfo['m']*60<$intervalInfo['sh']*3600+$intervalInfo['m']*60?2:1;
			if(mktime($intervalInfo['sh'],$intervalInfo['sm'],0,1,1)<=mktime($timeInfo['h'],$timeInfo['m'],0,1,$b) && mktime($timeInfo['h'],$timeInfo['m'],0,1,$b)<mktime($intervalInfo['eh'],$intervalInfo['em'],0,1,2)){
				$timeLeft=mktime($intervalInfo['eh'],$intervalInfo['em'],0,1,2)-mktime($timeInfo['h'],$timeInfo['m'],0,1,$b);
				$result=array(
					'h'	=> floor($timeLeft/3600),
					'm'	=> floor(($timeLeft-floor($timeLeft/3600)*3600)/60)
				);
				return $result;
			}else{
				return false;
			}
		}
	}
	
	// умное обрезание текста
	function TrimText($text,$len){
		if (mb_strlen($text)>$len) {
			$text = mb_substr($text,0,$len);
			$isSpace = false;
			$i = mb_strlen($text)-1;
			$bot = round($len-$len/4);
			while ($i>$bot&&!$isSpace) {
				if ($text{$i}==' '||$text{$i}=="\n") {
					$isSpace = true;
					$text = mb_substr($text,0,$i);
				}
				$i--;
			}
			$text = trim(preg_replace('/[:;.\'"]+$/is','',$text));
			$text = $text.'...';
		}
		return $text;
	}
	
	// умное обрезание html
	function TrimHtml($text,$len){
		if(mb_strlen(strip_tags($text))>$len){
			$res='';
			$tagged=false;
			$tagname='';
			$tags=array();
			$i=0;
			$lc=0;
			while($lc<=$len){
				$i++;
				if(mb_substr($text,$i-1,1)!='<'){
					if(mb_substr($text,$i-1,1)=='>'){ // конец названия тега (открывающего или закрывающего)
						$tagged=false;
						if(in_array($tagname,$tags)){ // уже есть - значит закрыли
							$key=array_search($tagname,$tags);
							unset($tags[$key]);
						}else{
							$tags[]=$tagname;
						}
					}else{ // не конец названия тега
						if(!$tagged){ // названия тега еще не начат => обычный текст
							$lc++;
						}else{ // идет название
							$tagname.=mb_substr($text,$i-1,1);
						}
					}
				}else{ // начало названия тега
					$tagged=true;
				}
				$res.=mb_substr($text,$i-1,1);
			}
			$res.='...';
			foreach(array_reverse($tags) as $k=>$tag){
				$res.='</'.$tag.'>';
			}
			return $res;
		}else{
			return $text;
		}	
	}

	
	/* обрезание текста с возможностью раскрыть полностью
	*	$text - текст
	*	$len - ограничение длины
	*	$word - слово-переключатель
	*/
	function SplitText($text,$len,$word=''){
		$text_cash=$text;
		if (mb_strlen($text)>$len) {
			$text = mb_substr($text,0,$len);
			$isSpace = false;
			$i = mb_strlen($text)-1;
			$bot = round($len-$len/4);
			while ($i>$bot&&!$isSpace) {
				if ($text{$i}==' '||$text{$i}=="\n") {
					$isSpace = true;
					$text = mb_substr($text,0,$i);
				}
				$i--;
			}
			$text = trim(preg_replace('/[:;.\'"]+$/is','',$text));
			$res = $text.($word?'<span>...</span>':'').' <span class="dashed" onclick="if(domPrevNode(this))hide(domPrevNode(this));show(domNextNode(this),\'inline\');hide(this);">'.($word?$word:'...').'</span><span style="display:none">'.mb_substr($text_cash,$i+1,mb_strlen($text_cash)-($i+1)).' <span class="dashed" onclick="hide(this.parentNode);show(domPrevNode(this.parentNode),\'inline\');if(domPrevNode(domPrevNode(this.parentNode)))show(domPrevNode(domPrevNode(this.parentNode)),\'inline\');">свернуть</span></span>';
		}else
			$res = $text;
		return $res;
	}
	
	// конвертация даты из 2007-12-30 в 30.12.2007
	function DateEng2Rus($date){
		if(preg_match("/(\d+){1,4}[\.-](\d+){1,2}[\.-](\d+){1,2}/i",$date,$regs))
			$result=$regs[3].'.'.$regs[2].'.'.$regs[1];
		return $result;
	}
	// конвертация даты из 30.12.2007 в 2007-12-30
	function DateRus2Eng($date){
		if(preg_match("/(\d+){1,2}[\.-](\d+){1,2}[\.-](\d+){1,4}/i",$date,$regs))
			$result=$regs[3].'-'.$regs[2].'-'.$regs[1];
		return $result;
	}

	public static function excess($string, $excess) {
		return str_replace($excess, '', $string);
	}

	public static function flGetExtension($filepath) {
	    return substr(strrchr($filepath,'.'),1);
 	}

 	public static function flSpace($filepath) {
		return str_replace(' ', '%20', $filepath);
	} 

	public static function flGetWebpByImage($filepath) {
		$extension = self::flGetExtension($filepath);
		return preg_replace('/\.' . $extension .'$/', '.webp', self::flSpace($filepath));
	}
	
	// четно - нечетно
	function isEven($number){
		if($number%2==0)return true;
		return false;
	}
	
	function Num125($n){
		$n100 = $n % 100;
		$n10 = $n % 10;
	  	if( ($n100 > 10) && ($n100 < 20) ) {
	    	return 5;
	  	}
	  	elseif( $n10 == 1) {
	    	return 1;
	  	}
	  	elseif( ($n10 >= 2) && ($n10 <= 4) ) {
	    	return 2;
	  	}
	  	else {
	    	return 5;
	  	}
	}
	
	function Word125($n,$ending1,$ending2,$ending5){
		return ${'ending'.Num125($n)};
	} 

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
	
	// функция генерации уникального постфикса для файла
    function SetPostfix($caption,$extension,$uploadpath){
        $i=1;
        $postfix='('.$i.')';
        $full_filename=$uploadpath.$caption.$postfix.'.'.$extension;
        while(file_exists($full_filename)){
            $i++;
            $postfix='('.$i.')';
            $full_filename=$uploadpath.$caption.$postfix.'.'.$extension;
        }
        return $uploadpath.$caption.$postfix.'.'.$extension;
    }
	}

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
		if($recordId!==false)$this->SetPosById($recordId);
		return prev($this->list);
	}
	
	public function GetNext($recordId=false){
		if($recordId!==false)$this->SetPosById($recordId);
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
	
	public function InsertFirst($src){
		switch(gettype($src)){
			case 'array':
				array_unshift($this->list,$src);
				break;
			case 'resource':
				$tmp=array();
				while($tmp[]=dbGetRecord($src));
				array_pop($tmp);
				array_unshift($this->list,$tmp);
				break;
			case 'string':
				$tmp=array();
				$res=dbDoQuery($src,__FILE__,__LINE__);
				while($tmp[]=dbGetRecord($res));
				array_pop($tmp);
				array_unshift($this->list,$tmp);
				break;
		}
	}
	
}

?>
