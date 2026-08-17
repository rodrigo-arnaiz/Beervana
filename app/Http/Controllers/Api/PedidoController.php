<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CobroImposible;
use App\Http\Controllers\Controller;
use App\Models\Cerveza;
use App\Models\Factura;
use App\Models\Pedido;
use App\Services\RegistrarCobro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    /* Pedidos del usuario, con el vencido ya reflejado en el estado. */
    public function index(Request $request)
    {
        $pedidos = Pedido::with('items.cerveza', 'factura')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn (Pedido $pedido) => $this->conEstadoReal($pedido));

        return response()->json($pedidos);
    }

    public function show(Request $request, $codigo)
    {
        return response()->json($this->conEstadoReal($this->pedidoDelUsuario($request, $codigo)));
    }

    /*
     * Busca el pedido del usuario por su código.
     *
     * Se usa el codigo y no el id porque el id es secuencial: con /pedidos/1,
     * /pedidos/2... se puede recorrer la tabla probando. El filtro por user_id
     * ya frena la fuga de datos, pero un identificador adivinable invita a
     * intentarlo y ensucia los logs.
     */
    private function pedidoDelUsuario(Request $request, string $codigo): Pedido
    {
        return Pedido::with('items.cerveza', 'factura')
            ->where('user_id', $request->user()->id)
            ->where('codigo', strtoupper(trim($codigo)))
            ->firstOrFail();
    }

    /**
     * Crea el pedido y reserva el stock.
     *
     * La reserva no toca cervezas.stock: se comprueba que la cantidad pedida
     * entre en lo disponible (stock menos lo reservado por otros pedidos
     * vigentes) y el pedido pasa a contar como reserva hasta que venza.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.cantidad' => 'required|integer|min:1',
            'metodo_entrega' => 'sometimes|in:'.implode(',', Pedido::METODOS_ENTREGA),
        ]);

        // El cliente elige el metodo, nunca el monto
        $metodoEntrega = $datos['metodo_entrega'] ?? Pedido::ENTREGA_ENVIO;
        $envio = Pedido::costoEnvioPara($metodoEntrega);

        try {
            $pedido = DB::transaction(function () use ($datos, $request, $metodoEntrega, $envio) {
                $pedido = Pedido::create([
                    'codigo' => Pedido::generarCodigo(),
                    'user_id' => $request->user()->id,
                    'estado' => Pedido::PENDIENTE,
                    'metodo_entrega' => $metodoEntrega,
                    'envio' => $envio,
                    'precio_total' => 0,
                    'expira_en' => now()->addHours(Pedido::HORAS_VENCIMIENTO),
                ]);

                $total = 0;

                foreach ($datos['items'] as $item) {
                    // lockForUpdate serializa a dos usuarios que piden la misma
                    // cerveza a la vez: sin esto los dos leen el mismo disponible
                    // y los dos reservan.
                    $cerveza = Cerveza::whereKey($item['id'])->lockForUpdate()->first();

                    if (! $cerveza) {
                        abort(422, "La cerveza con id {$item['id']} no existe");
                    }

                    $disponible = $cerveza->disponible();

                    if ($item['cantidad'] > $disponible) {
                        abort(409, "No hay stock suficiente de {$cerveza->nombre}. Disponible: {$disponible}");
                    }

                    $subtotal = $cerveza->precio * $item['cantidad'];

                    $pedido->items()->create([
                        'cerveza_id' => $cerveza->id,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $cerveza->precio,
                        'subtotal' => $subtotal,
                    ]);

                    $total += $subtotal;
                }

                $pedido->update(['precio_total' => $total + $envio]);

                return $pedido;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // El unique (pedido_id, cerveza_id) salta si mandan la misma cerveza
            // dos veces en el mismo pedido
            return response()->json([
                'error' => 'El pedido tiene la misma cerveza repetida en más de un renglón',
            ], 422);
        }

        return response()->json([
            'message' => 'Pedido creado. Tenés '.Pedido::HORAS_VENCIMIENTO.' horas para pagarlo.',
            'pedido' => $pedido->load('items.cerveza'),
        ], 201);
    }

    /** Libera la reserva antes de tiempo. */
    public function cancelar(Request $request, $codigo)
    {
        $pedido = $this->pedidoDelUsuario($request, $codigo);

        if ($pedido->estado === Pedido::PAGADO) {
            return response()->json(['error' => 'Un pedido pagado no se puede cancelar'], 409);
        }

        if (! in_array($pedido->estado, Pedido::ESTADOS_ACTIVOS, true)) {
            return response()->json(['error' => 'El pedido ya no está pendiente'], 409);
        }

        $pedido->update(['estado' => Pedido::CANCELADO, 'expira_en' => null]);

        return response()->json([
            'message' => 'Pedido cancelado',
            'pedido' => $pedido->fresh(),
        ]);
    }

    /*
     * El cliente avisa que va a pagarlo en el local.
     *
     * No cobra ni emite factura: solo deja constancia de que alguien va a
     * venir. Para el mostrador es la diferencia entre "hay que esperarlo" y
     * "el cliente todavía no hizo nada con este pedido".
     */
    public function confirmar(Request $request, $codigo)
    {
        $pedido = $this->pedidoDelUsuario($request, $codigo);

        if ($pedido->estado === Pedido::CONFIRMADO) {
            return response()->json(['pedido' => $pedido]); // idempotente
        }

        if ($pedido->estaVencido()) {
            $pedido->update(['estado' => Pedido::VENCIDO, 'expira_en' => null]);

            return response()->json(['error' => 'La reserva venció. Volvé a armar el pedido.'], 409);
        }

        if ($pedido->estado !== Pedido::PENDIENTE) {
            return response()->json(['error' => 'El pedido ya no está pendiente'], 409);
        }

        $pedido->update(['estado' => Pedido::CONFIRMADO]);

        return response()->json([
            'message' => 'Te esperamos en el local con tu comprobante',
            'pedido' => $pedido->fresh()->load('items.cerveza'),
        ]);
    }

    /*
     * Busca un pedido por su codigo de mostrador. Solo para quien cobra.
     *
     * Devuelve todo lo que el cajero necesita para verificar antes de cobrar:
     * a nombre de quién está, qué lleva y cuánto suma.
     */
    public function buscarPorCodigo(Request $request)
    {
        $this->soloPersonal($request);

        $datos = $request->validate(['codigo' => 'required|string']);

        $pedido = Pedido::with('items.cerveza', 'user', 'factura')
            ->where('codigo', strtoupper(trim($datos['codigo'])))
            ->first();

        if (! $pedido) {
            return response()->json(['error' => 'No hay ningún pedido con ese código'], 404);
        }

        return response()->json($this->conEstadoReal($pedido));
    }

    /*
     * Registra el cobro: baja el stock físico y emite la factura.
     *
     * Accion del cajero (empleado) que cobra el pedido en el local. Si el cliente lo decide paga en efectivo o con tarjeta en el local.
     * 
     */
    public function pagar(Request $request, RegistrarCobro $cobro, $id)
    {
        $this->soloPersonal($request);

        $pedido = Pedido::findOrFail($id);

        if ($pedido->estado === Pedido::PAGADO) {
            return response()->json(['error' => 'El pedido ya fue pagado'], 409);
        }

        try {
            // Misma lógica que el mostrador del panel y que el webhook de
            // Mercado Pago: descontar stock y emitir factura pasa por un solo
            // lugar, así no hay tres versiones que se desincronicen.
            $factura = $cobro->registrar($pedido, $request->user()->id, RegistrarCobro::EFECTIVO);
        } catch (CobroImposible $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }

        return response()->json([
            'message' => 'Pago realizado con éxito',
            'factura' => $factura->load('pedido.items.cerveza'),
        ], 201);
    }


    private function soloPersonal(Request $request): void
    {
        if (! $request->user()?->esPersonal()) {
            abort(403, 'Solo el personal puede registrar cobros');
        }
    }

    private function conEstadoReal(Pedido $pedido): Pedido
    {
        if ($pedido->estaVencido()) {
            $pedido->estado = Pedido::VENCIDO;
        }

        return $pedido;
    }
}
