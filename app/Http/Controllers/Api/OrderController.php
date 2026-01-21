<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Orders\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Requests\Api\Orders\UpdateOrderStatusRequest;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Http\Resources\Api\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $orders = Order::visibleTo($user)->with('items')->latest()->paginate(10);

        return OrderResource::collection($orders);
    }

    public function show(Order $order): OrderResource
    {
        return OrderResource::make($order->load(['items', 'user']));
    }

    public function store(StoreOrderRequest $request, OrderService $service): JsonResponse
    {
        $order = $service->createOrder($request->user(), $request->validated('items'));

        return $this->success(
            'Order created successfully.',
            201,
            ['order' => OrderResource::make($order)]
        );
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, OrderService $service): JsonResponse
    {
        try {
            $order = $service->updateStatus($order, OrderStatus::from($request->input('status')));

            return $this->success(
                'Order status updated successfully.',
                200,
                ['order' => OrderResource::make($order)]
            );
        } catch (InvalidOrderStatusTransitionException $e) {
            logger()->error('Order status update failed: ' . $e->getMessage());
            return $this->error($e->getMessage(), 422);
        }
    }
}
