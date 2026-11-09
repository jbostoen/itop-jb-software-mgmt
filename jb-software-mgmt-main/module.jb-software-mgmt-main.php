<?php

/**
 * @copyright   Copyright (c) 2019-2026 Jeffrey Bostoen
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     3.2.261109
 *
 * iTop module definition file
 */

SetupWebPage::AddModule(
        __FILE__, // Path to the current file, all other file names are relative to the directory containing this file
        'jb-software-mgmt-main/3.2.261109',
        array(
                // Identification
                //
                'label' => 'Datamodel: Software and license management',
                'category' => 'business',

                // Setup
                //
                'dependencies' => array( 
			'itop-config-mgmt/3.2.0 && itop-config-mgmt/<3.3.0',
                ),
                'mandatory' => false,
                'visible' => true,

                'installer' => 'SoftwareMgmtInstaller',

                // Components
                //
                'datamodel' => array(
			'model.jb-software-mgmt-main.php',
                        'src/JeffreyBostoenExtensions/SoftwareMgmt/DebianVersion.php',
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

                                        // - The platform of a build is a platform version next to a platform product.
                                        if($sPreviousVersion !== '' && version_compare($sPreviousVersion, '3.2.261109', '<')) {

                                                SetupLog::Info('Rename the platform of a software build to the platform version.');

                                                static::MoveColumnInDB('software_mgmt_build', 'platform_id', 'software_mgmt_build', 'platform_version_id');

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

                                // - The platform product of the builds that have a platform version.
                                try {

                                        if($sPreviousVersion !== '' && version_compare($sPreviousVersion, '3.2.261109', '<')) {

                                                SetupLog::Info('Fill the platform product of the software builds that have a platform version.');

                                                $oSet = new DBObjectSet(DBObjectSearch::FromOQL_AllData('SELECT SoftwareBuild WHERE platform_version_id > 0 AND platform_product_id = 0'));

                                                while($oBuild = $oSet->Fetch()) {

                                                        $oVersion = MetaModel::GetObject('SoftwareVersion', (int)$oBuild->Get('platform_version_id'), false, true);

                                                        if($oVersion !== null) {
                                                                $oBuild->Set('platform_product_id', (int)$oVersion->Get('softwareproduct_id'));
                                                                $oBuild->DBUpdate();
                                                        }

                                                }

                                        }

                                }
                                catch(Exception $e) {

                                        SetupLog::Error('Failed to fill the platform product of the software builds: '.$e->GetMessage());

                                }

                                // - The platform family of the operating systems that are known by name, then of the builds that have a platform product. Only what is still empty.
                                try {

                                        if($sPreviousVersion !== '') {

                                                SetupLog::Info('Fill the platform family of the operating systems and of the software builds.');

                                                $aFamilies = [
                                                        'Microsoft Windows' => 'windows',
                                                        'Microsoft Windows Server' => 'windows',
                                                        'Ubuntu' => 'linux',
                                                        'Debian' => 'linux',
                                                        'Android' => 'android',
                                                        'iOS' => 'ios',
                                                        'macOS' => 'macos',
                                                ];

                                                foreach($aFamilies as $sName => $sFamily) {

                                                        $oSet = new DBObjectSet(DBObjectSearch::FromOQL_AllData('SELECT SoftwareProduct WHERE name = :name AND type = \'operating_system\''), [], ['name' => $sName]);

                                                        while($oProduct = $oSet->Fetch()) {

                                                                if((string)$oProduct->Get('platform_family') === '') {
                                                                        $oProduct->Set('platform_family', $sFamily);
                                                                        $oProduct->DBUpdate();
                                                                }

                                                        }

                                                }

                                                $aFamilyByProduct = [];
                                                $oSet = new DBObjectSet(DBObjectSearch::FromOQL_AllData('SELECT SoftwareBuild WHERE platform_product_id > 0 AND platform_family = \'na\''));

                                                while($oBuild = $oSet->Fetch()) {

                                                        $iProductId = (int)$oBuild->Get('platform_product_id');

                                                        if(!isset($aFamilyByProduct[$iProductId])) {
                                                                $oProduct = MetaModel::GetObject('SoftwareProduct', $iProductId, false, true);
                                                                $aFamilyByProduct[$iProductId] = ($oProduct === null ? '' : (string)$oProduct->Get('platform_family'));
                                                        }

                                                        if($aFamilyByProduct[$iProductId] !== '') {
                                                                $oBuild->Set('platform_family', $aFamilyByProduct[$iProductId]);
                                                                $oBuild->DBUpdate();
                                                        }

                                                }

                                        }

                                }
                                catch(Exception $e) {

                                        SetupLog::Error('Failed to fill the platform family: '.$e->GetMessage());

                                }

                        
			
		}


	}

}
