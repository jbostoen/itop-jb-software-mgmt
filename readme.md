# jb-software-mgmt

Copyright (c) 2019-2023 Jeffrey Bostoen

[![License](https://img.shields.io/github/license/jbostoen/iTop-custom-extensions)](https://github.com/jbostoen/iTop-custom-extensions/blob/master/license.md)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/jbostoen)
🍻 ☕

Need assistance with iTop or one of its extensions?  
Need custom development?  
Please get in touch to discuss the terms: **info@jeffreybostoen.be** / https://jeffreybostoen.be

## What?

A different implementation of software and license management.

This aims to replace the regular iTop Software Catalog and Software instances.
It will NOT delete the existing data from the database by default; but it will make the old info invisible for the users.

The idea is to implement better software management:
* The software name could be something generic ("MS Office") or a only slightly more specific ("MS Office 2019").
* More detailed version info could be set on the software instance.
* Licenses could be linked to users, devices and/or software instances.
  * This covers downgrade rights.  
    With this extension, you can link a "Microsoft Windows Server 2019 Standard" license to both "Microsoft Windows Server 2019 Standard" and "Microsoft Windows Server 2016 Standard".
  * This covers usage rights, such as "Microsoft Windows Server User CAL".
  
Caution: Installing this extension affects all the classes listed below.
Except for FunctionalCI, they all get ```( original)``` appended to their class name (label) in iTop.

* FunctionalCI
  * Replaces Software list (AttributeLinkedSet): SoftwareInstance -> JBSoftwareInstallation.
  * Replaces neighbour "SoftwareInstance" with JBSoftwareInstallation.

* DatabaseSchema
* Patch
  * OSPatch
  * SoftwarePatch
* SoftwareInstance
  * DBServer
  * Middleware
  * OtherSoftware
  * PCSoftware
  * WebServer
* WebApplication


## Todo
* DatabaseSchema => Original DBServer
* JBsoftwareinstallation => parent 'FunctionalCI'


