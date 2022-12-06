<?php
namespace Site\Models;
use Engine\Library\Model;
use Engine\Utility\Morphology;

class ProductionModel extends Model {
  function getProductByCode($code) {
    return $this->table('products')->getOneWhere('Code = ?s', $code);
  }

  function getProductById($id) {
    return $this->table('products')->getOneWhere('Id = ?', $id);
  }

  function getProducts() {
    //$products = $this->table('products')->getAllSorted(false, false, $count, $offset);
    $products = $this->table('products')->getAllWhereSorted('Id <> ?i', 9);

    return $products;
  }

  function getProductImages($productId) {
    return $this->table('products-images')->getAllWhere('ProductId = ?i', $productId);
  }

  function getSubCategories($productId) {
    return $this->table('subcategories')->getAllWhere('ProductId = ?i', $productId);
  }

  function getSimilarProduction($product) {
    $products = $this->table('products')->getAllWhere('Id <> ?i', $product['Id']);

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

}

?>
