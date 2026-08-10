<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\InfluencerRegister;
use App\Models\WebmasterSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InfluencerRegisterController extends Controller
{
    public function index(Request $request)
    {
        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderby('row_no', 'asc')
            ->get();

        $query = InfluencerRegister::query();

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $influencers = $query
            ->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->appends($request->query());

        $stats = (object) [
            'total' => InfluencerRegister::count(),
            'pending' => InfluencerRegister::where('status', 'pending')->count(),
            'approved' => InfluencerRegister::where('status', 'approved')->count(),
        ];

        return view('dashboard.influencer_registers.list', compact('GeneralWebmasterSections', 'influencers', 'stats'));
    }

    public function view(int $id)
    {
        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderby('row_no', 'asc')
            ->get();

        $influencer = InfluencerRegister::findOrFail($id);

        $referralUsers = DB::table('users_registers as ur')
            ->where('ur.referred_by_influencer_id', $influencer->id)
            ->orderBy('ur.created_at', 'DESC')
            ->select([
                'ur.id',
                'ur.full_name',
                'ur.email',
                'ur.phone',
                'ur.company_name',
                'ur.user_type',
                'ur.nationality',
                'ur.created_at',
                'ur.influencer_referral_code',
            ])
            ->get();

        return view('dashboard.influencer_registers.view', compact('GeneralWebmasterSections', 'influencer', 'referralUsers'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'approval_message' => 'nullable|string|max:2000',
        ]);

        $influencer = InfluencerRegister::findOrFail($id);
        $influencer->status = $request->status;
        $influencer->approval_message = $request->approval_message;
        $influencer->save();

        return redirect()
            ->back()
            ->with('profxSwalSuccess', 'Influencer status updated to ' . ucfirst($request->status) . '.');
    }
}
