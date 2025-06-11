<?php

namespace src;
class Order
{
    private static int $counter = 0;
    private int $order_id;
    public array $items = [];
    public bool $done = false;
    public function __construct()
    {
        self::$counter++;
        $this->setOrderId(self::$counter);
    }

    public function getOrderId(): int
    {
        return $this->order_id;
    }

    public function setOrderId(int $order_id): void
    {
        $this->order_id = $order_id;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function isDone(): bool
    {
        return $this->done;
    }

    public function setDone(bool $done): void
    {
        $this->done = $done;
    }
}