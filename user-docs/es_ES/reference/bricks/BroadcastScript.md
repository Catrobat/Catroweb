---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Al recibir](../../../media/bricks/when-you-receive-__2.png)

Con este bloque, tu objeto espera un mensaje concreto. Cuando llega el mensaje, el objeto empieza el script. Sirve para que los objetos hablen entre ellos y para decirles cuándo tienen que hacer algo.

![Enviar a todos](../../../media/bricks/broadcast2.png)

**¿Por qué enviar mensajes?**

Con los mensajes, los objetos de una escena pueden hablar entre ellos y trabajar juntos. Un objeto envía un mensaje a todos los demás objetos. Los objetos que lo reciben pueden reaccionar a ese mensaje.

¡Estos mensajes **no se ven** en la pantalla! Si quieres mostrar mensajes, usa los bloques de la categoría “Apariencia”.

**¿Cómo funciona?**

- *Objeto que envía:* El objeto que envía empieza la comunicación con el bloque *“Enviar a todos”*. Este bloque envía un mensaje a todos los demás objetos de la escena (también al fondo).
- *Objeto que recibe:* El objeto que recibe escucha los mensajes y responde. Usa el bloque *“Al recibir”* para saber cuándo llega un mensaje concreto.
- *Qué pasa con el mensaje:* Cuando el objeto que recibe recibe el mensaje, hace todos los bloques que están debajo del bloque *“Al recibir”*. Así decides tú qué pasa cuando llega un mensaje concreto.
