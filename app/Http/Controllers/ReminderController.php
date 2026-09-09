<?php

namespace App\Http\Controllers;

use App\Actions\ReminderCalendarService;
use App\Http\Requests\Reminder\StoreReminderRequest;
use App\Http\Requests\Reminder\UpdateReminderRequest;
use App\Models\Reminder;
use App\Models\CashBankAccount;
use App\Http\Resources\CashBankAccountResource;
use App\Repositories\ReminderRepository;
use App\Services\CompanyContext;
use App\Services\ReminderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ReminderController extends Controller
{
    public function __construct(
        private CompanyContext $companyContext,
        private ReminderService $reminderService,
        private ReminderRepository $reminderRepository,
        private ReminderCalendarService $calendarService,
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Reminder::class);

        $companyId = $this->companyId();
        $filters = [
            'tab' => $request->input('tab', 'semua'),
            'search' => $request->input('search', ''),
            'transaction_type' => $request->input('transaction_type', ''),
            'month' => $request->input('month', now()->format('Y-m')),
        ];

        $reminders = $this->reminderRepository->paginate($companyId, [
            ...$filters,
            // Filter bulan hanya relevan untuk tampilan kalender (tab "semua");
            // tab lain (Jatuh Tempo, Terjadwal, Selesai) menampilkan lintas bulan.
            'month' => $filters['tab'] === 'semua' ? $filters['month'] : null,
        ]);
        $calendarEvents = $this->calendarService->buildEvents($companyId, $filters['month']);
        $stats = $this->calendarService->buildStats($companyId);

        return Inertia::render('Reminders/Index', [
            'reminders' => $reminders,
            'calendarData' => [
                'month' => $filters['month'],
                'events' => $calendarEvents->values(),
            ],
            'cashBankAccounts' => CashBankAccountResource::collection(
                CashBankAccount::query()
                    ->with('settings')
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get()
            )->resolve($request),
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    public function store(StoreReminderRequest $request): RedirectResponse
    {
        Gate::authorize('create', Reminder::class);

        $this->reminderService->create($this->companyId(), $request->validated());

        return back()->with('success', 'Event berhasil dibuat.');
    }

    public function update(UpdateReminderRequest $request, Reminder $reminder): RedirectResponse
    {
        Gate::authorize('update', $reminder);

        $this->reminderService->update($reminder, $request->validated());

        return back()->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Request $request, Reminder $reminder): RedirectResponse
    {
        Gate::authorize('delete', $reminder);

        if ($request->input('scope') === 'this' && $reminder->rrule && $request->filled('occurrence_start')) {
            $this->reminderService->deleteOccurrence($reminder, $request->string('occurrence_start')->toString());
        } elseif ($request->input('scope') === 'future' && $reminder->rrule && $request->filled('occurrence_start')) {
            $this->reminderService->deleteFuture($reminder, $request->string('occurrence_start')->toString());
        } else {
            $this->reminderService->delete($reminder);
        }

        return back()->with('success', 'Event berhasil dihapus.');
    }

    public function complete(Request $request, Reminder $reminder): RedirectResponse
    {
        Gate::authorize('update', $reminder);

        $this->reminderService->complete($reminder);

        return back()->with('success', 'Reminder ditandai selesai.');
    }

    public function cancel(Request $request, Reminder $reminder): RedirectResponse
    {
        Gate::authorize('update', $reminder);

        $this->reminderService->cancel($reminder);

        return back()->with('success', 'Reminder dibatalkan.');
    }

    private function resolveSeries(Reminder $reminder, ?string $idReminder): Reminder
    {
        if ($idReminder && $idReminder !== $reminder->id) {
            return Reminder::findOrFail($idReminder);
        }

        return $reminder;
    }

    private function companyId(): string
    {
        return (string) $this->companyContext->id();
    }
}
