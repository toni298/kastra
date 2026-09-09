# Sales Transactions Gold Standard

`/sales/transactions` is the reference architecture for all new, refactored, and audited modules in Kastra.

## Authorization and Inertia

- Share permissions with Inertia only as `list<string>` permission names.
- Use `InertiaAuthorizationService` for request-scoped authorization snapshots.
- Pass page capabilities once at the root level, for example `capabilities.create`, `capabilities.edit`, and `capabilities.delete`.
- Never serialize Eloquent `Role` or `Permission` collections into Inertia props.

## Models

- Do not call `$user->can()`, `$user->hasPermissionTo()`, `Gate::allows()`, or permission relations from accessors, casts, `$appends`, or serialization hooks.
- Models must not load permission relations as side effects of JSON serialization.

## Resources

- `JsonResource::toArray()` must not perform permission checks per row or per child item.
- Use `relationLoaded()` before accessing optional/heavy relations so list resources cannot trigger lazy loading.
- Return page-level capabilities from controllers, not from individual resource rows.

## Policies and Gates

- Policies check tenant ownership and use the request-scoped `InertiaAuthorizationService` snapshot for permission names.
- Do not call Spatie `$user->can()` inside high-traffic policies when the snapshot service is available.
- Do not issue manual permission queries inside loops.

## Controllers and Queries

- List endpoints select only table columns and eager-load only lightweight relations displayed in the table.
- Use aggregates such as `withCount()` and `withSum()` instead of loading child collections on list pages.
- Do not eager-load permission/role relations unless an explicit authorization administration screen needs them.
- Detail drawers use a dedicated tenant-scoped `findWithDetails()` query and fetch JSON on demand.
- Frontend drawers show a loading state while the detail endpoint resolves.

## Required Review Checklist

Before completing a module refactor, verify:

1. Inertia permissions are string arrays, not Eloquent models.
2. No permission check occurs in a resource loop or model accessor.
3. The list query has no unnecessary detail/payment/product eager loads.
4. Detail data is loaded by a dedicated endpoint only when requested.
5. Page capabilities are global booleans and authorization remains tenant-scoped.
