@homepage
Feature: Pocketcode homepage
  In order to access and browse the programs
  As a visitor
  I want to be able to see the homepage

Scenario: Viewing the homepage at website root
  Given I am on homepage
  Then I should see the featured slider
  And I should see newest programs
  And I should see most downloaded programs
  And I should see most viewed programs
  And I should see some programs