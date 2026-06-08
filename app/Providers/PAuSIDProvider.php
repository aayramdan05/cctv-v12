<?php

namespace App\Providers;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

use GuzzleHttp\ClientInterface;

class PAuSIDProvider extends AbstractProvider implements ProviderInterface
{

    protected $host = 'https://paus.unpad.ac.id';
    protected $guzzle = [
        'timeout' => 10
    ];

    protected $scopes = ['user.basic'];

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->host . '/oauth', $state);
    }

    protected function getTokenUrl()
    {
        return $this->host . '/oauth/access-token';
    }

    protected function getUserByToken($token)
    {
        try {
            $response = $this->getHttpClient()->get($this->host . '/api', [
                RequestOptions::QUERY => ['access_token' => $token],
            ]);
        } catch (GuzzleException $e) {
            throw new Exception("PAuS Sedang tidak dapat diakses. Silakan coba beberapa saat lagi.", 0, $e);
        }

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['paus_name'],
            'name' => $user['attributes']['nama']['attribute_value'],
        ]);
    }
}
