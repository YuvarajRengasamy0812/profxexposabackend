<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ClientSpeaker;
use App\Models\WebmasterSection;
use Illuminate\Http\Request;

class ClientSpeakerController extends Controller
{
    public function index(Request $request)
    {
        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderBy('row_no', 'asc')
            ->get();

        $query = ClientSpeaker::query();

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $speakers = $query->orderByRaw('CASE WHEN display_order IS NULL OR display_order = 0 THEN 999999 ELSE display_order END ASC')
            ->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->appends($request->query());

        $stats = (object) [
            'total' => ClientSpeaker::count(),
            'pending' => ClientSpeaker::where('status', 'pending')->count(),
            'approved' => ClientSpeaker::where('status', 'approved')->count(),
            'rejected' => ClientSpeaker::where('status', 'rejected')->count(),
        ];

        return view('dashboard.client_speakers.list', compact('GeneralWebmasterSections', 'speakers', 'stats'));
    }

    public function view($id)
    {
        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderBy('row_no', 'asc')
            ->get();

        $speaker = ClientSpeaker::findOrFail($id);

        return view('dashboard.client_speakers.view', compact('GeneralWebmasterSections', 'speaker'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected',
            'admin_message' => 'nullable|string|max:1000',
            'display_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $speaker = ClientSpeaker::findOrFail($id);
        $speaker->status = $request->status;
        $speaker->admin_message = $request->admin_message;
        $speaker->display_order = (int) $request->input('display_order', $speaker->display_order ?? 0);
        $speaker->approved_by = auth()->user()->name ?? 'Admin';
        $speaker->approved_at = $request->status === 'approved' ? now() : null;
        $speaker->save();

        return redirect()->back()->with('success', 'Speaker status updated successfully');
    }

    public function updateOrder(Request $request, $id)
    {
        $request->validate([
            'display_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $speaker = ClientSpeaker::findOrFail($id);
        $speaker->display_order = (int) $request->input('display_order', 0);
        $speaker->save();

        return redirect()->back()->with('success', 'Speaker position updated successfully');
    }
}
