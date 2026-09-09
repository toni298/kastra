<?php
namespace App\Policies; use App\Models\User;
abstract class OrganizationPolicy { protected string $module; public function viewAny(User $user): bool { return $user->can("{$this->module}.view"); } public function create(User $user): bool { return $user->can("{$this->module}.create"); } public function update(User $user, object $model): bool { return $user->can("{$this->module}.edit") && $user->company_id === $model->company_id; } public function delete(User $user, object $model): bool { return $user->can("{$this->module}.delete") && $user->company_id === $model->company_id; } }
