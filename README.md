# pamald

[![CircleCI](https://circleci.com/gh/pamald/pamald/tree/1.x.svg?style=svg)](https://circleci.com/gh/pamald/pamald/?branch=1.x)
[![codecov](https://codecov.io/gh/pamald/pamald/branch/1.x/graph/badge.svg?token=HSF16OGPyr)](https://app.codecov.io/gh/pamald/pamald/branch/1.x)

**Pa**ckage **Ma**nager **L**ock **D**iff = pamald \
Generates a report about the diff between two package manager lock file content.


## Features

- Compare dependencies between two lock files
- Detect version changes, additions, and removals
- Identify direct vs. transitive dependency changes
- Generate reports in multiple formats
  - Symfony Console Table
  - Markdown Table
  - JIRA Table
  - JSON
  - custom reporters can be added easily
- Every reporter has its own options to customize the output
  - Show/hide columns
  - Group by (Your responsibility to group the entries)
  - Filter by (Your responsibility to filter the entries)
  - Sort by (Your responsibility to sort the entries)


## Report Examples

Pamald can generate reports in various formats to help you understand what changed between two lock files.


### Report Examples - Markdown Table

```markdown
| Name | L Version | R Version | L Type   | R Type   | L Link   | R Link   | L Env      | R Env      | L Depth | R Depth |
|------|-----------|-----------|----------|----------|----------|----------|------------|------------|---------|---------|
| php  | 8.3.0     | 8.4.0     | platform | platform | required | required | production | production | direct  | direct  |
| a/b  | 2.2.3     | 2.2.4     | package  | package  | required | required | production | production | child   | direct  |
```


### Report Examples - JSON

```json
{
    "php": {
        "name": "php",
        "isTypeChanged": false,
        "isLinkChanged": false,
        "isEnvironmentChanged": false,
        "relationshipAction": "none",
        "isDirectDependencyChanged": false,
        "versionAction": "upgrade",
        "isVersionChanged": true,
        "versionPartChanged": "minor",
        "isVersionMajorChanged": false,
        "isVersionMinorChanged": true,
        "isVersionPatchChanged": true,
        "isVersionPreReleaseChanged": true,
        "isVersionMetadataChanged": true,
        "left": {
            "name": "php",
            "type": "platform",
            "link": "required",
            "environment": "production",
            "versionString": "8.3.0",
            "isDirectDependency": true
        },
        "right": {
            "name": "php",
            "type": "platform",
            "link": "required",
            "environment": "production",
            "versionString": "8.4.0",
            "isDirectDependency": true
        }
    }
}
```

## Usage

This example code generates the same report in three different formats.
```php
<?php

declare(strict_types = 1);

use Pamald\Pamald\LockDiffer;
use Pamald\Pamald\Reporter\MarkdownTableReporter;
use Pamald\Pamald\Reporter\JsonReporter;
use Pamald\Pamald\Reporter\JiraTableReporter;

// It is your responsibility to load the lock files.
// See packages:
// - pamald/pamald-composer
// - pamald/pamald-npm
// - pamald/pamald-yarn
$leftDependencies = [];
$rightDependencies = [];

$lockDiffer = new LockDiffer();
$diffEntries = $lockDiffer->diff($leftDependencies, $rightDependencies);

$markdownReporter = new MarkdownTableReporter();
$markdownReporter->setStream(\STDOUT);
$markdownReporter->generate($diffEntries);

$jsonReporter = new JsonReporter();
$jsonReporter->setStream(\STDOUT);
$jsonReporter->generate($diffEntries);

$jiraReporter = new JiraTableReporter();
$jiraReporter->setStream(\STDOUT);
$jiraReporter->generate($diffEntries);
```


## Links

- [pamald/pamald]
- [pamald/pamald-composer]
- [pamald/pamald-npm]
- [pamald/pamald-yarn]
- [pamald/robo-pamald]
- [pamald/robo-pamald-composer]
- [pamald/robo-pamald-npm]
- [pamald/robo-pamald-yarn]


[pamald/pamald]: https://github.com/pamald/pamald
[pamald/pamald-composer]: https://github.com/pamald/pamald-composer
[pamald/pamald-npm]: https://github.com/pamald/pamald-npm
[pamald/pamald-yarn]: https://github.com/pamald/pamald-yarn
[pamald/robo-pamald]: https://github.com/pamald/robo-pamald
[pamald/robo-pamald-composer]: https://github.com/pamald/robo-pamald-composer
[pamald/robo-pamald-npm]: https://github.com/pamald/robo-pamald-npm
[pamald/robo-pamald-yarn]: https://github.com/pamald/robo-pamald-yarn
