<?php
namespace App\Services;
use App\Models\TaxConfiguration; use Illuminate\Support\Facades\DB;
class TaxConfigurationService { public function create(string $companyId,array $data): TaxConfiguration{return DB::transaction(fn()=>TaxConfiguration::create([...$data,'company_id'=>$companyId]));} public function update(TaxConfiguration $tax,array $data):TaxConfiguration{return DB::transaction(function()use($tax,$data){$tax->update($data);return $tax->refresh();});} public function delete(TaxConfiguration $tax):void{DB::transaction(fn()=>$tax->delete());} }
