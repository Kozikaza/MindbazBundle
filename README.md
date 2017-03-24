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

Edit your configuration files to specify your Mindbaz credentials:

```yml
# parameters.yml
mindbaz_api_key: FILL_ME
mindbaz_site_id: FILL_ME
mindbaz_login: FILL_ME
mindbaz_password: FILL_ME
```

```yml
# config.yml
mindbaz:
    options:
        api_key: %mindbaz_api_key%
        site_id: %mindbaz_site_id%
        login: %mindbaz_login%
        password: %mindbaz_password%
```

You can also override the Mindbaz default WSDL url (default is
[http://webservice.mindbaz.com/Campaign.asmx?WSDL](http://webservice.mindbaz.com/Campaign.asmx?WSDL)):

```yml
mindbaz:
    wsdl: http://example.com/Campaign.asmx?WSDL
    options:
        api_key: %mindbaz_api_key%
        site_id: %mindbaz_site_id%
        login: %mindbaz_login%
        password: %mindbaz_password%
```

## Credits

Created by David DELEVOYE.
