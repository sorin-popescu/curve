Feature: Prepaid card

    Scenario: Emitting a prepaid card
        Given I am eligible for a card
        When I apply for a prepaid card
        Then The card should be emitted

    Scenario: I can make a deposit on a card
        Given I have a card
        When I make a deposit of 100 GBP
        And I check the balance
        Then The balance should be 100 GBP
