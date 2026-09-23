Feature: Count clicks on a job offer tile
  In order to know how much interest a job offer generates,
  As an ad platform administrator, and as the author of a job offer,
  I need every click on a job offer tile to be counted.

  Background:
    Given there is a job offer "php-developer"

  Scenario: A job offer that has never been clicked has no clicks
    Then the job offer "php-developer" has 0 clicks

  Scenario: Every click on a job offer tile is counted
    When a user clicks the tile of the job offer "php-developer" twice
    Then the job offer "php-developer" has 2 clicks
