<?php
namespace Site\Models;
use Engine\Library\Model;
use Engine\Utility\Morphology;

class ProjectModel extends Model {
  function getProjectByCode($code) {
    return $this->table('projects')->getOneWhere('Code = ?s', $code);
  }

  function getProjectById($id) {
    return $this->table('projects')->getOneWhere('Id = ?', $id);
  }

  function getProjects($count = 0, $offset = 0) {
    $displayedCount = 3;
    $projects = $this->table('projects')->getAllSorted(false, false, $count, $offset);

    foreach ($projects as &$project) {
      $project['Equipment'] = $this->getProjectEquipment($project['Id']);
      if (count($project['Equipment']) > $displayedCount) {
        $more = count($project['Equipment']) - $displayedCount;
        $project['More'] = '... и еще ' . $more . ' ' . Morphology::numeral($more,
            ['наименование', 'наименования', 'наименований']);

        $project['Equipment'] = array_slice($project['Equipment'], 0, 3);
      }
      else {
        $project['More'] = '';
      }
    }

    unset($project);
    return $projects;
  }

  function getProjectImages($projectId) {
    $gallery = $this->getProjectGallery($projectId);
    // var_dump($gallery);

    return $this->table('images')->getAllWhere('Id IN (?a)', $gallery);
  }

  function getProjectEquipment($projectId) {
    return $this->table('project-equipment')->getAllWhere('ProjectId = ?i', $projectId);
  }

  function getProjectGallery($projectId) {
    return $this->table('gallery')->getColWhere('ProjectId = ?i', $projectId);
  }

  function getProjectReview($id) {
    return $this->table('reviews')->getOneWhere('Id = ?i', $id);
  }

  // function getAllReviews(Type $var = null) {

  // }

}

?>
