@api @projects @post
Feature: After uploading unnecessary files should be removed

  Scenario: Program Sanitizer should remove unnecessary files
    Given I try to upload a project with unnecessary files
    Then the uploaded project should exist in the database
    And the resources should not contain the unnecessary files

  Scenario: Program Sanitizer should remove unnecessary files even when scenes are used
    Given I try to upload a project with scenes and unnecessary files
    Then the uploaded project should exist in the database
    And the resources should not contain the unnecessary files
