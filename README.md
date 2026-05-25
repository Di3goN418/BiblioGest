# BiblioGest

Repositorio de **BiblioGest** sistema de gestión diseñado para ejecutarse en un entorno local.

---

## Requisitos Previos

* **XAMPP** (con los servicios de Apache y MySQL activos).

---

## Instrucciones de Instalación y Configuración

Pasos para configurar el proyecto en un entorno local:

### 1. Configurar la Base de Datos
1. Abre **phpMyAdmin** desde el panel de control de XAMPP.
2. Crea una nueva base de datos.
3. Importa el archivo de respaldo `bibliotecanew.sql` incluido en este repositorio para generar las tablas y datos necesarios.

### 2. Configurar la Conexión
1. Dirígete a la carpeta del proyecto.
2. Abre el archivo `php/conexion.php` (o `phph/conexion.php` según corresponda).
3. Ajusta las credenciales de conexión (usuario, contraseña y nombre de la base de datos) para que coincidan con las de tu servidor local.

### 3. Despliegue Local
1. Mueve o copia la carpeta completa de **BiblioGest** dentro del directorio `htdocs` de tu instalación de XAMPP.
2. Abre tu navegador web de preferencia.
3. Ingresa a la siguiente dirección para comenzar a usar la aplicación:
   ```url
   http://localhost/BiblioGest
