<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\User;

class OAuthClientController extends BaseApiController
{
    public function __construct(
        protected ClientRepository $clients,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $clients = $user->oauthApps()
            ->where('revoked', false)
            ->orderBy('name')
            ->get()
            ->map(fn (Client $client) => $this->serializeClient($client));

        return $this->success($clients, 'OAuth clients retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => 'required|url',
            'confidential' => 'boolean',
        ]);

        $client = $this->clients->createAuthorizationCodeGrantClient(
            $validated['name'],
            [$validated['redirect']],
            $request->boolean('confidential', true),
            null,
        );

        $ownerType = $user->getMorphClass();
        $ownerId = $user->getKey();
        if (! is_string($ownerId)) {
            return $this->error('Invalid user identity', 500);
        }
        $client->owner_type = $ownerType;
        $client->owner_id = $ownerId;
        $client->save();

        return $this->success(
            $this->serializeClient($client, includeSecret: true),
            'OAuth client created successfully',
            201,
        );
    }

    public function update(Request $request, string $clientId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $client = $this->findOwnedClient($user, $clientId);

        if (! $client) {
            return $this->notFound('OAuth client');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'redirect' => 'required|url',
        ]);

        $this->clients->update(
            $client,
            $validated['name'],
            [$validated['redirect']],
        );

        $client->refresh();

        return $this->success($this->serializeClient($client), 'OAuth client updated successfully');
    }

    public function destroy(Request $request, string $clientId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $client = $this->findOwnedClient($user, $clientId);

        if (! $client) {
            return $this->notFound('OAuth client');
        }

        $this->clients->delete($client);

        return $this->success(null, 'OAuth client deleted successfully');
    }

    protected function findOwnedClient(User $user, string $clientId): ?Client
    {
        return $user->oauthApps()
            ->where('revoked', false)
            ->whereKey($clientId)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeClient(Client $client, bool $includeSecret = false): array
    {
        $redirectUrisRaw = $client->getAttribute('redirect_uris');
        $redirectUris = is_array($redirectUrisRaw) ? $redirectUrisRaw : [];

        $payload = [
            'id' => $client->id,
            'name' => $client->name,
            'redirect' => $redirectUris[0] ?? '',
            'redirect_uris' => $redirectUris,
            'revoked' => (bool) $client->revoked,
            'created_at' => $client->created_at,
        ];

        if ($includeSecret && ! empty($client->plainSecret)) {
            $payload['secret'] = $client->plainSecret;
        }

        return $payload;
    }
}
