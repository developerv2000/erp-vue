<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use App\Support\Helpers\ControllerHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('global/pages/notifications/Index', [
            // Lazy loads. Refetched only on locale change
            'allTableHeaders' => fn() => $this->getAllTableHeadersTranslated(),
        ]);
    }

    private function getAllTableHeadersTranslated(): Collection
    {
        $headers = collect([
            ['title' => "Date", 'key' => 'created_at', 'width' => 200, 'sortable' => true],
            ['title' => "Status", 'key' => 'read_at', 'width' => 140, 'sortable' => true],
            ['title' => "fields.Text", 'key' => 'text', 'width' => 800, 'sortable' => false],
        ]);

        ControllerHelper::translateTableHeadersTitle($headers);

        return $headers;
    }

    public function getUnreadCount(): JsonResponse
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);

        foreach ($ids as $id) {
            $notification = auth()->user()->notifications()->find($id);

            if ($notification) {
                $notification->markAsRead();
            }
        }

        return response()->json([
            'count' => count($ids),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);

        // Delete only notifications that belong to the authenticated user
        $deletedCount = $request->user()
            ->notifications()
            ->whereIn('id', $ids)
            ->delete();

        return response()->json([
            'count' => $deletedCount,
        ]);
    }
}
