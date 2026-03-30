@api @sanitizer
Feature: Text sanitizer filters profanity and contact info during project upload

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And I am "Catrobat"

  Scenario: Profanity in project name is masked on upload
    Given I have a project with "name" set to "My shit project"
    When I upload this generated project
    Then the project should have name "My **** project"

  Scenario: Email in project description is redacted on upload
    Given I have a project with "name" set to "Clean Name" and "description" set to "Contact me at user@example.com for details"
    When I upload this generated project
    Then the project should have description "Contact me at [contact removed] for details"

  Scenario: Phone number in project description is redacted on upload
    Given I have a project with "name" set to "Clean Name" and "description" set to "Call me at +1 555 123 4567 now"
    When I upload this generated project
    Then the project should have description "Call me at [contact removed] now"

  Scenario: Social media link in project description is redacted on upload
    Given I have a project with "name" set to "Clean Name" and "description" set to "Follow me at https://instagram.com/myprofile please"
    When I upload this generated project
    Then the project should have description "Follow me at [contact removed] please"

  Scenario: Clean text passes through unchanged
    Given I have a project with "name" set to "My awesome game" and "description" set to "A fun platformer for everyone"
    When I upload this generated project
    Then the project should have name "My awesome game"
    And the project should have description "A fun platformer for everyone"
