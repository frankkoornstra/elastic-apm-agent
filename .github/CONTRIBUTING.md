# Contributing

Thanks for considering making a contribution to the PHP Elastic APM Agent! To make the process go as smooth as possible, please read this document, which hopefully explains everything you need to know.

## Before you start

To make sure you don't waste your time, the change is valuable for the rest of the users, fits in with the vision and to discuss the plan of attack, please open an issue first. There you can explain what you're trying to achieve and how you're going to do that.

## Environment

The only thing you need is Docker. There's a `docker-compose.yml` file that will spin up Elastic's Elasticsearch and APM Server as well as a PHP7 container so you can run your tests.

## Running style checks and tests

There's a `makefile` in the repository to make it easy to run all style checks and tests. run `make` in your console to run everything. This file is also used in CircleCI to run tests when you submit a PR so if it runs locally, you should be good to go for your PR.

The code adheres to [these coding standards](https://github.com/frankkoornstra/coding-standard-php) (pretty standard) and uses PHPStan on the highest level for source code checks.

## Branches

We only have a `master` branch, pretty easy, so branch off of that. There's no naming scheme for branches but pick something snappy that explains what the goal of the changes in that branch is.

## Commits

I'm a bit picky when it comes to commits, so please makes sure your commit:
- is **atomic**, meaning it entails changes in a set of files that can be seen as one unit. Take a look at [this article](https://www.freshconsulting.com/atomic-commits/) if you want to know more.
- uses a commit message as defined in [this article](https://chris.beams.io/posts/git-commit/), tldr:
    - Separate subject from body with a blank line.
    - Limit the subject line to 50 characters.
    - Capitalize the subject line.
    - Do not end the subject line with a period.
    - Use the imperative mood in the subject line.
    - Wrap the body at 72 characters.
    - Use the body to explain what and why vs. how. 

## Merging

1. All changes need to be merged in via a PR.
1. The option _Rebase and Merge_ should be used for all merges.
1. PRs require builds to be passing.
1. PRs require a milestone and appropriate labels to be added in Github.
1. Documentation is up-to-date with the status of the code

## Releasing and Tagging

All releases are tagged in git with a signed tag (`git tag -s vX.Y.Z`). We follow semver so:
- for _bugs_ increment the patch version, ie `v1.10.1`
- for _new features_ increment the minor version, ie `v1.11.0`
- for _breaking changes_, increment the major version, ie `v2.0.0`


