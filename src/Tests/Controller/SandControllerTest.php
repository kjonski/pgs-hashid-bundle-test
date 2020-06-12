<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SandControllerTest extends WebTestCase
{
    public function testGenerateUrls()
    {
        $client = static::createClient();

        $client->request('GET', '/examples-list');

        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $links = json_decode($client->getResponse()->getContent(), true);

        foreach ($links as $case => $data) {
            $client->request('GET', $data['url']);

            $this->assertEquals(200, $client->getResponse()->getStatusCode());
        }
    }

    public function testGenerateUrlsInTwigTemplate()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/demo-twig-template');

        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'text/html; charset=UTF-8'
            ),
            "Content type is text/html"
        );

        $links = $crawler->filter('a')->links();

        foreach ($links as $link) {
            $client->click($link);

            $this->assertEquals(200, $client->getResponse()->getStatusCode());
        }
    }
}