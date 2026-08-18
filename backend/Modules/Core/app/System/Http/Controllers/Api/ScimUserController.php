<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\User;

class ScimUserController extends BaseApiController
{
    /**
     * Map User model to SCIM 2.0 schema format
     *
     * @return array<string, mixed>
     */
    private function mapToScim(User $user): array
    {
        return [
            'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
            'id' => $user->id,
            'userName' => $user->email,
            'name' => [
                'formatted' => $user->name,
                'familyName' => collect(explode(' ', $user->name))->last(),
                'givenName' => collect(explode(' ', $user->name))->first(),
            ],
            'emails' => [
                [
                    'value' => $user->email,
                    'type' => 'work',
                    'primary' => true,
                ],
            ],
            'active' => empty($user->deleted_at),
            'meta' => [
                'resourceType' => 'User',
                'created' => $user->created_at ? $user->created_at->toIso8601String() : null,
                'lastModified' => $user->updated_at ? $user->updated_at->toIso8601String() : null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function requestPayload(Request $request): array
    {
        $data = $request->json()->all();

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function extractEmail(array $data): ?string
    {
        if (isset($data['userName']) && is_string($data['userName'])) {
            return $data['userName'];
        }

        if (! isset($data['emails']) || ! is_array($data['emails'])) {
            return null;
        }

        $first = $data['emails'][0] ?? null;
        if (! is_array($first)) {
            return null;
        }

        $value = $first['value'] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function extractName(array $data): string
    {
        if (! isset($data['name']) || ! is_array($data['name'])) {
            return 'Unknown';
        }

        $name = $data['name'];
        if (isset($name['formatted']) && is_string($name['formatted']) && $name['formatted'] !== '') {
            return $name['formatted'];
        }

        $given = isset($name['givenName']) && is_string($name['givenName']) ? $name['givenName'] : '';
        $family = isset($name['familyName']) && is_string($name['familyName']) ? $name['familyName'] : '';

        $combined = trim($given.' '.$family);

        return $combined !== '' ? $combined : 'Unknown';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applyNameUpdate(User $user, array $data): void
    {
        if (! isset($data['name']) || ! is_array($data['name'])) {
            return;
        }

        $name = $data['name'];
        if (isset($name['formatted']) && is_string($name['formatted'])) {
            $user->name = $name['formatted'];

            return;
        }

        if (isset($name['givenName']) && is_string($name['givenName'])) {
            $family = isset($name['familyName']) && is_string($name['familyName']) ? $name['familyName'] : '';
            $user->name = trim($name['givenName'].' '.$family);
        }
    }

    /**
     * @param  array<mixed, mixed>  $op
     * @return array<string, mixed>
     */
    private function normalizePatchOperation(array $op): array
    {
        $normalized = [];
        foreach ($op as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $op
     */
    private function applyPatchOperation(User $user, array $op): void
    {
        $opType = isset($op['op']) && is_string($op['op']) ? strtolower($op['op']) : '';
        if ($opType !== 'replace') {
            return;
        }

        $path = isset($op['path']) && is_string($op['path']) ? $op['path'] : '';
        $value = $op['value'] ?? null;

        if ($path !== 'active' && ! (is_array($value) && array_key_exists('active', $value))) {
            return;
        }

        $active = is_array($value) ? ($value['active'] ?? true) : $value;
        if (is_array($active)) {
            $active = $active['active'] ?? true;
        }

        if ($active === false || $active === 'False' || $active === 'false') {
            $user->delete();
        } else {
            $user->restore();
        }
    }

    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        $filter = $request->query('filter');
        if (is_string($filter) && preg_match('/userName eq "([^"]+)"/i', $filter, $matches)) {
            $query->where('email', $matches[1]);
        }

        $startIndex = (int) $request->query('startIndex', 1);
        $count = (int) $request->query('count', 100);

        $totalResults = $query->count();
        $users = $query->skip($startIndex - 1)->take($count)->get();

        $resources = $users->map(fn (User $user) => $this->mapToScim($user));

        return response()->json([
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:ListResponse'],
            'totalResults' => $totalResults,
            'itemsPerPage' => $count,
            'startIndex' => $startIndex,
            'Resources' => $resources,
        ], 200);
    }

    public function show(string $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
                'detail' => 'User not found',
                'status' => '404',
            ], 404);
        }

        return response()->json($this->mapToScim($user), 200);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->requestPayload($request);

        $email = $this->extractEmail($data);
        $name = $this->extractName($data);

        if ($email === null) {
            return response()->json([
                'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
                'detail' => 'userName or emails are required',
                'status' => '400',
            ], 400);
        }

        $existing = User::where('email', $email)->first();
        if ($existing) {
            return response()->json([
                'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
                'detail' => 'User already exists',
                'status' => '409',
            ], 409);
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(16)),
            'email_verified_at' => now(),
        ]);

        return response()->json($this->mapToScim($user), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json([
                'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
                'detail' => 'User not found',
                'status' => '404',
            ], 404);
        }

        $data = $this->requestPayload($request);

        if (isset($data['userName']) && is_string($data['userName'])) {
            $user->email = $data['userName'];
        }

        $this->applyNameUpdate($user, $data);

        if (array_key_exists('active', $data)) {
            if ($data['active'] === false) {
                $user->delete();
            } else {
                $user->restore();
            }
        }

        $user->save();

        return response()->json($this->mapToScim($user), 200);
    }

    public function patch(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json([
                'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
                'detail' => 'User not found',
                'status' => '404',
            ], 404);
        }

        $data = $this->requestPayload($request);
        $operations = $data['Operations'] ?? [];
        if (! is_array($operations)) {
            $operations = [];
        }

        foreach ($operations as $op) {
            if (is_array($op)) {
                $this->applyPatchOperation($user, $this->normalizePatchOperation($op));
            }
        }

        return response()->json($this->mapToScim($user), 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json([
                'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
                'detail' => 'User not found',
                'status' => '404',
            ], 404);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
