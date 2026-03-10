# 🚀 Flujo Correcto --- Laravel + Vue + Vite

Este proyecto utiliza **Laravel + Vue + Vite** para el manejo del
frontend moderno.

Para que el sistema funcione correctamente en entorno local, debes
levantar **dos procesos simultáneamente**.

------------------------------------------------------------------------

## 📌 1️⃣ Requisitos Previos

Antes de comenzar, asegúrate de tener instalado:

-   PHP (compatible con la versión del proyecto)
-   Composer
-   Node.js (v18 o superior recomendado)
-   NPM

Verifica versiones:

``` bash
php -v
composer -v
node -v
npm -v
```

------------------------------------------------------------------------

## 📌 2️⃣ Instalar Dependencias

Desde la raíz del proyecto ejecutar:

### Backend (Laravel)

``` bash
composer install
```

### Frontend (Vue + Vite)

``` bash
npm install
```

------------------------------------------------------------------------

## 📌 3️⃣ Configuración del entorno

Si no existe archivo `.env`, crear uno:

``` bash
cp .env.example .env
```

Luego generar la clave:

``` bash
php artisan key:generate
```

Configurar conexión a base de datos en `.env`.

------------------------------------------------------------------------

## 📌 4️⃣ Levantar el proyecto en modo desarrollo

Este proyecto requiere **dos terminales abiertas**.

### 🔹 Terminal 1 --- Servidor Laravel

``` bash
php artisan serve
```

Disponible en:

http://127.0.0.1:8000

### 🔹 Terminal 2 --- Servidor Vite (Frontend)

``` bash
npm run dev
```

Esto iniciará el servidor de Vite con:

-   Hot Reload
-   Compilación en tiempo real
-   Integración automática con Laravel

------------------------------------------------------------------------

## 📌 5️⃣ Acceder al proyecto

Abrir en navegador:

http://127.0.0.1:8000

------------------------------------------------------------------------

# 🏗 Modo Producción (Build Local)

Si deseas compilar los assets sin usar servidor Vite:

``` bash
npm run build
```

Esto generará:

    public/build/
        manifest.json
        assets/

Después de esto, Laravel podrá servir los archivos estáticos sin
necesidad de `npm run dev`.

------------------------------------------------------------------------

# ❗ Error Común

### Vite manifest not found

Si aparece el error:

Vite manifest not found at: public/build/manifest.json

Significa que:

-   No ejecutaste `npm run dev`
-   O no ejecutaste `npm run build`

Solución: ejecutar alguno de los dos comandos.

------------------------------------------------------------------------

# ✅ Resumen Ejecutivo

  Entorno      Comando requerido
  ------------ -----------------------------------
  Desarrollo   php artisan serve + npm run dev
  Producción   php artisan serve + npm run build

------------------------------------------------------------------------

# 🔐 Recomendación

Nunca subir la carpeta `node_modules` al repositorio. Ejecutar siempre
`npm install` después de clonar el proyecto.

------------------------------------------------------------------------

Proyecto listo para trabajar 🚀
