<?php 

namespace Engine\Library;

abstract class Page {
  const TYPE_PAGE = 'page';
  const TYPE_PARTIAL = 'partial';

  protected $code;
  protected $content;

  protected $model;
  protected $templates;

  function __construct($code, $dir = '') {
    $this->code = $code;
    $this->dir = $dir;
  }

  // /**
  //  * Возвращает обработанный шаблон страницы (с замененными кастомными атрибутами)
  //  *
  //  * Заменяет кастомные атрибуты в шаблоне на соответствующие (полученные по ключу) значения из массива $attrs. Обрабатывае дефолтные свойства (HEADING, DESCRIPTION, BREADCRUMBS) на соответствующие им значения из класса. 
  //  * @param array $attrs Список пар `Кастомный атрибут` => `Значение для замены`
  //  * @return string Шаблон с замененными кастомными атрибутами
  //  */
  // function parse ($data = []): string {
  //   if ($this->current['type'] === self::TYPE_PAGE) {
  //     $view = $this->current['template']->Parse($data + $this->page);
  //   } else {
  //     var_dump($this->code);
  //     $view = $this->current['template']->Parse($data);
  //     // $var_dump($view);
  //   }
  //   return $view;
  // }

  function getTextBlocks() {
    $table = TABLES['TEXT_BLOCKS'];
    $code = $this->code;
    $request = dbDoQuery("SELECT * FROM {$table} WHERE `PageCode` = '{$code}'");
    
    $textblocks = [];

    while ($piece = dbGetRecord($request)) {
      $textblocks[$piece['Code']] = $piece;
    }

    return $textblocks;
  }
  
  /**
   * Возвращает свойство pageCode, содержащее код страницы для обращения к БД
   *
   * @return string
   */
  function getCode(): string {
    return $this->code;
  }

  public function code(): string{
    return $this->code;
  }

  /**
   * Возвращает свойство pageTitle, хранящее заголовок страницы (тег <title>)
   *
   * @return string
   */
  function getTitle(): string {
    return $this->page['Title'];
  }

  /**
   * Возвращает свойство pageHeading, содержащее главный заголовок, исользуемый на странице
   *
   * @return string
   */
  function getHeading(): string {
    return $this->page['Heading'];
  }

  /**
   * Возвращает свойство pageDescription, содержащее небольшое описание, отображаемое на странице
   *
   * @return string
   */
  function getDescription(): string {
    return $this->page['Description'];
  }

  function getSeoTitle(): string {
    return $this->page['SeoTitle'];
  }

  function getSeoDescription(): string {
    return $this->page['SeoDescription'];
  }

  function getSeoKeywords(): string {
    return $this->page['SeoKeywords'];
  }

  /**
   * Возвращает массив, содержащий данные страницы:
   * - Code - Код страницы, используемый для получения страницы из БД
   * - Title - Название страницы
   * - Heading - Главный заголовок страницы
   * - Description - Описание страницы, отображаемое, как правило, в качестве вводного параграфа на странице
   * @return array
   */
  function getData(): array {
    return $this->page;
  }

  function setModel($model) {
    if ($model) {
      $this->model = $model;      
    }
  }

  function setPages($templates) {
    foreach ($templates as $name => $page) {
      $this->pages[$name] = new Template($page['template'], $this->dir);
    }
  }

  function getPage($name) {
    return $this->pages[$name];
  }

  function page($name) {
    // $this->current['template'] = $this->getPage($name);
    // var_dump('TYPE: '.self::TYPE_PAGE);
    // $this->current['type'] = self::TYPE_PAGE;
    return $this->getPage($name);
  }


  function setPartials($templates) {
    foreach ($templates as $name => $partial) {
      
      $type = $partial['type'];
      // var_dump($type);
      switch ($type) {
        case 'partial':
          $template = new Template($partial['template'], $this->dir.'/partial');
        break;
        case 'component':
          $dir = $this->dir.'/component';
          $template = new Template($partial['template'], $dir);
        break;
        case 'list':
          $template = new ListTemplate($partial['template'], 
            $this->dir.'/partial');
        break;
        case 'navigation':
          // var_dump($partial['template']);
          $template = new NavigationTemplate($partial['template'], $this->dir.'/partial');
        break;
        default:
          var_dump('Unknown type of partial: '.$type);
        break;
      }

      $template->setName($name);
      $this->partials[$name] = $template;
    }
  }

  function getPartial($name) {
    return $this->partials[$name];
  }

  function partial($name) {
    // $this->current['template'] = $this->getPartial($name);
    // var_dump('TYPE: '.self::TYPE_PARTIAL);
    // $this->type['type'] = self::TYPE_PARTIAL;
    return $this->getPartial($name);
  }

  /**
   * Инициализирует базовые поля страницы:
   * - Title
   * - Heading
   * - Description
   *
   * @param [type] $data
   * @return void
   */
  function init($data) {
    $this->page['Title'] = $data['Title'] ?: '';
    $this->page['Heading'] = $data['Heading'] ?: '';    
    $this->page['Description'] = $data['Description'] ?: '';
    $this->page['SeoTitle'] = $data['SeoTitle'] ?: '';
    $this->page['SeoDescription'] = $data['SeoDescription'] ?: '';
    $this->page['SeoKeywords'] = $data['SeoKeywords'] ?: '';
  }

  /**
   * Добавляет к элементам массива новое свойство Links, построенное по свойству Code, которое содержит абсолютную ссылку на элемент. Ссылка строится в формате /код-страницы/метод/код-элемента
   *
   * @param array $array - массив элементов
   * @param string $action - метод, использующийся при формировании ссылки
   * @return array 
   */
  protected function setLinks($array, $action) {
    // var_dump(array_keys($array));
    // var_dump(range(0, count($array) - 1));
    foreach ($array as $key => $item) {
      $array[$key]['Link'] = "/{$this->code}/{$action}/{$item['Code']}";
    }
    return $array;
  }

  
}
