---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---
![Cando recibes 2](../../../media/bricks/when-you-receive-__2.png)

Con este bloque o teu obxecto escoita unha mensaxe de transmisión específica e despois executa o guión cando a mensaxe é recibida. Útil para comunicar entre os obxectos e dicirlles cando facer algo.

![Enviar a todos](../../../media/bricks/broadcast2.png)

**Por que enviar mensaxes de transmisión?**

Con mensaxes podes comunicarte e interactuar entre diferentes obxectos dentro dunha escena. Permite que un obxecto envíe unha mensaxe a todos os outros obxectos, e os obxectos receptores poden entón reaccionar ou responder a esa mensaxe.

Estas mensaxes están na pantalla **non mostrada**! Se queres mostrar mensaxes, usa os bloques de código na categoría «Aparencias»!

**Como funciona?**

- *Obxecto de remitente:* O obxecto remitente inicia a comunicación usando un bloque *«Enviar a todos»*. Este bloque é usado para enviar unha mensaxe a todos os outros obxectos da escena (tamén o fondo).
- *Obxecto receptor:* O obxecto receptor é aquel que escucha e responde a mensaxes. Utiliza o bloque *«Cando recibas»* para detectar cando se recibe unha mensaxe específica.
- *Manuseamento de mensaxes:* Cando o obxecto receptor recibe a mensaxe, executará todos os bloques que están debaixo do bloque *«Cando recibas»*. Isto permite definir o comportamento ou as accións personalizadas que ocorren cando un mensaxe particular é recibido.
