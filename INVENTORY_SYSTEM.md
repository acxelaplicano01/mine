# Sistema de Gestión de Inventario

## Descripción

Sistema completo de gestión de inventario con seguimiento de movimientos, alertas automáticas de stock bajo y registro de auditoría.

## Características Principales

### 1. Seguimiento de Inventario
- **Control de stock en tiempo real**: Actualización automática del inventario con cada venta
- **Registro de movimientos**: Histórico completo de todas las entradas, salidas y ajustes
- **Auditoría completa**: Cada cambio queda registrado con usuario, fecha y razón

### 2. Tipos de Movimientos

#### Salida (Ventas)
- Se registra automáticamente al crear un pedido
- Reduce el stock disponible
- Vinculado al pedido de origen

#### Entrada (Reabastecimiento)
- Aumenta el stock disponible
- Puede vincularse a órdenes de compra u otros documentos
- Registra la razón de entrada

#### Ajuste Manual
- Corrección de errores de inventario
- Recuentos físicos
- Productos dañados o perdidos
- Requiere especificar razón

#### Devolución
- Se registra automáticamente al cancelar/eliminar un pedido
- Devuelve el stock al inventario
- Vinculado al pedido original

### 3. Alertas de Stock Bajo

El sistema envía notificaciones automáticas cuando:
- El stock actual es menor o igual al umbral configurado
- Se envía por email y se guarda en la base de datos
- Los administradores reciben alertas en tiempo real

#### Configuración de Umbrales
En la tabla `inventories`:
- `umbral_aviso_inventario`: Cantidad mínima antes de alertar
- `seguimiento_inventario`: Activar/desactivar seguimiento
- `permitir_vender_sin_inventario`: Permitir ventas con stock 0

### 4. Vista de Gestión de Inventario

#### Acceso
```
URL: /inventario
Ruta: inventory.management
Middleware: auth
```

#### Funcionalidades

**Filtros:**
- Búsqueda por nombre, SKU o código de barras
- Filtrar por estado: Todos / Stock bajo / Sin stock / Stock normal
- Ordenar por nombre o cantidad en stock

**Acciones:**
- **Ajustar inventario**: Modificar cantidad manualmente
- **Ver historial**: Consultar todos los movimientos del producto
- **Exportar**: Descargar CSV de productos con stock bajo

**Información mostrada:**
- Imagen del producto
- Nombre y código de barras
- SKU
- Stock actual vs Umbral
- Estado visual (badge de color)
- Ubicación física
- Acciones disponibles

### 5. Widget de Alertas

Componente reutilizable para dashboard:

```blade
<livewire:product.low-stock-alerts />
```

Muestra:
- Productos con stock bajo (primeros 5)
- Contador total de alertas
- Enlace directo a gestión de inventario
- Estado visual de cada producto

## Uso del Sistema

### Configurar un Producto

1. Crear o editar producto
2. Asignar un inventario (`id_inventory`)
3. En la tabla `inventories` configurar:
   - `cantidad_inventario`: Stock inicial
   - `umbral_aviso_inventario`: Umbral de alerta (ej: 10)
   - `seguimiento_inventario`: true
   - `permitir_vender_sin_inventario`: false (recomendado)
   - `location`: Ubicación física (opcional)
   - `sku`: Código del producto
   - `barcode`: Código de barras (opcional)

### Flujo de Venta Automático

```php
// Al crear un pedido (automático)
Order::create([...]);
// ↓
// Se ejecuta boot() en Orders model
// ↓
// InventoryService::reduceStock() automáticamente
// ↓
// Se crea registro en inventory_movements
// ↓
// Si stock <= umbral → envía notificación LowStockNotification
```

### Ajuste Manual de Inventario

1. Ir a `/inventario`
2. Buscar el producto
3. Clic en "Ajustar"
4. Ingresar:
   - Nueva cantidad
   - Razón (recuento, producto dañado, etc.)
   - Notas adicionales
5. Guardar

### Ver Historial de Movimientos

1. En `/inventario`, clic en "Historial" del producto
2. Se muestra tabla con:
   - Fecha y hora
   - Tipo de movimiento (badge con color)
   - Cantidad modificada
   - Stock anterior y nuevo
   - Razón del cambio
   - Usuario que realizó el cambio
   - Notas adicionales

### Exportar Productos con Stock Bajo

1. En `/inventario`, clic en "Exportar Stock Bajo"
2. Se descarga CSV con:
   - ID del producto
   - Nombre
   - SKU
   - Stock actual
   - Umbral configurado
   - Estado

## Estructura de Base de Datos

### Tabla: `inventory_movements`

```sql
- id (bigint, PK)
- id_product (bigint, FK → products)
- id_variant (bigint, nullable, FK → variant_products)
- id_inventory (bigint, nullable, FK → inventories)
- type (enum: entrada, salida, ajuste, devolucion)
- quantity (integer) - Cantidad del movimiento
- cantidad_anterior (integer) - Stock antes del movimiento
- cantidad_nueva (integer) - Stock después del movimiento
- reason (string) - Razón del movimiento
- reference_type (string, nullable) - Tipo de documento relacionado
- reference_id (bigint, nullable) - ID del documento relacionado
- user_id (bigint, nullable, FK → users)
- notes (text, nullable) - Notas adicionales
- created_at, updated_at, deleted_at (timestamps)
```

**Índices:**
- `id_product` + `created_at` (para consultas rápidas)
- `id_inventory` + `created_at`
- `reference_type` + `reference_id` (seguimiento de referencias)

### Tabla: `inventories` (campos relevantes)

```sql
- id (bigint, PK)
- cantidad_inventario (integer) - Stock actual
- location (string, nullable) - Ubicación física
- id_status_inventory (string, nullable)
- seguimiento_inventario (boolean) - Activar seguimiento
- umbral_aviso_inventario (integer, nullable) - Umbral de alerta
- permitir_vender_sin_inventario (boolean) - Permitir ventas sin stock
- sku (string, nullable) - Código del producto
- barcode (string, nullable) - Código de barras
```

## API del Servicio

### InventoryService

```php
use App\Services\InventoryService;

// Reducir stock (venta)
InventoryService::reduceStock(
    $productId,
    $quantity,
    $variantId = null,
    $orderId = null
);

// Aumentar stock (entrada)
InventoryService::increaseStock(
    $productId,
    $quantity,
    $variantId = null,
    $reason = 'Entrada',
    $referenceType = null,
    $referenceId = null
);

// Ajustar stock manualmente
InventoryService::adjustStock(
    $productId,
    $newQuantity,
    $variantId = null,
    $reason = 'Ajuste manual',
    $notes = null
);

// Devolver stock (cancelación)
InventoryService::returnStock(
    $productId,
    $quantity,
    $variantId = null,
    $orderId = null
);

// Verificar disponibilidad
$available = InventoryService::checkAvailability(
    $productId,
    $quantity,
    $variantId = null
);

// Obtener historial
$movements = InventoryService::getMovements(
    $productId,
    $variantId = null,
    $limit = 50
);

// Obtener productos con stock bajo
$products = InventoryService::getLowStockProducts();
```

## Notificaciones

### LowStockNotification

Se envía cuando el stock llega al umbral:

**Canales:**
- Email
- Base de datos (tabla `notifications`)

**Destinatarios:**
- Usuarios con roles: `admin`, `super-admin`, `inventory-manager`

**Contenido:**
- Nombre del producto
- Stock actual
- Umbral configurado
- Enlace directo a gestión de inventario

**Consultar notificaciones:**
```php
$notifications = auth()->user()->notifications()
    ->where('type', 'App\Notifications\LowStockNotification')
    ->get();
```

## Relaciones de Modelos

```php
// Products
$product->inventory(); // belongsTo Inventories
$product->inventoryMovements(); // hasMany InventoryMovements

// Inventories
$inventory->product(); // belongsTo Products
$inventory->movements(); // hasMany InventoryMovements

// InventoryMovements
$movement->product(); // belongsTo Products
$movement->variant(); // belongsTo VariantProduct
$movement->inventory(); // belongsTo Inventories
$movement->user(); // belongsTo User
```

## Personalización

### Cambiar Roles que Reciben Alertas

En `InventoryService::checkLowStock()`:

```php
$admins = User::whereHas('roles', function($q) {
    $q->whereIn('name', ['tu-rol-1', 'tu-rol-2']);
})->get();
```

### Agregar Más Razones de Ajuste

En `inventory-management.blade.php`, modal de ajuste:

```blade
<option value="Tu nueva razón">Tu nueva razón</option>
```

### Personalizar Colores de Estado

En la vista, modificar badges:

```php
$typeColors = [
    'entrada' => 'green',
    'salida' => 'red',
    'ajuste' => 'blue',
    'devolucion' => 'yellow',
];
```

## Troubleshooting

### No se reduce el inventario al crear pedido

1. Verificar que `seguimiento_inventario = true` en inventories
2. Revisar logs: `storage/logs/laravel.log`
3. Verificar evento boot en Orders model

### No llegan notificaciones de stock bajo

1. Verificar configuración de email en `.env`
2. Verificar que el umbral esté configurado (`umbral_aviso_inventario > 0`)
3. Verificar roles de usuarios destinatarios
4. Ejecutar cola si usas queues: `php artisan queue:work`

### Discrepancias en el stock

1. Revisar historial en `/inventario` → "Historial"
2. Hacer ajuste manual con razón "Recuento físico"
3. Verificar tabla `inventory_movements` para auditoría

## Mejoras Futuras

- [ ] Reportes de rotación de inventario
- [ ] Predicción de reabastecimiento basada en ventas
- [ ] Integración con proveedores para pedidos automáticos
- [ ] Soporte para múltiples almacenes/ubicaciones
- [ ] Códigos de barras con generación automática
- [ ] App móvil para escaneo de inventario
- [ ] Alertas de productos próximos a vencer (fechas de caducidad)

## Soporte

Para dudas o problemas, contactar al equipo de desarrollo.
