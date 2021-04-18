<?php 
namespace Site\Models;
use Engine\Library\Model;
use Engine\Library\Common;

class ServiceModel extends Model {
  /**
   * Возвращает все услуги
   *
   * @param int $count - количество загружаемых услуг
   * @return array
   */
  function getServices($count = 0) {
    return $this->table('services')->getAll($count);
  }

  function getReview($id) {
    return $this->table('service-review')->getOneWhere('Id = ?i', $id);
  }

  function getServicesByCategory($id) {
    return $this->table('services')->getAllWhere('CategoryId = ?i', $id);
  }

  public function getCategories() {
    return $this->table('category')->getAll(); 
  }

  public function getCategoriesWithServices() {
		$serviceList = $this->getServices();
		
		$categoryList = $this->getCategories();
		
		foreach ($categoryList as $key => $category) {
			$category['ServiceList'] = array_filter($serviceList, function ($service) use ($category) {
				return $service['CategoryId'] == $category['Id'];
      });
      // TODO: Исправить добавление ссылок на услуги - код страницы услуг "угадывается"
      $category['ServiceList'] =  Common::setLinks($category['ServiceList'], 'services');
      
      $categoryList[$key] = $category;
    }
    
    return $categoryList;
  }

  function getServiceByCode($code) {
    return $this->table('services')->getOneWhere('Code = ?s', $code);
  }

  function getServiceById($id) {
    return $this->table('services')->getOneWhere('Id = ?i', $id);
  }

  function getGallery($serviceId) {
    // return $this->table('service-image')->getAll();
    return $this->table('service-image')->getAllWhere('ServiceId = ?i', $serviceId);
  }

}


?>
