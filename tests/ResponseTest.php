<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResponseTest extends WebTestCase
{
    public function testItRedirectsToTheProjectPageIfNoParams(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $url = $client->getKernel()->getContainer()->getParameter('app.project_page_url');

        $this->assertResponseRedirects($url);
    }

    public function test_it_generates_a_response_using_a_get_request(): void
    {
        $client = static::createClient();

        $queryParams = [
            'status_code' => 200,
            'headers'     => [
                'content-type' => 'application/json',
            ],
            'body'        => '{"hello": "world"}',
        ];

        $client->request('GET', '/?' . http_build_query($queryParams));

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertEquals('{"hello": "world"}', $client->getResponse()->getContent());
    }

    public function test_it_generates_a_response_using_a_post_request(): void
    {
        $client = static::createClient();

        $payload = [
            'status_code' => 200,
            'headers'     => [
                'content-type' => 'application/json',
            ],
            'body'        => '{"hello": "world"}',
        ];

        $client->request('POST', '/', [], [], [], json_encode($payload));

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertEquals('{"hello": "world"}', $client->getResponse()->getContent());
    }
}
