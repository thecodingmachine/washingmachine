[![Latest Stable Version](https://poser.pugx.org/thecodingmachine/washingmachine/v/stable)](https://packagist.org/packages/thecodingmachine/washingmachine)
[![Total Downloads](https://poser.pugx.org/thecodingmachine/washingmachine/downloads)](https://packagist.org/packages/thecodingmachine/washingmachine)
[![Latest Unstable Version](https://poser.pugx.org/thecodingmachine/washingmachine/v/unstable)](https://packagist.org/packages/thecodingmachine/washingmachine)
[![License](https://poser.pugx.org/thecodingmachine/washingmachine/license)](https://packagist.org/packages/thecodingmachine/washingmachine)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecodingmachine/washingmachine/badges/quality-score.png?b=1.0)](https://scrutinizer-ci.com/g/thecodingmachine/washingmachine/?branch=1.0)
[![Build Status](https://travis-ci.org/thecodingmachine/washingmachine.svg?branch=1.0)](https://travis-ci.org/thecodingmachine/washingmachine)
[![Coverage Status](https://coveralls.io/repos/thecodingmachine/washingmachine/badge.svg?branch=1.0&service=github)](https://coveralls.io/github/thecodingmachine/washingmachine?branch=1.0)


# Washing machine

**Work in progress**

The *washing machine* is a tool that helps you writing cleaner code by integrating PHPUnit with Gitlab CI.

As a result, when you perform a merge request in Gitlab, the washing machine will add meaningful information about your code quality.

## Usage

### Enable Gitlab CI for your project

First, you need a Gitlab project with continuous integration enabled (so a project with a `.gitlab-ci.yml` file).

### Create a personal access token

Then, you need a [Gitlab API personal access token](https://docs.gitlab.com/ce/api/README.html#personal-access-tokens).

Got it?

### Add a secret variable

Now, we need to add this token as a "secret variable" of your project (so the CI script can modify the merge request comments):

Go to your project page in Gitlab: **Settings ➔ Variables ➔ Add variable**

- Key: `GITLAB_API_TOKEN`
- Value: the token you just received in previous step

### Configure PHPUnit to dump a "clover" test file


Let's configure PHPUnit. Go to your `phpunit.xml.dist` file and add:

```
<phpunit>
    <logging>
        <log type="coverage-clover" target="clover.xml"/>
    </logging>
</phpunit>
```

Note: the "clover.xml" file must be written at the root of your GIT repository, so if your `phpunit.xml.dist` sits in a subdirectory, the correct path will be something like "../../clover.xml".

Alternatively, washing-machine also knows how to read Crap4J files. Crap4J files contain Crap score, but not code coverage score so you will get slightly less data from Crap4J. The expected file name is "crap4j.xml".

### Configure Gitlab CI yml file

Now, we need to install the *washingmachine*, and get it to run.

`.gitlab-ci.yml`
```
before_script:
 - composer global require 'thecodingmachine/washingmachine'
 
script:
 - phpdbg -qrr vendor/bin/phpunit
 
after_script:
 - /root/.composer/vendor/bin/washingmachine run -v
```

Notice that we need to make sure the PHPDbg extension for PHP is installed. Also, make sure that Xdebug is NOT enabled on your Docker instance. Xdebug can also return code coverage data but is less accurate than PHPDbg, leading to wrong CRAP score results.
