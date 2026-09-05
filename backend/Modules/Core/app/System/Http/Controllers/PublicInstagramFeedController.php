<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Core\System\Services\InstagramFeedService;

class PublicInstagramFeedController extends BaseApiController
{
    public function index(InstagramFeedService $feedService): JsonResponse
    {
        $feed = $feedService->getPublicFeed();

        return $this->success($feed, 'Public Instagram feed retrieved successfully');
    }
}
