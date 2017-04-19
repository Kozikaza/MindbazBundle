Feature: I should be able to send an email using Mindbaz transport

  Scenario: I send an email to an existing email address in a campaign using Mindbaz transport
    Given I set a campaign
    When I send an email to an existing subscriber
    Then an email should be sent to this user

  Scenario: I send an email to a non-existing email address in a campaign using Mindbaz transport with security enabled
    Given I set a campaign
    When I send an email to a non-existing subscriber
    Then I should get an error

  Scenario: I send an email to a non-existing email address in a campaign using Mindbaz transport with creation enabled
    Given I set a campaign
    And I allow to insert missing subscribers
    When I send an email to a non-existing subscriber
    Then an email should be sent to this user
    And this user should have been created on Mindbaz

  Scenario: I send an email to a non-existing email uppercase address in a campaign using Mindbaz transport with creation enabled
    Given I set a campaign
    And I allow to insert missing subscribers
    When I send an email to a non-existing subscriber with an uppercase address
    Then an email should be sent to this user
    And this user should have been created on Mindbaz
    And its email address should have been lowercased
    When I send another email to the same address
    Then an email should be sent to this user

  Scenario: I send an email in no campaign using Mindbaz transport
    When I send an email
    Then I should get an error
