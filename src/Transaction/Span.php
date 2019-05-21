<?php


namespace LogEngine\Transaction;


use JsonSerializable;
use LogEngine\Transaction\Context\SpanContext;

class Span implements JsonSerializable
{
    /**
     * The Transaction that own the span.
     *
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var float
     */
    protected $start;

    /**
     * Number of milliseconds until Span ends.
     *
     * @var float
     */
    protected $duration;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var SpanContext
     */
    protected $context;

    /**
     * Span constructor.
     *
     * @param string $type
     * @param Transaction $transaction
     * @param bool $autoplay
     */
    public function __construct($type, Transaction $transaction, $autoplay = false)
    {
        $this->type = $type;
        $this->transaction = $transaction;
        $this->context = new SpanContext();

        if($autoplay){
            $this->start();
        }
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function start(): Span
    {
        $this->start = microtime(true);
        return $this;
    }

    public function end(): Span
    {
        $this->duration = round((microtime(true) - $this->start)*1000, 2); // milliseconds
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'transaction_hash' => $this->transaction->hash,
            'start' => $this->start,
            'duration' => $this->duration,
        ];
    }

    /**
     * String representation.
     *
     * @return false|string
     */
    public function __toString()
    {
        return json_encode($this->jsonSerialize());
    }

    public function toString()
    {
        return $this->__toString();
    }
}