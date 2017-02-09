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

TODO