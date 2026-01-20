# Vista de Órdenes de Compra - Implementación Completa

## 📋 Resumen
Se ha implementado exitosamente la vista de **Órdenes de Compra** usando el sistema `saved-views-table` con todas sus funcionalidades, replicando la implementación de la vista de Pedidos (Orders).

## ✅ Características Implementadas

### 1. **Componente Livewire** 
- **Archivo**: `app/Livewire/Product/OrderPurchases.php`
- **Trait**: `HasSavedViews` para funcionalidad de vistas guardadas
- **Paginación**: Implementada con Livewire
- **Ordenamiento**: Por todas las columnas principales
- **Selección múltiple**: Con soporte para selectAll

### 2. **Vista Blade**
- **Archivo**: `resources/views/livewire/product/order-purchases.blade.php`
- **Componente**: Usa `<x-saved-views-table>`
- **Responsive**: Adaptable a móviles

### 3. **Funcionalidades Principales**

#### 🔍 Búsqueda y Filtros
- ✅ Búsqueda en tiempo real por:
  - Número de referencia
  - Número de guía
  - Notas al distribuidor
- ✅ Filtros disponibles:
  - Por distribuidor
  - Por producto
- ✅ Tabs predefinidos:
  - Todos
  - Pendientes
  - Recibidos

#### 📊 Exportación de Datos
- ✅ Modal de exportación con opciones:
  - **Página actual**: Exporta solo los registros visibles
  - **Todos**: Exporta todas las órdenes
  - **Seleccionados**: Exporta solo los marcados
  - **Búsqueda actual**: Exporta resultados de búsqueda
  - **Vista filtrada**: Exporta con filtros aplicados
- ✅ Formatos de exportación:
  - CSV para Excel/Numbers
  - CSV plano

#### ✏️ CRUD Completo
- ✅ Crear nueva orden de compra
- ✅ Editar orden existente
- ✅ Eliminar orden
- ✅ Modal con formulario incluido

#### 🎯 Acciones Masivas
- ✅ Selección múltiple de registros
- ✅ Imprimir órdenes seleccionadas
- ✅ Marcar estado en lote:
  - Pendiente
  - Recibido
  - Cancelado

#### 💾 Vistas Guardadas
- ✅ Guardar configuración de tabla personalizada
- ✅ Filtros guardados
- ✅ Ordenamiento guardado
- ✅ Columnas visibles guardadas

### 4. **Columnas de la Tabla**

| Columna | Ordenable | Descripción |
|---------|-----------|-------------|
| Select | No | Checkbox para selección |
| ID | Sí | Identificador único |
| Fecha | Sí | Fecha de creación |
| Referencia | Sí | Número de referencia |
| Distribuidor | Sí | Proveedor |
| Producto | Sí | Producto ordenado |
| Fecha estimada | Sí | Fecha de llegada esperada |
| Guía | No | Número de guía de envío |
| Acciones | No | Editar/Eliminar |

## 🔗 Ruta Configurada

```php
Route: /orders_purchases
Controller: App\Livewire\Product\OrderPurchases
Permission: acceder-orders-purchases
Role: admin_general
```

## 📦 Modelos Relacionados

### OrderPurchases
**Ubicación**: `app/Models/Product/OrderPurchase/OrderPurchases.php`

**Campos**:
- `id_distribuidor` - ID del distribuidor
- `id_sucursal_destino` - Sucursal de destino
- `id_condiciones_pago` - Condiciones de pago
- `id_moneda_del_distribuidor` - Moneda
- `fecha_llegada_estimada` - Fecha estimada
- `id_empresa_trasnportista` - Transportista
- `numero_guia` - Número de guía
- `id_product` - Producto
- `numero_referencia` - Referencia
- `nota_al_distribuidor` - Notas

### Relaciones Disponibles
- `Distribuidores` → `app/Models/Distribuidor/Distribuidores.php`
- `Products` → `app/Models/Product/Products.php`

## 🎨 Interfaz de Usuario

### Header
- **Título**: "Órdenes de compra"
- **Descripción**: "Administra las órdenes de compra a distribuidores"
- **Botones**:
  - Exportar
  - Más acciones (dropdown)
  - Crear orden de compra

### Modales
1. **Modal de Exportación**: Configuración de exportación
2. **Modal de Crear/Editar**: Formulario para orden de compra

## 🚀 Uso

### Acceder a la vista
```
Navega a: /orders_purchases
```

### Crear nueva orden
1. Click en "Crear orden de compra"
2. Completa el formulario
3. Guarda

### Exportar datos
1. Click en "Exportar"
2. Selecciona opciones de exportación
3. Click en "Exportar órdenes"

### Aplicar filtros
1. Click en "Agregar filtro"
2. Selecciona tipo de filtro
3. Los resultados se actualizan automáticamente

### Guardar vista personalizada
1. Configura filtros, ordenamiento y columnas
2. Click en "Guardar vista de tabla"
3. Asigna un nombre
4. La vista queda guardada para uso futuro

## 🔧 Próximos Pasos (Opcionales)

- [ ] Añadir items de la orden de compra (OrderPurchaseItems)
- [ ] Añadir estados personalizados
- [ ] Integrar con sistema de inventario automático
- [ ] Notificaciones de órdenes
- [ ] Dashboard de estadísticas

## 🧪 Testing y Datos de Prueba

### Factory Disponible
**Archivo**: `database/factories/OrderPurchasesFactory.php`

```php
// Crear una orden de compra
OrderPurchases::factory()->create();

// Crear orden pendiente
OrderPurchases::factory()->pending()->create();

// Crear orden recibida
OrderPurchases::factory()->received()->create();

// Crear orden con tracking
OrderPurchases::factory()->withTracking()->create();

// Crear múltiples órdenes
OrderPurchases::factory()->count(50)->create();
```

### Comando de Demostración
Genera datos de prueba rápidamente:

```bash
# Generar 50 órdenes (por defecto)
php artisan demo:order-purchases

# Generar cantidad personalizada
php artisan demo:order-purchases --count=100
```

Este comando:
- ✅ Verifica que existan distribuidores y productos
- ✅ Genera órdenes con datos realistas
- ✅ Muestra estadísticas al finalizar
- ✅ Incluye barra de progreso

## 📝 Notas Técnicas

- **Trait HasSavedViews**: Proporciona toda la lógica de vistas guardadas
- **Paginación**: 10 registros por defecto, configurable
- **Búsqueda**: Debounce de 300ms para optimizar rendimiento
- **Selección**: Normalización automática de IDs
- **Exportación**: BOM UTF-8 para compatibilidad con Excel

## ✨ Beneficios

1. **Reutilizable**: Usa el mismo sistema que Orders
2. **Consistente**: Interfaz familiar para los usuarios
3. **Escalable**: Fácil de extender con nuevas funcionalidades
4. **Performante**: Optimizado con lazy loading y paginación
5. **Flexible**: Vistas guardadas personalizables

---

**Estado**: ✅ **Implementación Completa y Funcional**
**Última actualización**: 20 de enero de 2026
