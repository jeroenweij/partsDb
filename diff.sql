composer require dompdf/dompdf

ALTER TABLE `stock` ADD `deleted` BOOLEAN NOT NULL DEFAULT FALSE AFTER `count`;
ALTER TABLE `companys` ADD `logo` VARCHAR(80) NOT NULL AFTER `logo`;
