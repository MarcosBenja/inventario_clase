# Sistema de Control de Inventario

**Nombre del proyecto:** Inventario  
**Nombre del estudiante:** Marcos Benjamin Morazan Rivas  

---

## Descripción General

Aplicación web para el control de inventario de una pequeña empresa. Permite administrar categorías, productos, usuarios y registrar movimientos de entrada y salida de existencias con actualización automática de stock.

## Tecnologías Utilizadas

- PHP 7.4+
- MySQL 5.7+
- HTML5
- Bootstrap 5.3
- Bootstrap Icons 1.11
- XAMPP

## Estructura del Proyecto

```
inventario/
├── config/
│   └── database.php
├── includes/
│   ├── auth.php
│   ├── header.php
│   └── footer.php
├── assets/
│   └── css/
│       └── style.css
├── modules/
│   ├── auth/         (login, logout)
│   ├── dashboard/    (panel principal)
│   ├── categorias/   (CRUD)
│   ├── productos/    (CRUD)
│   ├── entradas/     (registro + historial)
│   ├── salidas/      (registro + historial)
│   ├── movimientos/  (historial unificado)
│   └── usuarios/     (CRUD)
├── database.sql
├── setup.php
└── index.php
```

## Instrucciones de Instalación

### Paso 1 — Instalar y Arrancar XAMPP
1. Abrir **XAMPP Control Panel**.
2. Iniciar los servicios **Apache** y **MySQL**.

### Paso 2 — Copiar el Proyecto
1. Copiar la carpeta completa del proyecto a:
   ```
   C:\xampp\htdocs\inventario\
   ```
   (el nombre de la carpeta debe ser exactamente **inventario**)

### Paso 3 — Importar la Base de Datos
1. Abrir el navegador y entrar a:
   ```
   http://localhost/phpmyadmin
   ```
2. Hacer clic en **Nueva** (panel izquierdo).
3. Escribir el nombre: **`inventario_db`** y hacer clic en **Crear**.
4. Con la base de datos seleccionada, ir a la pestaña **Importar**.
5. Hacer clic en **Elegir archivo** y seleccionar `database.sql` (ubicado dentro de la carpeta del proyecto).
6. Hacer clic en **Continuar** o **Importar** al final de la página.
*(Esto creará las tablas de la base de datos, ingresará categorías, productos de muestra y los usuarios por defecto automáticamente).*

### Paso 4 — Acceder al Sistema
1. Abrir en el navegador:
   ```
   http://localhost/inventario/
   ```
2. Ingresar con cualquiera de las credenciales de acceso detalladas más abajo.

---

## Credenciales de Acceso

| Rol           | Usuario    | Contraseña |
|---------------|------------|------------|
| Administrador | `MARCOS`   | `1234`     |
| Operador      | `OPERADOR` | `1234`     |

---

## Base de Datos

**Nombre:** `inventario_db`  
**Motor:** InnoDB / MySQL  

### Tablas

| Tabla       | Descripción                          |
|-------------|--------------------------------------|
| `usuarios`  | Usuarios del sistema                 |
| `categorias`| Categorías de productos              |
| `productos` | Productos del inventario             |
| `entradas`  | Movimientos de entrada de stock      |
| `salidas`   | Movimientos de salida de stock       |

---

## Permisos por Rol

| Módulo           | Administrador | Operador  |
|------------------|:-------------:|:---------:|
| Dashboard        | ✅            | ✅        |
| Categorías CRUD  | ✅            | ❌        |
| Productos CRUD   | ✅            | Solo ver  |
| Entradas         | ✅            | ✅        |
| Salidas          | ✅            | ✅        |
| Historial        | ✅            | ✅        |
| Usuarios CRUD    | ✅            | ❌        |
