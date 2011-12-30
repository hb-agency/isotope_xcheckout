ModuleIsotopeXCheckout
======================

An advanced checkout module for Isotope eCommerce. Uses AJAX to load shipping and payment modules for a nice one or two-screen checkout process.

General Usage
-------------
- Creating guest members: Requires isotope\_member extension in order to process guest account creation. Be sure to enable "Create member: Guest" in your store config until isotope\_member supports the guest option. XCheckout adds in password fields to the billing address during checkout to allow the guest to set their own account password.
- Note that with this extension members will be automatically activated. Will make this a configurable option

Changelog
---------

* v0.1.4 -- 12/30/2011 - Added in HTML5 templates.
* v0.1.3 -- 12/29/2011 - Working version. Added AJAX update of review screen.
* v0.1.2 -- 12/29/2011 - Added in member password generation with a supplement for the missing "guest" option in isotope_member, also fixes for review stage
* v0.1.1 -- 12/27/2011 - AJAX now working. Added comments and additional checks for AJAX submissions
* v0.1.0 -- 12/26/2011 - Initial commit