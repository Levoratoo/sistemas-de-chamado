<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketQueueController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user && ($user->isAdmin() || $user->isGestor() || $user->isAtendente()), 403);

        $isAdmin = $user->isAdmin();
        $isGestor = $user->isGestor();
        $isAtendente = $user->isAtendente();
        $canManageAll = $isAdmin;
        $areaIdsFromProfiles = $user->groupsAreasIds();
        $filterAssignedToMe = $request->boolean('assigned_to_me');
        $filterOnlyUnassigned = $request->boolean('only_unassigned');

        $query = Ticket::query()
            ->with(['requester', 'assignee', 'area'])
            ->orderByDesc('updated_at');

        if (! $canManageAll) {
            if ($isGestor) {
                $ids = !empty($areaIdsFromProfiles) ? $areaIdsFromProfiles : [-1];
                $query->whereIn('area_id', $ids);

                if ($filterAssignedToMe) {
                    $query->where('assignee_id', $user->id);
                } elseif ($filterOnlyUnassigned) {
                    $query->whereNull('assignee_id');
                }
            } elseif ($isAtendente) {
                $ids = !empty($areaIdsFromProfiles) ? $areaIdsFromProfiles : [-1];
                $query->whereIn('area_id', $ids);

                if ($filterAssignedToMe) {
                    $query->where('assignee_id', $user->id);
                } else {
                    $query->whereNull('assignee_id')
                        ->whereIn('status', [
                            Ticket::STATUS_OPEN,
                            Ticket::STATUS_IN_PROGRESS,
                            Ticket::STATUS_WAITING_USER,
                        ]);
                }
            } else {
                $query->where('requester_id', $user->id);
            }
        } elseif ($filterAssignedToMe) {
            $query->where('assignee_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('request_type')) {
            $requestType = $request->string('request_type')->toString();
            if ($requestType === 'geral') {
                $query->whereNull('request_type')->orWhere('request_type', '');
            } else {
                $query->where('request_type', $requestType);
            }
        }

        if ($request->filled('area_id')) {
            $areaFilter = (int) $request->input('area_id');

            if (
                $canManageAll
                || in_array($areaFilter, $areaIdsFromProfiles, true)
            ) {
                $query->where('area_id', $areaFilter);
            }
        }

        if ($filterOnlyUnassigned && $canManageAll && ! $filterAssignedToMe) {
            $query->whereNull('assignee_id');
        }

        $tickets = $query->paginate(15)->withQueryString();

        $areas = $canManageAll
            ? Area::query()->orderBy('name')->get()
            : Area::query()->whereIn('id', $areaIdsFromProfiles)->orderBy('name')->get();

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $finalizadosMes = Ticket::finalizedBy($user->id)
            ->whereBetween('resolved_at', [$monthStart, $monthEnd])
            ->count();

        return view('tickets.queue', [
            'tickets' => $tickets,
            'areas' => $areas,
            'canManageAll' => $canManageAll,
            'finalizadosMes' => $finalizadosMes,
            'filters' => [
                'status' => $request->input('status'),
                'area_id' => $request->input('area_id'),
                'request_type' => $request->input('request_type'),
                'only_unassigned' => $request->boolean('only_unassigned'),
                'assigned_to_me' => $request->boolean('assigned_to_me'),
            ],
        ]);
    }
}
