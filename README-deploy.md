# Despliegue en Railway y MySQL gestionado

Resumen rápido:

- Empaqueta la aplicación con `Dockerfile` incluido en la raíz.
- Para pruebas locales puedes usar `docker-compose.yml` que incluye un servicio `db` de MySQL.
- En producción en Railway, provisiona un MySQL gestionado y configura variables de entorno.

Pasos locales (rápido):

1. Copia `.env.example` a `.env` y ajusta valores si usas el `db` del `docker-compose`.
2. Levanta el entorno:

```bash
docker-compose up --build
```

3. Abre `http://localhost:8080`.

Despliegue en Railway (resumen):

1. Regístrate / entra en https://railway.app y crea un nuevo proyecto.
2. Conecta tu repositorio (GitHub) o sube el código.
3. Railway detecta `Dockerfile` y construirá la imagen. Alternativamente, usa `railway up` desde tu máquina.
4. En Railway, añade el plugin o recurso de MySQL (Create Add-on → MySQL). Copia las variables que devuelve el plugin.
5. En `Settings` → `Environment` del proyecto, añade las variables de entorno que usa tu app:

   - `DB_HOST` → host proporcionado por el plugin
   - `DB_PORT` → puerto (normalmente 3306)
   - `DB_NAME` → nombre de la base
   - `DB_USER` → usuario
   - `DB_PASS` → contraseña

6. Despliega (por GitHub integration o `railway up`).

Notas importantes:

- No uses credenciales en el código. `modelos/conexion.php` ahora lee `DB_*` desde el entorno.
- Para pruebas locales puedes mantener el `db` en `docker-compose`. Para producción elimina el servicio `db` del `docker-compose` o no lo uses.
- Si Railway ofrece una URL única (p. ej. `DATABASE_URL`), extrae los campos o establece `DB_*` con esos valores.

Ayuda adicional que puedo hacer:

- Ajustar `Dockerfile` para incluir composer y dependencias si tu proyecto las usa.
- Crear workflow de GitHub Actions para desplegar automáticamente en Railway.
- Probar localmente la conexión al MySQL gestionado (si me das credenciales temporales).
