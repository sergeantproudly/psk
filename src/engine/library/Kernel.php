<?php    
	/**
	* Возвращает код страницы (значение параметра) к которой совершен GET запрос. 
	* Запрос к документу подменяется на уровне htaccess и заменяется на запрос к По параметру 'p_code' из GET массива. При запросе к корню, вернет 'main'
	* @return string Код страницы, к которой был совершен запрос
	*/ 
  function krnGetPageCode() {	
		return isset($_GET['p_code']) ? $_GET['p_code'] : 'main';
	}
	
	/**
	 * Возвращает название модуля на основании кода страницы.
	 * Во всех случаях возвращает код страницы, он не был изменен в ходе исполнения программы
	 * @return string Код модуля, соответствующий коду страницы
	 */
	function krnGetPageModule(){
		global $Params;
		return $Params['Site']['Page']['Code'] ? $Params['Site']['Page']['Code'] : false;
	}
	

	/**
	 * Подключает файл модуля.
	 * Имя модуля определяется из глобального массива параметров $Params['Site']['Page']['Module']
	 * @return krn_abstract Возвращает объект (модуль), реализующий интерфейс ядра
	 */
	function krnLoadModule(){
		global $Params;

		if(!$Params['Site']['Page']['Module']) {
			return false;
		}
		
		if(!file_exists(MODULE_DIR.$Params['Site']['Page']['Module'].'.php')) {
			$Params['Site']['Page']['Module'] = '_static';
		}

		require_once(MODULE_DIR.$Params['Site']['Page']['Module'].'.php');
		$module = new $Params['Site']['Page']['Module'];

		return $module;
	}
	
	// [TODO]: Добавить описание функции krnLoadModuleByName
	function krnLoadModuleByName($module_name, $Settings = false){
		if(!$module_name || !file_exists(MODULE_DIR.$module_name.'.php')) {
			return false;
		}
		
		require_once(MODULE_DIR.$module_name.'.php');
		$module = new $module_name($Settings);
		
		return $module;
	}
	
	/**
	 * Загружает шаблон с указанным именем.
	 * 
	 * В случае, если имя шаблона не указано, то будет загружен базовый шаблон, являющейся основой любой страницы сайта
	 *
	 * @param string $templateName
	 * @return void
	 */
	function krnLoadPage($templateName = ''){
		return LoadTemplate($templateName ? $templateName : 'base');
	}
	
	// [TODO]: Добавить описание функции krnLoadPageStatic
	function krnLoadPageStatic(){
		$base = krnLoadPage();
		$base = strtr($base,array(
			'{{CONTENT}}'	=> LoadTemplate('base_static')
		));
		return $base;
	}
	
	// [TODO]: Добавить описание функции krnLoadPageByTemplate
	function krnLoadPageByTemplate($templateName){
		$base = krnLoadPage();
		$base = strtr($base, array(
			'{{CONTENT}}'	=> LoadTemplate($templateName)
		));
		return $base;
	}
	
	/**
	 * Загрузка библиотеки.
	 * Возвращает TRUE, если загрука библиотеки из каталога библиотек (по умолчанию library/) проведена успешно. Если файл библиотеки не будет найден, будет предпринята попытка подключить обычный php файл. В противном случае вернет FALSE
	 * 
	 * @param string $libname Путь к подключаемой библиотеке
	 * @return bool
	 */
	function krnLoadLib($libname) {
		$libpath = LIBRARY_DIR.$libname.'.lib.php';
		$filepath = LIBRARY_DIR.$libname.'.php';
		$path = file_exists($libpath) ? $libpath : $filepath; 
		
		if (!file_exists($path)) {
			return false;
		}

		return require_once($path);
	}

?>
