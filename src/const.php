<?php
  define('ROOT', $_SERVER['DOCUMENT_ROOT']);

  const IMAGE_DIR = 'public/images/';

  const SITE         = ROOT.'/src/site';
  const PAGES        = SITE.'/pages/';
  const TEMPLATE_DIR = SITE.'/templates/';  // NOTE: Оставлено для обратной совместимости с предыдущей версией
  const TEMPLATES    = SITE.'/templates';
  
  const ENGINE      = ROOT.'/src/engine';
  const LIBRARY_DIR = ENGINE.'/library/';
  
  const TEMP_DIR = ROOT.'mycms/uploads/temp/';

?>