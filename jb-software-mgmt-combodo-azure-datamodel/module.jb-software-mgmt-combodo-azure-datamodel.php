<?php

/**
 * @copyright   Copyright (c) 2019-2026 Jeffrey Bostoen
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     3.2.260729
 *
 * iTop module definition file
 */

SetupWebPage::AddModule(
        __FILE__, // Path to the current file, all other file names are relative to the directory containing this file
        'jb-software-mgmt-combodo-azure-datamodel/3.2.260729',
        array(
                // Identification
                //
                'label' => 'Datamodel: Software and license management / Combodo Azure Datamodel',
                'category' => 'business',

                // Setup
                //
                'dependencies' => array( 
			'combodo-azure-datamodel/1.0.0',
                ),
                'mandatory' => false,
                'visible' => false,
                'auto_select' => 'SetupInfo::ModuleIsSelected("combodo-azure-datamodel")',

                // Components
                //
                'datamodel' => array(
			'model.jb-software-mgmt-combodo-azure-datamodel.php'
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



