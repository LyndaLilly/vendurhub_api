<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PDF;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $attachReceipt;
    public $productImageUrl;

    public function __construct(Order $order, $attachReceipt = false)
    {
        $this->order = $order;
        $this->attachReceipt = $attachReceipt;

        $this->productImageUrl = null;
        if ($order->product && $order->product->images->count() > 0 && in_array($order->image_choice, ['1', '2', '3'])) {
            $index = intval($order->image_choice) - 1;
            if (isset($order->product->images[$index])) {
                $this->productImageUrl = asset('storage/' . $order->product->images[$index]->image_path);
            }
        }
    }

   public function build()
{
    $mail = $this->subject('Order Status Updated')
                 ->view('emails.order_status_updated');

    if ($this->attachReceipt && $this->order->status === 'approved') {
        $pdf = PDF::loadView('emails.order_receipt_pdf', [
            'order' => $this->order,
            'productImageUrl' => $this->productImageUrl,
        ]);
        $mail->attachData($pdf->output(), 'OrderReceipt_' . $this->order->id . '.pdf');
    }

    return $mail;
}

}
