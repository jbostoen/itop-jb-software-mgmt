<?php

/**
 * @copyright   Copyright (c) 2019-2026 Jeffrey Bostoen
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     3.2.260801
 *
 * iTop module definition file
 */

SetupWebPage::AddModule(
        __FILE__, // Path to the current file, all other file names are relative to the directory containing this file
        'jb-software-mgmt-main/3.2.260801',
        array(
                // Identification
                //
                'label' => 'Datamodel: Software and license management',
                'category' => 'business',

                // Setup
                //
                'dependencies' => array( 
			'itop-config-mgmt/3.2.0',
                ),
                'mandatory' => false,
                'visible' => true,

                'installer' => 'SoftwareMgmtInstaller',

                // Components
                //
                'datamodel' => array(
			'model.jb-software-mgmt-main.php',
                        'src/JeffreyBostoenExtensions/SoftwareMgmt/EventListeners.php',
                        'src/JeffreyBostoenExtensions/SoftwareMgmt/Helper.php',
                        'src/JeffreyBostoenExtensions/SoftwareMgmt/Logger.php',
                        'src/JeffreyBostoenExtensions/SoftwareMgmt/ScheduledTask.php',
                ),
                'webservice' => array(

                ),
                'data.struct' => array(
		        // add your 'structure' definition XML files here,
                ),
                'data.sample' => array(
			// add your sample data XML files here,
                ),

                // Documentation
                //
                'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
                'doc.more_information' => '', // hyperlink to more information, if any

                // Default settings
                //
                'settings' => array(
                        // Module specific settings go here, if any
                ),
        )
);



if(!class_exists('SoftwareMgmtInstaller')) {


	/**
	 * Class SoftwareMgmtInstaller.
	 */
	abstract class SoftwareMgmtInstaller extends ModuleInstallerAPI {
		
		/**
		 * @inheritDoc
                 * 
                 * Renames database tables and columns.
		 */
		public static function BeforeDatabaseCreation(Config $oConfiguration, $sPreviousVersion, $sCurrentVersion) {

			// - Upgrade only.

                                try {
                                        
                                        if($sPreviousVersion !== '' && version_compare($sPreviousVersion, '3.2.260726', '<')) {
                                        
                                                SetupLog::Info('Rename database tables.');

                                                static::RenameTableInDB('jbsoftware', 'software_mgmt_product');
                                                static::RenameTableInDB('jbsoftwareversion', 'software_mgmt_version');
                                                static::RenameTableInDB('jbsoftwareinstallation', 'software_mgmt_installation');
                                                static::RenameTableInDB('jblicense', 'software_mgmt_license');
                                                static::RenameTableInDB('lnklicensetosoftwareversion', 'software_mgmt_lnklicensetoversion');


                                                static::MoveColumnInDB('softwareinstallation', 'jbsoftwareversion_id', 'softwareinstallation', 'softwareversion_id');
                                                static::MoveColumnInDB('lnklicensetosoftwareversion', 'jbsoftwareversion_id', 'lnklicensetosoftwareversion', 'softwareversion_id');


                                        }

                                }
                                catch(Exception $e) {

                                        SetupLog::Error('Failed during migrations: '.$e->GetMessage());

                                }

                        // @todo Migrate vendor info?
                        // @todo Migrate version details?
			
		}

		/**
		 * @inheritDoc
                 * 
                 * Adds release types.
		 */
		public static function AfterDatabaseCreation(Config $oConfiguration, $sPreviousVersion, $sCurrentVersion) {

			// - New & upgrade.

                                try  {
                                                
                                        if(version_compare($sPreviousVersion, '3.2.260726', '<')) {

                                                $aReleaseTypes = [
                                                        'Alpha',
                                                        'Beta',
                                                        'Development',
                                                        'General Availability (GA)',
                                                        'Long-Term Support (LTS)',
                                                        'Preview',
                                                        'Release Candidate (RC)',
                                                        'Release Preview (RP)',
                                                        'Rolling Release',
                                                        'Short-Term Support (STS)',
                                                        'Technical Preview (TP)',
                                                        
                                                ];

                                                foreach($aReleaseTypes as $sReleaseType) {

                                                        $oReleaseType = MetaModel::NewObject('SoftwareReleaseType', [
                                                                'name' => $sReleaseType,
                                                        ]);
                                                        $oReleaseType->DBInsert();

                                                }

                                        }
                                
                                }
                                catch(Exception $e) {

                                        SetupLog::Error('Failed to add release types: '.$e->GetMessage());

                                }

                        
			
		}


	}

}
