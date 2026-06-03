<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::orderBy('id', 'desc')->get();
        $stats = [
            'total' => $templates->count(),
            'active' => $templates->where('is_active', 1)->count(),
            'inactive' => $templates->where('is_active', 0)->count(),
        ];

        return view("admin.marketings.email-templates", compact('templates', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|integer|exists:email_templates,id',
            'name' => 'required|string|max:255',
            'is_active' => 'required|boolean',
            'template' => 'required|string',
        ]);

        $payload = [
            'name' => $data['name'],
            'is_active' => $data['is_active'],
            'template' => $data['template'],
        ];

        $template = $request->filled('id')
            ? tap(EmailTemplate::findOrFail($data['id']))->update($payload)
            : EmailTemplate::create($payload);

        return response()->json([
            'status' => true,
            'message' => $request->filled('id')
                ? 'Email template updated successfully'
                : 'Email template created successfully',
            'template' => $template,
        ]);
    }

    public function show($id)
    {
        $template = EmailTemplate::find($id);

        if (!$template) {
            return response()->json(['status' => false, 'message' => 'Email template not found'], 404);
        }

        return response()->json($template);
    }

    public function destroy($id)
    {
        $template = EmailTemplate::find($id);

        if (!$template) {
            return response()->json(['status' => false, 'message' => 'Email template not found'], 404);
        }

        $template->delete();

        return response()->json(['status' => true, 'message' => 'Email template deleted successfully']);
    }
}
