<?php

namespace src;
class Order
{
    private static int $counter = 0;
    private string $order_id;
    public array $items = [];
    public bool $done = false;

    public function __construct()
    {
        self::$counter++;
        $this->order_id = substr(md5(uniqid()), 0, 15);
    }

    public static function getCounter(): int
    {
        return self::$counter;
    }

    // public function setCounter(int $counter): void
    // {
    //     $this->counter = $counter;
    // }

    public function getOrderId(): string
    {
        return $this->order_id;
    }

    public function setOrderId(string $order_id): void
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