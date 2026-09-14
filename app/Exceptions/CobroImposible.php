<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * El pedido no se puede cobrar: venció, ya se cobró, o se quedó sin stock.
 *
 * Lleva el mensaje ya redactado para la persona porque los tres motivos se
 * cuentan igual en el mostrador, en la API y en el webhook; lo único que cambia
 * es el envoltorio (JSON o flash).
 */
class CobroImposible extends RuntimeException
{
    public static function yaPagado(): self
    {
        return new self('Ese pedido ya fue cobrado.');
    }

    public static function vencido(): self
    {
        // Dice qué hacer y no solo qué pasó: el cliente está parado en el
        // mostrador y "venció" a secas no le resuelve nada.
        return new self('La reserva venció. Volvé a armar el pedido.');
    }

    public static function inactivo(): self
    {
        return new self('Ese pedido ya no está pendiente.');
    }

    public static function sinStock(string $cerveza): self
    {
        return new self("Ya no hay stock de {$cerveza}.");
    }
}
