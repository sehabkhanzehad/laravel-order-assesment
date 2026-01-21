# PART C - Debugging & Refactoring

### C1) Issues Identified

#### Given Code

```php
class OrderController extends Controller {
  public function update(Request $request, $id) {
    $order = Order::find($id);
    if ($request->status) {
      $order->status = $request->status;
    }
    $order->save();
    return response()->json($order);
  }
}
```

---

#### 1. Authorization missing (Security)

There is no policy or authorization check, if no route middleware. Any user can update any order.

#### 2. Validation missing (Validation)

The `status` value is not validated. Invalid status can be saved.

#### 3. Business rules ignored (Business Logic)

Allowed status transitions are not enforced. Customers should not update status.

#### 4. Null handling missing

If order is not found, it will cause an error.

#### 5. No separation of concerns (Architecture)

Business logic inside controller, Service, policy FormRequest not used

#### 6. No Enum (Consistency)

For status, string used directly instead of Enum.

---

### C2) Refactoring Approach

* Used **Policy** for authorization.
* Used **FormRequest** for validation.
* Used **route model binding** to avoid null issues.
* Moved business logic to a **Service class**.
* Used **Enum** for order status.
* Added domain exception for invalid transitions.

**Note: The refactored code is already implemented in this project.**

### Refactored Code

#### Policy

```php
public function updateStatus(User $user, Order $order): bool
{
    return $user->isAdmin() || $user->isManager();
}
```

#### Form Request

```php
class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(OrderStatus::values())],
        ];
    }
}
```

#### Route

```php
// Inside Auth Middleware Group
Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
```

---

#### Service

```php
class OrderService
{
    public function updateStatus(Order $order, OrderStatus $newStatus): Order
    {
        if (!$order->canTransitionToStatus($newStatus)) {
            throw new InvalidOrderStatusTransitionException($order->status, $newStatus);
        }

        $order->status = $newStatus;
        $order->save();

        return $order->fresh();
    }
}
```

---

#### Controller

```php
class OrderController extends Controller
{
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
```
