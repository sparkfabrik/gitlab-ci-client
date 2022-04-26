# Gitlab CI client

This is a Gitlab API wrapper that helps to automate common actions on CI jobs (eg: Open a merge request, Open or close an issue etc)

## Basic Usage

```bash

docker run -e GITLAB_PRIVATE_TOKEN=<YOUR-ACCESS_TOKEN> -e CI_SERVER_URL=<YOUR-GITLAB-CI_SERVER_URL> sparkfabrik/gitlab-ci-client mergeRequests.all null {state:opened}
```
The above command will return all opened merge requests.

## How it works
The first argument defines which API resource you want to use and which action you want to perform.

Here's a list of all available resources:
- deployKeys
- deployments
- environments
- groups
- groupsBoards
- groupsEpics
- groupsMilestones
- issueBoards
- issueLinks
- issues
- issuesStatistics
- jobs
- keys
- mergeRequests
- milestones
- namespaces
- projects
- repositories
- repositoryFiles
- schedules
- snippets
- systemHooks
- tags
- users
- version
- wiki

From the second argument onwards you can define as many arguments as you want and those will be passed to the resource call.

It is also possible to pass complex arguments when there's a need to pass for a single argument an associative array, for example. It can be done by using a JSON like string.

```
mergeRequests.all 123 {state:opened}
```

or with multiple array entries like that:
```
mergeRequests.all 123 "{state:opened;author_id:22;iids:[234,456,789]}"
```

Please note that unlike JSON, the attribute separator here is the semicolon (;) and not the comma (,).
Comma is used to separate multiple arguments like in an indexed array.

This logic is in really early stage of development and it will be improved in the future.


Each resource has its own set of actions and common actions among all resources could have different set of arguments.
This work is based on https://github.com/GitLabPHP/Client project and to know all available actions for 
each resource please refer to available methods in the classes defined here: https://github.com/GitLabPHP/Client/tree/11.8/src/Api

For full list of available resources and methods,
please refer to the official [documentation](https://docs.gitlab.com/ee/api/).
