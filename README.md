The extension allows to perform on-site checks for various issues

# Requirements

* MediaWiki 1.35+

# Installation

* Clone the extension into the `extensions` directory
* Add `wfLoadExtension( 'StatusCheck' );` to the bottom of your settings file
* Configure as required

# Configuration

* `$wgStatusCheckEmailAddress` - receiving email address for email function testing

# Usage

* Navigate to `Special:StatusCheck`
* Use `statuscheck` API endpoint to run checks via API

# Development

* `src/Checks/BaseCheck.php` - is the base abstract class for creating new Checks
* `src/ChecksRunner.php` - class performs Checks runs
* `src/CheckResult.php` - class represents result of running a single Check
* `src/RunResult.php` - represents result of running batch of checks via ChecksRunner

See bundled checks to learn how to create new ones, e.g.: `src/Checks/EmailSendCheck.php`
