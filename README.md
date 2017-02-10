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

### Configure Gitlab CI yml file

