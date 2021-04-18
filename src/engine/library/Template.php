<?php
namespace Engine\Library;

class Template {
  const INCLUDE = 'INCLUDE';

  protected $name = '';
  protected $plain = '';  // Строковое представление шаблона
  protected $data = [];  // "Базовые" данные, которые обычно дублируются на всех шаблонах одного типа (например, на страницах - Title, Heading, Description)
  protected $includes = [];

  /**
   * Возвращает экзампляр класса, с инициализированным полем $plain, хранящим сроковый эквивалент файла шаблона
   *
   * @param string $filename
   * @param string $dir
   */
  function __construct(string $filename = '', string $dir = '') {
    if ($filename) {
      $this->plain = $this->load($filename, $dir);
    }
  }

  /**
   * Возвращает строку, содержащую шаблон
   *
   * @return string
   */
  function getPlain () : string {
    return $this->plain;
  }

  function setPlain ($plain) : void {
    $this->plain = $plain;
  }

  function setName ($name) {
    $this->name = strtoupper($name);
    return $this;
  }

  function getName() {
    return $this->name;
  }

  function setData($data) {
    $this->data = $data;
    return $this;
  }

  function getData($data) {
    return $this->data;
  }

  function addInclude ($template, $name = '') {
    $this->includes[strtoupper($name) ?: $template->getName()] = $template;
    return $this;
  }

  function removeInclude ($template) {
    $name = $template->getName();

    if (isset($this->includes[$name]))
      unset($this->includes[$name]);

    return $this;
  }

  function load ($name, $dir = '') {
    return Common::LoadTemplate($name, $dir);
  }

  /**
   * Возвращает содержимое шаблона с замененными на значения кастомными атрибутами
   *
   * @param array $data Массив, содержащий данные для замены кастомных атрибутов,
   * при этом ключи массива являются именами кастомных атрибутов шаблона
   * @return string
   */
  function parse ($data = []) {
    if (is_array($this->data) && count ($this->data))
      $data += $this->data;
    
    $attrs = $this->scan($this->plain);
    $substitues = [];
    // var_dump($this->name );
    $data = array_change_key_case($data, CASE_UPPER);
    
    foreach ($attrs as $attr) {
      $attr = strtoupper($attr);
      $value = '';
      $parent = '';
      $child = '';
      $delimiter = strpos($attr, ':');
      
      if($delimiter !== false) {
        list($parent, $child) = explode(':', $attr);
        $attr = "{$parent}:{$child}";
        if ($parent == self::INCLUDE) {

          // [TODO]: Обдумать идею добавления отдельного парсера
          // var_dump('Name: '. $child);
          // var_dump($data[$child]);
          // var_dump($this->includes[$child]);
          $value = isset($this->includes[$child]) ? $this->includes[$child]->parse($data[$child]) : '';
        } else {
          $data[$parent] = array_change_key_case($data[$parent], CASE_UPPER);
          $value = $data[$parent][$child] ?? '';        
        }
        
      }
    
      $value = $value ?: ($data[$attr] ?? '');     
      
      // if (!$value) {
      //   // [TODO]: Оставлено для debug
      //   $value = "<span style='color: red;'>DEBUG: ATTRIBUTE {{{$attr}}} NOT FOUND: </span>";
      //   // $substitues["{{{$attr}}}"] = $value;
      //   // var_dump($substitues);
      // }
      
      $substitues["{{{$attr}}}"] = $value;
 
    }

    // var_dump($substitues);
    return strtr($this->plain,  $substitues);
  }

  /**
   * Возвращает список кастомных атрибутов, найденных в файле
   *
   * @param string $plain
   * @return array
   */
  function scan($plain) : array {
    $attrs = [];
    $attrRegexp = '/{{([a-zA-Z\-_:]+)}}/';
    preg_match_all($attrRegexp, $plain, $attrs);
    // var_dump($attrs[1]);

    return array_change_key_case($attrs[1], CASE_UPPER);
  }
}

class ListTemplate extends Template {

  function __construct($name = '', $dir = '') {
    parent::__construct($name, $dir);
  }

  function parse($data = []) {
    $list = '';
    
    foreach ($data as $item) {
      $list .= parent::parse($item);
      // var_dump($item['Link']);
    }

    return $list;
  }
}

class NavigationTemplate extends Template {
  protected $default;
  protected $active;

  protected $callback;
  
  function __construct($templates, $dir = '') {
    $this->default = 
      new Template($templates['default'], $dir ?: $templates['dir']);
    $this->active = 
      new Template($templates['active'], $dir ?: $templates['dir']);  
  }

  function parse($data = [], $callback = '') {
    if (!$callback && $this->callback) {
      $callback = $this->callback;
    }

    $template = '';

    foreach ($data as $item) {
      // var_dump($item);
      if (is_callable($callback)) {
        $template .= $callback($item) ? $this->active->parse($item) : $this->default->parse($item);
      } else {
        $template .= $this->default->parse($item);
      }
    }
    
    return $template;
  }

  function setCallback($callback) {
    $this->callback = is_callable($callback) ? $callback : '';
    return $this;
  }

}

