<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoPopup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PromoPopupController extends Controller
{
    /**
     * Listing page (DataTables ajax + modal CRUD).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PromoPopup::query()
                ->select(['id', 'title', 'image', 'link_type', 'link_target', 'is_active', 'start_at', 'end_at', 'sort_order', 'updated_at'])
                ->orderBy('sort_order')
                ->orderByDesc('id');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image', function ($p) {
                    return '<img src="' . asset($p->image) . '" class="rounded shadow-sm" style="width:64px;height:64px;object-fit:cover">';
                })
                ->addColumn('title_info', function ($p) {
                    $html = '<div class="fw-bold text-dark">' . e(Str::limit($p->title, 60)) . '</div>';
                    $html .= '<div class="text-muted small">' . $this->linkLabel($p) . '</div>';
                    return $html;
                })
                ->addColumn('schedule', function ($p) {
                    if (!$p->start_at && !$p->end_at) {
                        return '<span class="badge bg-light text-muted border">Selalu Tampil</span>';
                    }
                    $start = $p->start_at ? $p->start_at->format('d M Y H:i') : '—';
                    $end   = $p->end_at ? $p->end_at->format('d M Y H:i') : '—';
                    return '<div class="small"><i class="bi bi-calendar-event me-1"></i>' . $start . '<br><i class="bi bi-calendar-check me-1"></i>' . $end . '</div>';
                })
                ->editColumn('is_active', function ($p) {
                    return $p->is_active
                        ? '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Aktif</span>'
                        : '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle">Nonaktif</span>';
                })
                ->addColumn('action', function ($p) {
                    return '
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-sm btn-light text-primary rounded-pill px-2 btn-edit-popup" data-id="' . $p->id . '" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light text-danger rounded-pill px-2 btn-delete-popup" data-id="' . $p->id . '" data-title="' . e($p->title) . '" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>';
                })
                ->rawColumns(['image', 'title_info', 'schedule', 'is_active', 'action'])
                ->make(true);
        }

        return view('admin.promo-popups.index');
    }

    /**
     * Return single popup data (for edit modal pre-fill).
     */
    public function show(PromoPopup $promo_popup): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id'                    => $promo_popup->id,
                'title'                 => $promo_popup->title,
                'image_url'             => asset($promo_popup->image),
                'link_type'             => $promo_popup->link_type,
                'link_target'           => $promo_popup->link_target,
                'button_label'          => $promo_popup->button_label,
                'start_at'              => optional($promo_popup->start_at)->format('Y-m-d\TH:i'),
                'end_at'                => optional($promo_popup->end_at)->format('Y-m-d\TH:i'),
                'display_delay'         => $promo_popup->display_delay,
                'show_once_per_session' => $promo_popup->show_once_per_session,
                'is_active'             => $promo_popup->is_active,
                'sort_order'            => $promo_popup->sort_order,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);

        $validated['image'] = $this->uploadImage($request);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['show_once_per_session'] = $request->boolean('show_once_per_session', true);

        $popup = PromoPopup::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Popup promo berhasil dibuat.',
            'data'    => $popup,
        ]);
    }

    public function update(Request $request, PromoPopup $promo_popup): JsonResponse
    {
        $validated = $this->validatePayload($request, $promo_popup);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadImage($request);
            // Remove the old file if it lives under our uploads folder
            $this->deleteOldImage($promo_popup->image);
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['show_once_per_session'] = $request->boolean('show_once_per_session', true);

        $promo_popup->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Popup promo berhasil diperbarui.',
        ]);
    }

    public function destroy(PromoPopup $promo_popup): JsonResponse
    {
        $this->deleteOldImage($promo_popup->image);
        $promo_popup->delete();

        return response()->json([
            'success' => true,
            'message' => 'Popup promo berhasil dihapus.',
        ]);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    /**
     * Shared validation rules for store + update.
     */
    protected function validatePayload(Request $request, ?PromoPopup $existing = null): array
    {
        $imageRule = $existing
            ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']
            : ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

        $rules = [
            'title'                 => 'required|string|max:255',
            'image'                 => $imageRule,
            'link_type'             => ['required', Rule::in(['none', 'product', 'page', 'url'])],
            'link_target'           => 'nullable|string|max:500',
            'button_label'          => 'nullable|string|max:100',
            'start_at'              => 'nullable|date',
            'end_at'                => 'nullable|date|after_or_equal:start_at',
            'display_delay'         => 'nullable|integer|min:0|max:60',
            'show_once_per_session' => 'nullable|boolean',
            'is_active'             => 'nullable|boolean',
            'sort_order'            => 'nullable|integer|min:0',
        ];

        $validated = $request->validate($rules, [
            'end_at.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
        ]);

        // Conditional: link_target is required if link_type != 'none'.
        if (($validated['link_type'] ?? 'none') !== 'none' && empty($validated['link_target'])) {
            abort(response()->json([
                'message' => 'Validasi gagal',
                'errors' => ['link_target' => ['Target link wajib diisi sesuai tipe link yang dipilih.']],
            ], 422));
        }

        // Default sort order to 0 if blank.
        if (!isset($validated['sort_order']) || $validated['sort_order'] === '') {
            $validated['sort_order'] = 0;
        }

        return $validated;
    }

    /**
     * Save the uploaded image and return its public-relative path.
     */
    protected function uploadImage(Request $request): string
    {
        $file = $request->file('image');
        $filename = 'popup_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $dir = public_path('uploads/promo_popups');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $file->move($dir, $filename);
        return 'uploads/promo_popups/' . $filename;
    }

    protected function deleteOldImage(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }
        $absolute = public_path($relativePath);
        if (File::exists($absolute) && str_starts_with($relativePath, 'uploads/promo_popups/')) {
            File::delete($absolute);
        }
    }

    protected function linkLabel(PromoPopup $p): string
    {
        return match ($p->link_type) {
            'product' => '<i class="bi bi-bag me-1"></i>Produk: ' . e($p->link_target),
            'page'    => '<i class="bi bi-file-earmark me-1"></i>Halaman: ' . e($p->link_target),
            'url'     => '<i class="bi bi-link-45deg me-1"></i>URL: ' . e(Str::limit($p->link_target, 40)),
            default   => '<i class="bi bi-dash-circle me-1"></i>Tanpa link',
        };
    }
}
