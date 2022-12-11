<?php
namespace Site\Models;
use Engine\Library\Model;
use Engine\Utility\Morphology;

class ProductionModel extends Model {

  protected $categoriesTree = [];

  public function init() {
    $categories = $this->db->getInd('Id', "SELECT p.*, d.Code AS DirectionCode FROM ?n p LEFT JOIN ?n p2d ON p.Id = p2d.CategoryId LEFT JOIN ?n d ON p2d.DirectionId = d.Id WHERE p.IsActive = 1 ORDER BY IF(p.`Order`, -100/p.`Order`, 0)", 
        $this->tables['products'],
        $this->tables['rel_products_dirs'],
        $this->tables['products_directions'] 
      );
    $subcategories = $this->db->getAllByInd('ProductId', 'SELECT *, (SELECT COUNT(g.Id) FROM ?n g WHERE g.SubcategoryId = sc.Id) AS GoodsCount FROM ?n sc WHERE sc.IsActive = 1 ORDER BY IF(sc.`Order`, -100/sc.`Order`, 0)',
        $this->tables['goods'],
        $this->tables['subcategories']
      );
    
    foreach ($categories as $categoryId => $category) {
      $this->categoriesTree[$categoryId] = $category;
      $this->categoriesTree[$categoryId]['Subcategories'] = $subcategories[$categoryId];
      $this->categoriesTree[$categoryId]['GoodsCount'] = 0;
      if ($subcategories[$categoryId] && count($subcategories[$categoryId])) {
        foreach ($subcategories[$categoryId] as $subcategory) {
          $this->categoriesTree[$categoryId]['GoodsCount'] += $subcategory['GoodsCount'];
        }
      }
    }
  }

  function getProductsDirections() {
    $directions = $this->table('products_directions')->getAllSorted();

    return $directions;
  }

  function getProductsDirectionsOthers($directionCode) {
    $directions = $this->table('products_directions')->getAllWhere('Code <> ?s', $directionCode);

    return $directions;
  }

  function getDirectionByCode($code) {
    return $this->table('products_directions')->getOneWhere('Code = ?s', $code);
  }

  function getProductByCode($code) {
    return $this->table('products')->getOneWhere('Code = ?s', $code);
  }

  function getProductById($id) {
    return $this->table('products')->getOneWhere('Id = ?', $id);
  }

  function getProducts($directionCode = false) {
    if ($directionCode) {
      $products = [];
      foreach ($this->categoriesTree as $category) {
        if ($category['DirectionCode'] == $directionCode) $products[] = $category;
      }

    } else {
      $products = $this->categoriesTree;
    }

    return $products;
  }

  function getProductSubcategories($productCode = false) {
    foreach ($this->categoriesTree as $category) {
      if ($category['Code'] == $productCode) return $category['Subcategories'];
    }

    return false;
  }

  function getAllProducts() {
    $products = $this->table('products')->getAllWhere('IsActive = 1');

    return $products;
  }
  

  // function getProductImages($productId) {
  //   return $this->table('products-images')->getAllWhere('ProductId = ?i', $productId);
  // }

  function getDirectionGoods($directionCode, $count = 0, $offset = 0) {
    $categories = $this->getProducts($directionCode);
    $subcategoriesIds = [];
    foreach ($categories as $category) {
      if ($category['Subcategories'] && count($category['Subcategories'])) {
        foreach ($category['Subcategories'] as $subcategory) {
          $subcategoriesIds[] = $subcategory['Id'];  
        }
      }
    }
    $goods = $this->db->getAll("SELECT g.*, sc.Code AS SubcategoryCode, p.Code AS ProductCode FROM ?n g LEFT JOIN ?n sc ON g.SubcategoryId = sc.Id LEFT JOIN ?n p ON sc.ProductId = p.Id WHERE g.SubcategoryId IN (?a) ORDER BY IF(g.`Order`, -1000/g.`Order`, 0) LIMIT ?i OFFSET ?i", 
        $this->tables['goods'],
        $this->tables['subcategories'],
        $this->tables['products'],
        $subcategoriesIds,
        $count,
        $offset
      );

    return $goods;
  }

  function getDirectionGoodsCount($directionCode) {
    $categories = $this->getProducts($directionCode);
    $subcategoriesIds = [];
    foreach ($categories as $category) {
      if ($category['Subcategories'] && count($category['Subcategories'])) {
        foreach ($category['Subcategories'] as $subcategory) {
          $subcategoriesIds[] = $subcategory['Id'];  
        }
      }
    }
    $count = count($subcategoriesIds) ? $this->db->getOne("SELECT COUNT(Id) FROM ?n WHERE SubcategoryId IN (?a) ORDER BY IF(`Order`, -1000/`Order`, 0)", 
        $this->tables['goods'],
        $subcategoriesIds
      ) : 0;

    return $count;
  }

  function getCategoryGoods($categoryCode, $count = 0, $offset = 0) {
    $category = $this->getProductByCode($categoryCode);
    $subcategoriesIds = [];
    if ($this->categoriesTree[$category['Id']]['Subcategories'] && count($this->categoriesTree[$category['Id']]['Subcategories'])) {
      foreach ($this->categoriesTree[$category['Id']]['Subcategories'] as $subcategory) {
        $subcategoriesIds[] = $subcategory['Id'];  
      }
    }
    $goods = $this->db->getAll("SELECT g.*, sc.Code AS SubcategoryCode, p.Code AS ProductCode FROM ?n g LEFT JOIN ?n sc ON g.SubcategoryId = sc.Id LEFT JOIN ?n p ON sc.ProductId = p.Id WHERE g.SubcategoryId IN (?a) ORDER BY IF(g.`Order`, -1000/g.`Order`, 0) LIMIT ?i OFFSET ?i", 
        $this->tables['goods'],
        $this->tables['subcategories'],
        $this->tables['products'],
        $subcategoriesIds,
        $count,
        $offset
      );

    return $goods;
  }

  function getCategoryGoodsCount($categoryCode) {
    $category = $this->getProductByCode($categoryCode);
    $subcategoriesIds = [];
    if ($this->categoriesTree[$category['Id']]['Subcategories'] && count($this->categoriesTree[$category['Id']]['Subcategories'])) {
      foreach ($this->categoriesTree[$category['Id']]['Subcategories'] as $subcategory) {
        $subcategoriesIds[] = $subcategory['Id'];  
      }
    }
    $count = count($subcategoriesIds) ? $this->db->getOne("SELECT COUNT(Id) FROM ?n WHERE SubcategoryId IN (?a) ORDER BY IF(`Order`, -1000/`Order`, 0)", 
        $this->tables['goods'],
        $subcategoriesIds
      ) : 0;

    return $count;
  }

  function getSubCategories($productId) {
    if (gettype($productId) === 'array')
      return $this->table('subcategories')->getAllWhere('ProductId IN (?a) AND IsActive = 1', $productId);
    else
      return $this->table('subcategories')->getAllWhere('ProductId = ?i AND IsActive = 1', $productId);
  }

  function getSimilarProduction($product) {
    $products = $this->table('products')->getAllWhere('Id <> ?i AND IsActive = 1', $product['Id']);

    return $products;
  }

  function getOtherCategories($product) {
    $products = $this->table('products')->getAllWhere('Id <> ?i AND IsActive = 1', $product['Id']);

    return $products;
  }

  function getSubcategoryByCode($code) {
    return $this->table('subcategories')->getOneWhere('Code = ?s', $code);
  }

  function getSubCategoryGoods($subcategoryId) {
    return $this->table('goods')->getAllWhereSorted('SubcategoryId = ?i', $subcategoryId);
  }

  function getGoodyByCode($code) {
    return $this->table('goods')->getOneWhere('Code = ?s', $code);
  }

  function getGoodyChars($goodyId) {
    return $this->table('goods_chars')->getAllWhereSorted('GoodyId = ?i', $goodyId);
  }

  function getOtherGoods($goodyCode, $count) {
    $goods = $this->db->getAll("SELECT g.*, sc.Code AS SubcategoryCode, p.Code AS ProductCode FROM ?n g LEFT JOIN ?n sc ON g.SubcategoryId = sc.Id LEFT JOIN ?n p ON sc.ProductId = p.Id WHERE g.Code <> ?s ORDER BY IF(g.`Order`, -1000/g.`Order`, 0) LIMIT ?i", 
        $this->tables['goods'],
        $this->tables['subcategories'],
        $this->tables['products'],
        $goodyCode,
        $count
      );

    return $goods;
  }

}

?>
