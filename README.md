# CEFIC --- Plataforma experimental SOAR para evaluación de controles de seguridad

CEFIC es un entorno experimental para comparar procesos automatizados
**sin controles de seguridad** frente a procesos equivalentes **con una
capa de seguridad SOAR**. Integra n8n, MySQL, phpMyAdmin y un dashboard
web, y utiliza Groq para análisis automatizado en los flujos con
seguridad.

## Arquitectura

  ------------------------------------------------------------------------
  Servicio              Función                                     Puerto
  --------------------- --------------------- ----------------------------
  n8n                   Automatización de                             5678
                        workflows             

  MySQL                 Base de datos `cefic`                  3307 → 3306

  phpMyAdmin            Administración de                        8080 → 80
                        MySQL                 

  Dashboard             Visualización de        Según `docker-compose.yml`
                        instrumentos y        
                        métricas              
  ------------------------------------------------------------------------

Zona horaria de n8n: `America/Lima`.

## Workflows

    N.º Proceso                   Escenario
  ----- ------------------------- --------------------
     01 Clasificador de correos   Sin seguridad
     02 Certificados              Sin seguridad
     03 Matrícula                 Sin seguridad
     04 Comunicados               Sin seguridad
     05 Clasificador de correos   Con seguridad SOAR
     06 Certificados              Con seguridad SOAR
     07 Matrícula                 Con seguridad SOAR
     08 Comunicados               Con seguridad SOAR

Los workflows 01--04 constituyen la línea base funcional. Los workflows
05--08 incorporan controles de seguridad, clasificación, score de
riesgo, enrutamiento y acciones automáticas según corresponda.

## Controles de seguridad evaluados

1.  Validación del remitente/origen.
2.  Validación o filtrado de entrada.
3.  Detección de amenazas.
4.  Clasificación de amenazas.
5.  Evaluación o score de riesgo.
6.  Enrutamiento automático según riesgo.
7.  Acción automática de respuesta.
8.  Notificación o alerta de incidente.
9.  Registro de eventos.
10. Trazabilidad y auditoría de ejecución.
11. Protección o bloqueo del activo.

## Instrumentos del dashboard

El dashboard presenta seis instrumentos:

1.  **Lista de cotejo --- Integración de controles de seguridad:**
    controles integrados, controles requeridos e índice de integración.
2.  **Ficha SLA --- MTTR:** hora de detección, hora de resolución,
    tiempo por incidente y Tiempo Medio de Respuesta.
3.  **Ficha de clasificación de amenazas:** clasificación del sistema,
    clasificación real, aciertos, score y tasa de clasificación
    correcta.
4.  **Lista de cotejo --- Ejecuciones seguras:** incidentes, activos
    comprometidos, ejecuciones seguras y porcentaje de ejecuciones
    seguras.
5.  **Ficha SLA --- MTTD:** hora de ingreso, hora de detección, tiempo
    de detección y Tiempo Medio de Detección.
6.  **Registro de disponibilidad:** horas evaluadas, horas con fallos,
    horas disponibles y tasa de disponibilidad.

## Base de datos

Base principal:

``` text
cefic
```

Tablas principales:

-   `ejecuciones`
-   `log_eventos`
-   `clasificaciones_amenazas`
-   `certificados`
-   `matriculas`
-   `comunicados`
-   `controles_seguridad`
-   `integracion_controles`

`ejecuciones` registra flujo, proceso, escenario, inicio, fin, estado,
incidente, activo comprometido y ejecución segura.

`log_eventos` registra ID de ejecución e incidente, horas de
ingreso/detección/resolución, categoría, categoría real, score, nivel,
acción ejecutada, activo comprometido e incidente resuelto.

`clasificaciones_amenazas` permite comparar categoría del sistema,
categoría real, clasificación correcta y score de riesgo.

## Groq

Los workflows con seguridad que requieren IA utilizan:

``` text
openai/gpt-oss-20b
```

La API key se proporciona mediante:

``` text
GROQ_API_KEY
```

No guardes la API key directamente en los JSON de n8n ni en el
repositorio.

## Variables de entorno

Ejemplo conceptual de `.env`:

``` env
MYSQL_ROOT_PASSWORD=CAMBIAR
MYSQL_DATABASE=cefic
MYSQL_USER=cefic_user
MYSQL_PASSWORD=CAMBIAR
GROQ_API_KEY=CAMBIAR
```

`.gitignore` recomendado:

``` gitignore
.env
*.log
```

## Iniciar CEFIC

``` bash
cd ~/CEFIC
docker compose up -d
docker compose ps
```

Si tu instalación utiliza Compose clásico:

``` bash
docker-compose up -d
```

## Credencial MySQL en n8n

Entre contenedores debe utilizarse el puerto interno 3306:

``` text
Host: mysql
Database: cefic
User: cefic_user
Password: valor de MYSQL_PASSWORD
Port: 3306
```

El puerto 3307 es para acceder a MySQL desde el host.

## Verificación general

Ejecuciones:

``` bash
docker exec cefic-mysql-1 mysql \
-uroot \
-p"$MYSQL_ROOT_PASSWORD" \
cefic \
-e "
SELECT id, flujo, proceso, escenario, estado,
       incidente, activo_comprometido, ejecucion_segura
FROM ejecuciones
ORDER BY id DESC
LIMIT 20;
"
```

Eventos:

``` bash
docker exec cefic-mysql-1 mysql \
-uroot \
-p"$MYSQL_ROOT_PASSWORD" \
cefic \
-e "
SELECT id, flujo, proceso, escenario, id_incidente,
       categoria, categoria_real, score_riesgo,
       nivel, accion_ejecutada
FROM log_eventos
ORDER BY id DESC
LIMIT 20;
"
```

Clasificaciones:

``` bash
docker exec cefic-mysql-1 mysql \
-uroot \
-p"$MYSQL_ROOT_PASSWORD" \
cefic \
-e "
SELECT id, id_ejecucion, flujo, proceso, escenario,
       categoria_sistema, categoria_real,
       clasificacion_correcta, score_riesgo
FROM clasificaciones_amenazas
ORDER BY id DESC
LIMIT 20;
"
```

## Estado de validación

Los ocho workflows fueron probados funcionalmente:

``` text
01  Clasificador de correos — SIN seguridad       OK
02  Certificados — SIN seguridad                  OK
03  Matrícula — SIN seguridad                     OK
04  Comunicados — SIN seguridad                   OK
05  Clasificador de correos — CON seguridad       OK
06  Certificados — CON seguridad                  OK
07  Matrícula — CON seguridad                     OK
08  Comunicados — CON seguridad                   OK
```

En las pruebas con seguridad se verificaron registros de clasificación,
score de riesgo, nivel, acciones de respuesta, trazabilidad y estado de
ejecución.

## Resultados de prueba observados

-   Workflow 05: phishing de riesgo alto y bloqueo automático.
-   Workflow 06: solicitud sospechosa enviada a cuarentena.
-   Workflow 07: matrícula legítima procesada con riesgo bajo.
-   Workflow 08: phishing de riesgo alto y comunicado cancelado
    automáticamente.
-   En las ejecuciones verificadas de los flujos con seguridad, el
    activo comprometido permaneció en `0` y la ejecución segura en `1`.

Los scores y tiempos concretos pueden variar entre ejecuciones; las
métricas finales deben calcularse con los datos experimentales
definitivos.

## MTTD y MTTR

MTTD:

``` text
hora_deteccion - hora_ingreso
```

MTTR:

``` text
hora_resolucion - hora_deteccion
```

Consulta de ejemplo:

``` sql
SELECT
    id_incidente,
    ROUND(TIMESTAMPDIFF(SECOND, hora_ingreso, hora_deteccion) / 60.0, 4) AS mttd_min,
    ROUND(TIMESTAMPDIFF(SECOND, hora_deteccion, hora_resolucion) / 60.0, 4) AS mttr_min
FROM log_eventos
WHERE hora_deteccion IS NOT NULL;
```

## Estructura del proyecto

``` text
CEFIC/
├── .env
├── docker-compose.yml
├── dashboard/
│   ├── Dockerfile
│   ├── api.php
│   ├── db.php
│   ├── index.php
│   └── assets/
└── workflows/
    ├── 01-...
    ├── 02-...
    ├── 03-...
    ├── 04-...
    ├── 05-...
    ├── 06-...
    ├── 07-...
    └── 08-...
```

## Consideraciones

-   No publicar `.env` ni credenciales.
-   Mantener MySQL en `utf8mb4` para tildes y caracteres especiales.
-   Ejecutar múltiples repeticiones por escenario antes del análisis
    final.
-   Separar los resultados `sin_seguridad` y `con_seguridad`.
-   Calcular los indicadores de tesis usando las ejecuciones
    experimentales definitivas, no registros preliminares.
-   El mecanismo demostrativo utilizado en el nodo de protección de
    datos del Workflow 07 no debe describirse como cifrado criptográfico
    si no utiliza un algoritmo criptográfico real.

## Objetivo experimental

CEFIC busca proporcionar evidencia cuantitativa y trazable para comparar
procesos automatizados antes y después de integrar controles SOAR
mediante:

-   índice de integración de controles;
-   MTTD;
-   MTTR;
-   precisión de clasificación de amenazas;
-   porcentaje de ejecuciones seguras;
-   disponibilidad de los flujos.

------------------------------------------------------------------------

**Proyecto:** CEFIC\
**Stack:** Docker + n8n + MySQL + PHP\
