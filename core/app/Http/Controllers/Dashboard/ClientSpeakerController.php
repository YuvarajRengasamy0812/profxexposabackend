<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ClientSpeaker;
use App\Models\WebmasterSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ClientSpeakerController extends Controller
{
    private string $uploadPath = 'uploads/topics/';

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

    public function create()
    {
        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderBy('row_no', 'asc')
            ->get();

        $speaker = new ClientSpeaker([
            'status' => 'approved',
            'display_order' => 0,
        ]);

        return view('dashboard.client_speakers.form', compact('GeneralWebmasterSections', 'speaker'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSpeaker($request);
        $validated['email'] = strtolower(trim($validated['email']));
        $validated['photo'] = $this->storePhoto($request);
        $validated['display_order'] = (int) ($validated['display_order'] ?? 0);

        if ($validated['status'] === 'approved') {
            $validated['approved_by'] = auth()->user()->name ?? 'Admin';
            $validated['approved_at'] = now();
        }

        ClientSpeaker::create($validated);

        return redirect()
            ->route('clientSpeakers')
            ->with('success', 'Client speaker added successfully');
    }

    public function view($id)
    {
        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderBy('row_no', 'asc')
            ->get();

        $speaker = ClientSpeaker::findOrFail($id);

        return view('dashboard.client_speakers.view', compact('GeneralWebmasterSections', 'speaker'));
    }

    public function edit($id)
    {
        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderBy('row_no', 'asc')
            ->get();

        $speaker = ClientSpeaker::findOrFail($id);

        return view('dashboard.client_speakers.form', compact('GeneralWebmasterSections', 'speaker'));
    }

    public function update(Request $request, $id)
    {
        $speaker = ClientSpeaker::findOrFail($id);
        $validated = $this->validateSpeaker($request);
        $validated['email'] = strtolower(trim($validated['email']));
        $validated['display_order'] = (int) ($validated['display_order'] ?? 0);
        unset($validated['photo']);

        if ($request->boolean('photo_delete') && $speaker->photo) {
            File::delete($this->uploadPath . $speaker->photo);
            $validated['photo'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($speaker->photo) {
                File::delete($this->uploadPath . $speaker->photo);
            }

            $validated['photo'] = $this->storePhoto($request);
        }

        if ($validated['status'] === 'approved') {
            $validated['approved_by'] = auth()->user()->name ?? 'Admin';
            $validated['approved_at'] = $speaker->approved_at ?: now();
        } else {
            $validated['approved_by'] = null;
            $validated['approved_at'] = null;
        }

        $speaker->update($validated);

        return redirect()
            ->route('clientSpeakers')
            ->with('success', 'Client speaker updated successfully');
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

    private function validateSpeaker(Request $request): array
    {
        return $request->validate([
            'user_id' => 'nullable|integer',
            'email' => 'required|email|max:255',
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:3000',
            'website' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:10048',
            'status' => 'required|string|in:pending,approved,rejected',
            'admin_message' => 'nullable|string|max:1000',
            'display_order' => 'nullable|integer|min:0|max:9999',
        ]);
    }

    private function storePhoto(Request $request): ?string
    {
        if (!$request->hasFile('photo')) {
            return null;
        }

        $file = $request->file('photo');
        $photoName = time() . rand(1111, 9999) . '.' . $file->getClientOriginalExtension();
        $file->move($this->uploadPath, $photoName);

        if (class_exists('Helper')) {
            \Helper::imageResize($this->uploadPath . $photoName);
            \Helper::imageOptimize($this->uploadPath . $photoName);
        }

        return $photoName;
    }
}
