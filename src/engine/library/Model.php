<?php
/**
 * Model
 * 
 * Набор базовых функций модели учитывает наиболее общие запросы к БД, которые могут быть совершены.
 * 
 * Каждая модель определяет для себя только 1 таблицу в БД. Все остальные данные для страницы должны быть получены через модели-зависимости (dependecies)
 * 
 * 
 */
namespace Engine\Library;

abstract class Model {
  protected $db;
  protected $currentTable;
  protected $tables = [
    // PAGES DATA
    'pages' => 'pages',
    'projects' => 'page_projects',
    'services' => 'page_services',
    'articles' => 'page_articles',
    'articles_photos' => 'page_articles_photos',
    'partners' => 'page_partners',
    'clients' => 'page_clients',
    'products_directions' => 'data_products_directions',
    'products' => 'data_products',
    'rel_products_dirs' => 'data_rel_products_directions',
    'subcategories' => 'data_subcategory',
    'goods' => 'data_goody',
    'goods_chars' => 'data_goody_char',
    'goods_photos' => 'data_goody_photo',
    'goods_related' => 'data_goody_related',
    'company' => 'page_company',
    'register' => 'page_register',
    'staff' => 'page_staff',
    'materials' => 'materials',
    'slider' => 'slider',


    // COMPONENTS DATA
    'navigation' => 'component_navigation',
    'auth-navigation' => '`component_auth-navigation`',
    'gallery' => 'component_gallery',
    'reviews' => 'component_reviews',
    'service-review' => '`component_service-review`',
    'service-image' => '`data_service-image`',
    'products-images' => 'data_products_images',
    'advantages' => 'data_company_advantages',


    // DATA
    'city' => 'data_city',
    'weekday' => 'data_weekday',
    'shipping' => 'data_shipping',
    'users' => 'data_users',
    
    'department' => 'data_department',

    'certificates' => 'data_certificates',
    'cert-types' => 'data_certificates_types',
    'cert-manufacturers' => 'data_certificates_manufacturers',
    
    'orders' => '`data_users-order`',
    'orders-items' => '`data_users-order_items`',
    
    'content' => 'data_content',
    
    'catalog' => 'data_items',
    'catalog_filters' => '`data_catalog-filters`',

    'catalog_cert' => 'data_items_certs',
    'catalog_cert_file' => 'data_items_certs_files',

    'stores'  => 'data_stores',
    'stores_attributes' => 'data_stores_attributes',
    'stores_vehicles' => 'data_stores_vehicles',
    'stores_photos' => 'data_stores_photos',
    
    'register-record' => '`data_register_record`',
    'register-company' => 'data_register_company',
    'user-to-contractor' => 'bridge_user_contractor',

    'license' => 'data_licenses',
    'review' => 'data_reviews',
    'project-equipment' => '`data_project-equipment`',
    'images' => 'data_images',
    'category' => 'data_category',

    'personal' => 'personal_data',

    'media' => 'data_media',
    'media-photo' => 'data_media_photos',
    'media-video' => 'data_media_videos',
  ];

  function __construct(SafeMySQL $db = null) {
    $this->db = $db;
  }

  function setDB($database) {
    if ($database) {
      // var_dump(get_class($this) . ': Database attached');
      $this->db = $database;
      return true;
    }
    return false;
  }

  function getDB() {
    return $this->db;
  }

  function table($name) {
    $this->currentTable = $this->tables[$name];
    return $this;
  }

  function getAll($count = 0, $offset = 0, $table = '') {
    if (!$table) $table = $this->currentTable;

    $query = "SELECT * FROM {$table}";

    return $this->_getAllByQuery($query, $count, $offset);
  }

  function getAllSorted($sortCol = false, $sortDirection = false, $count = 0, $offset = 0, $table = '') {
    if (!$table) $table = $this->currentTable;

    if (strtoupper($sortDirection) !== 'ASC' && strtoupper($sortDirection) !== 'DESC')
      $sortDirection = 'ASC';

    if ($sortCol !== false) {
      $query = $this->db->parse("SELECT * FROM {$table} ORDER BY ?n {$sortDirection}", $sortCol);
    } else {
      $query = $this->db->parse("SELECT * FROM {$table} ORDER BY IF(`Order`, -1000/`Order`, 0) ASC");
    }

    return $this->_getAllByQuery($query, $count, $offset);
  }

  protected function _getAllByQuery($query, $count = 0, $offset = 0) {
    $db = $this->db;

    $data = [];

    if ($count >= 1) {
      $query .= " LIMIT ?i";
      $query .= " OFFSET ?i";
      // var_dump(" LIMIT {$count}");
      // var_dump(" OFFSET {$offset}");
      // var_dump($db->parse($query, $count, $offset));
      $query = $db->parse($query, $count, $offset);
    }

    $data = $db->getAll($query);
    return $data;
  }



  function getAllWhere($condition, ...$params) {
    $query = "SELECT * FROM {$this->currentTable} WHERE {$condition}";
    $data = [];

    foreach ($params as $value) {
      $query = $this->db->parse($query, $value);
    }

    $data = $this->db->getAll($query);

    return $data;
  }

  function getAllWhereSorted($condition, ...$params) {
    $query = "SELECT * FROM {$this->currentTable} WHERE {$condition} ORDER BY IF(`Order`, -1000/`Order`, 0) ASC";
    $data = [];

    foreach ($params as $value) {
      $query = $this->db->parse($query, $value);
    }

    $data = $this->db->getAll($query);

    return $data;
  }

  function getOne() {
    $data = $this->db->getRow("SELECT * FROM {$this->currentTable}");
    return $data;
  }

  function getOneWhere($condition, ...$params) {
    $query = "SELECT * FROM {$this->currentTable} WHERE {$condition}";
    // var_dump($query);
    foreach ($params as $value) {
      $query = $this->db->parse($query, $value);
    }

    $data = $this->db->getRow($query);

    return $data;
  }

  function getOneById($id) {
    return $this->getOneWhere('`Id` = ?i', $id);
  }

  function getColWhere($condition, ...$params) {
    $query = "SELECT * FROM {$this->currentTable} WHERE {$condition}";

    foreach ($params as $value) {
      $query = $this->db->parse($query, $value);
    }

    return $this->db->getCol($query);

  }

  function queryAll($query) {
    $query = preg_replace('/\?c/', $this->currentTable, $query);
    var_dump($query);
  }

  function getContent($pageCode) {
    $pageId = $this->db->getOne('SELECT `Id` from ?n WHERE `Code` = ?s', $this->tables['pages'], $pageCode);
//    var_dump($pageCode .' : '. $pageId);
//     var_dump("$pageCode: id=$pageId");
    if (!$pageId) {
      return [];
    }

    $content = $this->db->getInd('Code',
      "SELECT `Code`, `Value` 
       FROM {$this->tables['content']} 
       WHERE `PageId` = ?i", $pageId
    );

    if (!is_array($content)) $content = [];

    foreach ($content as $key => $data) {
      $content[$key] = $data['Value'];
    }

    $content += $this->db->getRow(
      "SELECT `Title`, `Heading`, `Description`
       FROM {$this->tables['pages']} 
       WHERE `Id` = ?i", $pageId
    );

//    var_dump($content);

    return $content;
  }

  function getCount($table) {
      return $this->db->getOne("SELECT COUNT(Id) FROM {$table}");
  }

  function getCountWhere($table, $cond, ...$params) {
    foreach ($params as $param) {
      $cond = $this->db->parse($cond, $param);
    }
    return $this->db->getOne("SELECT COUNT(Id) FROM {$table} WHERE {$cond}");
  }

  function getChildrenPages($pageCode) {
    $parent = $this->table('pages')->getOneWhere('Code = ?s', $pageCode);
    return $this->table('pages')->getAllWhere('ParentId = ?i', $parent['Id']);
  }

  protected function _loopFormatDate($list, $dateKey) {
    foreach ($list as &$item) {
      $item[$dateKey] = $this->_formatDate($item[$dateKey]);
    }
    unset($item);
    return $list;
  }

  protected function _formatDate($dateString) {
    $date = new \DateTime($dateString);

    return $date->format('d.m.Y');
  }

  protected function _truncateAll($tableKey) {
    return $this->db->query("TRUNCATE {$this->tables[$tableKey]}");
  }

}
