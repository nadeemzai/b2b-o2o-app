<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Validation\ValidationException;

class OrderStatusTransitionService
{
    /**
     * Allowed transitions for store staff.
     * key   = current status
     * value = allowed next statuses
     */
    private const TRANSITIONS = [
        Order::STATUS_PENDING            => [Order::STATUS_PREPARING, Order::STATUS_CANCELLED],
        Order::STATUS_PREPARING          => [Order::STATUS_READY_FOR_DELIVERY, Order::STATUS_CANCELLED],
        Order::STATUS_READY_FOR_DELIVERY => [Order::STATUS_DELIVERED],
        Order::STATUS_DELIVERED          => [],
        Order::STATUS_CANCELLED          => [],
    ];

    /**
     * Validate and apply a status transition (store-staff triggered).
     * The /deliver endpoint uses OrderService::deliverOrder() instead.
     *
     * @throws ValidationException
     */
    public function transition(Order $order, string $toStatus, int $changedByUserId, ?string $note = null): Order
    {
        $from    = $order->status;
        $allowed = self::TRANSITIONS[$from] ?? [];

        if (! in_array($toStatus, $allowed)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot transition from '{$from}' to '{$toStatus}'."],
            ]);
        }

        $order->update(['status' => $toStatus]);

        OrderStatusHistory::create([
            'order_id'           => $order->id,
            'from_status'        => $from,
            'to_status'          => $toStatus,
            'changed_by_user_id' => $changedByUserId,
            'note'               => $note,
        ]);

        return $order->fresh();
    }

    /**
     * Return the list of statuses the given order can transition to.
     */
    public function allowedNext(Order $order): array
    {
        return self::TRANSITIONS[$order->status] ?? [];
    }
}
