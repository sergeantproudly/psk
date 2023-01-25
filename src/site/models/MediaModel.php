<?php

namespace Site\Models;

use Engine\Library\Model;
use Engine\Library\SafeMySQL;

class MediaModel extends Model {

  public function __construct(SafeMySQL $db) {
    parent::__construct($db);
  }

  public function getGalleries() {
    $items = $this->db->getAll("SELECT g.Title, g.Date, g.Image656_400 AS Image, g.Code, g.IsVideo, (SELECT COUNT(p.Id) FROM {$this->tables['media-photo']} p WHERE p.GalleryId = g.Id) + (SELECT COUNT(v.Id) FROM {$this->tables['media-video']} v WHERE v.GalleryId = g.Id) AS FilesQuantity FROM {$this->tables['media']} g ORDER BY IF(g.`Order`,-1000/g.`Order`,0) ASC, g.Date DESC");

    return $items;
  }

  public function getGalleryByCode($galleryCode) {
    return $this->db->getRow("SELECT g.* FROM {$this->tables['media']} g WHERE g.Code = ?s", $galleryCode);
  }

  public function getGalleryPhotos($galleryCode) {
    $items = $this->db->getAll("SELECT p.Title, p.Image1600_1000 AS ImageFull, p.Image656_400 AS Image FROM {$this->tables['media-photo']} AS p LEFT JOIN {$this->tables['media']} AS g ON p.GalleryId = g.Id WHERE g.Code = ?s ORDER BY IF(p.`Order`,-1000/p.`Order`,0) ASC", $galleryCode);

    return $items;
  }

  public function getGalleryVideos($galleryCode) {
    $items = $this->db->getAll("SELECT v.Title, v.Cover656_400 AS Cover, v.Code FROM {$this->tables['media-video']} AS v LEFT JOIN {$this->tables['media']} AS g ON v.GalleryId = g.Id WHERE g.Code = ?s ORDER BY IF(v.`Order`,-1000/v.`Order`,0) ASC", $galleryCode);

    return $items;
  }
}
