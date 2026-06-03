<?php

namespace App\Socialite;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use Illuminate\Support\Arr;

class PausProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The separator used for the scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ',';

    /**
     * Get the authentication URL for the provider.
     *
     * @param  string  $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(config('services.paus.base_url') . '/oauth', $state);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return config('services.paus.base_url') . '/oauth/token';
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param  string  $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(config('services.paus.base_url') . '/api/v1/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array  $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        // Mendukung format response 'data' atau flat
        $data = $user['data'] ?? $user;

        return (new User)->setRaw($data)->map([
            'id'       => $data['id'] ?? $data['paus_id'] ?? $data['username'],
            'nickname' => $data['username'] ?? $data['paus_username'],
            'name'     => $data['name'] ?? $data['fullname'] ?? $data['paus_name'],
            'email'    => $data['email'] ?? null,
        ]);
    }
}
