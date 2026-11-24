---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Enviar a todos](../../../media/bricks/broadcast2-1.png)

Envía un mensaje a otras partes de tu proyecto y les dice que empiecen sus propios scripts. Es como dar un aviso que todos escuchan.

**¿Por qué enviar mensajes?**

Con los mensajes, los objetos de una escena pueden hablar entre ellos y trabajar juntos. Un objeto envía un mensaje a todos los demás objetos. Los objetos que lo reciben pueden reaccionar a ese mensaje.

¡Estos mensajes **no se ven** en la pantalla! Si quieres mostrar mensajes, usa los bloques de la categoría “Apariencia”.

**¿Cómo funciona?**

- *Objeto que envía:* El objeto que envía empieza la comunicación con el bloque *“Enviar a todos”*. Este bloque envía un mensaje a todos los demás objetos de la escena (también al fondo).
- *Objeto que recibe:* El objeto que recibe escucha los mensajes y responde. Usa el bloque *“Al recibir”* para saber cuándo llega un mensaje concreto.
- *Qué pasa con el mensaje:* Cuando el objeto que recibe recibe el mensaje, hace todos los bloques que están debajo del bloque *“Al recibir”*. Así decides tú qué pasa cuando llega un mensaje concreto.
