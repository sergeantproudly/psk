<?php
namespace Site\Pages;
use Engine\Library\Page;

class SearchPage extends Page {
  const CODE = 'search';
  const DIR = self::CODE;
  public $modelName = 'PageModel';

  public function __construct() {
    parent::__construct(self::CODE, self::DIR);

    $this->setPages([
      'index' => ['template' => 'search'],
    ]);

    $this->setPartials([
      'list' => [
        'type' => 'list',
        'template' => 'search__item'
      ],
      'empty' => [
        'type' => 'partial',
        'template' => 'search__status--empty'
      ]
    ]);
  }
  
  function index($params = []) {
    $keyword = $params['keyword'] ?: '';
    $keyword = $keyword ?: $_GET['keyword'];
   
    $result = '';

    if ($keyword) {
      // CONTENT 
      // $prep_target = '%' . $keyword. '%';
      // var_dump($prep_target);
      $pattern = '/' . $keyword . '/iu';
      // var_dump('Ищем: '.$keyword);
      $matches = [];
      $servicesComponent = new ServicesComponent($this->model->getDB());
      $articlesComponent = new ArticlesComponent($this->model->getDB());

      $articles = $articlesComponent->model->getArticles();
      $articles = $articlesComponent->setLinks($articles, 'detail');
      
      $services = $servicesComponent->model->getServices();
      $services = $servicesComponent->setLinks($services, 'detail');
      // var_dump($services);
      $data = $services + $articles;
      $subjects = [
        'Title' => 'Title',
        'Description' => 'Content'
      ];

      foreach ($data as $object):
        
        $found = false;
        
        foreach ($object as $key => $value) {
          if (is_string($value) && preg_match($pattern, $value) === 1) {
            $found = true;
            break;
          }
        }

        if ($found):
          $content = strip_tags($object['Description']);
          $end = strlen($content);
          
          $start = mb_stripos($content, $keyword);
          $length = $start === false ? 200 : $start + mb_strlen($keyword);
          
          $start = $start - 100 > 0 ? $start - 100 : 0;
          $length =  $length + 100 < $end ? $length + 100 : $end;

          for (; $start > 0; $start--) { 
            if ($content[$start] === ' ') 
              break;
          }

          for (; $length < $end; $length++) { 
            if ($content[$length] == ' ') break;
          }
          // var_dump($start, $length);

          $excert = mb_substr($content, $start, $length);
          $excert = $start != 0 ? '...'.$excert : $excert;
          $excert = $length != $end ? $excert.'...' : $excert;

          $excert = str_replace($keyword, "<span class='search__keyword'>{$keyword}</span>",  $excert);

          
          $matches[] = [
            'title' => $object['Title'],
            'description' => $excert,
            'link' => $object['Link'],
          ];
        endif;
      endforeach;
      
      // var_dump($matches);
      
      // // ARTICLES
      // $matched = $this->model->getDB()->getAll("SELECT `Title`, `ShortDescription`, `Description` FROM `page_articles` WHERE `Title` LIKE ?s OR `ShortDescription` LIKE ?s OR `Description` LIKE ?s", $preg_target, $preg_target, $preg_target);
      // var_dump($matched);

      // // SERVICES
      // $matched = $this->model->getDB()->getAll("SELECT `Title`, `ShortDescription`, `Description` FROM `page_services` WHERE `Title` LIKE ?s OR `ShortDescription` LIKE ?s OR `Description` LIKE ?s", $preg_target, $preg_target, $preg_target);
      // var_dump($matched);

      // // PROJECTS
      // $matched = $this->model->getDB()->getAll("SELECT `Title`, `ShortDescription`, `Assignment`, `Solution` FROM `page_projects` WHERE `Title` LIKE ?s OR `ShortDescription` LIKE ?s OR `Assignment`LIKE ?s OR `Solution`LIKE ?s", $preg_target, $preg_target, $preg_target, $preg_target);

      // var_dump($matched);

    }
    
    $content = $matches ? $this->partial('list') : $this->partial('empty');
    $this->getPage('index')->addInclude($content, 'content');

    return $this->getPage('index')->parse([
      'content' => $matches ?: ['keyword' => $keyword]
    ]);
    
  }

  function keyword ($params = []) {
    // $keyword = array_shift($params) == 'keyword' ? array_shift($params) : '';
    $params['keyword'] = array_shift($params);
    // var_dump($params);
    return $this->index($params);
  }
  
  function GetSearchResultsList() {
    /** Поиск по новостям */
    $matches = $this->db->query('SELECT Id, Code, Title, Text FROM news WHERE Title LIKE ?s OR Text LIKE ?s', $prep_target, $prep_target);
    foreach ($matches as $match) {
      $content .= strtr($element, array(
        '{{TYPE}}' => 'Новость',
        '{{HREF}}' => 'news/' . $match['Code'],
        '{{TITLE}}' => $match['Title'],
        '{{DESCRIPTION}}' => $match['Text'] ? '<div class="desc">' . trimText(strip_tags($match['Text']), 700) . '</div>' : ''
      ));
    }
    
    /** Поиск по статьям */
    $matches = $this->db->query('SELECT Id, Code, Title, Text FROM statues WHERE Title LIKE ?s OR Text LIKE ?s', $prep_target, $prep_target);
    foreach ($matches as $match) {
      $content .= strtr($element, array(
        '{{TYPE}}' => 'Статья',
        '{{HREF}}' => 'statues/' . $match['Code'],
        '{{TITLE}}' => $match['Title'],
        '{{DESCRIPTION}}' => $match['Text'] ? '<div class="desc">' . trimText(strip_tags($match['Text']), 700) . '</div>' : ''
      ));
    }
    
    /** Поиск по интервью */
    $matches = $this->db->query('SELECT Id, Code, Title, Text FROM interviews WHERE Title LIKE ?s OR Text LIKE ?s', $prep_target, $prep_target);
    foreach ($matches as $match) {
      $content .= strtr($element, array(
        '{{TYPE}}' => 'Интервью',
        '{{HREF}}' => 'interviews/' . $match['Code'],
        '{{TITLE}}' => $match['Title'],
        '{{DESCRIPTION}}' => $match['Text'] ? '<div class="desc">' . trimText(strip_tags($match['Text']), 700) . '</div>' : ''
      ));
    }
    
    /** Поиск по советам */
    $matches = $this->db->query('SELECT Id, Code, Title, Text FROM advices WHERE Title LIKE ?s OR Text LIKE ?s', $prep_target, $prep_target);
    foreach ($matches as $match) {
      $content .= strtr($element, array(
        '{{TYPE}}' => 'Совет',
        '{{HREF}}' => 'advices/' . $match['Code'],
        '{{TITLE}}' => $match['Title'],
        '{{DESCRIPTION}}' => $match['Text'] ? '<div class="desc">' . trimText(strip_tags($match['Text']), 700) . '</div>' : ''
      ));
    }
    
    $result = strtr($result, array(
      '{{TITLE}}' => 'Результаты поиска',
      '{{CONTENT}}' => $content ? $content : '<div class="marg-st">По вашему запросу ничего не найдено</div>',
      '{{TARGET}}' => $this->search_target
    ));
    return $result;
  }
}

?>
