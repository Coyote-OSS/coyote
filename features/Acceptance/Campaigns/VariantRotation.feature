Feature: Rotation of variants across renders
  In order to give multiple campaigns fair exposure in the same slot,
  As a campaign space provider,
  I need each render to rotate through the eligible campaigns' variants.

  Scenario: Campaign with two variants of the same kind rotates through the square slot
    Given there is a campaign "solo", which has a "rectangle" variant "one.png"
    And the campaign "solo" also has a "rectangle" variant "two.png"
    When variants are resolved for a user on "desktop"
    Then the "square" slot contains "one.png"
    When variants are resolved for a user on "desktop" again
    Then the "square" slot contains "two.png"
    When variants are resolved for a user on "desktop" again
    Then the "square" slot contains "one.png"

  Scenario: Two campaigns with a rectangle variant rotate through the square slot
    Given there is a campaign "first", which has a "rectangle" variant "first.png"
    And there is a campaign "second", which has a "rectangle" variant "second.png"
    When variants are resolved for a user on "desktop"
    Then the "square" slot contains "first.png"
    When variants are resolved for a user on "desktop" again
    Then the "square" slot contains "second.png"
    When variants are resolved for a user on "desktop" again
    Then the "square" slot contains "first.png"

  Scenario: A campaign with three variants rotates through the feed slot
    Given there is a campaign "solo", which has a "rectangle" variant "one.png"
    And the campaign "solo" also has a "rectangle" variant "two.png"
    And the campaign "solo" also has a "rectangle" variant "three.png"
    When variants are resolved for a user on "desktop"
    Then the "feed" slot contains "one.png"
    And the "feed" slot contains "two.png"
    When variants are resolved for a user on "desktop" again
    Then the "feed" slot contains "two.png"
    And the "feed" slot contains "three.png"
    When variants are resolved for a user on "desktop" again
    Then the "feed" slot contains "three.png"
    And the "feed" slot contains "one.png"
