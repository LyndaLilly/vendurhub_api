<?php
namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $attachReceipt;
    public $vendorLogo;
    public $vendorSignature;
    public $productImageUrl;

    public function __construct(Order $order, $attachReceipt = false)
    {
        $this->order         = $order;
        $this->attachReceipt = $attachReceipt;

        $profile = $order->vendor->profile ?? null;

        // Vendor assets using URLs instead of local paths
        $this->vendorLogo = ($profile && $profile->business_logo)
            ? url('uploads/' . ltrim($profile->business_logo, '/'))
            : null;

        $this->vendorSignature = ($profile && $profile->signature)
            ? url('uploads/' . ltrim($profile->signature, '/'))
            : null;

        // Product image using URL
        $this->productImageUrl = null;

        if ($order->product && $order->product->images->count() > 0) {
            if ($order->image_choice && str_starts_with($order->image_choice, 'http')) {
                $this->productImageUrl = $order->image_choice;
            } else {
                $this->productImageUrl = url(
                    'uploads/' . ltrim($order->product->images[0]->image_path, '/')
                );
            }
        }
    }

    public function build()
    {
        $mail = $this->subject('Order Status Updated')
            ->view('emails.order_status_updated', [
                'order' => $this->order,
            ]);

        if ($this->attachReceipt && $this->order->status === 'approved') {
            $pdf = Pdf::loadView('emails.order_receipt_pdf', [
                'order'           => $this->order,
                'vendorLogo'      => $this->vendorLogo,
                'vendorSignature' => $this->vendorSignature,
                'productImageUrl' => $this->productImageUrl,
            ])->setPaper('A4', 'portrait');

            $mail->attachData(
                $pdf->output(),
                'OrderReceipt_' . $this->order->id . '.pdf'
            );
        }

        return $mail;
    }
}
