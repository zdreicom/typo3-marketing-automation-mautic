<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "marketing_automation_mautic".
 *
 * Auto generated 20-06-2018 11:55
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'Marketing Automation - Mautic Adapter',
  'description' => 'Add-on TYPO3 extension that enhances the "marketing-automation" TYPO3 extension by connecting it to the Mautic Marketing Automation platform: Determine "Persona" from Mautic segments. Also provides additional services e.g. language synchronisation between Mautic and TYPO3.',
  'category' => 'fe',
  'state' => 'stable',
  'uploadfolder' => 0,
  'createDirs' => '',
  'clearCacheOnLoad' => 0,
  'version' => '0.1.0',
  'constraints' =>
  array (
    'depends' =>
    array (
      'typo3' => '8.7.0-8.7.99',
      'marketing_automation' => '',
    ),
    'conflicts' =>
    array (
    ),
    'suggests' =>
    array (
      'static_info_tables' => '6.4.0',
    ),
  ),
  '_md5_values_when_last_written' => 'a:2:{s:13:"composer.json";s:4:"d49b";s:36:"Resources/Public/Icons/Extension.svg";s:4:"4473";}',
);

