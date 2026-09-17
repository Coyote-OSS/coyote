Feature: Leaderboard header for a sole campaign
  In order to give the sole campaign more prominence in the header,
  As a campaign space provider,
  I need to resolve its leaderboard variant into the header slot on desktop,
  falling back to the regular banner on mobile.

  Background:
    Given there is a sole campaign "sale"

  Scenario: A campaign with a leaderboard variant is resolved into the header
    Given the campaign "sale" has a "leaderboard" variant "leaderboard.png"
    When variants are resolved for a user on "desktop"
    Then the "header" slot contains "leaderboard.png"

  Scenario: A campaign without a leaderboard variant falls back to the regular banner
    Given the campaign "sale" has a "banner" variant "banner.png"
    When variants are resolved for a user on "desktop"
    Then the "header" slot contains "banner.png"

  Scenario: A campaign with a leaderboard variant has an empty header on mobile
    Given the campaign "sale" has a "leaderboard" variant "leaderboard.png"
    When variants are resolved for a user on "mobile"
    Then the "header" slot is empty

  Scenario: A campaign with a leaderboard variant resolves to the banner-xl variant on mobile
    Given the campaign "sale" has a "leaderboard" variant "leaderboard.png"
    And the campaign "sale" has a "banner-xl" variant "banner-xl.png"
    When variants are resolved for a user on "mobile"
    Then the "header" slot does not contain "leaderboard.png"

  Scenario: A campaign's leaderboard variant is not resolved into the feed
    Given the campaign "sale" has a "leaderboard" variant "leaderboard.png"
    When variants are resolved for a user on "desktop"
    Then the "feed" slot does not contain "leaderboard.png"
