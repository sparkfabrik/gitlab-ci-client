# Gitlab CI client

This is a Gitlab API wrapper that helps to automate common actions on CI jobs (eg: Open a merge request, Open or close an issue etc)

## Basic Usage

```bash

docker run \
  -e GITLAB_PRIVATE_TOKEN=<YOUR-ACCESS_TOKEN> \
  -e CI_SERVER_URL=<YOUR-GITLAB-CI_SERVER_URL> \
  sparkfabrik/gitlab-ci-client mergeRequests.all "[null,{\"state\":\"opened\"}]"
```
The above command will return all opened merge requests.

### Command format

```bash
gitlab-ci-client <RESOURCE>.<METHOD>(required arg: string) "[arg1,arg2,...,argN]" (optional arg: array of arguments as json string)
```

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

The second argument is a json string that represents an array or arguments.
This array is passed to the API as a query string.


Each resource has its own set of actions and common actions among all resources could have different set of arguments.
This work is based on https://github.com/GitLabPHP/Client project and to know all available actions for 
each resource please refer to available methods in the classes defined here: https://github.com/GitLabPHP/Client/tree/11.8/src/Api

For full list of available resources and methods,
please refer to the official [documentation](https://docs.gitlab.com/ee/api/).

## Gitlab CI pipeline
It is possible to use this client to automate steps in a CI pipeline. The only required action is to 
create an Access Token for your project and set a CI/CD Variable with the name `GITLAB_PRIVATE_TOKEN`. The scope required is "api".
This will grant access to your repository and all its resources.

Once done you can run as any other script in your gitlab-ci step.
### Examples in gitlab-ci pipeline
```yaml
example-gitlab-ci-job:
  stage: build
  script:
    - |
      docker run \
        -e GITLAB_PRIVATE_TOKEN \
        -e CI_SERVER_URL \
        sparkfabrik/gitlab-ci-client mergeRequests.create \
        '[$CI_PROJECT_ID,"autobranch/$CI_PIPELINE_ID","develop","AUTO: New merge request from pipeline $CI_PIPELINE_ID"]'
```
