<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Cliente de la API de Mercado Pago (Checkout Pro).
 *
 * Se habla HTTP directo en vez de usar el SDK oficial a propósito: son tres
 * llamadas, el código queda igual a los ejemplos de la documentación de MP, y
 * no hay una capa más que aprender ni una dependencia que se desactualice.
 *
 * Con Checkout Pro los datos de la tarjeta nunca pasan por este servidor: se
 * crea una "preferencia" (qué se vende y a cuánto), MP devuelve una URL, y el
 * cliente paga en el sitio de ellos.
 *
 * @see https://www.mercadopago.com.ar/developers/es/docs/checkout-pro/landing
 */
class MercadoPago
{
    private const BASE = 'https://api.mercadopago.com';

    public function __construct(
        private ?string $accessToken = null,
        private ?string $urlRetorno = null,
        private ?string $urlWebhook = null,
    ) {
        $this->accessToken ??= config('services.mercadopago.access_token');
        $this->urlRetorno ??= config('services.mercadopago.url_retorno');
        $this->urlWebhook ??= config('services.mercadopago.url_webhook');
    }

    /** Si falta el token no tiene sentido intentar: se avisa antes y claro. */
    public function estaConfigurado(): bool
    {
        return filled($this->accessToken);
    }

    /**
     * Crea la preferencia de pago de un pedido y devuelve la respuesta de MP.
     *
     * external_reference lleva el código del pedido: es lo que después permite
     * saber a qué venta corresponde un pago cuando MP avisa, sin depender de
     * que el aviso llegue con nuestro id interno.
     */
    public function crearPreferencia(Pedido $pedido): array
    {
        $items = $pedido->items->map(fn ($item) => [
            'id' => (string) $item->cerveza_id,
            'title' => $item->cerveza->nombre ?? 'Producto',
            'quantity' => (int) $item->cantidad,
            'unit_price' => (float) $item->precio_unitario,
            'currency_id' => config('services.mercadopago.moneda'),
        ])->values()->all();

        // El envío va como un ítem más y no en shipments.cost: así el total que
        // ve el cliente en MP coincide exactamente con el de la factura, sin
        // depender de cómo MP decida mostrar el costo de envío.
        //
        // Y no es opcional: MP arma el total sumando los ítems. Si el envío no
        // viaja como ítem, el cliente paga la mercadería sola y el envío queda
        // sin cobrar. La columna se llama `envio`, no `costo_envio`.
        if ($pedido->envio > 0) {
            $items[] = [
                'id' => 'envio',
                'title' => 'Envío a domicilio',
                'quantity' => 1,
                'unit_price' => (float) $pedido->envio,
                'currency_id' => config('services.mercadopago.moneda'),
            ];
        }

        $cuerpo = [
            'items' => $items,
            'external_reference' => $pedido->codigo,
            'statement_descriptor' => 'BEERVANA',
        ];

        // Las back_urls solo se mandan si apuntan a un dominio público.
        //
        // MP las rechaza explícitamente cuando son localhost o 127.0.0.1, y no
        // ignora el campo: falla la preferencia entera. O sea que mandarlas "por
        // las dudas" en desarrollo rompe el pago en vez de mejorarlo.
        //
        // Sin ellas el checkout funciona igual: el cliente termina en la
        // pantalla de MP y vuelve a la tienda por su cuenta. El pago se acredita
        // lo mismo, porque el estado se consulta contra MP y no depende de que
        // el navegador pase por ninguna URL nuestra.
        if ($this->esUrlPublica($this->urlRetorno)) {
            $cuerpo['back_urls'] = [
                'success' => $this->urlRetorno."/pedidos/{$pedido->codigo}?pago=exito",
                'pending' => $this->urlRetorno."/pedidos/{$pedido->codigo}?pago=pendiente",
                'failure' => $this->urlRetorno."/pedidos/{$pedido->codigo}?pago=fallo",
            ];

            // auto_return necesita back_urls: sin ellas MP rechaza la preferencia
            if (config('services.mercadopago.auto_return')) {
                $cuerpo['auto_return'] = 'approved';
            }
        }

        // Mismo criterio para el webhook: MP no puede alcanzar localhost.
        if ($this->esUrlPublica($this->urlWebhook)) {
            $cuerpo['notification_url'] = $this->urlWebhook;
        }

        return $this->pedir()->post(self::BASE.'/checkout/preferences', $cuerpo)
            ->throw()
            ->json();
    }

    /** Las credenciales de prueba arrancan con TEST-. */
    public function esDePrueba(): bool
    {
        return str_starts_with((string) $this->accessToken, 'TEST-');
    }

    /**
     * Cuál de las dos URLs que devuelve MP hay que usar.
     *
     * MP devuelve init_point (www.mercadopago.com) y sandbox_init_point
     * (sandbox.mercadopago.com), y cuál corresponde depende de CÓMO se prueba,
     * no de si el token es de prueba:
     *
     *   Pagando como invitado con tarjeta de prueba  → init_point
     *     El vendedor es tu cuenta, en modo prueba por el token TEST-. No hay
     *     comprador logueado, así que no hay nada que pueda no coincidir.
     *
     *   Pagando con un usuario de prueba             → sandbox_init_point
     *     Las cuentas de prueba viven solo en el sandbox. Pero además el
     *     VENDEDOR tiene que ser otro usuario de prueba: MP exige que las dos
     *     partes sean del mismo tipo, y si una es real y la otra de prueba
     *     rechaza el pago con "una de las partes es de prueba".
     *
     * Por defecto va el primer camino, que es el que no necesita crear cuentas.
     */
    public function puntoDeCheckout(array $preferencia): ?string
    {
        if (config('services.mercadopago.usar_sandbox')) {
            return $preferencia['sandbox_init_point']
                ?? $preferencia['init_point']
                ?? null;
        }

        return $preferencia['init_point']
            ?? $preferencia['sandbox_init_point']
            ?? null;
    }

    /** Si está configurada la caja para cobrar con QR presencial. */
    public function tieneCaja(): bool
    {
        return filled(config('services.mercadopago.user_id'))
            && filled(config('services.mercadopago.caja'));
    }

    /**
     * Genera el QR con el monto de un pedido.
     *
     * Es el QR que se escanea con la app de Mercado Pago, el mismo que usan los
     * locales. Devuelve qr_data: la trama EMVCo que hay que dibujar como código.
     * No es una URL y no sirve para abrir en el navegador.
     *
     * Ojo con una restricción de MP: una caja tiene UNA orden activa por vez.
     * Generar un QR nuevo reemplaza al anterior de esa caja, así que con una
     * sola caja no se pueden tener dos cobros esperando en paralelo.
     *
     * @see https://www.mercadopago.com.ar/developers/es/docs/qr-code/overview
     */
    public function crearQrDinamico(Pedido $pedido): array
    {
        $uid = config('services.mercadopago.user_id');
        $caja = config('services.mercadopago.caja');

        $items = $pedido->items->map(fn ($item) => [
            'title' => $item->cerveza->nombre ?? 'Producto',
            'quantity' => (int) $item->cantidad,
            'unit_price' => (float) $item->precio_unitario,
            'unit_measure' => 'unit',
            'total_amount' => (float) $item->subtotal,
        ])->values()->all();

        // MP valida que total_amount sea EXACTAMENTE la suma de los ítems, y
        // si no cierra rechaza el QR entero con un 400. Por eso el envío tiene
        // que ir como ítem: está adentro de precio_total.
        if ($pedido->envio > 0) {
            $items[] = [
                'title' => 'Envío a domicilio',
                'quantity' => 1,
                'unit_price' => (float) $pedido->envio,
                'unit_measure' => 'unit',
                'total_amount' => (float) $pedido->envio,
            ];
        }

        $cuerpo = [
            // La misma etiqueta que la preferencia: da igual por qué puerta
            // entre el pago, vuelve identificado con el código del pedido.
            'external_reference' => $pedido->codigo,
            'title' => 'Beervana · pedido '.$pedido->codigo,
            'description' => 'Compra en Beervana',
            'total_amount' => (float) $pedido->precio_total,
            'items' => $items,
        ];

        if ($this->esUrlPublica($this->urlWebhook)) {
            $cuerpo['notification_url'] = $this->urlWebhook;
        }

        return $this->pedir()
            ->post(self::BASE."/instore/orders/qr/seller/collectors/{$uid}/pos/{$caja}/qrs", $cuerpo)
            ->throw()
            ->json();
    }

    /** Libera la caja: sin esto el QR viejo sigue cobrable. */
    public function borrarQrDinamico(): void
    {
        $uid = config('services.mercadopago.user_id');
        $caja = config('services.mercadopago.caja');

        $this->pedir()->delete(
            self::BASE."/instore/qr/seller/collectors/{$uid}/pos/{$caja}/orders"
        );
    }

    /** Crea la sucursal y la caja. Lo usa el comando mp:caja, una sola vez. */
    public function crearSucursalYCaja(string $sucursal, string $caja, array $ubicacion): array
    {
        $uid = config('services.mercadopago.user_id') ?: $this->quienSoy()['id'];

        $s = $this->pedir()->post(self::BASE."/users/{$uid}/stores", [
            'name' => 'Beervana',
            'external_id' => $sucursal,
            'location' => $ubicacion,
        ])->throw()->json();

        $c = $this->pedir()->post(self::BASE.'/pos', [
            'name' => 'Caja Mostrador',
            'fixed_amount' => true,
            'store_id' => (string) $s['id'],
            'external_store_id' => $sucursal,
            'external_id' => $caja,
            'category' => 621102, // rubro: bebidas
        ])->throw()->json();

        return ['user_id' => $uid, 'sucursal' => $s, 'caja' => $c];
    }

    /** Datos de la cuenta detrás del token. */
    public function quienSoy(): array
    {
        return $this->pedir()->get(self::BASE.'/users/me')->throw()->json();
    }

    /** Trae un pago puntual por su id. */
    public function buscarPago(string $paymentId): ?array
    {
        $respuesta = $this->pedir()->get(self::BASE."/v1/payments/{$paymentId}");

        if ($respuesta->status() === 404) {
            return null;
        }

        return $respuesta->throw()->json();
    }

    /**
     * Busca el último pago asociado a un pedido, por su código.
     *
     * Es el camino que hace funcionar todo sin webhook: en local MP no puede
     * llegar a localhost, así que en vez de esperar el aviso se le pregunta.
     * En producción el webhook es más rápido, pero esto sigue sirviendo de red
     * de contención si un aviso se pierde.
     */
    public function ultimoPagoDePedido(string $codigoPedido): ?array
    {
        $respuesta = $this->pedir()->get(self::BASE.'/v1/payments/search', [
            'external_reference' => $codigoPedido,
            'sort' => 'date_created',
            'criteria' => 'desc',
            'limit' => 10,
        ]);

        if ($respuesta->failed()) {
            Log::warning('MP: no se pudo consultar el estado del pago', [
                'pedido' => $codigoPedido,
                'status' => $respuesta->status(),
            ]);

            return null;
        }

        $resultados = $respuesta->json('results', []);

        if (empty($resultados)) {
            return null;
        }

        // Un aprobado manda por sobre cualquier otro: si el cliente probó tres
        // veces y la tercera salió bien, la venta está pagada aunque el orden
        // por fecha ponga otro intento adelante.
        foreach ($resultados as $pago) {
            if (($pago['status'] ?? null) === 'approved') {
                return $pago;
            }
        }

        return $resultados[0];
    }

    /**
     * Si una URL existe desde afuera de esta máquina.
     *
     * En desarrollo todo corre en localhost, que desde internet no existe. MP no
     * lo ignora: rechaza la preferencia entera con un error de validación que no
     * dice cuál es el campo que le molesta, así que conviene no mandársela.
     *
     * Con ngrok (o cualquier dominio real) esto da true solo y no hay que tocar
     * ninguna otra cosa.
     */
    public function esUrlPublica(?string $url): bool
    {
        if (blank($url)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST) ?: '';

        if ($host === '') {
            return false;
        }

        return ! in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1', 'host.docker.internal'], true)
            && ! str_ends_with($host, '.local')
            && ! str_starts_with($host, '192.168.')
            && ! str_starts_with($host, '10.')
            && ! preg_match('/^172\.(1[6-9]|2\d|3[01])\./', $host);
    }

    /** Si MP puede avisarnos por webhook o hay que consultarle el estado. */
    public function webhookEsAlcanzable(): bool
    {
        return $this->esUrlPublica($this->urlWebhook);
    }

    private function pedir(): PendingRequest
    {
        if (! $this->estaConfigurado()) {
            throw new RuntimeException(
                'Falta configurar MERCADOPAGO_ACCESS_TOKEN en el .env.'
            );
        }

        return Http::withToken($this->accessToken)
            ->acceptJson()
            ->timeout(15)
            ->retry(2, 200);
    }
}
