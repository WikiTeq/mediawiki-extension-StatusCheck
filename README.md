The extension allows to perform on-site checks for various issues

# Requirements

* MediaWiki 1.35+

# Installation

* Clone the extension into the `extensions` directory
* Add `wfLoadExtension( 'StatusCheck' );` to the bottom of your settings file
* Configure as required

# Configuration

* `$wgStatusCheckEmailAddress` - receiving email address for email function testing
* `$wgStatusCheckDefaultSet` - list of Checks classes to include into default run

# Usage

* Navigate to `Special:StatusCheck`
* Use `statuscheck` API endpoint to run checks via API

# Bundled checks

* **Stuck jobs** - verifies if there are any jobs with an excessive number of attempts
* **File uploads** - verifies that files can be uploaded, no file permissions errors in place
* **Email sending** - verified that email can be sent ( it sends a real email to the address specified in the configuration )
* **Composer dependencies** - verifies that lock file and dependencies are in sync ( e.g., to catch situations when the wiki was updated but composer packages were not )
* **Search engine check** - verified that search works and can find some text from the wiki homepage

# Development

* `src/Checks/BaseCheck.php` - is the base abstract class for creating new Checks
* `src/ChecksRunner.php` - class performs Checks runs
* `src/CheckResult.php` - class represents result of running a single Check
* `src/RunResult.php` - represents result of running batch of checks via ChecksRunner

See bundled checks to learn how to create new ones, e.g.: `src/Checks/EmailSendCheck.php`
