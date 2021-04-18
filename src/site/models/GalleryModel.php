<?php
namespace Site\Models;
use Engine\Library\Model;

class GalleryModel extends Model {
  protected $tables = [
    'primary' => 'component_gallery',
    'images' => 'data_images'
  ];

  function getImages($projectId) {
    $ids = $this->db->getCol("SELECT `ImageId` FROM ?n WHERE `ProjectId` = ?i", $this->tables['primary'], $projectId);

    $data = $this->db->getAll("SELECT * FROM ?n WHERE `Id` IN (?a)", $this->tables['images'], $ids);
    
    return $data;
  }

}

?>