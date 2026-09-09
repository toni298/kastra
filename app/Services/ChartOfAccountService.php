<?php
namespace App\Services;
use App\Models\ChartOfAccount; use Illuminate\Support\Facades\DB;
class ChartOfAccountService { public function create(string $companyId,array $data): ChartOfAccount{return DB::transaction(fn()=>ChartOfAccount::create([...$data,'company_id'=>$companyId]));} public function update(ChartOfAccount $account,array $data): ChartOfAccount{return DB::transaction(function()use($account,$data){$account->update($data);return $account->refresh();});} public function delete(ChartOfAccount $account):void{if($account->is_system)abort(403,'Akun sistem tidak dapat dihapus.');DB::transaction(fn()=>$account->delete());} }
