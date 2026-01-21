<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(User $user, array $items): Order
    {
        return DB::transaction(function () use ($user, $items) {
            $totalAmount = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $unitPrice = (float) $item['unit_price'];
                $quantity = (int) $item['quantity'];

                $subTotal = round($unitPrice * $quantity, 2);
                $totalAmount += $subTotal;

                $orderItems[] = [
                    'product_name' => $item['product_name'],
                    'product_sku'  => $item['product_sku'] ?? null,
                    'quantity'     => $quantity,
                    'unit_price'   => $unitPrice,
                    'sub_total'    => $subTotal,
                ];
            }

            $order = $user->orders()->create([
                'total_amount' => round($totalAmount, 2),
                'status'       => OrderStatus::Pending,
            ]);

            $order->items()->createMany($orderItems);

            return $order->load('items');
        });
    }

    public function updateStatus(Order $order, OrderStatus $newStatus): Order
    {
        if (!$order->canTransitionToStatus($newStatus)) {
            throw new InvalidOrderStatusTransitionException($order->status, $newStatus);
        }

        $order->status = $newStatus;
        $order->save();

        return $order->fresh();
    }

    public function deleteOrder(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            $order->items()->delete();
            return $order->delete();
        });
    }
}
