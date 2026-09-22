<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IntelligentController extends Controller
{
    /**
     * Display the Intelligent Testing Dashboard.
     */
    public function index()
    {
        return view('intelligent.index');
    }

    /**
     * Poll data from UNV Camera API (LAPI)
     */
    public function getRealtimeData(Request $request)
    {
        $ip = $request->input('ip');
        $username = $request->input('username', 'admin');
        $password = $request->input('password');
        $customEndpoint = $request->input('endpoint', '/LAPI/V1.0/Intelligent/PeopleCounting/Report');

        if (!$ip || !$username || !$password) {
            return response()->json([
                'error' => 'IP, Username, dan Password kamera harus diisi.'
            ], 400);
        }

        try {
            // Menggunakan Endpoint Dinamis
            $url = "http://{$ip}" . $customEndpoint;

            // Uniview cameras usually require Digest Authentication
            $response = Http::timeout(3)
                ->withDigestAuth($username, $password)
                ->get($url);

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'raw_data' => $response->json() // Sending raw data back to frontend to inspect
                ]);
            }

            return response()->json([
                'error' => 'Gagal mengambil data. Status Code: ' . $response->status(),
                'raw_response' => $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error("UNV LAPI Error: " . $e->getMessage());
            return response()->json([
                'error' => 'Koneksi gagal atau kamera tidak merespons.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function checkOnvif(Request $request)
    {
        $ip = $request->input('ip');
        $username = $request->input('username', 'admin');
        $password = $request->input('password');

        if (!$ip || !$username || !$password) {
            return response()->json(['error' => 'IP, Username, dan Password kamera harus diisi.'], 400);
        }

        try {
            // Generate WS-UsernameToken profile for authentication
            $nonce = random_bytes(16);
            $timestamp = gmdate('Y-m-d\TH:i:s\Z');
            $passwordDigest = base64_encode(sha1($nonce . $timestamp . $password, true));
            $nonceBase64 = base64_encode($nonce);

            // SOAP Request for GetEventProperties (To see what Events this camera supports)
            $soapEnvelope = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope"
            xmlns:tev="http://www.onvif.org/ver10/events/wsdl">
  <s:Header>
    <Security s:mustUnderstand="1" xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
      <UsernameToken>
        <Username>{$username}</Username>
        <Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">{$passwordDigest}</Password>
        <Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">{$nonceBase64}</Nonce>
        <Created xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">{$timestamp}</Created>
      </UsernameToken>
    </Security>
  </s:Header>
  <s:Body>
    <tev:GetEventProperties/>
  </s:Body>
</s:Envelope>
XML;

            // Typically UNV ONVIF Events service is at /onvif/device_service or /onvif/Events
            // We'll try /onvif/Events first (Standard ONVIF)
            $response = Http::withHeaders([
                'Content-Type' => 'application/soap+xml; charset=utf-8',
            ])->timeout(5)->post("http://{$ip}/onvif/Events", $soapEnvelope);

            if (!$response->successful()) {
                // Fallback to device service
                $response = Http::withHeaders([
                    'Content-Type' => 'application/soap+xml; charset=utf-8',
                ])->timeout(5)->post("http://{$ip}/onvif/device_service", $soapEnvelope);
            }

            return response()->json([
                'status' => 'success',
                'xml_response' => $response->body()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'ONVIF Connection Failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
