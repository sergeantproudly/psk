<?php
namespace Site\Models;
use Engine\Library\Model;
class ArticleModel extends Model {
  
  function getArticles($count = 0, $offset = 0) {
    //$data = $this->table('articles')->getAll($count, $offset);
    if ($count > 0) {
      $data = $this->db->getAll('SELECT * FROM ?n ORDER BY PublishDate DESC LIMIT ?i OFFSET ?i', $this->tables['articles'], $count, $offset);  
    } else {
      $data = $this->db->getAll('SELECT * FROM ?n ORDER BY PublishDate DESC', $this->tables['articles']);
    }

    return $data;
  }

  function getSimilarArticles($article, $count = 0, $offset = 0) {
    if ($count > 0) {
      $data = $this->db->getAll('SELECT * FROM ?n WHERE Id <> ?i ORDER BY PublishDate DESC LIMIT ?i OFFSET ?i', $this->tables['articles'], $article['Id'], $count, $offset);  
    } else {
      $data = $this->db->getAll('SELECT * FROM ?n WHERE Id <> ?i ORDER BY PublishDate DESC', $this->tables['articles'], $article['Id']);
    }

    return $data;
  }

  function getCountByTag($tagCode) {
    $tag = $this->getTag($tagCode);

    return $this->db->getOne('SELECT COUNT(Id) AS ArticlesNumber FROM ?n WHERE TagId = ?i', $this->tables['articles'], $tag['Id']);
  }

  function getCountArticles() {
    return parent::getCount($this->tables['articles']);
  }

  function getArticleByCode($code) {
    $data = $this->table('articles')->getOneWhere('Code = ?s', $code);
    //    $data['Tag'] = $this->getTagById($data['TagId']);
    // unset($data['TagId']);
    return $data;
  }

  function getTags() {
    return $this->table('tags')->getAll();
  }

  function getTag($tag) {
    return $this->table('tags')->getOneWhere('Code = ?s', $tag);
  }

  function getTagById($id) {
    return $this->table('tags')->getOneWhere('Id = ?i', $id);
  }

  function getArticlesCount() {
    return $this->db->getOne('SELECT COUNT(Id) AS ArticlesCount FROM ?n', $this->tables['articles']);
  }


  
}



?>
