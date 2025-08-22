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

        // Convert image_choice filename to URL
        $this->selectedImageUrl = $order->image_choice && $order->image_choice !== 'null'
        ? asset('storage/product_images/' . $order->image_choice)
        : null;

        // Convert payment proof filename to URL
        $this->paymentProofUrl = $order->payment_proof
        ? asset('storage/payment_proofs/' . $order->payment_proof)
        : null;
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
