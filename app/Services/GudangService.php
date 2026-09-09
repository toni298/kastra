<?php
namespace App\Services;
use App\Models\Gudang; use Illuminate\Support\Facades\DB;
class GudangService { public function create(string $companyId,array $data): Gudang { return DB::transaction(fn()=>Gudang::create([...$data,'company_id'=>$companyId])); } public function update(Gudang $gudang,array $data): Gudang { return DB::transaction(function() use ($gudang,$data){ $gudang->update($data); return $gudang->refresh(); }); } public function delete(Gudang $gudang): void { DB::transaction(fn()=>$gudang->delete()); } }
