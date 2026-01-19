<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Product\Products;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $product;
    public $currentStock;
    public $threshold;

    /**
     * Create a new notification instance.
     */
    public function __construct(Products $product, $currentStock, $threshold)
    {
        $this->product = $product;
        $this->currentStock = $currentStock;
        $this->threshold = $threshold;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('⚠️ Alerta de Stock Bajo: ' . $this->product->name)
                    ->line('El producto **' . $this->product->name . '** tiene stock bajo.')
                    ->line('Stock actual: **' . $this->currentStock . '** unidades')
                    ->line('Umbral configurado: **' . $this->threshold . '** unidades')
                    ->action('Ver Inventario', url('/inventario'))
                    ->line('Por favor, considera reabastecer este producto.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_title' => $this->product->name,
            'current_stock' => $this->currentStock,
            'threshold' => $this->threshold,
            'sku' => $this->product->inventory?->sku,
            'message' => "Stock bajo: {$this->product->name} ({$this->currentStock} unidades disponibles)",
        ];
    }
}
