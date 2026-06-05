We build great development environment to make development faster with better code quality. There are the best available tools allow us to make development more productive and collaborative. Also there is an overview of the enhancements in the code and development environment to make **UNA** the better version ever released.

**1. Code, tickets (now issues) are on [GitHub](https://github.com/)**

GitHub allows to make code better and more secure, now everyone can help us by committing pull request. We check each pull request very carefully and accept only quality code.

**2. Automated unit tests**

Code is automatically tested upon each new code push. However code coverage is low for now, but we will add more unit test in the future. Unit test are powered by [PHPUnit](https://phpunit.de/)

**3. Automatic packaging**

Upon every new code push, ready to install version is packaged. It's not even nightly builds, it prepares new package as soon as new code is added to the repository, even every 2 minutes. So you can always get the latest installable development version and test it for the new features and report bugs. It will allow us to fix reported bugs even before beta version.

**4. Automatic live demo install**

Live demo site is installed upon each new code push to **GitHub**. So if you don't want to download and install development version by your own, then live version is always available for tests. It is reset and reinstalled as soon as new code is available. Hopefully this will allow to detect any bugs very early to make future beta and final release bug free as much as possible.

**5. Automatic code quality analyzation tools**

Code is continuously tested for any duplicated code. We already cleaned the code to get rid of any duplicates:

![UNA duplicate code trend](images/code-quality-duplicate-code-trend.png)

and watching for any new ones, if it is occasionally added.

**6. Continuous integration** 

Everything above is working together (thanks to [Jenkins](https://jenkins.io/index.html)) and history of changes is saved and presented as visual  graphs. 
Importantly it immediately notify developer by email if they commit code which is not installable or some test are failed, making them to fix problem ASAP.

**7. Command line tools**

**UNA** can be installed with just one command line with any set of modules, or modules can be installed using command line script. So testing separate install is really easy. Also I believe it will make life easier for hosting operators, allowing them install dolphin just in seconds. 
It will also make integration with automated install tools more consistent since the same script is used to install UNA using build-in wizard, or command line install by operator, or using automated install tools.

**8. Highest error reporting level during development**

UNA is developed with **E_ALL error** reporting level, so even notices are not emitted anymore.

**9. Standardised code style**

[PSR-1 code style standard](http://www.php-fig.org/psr/psr-1/) was used. Also automated script was used to clean-up existing code.

**10. No evals in the code**

We cleaned-up any evals from the code and DB to make code more secure and easier to debug.

**11. Prepared SQL statements**

All SQL queries were rewritten to use prepared statements. If properly used then no SQL injection is possible at all. We encourage others to use prepared statements as well.

**12. Proper file inclusion check** 

Every file has inclusion check, so PHP files which are supposed for inclusion can't be accessed directly. It will make code more secure in general.

**13. Code review** 

We review every line of newly added code, before it goes to production.

**14. Internal powerful collaboration tools** 

It allows us to solve any problems quickly, thanks to modern technologies which helps to communicate within the team, even if someone isn't on the work place.

**15. Testing issues on almost every possible device using [BrowserStack](http://browserstack.com/)**

![BrowserStack](images/browserstack-logo-300x158.png)

**16. UNA security practices**

- Users
  - User can delete all his data,  right to be forgotten - standards compliance
  - Multilevel ACL (users, admins, operators) - fine grained permissions
- Code 
  - HTML/SVG Input validation (HTMLPurifier & svg-sanitizer) - there is no any potentially dangerous code is submitted
  - Output escaping - prevent XSS attacks
  - No evals in PHP code
  - Proper file inclusion check - so there are only several "entry" php files, other code isn't directly accessible
- DB
  - Prepared statements are always used to prevent SQL injections attacks
  - Hashed  passwords - no clear passwords are stored
  - Hashed personal data (like IPs) - minimal personal data is stored , no users tacking
- Forms
  - CSRF tokens to prevent forms auto submission and double submissions
  - Form leave notifications - to prevent accidental data loss
- Cookies
  - HTTP only & secure flags - it's impossible so steal sensitive cookies
  - Cookies consent (optional) - it's complies with standards
- CI/CD
  - Unit tests - PHP code testing
  - UI Tests - frontend functionality  testing
  - Duplicate code scans 
- Code scans
  - Dependabot - early notification of vulnerabilities in 3rd-party libraries
  - Coderabbit - smart code review
  - Snyk, Sonarcube - code scan for potential problems



