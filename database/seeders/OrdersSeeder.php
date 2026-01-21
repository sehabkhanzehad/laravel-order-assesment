<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = User::where('email', 'customer@example.com')->first();

        if (!$customer) return;
        if (Order::ownedBy($customer)->exists()) return;

        $service = app(OrderService::class);
        $service->createOrder($customer, $this->getItems());
    }

    private function getItems(): array
    {
        return [
            ['product_name' => 'Laptop', 'unit_price' => 50000, 'quantity' => 1],
            ['product_name' => 'Headphones', 'unit_price' => 3000, 'quantity' => 1],
        ];
    }
}
