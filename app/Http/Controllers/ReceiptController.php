<?php
namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Trial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReceiptController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        Log::info("Receipt store called by user ID: {$user->id}");

        // 1. Check profile updated
        if (! $user->profile_updated) {
            return response()->json(['message' => 'Please complete your profile first.'], 403);
        }

        // 2. Subscription and trial check
        if (! $user->is_subscribed) {
            $trial = $user->trial;
            if (! $trial) {
                $trial = Trial::create(['user_id' => $user->id, 'started_at' => now()]);
            } else {
                $trialEnds = $trial->started_at->copy()->addDays(3);
                if (now()->greaterThan($trialEnds)) {
                    return response()->json([
                        'message' => 'Your 3-day free trial has ended. Please subscribe.',
                        'subscribe' => true,
                        'payment_url' => route('paystack.init')
                    ], 402);
                }
            }
        }

        // 3. Validate request
        $validated = $request->validate([
            'buyer_fullname'    => 'required|string',
            'payment_status'    => 'required|in:full,half',
            'delivery_status'   => 'required|in:pending,in_progress,completed',
            'items'             => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.quantity'  => 'required|integer|min:1',
            'items.*.price'     => 'required|numeric|min:0',
        ]);

        // 4. Generate unique receipt number
        $datePart = now()->format('Ymd');
        do {
            $uniqueCode = strtoupper(Str::random(6));
            $receiptNumber = "VHB-{$datePart}-{$uniqueCode}";
        } while (Receipt::where('receipt_number', $receiptNumber)->exists());

        // 5. Create receipt
        $receipt = Receipt::create([
            'vendor_id'       => $user->id,
            'buyer_fullname'  => $validated['buyer_fullname'],
            'payment_status'  => $validated['payment_status'],
            'delivery_status' => $validated['delivery_status'],
            'receipt_number'  => $receiptNumber,
        ]);

        foreach ($validated['items'] as $item) {
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

        return response()->json($receipt);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Receipt::with('items')->where('vendor_id', $user->id)->orderBy('created_at', 'desc');

        // Search by buyer fullname or receipt number
        if ($search = $request->query('search')) {
            $query->where(function($q) use ($search) {
                $q->where('buyer_fullname', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%");
            });
        }

        $receipts = $query->get()->groupBy('buyer_fullname'); // Group by buyer

        return response()->json([
            'message'  => 'Receipts fetched successfully.',
            'receipts' => $receipts
        ]);
    }
}
