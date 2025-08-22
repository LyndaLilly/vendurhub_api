<?php
namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $selectedImageUrl;
    public $paymentProofUrl;

    public function __construct(Order $order)
    {
        $this->order = $order;

        // URLs are already full
        $this->selectedImageUrl = $order->image_choice ?: null;
        $this->paymentProofUrl  = $order->payment_proof ?: null;
    }

    public function build()
    {
        return $this->subject('New Order Placed')
            ->view('emails.order_placed')
            ->with([
                'order'            => $this->order,
                'selectedImageUrl' => $this->selectedImageUrl,
                'paymentProofUrl'  => $this->paymentProofUrl,
            ]);
    }
}
