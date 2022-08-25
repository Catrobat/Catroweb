This step-by-step guide will support you on your way to your first pull request (PR) at Catrobat. To achieve this, we will guide you through the following steps:
1. Workspace Setup
2. Finding your first Ticket
3. Implementing your first ticket using Catrobat's Contribution Workflow

# 1. Workspace Setup
First, we will show you how to set up your working environment and verify the correctness of this setup.


## a. Installing the IDE and Tools
We recommend using [PhpStorm](https://www.jetbrains.com/phpstorm/) as all our contributors do. Please find the IDE version suitable for your operating system on the website and install it on your computer.


## b. Repository Setup
### (I) Git Setup
At Catrobat, we use Git to keep track of changes in our codebase. If you have not installed Git on your computer yet, please follow the [official guide to set up Git](https://docs.github.com/en/get-started/quickstart/set-up-git).

### (II) Catrobat's Forking Workflow
To enable the contribution of people like you, we decided to use a forking workflow. In a nutshell, this works as follows. First, everyone who wants to contribute creates (=forks) a personal copy of our repository (=fork). The contributor then makes changes on his fork and informs the community about the changes via a PR. A core contributor will review the changes in the PR. If the changes are accepted, the core contributor will merge the changes into the original repository of Catrobat.
If you are unfamiliar with Git or have not used it recently, the official guide about [forking a repository](https://docs.github.com/en/get-started/quickstart/fork-a-repo) is a good starting point.

### (III) Setting up your Fork
Now that you know how to work with Git, it is time to set up your fork by executing the following steps. We recommend to use Docker for an easy and seamless setup process:
- [Install Docker](https://github.com/Catrobat/Catroweb/wiki/Docker#install-docker)
- [Fork](https://docs.github.com/en/get-started/quickstart/fork-a-repo#forking-a-repository) the [repository of Catroweb](https://github.com/Catrobat/Catroweb)
- [Clone](https://github.com/Catrobat/Catroweb/wiki/Docker#checkout-the-catroweb-project) the repository

You can find more information about using Catroweb with Docker [here](https://github.com/Catrobat/Catroweb/wiki/Docker#introduction-to-catroweb-with-docker).


## c. Setup Verification
Now you should be able to [run Catroweb in Docker](https://github.com/Catrobat/Catroweb/wiki/Docker#running-catroweb-dev-in-docker) to display the sharing platform similar to our [current version online](https://share.catrob.at/app/).

Note: Make sure that you run the commands through [Git Bash in PhpStorm](https://medium.com/code-complete/using-git-bash-with-phpstorm-10f8d54a96da).



# 2. Finding your first Ticket


## a. Catrobat's Jira Workflow
At Catrobat, we use Jira to keep track of all issues (stories, tasks, and bugs) in our projects. You can find the Jira project of Catroweb [here](https://jira.catrob.at/projects/SHARE/issues/SHARE-527?filter=allopenissues).
If you click "Kanban Board" on the left menu in Jira, you will get an overview of what we are currently working on. You can see that different issues have different statuses (e.g., "Ready for Development"). The collection of all statuses makes up our Jira workflow that transparently shows the project's current state to every team member. You can find an overview of our Jira workflow if you click on an issue and the "(View Workflow)" next to the status field.
It is crucial to follow this workflow to keep the team informed about what you are currently working on.


## b. Choosing a suitable Ticket
We prepared a [beginner ticket](https://jira.catrob.at/browse/SHARE-532) for our newcomers that should be easy to implement. You will find all the necessary information in the ticket.


## c. Informing the Community
As mentioned earlier, it is essential to keep the team updated. As you do not have the permissions for our Jira project yet, you cannot change the status of the issue you chose. Instead, please assign the ticket to yourself by commenting on it using the following template.

```diff
"I am starting to work on this issue. For the next 10 days, this ticket is assigned to me. If I am not able to create a pull request within 10 days, anybody else can take over this issue."
```

After you have finished your work and submitted a pull request, you have to use the following template to request a review from our community:

```diff
"@[Name of the responsible reviewer as mentioned in the beginner ticket] please review my pull request [Link to PR on GitHub (e.g., https://github.com/Catrobat/Catroid/pull/4580)]."
```

💡 In the upcoming steps, you will find the general workflow of the project. As you do not have permissions for Jira and Confluence yet, please skip all actions that involve Jira ticket status updates and additional information on Confluence!



# 3. Catrobat's Contribution Workflow
The general workflow of the project involves the following steps:
- Claim a Ticket
- Do the Work
- Submit the Changes

Please refer to our [contribution guide](https://github.com/Catrobat/Catroweb/blob/develop/.github/contributing.md) to receive step-by-step guidance throughout your contribution.
