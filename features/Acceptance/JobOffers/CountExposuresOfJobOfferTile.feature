Feature: Count exposures of a job offer tile
  In order to know how many users actually saw a job offer,
  As an ad platform administrator, and as the author of a job offer,
  I need a job offer tile to be counted as exposed only once it was seen
  in the viewport for at least a second.

  Background:
    Given there is a job offer "php-developer"

  Scenario: A job offer tile below the viewport is not counted as exposed
    When a user opens a page with the tile of the job offer "php-developer" below the viewport
    Then the job offer "php-developer" has 0 exposures

  Scenario: A job offer tile scrolled into the viewport is counted as exposed
    Given a user opened a page with the tile of the job offer "php-developer" below the viewport
    When the user scrolls to the tile of the job offer "php-developer" and sees it for a second
    Then the job offer "php-developer" has 1 exposure
