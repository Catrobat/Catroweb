### What are GitHub Actions, and why should you use it?

[GitHub Actions](https://github.com/features/actions) are a service provided by [GitHub](https://github.com/), available for every repository on GitHub, to **automate any workflow during the software development life cycle**. GitHub Actions are optimized to work with GitHub repositories and GitHub events. Besides, the feedback is directly integrated into the GitHub web-interface.

By automating your workflows, you highly **reduce developers' manual workload**, and your work is **less prone to human errors**. Hence, GitHub Actions can significantly **reduce development costs**. The **reduced complexity** allows even non-developers to handle tasks with ease. Furthermore, the code to automate a workflow provides **implicit documentation** for future developers, preventing valuable information from being lost.

### But what do GitHub Actions cost?

For open-source projects hosted in **public repositories** on GitHub, the service is entirely **free**. No monthly payments are necessary. Besides, there are **zero acquisition costs for the infrastructure** and **low maintainance costs** to run your workflows, since the GitHub team maintains all the infrastructure, no valuable resources must be invested in maintaining your automation services.

### Sounds too good to be true - What are the drawbacks?

There are certain limitations. For example, with free services, **only 20 machines** can run in parallel. Besides, a workflow can not run longer than 72 hours. However, usually, that is more than enough.
Moreover, projects using the **forking workflow only have limited writing permission** and access to credentials hidden in GitHub Secrets.
Fortunately, it is possible to create workarounds by using different events.

### So, how can I create GitHub Actions?

GitHub Actions are written in a data-serialization language commonly used for configuration files, called "YAML Ain't Markup Language" (YAML).
Every workflow is located directly in a project's repository in the `.github/workflows` directory. GitHub Actions run either on Linux, Windows, macOS, or even in a Docker container. Hence, you can set up the environment as you wish and create a script to run your workflow in any language you'd like. However, there are two different possibilities to create a workflow: reusing an existing action or building one from scratch.

There already exists detailed [documentation on how to create workflows](https://docs.github.com/en/actions/configuring-and-managing-workflows/configuring-a-workflow). Hence, the basics of creating a workflow are not discussed in too much detail. This tutorial focuses on aspects, such as design desicions, that are not explicitly stated in the documentation.

### Should I write every action from scratch?

Hell **no**. Why re-invent the wheel all over again. **Reusing actions** saves valuable time. For example, there are actions to [cache runs](https://github.com/marketplace/actions/cache), [upload](https://github.com/marketplace/actions/upload-a-build-artifact)/[download](https://github.com/actions/download-artifact) files(artifacts), [synchronize (Crowdin) translations](https://github.com/marketplace/actions/crowdin-action), or even work with the GitHub API to [create a pull request or post a comment](https://github.com/marketplace/actions/create-or-update-comment). Check out the [GitHub marketplace](https://github.com/marketplace) or their [Hackathons](https://githubhackathon.com/) to utilize the power of existing actions or just get inspired.

### But there exist no actions I could use?

Make sure to **check out also GitHub apps** in the marketplace. Most of them are free to use for open-source projects, and similar to GitHub Actions, they provide various bots to automate your workflows with a few configurations lines. For example, [Dependabot](https://dependabot.com/) manages and automatically updates all your third party code, while [Codecov](https://codecov.io/) can manage your code coverage reports. Once again, check out the [GitHub marketplace](https://github.com/marketplace).

### Do I need to write an action for every step?

**No**. Outsourcing the work in an independent action allows your code to be easily reused even by other projects. However, most actions in a workflow are so simple there is no need to use an action for them. Directly implement the logic in your workflows. GitHub Action composites allow you to reuse steps between a workflow without creating an action.

### What should I do if GitHub Actions have no support for a required feature?

Try it out yourself. GitHub Actions are still very new and under constant development. There is a high chance features that were not working or missing in the past are already working/integrated. That said, do not blindly trust any forums on the internet. Yes, that also counts for StackOverflow.

Most of the time, everything will be possible, it may just require a complicated workaround by manually implementing every step with certain scripts. However, before investing to much time, look at the [GitHub Actions roadmap](https://github.com/github/roadmap/projects/1) to see what is already planned. Besides, feel free to browse the [GitHub Actions community forum](https://github.community/c/github-actions/41) and start a new question.

### What should I do if my GitHub Actions are failing randomly?

First, make sure it is not your fault. (E.g. Flaky tests)
By looking at the GitHub Actions feedback, it should be immediately apparent who is responsible for the error.

In case of errors with third-party dependencies like a package manager, do not hesitate to create issue reports at their repositories. Typically you are not the only one with the problems, and they will be quickly resolved.

In case of network problems during the run of the workflows, head over to GitHub status to see if there are any known issues. Even if the status provides no information, **wait** a few hours. Typically the problems will be resolved. Else, feel free to contact the GitHub support.

### How can I access secrets at a pull request from a forked repository?

You can't. At least at the moment. However, you can use the on schedule event to run a script on the official repository that reacts to every pull request. This can come in handy for various actions requiring writing access to the repository, for example, to automatically label a pull request. As a sidenote, scheduled jobs only work once they are merged into the repository. Hence, to test out your implementation, it can be useful to create a second GitHub account. Then fork your forked repository. Merge the (scheduled) action in your forked repository and create a PR from your second forked repository to test its functionality. However, do not forget to reset your Git environment before creating a pull request to the official branch.

### What workflow trigger event should I use?

For further triggers, like on release, on issue, etc. check out the documentation.

##### The `on pull request` trigger/event.

Every CI job, such as automated builds or tests that must run on every contribution, can be initiated with the on `pull request` event as a trigger. Hence, actions will be executed at the creation of a pull request, but also every time a pull request is updated. Side note: Using a forking workflow, write permissions are missing - possible workaround: the `on schedule` event.

##### The `on push` (to master) trigger/event.

The `on push` event initiates actions that must be executed after a merge. Besides, events can be configured to only react to specific branches. This allows us to combine the `on push` event with the `main` branch. Hence, for example, we can start the deployment on the merge of a pull request into the `main` branch.

##### The `on schedule` trigger/event.

However, various workflows can be independent of contributions to the project, such as automated daily synchronizations or weekly meeting note generations. The `on schedule` event allows Actions to workflows on schedule.

##### The `on dispatch` trigger/event.

A dispatch trigger provides developers with maximum control on when a workflow should run. You have a workflow you need to run once a month, but have no idea when exactly? Workflows triggered by the manual dispatch event can be started directly with a simple click in the GitHub Actions interface at any time.

### I have very similar jobs. Any chance to reduce the duplicated code?

Sure, use **[composites](https://docs.github.com/en/actions/creating-actions/creating-a-composite-run-steps-action)** or [actions](https://docs.github.com/en/actions/learn-github-actions/finding-and-customizing-actions) with variable parameters to reuse the logic of your workflows.
Moreover, you can use **[matrix builds](https://docs.github.com/en/actions/reference/workflow-syntax-for-github-actions#jobsjob_idstrategymatrix)** to create multiple in parallel running jobs with slight derivations. For example, you can run the same job in various environments or spawn jobs that execute a different test-suite.

### Can i run additional steps even if a job failed?

Yes, add a condition to the step.

- E.g. `if: always()` and the step will always be executed.
  However, you can add any condition you'd like and build execution chains based on the result of previously ran steps.

### Can I prevent my workflow from failing?

Yes, just set `continue-on-error` to `true.` However, it is recommended to do this only for informational metrics. Else developers will ignore the results. Better invest the required resources to provide an error-free pipeline. Might wanna consider using a baseline.

### Can I expect a GitHub Action's workflow step to fail?

Yes. However, not out of the box. You have to set the step you expect to fail to `continue-on-error.` Before doing the step's actual work, set a status variable to `failure.` At the end of the step, update the status variable to `success.` Next, create a second step. In this step, you can check the status variable of the previous steps. In case the step you expected failed, the status will be set to `failure,` else it will be set to `success.` So exit the workflow job in case the variable is set to `success.`

```
- id: s1
  continue-on-error: true
  run: |
    echo ::set-output name=status::failure
    <do your real work you expect to fail here>
    echo ::set-output name=status::success
- if: steps.s1.status == 'success'
  run: |
    exit -1
```

### My workflow requires private credentials to run. Where to store them?

**Never**, I repeat, **never** store them directly in the workflow files. Else everyone can easily steal your credentials.
Add them to the GitHub secrets storage. GitHub Action workflows can access the secrets. However, ensure your workflows do not leak the secrets. GitHub only blacks them out in the GitHub feedback. In case you have to add secrets in a repository where you have no permissions. Ask a project maintainer to add the secrets for you.

### I do not have a single docker container, but use docker compose

No problem, boot up any OS you like (e.g Ubuntu) and run docker compose to build and start your containers. Finally, just run your actions inside the docker containers.

```
runs-on: ubuntu-latest
steps:
  - uses: actions/checkout@v2
  - run: |
    docker compose -f <docker compose-path> up -d
    docker exec <container> <your-command>
```

### Is all the generated content lost after a workflow finishs its execution?

No, the feedback stays on GitHub, available for months. In case you need access to reports, additional debug information, or any other generated files, such as built executables. You can upload any files as artifacts to GitHub. Once uploaded, they can be downloaded in different workflows or manually from the interface.

### Can I share content between multiple jobs?

Yes, upload the information as artifacts in one job and download it in another one. Besides, you can cache files and restore them in all jobs that follow. However, directly sharing the same environment between various jobs is currently not supported.

### Can I run the GitHub Actions locally?

Not, without additional work. You need to [self-host GitHub Actions](https://docs.github.com/en/actions/hosting-your-own-runners/about-self-hosted-runners).

### Do I have to push changes to a pull request to rerun all workflows triggered by the on pull request event?

No, head to the GitHub Actions `Check` interface of a pull request on GitHub and click the `rerun` workflow button.

### Can I just rerun a job, not the whole workflow?

Currently not.

### My workflow job requires the results of another job. Can I wait for the other job to finish?

Yes, add `need: <name-of-job-you-need-for-this-job>` to the job that requires another job to be already finished.

### I just want to see some real-world examples!

At **Catroweb** we are responsible for **[Catrobat's Share community platform](https://share.catrob.at/)**. To easy the development we extensively make use of GitHub Actions to implement our continuous integration (CI) and continuous delivery (CD) system. Besides, various additional workflows have been automated, such as the Crowdin translation synchronization process, our API code generation process, or a check to compare our supported bricks with all existing bricks in the Catroid project.

So, what are you waiting for, head over to the **[Catroweb](https://github.com/Catrobat/Catroweb)** or [Catroweb-API](https://github.com/Catrobat/Catroweb-API)) repositories on GitHub. You find the exact implementations, in the [workflow directory at Catroweb](https://github.com/Catrobat/Catroweb/tree/main/.github/workflows) or at the [API repository](<(https://github.com/Catrobat/Catroweb/tree/main/.github/workflows)>). You might also want to check out the integrated [GitHub Actions feedback](https://github.com/Catrobat/Catroweb/actions) or the [pull request feedback](https://github.com/Catrobat/Catroweb/pull/913).
A detailed description of each workflow and the challenges of their automation is provided by the following work: (ToDo: add link to confluence once published) In case you need more details about specific implementations or design decisions feel free to contact [the author](mailto:daniiel.metzner@gmail.com).

### Still need more information?

- search the web and especially the official documentation
- ask your team colleagues (especially seniors)
- try things out - have fun to experiment

Add your knowledge to this FAQ to help future developers!
