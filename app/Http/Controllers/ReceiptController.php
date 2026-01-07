<?php
namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Trial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReceiptController extends Controller
{
    const TRIAL_DAYS = 30;

    public function store(Request $request)
    {
        $user = $request->user();
        Log::info("Receipt store called by user ID: {$user->id}");

        // 1️⃣ Profile check
        if (! $user->profile_updated) {
            return response()->json(['message' => 'Please complete your profile first.'], 403);
        }

        // 2️⃣ Trial/subscription check
        // 2️⃣ Subscription or trial check
        if (! $user->hasActiveSubscription()) {

            $trial = $user->trial;

            if (! $trial) {
                return response()->json([
                    'message' => 'Start your free trial or upgrade to continue.',
                    'action'  => 'start_trial_or_upgrade',
                ], 403);
            }

            $trialEnds = $trial->started_at->copy()->addDays(self::TRIAL_DAYS);

            if (now()->greaterThan($trialEnds)) {
                return response()->json([
                    'message'     => 'Your free trial has ended. Please upgrade.',
                    'subscribe'   => true,
                    'payment_url' => route('paystack.init'),
                ], 402);
            }
        }

        // 3️⃣ Validate request
        $validated = $request->validate([
            'buyer_fullname'      => 'required|string',
            'payment_status'      => 'required|in:full,half',
            'delivery_status'     => 'required|in:pending,in_progress,completed',
            'amount_paid'         => 'nullable|numeric|min:0',
            'items'               => 'required|array|min:1',
            'items.*.item_name'   => 'required|string',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.price'       => 'required|numeric|min:0',
            'items.*.amount_paid' => 'nullable|numeric|min:0',
            'items.*.balance'     => 'nullable|numeric|min:0',
        ]);

        // 4️⃣ Process items: calculate balance for each
        $items = collect($validated['items'])->map(function ($item) {
            $total      = $item['price'] * $item['quantity'];
            $amountPaid = (! empty($item['price']) && $item['price'] > 0)
                ? ($item['amount_paid'] ?? 0)
                : 0;
            $balance = $total - $amountPaid;

            return array_merge($item, [
                'amount_paid' => $amountPaid,
                'balance'     => $balance,
            ]);
        });

        // 5️⃣ Calculate totals
        $subtotal        = $items->sum(fn($i) => $i['price'] * $i['quantity']);
        $totalAmountPaid = $items->sum('amount_paid');
        $totalBalance    = $subtotal - $totalAmountPaid;

        // 6️⃣ Generate unique receipt number
        $datePart = now()->format('Ymd');
        do {
            $uniqueCode    = strtoupper(Str::random(6));
            $receiptNumber = "VHB-{$datePart}-{$uniqueCode}";
        } while (Receipt::where('receipt_number', $receiptNumber)->exists());

        // 7️⃣ Create receipt
        $receipt = Receipt::create([
            'vendor_id'       => $user->id,
            'buyer_fullname'  => $validated['buyer_fullname'],
            'payment_status'  => $validated['payment_status'],
            'delivery_status' => $validated['delivery_status'],
            'amount_paid'     => $totalAmountPaid,
            'balance'         => $totalBalance,
            'receipt_number'  => $receiptNumber,
        ]);

        // 8️⃣ Save items (only once!)
        foreach ($items as $item) {
            $receipt->items()->create($item);
        }

        return response()->json([
            'message'        => 'Receipt created successfully',
            'receipt_id'     => $receipt->id,
            'receipt_number' => $receiptNumber,
            'receipt'        => $receipt->load('items'),
        ], 201);
    }

    public function show($id)
    {
        $receipt = Receipt::with('items')->where('vendor_id', auth()->id())->find($id);

        if (! $receipt) {
            return response()->json(['message' => 'Receipt not found'], 404);
        }

        return response()->json(['receipt' => $receipt]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Receipt::with('items')->where('vendor_id', $user->id)->orderBy('created_at', 'desc');

        // Search by buyer fullname or receipt number
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('buyer_fullname', 'like', "%{$search}%")
                    ->orWhere('receipt_number', 'like', "%{$search}%");
            });
        }

        $receipts = $query->get();
        return response()->json([
            'message'  => 'Receipts fetched successfully.',
            'receipts' => $receipts,
        ]);

    }
}
