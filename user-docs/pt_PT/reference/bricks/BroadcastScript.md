---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Quando receber](../../../media/bricks/when-you-receive-__2.png)

Com este bloco, o teu objeto fica à escuta de uma mensagem de difusão específica e executa o script quando a mensagem chega. É útil para comunicar entre objetos e dizer-lhes quando devem fazer alguma coisa.

![Difundir](../../../media/bricks/broadcast2.png)

**Porque é que se envia mensagens de difusão?**

Com as mensagens, os objetos de uma cena conseguem comunicar e interagir entre si. Um objeto pode enviar uma mensagem a todos os outros objetos, e os objetos que a recebem podem reagir a essa mensagem.

Estas mensagens **não são mostradas** no ecrã! Se quiseres mostrar mensagens, usa os blocos da categoria “Aspeto”!

**Como funciona?**

- *Objeto que envia:* O objeto que envia começa a comunicação com um bloco *“Difundir”*. Este bloco envia uma mensagem a todos os outros objetos da cena (e também ao fundo).
- *Objeto que recebe:* O objeto que recebe é o que ouve e reage às mensagens. Usa o bloco *“Quando receber”* para detetar quando chega uma mensagem específica.
- *Tratamento da mensagem:* Quando o objeto que recebe recebe a mensagem, executa todos os blocos que estão por baixo do bloco *“Quando receber”*. Assim, podes definir um comportamento ou ações próprias que acontecem quando essa mensagem chega.
