@web @debug
Feature: To provide users with valid code statistics we should have the same bricks in the web as in the app

  Scenario: When Parsing Bricks and Scripts from XstreamSerializer.java and CategoryBricksFactory.java (Catroid Github)
  and comparing them with our defined Bricks and Scripts. Then the intersection should contain all bricks and scripts.

    Given all catroid blocks should also be implemented in catroweb
