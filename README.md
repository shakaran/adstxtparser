# AdsTxtParser

[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)][3]
[![Build Status](https://travis-ci.org/shakaran/adstxtparser.svg?branch=master)](https://travis-ci.org/shakaran/adstxtparser)
[![Latest Stable Version](https://poser.pugx.org/shakaran/adstxtparsere/v/stable.png)](https://packagist.org/packages/shakaran/adstxtparser)
[![Latest Unstable Version](https://poser.pugx.org/shakaran/adstxtparser/v/unstable)](https://packagist.org/packages/shakaran/adstxtparser)
[![License](https://poser.pugx.org/shakaran/adstxtparser/license)](https://packagist.org/packages/shakaran/adstxtparser)


[![Total Downloads](https://poser.pugx.org/shakaran/adstxtparser/downloads.png)](https://packagist.org/packages/shakaran/adstxtparser)
[![Monthly Downloads](https://poser.pugx.org/shakaran/adstxtparser/d/monthly)](https://packagist.org/packages/shakaran/adstxtparser)
[![Daily Downloads](https://poser.pugx.org/shakaran/adstxtparser/d/daily)](https://packagist.org/packages/shakaran/adstxtparser)



A open source implentation in PHP of [Ads.txt Specification Version 1.0.1](https://iabtechlab.com/wp-content/uploads/2017/09/IABOpenRTB_Ads.txt_Public_Spec_V1-0-1.pdf) (OpenRTB working group)

![Graph Ads.txt](https://i.imgur.com/NnVCHz9.png)

## WHAT IS ADS.TXT? ##

Ads.txt is an initiative by the Interactive Advertising Bureau to enable publishers to take control over who can sell their ad inventory.

Since 01 Nov 2017 Google is filtering traffic based on ads.txt

It is a simple logic of the textfile as mechanism to fight adfraud

Widely accepted and implemented by more and more publishers, ssp and dsp.

Can prevent advertisers from buying invalid traffic.

[Check more info](https://iabtechlab.com/ads-txt/) about it.

## Donate ##

I am a freelancer [Senior Backend Engineer](https://upwork.com/fl/angelguzmanmaeso#/) working on my free time for develop this.

If you want pay me some beer or severals to appreciate the efforts:

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=KY8JY46S5WPWS)

## Setup ##

Install with composer with only:

```cli
composer require shakaran/adstxtparser
```

## TECHNICAL NOTES ##

Requires PHP 7.0+.

## How to use ##

A quick use to instanciate and run over a domain is:

```php

use AdsTxtParser\Parser;

$parser = new Parser();
$parser->readExternalFile('http://estaticos.elmundo.es');
```

Note: that you only have to pass as argument the domain.

After you already fetch the data in the Parser object,
you can query about errors:

```php
$errors = $parser->getErrors();
```

Or warnings:

```php
$warnings = $parser->getWarnings();
```

Or get the variables defined:

```php
$variables = $parser->getVariables();
```

Or get the fields defined:

```php
$fields = $parser->getFields();
```

Or get the comments defined:

```php
$comments = $parser->getComments();
```

Even more complex operations, as know the list of resellers:

```php
$resellers = $parser->getResellers();
```

Or the list or directs:

```php
$directs = $parser->getDirects();
```

A complex example using all the options could be:

```php
try
        {
            // By default localhost
            $parser = new Parser();
            $parser->readExternalFile('http://estaticos.elmundo.es');

            // More examples to test:
            // https://elpais.com/ads.txt
            // https://www.elconfidencial.com/ads.txt
        }
        catch(AdsFileNotFound $e)
        {
            echo $e->getMessage();
        }

        $comments = $parser->getComments();
        // var_dump($comments);

        $errors = $parser->getErrors();
        //var_dump($errors);

        $warnings = $parser->getWarnings();
        // var_dump($warnings);

        $fields = $parser->getFields();
        // var_dump($fields);

        $variables = $parser->getVariables();
        //var_dump($variables);

        $resellers = $parser->getResellers();
        //var_dump(count($resellers));

        $directs = $parser->getDirects();
        //var_dump(count($directs));
```

## Tests ##

Executing the tests:

Run a local webserver under Test directory:

```cli
cd Tests; sudo php -S localhost:80
```

```php
vendor/phpunvendor/phpunit/phpunit/phpunit --configuration=Tests/phpunit.xml --include-path=Tests
```

## License ##

AGPLv3 or later, see [LICENSE](LICENSE) file for more details.

[3]: https://github.com/shakaran/adstxtparser/issues?utf8=%E2%9C%93&q=is%3Aopen%20is%3Aissue
