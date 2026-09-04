# Mercado Pago — puesta en marcha

Integración con **Checkout Pro** en modo prueba. Con credenciales `TEST-` no se
mueve plata real: se paga con tarjetas de prueba y las cuentas son ficticias.

Hay dos modos, y se cambian con una línea del `.env`:

| `MERCADOPAGO_MODO` | Qué hace | Cuándo |
|---|---|---|
| `simulado` | No llama a nadie. El checkout es una pantalla propia | Sin credenciales, sin internet, o si el panel de MP está caído |
| `real` | Habla con la API de Mercado Pago | Cuando tengas el Access Token |

**El flujo es el mismo en los dos.** Lo único que cambia es quién responde: la
API de MP o una simulación. Los controladores, el webhook y las pantallas no se
enteran, así que probar en simulado sirve para confiar en el real.

---

## Modo simulado (empezá por acá)

```env
MERCADOPAGO_MODO=simulado
```

```bash
docker compose exec backend php artisan config:clear
```

Listo, no hace falta nada más. Ni token, ni cuenta, ni internet.

En vez del checkout de Mercado Pago, el link (y el QR) llevan a una pantalla
propia con dos botones: **Pagar (aprobado)** y **Simular un rechazo**. Es el
equivalente a elegir el titular `APRO` u `OTHE` en las tarjetas de prueba.

Todo lo demás pasa de verdad: se descuenta el stock, se emite la factura, la
pantalla del mostrador se actualiza sola, y el aviso se procesa por el **mismo
webhook** que usa el modo real. No hay atajos: si los hubiera, probar en
simulado no diría nada sobre si el modo real funciona.

La pantalla está marcada como simulación con una franja gris a rayas, y las dos
rutas responden **404 cuando el modo es `real`**. En producción no existen.

---

## 1. Crear la aplicación en Mercado Pago

1. Entrá a <https://www.mercadopago.com.ar/developers/panel> con tu cuenta
   personal de Mercado Pago (sirve cualquiera, no hace falta una de negocio).
2. **Tus integraciones → Crear aplicación**.
3. Nombre: `Beervana`. Modelo de integración: **Pagos online → Checkout Pro**.
4. Guardá.

### El formulario te pide una URL. ¿Qué pongo si estoy en local?

Cualquier cosa con formato válido. **Esos campos no participan del pago.**

| Campo del panel | Para qué es | Qué poner |
|---|---|---|
| URL del sitio | Informativo, sale en el perfil público | `https://beervana.vercel.app` o el que tengas |
| **Redirect URI** | **Solo OAuth (marketplace)**. Checkout Pro no lo usa | Dejalo vacío |

El Redirect URI existe para el modelo *marketplace*, donde otros vendedores
autorizan a tu app a cobrar por ellos. Vos cobrás con tu propia cuenta, así que
ese flujo no entra en juego.

**Adónde vuelve el cliente después de pagar no se configura acá**: viaja en cada
preferencia, y sale de `MERCADOPAGO_URL_RETORNO` en el `.env`.

No necesitás nada publicado para obtener las credenciales de prueba.

## 2. Copiar las credenciales de prueba

Dentro de la aplicación, **Credenciales de prueba** (no las de producción).

Vas a ver dos valores:

| Qué | Cómo empieza | Para qué |
|---|---|---|
| Public Key | `TEST-xxxxxxxx-...` | El frontend (hoy no se usa, es para Bricks) |
| Access Token | `TEST-1234567890...` | El backend. **Es el que importa.** |

> El Access Token es como una contraseña de la cuenta: va solo en el `.env`, que
> está en `.gitignore`. No lo pegues en el código ni lo subas al repo.

## 3. Ponerlas en el `.env`

En `Beervana/.env` ya están las líneas, vacías:

```env
MERCADOPAGO_ACCESS_TOKEN=TEST-1234567890-abcdef-...
MERCADOPAGO_PUBLIC_KEY=TEST-abcd1234-...
MERCADOPAGO_MONEDA=ARS
MERCADOPAGO_URL_RETORNO=http://localhost:3000
MERCADOPAGO_URL_WEBHOOK=
MERCADOPAGO_AUTO_RETURN=true
```

Después:

```bash
docker compose exec backend php artisan config:clear
```

Sin esto Laravel sigue leyendo la config vieja y parece que no anduviera nada.

**Cómo saber si quedó bien:** entrá a un pedido pendiente y apretá "Pagar con
Mercado Pago". Si el token falta, la API devuelve 503 y un cartel que lo dice.

## 4. Crear los usuarios de prueba

Acá hay algo que confunde a todo el mundo la primera vez: **no podés pagarte a
vos mismo**. MP rechaza el pago si el comprador es el dueño de la aplicación.
Hacen falta dos cuentas ficticias.

Con tu Access Token de prueba, corré esto dos veces:

```bash
curl -X POST https://api.mercadopago.com/users/test_user \
  -H "Authorization: Bearer TEST-tu-access-token-aca" \
  -H "Content-Type: application/json" \
  -d '{"site_id":"MLA"}'
```

Cada llamada devuelve algo así:

```json
{ "id": 123456789, "email": "test_user_12345678@testuser.com", "password": "qatest1234" }
```

**Guardá las dos.** Una es el vendedor y la otra el comprador:

- **Vendedor**: entrá al panel de developers con esa cuenta y creá ahí la
  aplicación. Usá *su* Access Token en el `.env`. (Si preferís, podés saltear
  esto y usar el token de tu cuenta real en modo prueba: funciona igual.)
- **Comprador**: con esta te logueás en el checkout de MP cuando pruebes.

## 5. Tarjetas de prueba

En el checkout, cualquiera de estas. Vencimiento `11/30`, CVV `123`.

| Tarjeta | Número |
|---|---|
| Mastercard | `5031 7557 3453 0604` |
| Visa | `4509 9535 6623 3704` |
| American Express | `3711 803032 57522` |

**El resultado lo decide el nombre del titular**, no el número:

| Titular | Resultado | Sirve para probar |
|---|---|---|
| `APRO` | Aprobado | El camino feliz |
| `OTHE` | Rechazado (error general) | Que no se descuente stock |
| `CONT` | Pendiente | El estado "en revisión" |
| `FUND` | Rechazado por fondos | El mensaje de rechazo |

DNI: `12345678`.

---

## Cómo probar los dos flujos

### El cliente paga desde la tienda

1. Armá un carrito en <http://localhost:3000> y confirmá el pedido.
2. En el detalle, **Pagar con Mercado Pago**.
3. Te lleva al checkout de MP. Logueate con el **comprador de prueba** y pagá
   con `APRO`.
4. Volvés al pedido. La pantalla consulta el estado y, cuando MP confirma, te
   manda a *Compras* con la factura emitida.

Verificá que el stock bajó y que la factura dice `mercadopago`.

### El cajero cobra en el mostrador

1. Entrá al panel como empleado o admin → **Mostrador**.
2. Buscá el pedido por su código (`BV-XXXXXX`).
3. **Cobrar con Mercado Pago**.
4. Aparece un QR y un link. Escaneá el QR con el celular, o abrí el link en
   otra pestaña, y pagá con el comprador de prueba.
5. La pantalla se actualiza sola: cuando MP acredita, avisa y vuelve al
   mostrador. La factura queda **a tu nombre** como cajero, así aparece en
   *Mis cobros*.

---

## Por qué funciona en local sin exponer nada

MP tiene dos restricciones que suelen frenar a todo el mundo:

1. **Rechaza `localhost` en las `back_urls`.** No ignora el campo: falla la
   preferencia entera, con un error que no dice cuál es el campo que le molesta.
2. **No puede alcanzar `localhost` para el webhook**, porque desde internet esa
   dirección no existe.

El código las esquiva a las dos: detecta si tus URLs son públicas y, si no lo
son, **directamente no se las manda**. Sin `back_urls` el checkout funciona igual
—el cliente termina en la pantalla de MP y vuelve por su cuenta— y sin webhook el
sistema le pregunta el estado a MP (el mostrador cada 3 segundos, el frontend al
volver del checkout).

Qué cambia según el caso:

| | Local, sin exponer nada | Con ngrok o dominio real |
|---|---|---|
| **Mostrador** (el cajero cobra) | Igual de bien | Igual de bien |
| **Tienda** (el cliente paga) | Vuelve a la tienda a mano | Vuelve solo |
| Confirmación | Hasta 3 segundos | Instantánea |

O sea: **para la demo del mostrador no necesitás nada más**, porque ahí el
cliente paga con el celular y la pantalla de la caja se actualiza sola.

### Si querés la vuelta automática y el webhook

```bash
ngrok http 3000    # el frontend, para las back_urls
```

En el `.env`:

```env
MERCADOPAGO_URL_RETORNO=https://abc123.ngrok-free.app
```

Y si además querés el webhook, un segundo túnel al backend:

```bash
ngrok http 8000
```

```env
MERCADOPAGO_URL_WEBHOOK=https://xyz789.ngrok-free.app/my_api/webhooks/mercadopago
```

`config:clear` y listo: el sistema detecta solo que las URLs ya no son locales y
empieza a mandarlas. No hay que tocar código.

---

## Si algo falla

| Síntoma | Causa | Qué hacer |
|---|---|---|
| 503 "no está configurado" | Falta el Access Token | Cargalo y corré `config:clear` |
| 502 "no se pudo conectar" | Token inválido o vencido | Copialo de nuevo del panel |
| MP dice `invalid back_urls` o `invalid auto_return` | Le llegó una URL local | Revisá que `MERCADOPAGO_URL_RETORNO` no sea localhost, o dejalo así: el sistema no se las manda |
| Pagué pero no volví a la tienda | En local no hay back_urls (es lo esperado) | Volvé a la pestaña de la tienda; el pago ya está acreditado |
| El pago sale rechazado siempre | Estás pagando con tu propia cuenta | Usá el comprador de prueba |
| Pagué y el pedido sigue pendiente | El webhook no llegó (normal en local) | Esperá: la pantalla consulta sola cada 3s |

Los errores quedan en `storage/logs/laravel.log` con el prefijo `MP:`.

---

## Cómo está armado (para explicarlo)

```
                       ┌─────────────────┐
   tienda ────────────>│                 │
   (el cliente paga)   │ CobroMercadoPago│──> MercadoPago (API de MP)
   mostrador ─────────>│                 │
   (el cajero cobra)   │                 │──> RegistrarCobro
                       └─────────────────┘         │
                              ▲                    ├─> descuenta stock
                              │                    └─> emite la factura
                       webhook de MP
```

Tres decisiones que vale la pena poder defender:

**El cobro está en un solo lugar.** `RegistrarCobro` descuenta stock y emite la
factura. Antes esa lógica estaba copiada en dos controladores; con Mercado Pago
habrían sido tres, y la copia que alguien se olvidara de actualizar fallaría en
silencio.

**Al webhook no se le cree el estado.** La ruta es pública porque la llama MP,
no una persona. Del aviso se toma **solo el id** y el estado se le pregunta a MP.
Si se confiara en el `status` del cuerpo, cualquiera que descubra la URL manda un
`approved` y se lleva la mercadería sin pagar. Hay un test que prueba exactamente
ese ataque.

**Cobrar dos veces significa cosas distintas según quién pregunte.** MP reintenta
el mismo aviso varias veces, así que ahí el segundo tiene que devolver la factura
que ya existe sin descontar stock de nuevo. Pero un cajero que hace doble click
necesita que le digan "ya se cobró": un éxito silencioso se lee como "listo,
cobré", y nadie cobró nada. Por eso la idempotencia es un parámetro y no el
comportamiento por defecto.

Los tests están en `tests/Feature/MercadoPagoTest.php` (20 casos). Simulan la API
de MP con `Http::fake`, así que corren sin internet y sin cuenta.
