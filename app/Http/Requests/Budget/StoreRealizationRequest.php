<?php

namespace App\Http\Requests\Budget;

use App\Models\ActivityBudget;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRealizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'activity_budget_id' => ['required', Rule::exists(ActivityBudget::class, 'id')],
            'realization_type' => ['required', 'string', 'in:surat_pesanan,non_pengadaan'],
            'amount' => ['required', 'numeric', 'min:0'],
            'realization_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'receipt_number' => ['nullable', 'string', 'max:50'],

            // Dokumen pencairan
            'bast_number' => ['nullable', 'string', 'max:100'],
            'bast_date' => ['nullable', 'date'],
            'bap_number' => ['nullable', 'string', 'max:100'],
            'bap_date' => ['nullable', 'date'],
            'ba_penyerahan_number' => ['nullable', 'string', 'max:100'],
            'ba_penyerahan_date' => ['nullable', 'date'],
            'sp2d_number' => ['nullable', 'string', 'max:100'],
            'sp2d_date' => ['nullable', 'date'],
            'spp_number' => ['nullable', 'string', 'max:100'],
            'spp_date' => ['nullable', 'date'],
            'spm_number' => ['nullable', 'string', 'max:100'],
            'spm_date' => ['nullable', 'date'],
            'sptjb_number' => ['nullable', 'string', 'max:100'],
            'sptjb_date' => ['nullable', 'date'],

            // Pengadaan
            'procurement_type' => ['nullable', 'required_if:realization_type,surat_pesanan', 'string', 'in:surat_pesanan,spk'],
            'procurement_title' => ['nullable', 'required_if:realization_type,surat_pesanan', 'string', 'max:255'],
            'procurement_number' => ['nullable', 'required_if:realization_type,surat_pesanan', 'string', 'max:100'],
            'procurement_date' => ['nullable', 'required_if:realization_type,surat_pesanan', 'date'],
            'work_duration' => ['nullable', 'string', 'max:100'],
            'nota_dinas_number' => ['nullable', 'string', 'max:100'],
            'nota_dinas_date' => ['nullable', 'date'],
            'ba_pl_number' => ['nullable', 'string', 'max:100'],
            'ba_pl_date' => ['nullable', 'date'],
            'ppk_id' => ['nullable', 'exists:users,id'],
            'kpa_id' => ['nullable', 'exists:users,id'],

            // Vendor
            'vendor_name' => ['nullable', 'required_if:realization_type,surat_pesanan', 'string', 'max:255'],
            'vendor_npwp' => ['nullable', 'string', 'max:50'],
            'vendor_address' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],

            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.budget_item_id' => ['required', 'exists:budget_items,id'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.volume' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_pph21' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_pph21_mixed' => ['nullable', 'boolean'],
            'items.*.tax_pph22' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_pph23' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_ppn' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],
        ];
    }
}
