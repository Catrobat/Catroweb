@homepage
Feature: Pocketcode homepage
  In order to access and browse the programs
  As a visitor
  I want to be able to see the homepage

@Desktop
Scenario: Viewing the homepage at website root
  When I am on homepage
  Then I should see three containers: "newest", "mostDownloaded" and "mostViewed"
  And in each of them there should be "18" programs loaded

@Mobile
Scenario: Viewing the homepage at website root
  When I am on homepage
  Then I should see three containers: "newest", "mostDownloaded" and "mostViewed"
  And in each of them there should be "6" programs visible