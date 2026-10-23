<?php
namespace App\Http\Controllers\Admin;
use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CompanyApprovalController extends Controller
{
    private function authorizeReviewer(Request $request): void
    {
        abort_unless(in_array(strtolower((string)$request->user()?->user_type), [UserType::ADMIN, UserType::SUPER_ADMIN], true), 403);
        abort_unless($request->user()->can('employer-list'), 403);
    }
    public function index(Request $request)
    {
        $this->authorizeReviewer($request);
        $filter = $request->query('status', 'pending');
        abort_unless(in_array($filter, ['pending','approved','all'], true), 422);
        $query = User::whereIn('user_type', [UserType::EMPLOYER, 'Employer']);
        $pendingCount = (clone $query)->where(function ($query) {
            $query->whereNull('employer_approved_at')->orWhereNull('status')->orWhere('status', '!=', 1);
        })->count();
        if ($filter === 'pending') $query->where(function ($query) {
            $query->whereNull('employer_approved_at')->orWhereNull('status')->orWhere('status', '!=', 1);
        });
        if ($filter === 'approved') $query->whereNotNull('employer_approved_at')->where('status', 1);
        return response()->view('admin.companies.approvals', [
            'companies' => $query->latest('id')->paginate(30)->withQueryString(),
            'filter' => $filter, 'pendingCount' => $pendingCount,
        ])->header('Cache-Control', 'private, no-store');
    }
    public function decide(Request $request, int $user)
    {
        $this->authorizeReviewer($request);
        $data = $request->validate(['decision' => ['required', 'in:approve,revoke'], 'reason' => ['nullable', 'string', 'max:1000']]);
        DB::transaction(function () use ($request, $user, $data) {
            $account = User::whereIn('user_type', [UserType::EMPLOYER, 'Employer'])->lockForUpdate()->findOrFail($user);
            $approved = $data['decision'] === 'approve';
            $account->forceFill(['employer_approved_at' => $approved ? now() : null,
                'employer_approved_by' => $approved ? $request->user()->id : null,
                'status' => $approved ? 1 : 0])->save();
            if (!$approved) $account->tokens()->delete();
            DB::table('employer_review_events')->insert(['employer_id' => $account->id, 'actor_id' => $request->user()->id,
                'decision' => $data['decision'], 'reason' => $data['reason'] ?? null, 'created_at' => now()]);
        });
        return back()->with('success', 'Company approval decision saved.');
    }
}
