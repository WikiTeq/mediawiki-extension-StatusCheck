The extension allows to perform on-site checks for various issues

# Requirements

* MediaWiki 1.39+

# Installation

* Clone the extension into the `extensions` directory
* Add `wfLoadExtension( 'StatusCheck' );` to the bottom of your settings file
* Configure as required

# Configuration

* `$wgStatusCheckEmailAddress` - receiving email address for email function testing

# Usage

* Navigate to `Special:StatusCheck`
* Use `statuscheck` API endpoint to run checks via API

# Bundled checks

* [x] **Stuck jobs** - verifies if there are any jobs with an excessive number of attempts
* [x] **File uploads** - verifies that files can be uploaded, no file permissions errors in place
* [x] **Email sending** - verified that email can be sent ( it sends a real email to the address specified in the configuration )
* [x] **Composer dependencies** - verifies that lock file and dependencies are in sync ( e.g., to catch situations when the wiki was updated but composer packages were not )
* [x] **Search engine check** - verified that search works and can find some text from the wiki homepage

# Permissions

* `status-check` permission allows to use the API and perform checks via Special page, granted to `sysop` group by default

# Development

* `src/Checks/BaseCheck.php` - is the base abstract class for creating new Checks
* `src/ChecksRunner.php` - class performs Checks runs
* `src/CheckResult.php` - class represents result of running a single Check

See bundled checks to learn how to create new ones, e.g.: `src/Checks/EmailSendCheck.php`
