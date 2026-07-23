<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\WebmasterSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AwardNominationController extends Controller
{
    public function index(Request $request)
    {
        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderBy('row_no', 'asc')
            ->get();

        $categoryStats = DB::table('award_nominations')
            ->select('category', DB::raw('COUNT(*) as total'), DB::raw("SUM(CASE WHEN status = 'winner' THEN 1 ELSE 0 END) as winners"))
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $selectedCategory = $request->get('category', optional($categoryStats->first())->category);

        $query = DB::table('award_nominations');

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('award_title')) {
            $query->where('award_title', $request->award_title);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $nominations = $query->orderByRaw("CASE WHEN status = 'winner' THEN 0 WHEN status = 'pending' THEN 1 ELSE 2 END")
            ->orderBy('award_title')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->query());

        $stats = (object) [
            'total' => DB::table('award_nominations')->count(),
            'pending' => DB::table('award_nominations')->where('status', 'pending')->count(),
            'winners' => DB::table('award_nominations')->where('status', 'winner')->count(),
            'rejected' => DB::table('award_nominations')->where('status', 'rejected')->count(),
            'categories' => $categoryStats->count(),
        ];

        $awardTitles = DB::table('award_nominations')
            ->when($selectedCategory, function ($query) use ($selectedCategory) {
                $query->where('category', $selectedCategory);
            })
            ->select('award_title')
            ->whereNotNull('award_title')
            ->distinct()
            ->orderBy('award_title')
            ->pluck('award_title');

        return view('dashboard.awards.nominations', compact(
            'GeneralWebmasterSections',
            'nominations',
            'stats',
            'categoryStats',
            'selectedCategory',
            'awardTitles'
        ));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(['pending', 'winner', 'rejected'])],
            'winner_note' => 'nullable|string|max:1000',
        ]);

        $nomination = DB::table('award_nominations')->where('id', $id)->first();

        if (!$nomination) {
            return redirect()->back()->with('error', 'Nomination not found.');
        }

        DB::table('award_nominations')->where('id', $id)->update([
            'status' => $request->status,
            'winner_note' => $request->winner_note,
            'winner_selected_at' => $request->status === 'winner' ? now() : null,
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('awardNominations', ['category' => $nomination->category, 'award_title' => request('award_title')])
            ->with('success', 'Award nomination status updated successfully.');
    }

    public function selectWinner(Request $request, $id)
    {
        $request->merge(['status' => 'winner']);
        return $this->updateStatus($request, $id);
    }

    public function resetWinner($id)
    {
        $nomination = DB::table('award_nominations')->where('id', $id)->first();

        if (!$nomination) {
            return redirect()->back()->with('error', 'Nomination not found.');
        }

        DB::table('award_nominations')->where('id', $id)->update([
            'status' => 'pending',
            'winner_note' => null,
            'winner_selected_at' => null,
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('awardNominations', ['category' => $nomination->category])
            ->with('success', 'Winner status removed.');
    }
}