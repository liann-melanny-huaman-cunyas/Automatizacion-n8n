# Automatización de procesos con integración de controles de seguridad SOAR

Proyecto experimental orientado a evaluar el efecto de la integración de
controles de seguridad SOAR en procesos automatizados mediante n8n.

El estudio se aplica a procesos de **CEFIC** y compara su comportamiento
en dos escenarios:

-   **Sin seguridad:** los procesos se ejecutan sin controles de
    seguridad SOAR.
-   **Con seguridad:** los mismos procesos incorporan controles de
    detección, clasificación, evaluación de riesgo y respuesta
    automatizada.

La solución utiliza Docker, n8n, MySQL, phpMyAdmin, PHP y Groq para
automatizar los procesos, registrar evidencias de ejecución y obtener
métricas que permitan comparar ambos escenarios.

## Arquitectura

La arquitectura experimental está compuesta por los siguientes
servicios:

  ------------------------------------------------------------------------
  Servicio              Función                                     Puerto
  --------------------- --------------------- ----------------------------
  n8n                   Automatización y                              5678
                        orquestación de       
                        workflows             

  MySQL                 Almacenamiento de                      3307 → 3306
                        ejecuciones, eventos  
                        y métricas            

  phpMyAdmin            Administración visual                    8080 → 80
                        de MySQL              

  Dashboard             Visualización de        Según `docker-compose.yml`
                        resultados e          
                        instrumentos          
  ------------------------------------------------------------------------

La zona horaria configurada para n8n es:

``` text
America/Lima
```

## Workflows experimentales

Para comparar los procesos antes y después de integrar controles de
seguridad SOAR se implementaron ocho workflows.

### Escenario sin seguridad

    N.º Proceso                   Escenario
  ----- ------------------------- ---------------
     01 Clasificador de correos   Sin seguridad
     02 Certificados              Sin seguridad
     03 Matrícula                 Sin seguridad
     04 Comunicados               Sin seguridad

Los workflows 01--04 representan la ejecución funcional de los procesos
sin la integración de controles SOAR.

### Escenario con seguridad SOAR

    N.º Proceso                   Escenario
  ----- ------------------------- ---------------
     05 Clasificador de correos   Con seguridad
     06 Certificados              Con seguridad
     07 Matrícula                 Con seguridad
     08 Comunicados               Con seguridad

Los workflows 05--08 representan los mismos procesos después de integrar
controles de seguridad SOAR.

## Comparación experimental

La correspondencia entre los escenarios es:

  Proceso                     Sin seguridad   Con seguridad
  ------------------------- --------------- ---------------
  Clasificador de correos                01              05
  Certificados                           02              06
  Matrícula                              03              07
  Comunicados                            04              08

Esto permite comparar cada proceso bajo condiciones equivalentes antes y
después de integrar los controles de seguridad.

## Controles de seguridad evaluados

El experimento considera los siguientes controles:

1.  Validación del remitente u origen.
2.  Validación o filtrado de entrada.
3.  Detección de amenazas.
4.  Clasificación de amenazas.
5.  Evaluación o score de riesgo.
6.  Enrutamiento automático según el nivel de riesgo.
7.  Acción automática de respuesta.
8.  Notificación o alerta de incidente.
9.  Registro de eventos.
10. Trazabilidad y auditoría de ejecución.
11. Protección o bloqueo del activo.

La evidencia de implementación de los controles se almacena en la base
de datos para su posterior evaluación.

## Instrumentos de evaluación

El dashboard presenta seis instrumentos para analizar los resultados
experimentales.

### 1. Lista de cotejo --- Integración de controles de seguridad

Permite evaluar:

-   controles integrados;
-   controles requeridos;
-   índice de integración de controles.

### 2. Ficha SLA --- Tiempo Medio de Respuesta (MTTR)

Permite registrar y calcular:

-   hora de detección;
-   hora de resolución;
-   tiempo de respuesta por incidente;
-   MTTR.

### 3. Ficha de clasificación de amenazas

Permite comparar:

-   clasificación determinada por el sistema;
-   clasificación real;
-   clasificación correcta o incorrecta;
-   score de riesgo;
-   tasa de clasificación correcta.

### 4. Lista de cotejo --- Ejecuciones seguras

Permite evaluar:

-   número de incidentes;
-   activos comprometidos;
-   ejecuciones seguras;
-   porcentaje de ejecuciones seguras.

### 5. Ficha SLA --- Tiempo Medio de Detección (MTTD)

Permite registrar y calcular:

-   hora de ingreso;
-   hora de detección;
-   tiempo de detección;
-   MTTD.

### 6. Registro de disponibilidad del flujo

Permite evaluar:

-   horas evaluadas;
-   horas con fallos;
-   horas disponibles;
-   tasa de disponibilidad.

## Base de datos

La base de datos utilizada por la implementación se denomina:

``` text
cefic
```

Este nombre corresponde a la configuración técnica actual de la base de
datos del proyecto.

Entre las tablas utilizadas se encuentran:

-   `ejecuciones`
-   `log_eventos`
-   `clasificaciones_amenazas`
-   `certificados`
-   `matriculas`
-   `comunicados`
-   `controles_seguridad`
-   `integracion_controles`

### `ejecuciones`

Registra información general de cada ejecución:

-   flujo;
-   proceso;
-   escenario;
-   inicio;
-   fin;
-   estado;
-   incidente;
-   activo comprometido;
-   ejecución segura.

### `log_eventos`

Registra la trazabilidad de los eventos:

-   ID de ejecución;
-   ID de incidente;
-   hora de ingreso;
-   hora de detección;
-   hora de resolución;
-   categoría;
-   categoría real;
-   score de riesgo;
-   nivel de riesgo;
-   acción ejecutada;
-   activo comprometido;
-   incidente resuelto.

### `clasificaciones_amenazas`

Permite evaluar el resultado de la clasificación:

-   categoría determinada por el sistema;
-   categoría real;
-   clasificación correcta;
-   score de riesgo.

### Tablas de los procesos

Los resultados funcionales de los procesos se almacenan en:

-   `certificados`
-   `matriculas`
-   `comunicados`

## Integración con Groq

Los workflows con seguridad que requieren clasificación mediante
inteligencia artificial utilizan la API de Groq.

Modelo utilizado durante las pruebas:

``` text
openai/gpt-oss-20b
```

La clave de acceso se proporciona mediante la variable de entorno:

``` text
GROQ_API_KEY
```

> No se debe guardar la API key directamente en los archivos JSON de los
> workflows ni publicarla en el repositorio.

## Variables de entorno

Las credenciales y valores sensibles se almacenan en `.env`.

Ejemplo:

``` env
MYSQL_ROOT_PASSWORD=CAMBIAR
MYSQL_DATABASE=cefic
MYSQL_USER=cefic_user
MYSQL_PASSWORD=CAMBIAR

GROQ_API_KEY=CAMBIAR
```

El archivo `.env` no debe subirse al repositorio.

Se recomienda utilizar un `.env.example` sin credenciales reales.

## `.gitignore`

Como mínimo:

``` gitignore
.env
.env.*
!.env.example

*.log
node_modules/
.vscode/
.idea/
```

## Iniciar el entorno experimental

Desde el directorio técnico actual del proyecto:

``` bash
cd ~/CEFIC
```

Levantar los contenedores:

``` bash
docker compose up -d
```

Comprobar su estado:

``` bash
docker compose ps
```

Si la instalación utiliza Docker Compose clásico:

``` bash
docker-compose up -d
```

## Configuración de MySQL en n8n

Cuando n8n se comunica con MySQL dentro de la red Docker debe utilizar
el puerto interno `3306`.

Configuración:

``` text
Host: mysql
Database: cefic
User: cefic_user
Password: valor configurado en MYSQL_PASSWORD
Port: 3306
```

El puerto `3307` corresponde al acceso a MySQL desde el host.

## Verificación de ejecuciones

Para consultar las ejecuciones recientes:

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

## Verificación de eventos de seguridad

``` bash
docker exec cefic-mysql-1 mysql \
-uroot \
-p"$MYSQL_ROOT_PASSWORD" \
cefic \
-e "
SELECT id, flujo, proceso, escenario,
       id_ejecucion, id_incidente,
       categoria, categoria_real,
       score_riesgo, nivel,
       accion_ejecutada,
       activo_comprometido,
       incidente_resuelto
FROM log_eventos
ORDER BY id DESC
LIMIT 20;
"
```

## Verificación de clasificaciones

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

## MTTD y MTTR

### MTTD

El Tiempo Medio de Detección se obtiene a partir de:

``` text
hora_deteccion - hora_ingreso
```

### MTTR

El Tiempo Medio de Respuesta se obtiene a partir de:

``` text
hora_resolucion - hora_deteccion
```

Consulta de apoyo:

``` sql
SELECT
    id_incidente,
    ROUND(
        TIMESTAMPDIFF(SECOND, hora_ingreso, hora_deteccion) / 60.0,
        4
    ) AS mttd_min,
    ROUND(
        TIMESTAMPDIFF(SECOND, hora_deteccion, hora_resolucion) / 60.0,
        4
    ) AS mttr_min
FROM log_eventos
WHERE hora_deteccion IS NOT NULL;
```

## Estado de validación de los workflows

Durante las pruebas funcionales se verificaron los ocho workflows:

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

## Resultados observados durante las pruebas

En las ejecuciones de validación se observaron los siguientes
comportamientos:

-   El Workflow 05 detectó un correo clasificado como phishing de riesgo
    alto y ejecutó una acción automática de bloqueo.
-   El Workflow 06 identificó una solicitud sospechosa y la envió a
    cuarentena.
-   El Workflow 07 procesó correctamente una matrícula legítima con
    riesgo bajo.
-   El Workflow 08 detectó phishing con riesgo alto y canceló
    automáticamente el comunicado.
-   En las ejecuciones verificadas de los escenarios con seguridad, los
    activos comprometidos permanecieron en `0` y las ejecuciones fueron
    registradas como seguras.

Estos resultados corresponden a pruebas de validación. Los indicadores
finales deben calcularse utilizando las ejecuciones experimentales
definitivas.

## Estructura técnica del repositorio

``` text
Automatizacion-n8n/
├── .gitignore
├── README.md
├── docker-compose.yml
├── dashboard/
│   ├── Dockerfile
│   ├── api.php
│   ├── db.php
│   ├── index.html
│   ├── index.php
│   └── assets/
└── workflows/
    ├── 01-clasificador-sin-seguridad.json
    ├── 02-certificados-sin-seguridad.json
    ├── 03-matricula-sin-seguridad.json
    ├── 04-comunicados-sin-seguridad.json
    ├── 05-clasificador-con-seguridad.json
    ├── 06-certificados-con-seguridad.json
    ├── 07-matricula-con-seguridad.json
    └── 08-comunicados-con-seguridad.json
```

El directorio local puede conservar el nombre `CEFIC` debido a la
configuración técnica existente. Esto no implica que la plataforma o los
workflows se denominen CEFIC.

## Consideraciones de seguridad

-   No publicar `.env`.
-   No publicar contraseñas ni API keys.
-   No insertar la API key de Groq directamente en los JSON exportados
    de n8n.
-   Mantener las credenciales fuera del control de versiones.
-   Utilizar `utf8mb4` en MySQL para conservar correctamente caracteres
    especiales y tildes.
-   Revisar los workflows exportados antes de publicarlos para comprobar
    que no contengan secretos.
-   Mantener separados los registros de los escenarios `sin_seguridad` y
    `con_seguridad`.

## Consideraciones para la evaluación experimental

Para obtener resultados comparables:

1.  Ejecutar ambos escenarios bajo condiciones equivalentes.
2.  Realizar múltiples ejecuciones por cada workflow.
3.  Registrar los resultados en MySQL.
4.  Separar los datos correspondientes a `sin_seguridad` y
    `con_seguridad`.
5.  Calcular las métricas utilizando las ejecuciones experimentales
    definitivas.
6.  Evitar utilizar registros preliminares de configuración como parte
    de los resultados finales.
7.  Conservar la trazabilidad entre ejecución, incidente, clasificación
    y acción realizada.

## Objetivo experimental

El objetivo es evaluar el efecto de integrar controles de seguridad SOAR
en los procesos automatizados aplicados al caso de estudio, comparando
el comportamiento de los procesos antes y después de incorporar dichos
controles.

La comparación utiliza indicadores relacionados con:

-   integración de controles de seguridad;
-   Tiempo Medio de Detección (MTTD);
-   Tiempo Medio de Respuesta (MTTR);
-   clasificación de amenazas;
-   ejecuciones seguras;
-   activos comprometidos;
-   disponibilidad de los flujos.

## Tecnologías utilizadas

-   n8n
-   Docker
-   Docker Compose
-   MySQL
-   phpMyAdmin
-   PHP
-   Groq API

## Caso de aplicación

Los workflows experimentales se aplican a procesos de **CEFIC** como
caso de estudio. CEFIC no corresponde al nombre de la plataforma SOAR ni
al nombre de los workflows; es la organización sobre cuyos procesos se
realiza la comparación experimental antes y después de integrar
controles de seguridad SOAR.
