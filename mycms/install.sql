SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `mycms_documents` ( `Id` int(10) unsigned NOT NULL AUTO_INCREMENT, `Title` varchar(255) NOT NULL, `TableId` int(10) unsigned NOT NULL, `LeadElement` int(10) unsigned NOT NULL, `SortElement` int(10) unsigned NOT NULL, `SortDirection` tinyint(1) unsigned NOT NULL, `ActionCode` varchar(255) NOT NULL, `Order` int(10) unsigned NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM  DEFAULT CHARSET=cp1251;

INSERT INTO `mycms_documents` (`Id`, `Title`, `TableId`, `LeadElement`, `SortElement`, `SortDirection`, `ActionCode`, `Order`) VALUES (1, 'Статические страницы', 4, 0, 0, 0, '', 10),(2, 'Главное меню', 2, 0, 0, 0, '', 20),(3, 'Файлы', 3, 0, 0, 0, '', 30),(4, 'Текстовые блоки', 5, 0, 0, 0, '', 40),(5, 'Настройки', 1, 0, 0, 0, '', 50);

CREATE TABLE IF NOT EXISTS `mycms_elements` ( `Id` int(10) unsigned NOT NULL AUTO_INCREMENT, `Title` varchar(255) NOT NULL, `DocumentId` int(10) unsigned NOT NULL, `FieldId` int(10) unsigned NOT NULL, `Type` int(3) unsigned NOT NULL, `Show` tinyint(1) unsigned NOT NULL, `Order` int(10) unsigned NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM  DEFAULT CHARSET=cp1251;

INSERT INTO `mycms_elements` (`Id`, `Title`, `DocumentId`, `FieldId`, `Type`, `Show`, `Order`) VALUES (1, 'Название', 1, 5, 1, 1, 1),(2, 'Код', 1, 6, 1, 1, 2),(3, 'Содержимое', 1, 7, 2, 0, 3),(4, 'Порядок', 1, 8, 1, 1, 4),(5, 'Ключевые слова', 1, 9, 1, 0, 5),(6, 'Мета описание', 1, 10, 1, 0, 6),(7, 'Название', 2, 12, 1, 1, 1),(8, 'Ссылка', 2, 13, 1, 1, 2),(9, 'Порядок', 2, 14, 1, 1, 3),(10, 'Название', 3, 15, 1, 1, 1),(11, 'Код', 3, 16, 1, 1, 2),(12, 'Ссылка', 3, 17, 5, 1, 3),(13, 'Заголовок', 4, 18, 1, 1, 1),(14, 'Содержимое', 4, 19, 2, 1, 2),(15, ' Номер блока', 4, 20, 1, 1, 3),(16, 'Название', 5, 1, 1, 1, 1),(17, 'Код', 5, 2, 1, 1, 2),(18, 'Тип', 5, 3, 1, 1, 3),(19, 'Значение', 5, 4, 1, 1, 4);

CREATE TABLE IF NOT EXISTS `mycms_fields` ( `Id` int(10) unsigned NOT NULL AUTO_INCREMENT, `Name` varchar(255) NOT NULL, `TableId` int(10) unsigned NOT NULL, `TypeId` int(10) unsigned NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM  DEFAULT CHARSET=cp1251;

INSERT INTO `mycms_fields` (`Id`, `Name`, `TableId`, `TypeId`) VALUES (1, 'Title', 1, 1),(2, 'Code', 1, 1),(3, 'Type', 1, 1),(4, 'Value', 1, 2),(5, 'Title', 4, 1),(6, 'Code', 4, 1),(7, 'Content', 4, 2),(8, 'Order', 4, 3),(9, 'Keywords', 4, 1),(10, 'Description', 4, 1),(11, 'LastModTime', 4, 6),(12, 'Title', 2, 1),(13, 'Link', 2, 1),(14, 'Order', 2, 3),(15, 'Title', 3, 1),(16, 'Code', 3, 1),(17, 'Link', 3, 1),(18, 'Title', 5, 1),(19, 'Text', 5, 2),(20, 'Num', 5, 3);

CREATE TABLE IF NOT EXISTS `mycms_field_types` ( `Id` int(10) unsigned NOT NULL AUTO_INCREMENT, `Title` varchar(255) NOT NULL, `Value` varchar(255) NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM  DEFAULT CHARSET=cp1251;

INSERT INTO `mycms_field_types` (`Id`, `Title`, `Value`) VALUES (1, 'Строка', 'VARCHAR( 255 )'),(2, 'Текст', 'TEXT'),(3, 'Целое', 'INT UNSIGNED'),(4, 'Действительное', 'FLOAT UNSIGNED'),(5, 'Бит', 'TINYINT( 1 ) UNSIGNED'),(6, 'Дата', 'DATETIME');

CREATE TABLE IF NOT EXISTS `mycms_groups` ( `Id` int(10) unsigned NOT NULL AUTO_INCREMENT, `Title` varchar(255) NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM  DEFAULT CHARSET=cp1251;

INSERT INTO `mycms_groups` (`Id`, `Title`) VALUES (1, 'Разработчики'),(2, 'Администраторы');

CREATE TABLE IF NOT EXISTS `mycms_properties` ( `Id` int(10) unsigned NOT NULL AUTO_INCREMENT, `ElementId` int(10) unsigned NOT NULL, `Code` int(10) unsigned NOT NULL, `Value` text NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM  DEFAULT CHARSET=cp1251;

INSERT INTO `mycms_properties` (`Id`, `ElementId`, `Code`, `Value`) VALUES (1, 0, 3, '15'),(2, 1, 1, '1'),(3, 1, 2, '0'),(4, 1, 3, ''),(5, 1, 4, ''),(6, 1, 5, ''),(7, 1, 10, ''),(8, 2, 1, '1'),(9, 2, 2, '1'),(10, 2, 3, ''),(11, 2, 4, ''),(12, 2, 5, ''),(13, 2, 10, ''),(14, 3, 1, '0'),(15, 3, 2, '0'),(16, 3, 3, ''),(17, 3, 4, ''),(18, 3, 5, ''),(19, 3, 20, 'wysiwyg'),(20, 3, 21, ''),(21, 3, 22, '400'),(22, 3, 23, '500'),(23, 4, 1, '0'),(24, 4, 2, '0'),(25, 4, 3, ''),(26, 4, 4, ''),(27, 4, 5, 'number'),(28, 4, 10, ''),(29, 7, 1, '1'),(30, 7, 2, '0'),(31, 7, 3, ''),(32, 7, 4, ''),(33, 7, 5, ''),(34, 7, 10, ''),(35, 8, 1, '1'),(36, 8, 2, '0'),(37, 8, 3, ''),(38, 8, 4, ''),(39, 8, 5, ''),(40, 8, 10, ''),(41, 9, 1, '0'),(42, 9, 2, '0'),(43, 9, 3, ''),(44, 9, 4, ''),(45, 9, 5, 'number'),(46, 9, 10, ''),(47, 11, 1, '1'),(48, 11, 2, '1'),(49, 11, 3, ''),(50, 11, 4, ''),(51, 11, 5, ''),(52, 11, 10, ''),(53, 12, 1, '0'),(54, 12, 2, '0'),(55, 12, 3, ''),(56, 12, 4, ''),(57, 12, 5, ''),(58, 12, 50, 'files'),(59, 12, 51, '0'),(60, 14, 1, '0'),(61, 14, 2, '0'),(62, 14, 3, ''),(63, 14, 4, ''),(64, 14, 5, ''),(65, 14, 20, 'wysiwyg'),(66, 14, 21, ''),(67, 14, 22, ''),(68, 14, 23, ''),(69, 15, 1, '0'),(70, 15, 2, '0'),(71, 15, 3, ''),(72, 15, 4, ''),(73, 15, 5, 'number'),(74, 15, 10, '');

INSERT INTO `mycms_properties` (`ElementId`, `Code`, `Value`) VALUES (0, 3, '15');

CREATE TABLE IF NOT EXISTS `mycms_tables` ( `Id` int(10) unsigned NOT NULL AUTO_INCREMENT, `Name` varchar(255) NOT NULL, `Order` int(10) unsigned NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM  DEFAULT CHARSET=cp1251;

INSERT INTO `mycms_tables` (`Id`, `Name`, `Order`) VALUES (1, 'settings', 50),(2, 'menu_items', 20),(3, 'files', 30),(4, 'static_pages', 10),(5, 'text_blocks', 40);

CREATE TABLE IF NOT EXISTS `mycms_users` ( `Id` int(10) unsigned NOT NULL AUTO_INCREMENT, `Name` varchar(255) NOT NULL, `Status` int(11) unsigned NOT NULL, `Login` varchar(255) NOT NULL, `Password` varchar(255) NOT NULL, `GroupId` int(10) unsigned NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM  DEFAULT CHARSET=cp1251;

INSERT INTO `mycms_users` (`Id`, `Name`, `Status`, `Login`, `Password`, `GroupId`) VALUES (1, 'Разработчик', 7, 'roman', 'daf93dec2fed93d21a1a226fccdec724', 1);

CREATE TABLE IF NOT EXISTS `files` ( `Id` int(10) unsigned NOT NULL AUTO_INCREMENT, `Title` varchar(255) NOT NULL, `Code` varchar(255) NOT NULL, `Link` varchar(255) NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

CREATE TABLE IF NOT EXISTS `menu_items` ( `Id` int(10) unsigned NOT NULL AUTO_INCREMENT, `Title` varchar(255) NOT NULL, `Link` varchar(255) NOT NULL, `Order` int(10) unsigned NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM  DEFAULT CHARSET=cp1251;

CREATE TABLE IF NOT EXISTS `settings` ( `Id` int(10) unsigned NOT NULL AUTO_INCREMENT, `Title` varchar(255) NOT NULL, `Code` varchar(255) NOT NULL, `Type` varchar(255) NOT NULL, `Value` text NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM  DEFAULT CHARSET=cp1251;

CREATE TABLE IF NOT EXISTS `static_pages` ( `Id` int(10) unsigned NOT NULL AUTO_INCREMENT, `Title` varchar(255) NOT NULL, `Code` varchar(255) NOT NULL, `Content` text NOT NULL, `Order` int(10) unsigned NOT NULL, `Keywords` varchar(255) NOT NULL, `Description` varchar(255) NOT NULL, `LastModTime` date NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM  DEFAULT CHARSET=cp1251;

INSERT INTO `static_pages` (`Id`, `Title`, `Code`, `Content`, `Order`, `Keywords`, `Description`) VALUES (1, 'Контакты', 'contacts', '\r\n<p><strong>Добровольное объединение “Творческая группа”</strong></p>\r\n<p>Схема проезда к нам</p>', 4, 'Схема проезда Творческая группа, Контакты', 'Мы расскажем как с нами связаться и покажем как к нам можно проехать');

CREATE TABLE IF NOT EXISTS `text_blocks` ( `Id` int(10) unsigned NOT NULL AUTO_INCREMENT, `Title` varchar(255) NOT NULL, `Text` text NOT NULL, `Num` int(10) unsigned NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM DEFAULT CHARSET=cp1251;