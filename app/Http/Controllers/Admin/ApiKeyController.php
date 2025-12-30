<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\View\View
    {
        $apiKeys = ApiKey::orderBy('created_at', 'desc')->get();

        return view('admin.api-keys.index', compact('apiKeys'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\View\View
    {
        return view('admin.api-keys.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'allowed_ips' => 'nullable|string',
            'scopes' => 'nullable|array',
            'rate_limit' => 'nullable|integer|min:1|max:1000',
            'expires_at' => 'nullable|date|after:today',
        ]);

        // Generate API Key dan Secret Key
        $keys = ApiKey::generate();

        // Parse allowed IPs
        $allowedIps = null;
        if ($request->filled('allowed_ips')) {
            $ips = array_map('trim', explode(',', $request->allowed_ips));
            $allowedIps = array_filter($ips);
        }

        // Create API Key dengan secret key yang akan di-hash oleh model
        $apiKey = new ApiKey;
        $apiKey->name = $validated['name'];
        $apiKey->api_key = $keys['api_key'];
        $apiKey->consid = $keys['consid'];
        $apiKey->userkey = $keys['userkey'];
        $apiKey->secret_key = $keys['secret_key']; // Will be hashed by setSecretKeyAttribute
        $apiKey->description = $validated['description'] ?? null;
        $apiKey->webhook_url = $validated['webhook_url'] ?? null;
        $apiKey->allowed_ips = ! empty($allowedIps) ? array_values($allowedIps) : null;
        $apiKey->scopes = $validated['scopes'] ?? null;
        $apiKey->rate_limit = $validated['rate_limit'] ?? 60;
        $apiKey->expires_at = $validated['expires_at'] ?? null;
        $apiKey->is_active = true;
        $apiKey->save();

        // Store keys in session to show only once
        session()->flash('api_key_created', [
            'api_key' => $keys['api_key'],
            'consid' => $keys['consid'],
            'userkey' => $keys['userkey'],
            'secret_key' => $keys['secret_key'],
            'id' => $apiKey->id,
        ]);

        return redirect()->route('api-keys.show', $apiKey->id)
            ->with('success', 'API Key berhasil dibuat. Simpan Secret Key dengan aman, karena hanya ditampilkan sekali!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): \Illuminate\View\View
    {
        $apiKey = ApiKey::findOrFail($id);
        $createdKeys = session('api_key_created');

        return view('admin.api-keys.show', compact('apiKey', 'createdKeys'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): \Illuminate\View\View
    {
        $apiKey = ApiKey::findOrFail($id);

        return view('admin.api-keys.edit', compact('apiKey'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $apiKey = ApiKey::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'allowed_ips' => 'nullable|string',
            'scopes' => 'nullable|array',
            'rate_limit' => 'nullable|integer|min:1|max:1000',
            'expires_at' => 'nullable|date|after:today',
            'is_active' => 'boolean',
        ]);

        // Parse allowed IPs
        $allowedIps = null;
        if ($request->filled('allowed_ips')) {
            $ips = array_map('trim', explode(',', $request->allowed_ips));
            $allowedIps = array_filter($ips);
        }

        $apiKey->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'webhook_url' => $validated['webhook_url'] ?? null,
            'allowed_ips' => ! empty($allowedIps) ? array_values($allowedIps) : null,
            'scopes' => $validated['scopes'] ?? null,
            'rate_limit' => $validated['rate_limit'] ?? 60,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : $apiKey->is_active,
        ]);

        return redirect()->route('api-keys.index')
            ->with('success', 'API Key berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->delete();

        return redirect()->route('api-keys.index')
            ->with('success', 'API Key berhasil dihapus');
    }

    /**
     * Regenerate secret key
     */
    public function regenerateSecret(Request $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $apiKey = ApiKey::findOrFail($id);

        $newSecret = 'sk_'.Str::random(32);
        $apiKey->secret_key = $newSecret;
        $apiKey->save();

        session()->flash('secret_key_regenerated', $newSecret);

        return redirect()->route('api-keys.show', $apiKey->id)
            ->with('success', 'Secret Key berhasil di-regenerate. Simpan dengan aman!');
    }
}
