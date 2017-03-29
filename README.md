# MindbazBundle

Symfony bundle to provide a Mindbaz SwiftMailer service.

Feel free to contribute on it!

[![Build Status](https://travis-ci.org/daviddlv/MindbazBundle.svg?branch=master)](https://travis-ci.org/daviddlv/MindbazBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/daviddlv/MindbazBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/daviddlv/MindbazBundle/?branch=master)

## Installation

Installing MindbazBundle can be done easily through [Composer](https://getcomposer.org/):

```bash
composer require daviddlv/mindbaz-bundle
```

Register this bundle in your kernel:

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        new MindbazBundle\MindbazBundle(),
        // ...
    ];

    // ...
}
```

## Configuration

Edit your configuration file to specify your Mindbaz credentials & campaigns:

```yml
# config.yml
mindbaz:
    credentials:
        idSite: 123
        login: foo
        password: p4$$w0rd
    campaigns:
        register: 123
        forgot-password: 456
```

**Don't forget to change credentials in previous example!**

## Credits

Created by David DELEVOYE.
