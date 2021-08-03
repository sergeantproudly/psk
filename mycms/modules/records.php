<?php

krnLoadLib('define');
krnLoadLib('settings');
krnLoadLib('images');
krnLoadLib('files');
krnLoadLib('common');

define('DOCUMENT_CATALOG_ID', 40);

class records extends krn_abstract {

  function __construct() {
    parent::__construct();

    $this->mode = $_GET['mode'] ? $_GET['mode'] : 'Browse';
    if ($_GET['document_id']) {
      $query = 'SELECT t1.Id, t1.Title, t1.TableId, t1.ParentId, t1.ParentField, t1.LeadElement, t1.ActionCode, t1.OrderElement, t1.OrderDirection, '
        . 't2.Name AS `Table`, '
        . 't3.Title AS `ParentTitle`, t3.LeadElement AS ParentLeadElement, t3.OrderElement AS ParentOrderElement, t3.OrderDirection AS ParentOrderDirection,'
        . 't4.Name AS `ParentTable` '
        . 'FROM mycms_documents AS t1 '
        . 'LEFT JOIN mycms_tables AS t2 ON t1.TableId=t2.Id '
        . 'LEFT JOIN mycms_documents AS t3 ON t1.ParentId=t3.Id '
        . 'LEFT JOIN mycms_tables AS t4 ON t3.TableId=t4.Id '
        . 'WHERE t1.Id=' . (int)$_GET['document_id'] . ' '
        . 'ORDER BY IF(t1.`Order`,-1000/t1.`Order`,0) ASC';
      $this->document = dbGetRecordFromDb($query, __FILE__, __LINE__);
    }
    if (isset($_GET['filter_id'])) {
      if ($_GET['filter_id']) {
        $_SESSION['Filter'] = $_GET['filter_id'];
        $_SESSION['FilterDocument'] = $this->document['Id'];
      } else {
        unset($_SESSION['Filter']);
        unset($_SESSION['FilterDocument']);
      }
    }
    if ($_GET['orderby']) {
      $this->order['Field'] = $_GET['orderby'];
      $this->order['Direction'] = $_GET['orderdir'];
    }
  }

  function GetResult() {
    if ($this->mode == 'Browse' && !$this->document) $this->mode = 'BrowseDocuments';
    $result = $this->{$this->mode}();

    if ($this->mode == 'BrowseDocuments') {
      $main = krnLoadModuleByName('main');
      $result = $main->GetStatistics() . $result;
    }

    return $result;
  }

  function BrowseDocuments() {
    $table = new BrowserTable();
    $table->AddSimpleHeaderRow(array('Название'));
    $res = dbDoQuery('SELECT Id, Title FROM mycms_documents WHERE ParentId=0 ORDER BY IF(`Order`,-1000/`Order`,0) ASC', __FILE__, __LINE__);
    while ($rec = dbGetRecord($res)) {
      $row = array(
        '<a href="index.php?module=records&document_id=' . $rec['Id'] . '">' . htmlspecialchars($rec['Title'], ENT_QUOTES) . '</a>'
      );
      $table->AddSimpleBodyRow($row);
    }

    $result = LoadTemplate('base_browser');
    $result = strtr($result, array(
      '<%DISPLAYTOP%>' => '',
      '<%DOCUMENT_TITLE%>' => 'Управление сайтом',
      '<%BREAD_CRUMBS%>' => '',
      '<%TOOLS_RIGHT_TOP%>' => '',
      '<%FILTER%>' => '',
      '<%ACTION%>' => 'index.php#',
      '<%TABLE%>' => $table->GetTable(),
      '<%TOOLS_LEFT_BOTTOM%>' => '',
      '<%TOOLS_RIGHT_BOTTOM%>' => ''
    ));
    $result = strtr($result, array(
      'div class="clear-fix top-buttons"' => 'div class="clear-fix top-buttons" style="display: none;"',
      'div class="clear-fix bottom-buttons"' => 'div class="clear-fix top-buttons" style="display: none;"',
      'div class="clear-fix2"' => 'div class="clear-fix2" style="display: none;"'
    ));
    return $result;
  }

  function Browse($document = array(), $params = array()) {
    global $Settings;
    if (!count($document)) $document = $this->document;
    $fieldsTypes = Array();
    $thItems = Array();
    $fields = GetFieldsList($document, true);
    $fieldsIds = GetSubArray($fields, 'Id');
    $properties = $Settings->GetElementsSettings($fieldsIds);
    foreach ($fields as $name => $info) {
      $sortClass = ($name == $this->order['Field']
        ? ' curr' . ($this->order['Direction'] ? ' desc' : '')
        : '');
      $recSortByParams = "'{$name}', {($name == $this->order['Field'] &&!$this->order['Direction']? '1': '0')}, {$document['Id']}";

      $thItems[] = "
			<div class='sortby${sortClass}' onclick='recSortBy({$recSortByParams});'>		{$info['Title']}
			</div>";
    }
    $table = new BrowserTable($thItems);

    $page = (int)$_GET['page'] ? (int)$_GET['page'] : 1;
    $rowsOnPage = $_SESSION['Cms']['RowsOnPage'] ? $_SESSION['Cms']['RowsOnPage'] : $_SESSION['Cms']['RowsOnPage'] = $Settings->GetCmsSetting(3, 15);
    $pagesInGroup = $_SESSION['Cms']['PagesInGroup'] ? $_SESSION['Cms']['PagesInGroup'] : $_SESSION['Cms']['PagesInGroup'] = $Settings->GetCmsSetting(4, 15);

    if ($document['ParentId'] && $_SESSION['Filter']) {
      $countTotal = dbGetValueFromDb('SELECT COUNT(Id) FROM `' . $document['Table'] . '` WHERE ' . $document['ParentField'] . '=' . $_SESSION['Filter'], __FILE__, __LINE__);
    } elseif ($params['parent_record_field'] && $params['parent_record_id']) {
      $countTotal = dbGetValueFromDb('SELECT COUNT(Id) FROM `' . $document['Table'] . '` WHERE ' . $params['parent_record_field'] . '=' . $params['parent_record_id'], __FILE__, __LINE__);
    } else {
      $countTotal = dbGetValueFromDb('SELECT COUNT(Id) FROM `' . $document['Table'] . '`', __FILE__, __LINE__);
    }
    $navMn = GetNavigationMn($countTotal, $rowsOnPage, $pagesInGroup);

    /*
		if($document['ParentId'] && $_SESSION['Filter']){
			$query = 'SELECT Id, `'.implode('`, `',array_keys($fields)).'` '
				.'FROM `'.$document['Table'].'` WHERE '.$document['ParentField'].'='.$_SESSION['Filter'].' '
				.(($this->order['Field']||$this->order['Direction'])?(($this->order['Field']?'ORDER BY `'.$this->order['Field'].'`':'').($this->order['Field']?($this->order['Direction']?' DESC':' ASC'):'')):('ORDER BY '.$document['OrderElement'].' '.$document['OrderDirection']));

		}else*/
    if ($params['parent_record_field'] && $params['parent_record_id']) {
      $query = 'SELECT Id, `' . implode('`, `', array_keys($fields)) . '` '
        . 'FROM `' . $document['Table'] . '` WHERE ' . $params['parent_record_field'] . '=' . $params['parent_record_id'] . ' ';
    } else {
      $query = 'SELECT Id, `' . implode('`, `', array_keys($fields)) . '` '
        . 'FROM `' . $document['Table'] . '` ';
    }

    if (isset($this->order['Field'])) {
      $query .= 'ORDER BY ' . $this->order['Field'];
      $query .= $this->order['Direction'] ? ' DESC' : ' ASC';
    } elseif ((bool)$document['OrderElement']) {
      $query .= "ORDER BY {$document['OrderElement']}";
      $query .= $document['OrderDirection'] ? ' DESC' : ' ASC';
    }

    $res = dbDoQueryLimit($query, ($page - 1) * $rowsOnPage, $rowsOnPage, __FILE__, __LINE__);
    while ($rec = dbGetRecord($res)) {
      $tdItems = Array();
      foreach ($fields as $name => $info) {
        $elementProperties = $properties[$info['Id']];
        switch ($info['Type']) {
          case 2:
            if ($maxLength = $elementProperties[21]) {
              $tdItems[] = nl2br(htmlspecialchars(TrimText($rec[$name], $maxLength), ENT_QUOTES));
            } else {
              $tdItems[] = nl2br(htmlspecialchars($rec[$name], ENT_QUOTES));
            }

            break;
          case 3:
            $dateType = $elementProperties[30];
            $tdItems[] = ModifiedDateTime($rec[$name], $dateType);
            break;
          case 4:
            if ($rec[$name]) {
              $tdItems[] = '<a href="' . $rec[$name] . '" target="_blank"><img src="' . $rec[$name] . '" alt="" class="thumb"/></a>';
            } else {
              $tdItems[] = '';
            }
            break;
          case 5:
            if ($rec[$name]) {
              $fileInfo = flGetInfo($rec[$name]);
              $tdItems[] = '<a href="' . $rec[$name] . '">' . $fileInfo['basename'] . '</a>';
            } else {
              $tdItems[] = '';
            }
            break;
          case 6:
            $tdItems[] = $rec[$name] ? 'да' : 'нет';
            break;
          case 8:
            preg_match_all('/([A-z0-9]+)/', $elementProperties[81], $matchFields);
            $titleArr = dbGetRecordFromDb('SELECT `' . implode('`, `', $matchFields[0]) . '` FROM `' . $elementProperties[80] . '` WHERE `' . $elementProperties[82] . '`="' . $rec[$name] . '"', __FILE__, __LINE__);

            $value = is_array($titleArr) ? strtr($elementProperties[81], $titleArr) : '';

            // NOTE: Updated by Vitaly Krenel (3 July, 3:15 MSK)

            if ($document['LeadElement'] == $name
              || $name == 'Title' || $name == 'Name') {
              $tdItems[] = '<a href="index.php?module=records&mode=view&document_id=' . $document['Id'] . '&record_id=' . $rec['Id'] . '">' . htmlspecialchars($value, ENT_QUOTES) . '</a>';
            } else {
              $tdItems[] = htmlspecialchars($value, ENT_QUOTES);
            }
            break;
          default:
            if ($document['LeadElement'] == $name || $name == 'Title' || $name == 'Name') {
              $tdItems[] = '<a href="index.php?module=records&mode=view&document_id=' . $document['Id'] . '&record_id=' . $rec['Id'] . '">' . htmlspecialchars($rec[$name], ENT_QUOTES) . '</a>';
            } else {
              $tdItems[] = htmlspecialchars($rec[$name], ENT_QUOTES);
            }
            break;
        }
      }
      /*
			if($document['ParentId'] && $_SESSION['Filter']){
				$actionEdit='Popup(\'record_edit\',600,{\'id\':'.$rec['Id'].',\'document_id\':'.$document['Id'].',\'filter_id\':'.$_SESSION['Filter'].'});';

			}else*/
      if ($params['parent_record_field'] && $params['parent_record_id']) {
        $actionEdit = 'Popup(\'record_edit\',600,{id:' . $rec['Id'] . ', document_id:' . $document['Id'] . ', params:{parent_record_id: ' . $params['parent_record_id'] . ', parent_record_field: \'' . $params['parent_record_field'] . '\'}});';
      } else {
        $actionEdit = 'Popup(\'record_edit\',600,{id:' . $rec['Id'] . ', document_id:' . $document['Id'] . '});';
      }
      $actionDelete = 'recDeleteConfirm(this,' . $document['Id'] . ',' . $rec['Id'] . ');';

      if ($this->document['Id'] == DOCUMENT_CATALOG_ID) {
        $class = '';
        if ($rec['FastShipping']) $class = 'fastshipping';
        if ($rec['MinimumBalance']) $class .= ' minimumbalance';
        $table->AddBodyRow($rec['Id'], $actionEdit, $actionDelete, $tdItems, ['Class' => $class]);
      } else {
        $table->AddBodyRow($rec['Id'], $actionEdit, $actionDelete, $tdItems);
      }
    }

    $result = LoadTemplate('base_browser');
    /*
		if($document['ParentId'] && $_SESSION['Filter']){
			$toolAdd=strtr(LoadTemplate('tool_add'),array(
				'<%ACTION%>'	=> 'recAdd(this,'.$document['Id'].', {parent_record_id: '.$_SESSION['Filter'].'});'
			));

		}else*/
    if ($params['parent_record_field'] && $params['parent_record_id']) {
      $toolAdd = strtr(LoadTemplate('tool_add'), array(
        '<%ACTION%>' => 'recAdd(this,' . $document['Id'] . ', {parent_record_id: ' . $params['parent_record_id'] . ', parent_record_field: \'' . $params['parent_record_field'] . '\'});'
      ));
      $toolEdit = GetButton('Изменить', false, 'recEdit(this,' . $document['Id'] . ', {parent_record_id: ' . $params['parent_record_id'] . ', parent_record_field: \'' . $params['parent_record_field'] . '\'});');

    } else {
      $toolAdd = strtr(LoadTemplate('tool_add'), array(
        '<%ACTION%>' => 'recAdd(this,' . $document['Id'] . ');'
      ));
      $toolEdit = GetButton('Изменить', false, 'recEdit(this,' . $document['Id'] . ');');
    }
    $toolRop = strtr(LoadTemplate('tool_rop'), array(
      '<%ROWS_ON_PAGE%>' => $rowsOnPage
    ));

    // filters
    /*
		if($document['ParentId']){

			$defaultTitle='';
			$defaultValue='';
			$options='<span class="item'.($_SESSION['Filter']?'':' current').'" value="0" onclick="redirect(\'index.php?module=records&document_id='.$document['Id'].'&filter_id=0\');">All</span>';
			$res=dbDoQuery('SELECT Id, '.($document['ParentLeadElement']?$document['ParentLeadElement']:'Title').' AS Title FROM '.$document['ParentTable'].' ORDER BY '.($document['ParentLeadElement']?$document['ParentLeadElement']:'IF(`Order`,-1000/`Order`,0)').' ASC',__FILE__,__LINE__);


			while($rec=dbGetRecord($res)){
				if($rec['Id']==$_SESSION['Filter'] && $document['Id']==$_SESSION['FilterDocument']){
					$defaultValue=$rec['Id'];
					$defaultTitle=$rec['Title'];
					$options.='<span class="item current" value="'.$rec['Id'].'">'.$rec['Title'].'</span>';
				}else{
					$options.='<span class="item" value="'.$rec['Id'].'" onclick="redirect(\'index.php?module=records&document_id='.$document['Id'].'&filter_id='.$rec['Id'].'\');">'.$rec['Title'].'</span>';
				}
			}
			$inp=strtr(LoadTemplate('inp_select'),array(
				'<%IDNUM%>'			=> ' id="'.$name.$i.'"',
				'<%NAME%>'			=> $name.'['.$i.']',
				'<%DEFAULT_TITLE%>'	=> $defaultTitle,
				'<%DEFAULT_VALUE%>'	=> $defaultValue,
				'<%OPTIONS%>'		=> $options,
				'<%ATTRIBUTES%>'	=> $attributes
			));

			$filter='<div class="filter" style="padding: 10px 0 20px 10px;"><span class="lbl">Фильтр:</span> '.$inp.'</div>';
		}
		*/

    /*
		if($document['ParentId'] && $_SESSION['Filter']){
			$breadCrumbs='<a href="index.php?module=records">Управление сайтом</a> &rarr; <a href="index.php?module=records&document_id='.$document['ParentId'].'">'.$document['ParentTitle'].'</a> &rarr; ';

		}else{
			$breadCrumbs='<a href="index.php?module=records">Управление сайтом</a> &rarr; ';

		}
		*/

    $breadCrumbs = '<a href="index.php?module=records">Управление сайтом</a> &rarr; ';

    $result = strtr($result, array(
      '<%DISPLAYTOP%>' => isset($params['hide_top']) ? ' style="display: none;"' : '',
      '<%DOCUMENT_TITLE%>' => $document['Title'],
      '<%BREAD_CRUMBS%>' => $breadCrumbs,
      '<%TOOLS_RIGHT_TOP%>' => $toolRop,
      '<%FILTER%>' => isset($params['hide_top']) ? '' : $filter,
      '<%ACTION%>' => 'index.php#',
      '<%TABLE%>' => $table->GetTable(),
      '<%TOOLS_LEFT_BOTTOM%>' => $toolEdit . ' ' . GetButton('Удалить', false, 'recDeleteConfirm(this,' . $document['Id'] . ');'),
      '<%TOOLS_RIGHT_BOTTOM%>' => $toolAdd
    ));
    return $result . $navMn;
  }

  function View() {
    global $Settings;
    $this->record['Id'] = (int)$_GET['record_id'];
    $formElement = LoadTemplate('viewer_data_row');
    $content = '';

    $fields = GetFieldsList($this->document);
    $fieldsIds = GetSubArray($fields, 'Id');
    $properties = $Settings->GetElementsSettings($fieldsIds);

    $record = dbGetRecordFromDb('SELECT * FROM `' . $this->document['Table'] . '` WHERE Id=' . $this->record['Id'], __FILE__, __LINE__);
    if ($this->document['LeadElement'] && isset($record[$this->document['LeadElement']])) {
      $this->record['Name'] = $record[$this->document['LeadElement']];
    } elseif (isset($record['Title'])) {
      $this->record['Name'] = $record['Title'];
    } else {
      $this->record['Name'] = '';
    }

    $formRows = '';
    $j = 0;
    $total = count($fields);
    foreach ($fields as $name => $info) {
      $j++;

      $elementProperties = $properties[$info['Id']];
      switch ($info['Type']) {
        case 2:
          $maxLength = $elementProperties[21] ? $elementProperties[21] : 500;
          if ($elementProperties[20] == 'wysiwyg') {
            $data = SpoilerText(htmlentities($record[$name], ENT_QUOTES, 'UTF-8'), $maxLength);
          } else {
            $data = nl2br(SpoilerText($record[$name], $maxLength));
          }
          break;
        case 3:
          $dateType = $elementProperties[30];
          $data = ModifiedDateTime($record[$name], $dateType);
          break;
        case 4:
          if ($resize = $elementProperties[42]) continue 2;
          $data = '<a href="' . $record[$name] . '" target="_blank"><img src="' . $record[$name] . '" alt="" style="max-height: 300px; max-width: 300px;"/></a>';
          break;
        case 5:
          $data = '<a href="' . $record[$name] . '">' . $record[$name] . '</a>';
          break;
        case 6:
          $data = $record[$name] ? 'да' : 'нет';
          break;
        case 7:
          $data = $record[$name];
          break;
        case 8:
          if ($record[$name]) {
            preg_match_all('/([A-z0-9]+)/', $elementProperties[81], $matchFields);
            $rec = dbGetRecordFromDb('SELECT `' . $elementProperties[82] . '`, `' . implode('`, `', $matchFields[0]) . '` FROM `' . $elementProperties[80] . '` WHERE Id=' . $record[$name], __FILE__, __LINE__);
            $data = strtr($elementProperties[81], $rec);
          } else {
            $data = '';
          }

          break;
        default:
          $data = $record[$name];
          break;
      }
      if ($elementProperties[6] || $info['OutputFunc']) {
        krnLoadLib('browse');
        $func = $info['OutputFunc'] ? $info['OutputFunc'] : ($elementProperties[6] ? $elementProperties[6] : false);
        $data = $func($data, $record);
      }
      $formRows .= strtr($formElement, array(
        '<%EVEN%>' => isEven($j) ? ' class="even"' : '',
        '<%LABEL%>' => $info['Title'],
        '<%VALUE%>' => $data
      ));
      if ($elementProperties[4]) {
        $formRows .= '<div class="comment">' . $elementProperties[4] . '</div>';
      }
    }

    $actionEdit = 'Popup(\'record_edit\',600,{id:' . $this->record['Id'] . ', document_id:' . $this->document['Id'] . '});';
    $actionDelete = 'recDeleteConfirm(this,' . $this->document['Id'] . ',' . $this->record['Id'] . ');';

    $result = LoadTemplate('base_viewer');
    $result = strtr($result, array(
      '<%DOCUMENT_TITLE%>' => $this->record['Name'],
      '<%BREAD_CRUMBS%>' => '<a href="index.php?module=records">Управление сайтом</a> &rarr; <a href="index.php?module=records&document_id=' . $this->document['Id'] . '">' . $this->document['Title'] . '</a> &rarr; ',
      '<%TOOLS_RIGHT_TOP%>' => '',
      '<%TITLE%>' => $this->record['Name'],
      '<%CONTENT%>' => $formRows,
      '<%ACTION_EDIT%>' => $actionEdit,
      '<%ACTION_DELETE%>' => $actionDelete,
      '<%CHILDS%>' => $this->BrowseRecordChilds()
    ));
    return $result;
  }

  function BrowseRecordChilds() {
    $query = 'SELECT t1.Id, t1.Title, t1.TableId, t1.ParentId, t1.ParentField, t1.LeadElement, t1.ActionCode, t1.OrderElement, t1.OrderDirection, '
      . 't2.Name AS `Table`, '
      . 't3.Title AS `ParentTitle`, t3.LeadElement AS ParentLeadElement, t3.OrderElement AS ParentOrderElement, t3.OrderDirection AS ParentOrderDirection,'
      . 't4.Name AS `ParentTable` '
      . 'FROM mycms_documents AS t1 '
      . 'LEFT JOIN mycms_tables AS t2 ON t1.TableId=t2.Id '
      . 'LEFT JOIN mycms_documents AS t3 ON t1.ParentId=t3.Id '
      . 'LEFT JOIN mycms_tables AS t4 ON t3.TableId=t4.Id '
      . 'WHERE t1.ParentId=' . $this->document['Id'] . ' '
      . 'ORDER BY IF(t1.`Order`,-1000/t1.`Order`,0) ASC';
    $res = dbDoQuery($query, __FILE__, __LINE__);
    if (!dbGetNumRows($res)) return '';
    $tabElement = LoadTemplate('viewer_tabs_el');
    $tabContent = '';
    $content = '';
    $fst = true;
    $params = array(
      'hide_top' => true,
      'parent_record_id' => $this->record['Id']
    );
    while ($rec = dbGetRecord($res)) {

      // tabs
      $tabContent .= strtr($tabElement, array(
        '<%ID%>' => $rec['Id'],
        '<%ACTIVE%>' => $fst ? ' active' : '',
        '<%TITLE%>' => $rec['Title']
      ));

      // contents
      $params['parent_record_field'] = $rec['ParentField'];
      $content .= '<div style="display: ' . ($fst ? 'block' : 'none') . ';" id="tab-content-' . $rec['Id'] . '">' . $this->Browse($rec, $params) . '</div>';

      $fst = false;
    }
    return '<ul class="tabs">' . $tabContent . '</ul><div class="tabs-content">' . $content . '</div>';
  }

  function Add() {
    global $Settings;
    $count = (int)$_GET['count'] ? (int)$_GET['count'] : 1;
    $parentRecordId = $_GET['parent_record_id'];
    $parentRecordField = $_GET['parent_record_field'];
    $element = LoadTemplate('record_add');
    $formElement = LoadTemplate('form_row');
    $content = '';

    $elementsCodes = GetElementCodes();
    $fields = GetFieldsList($this->document);
    $fieldsIds = GetSubArray($fields, 'Id');
    $properties = $Settings->GetElementsSettings($fieldsIds);
    for ($i = 1; $i <= $count; $i++) {
      $formRows = '';
      $j = 0;
      $total = count($fields);
      foreach ($fields as $name => $info) {
        $j++;
        $inp = LoadTemplate($elementsCodes[$info['Type']]['Input']);
        $elementProperties = $properties[$info['Id']];
        $attributes = $elementProperties[1] ? ' important="true"' : '';
        switch ($info['Type']) {
          case 1:
            if ($elementProperties[5]) $attributes .= ' syntax="' . $elementProperties[5] . '"';
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $i . '"',
              '<%NAME%>' => $name . '[' . $i . ']',
              '<%VALUE%>' => $elementProperties[3],
              '<%MAXLENGTH%>' => $elementProperties[10],
              '<%ATTRIBUTES%>' => $attributes
            ));
            break;
          case 2:
            if ($elementProperties[5]) $attributes .= ' syntax="' . $elementProperties[5] . '"';
            $size = ($elementProperties[22] || $elementProperties[23]) ? 'style="' . ($elementProperties[22] ? 'width:' . $elementProperties[22] . 'px;' : '') . ($elementProperties[23] ? 'height:' . $elementProperties[23] . 'px;' : '') . '"' : '';
            if ($elementProperties[20] == 'wysiwyg') {
              $inp = strtr(LoadTemplate('inp_wysiwyg'), array(
                '<%IDNUM%>' => ' id="' . $name . $i . '"',
                '<%NAME%>' => $name . '[' . $i . ']',
                '<%VALUE%>' => $elementProperties[3],
                '<%SIZE%>' => $size,
                '<%ATTRIBUTES%>' => $attributes
              ));

            } else {
              $inp = strtr($inp, array(
                '<%IDNUM%>' => ' id="' . $name . $i . '"',
                '<%NAME%>' => $name . '[' . $i . ']',
                '<%VALUE%>' => $elementProperties[3],
                '<%SIZE%>' => $size,
                '<%ATTRIBUTES%>' => $attributes
              ));
            }
            break;
          case 3:
            $dateType = $elementProperties[30];
            $now = date($dateType == 'datetime' ? 'd/m/Y H:i' : ($dateType == 'time' ? 'H:i' : 'd/m/Y'));
            $class = $dateType == 'datetime' ? '' : ($dateType == 'time' ? ' time-only' : ' date-only');
            $attributes .= ' syntax="date' . ($elementProperties[5] ? ',' . $elementProperties[5] : '') . '"';
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $i . '"',
              '<%CLASS%>' => $class,
              '<%NAME%>' => $name . '[' . $i . ']',
              '<%VALUE%>' => $elementProperties[3] ? $elementProperties[3] : $now,
              '<%ATTRIBUTES%>' => $attributes
            ));
            break;
          case 4:
            if ($resize = $elementProperties[42]) continue 2;
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $i . '"',
              '<%NAME%>' => $name . '[' . $i . ']',
              '<%CAPTION%>' => '',
              '<%VALUE%>' => '',
              '<%RECORDID%>' => $i,
              '<%ELEMENTID%>' => $info['Id'],
              '<%UPLOADERCODE%>' => 'Image',
              '<%UPLOADERINFO%>' => 'id:' . $i . ',element_id:' . $info['Id']
            ));
            break;
          case 5:
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $i . '"',
              '<%NAME%>' => $name . '[' . $i . ']',
              '<%CAPTION%>' => '',
              '<%VALUE%>' => '',
              '<%RECORDID%>' => $i,
              '<%ELEMENTID%>' => $info['Id'],
              '<%UPLOADERCODE%>' => 'File',
              '<%UPLOADERINFO%>' => 'id:' . $i . ',element_id:' . $info['Id']
            ));
            break;
          case 6:
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $i . '"',
              '<%NAME%>' => $name . '[' . $i . ']',
              '<%CB_CHECKED%>' => $elementProperties[3] ? ' cb-checked' : '',
              '<%CHECKED%>' => $elementProperties[3] ? ' checked="checked"' : '',
              '<%ATTRIBUTES%>' => $attributes
            ));
            break;
          case 7:
            $variants = explode('; ', $elementProperties[70]);
            $inpRow = $inp;
            $inp = '';
            foreach ($variants as $variant) {
              $inp .= ($inp ? '<br/>' : '') . strtr($inpRow, array(
                  '<%NAME%>' => $name . '[' . $i . ']',
                  '<%VALUE%>' => $variant,
                  '<%TITLE%>' => $variant,
                  '<%RB_CHECKED%>' => '',
                  '<%CHECKED%>' => ''
                ));
            }
            break;
          case 8:
            $defaultTitle = '';
            $defaultValue = '';
            $options = $elementProperties[1] ? '' : '<span class="item current" value="0">&nbsp;</span>';
            preg_match_all('/([A-z0-9]+)/', $elementProperties[81], $matchFields);
            if ($elementProperties[84]) {
              $res = dbDoQuery('SELECT `' . $elementProperties[82] . '`, `' . implode('`, `', $matchFields[0]) . '` FROM `' . $elementProperties[80] . '` ' . ($elementProperties[84] ? 'ORDER BY ' . $elementProperties[84] . ' ' . $elementProperties[85] : ''), __FILE__, __LINE__);
            } else {
              $res = dbDoQuery('SELECT `' . $elementProperties[82] . '`, `' . implode('`, `', $matchFields[0]) . '` FROM `' . $elementProperties[80] . '`', __FILE__, __LINE__);
            }
            while ($rec = dbGetRecord($res)) {
              if (!$defaultTitle && $elementProperties[1]) {
                $defaultTitle = strtr($elementProperties[81], $rec);
                $defaultValue = $rec[$elementProperties[82]];
                $options .= '<span class="item current" value="' . $rec[$elementProperties[82]] . '">' . strtr($elementProperties[81], $rec) . '</span>';
              } else {
                $options .= '<span class="item" value="' . $rec[$elementProperties[82]] . '">' . strtr($elementProperties[81], $rec) . '</span>';
              }
            }
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $i . '"',
              '<%NAME%>' => $name . '[' . $i . ']',
              '<%DEFAULT_TITLE%>' => $defaultTitle,
              '<%DEFAULT_VALUE%>' => $defaultValue,
              '<%OPTIONS%>' => $options,
              '<%ATTRIBUTES%>' => $attributes
            ));
            break;
          case 9:
            $defaultTitle = '';
            $defaultValue = '';
            $options = '';
            preg_match_all('/([A-z0-9]+)/', $elementProperties[91], $matchFields);
            if ($elementProperties[94]) {
              $res = dbDoQuery('SELECT `' . $elementProperties[92] . '`, `' . implode('`, `', $matchFields[0]) . '` FROM `' . $elementProperties[90] . '` ' . ($elementProperties[94] ? 'ORDER BY ' . $elementProperties[94] . ' ' . $elementProperties[95] : ''), __FILE__, __LINE__);
            } else {
              $res = dbDoQuery('SELECT `' . $elementProperties[92] . '`, `' . implode('`, `', $matchFields[0]) . '` FROM `' . $elementProperties[90] . '`', __FILE__, __LINE__);
            }
            while ($rec = dbGetRecord($res)) {
              if (!$defaultTitle && $elementProperties[1]) {
                $defaultTitle = strtr($elementProperties[91], $rec);
                $defaultValue = $rec[$elementProperties[92]];
                $options .= '<span class="item current" value="' . $rec[$elementProperties[92]] . '">' . strtr($elementProperties[91], $rec) . '</span>';
              } else {
                $options .= '<span class="item" value="' . $rec[$elementProperties[92]] . '">' . strtr($elementProperties[91], $rec) . '</span>';
              }
            }
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $elementProperties[96] . $i . '"',
              '<%NAME%>' => $elementProperties[96] . '[' . $i . ']',
              '<%DEFAULT_TITLE%>' => $defaultTitle,
              '<%DEFAULT_VALUE%>' => $defaultValue,
              '<%OPTIONS%>' => $options,
              '<%ATTRIBUTES%>' => $attributes
            ));
            break;
          default:
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $i . '"',
              '<%NAME%>' => $name . '[' . $i . ']',
              '<%VALUE%>' => '',
              '<%ATTRIBUTES%>' => $attributes
            ));
            break;
        }
        /*
				if($this->document['ParentId'] && $name==$this->document['ParentField'] && $_SESSION['Filter']){
					$formRows.='<input type="hidden" name="'.$this->document['ParentField'].'['.$i.']" value="'.$_SESSION['Filter'].'"/>';
				}else*/

        if ($name == $parentRecordField && $parentRecordId) {
          $formRows .= '<input type="hidden" name="' . $parentRecordField . '[' . $i . ']" value="' . $parentRecordId . '"/>';
          $formRows .= '<input type="hidden" name="parent_document_id" value="' . $this->document['ParentId'] . '"/>';
          $formRows .= '<input type="hidden" name="parent_record_id" value="' . $parentRecordId . '"/>';
          if ($j == 1) $formRows .= '<input type="hidden" name="id[' . $i . ']" value="' . $i . '"/>';

        } else {
          $formRows .= strtr($formElement, array(
            '<%LABEL%>' => $info['Title'],
            '<%ELEMENT%>' => $inp,
            '<%HIDDEN%>' => ($j == 1) ? '<input type="hidden" name="id[' . $i . ']" value="' . $i . '"/>' : ''
          ));
        }
        if ($elementProperties[4]) {
          $formRows .= '<div class="comment">' . $elementProperties[4] . '</div>';
        }
      }
      $content .= strtr($element, array(
        '<%EVEN%>' => isEven($i) ? ' form-even' : '',
        '<%NUM%>' => $i,
        '<%CONTENT%>' => $formRows
      ));
    }

    $result = LoadTemplate('base_editor');
    $result = strtr($result, array(
      '<%DOCUMENT_TITLE%>' => 'Создать запис' . ($count > 1 ? 'и' : 'ь'),
      '<%BREAD_CRUMBS%>' => '<a href="index.php?module=records">Управление сайтом</a> &rarr; <a href="index.php?module=records&document_id=' . $this->document['Id'] . '">' . $this->document['Title'] . '</a> &rarr; ',
      '<%CONTENT%>' => $content,
      '<%ACTION_FORM%>' => 'index.php?module=records&act=AddRecord&document_id=' . $this->document['Id'],
      '<%ACTION_CANCEL%>' => 'recAddCancel(' . $this->document['Id'] . ($parentRecordId ? (', {parent_document_id:' . $this->document['ParentId'] . ', parent_record_id:' . $parentRecordId . '}') : '') . ');',
      '<%ACTION_SUBMIT%>' => 'recAddSendForm(this);'
    ));
    return $result;
  }

  function AddRecord() {
    global $Settings;
    $fields = GetFieldsList($this->document);
    $fieldsIds = GetSubArray($fields, 'Id');
    $properties = $Settings->GetElementsSettings($fieldsIds);
    $ids = $_POST['id'];
    $javascript = '';
    $error = false;

    // many
    if (is_array($ids)) {
      foreach ($ids as $id) {
        $errorInner = false;
        foreach ($fields as $name => $info) {
          $elementProperties = $properties[$info['Id']];

          // required
          if ($elementProperties[1] && !$_POST[$name][$id]) {
            $errorInner = true;
            $javascript .= 'stxAddErrorStatus(gei(\'' . $name . $id . '\'),\'Field can&#39t be empty\');';
          }
          // unique
          if ($elementProperties[2] && dbGetValueFromDb('SELECT COUNT(Id) FROM `' . $this->document['Table'] . '` WHERE `' . $name . '`="' . $_POST[$name][$id] . '"', __FILE__, __LINE__)) {
            $errorInner = true;
            $javascript .= 'stxAddErrorStatus(gei(\'' . $name . $id . '\'),\'Уже есть запись с таким значением\');';
          }
          if (!$errorInner) {
            switch ($info['Type']) {
              case 1:
                if ($elementProperties[5] == 'password') {
                  $value = md5($_POST[$name][$id]);
                } else {
                  $value = str_replace('"', '\"', $_POST[$name][$id]);
                }
                break;
              case 3:
                $dateType = $elementProperties[30];
                $value = DatePrepareToDb(($dateType == 'time' ? '0000-00-00 ' : '') . $_POST[$name][$id]);
                break;
              case 4:
                if (!$elementProperties[42]) {
                  if ($sourcepath = $_POST[$name][$id]) {
                    $uploadpath = $elementProperties[40] ? $elementProperties[40] : UPLOADS_DIR;
                    if (mb_substr($uploadpath, mb_strlen($uploadpath) - 1) != '/') $uploadpath .= '/';
                    $info = flGetInfo($sourcepath);
                    //flMoveFile($sourcepath, ROOT_DIR . $uploadpath . $info['basename']);
                    //$_POST[$name][$id] = $value = $uploadpath . $info['basename'];
                    $value = str_replace(ROOT_DIR, '', flMoveFile($sourcepath, ROOT_DIR . $uploadpath . $info['basename']));
                    $_POST[$name][$id] = $value;
                  }
                } else {
                  $resize['source'] = $elementProperties[43] ? $elementProperties[43] : 'Image';
                  $sourceProperties = $properties[$fields[$resize['source']]['Id']];
                  if ($sourcepath = $_POST[$resize['source']][$id]) {
                    $resize['uploadpath'] = $elementProperties[40] ? $elementProperties[40] : ($sourceProperties[40] ? $sourceProperties[40] : UPLOADS_DIR);
                    $resize['type'] = strtolower($elementProperties[44]);
                    $resize['width'] = $elementProperties[45];
                    $resize['height'] = $elementProperties[46];
                    $resize['function'] = ($resize['type'] == 'fit' ? 'imgFit' : ($resize['type'] == 'thumbnail' ? 'imgThumbnail' : 'imgResize'));
                    if (function_exists($resize['function'])) $value = $resize['function'](ROOT_DIR . $sourcepath, $resize['width'], $resize['height'], ROOT_DIR . $resize['uploadpath']);
                    $info = flGetInfo($value);
                    if (mb_substr($resize['uploadpath'], mb_strlen($resize['uploadpath']) - 1) != '/') $resize['uploadpath'] .= '/';
                    $value = $resize['uploadpath'] . $info['basename'];
                  }
                }
                break;
              case 5:
                if ($sourcepath = $_POST[$name][$id]) {
                  $uploadpath = $elementProperties[50] ? $elementProperties[50] : UPLOADS_DIR;
                  if (mb_substr($uploadpath, mb_strlen($uploadpath) - 1) != '/') $uploadpath .= '/';
                  $info = flGetInfo($sourcepath);
                  flMoveFile($sourcepath, ROOT_DIR . $uploadpath . $info['basename']);
                  $_POST[$name][$id] = $value = $uploadpath . $info['basename'];
                }
                break;
              case 6:
                $value = $_POST[$name][$id] ? 1 : 0;
                break;
              case 9:
                $storageTable = $elementProperties[96];
                $storageSelfField = $elementProperties[97];
                $storageField = $elementProperties[98];
                $this->document['SystemAction'][] = 'SystemMultiSelect';
                $this->document['SystemParams'][] = array(
                  'storageTable' => $elementProperties[96],
                  'storageSelfField' => $elementProperties[97],
                  'storageField' => $elementProperties[98],
                  'values' => explode(',', $_POST[$storageTable][$id])
                );
                continue(2);
                break;
              default:
                $value = str_replace('"', '\"', $_POST[$name][$id]);
                break;
            }
            $newRecord[$name] = $value;
            $value = '';
          }
        }
        // before add action
        if ($this->document['ActionCode']) {
          $actionsModule = krnLoadModuleByName('actions');
          $actionsMethod = 'BeforeAdd' . $this->document['ActionCode'];
          if (method_exists($actionsModule, $actionsMethod)) {
            $r = $actionsModule->$actionsMethod($newRecord);
            if ($r !== true) {
              $errorInner = true;
              $javascript .= 'stxAddErrorStatus(domPN(domLC(gei(\'form' . $id . '\'))),\'<span style="padding-left:12px;">' . $r . '</span>\');';
            }
          }
        }
        if (!$errorInner) {
          unset($newRecord['Id']); // force fix
          $sqlDescription = $this->buildSql($newRecord);
          dbDoQuery('INSERT INTO `' . $this->document['Table'] . '` SET ' . $sqlDescription, __FILE__, __LINE__);
          $newRecord['Id'] = $oldRecord['Id'] = dbGetInsertedId();

          // system action
          if (isset($this->document['SystemAction'])) {
            foreach ($this->document['SystemAction'] as $k => $systemAction) {
              $this->document['SystemParams'][$k]['selfValue'] = $newRecord['Id'];
              $actionsModule->$systemAction($this->document['SystemParams'][$k]);
            }
          }

          // on add action
          if ($this->document['ActionCode']) {
            $actionsMethod = 'OnAdd' . $this->document['ActionCode'];
            if (method_exists($actionsModule, $actionsMethod)) {
              //$newRecord=GetSubArray(array_intersect_key($_POST,$fields),$id);
              $actionsModule->$actionsMethod($newRecord);
            }
          }
          $javascript .= 'domD(gei(\'form' . $id . '\'));';
        }
        if (!$error) $error = $errorInner;
      }
      //if(!$error)$javascript='redirect(\'index.php?module=records&document_id='.$this->document['Id'].'\');';
      if (!$error) {
        if ($_POST['parent_record_id']) {
          $javascript = 'redirect(\'index.php?module=records&mode=view&document_id=' . $_POST['parent_document_id'] . '&record_id=' . $_POST['parent_record_id'] . '#tab-' . $this->document['Id'] . '\');';
        } else {
          $javascript = 'redirect(\'index.php?module=records&document_id=' . $this->document['Id'] . '\');';
        }
      }

      // single
    } else {

      $sqlDescription = '';
      foreach ($fields as $name => $info) {
        $elementProperties = $properties[$info['Id']];

        // reuqired
        if ($elementProperties[1] && !$_POST[$name]) {
          $error = true;
          $javascript .= 'stxAddErrorStatus(domP(form.' . $name . '),\'Field can&#39t be empty\');';
        }
        // unique
        if ($elementProperties[2] && dbGetValueFromDb('SELECT COUNT(Id) FROM `' . $this->document['Table'] . '` WHERE `' . $name . '`="' . $_POST[$name] . '"', __FILE__, __LINE__)) {
          $error = true;
          $javascript .= 'stxAddErrorStatus(domP(form.' . $name . '),\'Уже есть запись с таким значением\');';
        }
        if (!$error) {
          switch ($info['Type']) {
            case 1:
              if ($elementProperties[5] == 'password') {
                $value = md5($_POST[$name]);
              } else {
                $value = str_replace('"', '\"', $_POST[$name]);
              }
              break;
            case 3:
              $dateType = $elementProperties[30];
              $value = DatePrepareToDb(($dateType == 'time' ? '0000-00-00 ' : '') . $_POST[$name]);
              break;
            case 4:
              if (!$elementProperties[42]) {
                if ($sourcepath = $_POST[$name]) {
                  $uploadpath = $elementProperties[40] ? $elementProperties[40] : UPLOADS_DIR;
                  if (mb_substr($uploadpath, mb_strlen($uploadpath) - 1) != '/') $uploadpath .= '/';
                  $info = flGetInfo($sourcepath);
                  $value = str_replace(ROOT_DIR, '', flMoveFile($sourcepath, ROOT_DIR . $uploadpath . $info['basename']));
                  $_POST[$name] = $value;
                }
              } else {
                $resize['source'] = $elementProperties[43] ? $elementProperties[43] : 'Image';
                $sourceProperties = $properties[$fields[$resize['source']]['Id']];
                if ($sourcepath = $_POST[$resize['source']]) {
                  $resize['uploadpath'] = $elementProperties[40] ? $elementProperties[40] : ($sourceProperties[40] ? $sourceProperties[40] : UPLOADS_DIR);
                  $resize['type'] = strtolower($elementProperties[44]);
                  $resize['width'] = $elementProperties[45];
                  $resize['height'] = $elementProperties[46];
                  $resize['function'] = ($resize['type'] == 'fit' ? 'imgFit' : ($resize['type'] == 'thumbnail' ? 'imgThumbnail' : 'imgResize'));
                  if (function_exists($resize['function'])) $value = $resize['function'](ROOT_DIR . $sourcepath, $resize['width'], $resize['height'], ROOT_DIR . $resize['uploadpath']);
                  $info = flGetInfo($value);
                  if (mb_substr($resize['uploadpath'], mb_strlen($resize['uploadpath']) - 1) != '/') $resize['uploadpath'] .= '/';
                  $value = $resize['uploadpath'] . $info['basename'];
                }
              }
              break;
            case 5:
              if ($sourcepath = $_POST[$name]) {
                $uploadpath = $elementProperties[50] ? $elementProperties[50] : UPLOADS_DIR;
                if (mb_substr($uploadpath, mb_strlen($uploadpath) - 1) != '/') $uploadpath .= '/';
                $info = flGetInfo($sourcepath);
                flMoveFile($sourcepath, ROOT_DIR . $uploadpath . $info['basename']);
                $_POST[$name] = $value = $uploadpath . $info['basename'];
              }
              break;
            case 6:
              $value = $_POST[$name] ? 1 : 0;
              break;
            case 9:
              $storageTable = $elementProperties[96];
              $storageSelfField = $elementProperties[97];
              $storageField = $elementProperties[98];
              $this->document['SystemAction'][] = 'SystemMultiSelect';
              $this->document['SystemParams'][] = array(
                'storageTable' => $elementProperties[96],
                'storageSelfField' => $elementProperties[97],
                'storageField' => $elementProperties[98],
                'values' => explode(',', $_POST[$storageTable])
              );
              continue(2);
              break;
            default:
              $value = str_replace('"', '\"', $_POST[$name]);
              break;
          }
          $sqlDescription .= ($sqlDescription ? ', ' : '') . '`' . $name . '`="' . $value . '"';
          $newRecord[$name] = $value;
          $value = '';
        }
      }
      // before add action
      if ($this->document['ActionCode']) {
        $actionsModule = krnLoadModuleByName('actions');
        $actionsMethod = 'BeforeAdd' . $this->document['ActionCode'];

        if (method_exists($actionsModule, $actionsMethod)) {
          $r = $actionsModule->$actionsMethod($newRecord);
          if ($r !== true) {
            $error = true;
            $javascript .= 'stxAddErrorStatus(domPN(domLC(gei(\'form\'))),\'<span style="padding-left:160px;">' . $r . '</span>\');';
          }
        }
      }
      if (!$error) {
        $sqlDescription = $this->buildSql($newRecord);
        dbDoQuery('INSERT INTO `' . $this->document['Table'] . '` SET ' . $sqlDescription, __FILE__, __LINE__);
        $newRecord['Id'] = dbGetInsertedId();

        // system action
        if (isset($this->document['SystemAction'])) {
          foreach ($this->document['SystemAction'] as $k => $systemAction) {
            $this->document['SystemParams'][$k]['selfValue'] = $newRecord['Id'];
            $actionsModule->$systemAction($this->document['SystemParams'][$k]);
          }
        }

        // on add action
        if ($this->document['ActionCode']) {
          $actionsMethod = 'OnAdd' . $this->document['ActionCode'];
          if (method_exists($actionsModule, $actionsMethod)) {
            //$newRecord=array_intersect_key($_POST,$fields);
            $actionsModule->$actionsMethod($newRecord);
          }
        }
        $javascript .= 'reload(true);';
      }

    }
    return $javascript;
  }

  function Edit() {
    global $Settings;
    $ids = $_POST['id'] ?? $_POST['record_id'];
    $parentRecordId = $_GET['parent_record_id'];
    $parentRecordField = $_GET['parent_record_field'];
    $element = LoadTemplate('record_edit');
    $formElement = LoadTemplate('form_row');
    $content = '';

    $elementsCodes = GetElementCodes();
    $fields = GetFieldsList($this->document);
    $fieldsIds = GetSubArray($fields, 'Id');
    $properties = $Settings->GetElementsSettings($fieldsIds);

    $i = 0;
    $sqlCondition = '';
    foreach ($ids as $id => $on) {
      $sqlCondition .= ($sqlCondition ? ' OR Id=' . $id : 'Id=' . $id);
    }
    $res = dbDoQuery('SELECT * FROM `' . $this->document['Table'] . '` WHERE ' . $sqlCondition, __FILE__, __LINE__);
    while ($rec = dbGetRecord($res)) {
      $i++;
      $formRows = '';
      $j = 0;
      $total = count($fields);
      foreach ($fields as $name => $info) {
        $j++;
        $inp = LoadTemplate($elementsCodes[$info['Type']]['Input']);
        $elementProperties = $properties[$info['Id']];
        $attributes = $elementProperties[1] ? ' important="true"' : '';
        switch ($info['Type']) {
          case 1:
            if ($elementProperties[5]) $attributes .= ' syntax="' . $elementProperties[5] . '"';
            if ($elementProperties[5] == 'password') $attributes .= ' placeholder="Complete to change password"';
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $rec['Id'] . '"',
              '<%NAME%>' => $name . '[' . $rec['Id'] . ']',
              '<%VALUE%>' => $elementProperties[5] != 'password' ? $rec[$name] : '',
              '<%MAXLENGTH%>' => $elementProperties[10],
              '<%ATTRIBUTES%>' => $attributes
            ));
            break;
          case 2:
            if ($elementProperties[5]) $attributes .= ' syntax="' . $elementProperties[5] . '"';
            $size = ($elementProperties[22] || $elementProperties[23]) ? 'style="' . ($elementProperties[22] ? 'width:' . $elementProperties[22] . 'px;' : '') . ($elementProperties[23] ? 'height:' . $elementProperties[23] . 'px;' : '') . '"' : '';
            if ($elementProperties[20] == 'wysiwyg') {
              $inp = strtr(LoadTemplate('inp_wysiwyg'), array(
                '<%IDNUM%>' => ' id="' . $name . $rec['Id'] . '"',
                '<%NAME%>' => $name . '[' . $rec['Id'] . ']',
                '<%VALUE%>' => $rec[$name],
                '<%SIZE%>' => $size,
                '<%ATTRIBUTES%>' => $attributes
              ));

            } else {
              $inp = strtr($inp, array(
                '<%IDNUM%>' => ' id="' . $name . $rec['Id'] . '"',
                '<%NAME%>' => $name . '[' . $rec['Id'] . ']',
                '<%VALUE%>' => $rec[$name],
                '<%SIZE%>' => $size,
                '<%ATTRIBUTES%>' => $attributes
              ));
            }
            break;
          case 3:
            $dateType = $elementProperties[30];
            $class = $dateType == 'datetime' ? '' : ($dateType == 'time' ? ' time-only' : ' date-only');
            $attributes .= ' syntax="date' . ($elementProperties[5] ? ',' . $elementProperties[5] : '') . '"';
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $rec['Id'] . '"',
              '<%CLASS%>' => $class,
              '<%NAME%>' => $name . '[' . $rec['Id'] . ']',
              '<%VALUE%>' => ModifiedDateTime($rec[$name], $dateType),
              '<%ATTRIBUTES%>' => $attributes
            ));
            break;
          case 4:
            if ($resize = $elementProperties[42]) continue 2;
            if ($rec[$name]) $fileInfo = flGetInfo($rec[$name]);
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $rec['Id'] . '"',
              '<%NAME%>' => $name . '[' . $rec['Id'] . ']',
              '<%CAPTION%>' => $rec[$name] ? $fileInfo['basename'] : '',
              '<%VALUE%>' => '',
              '<%RECORDID%>' => $rec['Id'],
              '<%ELEMENTID%>' => $info['Id'],
              '<%UPLOADERCODE%>' => 'Image',
              '<%UPLOADERINFO%>' => 'id:' . $rec['Id'] . ',element_id:' . $info['Id']
            ));
            break;
          case 5:
            if ($rec[$name]) $fileInfo = flGetInfo($rec[$name]);
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $rec['Id'] . '"',
              '<%NAME%>' => $name . '[' . $rec['Id'] . ']',
              '<%CAPTION%>' => $rec[$name] ? $fileInfo['basename'] : '',
              '<%VALUE%>' => '',
              '<%RECORDID%>' => $rec['Id'],
              '<%ELEMENTID%>' => $info['Id'],
              '<%UPLOADERCODE%>' => 'File',
              '<%UPLOADERINFO%>' => 'id:' . $rec['Id'] . ',element_id:' . $info['Id']
            ));
            break;
          case 6:
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $rec['Id'] . '"',
              '<%NAME%>' => $name . '[' . $rec['Id'] . ']',
              '<%CB_CHECKED%>' => $rec[$name] ? ' cb-checked' : '',
              '<%CHECKED%>' => $rec[$name] ? ' checked="checked"' : '',
              '<%ATTRIBUTES%>' => $attributes
            ));
            break;
          case 7:
            $variants = explode('; ', $elementProperties[70]);
            $inpRow = $inp;
            $inp = '';
            foreach ($variants as $variant) {
              $inp .= ($inp ? '<br/>' : '') . strtr($inpRow, array(
                  '<%NAME%>' => $name . '[' . $rec['Id'] . ']',
                  '<%VALUE%>' => $variant,
                  '<%TITLE%>' => $variant,
                  '<%RB_CHECKED%>' => $rec[$name] == $variant ? ' rb-checked' : '',
                  '<%CHECKED%>' => $rec[$name] == $variant ? ' checked="checked"' : ''
                ));
            }
            break;
          case 8:
            $defaultTitle = '';
            $defaultValue = '';
            $options = $elementProperties[1] ? '' : '<span class="item' . (!$record[$name] ? ' current' : '') . '" value="0">&nbsp;</span>';
            $sqlCondition = '';
            if ($elementProperties[83]) {
              $table = dbGetValueFromDb('SELECT t1.Name FROM mycms_tables AS t1 WHERE t1.Id=(SELECT t2.TableId FROM mycms_documents AS t2 WHERE t2.Id=' . $this->document['Id'] . ')', __FILE__, __LINE__);
              if ($this->document['Table'] == $elementProperties[80]) {
                $sqlCondition = ' WHERE Id<>' . $id;
              }
            }
            preg_match_all('/([A-z0-9]+)/', $elementProperties[81], $matchFields);
            if ($elementProperties[84]) {
              $resOpt = dbDoQuery('SELECT `' . $elementProperties[82] . '`, `' . implode('`, `', $matchFields[0]) . '` FROM `' . $elementProperties[80] . '`' . $sqlCondition . ' ' . ($elementProperties[84] ? 'ORDER BY ' . $elementProperties[84] . ' ' . $elementProperties[85] : ''), __FILE__, __LINE__);
            } else {
              $resOpt = dbDoQuery('SELECT `' . $elementProperties[82] . '`, `' . implode('`, `', $matchFields[0]) . '` FROM `' . $elementProperties[80] . '`' . $sqlCondition, __FILE__, __LINE__);
            }
            while ($opt = dbGetRecord($resOpt)) {
              if ($opt[$elementProperties[82]] == $rec[$name]) {
                $defaultTitle = strtr($elementProperties[81], $opt);
                $defaultValue = $opt[$elementProperties[82]];
                $options .= '<span class="item current" value="' . $opt[$elementProperties[82]] . '">' . strtr($elementProperties[81], $opt) . '</span>';
              } else {
                $options .= '<span class="item" value="' . $opt[$elementProperties[82]] . '">' . strtr($elementProperties[81], $opt) . '</span>';
              }
            }
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $rec['Id'] . '"',
              '<%NAME%>' => $name . '[' . $rec['Id'] . ']',
              '<%DEFAULT_TITLE%>' => $defaultTitle,
              '<%DEFAULT_VALUE%>' => $defaultValue,
              '<%OPTIONS%>' => $options,
              '<%ATTRIBUTES%>' => $attributes
            ));
            break;
          case 9:
            $options = '';
            $sqlCondition = '';
            if ($elementProperties[93]) {
              $table = dbGetValueFromDb('SELECT t1.Name FROM mycms_tables AS t1 WHERE t1.Id=(SELECT t2.TableId FROM mycms_documents AS t2 WHERE t2.Id=' . $this->document['Id'] . ')', __FILE__, __LINE__);
              if ($this->document['Table'] == $elementProperties[90]) {
                $sqlCondition = ' WHERE Id<>' . $rec['Id'];
              }
            }
            $values = array();
            $resValues = dbDoQuery('SELECT `' . $elementProperties[98] . '` FROM `' . $elementProperties[96] . '` WHERE `' . $elementProperties[97] . '`=' . $rec['Id'], __FILE__, __LINE__);
            while ($recValue = dbGetRow($resValues)) {
              $values[] = $recValue[0];
            }

            preg_match_all('/([A-z0-9]+)/', $elementProperties[91], $matchFields);
            if ($elementProperties[94]) {
              $resOpt = dbDoQuery('SELECT `' . $elementProperties[92] . '`, `' . implode('`, `', $matchFields[0]) . '` FROM `' . $elementProperties[90] . '`' . $sqlCondition . ' ' . ($elementProperties[94] ? 'ORDER BY ' . $elementProperties[94] . ' ' . $elementProperties[95] : ''), __FILE__, __LINE__);
            } else {
              $resOpt = dbDoQuery('SELECT `' . $elementProperties[92] . '`, `' . implode('`, `', $matchFields[0]) . '` FROM `' . $elementProperties[90] . '`' . $sqlCondition, __FILE__, __LINE__);
            }
            $counter = 0;
            while ($opt = dbGetRecord($resOpt)) {
              if (in_array($opt[$elementProperties[92]], $values)) {
                $options .= '<span class="item current" value="' . $opt[$elementProperties[92]] . '">' . strtr($elementProperties[91], $opt) . '</span>';
                $counter++;
              } else {
                $options .= '<span class="item" value="' . $opt[$elementProperties[92]] . '">' . strtr($elementProperties[91], $opt) . '</span>';
              }
            }
            $defaultTitle = 'Выбрано ' . $counter . ' ' . Word125($counter, 'значение', 'значения', 'значений');
            $defaultValue = implode(',', $values);
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $elementProperties[96] . $rec['Id'] . '"',
              '<%NAME%>' => $elementProperties[96] . '[' . $rec['Id'] . ']',
              '<%DEFAULT_TITLE%>' => $defaultTitle,
              '<%DEFAULT_VALUE%>' => $defaultValue,
              '<%OPTIONS%>' => $options,
              '<%ATTRIBUTES%>' => $attributes
            ));
            break;
          default:
            $inp = strtr($inp, array(
              '<%IDNUM%>' => ' id="' . $name . $rec['Id'] . '"',
              '<%NAME%>' => $name . '[' . $rec['Id'] . ']',
              '<%VALUE%>' => $rec[$name],
              '<%ATTRIBUTES%>' => $attributes
            ));
            break;
        }

        /*
				if($this->document['ParentId'] && $name==$this->document['ParentField'] && $_SESSION['Filter']){
					$formRows.='<input type="hidden" name="'.$this->document['ParentField'].'['.$rec['Id'].']" value="'.$_SESSION['Filter'].'"/>';
				}else*/
        if ($name == $parentRecordField && $parentRecordId) {
          $formRows .= '<input type="hidden" name="' . $parentRecordField . '[' . $rec['Id'] . ']" value="' . $parentRecordId . '"/>';
          $formRows .= '<input type="hidden" name="parent_document_id" value="' . $this->document['ParentId'] . '"/>';
          $formRows .= '<input type="hidden" name="parent_record_id" value="' . $parentRecordId . '"/>';
          if ($j == 1) $formRows .= '<input type="hidden" name="id[' . $rec['Id'] . ']" value="' . $rec['Id'] . '"/>';

        } else {
          $formRows .= strtr($formElement, array(
            '<%LABEL%>' => $info['Title'],
            '<%ELEMENT%>' => $inp,
            '<%HIDDEN%>' => ($j == 1) ? '<input type="hidden" name="id[' . $rec['Id'] . ']" value="' . $rec['Id'] . '"/>' : ''
          ));
        }
        if ($elementProperties[4]) {
          $formRows .= '<div class="comment">' . $elementProperties[4] . '</div>';
        }
      }
      $content .= strtr($element, array(
        '<%EVEN%>' => isEven($i) ? ' form-even' : '',
        '<%NUM%>' => $rec['Id'],
        '<%CONTENT%>' => $formRows
      ));
    }

    $result = LoadTemplate('base_editor');
    $result = strtr($result, array(
      '<%DOCUMENT_TITLE%>' => 'Изменить запис' . (count($ids) > 1 ? 'и' : 'ь'),
      '<%BREAD_CRUMBS%>' => '<a href="index.php?module=records">Управление сайтом</a> &rarr; <a href="index.php?module=records&document_id=' . $this->document['Id'] . '">' . $this->document['Title'] . '</a> &rarr; ',
      '<%CONTENT%>' => $content,
      '<%ACTION_FORM%>' => 'index.php?module=records&act=EditRecord&document_id=' . $this->document['Id'],
      '<%ACTION_CANCEL%>' => 'recEditCancel(' . $this->document['Id'] . ($parentRecordId ? (', {parent_document_id:' . $this->document['ParentId'] . ', parent_record_id:' . $parentRecordId . '}') : '') . ');',
      '<%ACTION_SUBMIT%>' => 'recEditSendForm(this);'
    ));
    return $result;
  }

  function EditRecord() {
    global $Settings;
    $fields = GetFieldsList($this->document);
    $fieldsIds = GetSubArray($fields, 'Id');
    $properties = $Settings->GetElementsSettings($fieldsIds);
    $ids = $_POST['id'];
    $javascript = '';
    $error = false;

    // many
    if (is_array($ids)) {
      foreach ($ids as $id) {
        $errorInner = false;
        $sqlDescription = '';
        foreach ($fields as $name => $info) {
          $elementProperties = $properties[$info['Id']];

          // required
          if ($elementProperties[1] && !$_POST[$name][$id]) {
            $errorInner = true;
            $javascript .= 'stxAddErrorStatus(gei(\'' . $name . $id . '\'),\'Field can&#39t be empty\');';
          }
          // unique
          if ($elementProperties[2] && dbGetValueFromDb('SELECT COUNT(Id) FROM `' . $this->document['Table'] . '` WHERE `' . $name . '`="' . $_POST[$name][$id] . '" AND Id<>' . $id, __FILE__, __LINE__)) {
            $errorInner = true;
            $javascript .= 'stxAddErrorStatus(gei(\'' . $name . $id . '\'),\'Уже есть запись с таким значением\');';
          }
          if (!$errorInner) {
            switch ($info['Type']) {
              case 1:
                if ($elementProperties[5] == 'password') {
                  if ($_POST[$name][$id]) {
                    $value = md5($_POST[$name][$id]);
                  } else {
                    $untouched = true;
                  }
                } else {
                  $value = str_replace('"', '\"', $_POST[$name][$id]);
                }
                break;
              case 3:
                $dateType = $elementProperties[30];
                $value = DatePrepareToDb(($dateType == 'time' ? '0000-00-00 ' : '') . $_POST[$name][$id]);
                break;
              case 4:
                if (!$elementProperties[42]) {
                  if ($sourcepath = $_POST[$name][$id]) {
                    $filepath = dbGetValueFromDb('SELECT `' . $name . '` FROM `' . $this->document['Table'] . '` WHERE Id=' . $id, __FILE__, __LINE__);
                    flDeleteFile(ROOT_DIR . $filepath);

                    $uploadpath = $elementProperties[40] ? $elementProperties[40] : UPLOADS_DIR;
                    if (mb_substr($uploadpath, mb_strlen($uploadpath) - 1) != '/') $uploadpath .= '/';
                    $info = flGetInfo($sourcepath);
                    //flMoveFile($sourcepath, ROOT_DIR . $uploadpath . $info['basename']);
                    //$_POST[$name][$id] = $value = $uploadpath . $info['basename'];
                    $value = str_replace(ROOT_DIR, '', flMoveFile($sourcepath, ROOT_DIR . $uploadpath . $info['basename']));
                    $_POST[$name][$id] = $value;
                  } else {
                    $untouched = true;
                  }
                } else {
                  $resize['source'] = $elementProperties[43] ? $elementProperties[43] : 'Image';
                  $sourceProperties = $properties[$fields[$resize['source']]['Id']];
                  if ($sourcepath = $_POST[$resize['source']][$id]) {
                    $filepath = dbGetValueFromDb('SELECT `' . $name . '` FROM `' . $this->document['Table'] . '` WHERE Id=' . $id, __FILE__, __LINE__);
                    flDeleteFile(ROOT_DIR . $filepath);

                    $resize['uploadpath'] = $elementProperties[40] ? $elementProperties[40] : ($sourceProperties[40] ? $sourceProperties[40] : UPLOADS_DIR);
                    $resize['type'] = strtolower($elementProperties[44]);
                    $resize['width'] = $elementProperties[45];
                    $resize['height'] = $elementProperties[46];
                    $resize['function'] = ($resize['type'] == 'fit' ? 'imgFit' : ($resize['type'] == 'thumbnail' ? 'imgThumbnail' : 'imgResize'));
                    if (function_exists($resize['function'])) $value = $resize['function'](ROOT_DIR . $sourcepath, $resize['width'], $resize['height'], ROOT_DIR . $resize['uploadpath']);
                    $info = flGetInfo($value);
                    if (mb_substr($resize['uploadpath'], mb_strlen($resize['uploadpath']) - 1) != '/') $resize['uploadpath'] .= '/';
                    $value = $resize['uploadpath'] . $info['basename'];
                  } else {
                    $untouched = true;
                  }
                }
                break;
              case 5:
                if ($sourcepath = $_POST[$name][$id]) {
                  $filepath = dbGetValueFromDb('SELECT `' . $name . '` FROM `' . $this->document['Table'] . '` WHERE Id=' . $id, __FILE__, __LINE__);
                  flDeleteFile(ROOT_DIR . $filepath);

                  $uploadpath = $elementProperties[50] ? $elementProperties[50] : UPLOADS_DIR;
                  if (mb_substr($uploadpath, mb_strlen($uploadpath) - 1) != '/') $uploadpath .= '/';
                  $info = flGetInfo($sourcepath);
                  flMoveFile($sourcepath, ROOT_DIR . $uploadpath . $info['basename']);
                  $_POST[$name][$id] = $value = $uploadpath . $info['basename'];
                } else {
                  $untouched = true;
                }
                break;
              case 6:
                $value = $_POST[$name][$id] ? 1 : 0;
                break;
              case 9:
                $storageTable = $elementProperties[96];
                $storageSelfField = $elementProperties[97];
                $storageField = $elementProperties[98];
                $this->document['SystemAction'][] = 'SystemMultiSelect';
                $this->document['SystemParams'][] = array(
                  'storageTable' => $elementProperties[96],
                  'storageSelfField' => $elementProperties[97],
                  'storageField' => $elementProperties[98],
                  'values' => explode(',', $_POST[$storageTable][$id])
                );
                continue(2);
                break;
              default:
                $value = str_replace('"', '\"', $_POST[$name][$id]);
                break;
            }
            if (!$untouched) $sqlDescription .= ($sqlDescription ? ', ' : '') . '`' . $name . '`="' . $value . '"';
            $newRecord[$name] = $value;
            $updatedColumns[$name] = !$untouched;
            $value = '';
            $untouched = false;
          }
        }
        // before add action
        if ($this->document['ActionCode']) {
          $oldRecord = dbGetRecordFromDb('SELECT * FROM `' . $this->document['Table'] . '` WHERE Id=' . $id, __FILE__, __LINE__);
          $actionsModule = krnLoadModuleByName('actions');
          $actionsMethod = 'BeforeEdit' . $this->document['ActionCode'];
          if (method_exists($actionsModule, $actionsMethod)) {
            $r = $actionsModule->$actionsMethod($newRecord, $oldRecord);
            if ($r !== true) {
              $errorInner = true;
              $javascript .= 'stxAddErrorStatus(domPN(domLC(gei(\'form' . $id . '\'))),\'<span style="padding-left:12px;">' . $r . '</span>\');';
            }
          }
        }
        if (!$errorInner) {
          $sqlDescription = $this->buildSql($newRecord, $updatedColumns);
          dbDoQuery('UPDATE `' . $this->document['Table'] . '` SET ' . $sqlDescription . ' WHERE Id=' . $id, __FILE__, __LINE__);
          $newRecord['Id'] = $oldRecord['Id'] = $id;

          // system action
          if (isset($this->document['SystemAction'])) {
            foreach ($this->document['SystemAction'] as $k => $systemAction) {
              $this->document['SystemParams'][$k]['selfValue'] = $newRecord['Id'];
              $actionsModule->$systemAction($this->document['SystemParams'][$k]);
            }
          }

          // on edit action
          if ($this->document['ActionCode']) {
            $actionsMethod = 'OnEdit' . $this->document['ActionCode'];
            if (method_exists($actionsModule, $actionsMethod)) {
              //$newRecord=GetSubArray(array_intersect_key($_POST,$fields),$id);
              $newRecord['Id'] = $oldRecord['Id'] = $id;
              $actionsModule->$actionsMethod($newRecord, $oldRecord);
            }
          }
          $javascript .= 'domD(gei(\'form' . $id . '\'));';
        }
        if (!$error) $error = $errorInner;
      }
      //if(!$error)$javascript='redirect(\'index.php?module=records&document_id='.$this->document['Id'].'\');';
      if (!$error) {
        if ($_POST['parent_record_id']) {
          $javascript = 'redirect(\'index.php?module=records&mode=view&document_id=' . $_POST['parent_document_id'] . '&record_id=' . $_POST['parent_record_id'] . '#tab-' . $this->document['Id'] . '\');';
        } else {
          $javascript = 'redirect(\'index.php?module=records&document_id=' . $this->document['Id'] . '\');';
        }
      }

      // single
    } else {
      $id = $ids;
      $sqlDescription = '';
      foreach ($fields as $name => $info) {
        $elementProperties = $properties[$info['Id']];

        // required
        if ($elementProperties[1] && !$_POST[$name]) {
          $error = true;
          $javascript .= 'stxAddErrorStatus(domP(form.' . $name . '),\'Field can&#39t be empty\');';
        }
        // unique
        if ($elementProperties[2] && dbGetValueFromDb('SELECT COUNT(Id) FROM `' . $this->document['Table'] . '` WHERE `' . $name . '`="' . $_POST[$name] . '" AND Id<>' . $id, __FILE__, __LINE__)) {
          $error = true;
          $javascript .= 'stxAddErrorStatus(domP(form.' . $name . '),\'Уже есть запись с таким значением\');';
        }
        if (!$error) {
          switch ($info['Type']) {
            case 1:
              if ($elementProperties[5] == 'password') {
                if ($_POST[$name]) {
                  $value = md5($_POST[$name]);
                } else {
                  $untouched = true;
                }
              } else {
                $value = str_replace('"', '\"', $_POST[$name]);
              }
              break;
            case 3:
              $dateType = $elementProperties[30];
              $value = DatePrepareToDb(($dateType == 'time' ? '0000-00-00 ' : '') . $_POST[$name]);
              break;
            case 4:
              if (!$elementProperties[42]) {
                if ($sourcepath = $_POST[$name]) {
                  $filepath = dbGetValueFromDb('SELECT `' . $name . '` FROM `' . $this->document['Table'] . '` WHERE Id=' . $id, __FILE__, __LINE__);
                  flDeleteFile(ROOT_DIR . $filepath);

                  $uploadpath = $elementProperties[40] ? $elementProperties[40] : UPLOADS_DIR;
                  if (mb_substr($uploadpath, mb_strlen($uploadpath) - 1) != '/') $uploadpath .= '/';
                  $info = flGetInfo($sourcepath);
                  //flMoveFile($sourcepath, ROOT_DIR . $uploadpath . $info['basename']);
                  //$_POST[$name] = $value = $uploadpath . $info['basename'];
                  $value = str_replace(ROOT_DIR, '', flMoveFile($sourcepath, ROOT_DIR . $uploadpath . $info['basename']));
                  $_POST[$name] = $value;
                } else {
                  $untouched = true;
                }
              } else {
                $resize['source'] = $elementProperties[43] ? $elementProperties[43] : 'Image';
                $sourceProperties = $properties[$fields[$resize['source']]['Id']];
                if ($sourcepath = $_POST[$resize['source']]) {
                  $filepath = dbGetValueFromDb('SELECT `' . $name . '` FROM `' . $this->document['Table'] . '` WHERE Id=' . $id, __FILE__, __LINE__);
                  flDeleteFile(ROOT_DIR . $filepath);

                  $resize['uploadpath'] = $elementProperties[40] ? $elementProperties[40] : ($sourceProperties[40] ? $sourceProperties[40] : UPLOADS_DIR);
                  $resize['type'] = strtolower($elementProperties[44]);
                  $resize['width'] = $elementProperties[45];
                  $resize['height'] = $elementProperties[46];
                  $resize['function'] = ($resize['type'] == 'fit' ? 'imgFit' : ($resize['type'] == 'thumbnail' ? 'imgThumbnail' : 'imgResize'));
                  if (function_exists($resize['function'])) $value = $resize['function'](ROOT_DIR . $sourcepath, $resize['width'], $resize['height'], ROOT_DIR . $resize['uploadpath']);
                  $info = flGetInfo($value);
                  if (mb_substr($resize['uploadpath'], mb_strlen($resize['uploadpath']) - 1) != '/') $resize['uploadpath'] .= '/';
                  $value = $resize['uploadpath'] . $info['basename'];
                } else {
                  $untouched = true;
                }
              }
              break;
            case 5:
              if ($sourcepath = $_POST[$name]) {
                $filepath = dbGetValueFromDb('SELECT `' . $name . '` FROM `' . $this->document['Table'] . '` WHERE Id=' . $id, __FILE__, __LINE__);
                flDeleteFile(ROOT_DIR . $filepath);

                $uploadpath = $elementProperties[50] ? $elementProperties[50] : UPLOADS_DIR;
                if (mb_substr($uploadpath, mb_strlen($uploadpath) - 1) != '/') $uploadpath .= '/';
                $info = flGetInfo($sourcepath);
                flMoveFile($sourcepath, ROOT_DIR . $uploadpath . $info['basename']);
                $_POST[$name] = $value = $uploadpath . $info['basename'];
              } else {
                $untouched = true;
              }
              break;
            case 6:
              $value = $_POST[$name] ? 1 : 0;
              break;
            case 9:
              $storageTable = $elementProperties[96];
              $storageSelfField = $elementProperties[97];
              $storageField = $elementProperties[98];
              $this->document['SystemAction'][] = 'SystemMultiSelect';
              $this->document['SystemParams'][] = array(
                'storageTable' => $elementProperties[96],
                'storageSelfField' => $elementProperties[97],
                'storageField' => $elementProperties[98],
                'values' => explode(',', $_POST[$storageTable])
              );
              continue(2);
              break;
            default:
              $value = str_replace('"', '\"', $_POST[$name]);
              break;
          }
          if (!$untouched) {
            $sqlDescription .= ($sqlDescription ? ', ' : '') . '`' . $name . '`="' . $value . '"';
            $newRecord[$name] = $value;
          }
          $value = '';
          $untouched = false;
        }
      }
      // before edit action
      if ($this->document['ActionCode']) {
        $oldRecord = dbGetRecordFromDb('SELECT * FROM `' . $this->document['Table'] . '` WHERE Id=' . $id, __FILE__, __LINE__);
        $actionsModule = krnLoadModuleByName('actions');
        $actionsMethod = 'BeforeEdit' . $this->document['ActionCode'];
        if (method_exists($actionsModule, $actionsMethod)) {
          $r = $actionsModule->$actionsMethod($newRecord, $oldRecord);
          if ($r !== true) {
            $error = true;
            $javascript .= 'stxAddErrorStatus(domPN(domLC(gei(\'form\'))),\'<span style="padding-left:160px;">' . $r . '</span>\');';
          }
        }
      }
      if (!$error) {
        $sqlDescription = $this->buildSql($newRecord, $updatedColumns);
        dbDoQuery('UPDATE `' . $this->document['Table'] . '` SET ' . $sqlDescription . ' WHERE Id=' . $id, __FILE__, __LINE__);
        $newRecord['Id'] = $oldRecord['Id'] = $id;

        // system action
        if (isset($this->document['SystemAction'])) {
          foreach ($this->document['SystemAction'] as $k => $systemAction) {
            $this->document['SystemParams'][$k]['selfValue'] = $newRecord['Id'];
            $actionsModule->$systemAction($this->document['SystemParams'][$k]);
          }
        }

        // on edit action
        if ($this->document['ActionCode']) {
          $actionsMethod = 'OnEdit' . $this->document['ActionCode'];
          if (method_exists($actionsModule, $actionsMethod)) {
            //$newRecord=array_intersect_key($_POST,$fields);
            $actionsModule->$actionsMethod($newRecord, $oldRecord);
          }
        }
        $javascript .= 'reload(true);';
      }
    }
    return $javascript;
  }

  function DeleteRecord() {
    $ids = is_array($_POST['id']) ? $_POST['id'] : Array((int)$_POST['id'] => 'on');
    $javascript = '';
    $sqlCondition = '';
    $fields = GetFieldsList($this->document);
    foreach ($ids as $id => $on) {
      // on delete action
      if ($this->document['ActionCode']) {
        $actionsModule = krnLoadModuleByName('actions');
        $actionsMethod = 'OnDelete' . $this->document['ActionCode'];
        if (method_exists($actionsModule, $actionsMethod)) {
          $oldRecord = dbGetRecordFromDb('SELECT * FROM `' . $this->document['Table'] . '` WHERE Id=' . $id, __FILE__, __LINE__);
          $oldRecord['Id'] = $id;
          $actionsModule->$actionsMethod($oldRecord);
        }
      }

      foreach ($fields as $name => $info) {
        $elementProperties = $properties[$info['Id']];
        switch ($info['Type']) {
          case 4:
            $filepath = dbGetValueFromDb('SELECT `' . $name . '` FROM `' . $this->document['Table'] . '` WHERE Id=' . $id, __FILE__, __LINE__);
            flDeleteFile(ROOT_DIR . $filepath);
            break;
          case 5:
            $filepath = dbGetValueFromDb('SELECT `' . $name . '` FROM `' . $this->document['Table'] . '` WHERE Id=' . $id, __FILE__, __LINE__);
            flDeleteFile(ROOT_DIR . $filepath);
            break;
          case 9:
            $storageTable = $elementProperties[96];
            $storageSelfField = $elementProperties[97];
            $storageField = $elementProperties[98];
            $this->document['SystemAction'][] = 'SystemMultiSelect';
            $this->document['SystemParams'][] = array(
              'storageTable' => $elementProperties[96],
              'storageSelfField' => $elementProperties[97],
              'storageField' => $elementProperties[98]
            );
            break;
        }
      }
      $sqlCondition .= ($sqlCondition ? ' OR Id=' . $id : 'Id=' . $id);
    }
    dbDoQuery('DELETE FROM `' . $this->document['Table'] . '` WHERE ' . $sqlCondition, __FILE__, __LINE__);

    // system action
    if (isset($this->document['SystemAction'])) {
      foreach ($this->document['SystemAction'] as $k => $systemAction) {
        $this->document['SystemParams'][$k]['selfValue'] = $newRecord['Id'];
        $actionsModule->$systemAction($this->document['SystemParams'][$k]);
      }
    }

    $javascript = 'reload(true);';
    return $javascript;
  }

  public function buildSql($data, $updatedColumns = []) {
    $sql = '';

    foreach ($data as $key => $value) {

      if ($updatedColumns) {
        if ($updatedColumns[$key]) {
          $sql .= ($sql ? ', ' : '') . '`'.$key.'` = "' . $value .'"';
        }
      } else {
        $sql .= ($sql ? ', ' : '') . '`'.$key.'` = "' . $value .'"';
      }
    }
    return $sql;
  }

}

?>
