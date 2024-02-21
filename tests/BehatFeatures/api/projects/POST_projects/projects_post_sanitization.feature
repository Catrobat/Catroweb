@api @projects @post
Feature: After uploading unnecessary files should be removed

  Scenario: Project Sanitizer should remove unnecessary files
    Given I try to upload a project with unnecessary files, API version 2
    Then the uploaded project should exist in the database, API version 2
    And the resources should not contain the unnecessary files

  Scenario: Project Sanitizer should remove unnecessary files even when scenes are used
    Given I try to upload a project with scenes and unnecessary files, API version 2
    Then the uploaded project should exist in the database, API version 2
    And the resources should not contain the unnecessary files
