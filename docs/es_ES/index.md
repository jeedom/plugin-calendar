Complemento para crear una agenda y activar acciones
(comando o escenario).

Configuración del plugin
=======================

La configuración es muy simple, después de descargar el complemento,
simplemente lo activas y eso es todo.

Configuración del equipo
=============================

Se puede acceder a la configuración de los dispositivos de calendario desde el menú
Complementos luego Organización.

Una vez allí, encontrará la lista de su Calendario.

Aquí encontrarás toda la configuración de tu equipo :

-   **Nombre del equipo** : nombre de tu calendario.

-   **Objeto padre** : indica el objeto padre al que
    pertenece equipo.

-   **Categoría** : categorías de equipos (puede pertenecer a
    categorías múltiples).

-   **Activer** : activa su equipo.

-   **Visible** : lo hace visible en el tablero.

-   **Widget, número de días** : define el número de días
    eventos para mostrar en el widget.

-   **Numero maximo de eventos** : establecer el número máximo
    eventos para mostrar en el tablero.

-   **No mostrar estado y pedidos
    activación / desactivación** : permite ocultar el estado de
    la agenda, así como los comandos para activarla o no.

-   **Lista de eventos del calendario.** : mostrar debajo de la
    lista de todos los eventos del calendario (un clic en él permite
    editar el evento directamente).

-   **Agregar evento** : agregar un evento al calendario.

-   **Agenda** : Visualización de una vista de tipo de calendario con todos
    eventos en los que puede mudarse, elija mostrarlo
    por semana o día, mover eventos (arrastrar y soltar) y un
    Al hacer clic en un evento se abrirá su ventana de edición.

Editar un evento
======================

La parte más importante del complemento, aquí es donde podrá
configura tu evento.

Evento
---------

Aqui encuentras :

-   **Nombre del evento.** : Nombre de tu evento.

-   **Icono** : le permite agregar un icono delante de su nombre
    equipo (para hacer esto, haga clic en "Elegir un icono").

-   **Couleur** : le permite elegir el color de su evento (un
    la marca de verificación también le permite hacerlo transparente).

-   **Color del texto** : le permite elegir el color del texto de
    tu evento.

-   **No mostrar en el tablero** : permite no mostrar
    este evento en el widget.

Inicia acción
---------------

El permite elegir las acciones que se realizarán al iniciar
el evento.

Para agregar una acción, simplemente haga clic en el botón + al final de
la línea, entonces vas a tener un botón para buscar un pedido
una vez que se encuentre, tendrá la opción de elegir si tiene alguna. Vosotras
puede agregar tanta acción como desee.

> **Tip**
>
> Es posible modificar el orden de las acciones manteniendo presionado / arrastrando
> esta


> **Tip**
>
>Es posible realizar las mismas acciones que en los escenarios (ver [aquí](https://jeedom.github.io/core/fr_FR/scenario))

Acción final
-------------

Igual que la acción de inicio, pero esta vez son las acciones
actuar al final del evento.

Programmation
-------------

Aquí es donde se encuentra todo el tiempo de gestión de su evento. :

-   **Inicio** : Fecha de inicio del evento.

-   **Fin** : Fecha de finalización del evento.

-   **Todo el día** : permite definir el evento en cualquier
    la jornada.

-   **Incluir por otro calendario** : Permite incluir otro
    evento en su evento actual. Por ejemplo, si tienes un
    evento repetido todos los lunes, e incluye esto
    evento A en su evento actual, entonces este será
    repetido automáticamente todos los lunes.

-   **Inclure** : le permite forzar una fecha de ocurrencia, puede
    pon varios separándolos con, (comas), puedes
    También defina un rango con : (dos puntos).

-   **Repetido** : Digamos que su evento se repite (si esto
    la casilla no está marcada, no tendrá las siguientes opciones).

-   **Modo de repetición** : le permite especificar el modo de repetición,
    se simple : todos los días, cada X días ... o la repetición cada
    el 1º, 2º… para repetir un evento cada 3er lunes del
    meses por ejemplo (las siguientes opciones pueden ser diferentes
    dependiendo de esta elección).

-   **Repite cada** : \ [solo modo de repetición simple \] permite
    definir la frecuencia de repetición del evento (por ejemplo, cada 3
    días o cada 2 meses ...).

-   **Le** : \ [modo de repetición el primero, el segundo ... solo \] :
    le permite elegir un ensayo cada segundo lunes del mes
    Por ejemplo.

-   **Solo el** : le permite restringir la repetición a ciertas
    días de la semana.

-   **Restriction** : le permite restringir solo el evento
    vacaciones o excluir vacaciones.

-   **Jusqu'à** : da la fecha final de ocurrencia del evento.

-   **Excluir por otro calendario** : permite excluir esto
    evento de acuerdo con otro calendario (para evitar, por ejemplo, que
    2 eventos contradictorios se encuentran juntos).

-   **Exclure** : igual que "Incluir" pero esta vez para excluir
    fechas.

> **Note**
>
> Los días festivos son franceses y solo francés, esto no
> no funciona para otros países

> **Note**
>
> En la parte superior derecha tiene 3 botones, uno para eliminar, uno para
> guardar y uno para duplicar. Al hacer clic en esta última libertad
> muestra el evento resultante de la duplicación para que
> puede cambiar el nombre por ejemplo.Así que no olvides
> guardar siguiendo un clic en el botón duplicar

Diario, pedidos y escenario
=============================

Una agenda tiene controles :

-   **En curso** : enumera los eventos actuales separados por
    comas, para usar en el escenario más simple y
    utilizar el operador contiene (coincidencias) o no contiene (no
    coincidencias), por ejemplo * \ [Apartamento \] \ [prueba \] \ [En progreso \] * coincidencias
    "/ Anniv / ", será verdadero si en la lista de eventos actuales hay
    un "Anniv"

- **Agregar una fecha** : permite que un escenario agregue una fecha a un evento (tenga cuidado si cambia el nombre del evento, también deberá corregirlo en el escenario). Puedes poner varios eventos separados por ,

- **Eliminar una fecha** : permite que un escenario excluya una fecha de un evento (tenga cuidado si cambia el nombre del evento, también deberá corregirlo en el escenario). Puedes poner varios eventos separados por ,

> **Note**
>
> Puede usar el comando "En progreso" como desencadenante
> en un escenario, cada actualización de la información activará
> la ejecución del escenario. Sin embargo, es mejor usar esto
> comando en un escenario programado con una prueba del valor.
