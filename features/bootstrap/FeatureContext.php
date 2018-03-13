<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private $client;

    /** @var \GuzzleHttp\Psr7\Response */
    private $response;

    private $card;

    private $balance;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(
            [
                'base_uri' => 'http://localhost:8080'
            ]
        );
    }

    /**
     * @Given I am eligible for a card
     */
    public function iAmEligibleForACard()
    {
        return true;
    }

    /**
     * @When I apply for a prepaid card
     */
    public function iApplyForAPrepaidCard()
    {
        $this->response = $this->client->request('POST', 'card');
    }

    /**
     * @Then The card should be emitted
     */
    public function theCardShouldBeEmitted()
    {
        PHPUnit_Framework_TestCase::assertEquals(201, $this->response->getStatusCode());
    }

    /**
     * @Given I have a card
     */
    public function iHaveACard()
    {
        $this->response = $this->client->request('POST', 'card');
        $this->card = json_decode($this->response->getBody()->getContents(), true);
    }

    /**
     * @When I make a deposit of :arg1 GBP
     */
    public function iMakeADepositOfGbp($arg1)
    {
        $this->response = $this->client->patch(
            'card/deposit',
            ['json' =>[
                'card_number' => $this->card['card'],
                'amount' => $arg1
            ]]
        );
    }

    /**
     * @When I check the balance
     */
    public function iCheckTheBalance()
    {
        $this->response = $this->client->request(
            'GET',
            'card/' . $this->card['card']
        );

        PHPUnit_Framework_TestCase::assertEquals(200, $this->response->getStatusCode());

        $this->balance = json_decode($this->response->getBody()->getContents(), true);
    }

    /**
     * @Then The balance should be :arg1 GBP
     */
    public function theBalanceShouldBeGbp($arg1)
    {
        PHPUnit_Framework_TestCase::assertEquals(100, $this->balance['balance']);
        PHPUnit_Framework_TestCase::assertEquals(100, $this->balance['available_balance']);
    }
}
