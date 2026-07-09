# jb-software-mgmt

Copyright (c) 2019-2026 Jeffrey Bostoen

[![License](https://img.shields.io/github/license/jbostoen/iTop-custom-extensions)](https://github.com/jbostoen/iTop-custom-extensions/blob/master/license.md)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/jbostoen)
🍻 ☕

Need assistance with iTop or one of its extensions?  
Need custom development?  
Please get in touch to discuss the terms: **info@jeffreybostoen.be** / https://jeffreybostoen.be

## What?

A different, simplified and more flexible implementation of software and license management.

## Why?

It aims to overcome some challenges:

- The complexity of the software instances in iTop (E.g. the differention between database server, middleware, other software, PC software, webserver, ...).
- The overhead of OS patches and software patches. In modern environments, it usually means a new version of a specific software.
- The complexity of modern licenses (a license can cover an organization, a user, a device; it can contain downgrade rights; ...).

This aims to replace the regular iTop Software Catalog and Software instances.  
It will NOT delete the existing data from the database by default; but it will make the old info invisible for the users.  
Unfortunately, the relevant original classes in iTop are part of a large module in iTop.

The goal is to implement better software management, while keeping the flexiblity.  
For instance, the new implementation has Software.  


## New datamodel



### Software

It's up to your organization whether for instance the **software** will be named "Microsoft Windows Server" or "Microsoft Windows Server 2025 DataCenter Edition".  

Fields:

* Name.
* Vendor.
* Type.
  * Client software.
  * Cloud software.
  * Server software.
  * Operating system.
* Linked software versions.


### Software version

Fields:

* Link to software.
* Version number: Avoid using this for build numbers. For e.g. Microsoft Windows, it's okay to list 25H2 here. Build numbers should be set on the software installation (version details).
* Edition/SKU: Flexible to use. You could decide to add "2025 DataCenter Edition" only here rather than in the software's name.
* Release date
* End of life date
* Description
* Linked software installations


### Software installation

A software installation links a software version to a functional CI, and adds some extra data.


Fields:

* Link to functional CI.  
  The following types are supported:
  * Hypervisor
  * NetworkDevice
  * Printer
  * PC
  * Server
  * VirtualMachine
* Link to software version.
* Version details. Use this to register for example a build number.
* Status
  * Implementation
  * Production
  * Obsolete


### License

There is a lot of variety in licenses nowadays.  

Fields:

* Name
* Org ID
* Provider org ID
* Comment
* Start date
* End date
* Reminder date. A field that can be used to configure reminders about expirations.
* Linked software **versions** that **could** be covered by the license.  
  For example, a "Microsoft Windows Server 2025 DataCenter license" could contain downgrade rights; which means it also covers "Microsoft Windows Server 2022 DataCenter" versions etc.
* Linked software **installations** that **are** covered by the license. Meant to track device-based licensing.
* Linked contacts (teams, people) that **are** covered by the license. Meant to track user-based licensing.
* Linked documents. Meant to link uploaded support contracts, purchase documents, ... .
* Amount. How many users, devices, ... are covered.
* Serial key. The serial or license key.
* Type. What kind of license this is.
  * Named user.
  * Concurrent user.
  * Organization.
  * Device.
* Purchase type.
  * One-time. A perpetual license.
  * Limited subscription.
  * Automatically renewed.

Hint: You can also enable iTop attachments, so you can attach the license file.



# Caution
  
Caution: Installing this extension affects all the classes listed below.

* FunctionalCI
  * Replaces the tab with linked SoftwareInstance instances with JBSoftwareInstallation instances.
  * Replaces neighbour "SoftwareInstance" with JBSoftwareInstallation.

* DatabaseSchema
  * Replaces the link to the DBServer (originally a subclass of SoftwareInstance) with a link to a JBSoftwareInstallation.

* WebApplication
  * Replaces the link to the WebServer (originally a subclass of SoftwareInstance) with a link to a JBSoftwareInstallation.




The following classes are removed:

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
* Link Functional CI / OSPatch
* Link Document / Licence
* Link Document / Software





