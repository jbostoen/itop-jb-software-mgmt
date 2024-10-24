<?php
/**
 * @copyright   Copyright (c) 2019-2023 Jeffrey Bostoen
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     2.7.230522
 *
 * Localized data
 */

Dict::Add('EN US', 'English', 'English', array(

	//	'Class:SomeClass' => 'Class name',
	//	'Class:SomeClass+' => 'More info on class name',
	//	'Class:SomeClass/Attribute:some_attribute' => 'your translation for the label',
    //	'Class:SomeClass/Attribute:some_attribute/Value:some_value' => 'your translation for a value',
    //	'Class:SomeClass/Attribute:some_attribute/Value:some_value+' => 'your translation for more info on the value',
	
	'Class:JBSoftware' => 'Software',
	'Class:JBSoftware+' => 'Generic name of the software',
	'Class:JBSoftware/Attribute:name' => 'Name of the software',
	'Class:JBSoftware/Attribute:name+' => 'Main name of the software, such as MS Office, MS Windows Server. Does not include version info or a year.',
	'Class:JBSoftware/Attribute:org_id' => 'Organization',
	'Class:JBSoftware/Attribute:org_id+' => 'Organization where this software is used',
	'Class:JBSoftware/Attribute:vendor' => 'Vendor',
	'Class:JBSoftware/Attribute:vendor+' => 'Vendor',
	'Class:JBSoftware/Attribute:type' => 'Type',
	'Class:JBSoftware/Attribute:type+' => 'Type of software',
	'Class:JBSoftware/Attribute:type/Value:client_software' => 'Client software',
	'Class:JBSoftware/Attribute:type/Value:cloud_software' => 'Cloud software',
	'Class:JBSoftware/Attribute:type/Value:operating_system' => 'Operating system',
	'Class:JBSoftware/Attribute:type/Value:server_software' => 'Server software',
	'Class:JBSoftware/Attribute:versions_list' => 'Versions',
	'Class:JBSoftware/Attribute:versions_list+' => 'Versions',
	'Class:JBSoftware/UniquenessRule:unique_software' => 'The name of the software must be unique.',
	
	'Class:JBSoftwareVersion' => 'Software Version',
	'Class:JBSoftwareVersion+' => 'Software version',
	'Class:JBSoftwareVersion/Attribute:org_id' => 'Organization',
	'Class:JBSoftwareVersion/Attribute:org_id+' => 'Organization where this software is used',
	'Class:JBSoftwareVersion/Attribute:software_id' => 'Software',
	'Class:JBSoftwareVersion/Attribute:software_id+' => 'Software name',
	'Class:JBSoftwareVersion/Attribute:version' => 'Version',
	'Class:JBSoftwareVersion/Attribute:version+' => 'Version of this software. Could be a name, major/minor version, build number, ...',
	'Class:JBSoftwareVersion/Attribute:installations_list' => 'Installations',
	'Class:JBSoftwareVersion/Attribute:installations_list+' => 'Installations of this software version on physical or virtual devices',
	'Class:JBSoftwareVersion/UniquenessRule:unique_software_version' => 'The combination of a software (name) and version must be unique.',
	
	'Class:JBSoftwareInstallation' => 'Software Installation',
	'Class:JBSoftwareInstallation+' => 'Software installation on a physical or virtual device.',
	'Class:JBSoftwareInstallation/Name' => '%1$s | %2$s (%3$s)',
	'Class:JBSoftwareInstallation/Attribute:org_id' => 'Organization',
	'Class:JBSoftwareInstallation/Attribute:org_id+' => 'Organization',
	'Class:JBSoftwareInstallation/Attribute:softwareversion_id' => 'Software version',
	'Class:JBSoftwareInstallation/Attribute:softwareversion_id+' => 'Software version which is installed on the device',
	'Class:JBSoftwareInstallation/Attribute:license_id' => 'License',
	'Class:JBSoftwareInstallation/Attribute:license_id+' => 'License info',
	'Class:JBSoftwareInstallation/Attribute:functionalci_id' => 'Host',
	'Class:JBSoftwareInstallation/Attribute:functionalci_id+' => 'Device on which the software is installed',
	'Class:JBSoftwareInstallation/Attribute:version_details' => 'Version detail',
	'Class:JBSoftwareInstallation/Attribute:version_details+' => 'Detailed version info. Example: build info.',
	'Class:JBSoftwareInstallation/Attribute:status' => 'Status',
	'Class:JBSoftwareInstallation/Attribute:status+' => 'Status of this software installation.',
	'Class:JBSoftwareInstallation/Attribute:status/Value:production' => 'Production',
	'Class:JBSoftwareInstallation/Attribute:status/Value:implementation' => 'Implementation',
	'Class:JBSoftwareInstallation/Attribute:status/Value:obsolete' => 'Obsolete',
	'Class:JBSoftwareInstallation/UniquenessRule:unique_software_installation_per_org' => 'The combination of the software version, functional CI and organization must be unique.',
	
	'Class:JBLicense' => 'License',
	'Class:JBLicense+' => 'License',
	'Class:JBLicense/Attribute:name' => 'Name',
	'Class:JBLicense/Attribute:name+' => 'Short name of this license',
	'Class:JBLicense/Attribute:org_id' => 'Organization',
	'Class:JBLicense/Attribute:org_id+' => 'Organization to which this license belongs',
	'Class:JBLicense/Attribute:provider_org_id' => 'Provider',
	'Class:JBLicense/Attribute:provider_org_id+' => 'Organization providing this license',
	'Class:JBLicense/Attribute:softwareversion_id' => 'Software version',
	'Class:JBLicense/Attribute:softwareversion_id+' => 'Software version',
	'Class:JBLicense/Attribute:comment' => 'Comment',
	'Class:JBLicense/Attribute:comment+' => 'Comment about this license. Could be use cases, special notes, requirements such as a license USB key, ...',
	'Class:JBLicense/Attribute:start_date' => 'Start date',
	'Class:JBLicense/Attribute:start_date+' => 'Start date',
	'Class:JBLicense/Attribute:end_date' => 'End date',
	'Class:JBLicense/Attribute:end_date+' => 'End date',
	'Class:JBLicense/Attribute:reminder_date' => 'Reminder date',
	'Class:JBLicense/Attribute:reminder_date+' => 'Reminder date',
	'Class:JBLicense/Attribute:type' => 'Type',
	'Class:JBLicense/Attribute:type+' => 'License type. How is this licensed?',
	'Class:JBLicense/Attribute:type/Value:named_user' => 'Named user',
	'Class:JBLicense/Attribute:type/Value:concurrent_user' => 'Concurrent user',
	'Class:JBLicense/Attribute:type/Value:organization' => 'Organization',
	'Class:JBLicense/Attribute:type/Value:device' => 'Device',
	'Class:JBLicense/Attribute:purchase_type' => 'Purchase type',
	'Class:JBLicense/Attribute:purchase_type+' => 'Chosen purchase formula',
	'Class:JBLicense/Attribute:purchase_type/Value:one_time' => 'One time',
	'Class:JBLicense/Attribute:purchase_type/Value:limited' => 'Limited subscription',
	'Class:JBLicense/Attribute:purchase_type/Value:autorenewal' => 'Automatically renewed',
	'Class:JBLicense/Attribute:amount' => 'Amount',
	'Class:JBLicense/Attribute:amount+' => 'Amount of users, devices, organizations',
	'Class:JBLicense/Attribute:serial' => 'Serial key',
	'Class:JBLicense/Attribute:serial+' => 'Serial key',
	'Class:JBLicense/Attribute:contacts_list' => 'Licensed users',
	'Class:JBLicense/Attribute:contacts_list+' => 'Persons or Teams who use this license',
	'Class:JBLicense/Attribute:documents_list' => 'Documents',
	'Class:JBLicense/Attribute:documents_list+' => 'Documents related to this license',
	'Class:JBLicense/Attribute:installations_list' => 'Licensed devices',
	'Class:JBLicense/Attribute:installations_list+' => 'Devices using this license',
	'Class:JBLicense/UniquenessRule:unique_license_per_org' => 'The combination of the license name and organization must be unique.',
		
	'Class:lnkJBLicenseToContact' => 'Link License / Contact',
	'Class:lnkJBLicenseToContact+' => 'Link between License and Contact',
	'Class:lnkJBLicenseToContact/Name' => '%1$s | %2$s',
	'Class:lnkJBLicenseToContact/Attribute:izlicense_id' => 'License',
	'Class:lnkJBLicenseToContact/Attribute:izlicense_id+' => 'License',
	'Class:lnkJBLicenseToContact/Attribute:contact_id' => 'Contact',
	'Class:lnkJBLicenseToContact/Attribute:contact_id+' => 'Contact. Likely a Person, but could also be a Team.',
	'Class:lnkJBLicenseToContact/Attribute:comment' => 'Comment',
	'Class:lnkJBLicenseToContact/Attribute:comment+' => 'Comment. Reason of installation, downgrade rights, ... could be logged here.',
		
	'Class:JBDatabaseSchema' => 'Database Schema',
	'Class:JBDatabaseSchema/Attribute:izsoftwareinstallation_id' => 'Software installation',
	
	'Class:JBWebApplication' => 'Web Application',
	'Class:JBWebApplication/Attribute:izsoftwareinstallation_id' => 'Software installation',
	'Class:JBWebApplication/Attribute:url' => 'URL',
	
	
	// Temporary
	'Class:Software' => 'Software (original)',
	'Class:Middleware' => 'Middleware (original)',
	'Class:MiddlewareInstance' => 'Middleware instance (original)',
	'Class:PCSoftware' => 'PC Software (original)',
	'Class:OtherSoftware' => 'Other Software (original)',
	'Class:DBServer' => 'Database Server (original)',
	'Class:WebServer' => 'Web Server (original)',
	'Class:Licence' => 'License (original)',
	'Class:OSLicence' => 'OS License (original)',
	'Class:SoftwareInstance' => 'Software Instance (original)',
	'Class:SoftwareLicence' => 'Software Licence (original)',
	'Class:lnkSoftwareInstanceToSoftwarePatch' => 'Link Software Instance (original) / Software Patch',
	'Class:DatabaseSchema' => 'Database Schema (original)',
	'Class:WebApplication' => 'Web Application (original)',
	
));

