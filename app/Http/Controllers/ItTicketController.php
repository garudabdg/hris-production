<?php

namespace App\Http\Controllers;

use App\Models\ItTicket;
use App\Models\ItTicketResponse;
use App\Models\User;
use App\Notifications\NewItTicketNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ItTicketController extends Controller
{
    // ── Helpers ────────────────────────────────────────────────────────────────

    private function scopedQuery()
    {
        $user  = auth()->user();
        $query = ItTicket::with(['pemohon', 'assignedTo', 'cabang']);

        if ($user->isSuperAdmin() || $user->hasRole('it staff')) {
            return $query; // Lihat semua tiket
        }

        // User biasa hanya lihat tiket miliknya sendiri atau di cabangnya
        $userCabangs = $user->getCabangCodes();
        $query->where(function ($q) use ($user, $userCabangs) {
            $q->where('pemohon_id', $user->id);
            if (!empty($userCabangs)) {
                $q->orWhereIn('kode_cabang', $userCabangs);
            }
        });

        return $query;
    }

    private function authorizeTicket(ItTicket $ticket)
    {
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->hasRole('it staff')) return;

        $userCabangs = $user->getCabangCodes();
        $ownTicket   = $ticket->pemohon_id === $user->id;
        $sameCabang  = in_array($ticket->kode_cabang, $userCabangs);

        if (!$ownTicket && !$sameCabang) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }
    }

    // ── Index ──────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = $this->scopedQuery();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nomor_tiket', 'like', '%' . $request->search . '%')
                  ->orWhere('judul', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('prioritas')) $query->where('prioritas', $request->prioritas);
        if ($request->filled('kategori'))  $query->where('kategori', $request->kategori);
        if ($request->filled('kode_cabang')) $query->where('kode_cabang', $request->kode_cabang);

        $tickets = $query->orderByRaw("FIELD(prioritas,'critical','high','medium','low')")
                         ->orderByDesc('created_at')
                         ->paginate(15)->withQueryString();

        $cabang  = $user->getCabang();

        $summary = [
            'total'       => $this->scopedQuery()->count(),
            'open'        => $this->scopedQuery()->whereIn('status', ['open', 'in_progress'])->count(),
            'pending'     => $this->scopedQuery()->where('status', 'pending')->count(),
            'resolved'    => $this->scopedQuery()->where('status', 'resolved')->count(),
            'overdue'     => $this->scopedQuery()->where('tanggal_target', '<', now())
                                  ->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];

        $view = $user->hasRole('karyawan') ? 'it-ticket.index-mobile' : 'it-ticket.index';
        return view($view, compact('tickets', 'cabang', 'summary'));
    }

    // ── Create / Store ─────────────────────────────────────────────────────────

    public function create()
    {
        $user   = auth()->user();
        $cabang = $user->getCabang();
        $view = $user->hasRole('karyawan') ? 'it-ticket.create-mobile' : 'it-ticket.create';
        return view($view, compact('cabang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'            => 'required|string|max:255',
            'deskripsi'        => 'required|string',
            'kategori'         => 'required|in:hardware,software,jaringan,keamanan,akses,data,lainnya',
            'prioritas'        => 'required|in:critical,high,medium,low',
            'klasifikasi_data' => 'required|in:confidential,internal,public',
            'dampak'           => 'required|in:individu,departemen,cabang,perusahaan',
            'kode_cabang'      => 'nullable|exists:cabang,kode_cabang',
            'lampiran'         => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xlsx,zip|max:5120',
        ]);

        // Validasi akses cabang
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $request->filled('kode_cabang')) {
            if (!in_array($request->kode_cabang, $user->getCabangCodes())) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke cabang tersebut.');
            }
        }

        $data = $request->except('lampiran');
        $data['nomor_tiket']    = ItTicket::generateNomor();
        $data['pemohon_id']     = $user->id;
        $data['status']         = 'open';
        $data['tanggal_target'] = now()->addDays(ItTicket::slaDays($request->prioritas))->toDateString();

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $filename = 'tkt_' . Str::random(12) . '.' . $file->extension();
            $file->storeAs('it-tickets', $filename, 'public');
            $data['lampiran'] = $filename;
        }

        $ticket = ItTicket::create($data);

        // Auto-log activity
        ItTicketResponse::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'pesan'     => "Tiket dibuat oleh **{$user->name}** dengan prioritas **{$request->prioritas}**.",
            'tipe'      => 'status_change',
        ]);

        // Kirim notifikasi ke semua IT Staff + Super Admin
        try {
            $ticket->load('pemohon');
            $recipients = User::role(['it staff', 'super admin'])->where('id', '!=', $user->id)->get();
            Notification::send($recipients, new NewItTicketNotification($ticket));
        } catch (\Exception $e) {
            \Log::warning('Gagal kirim notifikasi IT ticket: ' . $e->getMessage());
        }

        return redirect()->route('it-ticket.show', $ticket->id)
                         ->with('success', "Tiket {$ticket->nomor_tiket} berhasil dibuat.");
    }

    // ── Show ───────────────────────────────────────────────────────────────────

    public function show(ItTicket $itTicket)
    {
        $this->authorizeTicket($itTicket);
        $itTicket->load(['pemohon', 'assignedTo', 'resolvedBy', 'cabang', 'responses.user']);

        $user     = auth()->user();
        $itStaffs = User::role('it staff')->orderBy('name')->get();
        $canManage = $user->isSuperAdmin() || $user->hasRole('it staff');

        $view = $user->hasRole('karyawan') ? 'it-ticket.show-mobile' : 'it-ticket.show';
        return view($view, compact('itTicket', 'itStaffs', 'canManage'));
    }

    // ── Response ───────────────────────────────────────────────────────────────

    public function respond(Request $request, ItTicket $itTicket)
    {
        $this->authorizeTicket($itTicket);

        $request->validate([
            'pesan'    => 'required|string',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xlsx,zip|max:5120',
        ]);

        $user = auth()->user();
        $data = [
            'ticket_id' => $itTicket->id,
            'user_id'   => $user->id,
            'pesan'     => $request->pesan,
            'tipe'      => 'response',
        ];

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $filename = 'tkt_resp_' . Str::random(12) . '.' . $file->extension();
            $file->storeAs('it-tickets', $filename, 'public');
            $data['lampiran'] = $filename;
        }

        $response = ItTicketResponse::create($data);
        $response->load('user');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'response' => [
                    'id'         => $response->id,
                    'pesan'      => $response->pesan,
                    'user_name'  => $response->user->name ?? '-',
                    'user_initial' => strtoupper(substr($response->user->name ?? '?', 0, 1)),
                    'created_at' => $response->created_at->format('d/m H:i'),
                    'lampiran'   => $response->lampiran
                        ? asset('storage/it-tickets/' . $response->lampiran)
                        : null,
                ],
            ]);
        }

        return redirect()->route('it-ticket.show', $itTicket->id)->with('success', 'Balasan berhasil dikirim.');
    }

    // ── Get Responses (AJAX Polling) ───────────────────────────────────────────

    public function getResponses(Request $request, ItTicket $itTicket)
    {
        $this->authorizeTicket($itTicket);

        $after = $request->query('after', 0); // last response id client sudah punya

        $responses = $itTicket->responses()
            ->with('user')
            ->where('id', '>', (int) $after)
            ->orderBy('id')
            ->get()
            ->map(fn($r) => [
                'id'           => $r->id,
                'pesan'        => $r->pesan,
                'user_name'    => $r->user->name ?? '-',
                'user_initial' => strtoupper(substr($r->user->name ?? '?', 0, 1)),
                'created_at'   => $r->created_at->format('d/m H:i'),
                'lampiran'     => $r->lampiran
                    ? asset('storage/it-tickets/' . $r->lampiran)
                    : null,
            ]);

        // Cek juga perubahan status tiket
        $itTicket->refresh();

        return response()->json([
            'responses' => $responses,
            'status'    => $itTicket->status,
        ]);
    }

    // ── Update Status (IT Staff / Super Admin) ────────────────────────────────

    public function updateStatus(Request $request, ItTicket $itTicket)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->hasRole('it staff')) {
            abort(403);
        }

        $request->validate([
            'status'           => 'required|in:open,in_progress,pending,resolved,closed',
            'catatan_resolusi' => 'nullable|string',
        ]);

        $oldStatus = $itTicket->status;
        $newStatus = $request->status;

        $itTicket->status = $newStatus;
        if (in_array($newStatus, ['resolved', 'closed']) && !$itTicket->resolved_at) {
            $itTicket->resolved_at      = now();
            $itTicket->resolved_by      = $user->id;
            $itTicket->catatan_resolusi = $request->catatan_resolusi;
        }
        $itTicket->save();

        ItTicketResponse::create([
            'ticket_id' => $itTicket->id,
            'user_id'   => $user->id,
            'pesan'     => "Status diubah dari **{$oldStatus}** menjadi **{$newStatus}**."
                         . ($request->catatan_resolusi ? "\n\nCatatan: " . $request->catatan_resolusi : ''),
            'tipe'      => in_array($newStatus, ['resolved', 'closed']) ? 'resolusi' : 'status_change',
        ]);

        return redirect()->route('it-ticket.show', $itTicket->id)->with('success', 'Status tiket diperbarui.');
    }

    // ── Assign (IT Staff / Super Admin) ───────────────────────────────────────

    public function assign(Request $request, ItTicket $itTicket)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->hasRole('it staff')) {
            abort(403);
        }

        $request->validate(['assigned_to' => 'required|exists:users,id']);

        $assignee          = User::find($request->assigned_to);
        $itTicket->assigned_to = $request->assigned_to;
        if ($itTicket->status === 'open') {
            $itTicket->status = 'in_progress';
        }
        $itTicket->save();

        ItTicketResponse::create([
            'ticket_id' => $itTicket->id,
            'user_id'   => $user->id,
            'pesan'     => "Tiket di-assign ke **{$assignee->name}** oleh **{$user->name}**.",
            'tipe'      => 'assignment',
        ]);

        return redirect()->route('it-ticket.show', $itTicket->id)->with('success', 'Tiket berhasil di-assign.');
    }

    // ── Destroy ────────────────────────────────────────────────────────────────

    public function destroy(ItTicket $itTicket)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) abort(403);

        if ($itTicket->lampiran && Storage::disk('public')->exists('it-tickets/' . $itTicket->lampiran)) {
            Storage::disk('public')->delete('it-tickets/' . $itTicket->lampiran);
        }
        $itTicket->delete();

        return redirect()->route('it-ticket.index')->with('success', 'Tiket berhasil dihapus.');
    }
}
