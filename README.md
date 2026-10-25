# CRUD Rápido con Laravel

Laboratorio #2: CRUD de productos con Laravel, generado con `ibex/crud-generator` y Bootstrap.

---

## 1. Detalles del laboratorio

| Campo | Detalle |
|---|---|
| Curso | Herramientas de la Programación Aplicada I |
| Laboratorio | #2 – Acercamiento a un CRUD rápido en Laravel |
| Docente | Ing. Irina Fong |
| Objetivo | Crear una aplicación web que permita **crear, leer, actualizar y eliminar** productos usando Laravel, migraciones y un generador de CRUD |
| Tabla utilizada | `products` (`id`, `description`, `price`, `stock`, `timestamps`) |
| Ruta principal | `http://127.0.0.1:8000/products` |

---

## 2. Evidencias

### 2.1 Insertar registros

**Formulario de creación**

![Formulario de creación](docs/img/01-crear-formulario.png)

**Registro insertado en el listado**

![Registro insertado](docs/img/02-crear-listado.png)

### 2.2 Modificar registros

**Formulario de edición**

![Formulario de edición](docs/img/03-modificar-formulario.png)

**Listado con el registro modificado**

![Registro modificado](docs/img/04-modificar-listado.png)

### 2.3 Eliminar registros

**Antes de eliminar**

![Antes de eliminar](docs/img/05-eliminar-antes.png)

**Después de eliminar**

![Después de eliminar](docs/img/06-eliminar-despues.png)

### 2.4 Otras vistas

**Detalle del producto (Show)**

![Detalle del producto](docs/img/07-show.png)

---

## 3. Controles utilizados

| Control | Dónde se usa | Función |
|---|---|---|
| Campo de texto (`input text`) | Formulario: *Description* | Ingresar la descripción del producto |
| Campo numérico (`input number`) | Formulario: *Price* y *Stock* | Ingresar precio y existencias |
| Botón **Create New** | Listado | Abre el formulario de creación |
| Botón **Submit** | Formularios | Envía y guarda los datos |
| Botón **Show** | Listado | Muestra el detalle de un producto |
| Botón **Edit** | Listado | Abre el formulario de edición |
| Botón **Delete** | Listado | Elimina el producto |
| Botón **Back** | Vista Show | Regresa al listado |
| Tabla | Listado | Muestra los registros (No, Description, Price, Stock) |
| Barra de navegación | Todas las vistas | Navegación general (layout de Bootstrap) |

---

## 4. Tecnologías y versiones

| Tecnología | Versión |
|---|---|
| Laravel | _(ejecutar `php artisan --version`)_ |
| PHP | _(ejecutar `php -v`)_ |
| MySQL (WAMP) | _(ver en phpMyAdmin)_ |
| WampServer | _(ver en el menú de WAMP)_ |
| Composer | _(ejecutar `composer -V`)_ |
| Node.js | _(ejecutar `node -v`)_ |
| npm | _(ejecutar `npm -v`)_ |
| laravel/ui | 4.6.3 |
| ibex/crud-generator | _(ver en `composer.json`)_ |
| Bootstrap | Incluido con `laravel/ui` |
| Vite | Incluido con Laravel |

---

## 5. Proceso de instalación

### Requisitos previos

- PHP, Composer, Node.js y npm instalados
- WampServer en ejecución (Apache y MySQL en verde)
- Git

### Pasos para ejecutar el proyecto desde GitHub

1. **Clonar el repositorio**

   ```bash
   git clone https://github.com/jazidsanchez-create/Lab_crud-rapido.git
   cd Lab_crud-rapido
   ```

2. **Instalar dependencias**

   ```bash
   composer install
   npm install
   ```

3. **Crear el archivo de entorno**

   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

4. **Crear la base de datos** `crud_rapido` en phpMyAdmin (`http://localhost/phpmyadmin`).

5. **Configurar el `.env`**

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=crud_rapido
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Ejecutar las migraciones**

   ```bash
   php artisan config:clear
   php artisan migrate
   ```

7. **Compilar el frontend** (dejar esta terminal abierta)

   ```bash
   npm run dev
   ```

8. **Levantar el servidor** (en otra terminal)

   ```bash
   php artisan serve
   ```

9. **Abrir en el navegador:** <http://127.0.0.1:8000/products>

### Cómo se creó el proyecto (resumen)

```bash
laravel new crud_rapido
php artisan make:model Product -m
php artisan migrate
composer require ibex/crud-generator --dev
php artisan vendor:publish --tag=crud
php artisan make:crud products
```

Y se registró la ruta en `routes/web.php`:

```php
Route::resource('products', ProductController::class);
```

### Problemas comunes y soluciones

| Problema | Solución |
|---|---|
| `Specified key was too long; max key length is 1000 bytes` | Agregar `Schema::defaultStringLength(191);` en `AppServiceProvider::boot()` y poner `'engine' => 'InnoDB'` en `config/database.php`; luego `php artisan migrate:fresh` |
| Configuración del `.env` no se aplica | `php artisan config:clear` |
| Clase no encontrada | `composer dump-autoload` |
| Página sin estilos o error de Vite manifest | Ejecutar `npm install` y mantener `npm run dev` activo |

---

## 6. Detalles del autor

| Campo | Detalle |
|---|---|
| Autor | Jazid Sánchez |
| Fecha | 16 de octubre de 2026 |
| Repositorio | <https://github.com/jazidsanchez-create/Lab_crud-rapido> |

---

## 7. Referencias

- Guía del Laboratorio #2 de CRUD con Laravel – Ing. Irina Fong.
- Tutorial en video: <https://www.youtube.com/watch?v=j5baJsM_Adc&t=111s>
- Documentación de Laravel: <https://laravel.com/docs>
- Migraciones en Laravel: <https://laravel.com/docs/migrations>
- Asignación masiva (`$fillable`): <https://laravel.com/docs/eloquent#mass-assignment>
- Laravel UI: <https://github.com/laravel/ui>
- Ibex CRUD Generator: <https://github.com/awais-vteams/laravel-crud-generator>
