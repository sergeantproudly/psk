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

  function getProducts($count = 0, $offset = 0) {
    $products = $this->table('products')->getAllSorted(false, false, $count, $offset);

    return $products;
  }

  function getProductImages($productId) {
    return $this->table('products-images')->getAllWhere('ProductId = ?i', $productId);
  }

  function getSimilarProduction($product) {
    $products = $this->table('products')->getAllWhere('Id <> ?i', $product['Id']);

    return $products;
  }

}

?>
