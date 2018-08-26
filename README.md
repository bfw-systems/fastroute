bfw-fastroute
=

[![Build Status](https://travis-ci.org/bulton-fr/bfw-fastroute.svg?branch=2.0)](https://travis-ci.org/bulton-fr/bfw-fastroute) [![Coverage Status](https://coveralls.io/repos/github/bulton-fr/bfw-fastroute/badge.svg?branch=2.0)](https://coveralls.io/github/bulton-fr/bfw-fastroute?branch=2.0) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bulton-fr/bfw-fastroute/badges/quality-score.png?b=2.0)](https://scrutinizer-ci.com/g/bulton-fr/bfw-fastroute/?branch=2.0)
[![Latest Stable Version](https://poser.pugx.org/bulton-fr/bfw-fastroute/v/stable)](https://packagist.org/packages/bulton-fr/bfw-fastroute) [![License](https://poser.pugx.org/bulton-fr/bfw-fastroute/license)](https://packagist.org/packages/bulton-fr/bfw-fastroute)

Router module for the framework BFW. Use the lib [fastRoute](https://github.com/nikic/FastRoute)

---

__Install :__

You can use composer to get the module : `composer require bulton-fr/bfw-fastroute @stable`

And to install the module : `./vendor/bin/bfwInstallModules`

__Config :__

All config file for this module will be into `app/config/bfw-fastroute/`. There is one file to configure (manifest.json is for the module update system).

The file routes.php
* `routes` : All routes defined. You have an exemple for the format to use. For the key "target", referer you to the controller module used.

__Example :__

Extract from the [BFW wiki](https://bfw.bulton.fr/wiki/v3.0/fr/scripts-d-exemple#web), an exemple of the config file to use with the module [bfw-controller](https://github.com/bulton-fr/bfw-controller).

```php
<?php
return [
    'routes' => [
        '/test' => [
            'target' => ['\Controller\Test', 'index']
        ]
    ]
];
```
